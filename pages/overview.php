<?php

namespace FriendsOfRedaxo\Security;

/**
 * @var \rex_addon $this
 */

$content = [];
$content[] = '<h3>'.$this->i18n('frontend_access'). '</h3>';
$content[] = 1 == \rex_config::get('security', 'fe_access_status') ? '<div class="alert alert-success">'.$this->i18n('enabled').'</div>' : '<div class="alert alert-info" style=";">'.$this->i18n('disabled').'</div>';

$content[] = '<h3>'.$this->i18n('error_notification'). '</h3>';
$content[] = 1 == \rex_config::get('security', 'error_notification_status') ? '<div class="alert alert-success">'.$this->i18n('enabled').'</div>' : '<div class="alert alert-warning" style=";">'.$this->i18n('disabled').'</div>';

$content[] = '<h3>'.$this->i18n('be_user_log'). '</h3>';
$content[] = BackendUserLog::isActive() ? '<div class="alert alert-success">'.$this->i18n('enabled').'</div>' : '<div class="alert alert-warning" style=";">'.$this->i18n('disabled').'</div>';

$content[] = '<h3>'.$this->i18n('ip_access_frontend'). '</h3>';
$content[] = IPAccess::isActive() ? '<div class="alert alert-success">'.$this->i18n('enabled').'</div>' : '<div class="alert alert-info" style=";">'.$this->i18n('disabled').'</div>';

$content[] = '<h3>'.$this->i18n('ip_access_backend'). '</h3>';
$content[] = IPAccess::isActive('backend') ? '<div class="alert alert-success">'.$this->i18n('enabled').'</div>' : '<div class="alert alert-warning" style=";">'.$this->i18n('disabled').'</div>';

$content[] = '<h3>'.$this->i18n('health_link'). '</h3>';
$content[] = '<div class="alert alert-info"><a href="'.Health::getLink().'">'.Health::getLink().'</a></div>';

$fragment = new \rex_fragment();
$fragment->setVar('title', $this->i18n('overview_description_title'), false);
$fragment->setVar('body', implode('', $content), false);
echo $fragment->parse('core/page/section.php');

$content = \rex_file::get(\rex_path::addon('security', 'README.md'));
echo \rex_view::content(\rex_markdown::factory()->parse($content));
