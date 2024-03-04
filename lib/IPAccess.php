<?php

namespace FriendsOfRedaxo\Security;

use rex;
use rex_addon;
use rex_exception;
use rex_extension;
use rex_file;
use rex_response;
use rex_yform_manager_table;

use function count;
use function ord;

use const PHP_SAPI;
use const STR_PAD_LEFT;

final class IPAccess
{
    /** @var string */
    public const table_name = 'rex_security_ip_access';

    /** @var string */
    private const config_name = 'config/ip_access_config.json';

    public static bool $active = true;

    /**
     * @throws rex_exception
     */
    public static function init(): void
    {
        if (!self::$active) {
            return;
        }

        if (rex::isBackend()) {
            rex_extension::register(['YFORM_DATA_UPDATED', 'YFORM_DATA_ADDED', 'YFORM_DATA_DELETED'], static function ($ep): void {
                $params = $ep->getParams();
                if (!$params['table']) {
                    return;
                }

                if (self::table_name != $params['table']->getTableName()) {
                    return;
                }

                self::getConfig(true);
            });
        }

        if ('cli' === PHP_SAPI) {
            return;
        }

        if (!self::getStatus(self::getIP())) {
            rex_response::setStatus(rex_response::HTTP_UNAUTHORIZED);
            rex_response::sendContent(rex_response::HTTP_UNAUTHORIZED);
            exit;
        }
    }

