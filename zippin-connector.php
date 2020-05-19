<?php

namespace Zippin\Zippin;

use DateTime;
use Zippin\Zippin\Helper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ZippinConnector
{
    private $api_key, $api_secret, $account_id;

    const VERSION = '1_2';

    public function __construct()
    {

        $this->api_key = get_option('zippin_api_key');
        $this->api_secret = get_option('zippin_api_secret');
        $this->account_id = get_option('zippin_account_id');
        $this->origin_id = get_option('zippin_origin_id');
        $this->logger = wc_get_logger();

    }

    public function get_api_key()
    {
        return $this->api_key;
    }

    public function get_api_secret()
    {
        return $this->api_secret;
    }

    public function get_account_id()
    {
        return $this->account_id;
    }

    public function get_origin_id()
    {
        return $this->origin_id;
    }



    public function create_shipment($order = null)
    {
        if (!$order) {
            return false;
        }

        $helper = new Helper($order);
        $shipment_info = unserialize($order->get_meta('zippin_shipping_info', true));
        if (!isset($shipment_info['logistic_type'])) {
            $shipment_info['logistic_type'] = null;
        }

        // Prepare packages
        $products = $helper->get_items_from_order($order);

        // Create destination object
        $destination = $helper->get_destination_from_order($order);

        $payload = array(
            'account_id' => $this->get_account_id(),
            'origin_id' => $this->get_origin_id(),
            'external_id' => 'W'.$order->get_id(),
            'source' => 'woocommerce_'.self::VERSION,   // Por favor dejar para poder dar mejor soporte.
            'declared_value' => round(floatval($order->get_total()),2),
            'packages' => $products['packages'],
            'destination' => $destination
        );

        if (!is_null($shipment_info['logistic_type'])) {
            $payload['logistic_type'] = $shipment_info['logistic_type'];
        } elseif (strval($this->origin_id) == '32') {
            $payload['logistic_type'] = 'fulfillment';
        }

        if (!is_null($shipment_info['carrier_id'])) {
            $payload['carrier_id'] = $shipment_info['carrier_id'];
        }

        if (!is_null($shipment_info['service_type'])) {
            $payload['service_type'] = $shipment_info['service_type'];
        } else {
            $payload['service_type'] = 'standard_delivery';
        }

        $response = $this->call_api('POST', '/shipments', $payload);

        if (is_wp_error($response)) {
            $this->logger->error('Zippin: WP Error al crear pedido: ' . $response->get_error_message(), unserialize(ZIPPIN_LOGGER_CONTEXT));
            return false;
        }

        if ($response['response']['code'] === 201) {
            $response = json_decode($response['body'], true);
            return $response;

        } else {
            $this->logger->error('Zippin: Crear envio - Errors: '.wc_print_r($response['body'], true), unserialize(ZIPPIN_LOGGER_CONTEXT));
            $this->logger->info('Zippin: Crear envio - Request: ' . wc_print_r(json_encode($payload), true), unserialize(ZIPPIN_LOGGER_CONTEXT));
            return false;
        }

    }


    public function get_origins()
    {
        $response = $this->call_api('GET', '/addresses', array('account_id' => $this->get_account_id()));

        if (is_wp_error($response)) {
            $this->logger->error('Zippin: WP Error al obtener direcciones de envio: ' . $response->get_error_message(), unserialize(ZIPPIN_LOGGER_CONTEXT));
            return false;
        }

        if ($response['response']['code'] === 200) {
            $response = json_decode($response['body'], true);
            $new_addresses = array();

            foreach ($response['data'] as $address) {
                $new_address = $address;
                $new_address['id'] = $address['id'];
                $new_address['name'] = $address['name'];
                $new_address['address'] = $address['street'] . ' ' . $address['street_number'];
                $new_addresses[] = $new_address;
            }
            return $new_addresses;

        } else {
            $this->logger->error('Zippin: Obtener Origenes (account '.$this->get_account_id().') - Errors: '.wc_print_r($response['body'], true), unserialize(ZIPPIN_LOGGER_CONTEXT));
            return false;
        }
    }

    public function get_account()
    {
        $response = $this->call_api('GET', '/accounts/'.$this->get_account_id());

        if (is_wp_error($response)) {
            $this->logger->error('Zippin: WP Error al obtener cuenta: ' . $response->get_error_message(), unserialize(ZIPPIN_LOGGER_CONTEXT));
            return false;
        }

        if ($response['response']['code'] === 200) {
            return json_decode($response['body'], true);

        } else {
            $this->logger->error('Zippin: Obtener cuenta ('.$this->get_account_id().') - Errors: '.wc_print_r($response['body'], true), unserialize(ZIPPIN_LOGGER_CONTEXT));
            return false;
        }
    }

    public function get_shipment($shipment_id)
    {
        $response = $this->call_api('GET', '/shipments/'.$shipment_id);

        if (is_wp_error($response)) {
            $this->logger->error('Zippin: WP Error al obtener shipment: ' . $response->get_error_message(), unserialize(ZIPPIN_LOGGER_CONTEXT));
            return false;
        }

        if ($response['response']['code'] === 200) {
            return json_decode($response['body'], true);

        } else {
            $this->logger->error('Zippin: Obtener Shipment ('.$shipment_id.') - Errors: '.wc_print_r($response['body'], true), unserialize(ZIPPIN_LOGGER_CONTEXT));
            return false;
        }
    }

    public function quote($destination, $packages, $declared_value = 0, $service_types = null)
    {
        $payload = array(
            'account_id' => $this->get_account_id(),
            'origin_id' => $this->get_origin_id(),
            'declared_value' => round(floatval($declared_value),2),
            'packages' => $packages,
            'destination' => $destination
        );

        $response = $this->call_api('POST', '/shipments/quote', $payload);

        if (is_wp_error($response)) {
            $this->logger->error('Zippin: WP Error al obtener cotizacion de envio: ' . $response->get_error_message(), unserialize(ZIPPIN_LOGGER_CONTEXT));
            return false;
        }

        if ($response['response']['code'] === 200) {
            $response = json_decode($response['body'], true);

            $quote_results = array();
            foreach ($response['all_results'] as $result) {

                if (is_array($service_types)) {
                    if (!in_array($result['service_type']['code'], $service_types)) {
                        // Don't add options from disabled service_types
                        continue;
                    }
                }

                $quote_result = array();
                $quote_result['service_name'] = $result['carrier']['name'].' - '.$result['service_type']['name'];
                $quote_result['shipping_time'] = $result['delivery_time']['max']*24;
                $quote_result['price'] = $result['amounts']['price_incl_tax'];
                $quote_result['code'] = $result['carrier']['id'].'|'.$result['service_type']['code'].'|'.$result['logistic_type'];
                $quote_results[] = $quote_result;
            }
            return $quote_results;

        } else {
            $this->logger->error('Zippin: Cotizar envio - Errors: '.wc_print_r($response, true), unserialize(ZIPPIN_LOGGER_CONTEXT));
            $this->logger->info('Zippin: Cotizar envio - Request: ' . wc_print_r(json_encode($payload), true), unserialize(ZIPPIN_LOGGER_CONTEXT));
            return false;
        }
    }


    public function call_api($method = '', $endpoint = '', $params = array(), $headers = array())
    {
        if ($method && $endpoint) {
            $headers['Content-Type'] = 'application/json';
            $headers['Accept'] = 'application/json';
            $headers['Authorization'] = 'Basic '.base64_encode($this->get_api_key().':'.$this->get_api_secret());

            $url = 'https://api.zippin.com.ar/v2' . $endpoint;
            $args = array(
                'headers' => $headers,
                'timeout' => 10
            );

            if ($method === 'GET') {
                $url .= '?' . http_build_query($params);
                $response = wp_remote_get($url, $args);

            } elseif ($method === 'POST') {
                $args['body'] = json_encode($params);
                $response = wp_remote_post($url, $args);

            } else {
                $args['body'] = json_encode($params);
                $args['method'] = $method;
                $response = wp_remote_request($url, $args);
            }


            if (is_wp_error($response)) {
                $this->logger->error('Wordpress error: ' . $response->get_error_message(), unserialize(ZIPPIN_LOGGER_CONTEXT));
                return false;
            }

            if ($response['response']['code'] != 200 && $response['response']['code'] != 201) {
                $this->logger->error('Error '.$response['response']['code'].' desde la api: '.print_r($response['body'],1), unserialize(ZIPPIN_LOGGER_CONTEXT));
                $this->logger->info('Request params: '.wc_print_r(json_encode($params), true), unserialize(ZIPPIN_LOGGER_CONTEXT));
                return false;
            } else {
            // Success
                return $response;
            }

        }
    }
}
