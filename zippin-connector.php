<?php

namespace Zippin\Zippin;

use DateTime;
use Zippin\Zippin\Helper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ZippinConnector
{
    private $api_key, $api_secret, $account_id, $origin_id, $logger;
    public $credentials_checked;
    protected $last_error;

    const VERSION = 'official_1_6';

    public function __construct()
    {

        $this->api_key = get_option('zippin_api_key');
        $this->api_secret = get_option('zippin_api_secret');
        $this->account_id = get_option('zippin_account_id');
        $this->origin_id = get_option('zippin_origin_id');
        $this->logger = wc_get_logger();
        $this->credentials_checked = get_option('zippin_credentials_check');

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
            $this->last_error = 'Invalid order.';
            $this->logger->warning('Invalid order', unserialize(ZIPPIN_LOGGER_CONTEXT));
            return false;
        }

        if (!$this->credentials_checked) {
            $this->last_error = 'Invalid credentials.';
            $this->logger->warning('Invalid credentials', unserialize(ZIPPIN_LOGGER_CONTEXT));
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
            'source' => 'wc_'.self::VERSION,   // Por favor dejar para poder dar mejor soporte.
            'declared_value' => round(floatval($order->get_total()),2),
            //'packages' => $products['packages'],
            'items' => $products['items'],
            'destination' => $destination
        );

        if (!$payload['items'] || !$payload['destination']) {
            $this->logger->warning('No items or destination', unserialize(ZIPPIN_LOGGER_CONTEXT));
            return false;
        }

        if (!is_null($shipment_info['logistic_type'])) {
            $payload['logistic_type'] = $shipment_info['logistic_type'];

        } elseif (in_array(strval($this->origin_id), ['32', '2014'])) {
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

        if ($payload['service_type'] == 'pickup_point' && isset($shipment_info['point_id'])) {
            $payload['destination']['point_id'] = $shipment_info['point_id'];
            unset($payload['destination']['street'], $payload['destination']['street_number'], $payload['destination']['street_extras']);
            unset($payload['destination']['city'], $payload['destination']['state'], $payload['destination']['zipcode']);
        }

        if ($response = $this->call_api('POST', '/shipments', $payload)) {
            return json_decode($response['body'], true);
        }

        return false;

    }


    public function get_origins()
    {
        if (!$this->credentials_checked) {
            return false;
        }

        if ($response = $this->call_api('GET', '/addresses', array('account_id' => $this->get_account_id()))) {
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
            return false;
        }
    }

    public function get_account()
    {
        if ($response = $this->call_api('GET', '/accounts/'.$this->get_account_id())) {
            return json_decode($response['body'], true);

        } else {
            return false;
        }
    }

    public function get_shipment($shipment_id)
    {
        if (!$this->credentials_checked) {
            return false;
        }
        if ($response = $this->call_api('GET', '/shipments/'.$shipment_id)) {
            return json_decode($response['body'], true);

        } else {
            return false;
        }
    }

    public function quote($destination, $packages = [], $items = [], $declared_value = 0, $service_types = null, $mix = null)
    {
        if (!$this->credentials_checked) {
            return false;
        }

        if (strlen($destination['zipcode'])<4 || strlen($destination['city'])==0 || strlen($destination['state'])<4) {
            // Evitamos enviar un request que sabemos que va a fallar
            return false;
        }

        $payload = array(
            'account_id' => $this->get_account_id(),
            'origin_id' => $this->get_origin_id(),
            'declared_value' => round(floatval($declared_value),2),
            'destination' => $destination
        );

        if (count($items)) {
            $payload['items'] = $items;
            if (count($items)>500) {
                $this->logger->warning('Unable to quote more than 500 items - body: '.wc_print_r(json_encode($payload), true), unserialize(ZIPPIN_LOGGER_CONTEXT));
                return false;
            }

        } else {
            $payload['packages'] = $packages;
        }

        if ($response = $this->call_api('POST', '/shipments/quote', $payload)) {
            $response = json_decode($response['body'], true);
            $this->logger->info('Quote Log - body: '.wc_print_r(json_encode($payload), true).' - response: '.wc_print_r(json_encode($response), true), unserialize(ZIPPIN_LOGGER_CONTEXT));

            $quote_results = array();
            $service_type_counter = [];

            foreach ($response['all_results'] as $result) {

                if (is_array($service_types)) {
                    if (!in_array($result['service_type']['code'], $service_types)) {
                        // Don't add options from disabled service_types
                        continue;
                    }
                    if ($mix == 'first' && count($quote_results)> 0) {
                        continue;
                    }
                    if ($mix == 'first_by_service' && isset($service_type_counter[$result['service_type']['code']])) {
                        continue;
                    }
                }

                $quote_result = array();
                if ($result['service_type']['code'] == 'pickup_point') {
                    $quote_result['service_name'] = 'Retiro en '.$result['carrier']['name'];
                } else {
                    $quote_result['service_name'] = 'Entrega '.$result['carrier']['name']. ' a domicilio';
                }
                $quote_result['shipping_time'] = $result['delivery_time']['max']*24;
                $quote_result['price'] = $result['amounts']['price_incl_tax'];
                $quote_result['code'] = $result['carrier']['id'].'|'.$result['service_type']['code'].'|'.$result['logistic_type'];
                $quote_result['result'] = $result;
                $quote_results[] = $quote_result;

                if (!isset($service_type_counter[$result['service_type']['code']])) {
                    $service_type_counter[$result['service_type']['code']] = 1;
                } else {
                    $service_type_counter[$result['service_type']['code']]++;
                }

            }
            return $quote_results;

        } else {
            return false;
        }
    }


    public function call_api($method = '', $endpoint = '', $params = array(), $headers = array())
    {
        if ($method && $endpoint) {
            $headers['Content-Type'] = 'application/json';
            $headers['Accept'] = 'application/json';
            $headers['Authorization'] = 'Basic '.base64_encode($this->get_api_key().':'.$this->get_api_secret());
            $headers['x-show-pickup-points'] = 'true';

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
                // Request failed with local error
                $this->logger->error('Wordpress error: ' . $response->get_error_message(), unserialize(ZIPPIN_LOGGER_CONTEXT));
                return false;
            }

            if ($response['response']['code'] != 200 && $response['response']['code'] != 201) {
                if ($response['response']['code'] == 403) {
                    update_option('zippin_credentials_check',false);
                }
                // API Request failed
                $this->last_error = wc_print_r($response['body'], 1);
                $this->logger->error('Error '.$response['response']['code'].' calling '.$endpoint.' - Response: '.wc_print_r(json_encode($response), true).' with params: '.wc_print_r(json_encode($params), true), unserialize(ZIPPIN_LOGGER_CONTEXT));
                return false;

            } else {
                // Success
                return $response;
            }

        }
    }


    public function getLastError()
    {
        return $this->last_error;
    }
}