    private static function inet_to_bits(string $inet): string
    {
        $unpacked = unpack('A16', $inet);
        $unpacked = str_split($unpacked[1]);

        $binaryip = '';
        foreach ($unpacked as $char) {
            $binaryip .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        return $binaryip;
    }

    public static function getStatus(string $user_ip): bool
    {
        $config = self::getConfig(true);

        if (rex::isBackend()) {
            $environment = 'backend';
        } elseif (rex::isFrontend()) {
            $environment = 'frontend';
        } else {
            return true;
        }

        foreach (['allow' => true, 'block' => false] as $type => $type_return) {
            if (isset($config[$type][$environment])) {
                foreach ($config[$type][$environment] as $ip_type => $ips) {
                    if ('v6' == $ip_type) {
                        if (isset($ips['ip'])) {
                            foreach ($ips['ip'] as $ip) {
                                if ($user_ip == $ip) {
                                    return $type_return;
                                }
                            }
                        }

                        if (isset($ips['range'])) {
                            $ip = inet_pton($user_ip);
                            $binaryip = self::inet_to_bits($ip);
                            foreach ($ips['range'] as $range) {
                                [$net, $maskbits] = explode('/', $range);
                                $net = inet_pton($net);
                                $binarynet = self::inet_to_bits($net);

                                $ip_net_bits = substr($binaryip, 0, (int) $maskbits);
                                $net_bits = substr($binarynet, 0, (int) $maskbits);

                                if ($ip_net_bits !== $net_bits) {
                                } else {
                                    return $type_return;
                                }
                            }
                        }
                    } elseif ('v4' == $ip_type) {
                        if (isset($ips['ip'])) {
                            foreach ($ips['ip'] as $ip) {
                                if ($user_ip == $ip) {
                                    return $type_return;
                                }
                            }
                        }

                        if (isset($config['v4']['range'])) {
                            foreach ($config['v4']['range'] as $range) {
                                if (false == strpos($range, '/')) {
                                    $range .= '/32';
                                }

                                // $range is in IP/CIDR format eg 127.0.0.1/24
                                [$range, $netmask] = explode('/', $range, 2);
                                $range_decimal = ip2long($range);
                                $ip_decimal = ip2long($user_ip);
                                $wildcard_decimal = 2 ** (32 - (int) $netmask) - 1;
                                $netmask_decimal = ~$wildcard_decimal;
                                if (($ip_decimal & $netmask_decimal) === ($range_decimal & $netmask_decimal)) {
                                    return $type_return;
                                }
                            }
                        }
                    }
                }

                return !$type_return;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getConfig(bool $refresh_load = false): array
    {
        $filename = rex_addon::get('security')->getDataPath(self::config_name);
        if (!file_exists($filename) || $refresh_load) {
            $config = self::loadConfig();
            rex_file::putCache($filename, $config);
        } else {
            $config = rex_file::getCache($filename);
        }

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    public static function loadConfig(): array
    {
        $table = rex_yform_manager_table::get(self::table_name);
        if (!$table) {
            return [];
        }

        $ips = $table->query()->where('environment', '', '<>')->find();

        // type = 1 = allow
        // type = 2 = block

        $config = [];

        foreach ($ips as $ip) {
            if (str_contains($ip->getValue('ip'), ':')) {
                // v6
                if (str_contains($ip->getValue('ip'), '/')) {
                    $config[$ip->getValue('type')][$ip->getValue('environment')]['v6']['range'][] = $ip->getValue('ip');
                } elseif (str_contains($ip->getValue('ip'), '-')) {
                    $ip_dash_range = str_replace(' ', '', $ip->getValue('ip'));
                    $ip_dash_range = explode('-', $ip_dash_range);
                    if (2 == count($ip_dash_range) && 4 == count(explode('.', $ip_dash_range[0])) && 4 == count(explode('.', $ip_dash_range[1]))) {
                        $start = explode('.', $ip_dash_range[0]);
                        $end = explode('.', $ip_dash_range[1]);
                        if ($start[3] < $end[3]) {
                            for ($i = (int) $start[3]; $i <= $end[3]; ++$i) {
                                $config[$ip->getValue('type')][$ip->getValue('environment')]['v6']['ip'][] = $start[0] . '.' . $start[1] . '.' . $start[2] . '.' . $i;
                            }
                        }
                    }

                    $config[$ip->getValue('type')][$ip->getValue('environment')]['v6']['range'][] = $ip->getValue('ip');
                } else {
                    $config[$ip->getValue('type')][$ip->getValue('environment')]['v6']['ip'][] = $ip->getValue('ip');
                }
            } elseif (str_contains($ip->getValue('ip'), '/')) {
                // v4
                $config[$ip->getValue('type')][$ip->getValue('environment')]['v4']['range'][] = $ip->getValue('ip');
            } elseif (str_contains($ip->getValue('ip'), '-')) {
                $ip_dash_range = str_replace(' ', '', $ip->getValue('ip'));
                $ip_dash_range = explode('-', $ip_dash_range);
                if (2 == count($ip_dash_range) && 4 == count(explode('.', $ip_dash_range[0])) && 4 == count(explode('.', $ip_dash_range[1]))) {
                    $start = explode('.', $ip_dash_range[0]);
                    $end = explode('.', $ip_dash_range[1]);
                    if ($start[3] < $end[3]) {
                        for ($i = (int) $start[3]; $i <= $end[3]; ++$i) {
                            $config[$ip->getValue('type')][$ip->getValue('environment')]['v4']['ip'][] = $start[0] . '.' . $start[1] . '.' . $start[2] . '.' . $i;
                        }
                    }
                }
            } else {
                $config[$ip->getValue('type')][$ip->getValue('environment')]['v4']['ip'][] = $ip->getValue('ip');
            }
        }

        return $config;
    }

    public static function getIP(): string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // remove Port if IPv4
        if (1 == substr_count($ip, ':')) {
            return substr($ip, 0, (int) strpos($ip, ':'));
        }

        return $ip;
    }

    /**
     * @param string[] $ip_array
     * @return string[]
     */
    public static function addIP(array $ip_array): array
    {
        $table = rex_yform_manager_table::get(self::table_name);
        if (!$table) {
            return [];
        }

        $ds = $table->createDataset();
        $ds->setvalue('ip', $ip_array['ip'])
            ->setValue('type', $ip_array['type'])
            ->setValue('environment', $ip_array['type'])
            ->save();

        return $ds->getMessages();
    }

    public static function isActive(string $envirement = 'frontend'): bool
    {
        $config = self::getConfig();

        foreach (['allow' => true, 'block' => false] as $type => $type_return) {
            if (isset($config[$type][$envirement])) {
                return true;
            }
        }

        return false;
    }
}
