<?php

namespace FriendsOfRedaxo\Securit;

final class FrontendAccess
{
    /**
     * @var string
     */
    private const COOKIE_NAME = 'securit_fe_access_password';

    /**
     * @throws \rex_exception
     */
    public static function init(): bool
    {
        if (!\rex::isFrontend()) {
            return true;
        }

        if (!self::getStatus()) {
            return true;
        }

        // Domaincheck
        $domainIds = \rex_config::get('securit', 'fe_access_domains');

        if ('' == $domainIds) {
            return true;
        }

        $domainIds = array_map('intval', explode(',', $domainIds));
        $currentDomain = \rex_yrewrite::getCurrentDomain();

        if (!$currentDomain) {
            return true;
        }

        if (!\in_array($currentDomain->getId(), $domainIds, true)) {
            return true;
        }

        if (rex_cookie(self::COOKIE_NAME) == sha1(\rex_config::get('securit', 'fe_access_password'))) {
            return true;
        }

        if (rex_request('fe_access_password') == \rex_config::get('securit', 'fe_access_password')) {
            $sessionConfig = \rex::getProperty('session', [])['frontend']['cookie'] ?? [];

            // Diese Cookie muss auch verfügbar sein, wenn man von außen kommt, da sonst bestimmte
            // Authentifizierungen nicht funktionieren können. Hier z.B. SAML

            \rex_response::sendCookie(self::COOKIE_NAME, sha1(rex_request('fe_access_password')), [
                'expires' => strtotime('+1 year'),
                'samesite' => 'none',
                'secure' => (bool) $sessionConfig['secure'],
            ]);

            return true;
        }

        $PasswordForm = '<form action="" method="post"><input type="text" name="fe_access_password" value="" /><input type="submit" /></form>';
        $PasswordForm = \rex_extension::registerPoint(new \rex_extension_point('securit_PASSWORD_FORM', $PasswordForm));

        \rex_response::sendContent($PasswordForm);
        exit;
    }

    public static function getPassword(): string
    {
        return \rex_config::get('securit', 'fe_access_password');
    }

    public static function setPassword(string $password): bool
    {
        return \rex_config::set('securit', 'fe_access_password', $password);
    }

    public static function getStatus(): bool
    {
        return 1 == \rex_config::get('securit', 'fe_access_status');
    }

    public static function activate(): void
    {
        \rex_config::set('securit', 'fe_access_status', 1);
    }

    public static function deactivate(): void
    {
        \rex_config::set('securit', 'fe_access_status', 0);
    }
}
