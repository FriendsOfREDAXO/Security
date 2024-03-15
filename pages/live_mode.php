<?php

echo rex_view::error(rex_i18n::msg('security_live_mode_activate_info'));

switch (rex_request('func', 'string')) {
    case 'security_live_mode_activate':
        $config = rex_file::getConfig(rex_path::coreData('config.yml'));
        $config['live_mode'] = true;
        rex_file::putConfig(rex_path::coreData('config.yml'), $config);

        echo rex_view::success(rex_i18n::msg('security_live_mode_activated'));
        break;
}

$content = '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        <input type="hidden" name="func" value="security_live_mode_activate">
        <button type="submit" class="btn btn-danger" name="sendit" value="1">' . rex_i18n::msg('security_live_mode_activate') . '</button>
    </form>';

$fragment = new rex_fragment();
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
