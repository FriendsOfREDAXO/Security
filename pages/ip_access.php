<?php

namespace FriendsOfRedaxo\Security;

use rex_extension;
use rex_extension_point;
use rex_i18n;
use rex_path;
use rex_view;

$_REQUEST['table_name'] = 'rex_security_ip_access';

echo rex_view::info(rex_i18n::msg('security_ip_access_curremt', IPAccess::getIP()));

rex_extension::register(
    'YFORM_MANAGER_DATA_PAGE_HEADER',
    static function (rex_extension_point $ep) {
        if ($ep->getParam('yform')->table->getTableName() === $ep->getParam('table_name')) {
            return '';
        }

        return $ep->getSubject();
    },
    rex_extension::EARLY,
    ['table_name' => 'rex_security_ip_access'],
);

echo rex_view::content(rex_i18n::msg('security_ip_access_info'));

include rex_path::plugin('yform', 'manager', 'pages/data_edit.php');
