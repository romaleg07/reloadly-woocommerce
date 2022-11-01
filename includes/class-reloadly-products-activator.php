<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/romaleg07/
 * @since      1.0.0
 *
 * @package    Reloadly_Products
 * @subpackage Reloadly_Products/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Reloadly_Products
 * @subpackage Reloadly_Products/includes
 * @author     Romaleg <romaleg.sky@yandex.ru>
 */
class Reloadly_Products_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;

        $table_name = $wpdb->prefix . 'reloadly_products_table';

		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE " . $table_name . " (
				id int NOT NULL,
				mode tinytext NOT NULL,
				client_id tinytext NOT NULL,
				client_secret tinytext NOT NULL,
				access_token text,
				expire_in int,
				token_timestamp int,
				activate_timastamp int,
				mode_change_timastamp int,
				UNIQUE KEY id (id)
			  );";
		  
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);

			$insert = $wpdb->insert( $table_name, array( 'id' => '0', 'mode' => '', 'client_id' => '', 'client_secret' => '', 'activate_timastamp' => time()));
            dbDelta( $insert );
		}
	}

}
