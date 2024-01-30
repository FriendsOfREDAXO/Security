<?php

/**
 * @var rex_addon $this
 * @psalm-scope-this rex_addon
 */

echo rex_view::content(rex_i18n::msg('security_fe_access_info'));

$addon = rex_addon::get('security');
$addon_yrewrite = rex_addon::get('yrewrite');

if ('update' == rex_request('func', 'string')) {
    $this->setConfig('fe_access_password', rex_request('fe_access_password', 'string'));
    $this->setConfig('fe_access_status', rex_request('fe_access_status', 'int'));
    $this->setConfig('fe_access_domains', implode(',', rex_request('fe_access_domains', 'array')));
    echo rex_view::success($this->i18n('security_fe_access_settings_updated'));
}

$formElements = [];

$selActive = new rex_select();
$selActive->setId('security_fe_access_status');
$selActive->setName('fe_access_status');
$selActive->setSize(1);
$selActive->setAttribute('class', 'form-control selectpicker');
$selActive->setSelected($addon->getConfig('fe_access_status'));
foreach ([0 => $addon->i18n('disabled'), 1 => $addon->i18n('enabled')] as $i => $type) {
    $selActive->addOption($type, $i);
}

$n = [];
$n['label'] = '<label for="security_fe_access_status">' . rex_escape($addon->i18n('frontend_access')) . '</label>';
$n['field'] = $selActive->get();
$formElements[] = $n;

$selDomains = new rex_select();
$selDomains->setId('fe_access_domains');
$selDomains->setName('fe_access_domains[]');
$selDomains->setSize(5);
$selDomains->setMultiple();
$selDomains->setAttribute('class', 'form-control selectpicker');
if ('' != $addon->getConfig('fe_access_domains')) {
    foreach (explode(',', $addon->getConfig('fe_access_domains')) as $domain) {
        $selDomains->setSelected($domain);
    }
}

foreach (rex_yrewrite::getDomains() as $domain) {
    $selDomains->addOption($domain->getName(), $domain->getId());
}

$n = [];
$n['label'] = '<label for="security_fe_access_domains">' . rex_escape($addon->i18n('frontend_access_domains')) . '</label>';
$n['field'] = $selDomains->get();
$n['notice'] = $addon->i18n('frontend_access_domains_info');

$formElements[] = $n;

$n = [];
$n['label'] = '<label for="fe_access_password">' . $addon->i18n('security_fe_access_password') . '</label>';
$n['field'] = '<input class="form-control" id="fe_access_password" type="text" name="fe_access_password" placeholder="" value="' . rex_escape($addon->getConfig('fe_access_password')) . '" />';
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
$fragment->setVar('title', $this->i18n('security_settings'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
