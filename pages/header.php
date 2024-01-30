<?php

namespace FriendsOfRedaxo\Securit;

/**
 * @var \rex_addon $this
 */

$currentDomain = \rex_yrewrite::getCurrentDomain();

// HTTPS
$content = [];
if ('https' == substr($currentDomain->getUrl(), 0, 5)) {
    $content[] = \rex_view::info(\rex_i18n::msg('securit_fe_https_ok'));
} else {
    $content[] = \rex_view::error(\rex_i18n::msg('securit_fe_https_warning'));
}

$fragment = new \rex_fragment();
$fragment->setVar('title', \rex_i18n::msg('security_header_https'), false);
$fragment->setVar('body', implode('', $content), false);
echo $fragment->parse('core/page/section.php');

// REDAXO Frontend

foreach (['frontend', 'frontendredaxo', 'backend'] as $envirement) {

    switch ($envirement) {
        case 'frontend':
            $file = $currentDomain->getUrl().'assets/core/redaxo-logo.svg';
            break;
        case 'frontendredaxo':
            $file = $currentDomain->getUrl();
            break;
        case 'backend':
            $file = $currentDomain->getUrl().'redaxo/';
            break;

    }
    $content = [];

    $header = [];

    foreach (get_headers($file) as $item) {
        $headerParts = explode(':', $item);
        if (\count($headerParts) > 1) {
            $headerName = trim($headerParts[0]);
            $headerValue = trim($headerParts[1]);
            $header[$headerName] = $headerValue;
        }
    }

    $type = 'Strict-Transport-Security';
    if (isset($header['Strict-Transport-Security'])) {
        $content[] = \rex_view::success(\rex_i18n::msg('securit_sts_fe_header_found', $header[$type]));
        // TODO und Check: Info über die Dauer der HSTS
        // Info über preload
    } else {
        $content[] = \rex_view::error(\rex_i18n::msg('securit_sts_fe_header_missing'));
    }

    $type = 'Referrer-Policy';
    if (isset($header[$type])) {
        $content[] = \rex_view::success(\rex_i18n::msg('securit_rp_fe_header_found', $header[$type]));
        // TODO und Check: same-origin
    } else {
        $content[] = \rex_view::error(\rex_i18n::msg('securit_rp_fe_header_missing'));
    }

    $type = 'X-XSS-Protection';
    if (isset($header[$type])) {
        $content[] = \rex_view::info(\rex_i18n::msg('securit_xss_fe_header_found', $header[$type]));
        // TODO: Erklärung
    } else {
        $content[] = \rex_view::info(\rex_i18n::msg('securit_xss_fe_header_missing'));
    }

    $type = 'X-Content-Type-Options';
    if (isset($header[$type])) {
        $content[] = \rex_view::success(\rex_i18n::msg('securit_cto_fe_header_found', $header[$type]));
        // TODO: Erklärung
    } else {
        $content[] = \rex_view::error(\rex_i18n::msg('securit_cto_fe_header_missing'));
    }

    $type = 'X-Frame-Options';
    if (isset($header[$type])) {
        $content[] = \rex_view::success(\rex_i18n::msg('securit_fo_fe_header_found', $header[$type]));
        // TODO: Erklärung
    } else {
        $content[] = \rex_view::error(\rex_i18n::msg('securit_fo_fe_header_missing'));
    }

    $fragment = new \rex_fragment();
    $fragment->setVar('title', \rex_i18n::msg('securit_header_title_'.$envirement, $file), false);
    $fragment->setVar('body', implode('', $content), false);
    echo $fragment->parse('core/page/section.php');

}

$content = [];
$content[] = \rex_view::info($this->i18n('securit_header_htaccess'));

$content[] = '<h4>Apache</h4>';
$content[] = '<pre>';
$content[] = rex_escape(Header::getHeaderAsHtaccess());
$content[] = '</pre>';

$content[] = '<h4>Nginx</h4>';
$content[] = '<pre>';
$content[] = rex_escape(Header::getHeaderForNginx());
$content[] = '</pre>';

$fragment = new \rex_fragment();
$fragment->setVar('title', \rex_i18n::msg('securit_header_webserver_info'), false);
$fragment->setVar('body', implode('', $content), false);
echo $fragment->parse('core/page/section.php');

// TODO: Content Security Policy

$addon = \rex_addon::get('securit');
$func = rex_request('func', 'string');
$activationLink = \rex_url::currentBackendPage() . '&func=securit_activate_nonce';
$deactivationLink = \rex_url::currentBackendPage() . '&func=securit_deactivate_nonce';

switch ($func) {
    case 'securit_activate_nonce':
        echo \rex_view::success($addon->i18n('ycom_user_log_activated'));
        Header::activateBackendNonce();
        break;
    case 'securit_deactivate_nonce':
        echo \rex_view::success($addon->i18n('ycom_user_log_deactivated'));
        Header::deactivateBackendNonce();
        break;
}

if (!Header::isBackendNonceActive()) {
    echo \rex_view::warning(\rex_i18n::rawMsg('securit_backend_nonce_inactive', $activationLink));
} else {
    echo \rex_view::warning(\rex_i18n::rawMsg('securit_backend_nonce_active', $deactivationLink));
}
