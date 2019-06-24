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
                $destination = array();
                $destination['zipcode'] = WC()->customer->get_shipping_postcode();
                if (empty($zipcode)) {
                    $destination['zipcode'] = WC()->customer->get_billing_postcode();
                }
                $destination['zipcode'] = filter_var($destination['zipcode'], FILTER_SANITIZE_NUMBER_INT);
                $destination['state'] = WC()->customer->get_shipping_state();
                if (empty($destination['state'])) {
                    $destination['state'] = WC()->customer->get_billing_state();
                }
                $destination['state'] = $helper->get_province_name($destination['state']);

                $destination['city'] = WC()->customer->get_shipping_city();
                if (empty($destination['city'])) {
                    $destination['city'] = WC()->customer->get_billing_city();
                }

                // Get declared value
                $declared_value = WC()->cart->get_subtotal();
                if (!empty($declared_value)) {
                    $declared_value = number_format($declared_value, 2, '.', '');
                }

                // Quote and get results
                $connector = new ZippinConnector;
                $quote_results = $connector->quote($destination, $products['packages'], $declared_value, $this->get_instance_option('service_types'));
                if ($quote_results) {
                    foreach ($quote_results as $result) {

                        if ($result['shipping_time']>48) {
                            $time = ($result['shipping_time']/24).' días háb.';
                        } else {
                            $time = $result['shipping_time']. 'hs.';
                        }

                        $rate = array(
                            'id' => 'zippin|' . (isset($result['code']) ? $result['code'] : ''),
                            'label' => $result['service_name'] . ' (hasta ' . $time . ')',
                            'cost' => (isset($result['price']) ? $result['price'] : 0),
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
