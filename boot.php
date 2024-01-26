<?php

namespace FriendsOfRedaxo\Securit;

Health::checkRequest();
Header::init();

\rex_extension::register('PACKAGES_INCLUDED', static function ($params): void {
    ErrorNotification::init();
    FrontendAccess::init();
    BackendUserLog::init();
    IPAccess::init();
}, \rex_extension::EARLY);
