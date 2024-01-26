<?php

namespace FriendsOfRedaxo\Securit;

/**
 * @var \rex_addon $this
 */

echo \rex_view::info($this->i18n('securit_header_htaccess'));

echo '<pre>';
echo rex_escape(Header::getHeaderAsHtaccess());
echo '</pre>';
