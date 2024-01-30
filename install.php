<?php
$config_file = rex_path::coreData('config.yml');
$data = rex_file::getConfig($config_file);
if ($data && !in_array('securit', $data['setup_addons'], true)) {
    $data['setup_addons'][] = 'securit';
    rex_file::putConfig($config_file, $data);
}

rex_yform_manager_table::deleteCache();

$content = rex_file::get(rex_path::addon('securit', 'install/yform/yform_manager_tableset_export_tables_rex_ysecure_ip_access.json'));
// @phpstan-ignore-next-line
if ($content && '' != $content) {
    rex_yform_manager_table_api::importTablesets($content);
}

rex_delete_cache();
rex_yform_manager_table::deleteCache();
