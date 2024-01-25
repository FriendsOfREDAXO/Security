<?php

/**
 * @var rex_addon $this
 * @psalm-scope-this rex_addon
 */

$CoreConfig = rex_file::getConfig(rex_path::coreData('config.yml'));
if ('update' == rex_request('func', 'string')) {
    $CoreConfig['session_duration'] = rex_request('session_duration', 'int');
    $CoreConfig['session_keep_alive'] = rex_request('session_keep_alive', 'int');
    $CoreConfig['session_max_overall_duration'] = rex_request('session_max_overall_duration', 'int');
    rex_file::putConfig(rex_path::coreData('config.yml'), $CoreConfig);
    echo rex_view::success($this->i18n('securit_be_login_settings_updated'));
}

$formElements = [];

$n = [];
$n['label'] = '<label for="be_login_config_session_duration">' . $this->i18n('securit_be_login_config_session_duration') . '</label>';
$n['field'] = '<input class="form-control" id="session_duration" type="text" name="session_duration" placeholder="" value="' . rex_escape((int) $CoreConfig['session_duration']) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="be_login_config_keep_alive">' . $this->i18n('securit_be_login_config_keep_alive') . '</label>';
$n['field'] = '<input class="form-control" id="session_keep_alive" type="text" name="session_keep_alive" placeholder="" value="' . rex_escape((int) $CoreConfig['session_keep_alive']) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="be_login_overall_duration">' . $this->i18n('securit_be_login_overall_duration') . '</label>';
$n['field'] = '<input class="form-control" id="session_max_overall_duration" type="text" name="session_max_overall_duration" placeholder="" value="' . rex_escape((int) $CoreConfig['session_max_overall_duration']) . '" />';
$n['note'] = $this->i18n('securit_be_login_overall_duration_notice');
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="fe_access_submit"></label>';
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
$fragment->setVar('title', $this->i18n('securit_settings'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
