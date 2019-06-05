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

    public function get_state_center($province_id = '')
    {
        if (!$province_id) {
            return false;
        }
        switch ($province_id) {
            case 'A':
                $coords = array(-24.7959127, -65.5006682);
                break;
            case 'B':
            case 'C':
            default:
                $coords = array(-34.699918, -58.5811109);
                break;
            case 'D':
                $coords = array(-33.2975802, -66.344685);
                break;
            case 'E':
                $coords = array(-32.1156458, -60.0319688);
                break;
            case 'F':
                $coords = array(-29.8396499, -68.273314);
                break;
            case 'G':
                $coords = array(-28.0532798, -64.5710443);
                break;
            case 'H':
                $coords = array(-26.1878152, -61.6924568);
                break;
            case 'J':
                $coords = array(-31.5462472, -68.5566567);
                break;
            case 'K':
                $coords = array(-27.7553095, -67.8238272);
                break;
            case 'L':
                $coords = array(-37.0395855, -66.2405196);
                break;
            case 'M':
                $coords = array(-32.88337, -68.875342);
                break;
            case 'N':
                $coords = array(-26.8225555, -55.9700858);
                break;
            case 'P':
                $coords = array(-24.657959, -61.0295816);
                break;
            case 'Q':
                $coords = array(-38.9560437, -68.1185493);
                break;
            case 'R':
                $coords = array(-40.0178043, -68.8075603);
                break;
            case 'S':
                $coords = array(-31.6134016, -60.7152858);
                break;
            case 'T':
                $coords = array(-27.0278799, -65.7376345);
                break;
            case 'U':
                $coords = array(-43.9710412, -70.0556373);
                break;
            case 'V':
                $coords = array(-54.0550412, -68.0063843);
                break;
            case 'W':
                $coords = array(-27.4878462, -58.8234578);
                break;
            case 'X':
                $coords = array(-31.4010127, -64.2492772);
                break;
            case 'Y':
                $coords = array(-23.3030358, -66.6469644);
                break;
            case 'Z':
                $coords = array(-49.4267631, -71.4255266);
                break;
        }
        return $coords;
    }

    public function get_province_name($province_id = '')
    {
        switch ($province_id) {
            case 'C':
                $zone = 'Capital Federal';
                break;
            case 'B':
            default:
                $zone = 'Buenos Aires';
                break;
            case 'K':
                $zone = 'Catamarca';
                break;
            case 'H':
                $zone = 'Chaco';
                break;
            case 'U':
                $zone = 'Chubut';
                break;
            case 'X':
                $zone = 'Cordoba';
                break;
            case 'W':
                $zone = 'Corrientes';
                break;
            case 'E':
                $zone = 'Entre Rios';
                break;
            case 'P':
                $zone = 'Formosa';
                break;
            case 'Y':
                $zone = 'Jujuy';
                break;
            case 'L':
                $zone = 'La Pampa';
                break;
            case 'F':
                $zone = 'La Rioja';
                break;
            case 'M':
                $zone = 'Mendoza';
                break;
            case 'N':
                $zone = 'Misiones';
                break;
            case 'Q':
                $zone = 'Neuquen';
                break;
            case 'R':
                $zone = 'Rio Negro';
                break;
            case 'A':
                $zone = 'Salta';
                break;
            case 'J':
                $zone = 'San Juan';
                break;
            case 'D':
                $zone = 'San Luis';
                break;
            case 'Z':
                $zone = 'Santa Cruz';
                break;
            case 'S':
                $zone = 'Santa Fe';
                break;
            case 'G':
                $zone = 'Santiago del Estero';
                break;
            case 'V':
                $zone = 'Tierra del Fuego';
                break;
            case 'T':
                $zone = 'Tucuman';
                break;
        }
        return $zone;
    }

    public function get_zones_names_for_shipping_zone()
    {
        $zones = array();
        $zones[] = array('code' => 'AR:C', 'type' => 'state');
        $zones[] = array('code' => 'AR:B', 'type' => 'state');
        $zones[] = array('code' => 'AR:K', 'type' => 'state');
        $zones[] = array('code' => 'AR:H', 'type' => 'state');
        $zones[] = array('code' => 'AR:U', 'type' => 'state');
        $zones[] = array('code' => 'AR:X', 'type' => 'state');
        $zones[] = array('code' => 'AR:W', 'type' => 'state');
        $zones[] = array('code' => 'AR:E', 'type' => 'state');
        $zones[] = array('code' => 'AR:P', 'type' => 'state');
        $zones[] = array('code' => 'AR:Y', 'type' => 'state');
        $zones[] = array('code' => 'AR:L', 'type' => 'state');
        $zones[] = array('code' => 'AR:F', 'type' => 'state');
        $zones[] = array('code' => 'AR:M', 'type' => 'state');
        $zones[] = array('code' => 'AR:N', 'type' => 'state');
        $zones[] = array('code' => 'AR:Q', 'type' => 'state');
        $zones[] = array('code' => 'AR:R', 'type' => 'state');
        $zones[] = array('code' => 'AR:A', 'type' => 'state');
        $zones[] = array('code' => 'AR:J', 'type' => 'state');
        $zones[] = array('code' => 'AR:D', 'type' => 'state');
        $zones[] = array('code' => 'AR:Z', 'type' => 'state');
        $zones[] = array('code' => 'AR:S', 'type' => 'state');
        $zones[] = array('code' => 'AR:G', 'type' => 'state');
        $zones[] = array('code' => 'AR:V', 'type' => 'state');
        $zones[] = array('code' => 'AR:T', 'type' => 'state');
        return $zones;
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
        $products['packages'] = array();
        $skus = array();

        foreach ($products['products'] as $index => $product) {
            $products['shipping_info']['total_weight'] += $product['weight'];
            $products['shipping_info']['total_volume'] += $product['height'] * $product['width'] * $product['length'];
            $skus[] = $product['sku'];

            // One package per unit of product sold
            if (get_option('zippin_packaging_mode') != 'grouped') {
                $products['packages'][] = array(
                    'classification_id' => 1,
                    'weight' => $product['weight'],
                    'height' => $product['height'],
                    'width' => $product['width'],
                    'length' => $product['length'],
                    'description_1' => $product['sku'],
                    'description_2' => $product['name']
                );
            }
        }

        // One package grouping all products
        if (get_option('zippin_packaging_mode') == 'grouped') {
            $side = pow($products['shipping_info']['total_volume'],1/3);
            $products['packages'][] = array(
                'classification_id' => 1,
                'weight' => $products['shipping_info']['total_weight'],
                'height' => $side,
                'width' => $side,
                'length' => $side,
                'description_1' => implode('_',$skus),
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
            'height' => ($product->get_height() ? wc_get_dimension($product->get_height(), 'cm') : '0'),
            'width' => ($product->get_width() ? wc_get_dimension($product->get_width(), 'cm') : '0'),
            'length' => ($product->get_length() ? wc_get_dimension($product->get_length(), 'cm') : '0'),
            'weight' => ($product->has_weight() ? wc_get_weight($product->get_weight(), 'kg')*1000 : '0'),
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
                'state' => $this->get_province_name($order->get_shipping_state()),
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
                'state' => $this->get_province_name($order->get_billing_state()),
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