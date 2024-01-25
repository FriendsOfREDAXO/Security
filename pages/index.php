<?php

/**
 * @var rex_addon $this
 * @psalm-scope-this rex_addon
 */

echo rex_view::title($this->i18n('securit_title'));

rex_be_controller::includeCurrentPageSubPath();
