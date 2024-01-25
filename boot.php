<?php


rex_securit_health::checkRequest();
rex_securit_header::init();

rex_extension::register('PACKAGES_INCLUDED', static function ($params): void {
    rex_securit_error_notification::init();
    rex_securit_fe_access::init();
    rex_securit_be_user_log::init();
    rex_securit_ip_access::init();
}, rex_extension::EARLY);
