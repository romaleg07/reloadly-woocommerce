<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/romaleg07/
 * @since             1.0.0
 * @package           Reloadly_Products
 *
 * @wordpress-plugin
 * Plugin Name:       Reloadly Products
 * Plugin URI:        https://github.com/romaleg07/reloadly-product-woocommerce
 * Description:       Add new product type for integrations with Reloadly
 * Version:           1.0.0
 * Author:            Romaleg
 * Author URI:        https://github.com/romaleg07/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       reloadly-products
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'RELOADLY_PRODUCTS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-reloadly-products-activator.php
 */
function activate_reloadly_products() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-reloadly-products-activator.php';
	Reloadly_Products_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-reloadly-products-deactivator.php
 */
function deactivate_reloadly_products() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-reloadly-products-deactivator.php';
	Reloadly_Products_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_reloadly_products' );
register_deactivation_hook( __FILE__, 'deactivate_reloadly_products' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-reloadly-products.php';


function reloadly_products_create_product_type() {
    class WC_Product_Reloadly extends WC_Product {
		
        public function get_type() {
            return 'reloadly'; 
        }

        public function add_to_cart_url() {
            $url = $this->is_purchasable() && $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id );
            return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
        }

    }
}

// include class with api requests
require_once plugin_dir_path( __FILE__ ) . 'includes/class-reloadly-products-api.php';


add_action( 'wp_ajax_get_countries', 'get_countries' );

function get_countries() {
    $token_class = new Reloadly_Products_Api_Auth();
    $token = $token_class->get_access_token();

    $api_class = new Reloadly_Products_Api($token);
    $countries = $api_class->get_countries();
    echo $countries;

	wp_die(); // this is required to terminate immediately and return a proper response
}


add_action( 'wp_ajax_get_products', 'get_products' );

function get_products() {
    $token_class = new Reloadly_Products_Api_Auth();
    $token = $token_class->get_access_token();

    $country = $_POST['country'];
    $nameProd = $_POST['nameProd'];

    $api_class = new Reloadly_Products_Api($token);
    $products = $api_class->get_products($country, $nameProd);
    echo $products;

	wp_die(); // this is required to terminate immediately and return a proper response
}


add_action( 'wp_ajax_add_img_product_from_reloadly', 'add_img_product_from_reloadly' );

function add_img_product_from_reloadly() {
    if ( defined( 'RELOADLY_PRODUCTS_VERSION' ) ) {
        $version = RELOADLY_PRODUCTS_VERSION;
    } else {
        $version = '1.0.0';
    }
    $plugin_name = 'reloadly-products';
    $smt = new Reloadly_Products_Admin($plugin_name, $version);
    $smt->add_img_for_product_from_reloadly();

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_save_reloadly_data', 'save_reloadly_data' );

function save_reloadly_data() {
    $id_prod_wc = (int)$_POST['product_id_woocommerce'];
    $product_name_reloadly = $_POST['product_name_reloadly'];
    $product_id_reloadly = $_POST['product_id_reloadly'];
    $product_country_reloadly = $_POST['product_country_reloadly'];
    $product_sender_fee_reloadly = $_POST['product_sender_fee_reloadly'];
    $product_sender_fee_percentage_reloadly = $_POST['product_sender_fee_percentage_reloadly'];
    $product_discount_percentage_reloadly = $_POST['product_discount_percentage_reloadly'];
    $product_denomination_reloadly = $_POST['product_denomination_reloadly'];
    $product_denomination_currency_reloadly = $_POST['product_denomination_currency_reloadly'];

    update_post_meta($id_prod_wc, '_name_in_reloadly', $product_name_reloadly);
    update_post_meta($id_prod_wc, '_id_in_reloadly', $product_id_reloadly);
    update_post_meta($id_prod_wc, '_country_in_reloadly', $product_country_reloadly);
    update_post_meta($id_prod_wc, '_fee_in_reloadly', $product_sender_fee_reloadly);
    update_post_meta($id_prod_wc, '_fee_percentage_in_reloadly', $product_sender_fee_percentage_reloadly);
    update_post_meta($id_prod_wc, '_denomination_in_reloadly', $product_denomination_reloadly);
    update_post_meta($id_prod_wc, '_discount_percentage_in_reloadly', $product_discount_percentage_reloadly);
    update_post_meta($id_prod_wc, '_denomination_currency_reloadly', $product_denomination_currency_reloadly);

	wp_die(); // this is required to terminate immediately and return a proper response
}


// create new product type for reloadly
add_action('init', 'reloadly_products_create_product_type');


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_reloadly_products() {

	$plugin = new Reloadly_Products();
	$plugin->run('test');

}
run_reloadly_products();
