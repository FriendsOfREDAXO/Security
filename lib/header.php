<?php

namespace FriendsOfRedaxo\Security;

use rex;
use rex_addon;
use rex_response;

/**
 * https://wiki.selfhtml.org/wiki/Sicherheit/Content_security_Policy
 * https://cspvalidator.org/#url=
 * https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-security-Policy/script-src
 * https://cheatsheetseries.owasp.org/cheatsheets/Content_security_Policy_Cheat_Sheet.html.
 * https://report-uri.com/home/generate.
 */

/*
 * https://www.invicti.com/blog/web-security/content-security-policy/
 *
 * mit Reporting loslegen und dann wirklich testen
 *
 */

final class Header
{
    private static ?bool $NonceActive = null;

    public static function init(): void
    {
        self::send();
    }

    /**
     * @param array<string, mixed> $CSPArray
     */
    private static function buildCSPHeader(array $CSPArray): string
    {
        $Value = [];
        foreach ($CSPArray as $src => $v) {
            $Value[] = $src . ' ' . implode(' ', $v) . ';';
        }

        return implode(' ', $Value);
    }

    /**
     * @return array<string, string>
     */
    public static function getHeader(bool $ignoreNonce = false): array
    {
        $header = [];
        $header['Content-security-Policy'] = [
            'default-src' => [
                "'self'",
                'https://www.youtube.com',
                'https://www.w3.org/2000/svg',
                'data:',
            ],
            'base-uri' => [
                "'self'",
            ],
            'img-src' => [
                "'self'",
                'data:',
            ],
            'script-src' => [
                "'self'",
            ],
            'style-src' => [
                "'self'",
                // "'unsafe-inline'",
            ],
            'object-src' => [
                "'none'",
            ],
            // 'frame-ancestors' => ["'self'"],
            // 'form-action' => ["'self'"], // 'none'
            // connect-src
            // font-src
            // frame-src
            // manifest-src
            // media-src
            // prefetch-src
            // script-src-elem
            // script-src-attr
            // style-src-elem
            // style-src-attr
            // worker-src
        ];

        if (!$ignoreNonce) {
            $header['Content-security-Policy']['script-src'][] = "'nonce-" . rex_response::getNonce() . "'";
            $header['Content-security-Policy']['style-src'][] = "'nonce-" . rex_response::getNonce() . "'";
        }

        $header['Content-security-Policy'] = self::buildCSPHeader($header['Content-security-Policy']);

        $header['Strict-Transport-security'] = 'max-age=31536000; includeSubDomains; preload';
        $header['Referrer-Policy'] = 'same-origin';
        $header['X-Content-Type-Options'] = 'nosniff';
        $header['X-Frame-Options'] = 'sameorigin';

        return $header;
    }

    public static function getHeaderAsHtaccess(): string
    {
        $return = [];
        foreach (self::getHeader(true) as $name => $value) {
            $return[] = "\t" . 'Header set ' . $name . ' "' . $value . '"';
        }

        $return[] = '	Header unset X-Powered-By';
        $return[] = '	Header unset Server';

        return '<IfModule mod_headers.c>' . "\n" . implode("\n", $return) . "\n" . '</IfModule>';
    }

    public static function getHeaderForNginx(): string
    {
        $return = [];
        foreach (self::getHeader(true) as $name => $value) {
            $return[] = 'add_header ' . $name . ' "' . $value . '"';
        }

        return implode("\n", $return);
    }

    public static function activateBackendNonce(): void
    {
        $addon = rex_addon::get('security');
        $addon->setConfig('BackendNonce', 1);
        self::$NonceActive = true;
    }

    public static function deactivateBackendNonce(): void
    {
        $addon = rex_addon::get('security');
        $addon->setConfig('BackendNonce', 0);
        self::$NonceActive = false;
    }

    public static function isBackendNonceActive(): bool
    {
        if (null === self::$NonceActive) {
            $addon = rex_addon::get('security');
            self::$NonceActive = (1 === $addon->getConfig('BackendNonce')) ? true : false;
        }
        return (self::$NonceActive) ? true : false;
    }

    private static function send(): void
    {
        if (rex::isBackend() && self::isBackendNonceActive()) {
            // fürs Backend wird ein sehr viel lockereres CSP verwendet

            $Content_security_Policy_Header = [];
            $Content_security_Policy_Header['script-src'][] = "'self'";
            $Content_security_Policy_Header['style-src'][] = "'self'";
            $Content_security_Policy_Header['base-uri'][] = "'self'";
            $Content_security_Policy_Header['object-src'][] = "'none'";

            $be_login = rex::getProperty('login');
            if (null == $be_login || !$be_login->getUser()) {
                // nicht eingeloggt
                $Content_security_Policy_Header['script-src'][] = "'unsafe-inline'";
                $Content_security_Policy_Header['script-src'][] = "'unsafe-eval'";
                $Content_security_Policy_Header['style-src'][] = "'unsafe-inline'";
            } else {
                // eingeloggt
                $Content_security_Policy_Header['script-src'][] = "'nonce-" . rex_response::getNonce() . "'";
                $Content_security_Policy_Header['style-src'][] = "'nonce-" . rex_response::getNonce() . "'";
            }

            $value = self::buildCSPHeader($Content_security_Policy_Header);
            rex_response::setHeader('Content-security-Policy', $value);
            rex_response::sendCacheControl('no-store');
            rex_response::setHeader('Pragma', 'no-cache');
        }
    }
}
