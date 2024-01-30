<?php

/**
 * @var rex_addon $this
 * @psalm-scope-this rex_addon
 */

$links = [];

$links[] = [
    'url' => 'https://observatory.mozilla.org/analyze/redaxo.org',
    'name' => '',
];

$links[] = [
    'url' => 'https://validator.w3.org',
    'name' => '',
];

$links[] = [
    'url' => 'https://developers.google.com/speed/pagespeed/insights/?hl=de',
    'name' => '',
];

$links[] = [
    'url' => 'https://www.seobility.net/de/seocheck/',
    'name' => '',
];
$links[] = [
    'url' => 'https://www.backlinktest.com/deadlink.php',
    'name' => '',
];
$links[] = [
    'url' => 'https://www.ssllabs.com/ssltest/analyze.html?d=redaxo.org',
    'name' => '',
];

$links[] = [
    'url' => 'https://www.whynopadlock.com/',
    'name' => 'Keychainüberprüfunge',
];

$links[] = [
    'url' => 'https://web.dev/measure/',
    'name' => '',
];

$links[] = [
    'url' => 'https://webaccessibilitychecklist.com/',
    'name' => '',
];

$links[] = [
    'url' => 'https://www.a11yproject.com/checklist/',
    'name' => '',
];

$links[] = [
    'url' => 'https://www.cookiemetrix.com/',
    'name' => '',
];

/*
https://github.com/davidsonfellipe/awesome-wpo (Web Performance Optimization List)
https://github.com/TheJambo/awesome-testing
https://github.com/ZYSzys/awesome-captcha#readme
Twitter-Card-Validator https://cards-dev.twitter.com/validator
Browser Test https://ghostinspector.com
https://googlechrome.github.io/lighthouse/viewer/?psiurl=https%3A%2F%2Fmeinedomain.de%2F&strategy=mobile&category=performance&category=accessibility&category=best-practices&category=seo&category=pwa&utm_source=lh-chrome-ext
*/

/*
 * Best practise
 *
 * PHPSESSID php.ini wird der Name der Session in dem Parameter session.name
 *  session_name("meineSession");
 */

$content = [];
foreach ($links as $link) {
    $content[] = '<h3>'.(('' == $link['name']) ? $link['url'] : $link['name']).'</h3><p><a href="'.$link['url'].'">'.$link['url'].'</a></p>';
}

echo rex_view::content(rex_i18n::msg('security_checks_info'));

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('security_checks'), false);
$fragment->setVar('body', implode('', $content), false);
echo $fragment->parse('core/page/section.php');
