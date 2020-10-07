<?php

namespace Zippin\Zippin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Helper
{

    private $order, $logger;

    public function __construct($order = '')
    {
        $this->order = $order;
        $this->logger = wc_get_logger();
    }

    public function get_comments()
    {
        if (!$this->order) {
            return false;
        }
        return $this->order->get_customer_note();
    }

    public function get_customer()
    {
        if (!$this->order) {
            return false;
        }
        return array(
            'name' => ($this->order->has_shipping_address() ? $this->order->get_shipping_first_name() : $this->order->get_billing_first_name()),
            'last_name' => ($this->order->has_shipping_address() ? $this->order->get_shipping_last_name() : $this->order->get_billing_last_name()),
            'email' => $this->order->get_billing_email(),
            'phone' => $this->order->get_billing_phone()
        );
    }


    public static function get_state_name($state_id = '')
    {
        $states = [
            'C' => 'Capital Federal',
            'B' => 'Buenos Aires',
            'K' => 'Catamarca',
            'H' => 'Chaco',
            'U' => 'Chubut',
            'X' => 'Cordoba',
            'W' => 'Corrientes',
            'E' => 'Entre Rios',
            'P' => 'Formosa',
            'Y' => 'Jujuy',
            'L' => 'La Pampa',
            'F' => 'La Rioja',
            'M' => 'Mendoza',
            'N' => 'Misiones',
            'Q' => 'Neuquen',
            'R' => 'Rio Negro',
            'A' => 'Salta',
            'J' => 'San Juan',
            'D' => 'San Luis',
            'Z' => 'Santa Cruz',
            'S' => 'Santa Fe',
            'G' => 'Santiago del Estero',
            'V' => 'Tierra del Fuego',
            'T' => 'Tucuman',
        ];

        if (isset($states[$state_id])) {
            return $states[$state_id];
        }
        
        return null;
    }

    public static function get_shipping_zone_regions()
    {
        $regions = array();
        $regions[] = array('code' => 'AR:C', 'type' => 'state');
        $regions[] = array('code' => 'AR:B', 'type' => 'state');
        $regions[] = array('code' => 'AR:K', 'type' => 'state');
        $regions[] = array('code' => 'AR:H', 'type' => 'state');
        $regions[] = array('code' => 'AR:U', 'type' => 'state');
        $regions[] = array('code' => 'AR:X', 'type' => 'state');
        $regions[] = array('code' => 'AR:W', 'type' => 'state');
        $regions[] = array('code' => 'AR:E', 'type' => 'state');
        $regions[] = array('code' => 'AR:P', 'type' => 'state');
        $regions[] = array('code' => 'AR:Y', 'type' => 'state');
        $regions[] = array('code' => 'AR:L', 'type' => 'state');
        $regions[] = array('code' => 'AR:F', 'type' => 'state');
        $regions[] = array('code' => 'AR:M', 'type' => 'state');
        $regions[] = array('code' => 'AR:N', 'type' => 'state');
        $regions[] = array('code' => 'AR:Q', 'type' => 'state');
        $regions[] = array('code' => 'AR:R', 'type' => 'state');
        $regions[] = array('code' => 'AR:A', 'type' => 'state');
        $regions[] = array('code' => 'AR:J', 'type' => 'state');
        $regions[] = array('code' => 'AR:D', 'type' => 'state');
        $regions[] = array('code' => 'AR:Z', 'type' => 'state');
        $regions[] = array('code' => 'AR:S', 'type' => 'state');
        $regions[] = array('code' => 'AR:G', 'type' => 'state');
        $regions[] = array('code' => 'AR:V', 'type' => 'state');
        $regions[] = array('code' => 'AR:T', 'type' => 'state');
        return $regions;
    }

    public function get_street()
    {
        if (!$this->order) {
            return false;
        }
        if ($this->order->has_shipping_address()) {
            $address = $this->order->get_shipping_address_1();
        } else {
            $address = $this->order->get_billing_address_1();
        }

        $address_array = explode(" ", $address);
        $address = '';
        foreach ($address_array as $key => $value_of_array) {
            if ($key === 0) {
                $address .= $value_of_array;
            } else {
                if (is_numeric($value_of_array)) {
                    break;
                }
                $address .= ' ' . $value_of_array;
            }
        }
        return $address;
    }

    public function get_province_id()
    {
        if (!$this->order) {
            return false;
        }
        if ($this->order->has_shipping_address()) {
            $province = $this->order->get_shipping_state();
        } else {
            $province = $this->order->get_billing_state();
        }
        return $province;
    }

