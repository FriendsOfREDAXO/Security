<?php
// remove securit from System-AddOns
$config_file = rex_path::coreData('config.yml');
$config = rex_file::get($config_file); 
if ($config !== null) {
    $data = rex_string::yamlDecode($config);
    if (in_array("securit", $data['setup_addons'],true)) {
      $data['system_addons'] =  array_filter($data['setup_addons'] , fn($e) => !in_array($e, ['securit'],true));
      rex_file::put($config_file, rex_string::yamlEncode($data, 3));
    } 
}
