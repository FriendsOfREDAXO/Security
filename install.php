<?php
$addon = rex_addon::get('maintenance');

// Write securit to setup addOns system config
$config_file = rex_path::coreData('config.yml');
$config = rex_file::get($config_file);
if ($config !== null) {
    $data = rex_string::yamlDecode($config);
    if (in_array("securit", $data['setup_addons'], true)) {
    } else {
        $data['setup_addons'][] = 'securit';
        rex_file::put($config_file, rex_string::yamlEncode($data, 3));
    }
}

rex_yform_manager_table::deleteCache();

$content = rex_file::get(rex_path::addon('securit', 'install/yform/yform_manager_tableset_export_tables_rex_ysecure_ip_access.json'));
// @phpstan-ignore-next-line
if ($content && '' != $content) {
    rex_yform_manager_table_api::importTablesets($content);
}

rex_delete_cache();
rex_yform_manager_table::deleteCache();