    public function get_postal_code()
    {
        if (!$this->order) {
            return false;
        }
        if ($this->order->has_shipping_address()) {
            return $this->order->get_shipping_postcode();
        }
        return $this->order->get_billing_postcode();
    }

    private function get_packages_from_products($products)
    {
        $products['shipping_info']['total_weight'] = 0;
        $products['shipping_info']['total_volume'] = 0;
        $products['items'] = array();
        $products['packages'] = array();

        $skus = array();

        foreach ($products['products'] as $index => $product) {
            // $product is https://docs.woocommerce.com/wc-apidocs/class-WC_Product.html
            $sku = $product['sku'];
            if (empty($sku)) {
                $sku = 'wc'.$product['id'];
            }

            $products['shipping_info']['total_weight'] += $product['weight'];
            $products['shipping_info']['total_volume'] += $product['height'] * $product['width'] * $product['length'];
            $skus[] = $sku;

            $products['items'][] = array(
                'weight' => intval(ceil($product['weight'])),
                'height' => intval(ceil($product['height'])),
                'width' => intval(ceil($product['width'])),
                'length' => intval(ceil($product['length'])),
                'sku' => substr($sku,0,60),
            );

            // One package per unit of product sold
            if (get_option('zippin_packaging_mode') != 'grouped') {
                $products['packages'][] = array(
                    'classification_id' => 1,
                    'weight' => intval(ceil($product['weight'])),
                    'height' => intval(ceil($product['height'])),
                    'width' => intval(ceil($product['width'])),
                    'length' => intval(ceil($product['length'])),
                    'description_1' => substr($sku,0,60),
                    'description_2' => substr($product['name'],0,60)
                );
            }
        }

        // One package grouping all products
        if (get_option('zippin_packaging_mode') == 'grouped') {
            $side = intval(ceil(pow($products['shipping_info']['total_volume'],1/3)));
            $products['packages'][] = array(
                'classification_id' => 1,
                'weight' => intval(ceil($products['shipping_info']['total_weight'])),
                'height' => $side,
                'width' => $side,
                'length' => $side,
                'description_1' => substr(implode('_',$skus),0,60),
            );
        }

        return $products;

    }

    private function get_product_dimensions($product_id)
    {
        $product = wc_get_product($product_id);
        if (!$product) {
            return false;
        }
        if (empty($product->get_height()) || empty($product->get_length()) || empty($product->get_width()) || !$product->has_weight()) {
            return false;
        }
        $new_product = array(
            'id' => $product_id,
            'name' => $product->get_name(),
            'sku' => $product->get_sku(),
            'height' => ceil($product->get_height() ? wc_get_dimension($product->get_height(), 'cm') : '0'),
            'width' => ceil($product->get_width() ? wc_get_dimension($product->get_width(), 'cm') : '0'),
            'length' => ceil($product->get_length() ? wc_get_dimension($product->get_length(), 'cm') : '0'),
            'weight' => ceil($product->has_weight() ? wc_get_weight($product->get_weight(), 'kg')*1000 : '0'),
        );
        return $new_product;
    }

    public function get_items_from_cart()
    {
        $products = array(
            'products' => array(),
            'shipping_info' => array()
        );

        $items = WC()->cart->get_cart();

        foreach ($items as $item) {
            $product_id = $item['data']->get_id();
            $product = $this->get_product_dimensions($product_id);
            if (!$product) {
                $this->logger->error('Zippin Helper: Error obteniendo productos del carrito, producto con malas dimensiones - ID: ' . $product_id, unserialize(ZIPPIN_LOGGER_CONTEXT));
                return false;
            }
            for ($i = 0; $i < $item['quantity']; $i++) {
                array_push($products['products'], $product);
            }
        }

        $packages = $this->get_packages_from_products($products);

        if (!$packages) {
            $this->logger->error('Zippin Helper: Error obteniendo productos del carrito, productos con malas dimensiones/peso', unserialize(ZIPPIN_LOGGER_CONTEXT));
            return false;
        }

        return $packages;
    }

