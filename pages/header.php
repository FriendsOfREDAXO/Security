<?php

/**
 * @var rex_addon $this
 * @psalm-scope-this rex_addon
 */

echo rex_view::info($this->i18n('securit_header_htaccess'));

echo '<pre>';
echo rex_escape(rex_securit_header::getHeaderAsHtaccess());
echo '</pre>';
