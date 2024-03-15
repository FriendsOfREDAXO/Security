<?php

namespace FriendsOfRedaxo\Security;

use rex_addon;
use rex_fragment;
use rex_i18n;
use rex_url;
use rex_view;
use rex_yrewrite;

use function count;

/** @var rex_addon $this */

// HTTPS
$content = [];
if (rex_yrewrite::isHttps()) {
    $content[] = rex_view::success(rex_i18n::msg('security_fe_https_ok'));
    $server = 'https://' . $_SERVER['HTTP_HOST'] . '/';
} else {
    $content[] = rex_view::error(rex_i18n::msg('security_fe_https_warning'));
    $server = 'http://' . $_SERVER['HTTP_HOST'] . '/';
}

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('security_header_https'), false);
$fragment->setVar('body', implode('', $content), false);
echo $fragment->parse('core/page/section.php');

// REDAXO Frontend Scan
foreach (['frontend', 'frontendredaxo', 'backend'] as $envirement) {
    switch ($envirement) {
        case 'frontend':
            $file = $server . 'assets/core/redaxo-logo.svg';
            break;
        case 'frontendredaxo':
            $file = $server;
            break;
        case 'backend':
            $file = $server . 'redaxo/';
            break;
    }
    $content = [];

    $fileHeaders = get_headers($file);
    if (false === $fileHeaders) {
        $content[] = rex_view::error(rex_i18n::msg('security_header_not_found', $file));
    } else {
        $header = [];
        foreach (get_headers($file) as $item) {
            $headerParts = explode(':', $item, 2);
            if (count($headerParts) > 1) {
                $headerName = strtolower(trim($headerParts[0]));
                $headerValue = trim($headerParts[1]);
                $header[$headerName] = $headerValue;
            }
        }

        $type = 'Strict-Transport-Security';
        if (isset($header[strtolower($type)])) {
            $content[] = rex_view::success(rex_i18n::msg('security_sts_fe_header_found', $header[strtolower($type)]));
            // TODO und Check: Info über die Dauer der HSTS
            // Info über preload
        } else {
            $content[] = rex_view::error(rex_i18n::msg('security_sts_fe_header_missing'));
        }

        $type = 'Referrer-Policy';
        if (isset($header[strtolower($type)])) {
            $content[] = rex_view::success(rex_i18n::msg('security_rp_fe_header_found', $header[strtolower($type)]));
            // TODO und Check: same-origin
        } else {
            $content[] = rex_view::error(rex_i18n::msg('security_rp_fe_header_missing'));
        }

        $type = 'X-XSS-Protection';
        if (isset($header[strtolower($type)])) {
            $content[] = rex_view::info(rex_i18n::msg('security_xss_fe_header_found', $header[strtolower($type)]));
            // TODO: Erklärung
        } else {
            $content[] = rex_view::info(rex_i18n::msg('security_xss_fe_header_missing'));
        }

        $type = 'X-Content-Type-Options';
        if (isset($header[strtolower($type)])) {
            $content[] = rex_view::success(rex_i18n::msg('security_cto_fe_header_found', $header[strtolower($type)]));
            // TODO: Erklärung
        } else {
            $content[] = rex_view::error(rex_i18n::msg('security_cto_fe_header_missing'));
        }

        $type = 'X-Frame-Options';
        if (isset($header[strtolower($type)])) {
            $content[] = rex_view::success(rex_i18n::msg('security_fo_fe_header_found', $header[strtolower($type)]));
            // TODO: Erklärung
        } else {
            $content[] = rex_view::error(rex_i18n::msg('security_fo_fe_header_missing'));
        }

        $type = 'Content-Security-Policy';
        if (isset($header[strtolower($type)])) {
            $content[] = rex_view::info(rex_i18n::msg('security_csp_fe_header_found'));
            $content[] = '<pre>' . $header[strtolower($type)] . '</pre>';
            // TODO: Erklärung
        }
    }

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('security_header_title_' . $envirement, $file), false);
    $fragment->setVar('body', implode('', $content), false);
    echo $fragment->parse('core/page/section.php');
}

$content = [];
$content[] = rex_view::info($this->i18n('security_header_htaccess'));

$content[] = '<h4>Apache</h4>';
$content[] = '<pre>';
$content[] = rex_escape(Header::getHeaderAsHtaccess());
$content[] = '</pre>';

$content[] = '<h4>Nginx</h4>';
$content[] = '<pre>';
$content[] = rex_escape(Header::getHeaderForNginx());
$content[] = '</pre>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('security_header_webserver_info'), false);
$fragment->setVar('body', implode('', $content), false);
echo $fragment->parse('core/page/section.php');

// TODO: Content security Policy

$addon = rex_addon::get('security');
$func = rex_request('func', 'string');
$activationLink = rex_url::currentBackendPage() . '&func=security_activate_nonce';
$deactivationLink = rex_url::currentBackendPage() . '&func=security_deactivate_nonce';

switch ($func) {
    case 'security_activate_nonce':
        echo rex_view::success($addon->i18n('ycom_user_log_activated'));
        Header::activateBackendNonce();
        break;
    case 'security_deactivate_nonce':
        echo rex_view::success($addon->i18n('ycom_user_log_deactivated'));
        Header::deactivateBackendNonce();
        break;
}

if (!Header::isBackendNonceActive()) {
    echo rex_view::warning(rex_i18n::rawMsg('security_backend_nonce_inactive', $activationLink));
} else {
    echo rex_view::warning(rex_i18n::rawMsg('security_backend_nonce_active', $deactivationLink));
}
