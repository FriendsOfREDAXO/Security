<?php
$config_file = rex_path::coreData('config.yml');
$data = rex_file::getConfig($config_file);
if ($data && in_array('securit', $data['setup_addons'], true)) {
    $data['setup_addons'] = array_filter($data['setup_addons'], fn($e) => $e !== 'securit');
    rex_file::putConfig($config_file, $data);
}
