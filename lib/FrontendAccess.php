<?php

namespace FriendsOfRedaxo\Security;

use rex;
use rex_config;
use rex_exception;
use rex_extension;
use rex_extension_point;
use rex_fragment;
use rex_response;
use rex_yrewrite;

use function in_array;

final class FrontendAccess
{
    /** @var string */
    private const COOKIE_NAME = 'security_fe_access_password';

    /**
     * @throws rex_exception
     */
    public static function init(): bool
    {
        if (!rex::isFrontend()) {
            return true;
        }

        if (!self::getStatus()) {
            return true;
        }

        // Domaincheck
        $domainIds = rex_config::get('security', 'fe_access_domains');

        if ('' == $domainIds) {
            return true;
        }

        $domainIds = array_map('intval', explode(',', $domainIds));
        $currentDomain = rex_yrewrite::getCurrentDomain();

        if (!$currentDomain) {
            return true;
        }

        if (!in_array($currentDomain->getId(), $domainIds, true)) {
            return true;
        }

        if (rex_cookie(self::COOKIE_NAME) == sha1(rex_config::get('security', 'fe_access_password') . $currentDomain->getName())) {
            return true;
        }

        if (rex_request('fe_access_password') == rex_config::get('security', 'fe_access_password')) {
            // Diese Cookie muss auch verfügbar sein, wenn man von außen kommt, da sonst bestimmte
            // Authentifizierungen nicht funktionieren können. Hier z.B. SAML

            rex_response::sendCookie(self::COOKIE_NAME, sha1(rex_request('fe_access_password') . $currentDomain->getName()), [
                'expires' => strtotime('+1 year'),
                'samesite' => 'none',
                'secure' => true,
            ]);

            return true;
        }

        $PasswordFormPage = new rex_fragment();
        $PasswordForm = $PasswordFormPage->parse('security_fe_access_form.php');
        $PasswordForm = rex_extension::registerPoint(new rex_extension_point('security_PASSWORD_FORM', $PasswordForm));

        rex_response::sendContent($PasswordForm);
        exit;
    }

    public static function getPassword(): string
    {
        return rex_config::get('security', 'fe_access_password');
    }

    public static function setPassword(string $password): bool
    {
        return rex_config::set('security', 'fe_access_password', $password);
    }

    public static function getStatus(): bool
    {
        return 1 == rex_config::get('security', 'fe_access_status');
    }

    public static function activate(): void
    {
        rex_config::set('security', 'fe_access_status', 1);
    }

    public static function deactivate(): void
    {
        rex_config::set('security', 'fe_access_status', 0);
    }
}