    public function get_items_from_order($order)
    {
        $products = array(
            'products' => array(),
            'shipping_info' => array()
        );
        $items = $order->get_items();
        foreach ($items as $item) {
            $product_id = $item->get_variation_id();
            if (!$product_id)
                $product_id = $item->get_product_id();
            $product = $this->get_product_dimensions($product_id);
            if (!$product) {
                $this->logger->error('Zippin Helper: Error obteniendo productos de la orden, producto con malas dimensiones - ID: ' . $product_id, unserialize(ZIPPIN_LOGGER_CONTEXT));
                return false;
            }
            for ($i = 0; $i < $item->get_quantity(); $i++) {
                array_push($products['products'], $product);
            }
        }

        $packages = $this->get_packages_from_products($products);
        if (!$packages) {
            $this->logger->error('Zippin Helper: Error obteniendo productos de la orden, productos con malas dimensiones/peso', unserialize(ZIPPIN_LOGGER_CONTEXT));
            return false;
        }

        return $packages;
    }

    public function get_street_number()
    {
        if (!$this->order) {
            return false;
        }
        if ($this->order->has_shipping_address()) {
            $address = $this->order->get_shipping_address_1();
        } else {
            $address = $this->order->get_billing_address_1();
        }

        $number = '';
        $address_array = array_reverse(explode(" ", $address));
        foreach ($address_array as $value_of_array) {
            if (is_numeric($value_of_array)) {
                $number = $value_of_array;
                break;
            }
        }

        if (!$number) {
            if ($this->order->has_shipping_address()) {
                $address = $this->order->get_shipping_address_2();
            } else {
                $address = $this->order->get_billing_address_2();
            }
            if (is_numeric($address)) {
                $number = $address;
            }
        }

        return $number;
    }



    public function get_destination_from_order($order)
    {
        $address = Helper::get_address($order);

        if ($order->has_shipping_address()) {
            $destination = array(
                'name' => $order->get_shipping_first_name().' '.$order->get_shipping_last_name(),
                'document' => 11111111,
                'street' => $address['street'],
                'street_number' => $address['number'],
                'street_extras' => $address['floor'].' '.$address['apartment'],
                'city' => $order->get_shipping_city(),
                'state' => Helper::get_state_name($order->get_shipping_state()),
                'zipcode' => $order->get_shipping_postcode(),
                'phone' => $order->get_billing_phone(),
                'email' => $order->get_billing_email(),
            );

        } else {
            $destination = array(
                'name' => $order->get_billing_first_name().' '.$order->get_billing_last_name(),
                'document' => 11111111,
                'street' => $address['street'],
                'street_number' => $address['number'],
                'street_extras' => $address['floor'].' '.$address['apartment'],
                'city' => $order->get_billing_city(),
                'state' => Helper::get_state_name($order->get_billing_state()),
                'zipcode' => $order->get_billing_postcode(),
                'phone' => $order->get_billing_phone(),
                'email' => $order->get_billing_email(),
            );

        }

        return $destination;

    }

    public static function get_address($order)
    {
        if ($order->get_shipping_address_1()) {
            $shipping_line_1 = $order->get_shipping_address_1();
            $shipping_line_2 = $order->get_shipping_address_2();
        } else {
            $shipping_line_1 = $order->get_billing_address_1();
            $shipping_line_2 = $order->get_billing_address_2();
        }

        $street_name = $street_number = $floor = $apartment = "";

        if (!empty($shipping_line_2)) {
            //there is something in the second line. Let's find out what
            $fl_apt_array = self::get_floor_and_apt($shipping_line_2);
            $floor = $fl_apt_array[0];
            $apartment = $fl_apt_array[1];
        }
    
        //Now let's work on the first line
        preg_match('/(^\d*[\D]*)(\d+)(.*)/i', $shipping_line_1, $res);
        $line1 = $res;

        if ((isset($line1[1]) && !empty($line1[1]) && $line1[1] !== " ") && !empty($line1)) {
            //everything's fine. Go ahead
            if (empty($line1[3]) || $line1[3] === " ") {
                //the user just wrote the street name and number, as he should
                $street_name = trim($line1[1]);
                $street_number = trim($line1[2]);
                unset($line1[3]);
            } else {
                //there is something extra in the first line. We'll save it in case it's important
                $street_name = trim($line1[1]);
                $street_number = trim($line1[2]);
                $shipping_line_2 = trim($line1[3]);

                if (empty($floor) && empty($apartment)) {
                    //if we don't have either the floor or the apartment, they should be in our new $shipping_line_2
                    $fl_apt_array = self::get_floor_and_apt($shipping_line_2);
                    $floor = $fl_apt_array[0];
                    $apartment = $fl_apt_array[1];

                } elseif (empty($apartment)) {
                    //we've already have the floor. We just need the apartment
                    $apartment = trim($line1[3]);
                } else {
                    //we've got the apartment, so let's just save the floor
                    $floor = trim($line1[3]);
                }
            }
        } else {
            //the user didn't write the street number. Maybe it's in the second line
            //given the fact that there is no street number in the fist line, we'll asume it's just the street name
            $street_name = $shipping_line_1;

            if (!empty($floor) && !empty($apartment)) {
                //we are in a pickle. It's a risky move, but we'll move everything one step up
                $street_number = $floor;
                $floor = $apartment;
                $apartment = "";
            } elseif (!empty($floor) && empty($apartment)) {
                //it seems the user wrote only the street number in the second line. Let's move it up
                $street_number = $floor;
                $floor = "";
            } elseif (empty($floor) && !empty($apartment)) {
                //I don't think there's a chance of this even happening, but let's write it to be safe
                $street_number = $apartment;
                $apartment = "";
            }
        }

        if (!preg_match('/^ ?\d+ ?$/', $street_number, $res)) {
            //the street number it's not an actual number. We'll move it to street
            $street_name .= " " . $street_number;
            $street_number = "";
        }

        return array('street' => $street_name, 'number' => $street_number, 'floor' => $floor, 'apartment' => $apartment);
    }

