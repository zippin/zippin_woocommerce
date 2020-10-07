<?php
/*
 * Plugin Name: Envíos con Zippin para Woocommerce
 * Plugin URI: https://zippin-plugins.s3.amazonaws.com/woocommerce/zippin_woocommerce.zip
 * Description: Integra WooCommerce con Zippin para realizar envíos con múltiples transportes a todo el país.
 * Version: 1.6
 * Author: Zippin
 * Author URI: https://www.zippin.com.ar/
 * Requires PHP: 7
 * License: GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * WC requires at least: 4.0.0
 * WC tested up to: 4.5.2
 * Text Domain: zippin_woocommerce
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('ZIPPIN_LOGGER_CONTEXT', serialize(array('source' => 'zippin')));
define('ZIPPIN_APIKEY', '');
define('ZIPPIN_SECRETKEY', '');

register_activation_hook(__FILE__, 'Zippin\Zippin\Utils\activate_plugin');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'Zippin\Zippin\Utils\add_plugin_column_links');
add_filter('plugin_row_meta', 'Zippin\Zippin\Utils\add_plugin_description_links', 10, 4);

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