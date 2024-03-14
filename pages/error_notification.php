<?php

namespace FriendsOfRedaxo\Security;

use rex;
use rex_addon;
use rex_fragment;
use rex_i18n;
use rex_select;
use rex_url;
use rex_view;

use function count;

/**
 * @var rex_addon $this
 * @psalm-scope-this rex_addon
 */

$addon = rex_addon::get('security');

echo rex_view::content(rex_i18n::msg('security_error_notification_info'));

if ('update' == rex_request('func', 'string')) {
    $this->setConfig('error_notification_status', rex_request('error_notification_status', 'int'));
    $this->setConfig('error_notification_email', rex_request('error_notification_email', 'string'));
    $this->setConfig('error_notification_name', rex_request('error_notification_name', 'string'));
    $this->setConfig('error_notification_key', rex_request('error_notification_key', 'string'));
    $this->setConfig('error_notification_package', rex_request('error_notification_package', 'int'));
    echo rex_view::success($this->i18n('security_fe_access_settings_updated'));
}

$formElements = [];

$selActive = new rex_select();
$selActive->setId('security_error_notification_status');
$selActive->setName('error_notification_status');
$selActive->setSize(1);
$selActive->setAttribute('class', 'form-control selectpicker');
$selActive->setSelected($addon->getConfig('error_notification_status'));
foreach ([0 => $addon->i18n('disabled'), 1 => $addon->i18n('enabled')] as $i => $type) {
    $selActive->addOption($type, $i);
}

$n = [];
$n['label'] = '<label for="security_error_notification_status">' . rex_escape($this->i18n('error_notification')) . '</label>';
$n['field'] = $selActive->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="error_notification_email">' . $this->i18n('error_notification_email') . '</label>';
$n['field'] = '<input class="form-control" id="error_notification_email" type="text" name="error_notification_email" placeholder="' . rex_escape(rex::getErrorEmail()) . '" value="' . rex_escape($addon->getConfig('error_notification_email')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="error_notification_name">' . $this->i18n('error_notification_name') . '</label>';
$n['field'] = '<input class="form-control" id="error_notification_name" type="text" name="error_notification_name" placeholder="' . rex_escape(ErrorNotification::email_name) . '" value="' . rex_escape($addon->getConfig('error_notification_name')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="error_notification_key">' . $this->i18n('error_notification_key') . '</label>';
$n['field'] = '<input class="form-control" id="error_notification_key" type="text" name="error_notification_key" value="' . rex_escape($addon->getConfig('error_notification_key')) . '" />';
$formElements[] = $n;

$selActive = new rex_select();
$selActive->setId('security_error_notification_package');
$selActive->setName('error_notification_package');
$selActive->setSize(1);
$selActive->setAttribute('class', 'form-control selectpicker');
$selActive->setSelected($addon->getConfig('error_notification_package'));
foreach ([
    0 => $addon->i18n('security_direct_email'),
    1 => $addon->i18n('security_bundle_email'),
] as $i => $type) {
    $selActive->addOption($type, $i);
}

$n = [];
$n['label'] = '<label for="security_error_notification_package">' . rex_escape($this->i18n('error_notification_package')) . '</label>';
$n['field'] = $selActive->get() . '<p class="help-block rex-note">' . rex_escape(rex_i18n::rawMsg('security_error_notification_package_notice')) . '</p>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="error_notification_submit"></label>';
$n['field'] = '<button class="btn btn-save right" type="submit" name="config-submit" value="1" title="' . $this->i18n('config_save') . '">' . $this->i18n('config_save') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$formElementsView = $fragment->parse('core/form/form.php');

$content = '
<form action="' . rex_url::currentBackendPage() . '" method="post">
    <input type="hidden" name="func" value="update" />
	<fieldset>
		' . $formElementsView . '
    </fieldset>
	</form>
  ';

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $this->i18n('security_settings'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

if (!file_exists(ErrorNotification::getPath())) {
    echo rex_view::error(rex_i18n::rawMsg('security_error_notification_path_not_exists', ErrorNotification::getPath()));
} else {
    if ('delete_log' == rex_request('func', 'string')) {
        ErrorNotification::deleteLogFiles();
        echo rex_view::success($this->i18n('log_deleted'));
    } elseif ('download_log' == rex_request('func', 'string')) {
        ErrorNotification::downloadLogFiles();
        echo rex_view::success($this->i18n('log_deleted'));
    }

    $formElements = [];

    $n = [];
    $n['label'] = '<label for="error_notification_log">' . $this->i18n('log') . '</label>';
    $n['field'] = '<div>' . $this->i18n('log_info', count(ErrorNotification::getLogFiles()), rex_addon::get('security')->getDataPath('error_notifications')) . '</div>';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="error_notification_delete"></label>';
    $n['field'] = '<button class="btn btn-delete right" type="submit" name="func" value="delete_log" title="' . $this->i18n('log_delete') . '">' . $this->i18n('log_delete') . '</button>';
    $n['field'] .= ' <button class="btn btn-save right" type="submit" name="func" value="download_log" title="' . $this->i18n('log_download') . '">' . $this->i18n('log_download') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $formElementsView = $fragment->parse('core/form/form.php');

    $content = '
<form action="' . rex_url::currentBackendPage() . '" method="post">
	<fieldset>
		' . $formElementsView . '
    </fieldset>
	</form>
  ';

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit');
    $fragment->setVar('title', $this->i18n('security_settings'));
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}