    public static function get_floor_and_apt($fl_apt)
    {
        $street_name = $street_number = $floor = $apartment = "";

        //firts we'll asume the user did things right. Something like "piso 24, depto. 5h"
        preg_match('/(piso|p|p.) ?(\w+),? ?(departamento|depto|dept|dpto|dpt|dpt.º|depto.|dept.|dpto.|dpt.|apartamento|apto|apt|apto.|apt.) ?(\w+)/i', $fl_apt, $res);
        $line2 = $res;

        if (!empty($line2)) {
            //everything was written great. Now lets grab what matters
            $floor = trim($line2[2]);
            $apartment = trim($line2[4]);
        } else {
            //maybe the user wrote something like "depto. 5, piso 24". Let's try that
            preg_match('/(departamento|depto|dept|dpto|dpt|dpt.º|depto.|dept.|dpto.|dpt.|apartamento|apto|apt|apto.|apt.) ?(\w+),? ?(piso|p|p.) ?(\w+)/i', $fl_apt, $res);
            $line2 = $res;
        }

        if (!empty($line2) && empty($apartment) && empty($floor)) {
            //apparently, that was the case. Guess some people just like to make things difficult
            $floor = trim($line2[4]);
            $apartment = trim($line2[2]);
        } else {
            //something is wrong. Let's be more specific. First we'll try with only the floor
            preg_match('/^(piso|p|p.) ?(\w+)$/i', $fl_apt, $res);
            $line2 = $res;
        }

        if (!empty($line2) && empty($floor)) {
            //now we've got it! The user just wrote the floor number. Now lets grab what matters
            $floor = trim($line2[2]);
        } else {
            //still no. Now we'll try with the apartment
            preg_match('/^(departamento|depto|dept|dpto|dpt|dpt.º|depto.|dept.|dpto.|dpt.|apartamento|apto|apt|apto.|apt.) ?(\w+)$/i', $fl_apt, $res);
            $line2 = $res;
        }

        if (!empty($line2) && empty($apartment) && empty($floor)) {
            //success! The user just wrote the apartment information. No clue why, but who am I to judge
            $apartment = trim($line2[2]);
        } else {
            //ok, weird. Now we'll try a more generic approach just in case the user missplelled something
            preg_match('/(\d+),? [a-zA-Z.,!*]* ?([a-zA-Z0-9 ]+)/i', $fl_apt, $res);
            $line2 = $res;
        }

        if (!empty($line2) && empty($floor) && empty($apartment)) {
            //finally! The user just missplelled something. It happens to the best of us
            $floor = trim($line2[1]);
            $apartment = trim($line2[2]);
        } else {
            //last try! This one is in case the user wrote the floor and apartment together ("12C")
            preg_match('/(\d+)(\D*)/i', $fl_apt, $res);
            $line2 = $res;
        }

        if (!empty($line2) && empty($floor) && empty($apartment)) {
            //ok, we've got it. I was starting to panic
            $floor = trim($line2[1]);
            $apartment = trim($line2[2]);
        } elseif (empty($floor) && empty($apartment)) {
            //I give up. I can't make sense of it. We'll save it in case it's something useful 
            $floor = $fl_apt;
        }

        return array($floor, $apartment);
    }
}