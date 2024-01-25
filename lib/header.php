<?php

/**
 * https://wiki.selfhtml.org/wiki/Sicherheit/Content_Security_Policy
 * https://cspvalidator.org/#url=
 * https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src
 * https://cheatsheetseries.owasp.org/cheatsheets/Content_Security_Policy_Cheat_Sheet.html.
 * https://report-uri.com/home/generate.
 */

/*
 * https://www.invicti.com/blog/web-security/content-security-policy/
 *
 * mit Reporting loslegen und dann wirklich testen
 *
 */

final class rex_securit_header
{
    public static function init(): void
    {
        self::send();
    }

    private static function buildCSPHeader(array $CSPArray): string
    {
        $Value = [];
        foreach ($CSPArray as $src => $v) {
            $Value[] = $src . ' ' . implode(' ', $v).';';
        }

        return implode(' ', $Value);
    }

    private static function getHeader(bool $ignoreNonce = false): array
    {
        $header = [];
        $header['Content-Security-Policy'] = [
            'default-src' => [
                "'self'",
                'https://www.youtube-nocookie.com',
                'https://www.w3.org/2000/svg',
            ],
            'base-uri' => ["'self'"],
            'img-src' => ["'self'", 'data:'],
            'script-src' => [
                "'self'",
            ],
            'style-src' => [
                "'self'",
            ], // , "'unsafe-inline'" "*", "'self'", "'nonce-".self::$nonce."'",
            'object-src' => ["'none'"],
            //            'frame-ancestors' => ["'self'"],
            //            'form-action' => ["'self'"], // 'none'
            //        connect-src
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

        // if (!$ignoreNonce) {
            $header['Content-Security-Policy']['script-src'][] = "'nonce-".rex_response::getNonce()."'";
            $header['Content-Security-Policy']['style-src'][] = "'nonce-".rex_response::getNonce()."'";
        // }

        $header['Content-Security-Policy'] = self::buildCSPHeader($header['Content-Security-Policy']);

        // edge cases

        // App, Menu
        // if (isset($_SERVER['REQUEST_URI']) && '/menu/' == $_SERVER['REQUEST_URI']) {
        //     unset($header['Content-Security-Policy']);
        // }
        //
        unset($header['Content-Security-Policy']);

        // $header['Strict-Transport-Security'] = 'max-age=31536000; preload';
        // $header['Referrer-Policy'] = 'same-origin';
        // $header['X-XSS-Protection'] = '0'; // '1; mode=block';
        // $header['X-Content-Type-Options'] = 'nosniff';
        // $header['X-Frame-Options'] = 'sameorigin';
        // Header always unset "X−Powered−By"
        // Header always unset "Server"

        return $header;
    }

    public static function getHeaderAsHtaccess(): string
    {
        $return = [];
        foreach (self::getHeader(true) as $name => $value) {
            $return[] = "\t".'Header set '.$name.' "'.$value.'"';
        }

        $return[] = '	Header unset X-Powered-By';

        return '<IfModule mod_headers.c>'."\n".implode("\n", $return)."\n".'</IfModule>';
    }

    public static function getHeaderForNginx(): string
    {
        $return = [];
        foreach (self::getHeader(true) as $name => $value) {
            $return[] = "\t".'add_header '.$name.' "'.$value.'"';
        }

        return implode("\n", $return);
    }

    private static function send(): void
    {
        foreach (self::getHeader() as $name => $value) {
            if (rex::isBackend() && 'Content-Security-Policy' == $name) {
                // fürs Backend wird ein sehr viel lockereres CSP verwendet

                $Content_Security_Policy_Header = [];
                $Content_Security_Policy_Header['script-src'][] = "'self'";
                $Content_Security_Policy_Header['style-src'][] = "'self'";
                $Content_Security_Policy_Header['base-uri'][] = "'self'";
                $Content_Security_Policy_Header['object-src'][] = "'none'";

                // find out if redaxo is logged in

//                $be_login = rex::getProperty('login');
//                if (null == $be_login || !$be_login->getUser()) {
                    // nicht eingeloggt
                    $Content_Security_Policy_Header['script-src'][] = "'nonce-".rex_response::getNonce()."'";
                    $Content_Security_Policy_Header['style-src'][] = "'nonce-".rex_response::getNonce()."'";
//                } else {
//                    // eingeloggt
//                    $Content_Security_Policy_Header['script-src'][] = "'unsafe-inline'";
//                    $Content_Security_Policy_Header['script-src'][] = "'unsafe-eval'";
//                    $Content_Security_Policy_Header['style-src'][] = "'unsafe-inline'";
//                }

                $value = self::buildCSPHeader($Content_Security_Policy_Header);
//                continue;
            }

            rex_response::setHeader($name, $value);
        }

        if (rex::isBackend()) {
            rex_response::sendCacheControl('no-store');
            rex_response::setHeader('Pragma', 'no-cache');
        }
    }
}
