<?php

namespace Zippin\Zippin\Settings;

use Zippin\Zippin\ZippinConnector;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function init_settings()
{
    register_setting('zippin_main_section', 'zippin_other_options');

    add_settings_section(
        'zippin_main_section',
        'Configuración',
        __NAMESPACE__ . '\print_instructions',
        'zippin_settings'
    );

    add_settings_field(
        'api_key',
        'API v2 Account Key',
        __NAMESPACE__ . '\print_api_key',
        'zippin_settings',
        'zippin_main_section'
    );

    add_settings_field(
        'api_secret',
        'API v2 Account Secret',
        __NAMESPACE__ . '\print_api_secret',
        'zippin_settings',
        'zippin_main_section'
    );

    add_settings_field(
        'account_id',
        'ID de Cuenta',
        __NAMESPACE__ . '\print_account_id',
        'zippin_settings',
        'zippin_main_section'
    );


    add_settings_field(
        'webhook_instructions',
        'Notificaciones de Zippin',
        __NAMESPACE__ . '\print_webhooks_instructions',
        'zippin_settings',
        'zippin_main_section'
    );

    add_settings_field(
        'origin_id',
        'Origen predeterminado para envíos',
        __NAMESPACE__ . '\print_origins',
        'zippin_settings',
        'zippin_main_section'
    );

    add_settings_field(
        'packaging_mode',
        'Agrupación en paquetes',
        __NAMESPACE__ . '\print_packaging_mode',
        'zippin_settings',
        'zippin_main_section'
    );

    add_settings_field(
        'default_shipping_status',
        'Estado de pedido',
        __NAMESPACE__ . '\print_default_shipping_status',
        'zippin_settings',
        'zippin_main_section'
    );

    add_settings_field(
        'enable_free_shipping_creation',
        'Crear envíos con Envío Gratis',
        __NAMESPACE__ . '\print_free_shipping_creation',
        'zippin_settings',
        'zippin_main_section'
    );


    add_settings_field(
        'extra_info',
        'Información adicional',
        __NAMESPACE__ . '\print_extra_info',
        'zippin_settings',
        'zippin_main_section'
    );
}

function add_assets_files($hook)
{
    if ($hook !== 'settings_page_zippin_settings') {
        return;
    }
    wp_enqueue_style('admin.css', plugin_dir_url(__FILE__) . 'css/admin.css', array(), 1.0);
}

function print_instructions()
{
    echo 'Para continuar deberás crear credenciales de API v2. Deberás crear un nuevo <b>token de cuenta</b> desde la sección de <a href="https://app.zippin.com.ar/myaccount/integrations/webhooks" target="_blank">Credenciales y Webhooks</a>';
}

function print_api_key()
{
    $previous_config = get_option('zippin_api_key');
    echo '<input type="text" required name="api_key" value="' . ($previous_config ? $previous_config : '') . '" />';
}

function print_api_secret()
{
    $previous_config = get_option('zippin_api_secret');
    echo '<input type="text" required name="api_secret" value="' . ($previous_config ? $previous_config : '') . '" />';
}

function print_account_id()
{
    $previous_config = get_option('zippin_account_id');
    echo '<input type="number" required name="account_id" value="' . ($previous_config ? $previous_config : '') . '" />
    <br><small>Lo encontrarás a la derecha en la página de credenciales y webhooks.</small>';

    if (get_option('zippin_account_id')) {
        $connector = new ZippinConnector;
        $account = $connector->get_account();
        if ($account) {
            echo '<p class="info-text">Cuenta: <b>'.$account['name'].'</b></p>';
        } else {
            echo '<p class="danger-text">Cuenta: <b>SIN AUTORIZACION</b><br>
                Verifica que las credenciales son válidas y corresponden a la cuenta indicada.</p>';
        }
    }

}

function print_webhooks_instructions()
{
    echo '<p class="info-text">Ingresa a <a href="https://app.zippin.com.ar/myaccount/integrations/webhooks" target="_blank">Credenciales y Webhooks</a> y a la derecha, en Webhooks, ve a crear un nuevo webhook.';
    echo '<br>Configura tu webhook de la siguiente manera: <br>topic: <b>shipment</b> <br>URL: <strong>' . get_site_url(null, '?wc-api=zippin') . '</strong></p>';
}

function print_packaging_mode()
{
    $previous_config = get_option('zippin_packaging_mode');
    echo '<p><label><input type="radio" name="packaging_mode" value="grouped"'.($previous_config=='grouped' || empty($previous_config) ? ' checked':'').'> Agrupar productos en un solo paquete por envío</label></p>';
    echo '<p><label><input type="radio" name="packaging_mode" value="separate"'.($previous_config=='separate' ? ' checked':'').'> No agrupar productos (crear un paquete por cada unidad de cada producto).</label></p>';

    echo '<br><p class="info-text">Si eliges agrupar los productos en un solo paquete, se sumará el peso y volumen de todos los productos y luego se calcularán dimensiones como una caja en forma de cubo.</p>';

}


function print_default_shipping_status()
{
    $statuses = wc_get_order_statuses();
    $previous_config = get_option('zippin_shipping_status');
    if (!$previous_config) update_option('zippin_shipping_status', 'wc-completed');
    echo '<select name="shipping_status">';
    foreach ($statuses as $status_key => $status_name) {
        if ($previous_config) {
            echo '<option value="' . $status_key . '" ' . ($previous_config === $status_key ? 'selected' : '') . '>' . $status_name . '</option>';
        } else {
            echo '<option value="' . $status_key . '" ' . ($status_key === 'wc-completed' ? 'selected' : '') . '>' . $status_name . '</option>';
        }
    }
    echo '</select>';
    echo '<p class="info-text">Los pedidos con este estado serán enviados automáticamente a Zippin</p>';
}

