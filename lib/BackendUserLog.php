<?php

namespace FriendsOfRedaxo\Security;

use rex;
use rex_addon;
use rex_backend_login;
use rex_log_file;
use rex_path;
use rex_request;
use rex_sql;
use rex_string;
use rex_user;

final class BackendUserLog
{
    /** @var string */
    public const TYPE_ACCESS = 'access';

    /** @var string */
    public const TYPE_LOGIN = 'login';

    /** @var string */
    public const TYPE_LOGOUT = 'logout';

    /** @var string */
    public const TYPE_UPDATE = 'update';

    /** @var string */
    public const TYPE_CLICK = 'click';

    /** @var string */
    public const TYPE_LOGIN_FAILED = 'login_failed';

    /** @var string */
    public const TYPE_REGISTERD = 'registerd';

    /** @var string */
    public const TYPE_DELETE = 'delete';

    /** @var array<string> */
    public const TYPES = [self::TYPE_ACCESS, self::TYPE_LOGIN, self::TYPE_LOGOUT, self::TYPE_UPDATE, self::TYPE_CLICK, self::TYPE_LOGIN_FAILED, self::TYPE_REGISTERD, self::TYPE_DELETE];

    /** @var int */
    private const MAX_FILE_SIZE = 20000000; // 20 Mb Default
    /** @var bool|null */
    private static $active;

    public static function init(): void
    {
        if (null !== rex_request('rex-api-call', 'string', null)) {
            return;
        }

        if (!rex::isBackend()) {
            return;
        }

        /** @var rex_backend_login $be_login */
        $be_login = rex::getProperty('login');
        if (null == $be_login) {
            return;
        }

        if (null == $be_login->getUser()) {
            return;
        }

        // Ansonsten URL spezifische Logs
        $pages = explode('/', rex_request::get('page', 'string', ''));
        $params = [];
        if ('yform' === $pages[0]) {
            $params = [
                'table_name' => rex_request::get('table_name', 'string', ''),
                'func' => rex_request::get('func', 'string', ''),
                'data_id' => rex_request::get('data_id', 'int', 0),
            ];
        }

        self::log($be_login, rex_request::get('page', 'string', ''), self::TYPE_ACCESS, $params);
    }

    public static function activate(): void
    {
        $addon = rex_addon::get('security');
        if ($addon->isAvailable()) {
            $addon->setConfig('be_user_log', 1);
            self::$active = true;
        }
    }

    public static function deactivate(): void
    {
        $addon = rex_addon::get('security');
        if ($addon->isAvailable()) {
            $addon->setConfig('be_user_log', 0);
            self::$active = false;
        }
    }

    public static function isActive(): bool
    {
        if (null === self::$active) {
            $addon = rex_addon::get('security');
            if ($addon->isAvailable()) {
                self::$active = 1 === $addon->getConfig('be_user_log');
            }
        }

        return (bool) self::$active;
    }

    public static function logFolder(): string
    {
        return rex_path::addonData('security', 'be_user');
    }

    public static function logFile(): string
    {
        return rex_path::log('be_user.log');
    }

    public static function delete(): bool
    {
        return rex_log_file::delete(self::logFile());
    }

    /**
     * @param rex_backend_login $be_login
     * @param array<string|int, string> $params
     */
    public static function log($be_login, string $page = '', string $type = self::TYPE_ACCESS, array $params = []): void
    {
        if (!self::isActive()) {
            return;
        }

        /** @var rex_user|rex_sql|null $be_user */
        $be_user = $be_login->getUser();
        $be_impersonate_user = $be_login->getImpersonator();

        $ip = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        $be_user_view = '';
        if ($be_user) {
            if ('rex_user' == $be_user::class) {
                $be_user_view = $be_user->getId() . ' [' . $be_user->getValue('email') . ']';
                $params['request_uri'] = $_SERVER['REQUEST_URI'];
            } elseif ('rex_sql' == $be_user::class) {
                /** @var rex_sql $be_user_view */
                $be_user_view = rex_string::normalize(rex_request('rex_user_login', 'string', ''));
                $type = self::TYPE_LOGIN_FAILED;
                $params = [
                    'SERVER' => $_SERVER,
                    'REQUEST' => $_REQUEST,
                ];
            }
        }

        $be_impersonate_user_view = '-';
        if ($be_impersonate_user) {
            $be_impersonate_user_view = $be_impersonate_user->getId() . ' [' . $be_impersonate_user->getEmail() . ']';
        }

        $log = rex_log_file::factory(self::logFile(), self::MAX_FILE_SIZE);
        $data = [
            $ip,
            $be_user_view,
            $be_impersonate_user_view,
            $page,
            $type,
            (string) json_encode($params),
        ];
        $log->add($data);
    }
}
