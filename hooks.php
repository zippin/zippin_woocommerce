<?php

if (!defined('ABSPATH')) {
	exit;// Exit if accessed directly.
}

// --- Settings
add_action('admin_init', 'Zippin\Zippin\Settings\init_settings');
add_action('admin_menu', 'Zippin\Zippin\Settings\create_menu_option');
add_action('admin_enqueue_scripts', 'Zippin\Zippin\Settings\add_assets_files');

// --- Method
add_action('woocommerce_shipping_init', 'Zippin\Zippin\zippin_init');
add_filter('woocommerce_shipping_methods', 'Zippin\Zippin\Utils\add_method');

// --- Checkout
add_action('woocommerce_checkout_update_order_meta', 'Zippin\Zippin\Utils\update_order_meta');
add_filter('woocommerce_cart_shipping_method_full_label', 'Zippin\Zippin\Utils\zippin_add_free_shipping_label', 10, 2);
add_filter('woocommerce_checkout_update_order_review', 'Zippin\Zippin\Utils\clear_cache');

// --- Orders
add_action('woocommerce_order_status_changed', 'Zippin\Zippin\Utils\process_order_status', 10, 3);

add_action('add_meta_boxes', 'Zippin\Zippin\Utils\add_order_side_box');
add_filter('woocommerce_admin_order_actions', 'Zippin\Zippin\Utils\add_action_button', 10, 2);
add_action('admin_enqueue_scripts', 'Zippin\Zippin\Utils\add_button_css_file');

// --- Tracking shortcode
add_shortcode('zippin_tracking', 'Zippin\Zippin\Utils\create_shortcode');

// --- Webhook handler
add_action('woocommerce_api_zippin', 'Zippin\Zippin\Utils\handle_webhook');