function print_free_shipping_creation()
{
    $previous_config = get_option('zippin_create_free_shipments');
    echo '<p><label><input type="radio" name="enable_free_shipping_creation" value="yes"'.($previous_config=='yes' ? ' checked':'').'> Si</label> ';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;<label><input type="radio" name="enable_free_shipping_creation" value="no"'.($previous_config=='no' || empty($previous_config) ? ' checked':'').'> No</label></p>';
    echo '<p class="info-text">Si habilitas esta opción, tambien se crearán en Zippin los envíos con Envío gratis. La selección del transporte será automática según la configuración de tu cuenta. <br>
<small>Aprende <a href="https://docs.woocommerce.com/document/free-shipping/" target="_blank">cómo configurar envío gratis en Woocomerce</a> y configura en Zippin <a target="_blank" href="https://app.zippin.com.ar/myaccount/account/settings">tus opciones de selección de transporte (solapa Opciones)</a>.</small>
</p>';
}


function print_origins()
{
    $previous_config = get_option('zippin_origin_id');
    if (get_option('zippin_api_key') && get_option('zippin_api_secret') && get_option('zippin_account_id')) {
        $connector = new ZippinConnector;
        $addresses = $connector->get_origins();
        if (count($addresses)>0) {
            echo '<select name="origin_id" required>';
        } else {
            echo '<select name="origin_id">';
        }
        echo '<option value="">Seleccionar Origen...</option>';
        foreach ($addresses as $address) {
            $show_as = $address['name'].' - '.$address['street'].' '.$address['street_number'].', '.$address['city']['name'];
            if ($previous_config) {
                if ($previous_config == $address['id']) {
                    echo '<option value="' . $address['id'] . '" selected>' . $show_as . '</option>';
                } else {
                    echo '<option value="' . $address['id'] . '">' . $show_as . '</option>';
                }
            } else {
                if (count($addresses)===1) {
                    echo '<option value="' . $address['id'] . '" selected>' . $show_as . '</option>';
                    update_option('zippin_origin_id', $address['id']);
                } else {
                    echo '<option value="' . $address['id'] . '">' . $show_as . '</option>';
                }
            }
        }
        echo '</select>';
    } else {
        echo '<p class="info-text">Luego de guardar tus credenciales te mostraremos aquí los orígenes disponibles y podrás seleccionar uno.</p>';
    }

}


function print_extra_info()
{
    echo '<p class="info-text">Al instalar este plugin podrás empezar a usar el shortcode <code>[zippin_tracking]</code>. Coloca este shortcode en cualquier página que desees usar para crear un formulario de seguimiento de pedidos de Zippin.</p>';
}

function create_menu_option()
{
    add_options_page(
        'Configuración de Zippin',
        'Envíos con Zippin',
        'manage_options',
        'zippin_settings',
        __NAMESPACE__ . '\settings_page_content'
    );
}

function settings_page_content()
{
    if (!current_user_can('manage_options')) {
        return;
    }

	// Save api_key
    if (isset($_POST['api_key'])) {
        wp_verify_nonce($_REQUEST['zippin_wpnonce'], 'zippin_settings_save' );
        update_option('zippin_api_key', sanitize_text_field($_POST['api_key']));
    }

	// Save api_secret
    if (isset($_POST['api_secret'])) {
        wp_verify_nonce($_REQUEST['zippin_wpnonce'], 'zippin_settings_save' );
        update_option('zippin_api_secret', sanitize_text_field($_POST['api_secret']));
    }

    // Save account_id
    if (isset($_POST['account_id'])) {
        wp_verify_nonce($_REQUEST['zippin_wpnonce'], 'zippin_settings_save' );
        if (get_option('zippin_account_id') != filter_var($_POST['account_id'],FILTER_SANITIZE_NUMBER_INT)) {
            delete_option('zippin_origin_id');
        }
        update_option('zippin_account_id', filter_var($_POST['account_id'],FILTER_SANITIZE_NUMBER_INT));
    }

    // Save origin id
    if (isset($_POST['origin_id'])) {
        wp_verify_nonce($_REQUEST['zippin_wpnonce'], 'zippin_settings_save' );
        update_option('zippin_origin_id', filter_var($_POST['origin_id'],FILTER_SANITIZE_NUMBER_INT));
    }

	// Save packaging mode
    if (isset($_POST['packaging_mode'])) {
        wp_verify_nonce($_REQUEST['zippin_wpnonce'], 'zippin_settings_save' );
        update_option('zippin_packaging_mode', sanitize_text_field($_POST['packaging_mode']));
    }

	// Save shipping status
    if (isset($_POST['shipping_status'])) {
        wp_verify_nonce($_REQUEST['zippin_wpnonce'], 'zippin_settings_save' );
        update_option('zippin_shipping_status', sanitize_text_field($_POST['shipping_status']));
    }

    // Save shipping status
    if (isset($_POST['enable_free_shipping_creation'])) {
        wp_verify_nonce($_REQUEST['zippin_wpnonce'], 'zippin_settings_save' );
        update_option('zippin_create_free_shipments', sanitize_text_field($_POST['enable_free_shipping_creation']));
    }
    ?>

	<div class="wrap">
        <img src="<?=plugin_dir_url(__FILE__) ?>images/zippin.png" />
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
		<form action="options-general.php?page=zippin_settings" method="post">
        <?php
        wp_nonce_field('zippin_settings_save','zippin_wpnonce',false,true);
        settings_fields('zippin_settings');
        do_settings_sections('zippin_settings');
        submit_button('Guardar');
        ?>
		</form>
	</div>
	<?php

}
