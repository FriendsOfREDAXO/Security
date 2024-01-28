<?php

namespace FriendsOfRedaxo\Securit;

/**
 * @var \rex_addon $this
 */

// Frontendsessions killen können
// Allen Usern neue SessionID geben

// SESSION HANDLING um alte Sessions zu löschen
// -> FE / BE / beides
// -> Zeitraum in Session setzen wenn nicht gesetzt
// -> wenn Zeitpunkt erreicht -> Session killen

// Benachrichtigung bei bestimmten EPs aktivieren
// z.B. EP erreicht, dann User loggen und Email raus

// 2 Factor aktivierbar fürs BE machen
// Welche Authentifizierung
// Email mit Code, Authenticator

// Passwort vergessen
// E-Mail Info durchschicken.

// Failed Logins
// Benachrichtigung
// log
// IP Block bei bestimmten Mengen in bestimmter Zeit

// Passwortregeln erweitern
// - Reenter Password after x Months
// - alte hashes speichern und prüfen ob das passwort schon benutzt wurde

// Header ergänzen

$content = [];
$content[] = '<h3>'.$this->i18n('frontend_access'). '</h3>';
$content[] = 1 == \rex_config::get('securit', 'fe_access_status') ? '<div class="alert alert-success">'.$this->i18n('enabled').'</div>' : '<div class="alert alert-info" style=";">'.$this->i18n('disabled').'</div>';

$content[] = '<h3>'.$this->i18n('error_notification'). '</h3>';
$content[] = 1 == \rex_config::get('securit', 'error_notification_status') ? '<div class="alert alert-success">'.$this->i18n('enabled').'</div>' : '<div class="alert alert-warning" style=";">'.$this->i18n('disabled').'</div>';

$content[] = '<h3>'.$this->i18n('be_user_log'). '</h3>';
$content[] = BackendUserLog::isActive() ? '<div class="alert alert-success">'.$this->i18n('enabled').'</div>' : '<div class="alert alert-warning" style=";">'.$this->i18n('disabled').'</div>';

$content[] = '<h3>'.$this->i18n('ip_access_frontend'). '</h3>';
$content[] = IPAccess::isActive() ? '<div class="alert alert-success">'.$this->i18n('enabled').'</div>' : '<div class="alert alert-info" style=";">'.$this->i18n('disabled').'</div>';

$content[] = '<h3>'.$this->i18n('ip_access_backend'). '</h3>';
$content[] = IPAccess::isActive("backend") ? '<div class="alert alert-success">'.$this->i18n('enabled').'</div>' : '<div class="alert alert-warning" style=";">'.$this->i18n('disabled').'</div>';

$content[] = '<h3>'.$this->i18n('health_link'). '</h3>';
$content[] = '<div class="alert alert-info"><a href="'.Health::getLink().'">'.Health::getLink().'</a></div>';

$fragment = new \rex_fragment();
$fragment->setVar('title', $this->i18n('overview_description_title'), false);
$fragment->setVar('body', implode('', $content), false);
echo $fragment->parse('core/page/section.php');

$content = \rex_file::get(\rex_path::addon('securit', 'README.md'));
echo \rex_view::content(\rex_markdown::factory()->parse($content));
