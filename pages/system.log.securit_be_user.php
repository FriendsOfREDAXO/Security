<?php

namespace FriendsOfRedaxo\Security;

use rex_addon;

/**
 * @var \rex_addon $this
 * @psalm-scope-this rex_addon
 */

$addon = \rex_addon::get('security');
$func = rex_request('func', 'string');
$activationLink = \rex_url::currentBackendPage().'&func=security_be_user_activate_log';
$deactivationLink = \rex_url::currentBackendPage().'&func=security_be_user_deactivate_log';
$logFile = \FriendsOfRedaxo\Security\BackendUserLog::logFile();

switch ($func) {
    case 'security_be_user_activate_log':
        echo \rex_view::success($addon->i18n('security_be_user_log_activated'));
        BackendUserLog::activate();
        break;
    case 'security_be_user_deactivate_log':
        echo \rex_view::success($addon->i18n('security_be_user_log_deactivated'));
        BackendUserLog::deactivate();
        break;
    case 'security_be_user_delete_log':
        if (BackendUserLog::delete()) {
            echo \rex_view::success($addon->i18n('syslog_deleted'));
        } else {
            echo \rex_view::error($addon->i18n('syslog_delete_error'));
        }
}

if (!BackendUserLog::isActive()) {
    echo \rex_view::warning(\rex_i18n::rawMsg('security_be_user_log_warning_logisinactive', $activationLink));
} else {
    echo \rex_view::warning(\rex_i18n::rawMsg('security_be_user_log_warning_logisactive', $deactivationLink));
}

$content = '
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>' . \rex_i18n::msg('security_be_user_log_time') . '</th>
                        <th>' . \rex_i18n::msg('security_be_user_log_ip') . '</th>
                        <th>' . \rex_i18n::msg('security_be_user_log_user_id') . '</th>
                        <th>' . \rex_i18n::msg('security_be_user_log_impersonator_user_id') . '</th>
                        <th>' . \rex_i18n::msg('security_be_user_log_page') . '</th>
                        <th>' . \rex_i18n::msg('security_be_user_log_type') . '</th>
                        <th>' . \rex_i18n::msg('security_be_user_log_params') . '</th>
                    </tr>
                </thead>
                <tbody>';

$file = new \rex_log_file($logFile);
foreach (new \LimitIterator($file, 0, 30) as $entry) {
    $data = $entry->getData();
    $class = 'ERROR' == trim($data[0]) ? 'rex-state-error' : 'rex-mailer-log-ok';
    $content .= '
                <tr class="'.$class.'">
                  <td data-title="' . \rex_i18n::msg('phpmailer_log_date') . '" class="rex-table-tabular-nums">' . \rex_formatter::intlDateTime($entry->getTimestamp(), [\IntlDateFormatter::SHORT, \IntlDateFormatter::MEDIUM]) . '</td>
                  <td data-title="' . \rex_i18n::msg('security_be_user_log_user_ip') . '">' . rex_escape($data[0]) . '</td>
                  <td data-title="' . \rex_i18n::msg('security_be_user_log_user_id') . '">' . rex_escape($data[1]) . '</td>
                  <td data-title="' . \rex_i18n::msg('security_be_user_log_impersonator_user_id') . '">' . rex_escape($data[2]) . '</td>
                  <td data-title="' . \rex_i18n::msg('security_be_user_log_page') . '">' . rex_escape($data[3]) . '</td>
                  <td data-title="' . \rex_i18n::msg('security_be_user_log_type') . '">' . rex_escape($data[4]) . '</td>
                  <td data-title="' . \rex_i18n::msg('security_be_user_log_params') . '">' . rex_escape((string) ($data[5] ?? '')) . '</td>
                </tr>';
}

$content .= '
                </tbody>
            </table>';

$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-delete" type="submit" name="del_btn" data-confirm="' . \rex_i18n::msg('security_be_user_delete_log_msg') . '">' . \rex_i18n::msg('syslog_delete') . '</button>';
$formElements[] = $n;

$fragment = new \rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new \rex_fragment();
$fragment->setVar('title', \rex_i18n::msg('security_be_user_log_title', $logFile), false);
$fragment->setVar('content', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');
$content = '
    <form action="' . \rex_url::currentBackendPage() . '" method="post">
        <input type="hidden" name="func" value="security_be_user_delete_log" />
        ' . $content . '
    </form>';

echo $content;
