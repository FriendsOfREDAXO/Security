<?php

/**
 * @var rex_addon $this
 * @psalm-scope-this rex_addon
 */

$addon = rex_addon::get('securit');
$func = rex_request('func', 'string');
$activationLink = rex_url::currentBackendPage().'&func=securit_be_user_activate_log';
$deactivationLink = rex_url::currentBackendPage().'&func=securit_be_user_deactivate_log';
$logFile = rex_securit_be_user_log::logFile();

switch ($func) {
    case 'securit_be_user_activate_log':
        echo rex_view::success($addon->i18n('securit_be_user_log_activated'));
        rex_securit_be_user_log::activate();
        break;
    case 'securit_be_user_deactivate_log':
        echo rex_view::success($addon->i18n('securit_be_user_log_deactivated'));
        rex_securit_be_user_log::deactivate();
        break;
    case 'securit_be_user_delete_log':
        if (rex_securit_be_user_log::delete()) {
            echo rex_view::success($addon->i18n('syslog_deleted'));
        } else {
            echo rex_view::error($addon->i18n('syslog_delete_error'));
        }
}

if (!rex_securit_be_user_log::isActive()) {
    echo rex_view::warning(rex_i18n::rawMsg('securit_be_user_log_warning_logisinactive', $activationLink));
} else {
    echo rex_view::warning(rex_i18n::rawMsg('securit_be_user_log_warning_logisactive', $deactivationLink));
}

$content = '
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>' . rex_i18n::msg('securit_be_user_log_time') . '</th>
                        <th>' . rex_i18n::msg('securit_be_user_log_ip') . '</th>
                        <th>' . rex_i18n::msg('securit_be_user_log_user_id') . '</th>
                        <th>' . rex_i18n::msg('securit_be_user_log_impersonator_user_id') . '</th>
                        <th>' . rex_i18n::msg('securit_be_user_log_page') . '</th>
                        <th>' . rex_i18n::msg('securit_be_user_log_type') . '</th>
                        <th>' . rex_i18n::msg('securit_be_user_log_params') . '</th>
                    </tr>
                </thead>
                <tbody>';

$file = new rex_log_file($logFile);
foreach (new LimitIterator($file, 0, 30) as $entry) {
    $data = $entry->getData();
    $class = 'ERROR' == trim($data[0]) ? 'rex-state-error' : 'rex-mailer-log-ok';
    $content .= '
                <tr class="'.$class.'">
                  <td data-title="' . rex_i18n::msg('phpmailer_log_date') . '" class="rex-table-tabular-nums">' . rex_formatter::intlDateTime($entry->getTimestamp(), [IntlDateFormatter::SHORT, IntlDateFormatter::MEDIUM]) . '</td>
                  <td data-title="' . rex_i18n::msg('securit_be_user_log_user_ip') . '">' . rex_escape($data[0]) . '</td>
                  <td data-title="' . rex_i18n::msg('securit_be_user_log_user_id') . '">' . rex_escape($data[1]) . '</td>
                  <td data-title="' . rex_i18n::msg('securit_be_user_log_impersonator_user_id') . '">' . rex_escape($data[2]) . '</td>
                  <td data-title="' . rex_i18n::msg('securit_be_user_log_page') . '">' . rex_escape($data[3]) . '</td>
                  <td data-title="' . rex_i18n::msg('securit_be_user_log_type') . '">' . rex_escape($data[4]) . '</td>
                  <td data-title="' . rex_i18n::msg('securit_be_user_log_params') . '">' . rex_escape((string) ($data[5] ?? '')) . '</td>
                </tr>';
}

$content .= '
                </tbody>
            </table>';

$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-delete" type="submit" name="del_btn" data-confirm="' . rex_i18n::msg('securit_be_user_delete_log_msg') . '">' . rex_i18n::msg('syslog_delete') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('securit_be_user_log_title', $logFile), false);
$fragment->setVar('content', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');
$content = '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        <input type="hidden" name="func" value="securit_be_user_delete_log" />
        ' . $content . '
    </form>';

echo $content;
