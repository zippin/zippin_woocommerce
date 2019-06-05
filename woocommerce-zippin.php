<?php

/*
Plugin Name: Envíos con Zippin para Woocommerce
Plugin URI: https://www.zippin.com.ar
Description: Integra WooCommerce con Zippin para realizar envíos a todo el país.
Version: 1.0
Author: Zippin
Requires PHP: 7
Author URI: https://www.zippin.com.ar
License: GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('ZIPPIN_LOGGER_CONTEXT', serialize(array('source' => 'zippin')));
define('ZIPPIN_APIKEY', '');
define('ZIPPIN_SECRETKEY', '');

register_activation_hook(__FILE__, 'Zippin\Zippin\Utils\create_page');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'Zippin\Zippin\Utils\create_settings_link');

require_once 'zippin-method.php';
require_once 'zippin-settings.php';
require_once 'zippin-connector.php';
require_once 'hooks.php';
require_once 'helper.php';
require_once 'utils.php';

add_filter('gettext', 'zippin_translate_words_array', 20, 3);
add_filter('ngettext', 'zippin_translate_words_array', 20, 3);
function zippin_translate_words_array($translation, $text, $domain)
{
    if ($text === 'Enter your address to view shipping options.') {
        $translation = 'Ingresá tu dirección para conocer los costos de envio';
    }
    return $translation;
}