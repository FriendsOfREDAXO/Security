<?php

rex_yform_manager_table::deleteCache();

$content = rex_file::get(rex_path::addon('security', 'install/yform/yform_manager_tableset_export_tables_rex_security_ip_access.json'));
// @phpstan-ignore-next-line
if ($content && '' != $content) {
    rex_yform_manager_table_api::importTablesets($content);
}

rex_delete_cache();
rex_yform_manager_table::deleteCache();
