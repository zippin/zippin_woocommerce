<?php

namespace Zippin\Zippin;

use WC_Shipping_Method;
use Zippin\Zippin\ZippinConnector;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function zippin_init()
{
    if (!class_exists('WC_Zippin')) {
        class WC_Zippin extends WC_Shipping_Method
        {

            private $logger;

            public function __construct($instance_id = 0)
            {
                $this->id = 'zippin';
                $this->method_title = 'Zippin';
                $this->method_description = 'Envíos con Zippin';
                $this->title = 'Envío con Zippin';
                $this->instance_id = absint($instance_id);
                $this->supports = array(
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal'
                );
                $this->logger = wc_get_logger();
                $this->init();
                add_action('woocommerce_update_options_shipping_zippin', array($this, 'process_admin_options'));
            }

            function init()
            {
                $this->form_fields = array();
                $this->instance_form_fields = array(
                    'service_types' => array(
                        'title' => __('Tipos de Servicio Habilitados', 'woocommerce'),
                        'description'=>'Selecciona los tipos de servicio que quieras ofrecer. Selecciona múltiples opciones manteniendo la tecla CTRL.',
                        'type' => 'multiselect',
                        'default' => array('standard_delivery','urgent_delivery'),
                        'options' => array(
                            'standard_delivery' => 'Entrega a domicilio estándar',
                            'urgent_delivery' => 'Entrega en el Día',
                        )
                    ),
                );
                /*
                $classes = WC()->shipping->get_shipping_classes();
                foreach ($classes as $class) {
                    $this->instance_form_fields['clase']['options'][$class->name] = $class->name;
                }
				*/
            }

            public function calculate_shipping($package = array())
            {
                $helper = new Helper();

                // Prepare packages
                $products = $this->get_products_from_cart();

                // Create destination object
                $billing_address = [
                    'city' => WC()->customer->get_billing_city(),
                    'state' => WC()->customer->get_billing_state(),
                    'zipcode' => WC()->customer->get_billing_postcode()
                ];

                $shipping_address = [
                    'city' => WC()->customer->get_shipping_city(),
                    'state' => WC()->customer->get_shipping_state(),
                    'zipcode' => WC()->customer->get_shipping_postcode()
                ];

                //$this->logger->info('Quote Log - billing: '.wc_print_r(json_encode($billing_address), true).' - shipping: '.wc_print_r(json_encode($shipping_address), true), unserialize(ZIPPIN_LOGGER_CONTEXT));

                if (!empty($shipping_address['city']) && !empty($shipping_address['state']) && !empty($shipping_address['zipcode'])) {
                    $destination = $shipping_address;
                } else {
                    $destination = $billing_address;
                }

                $destination['zipcode'] = filter_var($destination['zipcode'], FILTER_SANITIZE_NUMBER_INT);
                $destination['state'] = $helper->get_province_name($destination['state']);

                // Get declared value
                $declared_value = WC()->cart->get_subtotal();
                if (!empty($declared_value)) {
                    $declared_value = number_format($declared_value, 2, '.', '');
                }

                // Quote and get results
                $mix = get_option('zippin_options_mix');
                $connector = new ZippinConnector;
                $quote_results = $connector->quote($destination, [], $products['items'], $declared_value, $this->get_instance_option('service_types'), $mix);

                if (get_option('zippin_additional_charge'))	{
                    $additional_charge = get_option('zippin_additional_charge');
                } else {
                    $additional_charge = '0';
                }

                $use_free_shipping = false;
                if (get_option('zippin_free_shipping_threshold')) {
                    if (WC()->cart->get_subtotal() >= floatval(get_option('zippin_free_shipping_threshold'))) {
                        $use_free_shipping = true;
                    }
                }

                if ($quote_results) {
                    foreach ($quote_results as $result) {

                        if ($result['shipping_time'] > 48) {
                            $time = '(hasta '. ($result['shipping_time']/24).' días háb. desde el despacho)';
                        } elseif ($result['shipping_time'] == 24) {
                            $time = '(al día siguiente del despacho)';
                        } else {
                            $time = '(el día del despacho)';
                        }

                        if ($use_free_shipping) {
                            $cost = 0;
                        } elseif (!empty($additional_charge)){
                            $cost = (isset($result['price']) ? $result['price'] + ($result['price'] * $additional_charge / 100) : 0);
                        } else {
                            $cost = (isset($result['price']) ? $result['price'] : 0);
                        }

                        $rate = array(
                            'id' => 'zippin|' . (isset($result['code']) ? $result['code'] : ''),
                            'label' => $result['service_name'] . ' ' . $time,
                            'cost' => $cost,
                            'calc_tax' => 'per_order'
                        );

                        $this->add_rate($rate);

                    }
                }
            }

            public function get_products_from_cart()
            {
                $helper = new Helper();
                $products = $helper->get_items_from_cart();
                return $products;
            }

        }
    }
}
