<?php
/**
 * Cocomarshmallow Books theme bootstrap.
 */

defined('ABSPATH') || exit;

define('CM_VERSION', wp_get_theme()->get('Version'));

require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/setup.php';
require_once __DIR__ . '/inc/post-types.php';
require_once __DIR__ . '/inc/acf-fields.php';
require_once __DIR__ . '/inc/contact-form.php';
require_once __DIR__ . '/inc/woocommerce.php';
