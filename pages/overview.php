<?php

/**
 * @var rex_addon $this
 * @psalm-scope-this rex_addon
 */

// FE Access
// - gültigkeitsdauer ergänzen - mit Zeit in SHA verpacken. HH:ii:ss ..

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

/*
$addon = rex_addon::get('securit');
$form = rex_config_form::factory($addon->getName());

$field = $form->addSelectField('fe_access_status', rex_config::get('securit', 'fe_access_status'));
$field->getSelect()->addOptions([1 => $addon->i18n('enabled'), 0 => $addon->i18n('disabled')]);
$field->setLabel(rex_i18n::msg('securit_fe_access_status'));
$field->setNotice(rex_i18n::msg('securit_fe_access_status_notice'));

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Settings', false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');
*/

// echo '<div class="alert alert-info" style=";">abc</div>';

$content = [];
$content[] = '<h3>'.$this->i18n('frontend_access'). '</h3>';
$content[] = 1 == rex_config::get('securit', 'fe_access_status') ? '<div class="alert alert-success">'.$this->i18n('enabled').'</div>' : '<div class="alert alert-info" style=";">'.$this->i18n('disabled').'</div>';

$content[] = '<h3>'.$this->i18n('error_notification'). '</h3>';
$content[] = 1 == rex_config::get('securit', 'error_notification_status') ? '<div class="alert alert-success">'.$this->i18n('enabled').'</div>' : '<div class="alert alert-warning" style=";">'.$this->i18n('disabled').'</div>';

$content[] = '<h3>'.$this->i18n('health_link'). '</h3>';
$content[] = '<div class="alert alert-info"><a href="'.rex_securit_health::getLink().'">'.rex_securit_health::getLink().'</a></div>';

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('overview_description_title'), false);
$fragment->setVar('body', implode('', $content), false);
echo $fragment->parse('core/page/section.php');
