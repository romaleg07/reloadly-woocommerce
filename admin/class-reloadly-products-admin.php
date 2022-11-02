<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/romaleg07/
 * @since      1.0.0
 *
 * @package    Reloadly_Products
 * @subpackage Reloadly_Products/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Reloadly_Products
 * @subpackage Reloadly_Products/admin
 * @author     Romaleg <romaleg.sky@yandex.ru>
 */
class Reloadly_Products_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Reloadly_Products_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Reloadly_Products_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/reloadly-products-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Reloadly_Products_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Reloadly_Products_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/reloadly-products-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
     * Register the page name on the admin menu
     *
     */
    public function reloadly_products_custom_menu_item() {

		add_menu_page(  'Reloadly Products > Settings',
						'Reloadly Products',
						'manage_options',
						'reloadly-products',
						array($this, "reloadly_products_admin_page") ,
						"https://cdn.reloadly.com/favicon-wp.ico",
						30);
	}

	public function reloadly_products_admin_page(){

        global $wpdb;

        $table_name = $wpdb->prefix . 'reloadly_products_table';


        // Select
        $results = $wpdb->get_results("SELECT * FROM  $table_name WHERE id = 0");
        $results = $results[0];

        if(isset($_POST['submit'])) {

            // Insert
            $update = $wpdb->update(
                $table_name,
                array(
                    'id'            => '0',
                    'mode'          => sanitize_text_field(str_replace(' ', '', $_POST['mode'])),
					'client_id'     => sanitize_text_field(str_replace(' ', '', $_POST['client_id'])),
					'client_secret' => sanitize_text_field(str_replace(' ', '', $_POST['client_secret'])),
					'mode_change_timastamp' => time()
                ),
                array(
                    'id' => '0'
                )
            );

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $update );

            $results->mode = esc_textarea($_POST['mode']);
			$results->client_id = esc_textarea($_POST['client_id']);
			$results->client_secret = esc_textarea($_POST['client_secret']);

        } ?>

        <div class="wrap">
            <h2>Reloadly products</h2>
        <div>
        </div>
            <table class="form-table" role="presentation">
                <tbody>
                    <form  action="" method="POST">
                        <tr>
                            <th scope="row">
                                <span>Use Sandbox mode:</span>
                            </th>
                            <td>
								<label style="margin-right: 15px;">
									<input name="mode" type="radio" id="sandbox" value="sandbox" <?php echo $results->mode ==  'sandbox' ? 'checked' : '';?>>
									Yes
								</label>
								
								<label>
									<input name="mode" type="radio" id="sandbox" value="live" <?php echo $results->mode ==  'live' ? 'checked' : '';?>>
									No
								</label>
                            </td>
                        </tr>
						<tr>
                            <th scope="row">
                                <label for="client_id">Client ID:</label>
                            </th>
                            <td>
                                <input name="client_id" type="text" id="client_id" class="regular-text" value="<?php echo $results->client_id ?> ">
                            </td>
                        </tr>
						<tr>
                            <th scope="row">
                                <label for="client_secret">Client Srcret:</label>
                            </th>
                            <td>
                                <input name="client_secret" type="text" id="client_secret" class="regular-text" value="<?php echo $results->client_secret ?> ">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                            </th>
                        </tr>
                    </form>
                </tbody>
            </table>
        </div>
        <?php
    }

	public function create_term() {
		// create new term. this code gonna be run 1 time

		if ( ! get_term_by( 'slug', 'reloadly', 'product_type' ) ) {
			wp_insert_term( 'reloadly', 'product_type' );
		}
	}

	public function reloadly_type_select( $types ) {
		// add new type into select

		$types[ 'reloadly' ] = 'Reloadly';
		return $types;
	}

	public function add_tab_reloadly( $tabs) {
		// add new tab for reloadly products

		$tabs[ 'reloadly' ] = array(
			'label'	 => 'Reloadly',
			'target' => 'reloadly_options', 
			'class'  => 'show_if_reloadly', 
		);
		return $tabs;
	}

	public function add_tab_content_reloadly() {
		?>
		<div id="reloadly_options" class="panel woocommerce_options_panel">
			<div class="info">
				Связать товары с Reloadly
			</div>
			<div class="wrapper-content">

				<div class="left">
					<div class="item">
						<select name="country-reloadly" id="country-reloadly">
							<option value="0">Выберите страну</option>
						</select>
					</div>
					<div class="item">
						<span>Начните вводить название в системе Reloadly</span>
						<input type="text" id="name-reloadly">
						<div id="help-div-1"></div>
					</div>
					<div class="item item-value-reloadly">

					</div>
					
				</div>
				<div class="right">
					<div class="item">
						<span>Название в системе Reloadly</span>
						<input type="text" id="name-reloadly-system" value="<?php echo get_post_meta(get_the_ID(), '_name_in_reloadly', true);?>" disabled >
					</div>
					<div class="item">
						<span>Номинал в системе Reloadly</span>
						<input type="text" id="denomination-reloadly-system" value="<?php echo get_post_meta(get_the_ID(), '_denomination_in_reloadly', true);?> (<?php echo get_post_meta(get_the_ID(), '_denomination_currency_reloadly', true);?>)" disabled >
					</div>
					<div class="item">
						<span>ID в системе Reloadly</span>
						<input type="text" id="id-reloadly" value="<?php echo get_post_meta(get_the_ID(), '_id_in_reloadly', true);?>" disabled >
					</div>
					<div class="item">
						<span>Страна товара</span>
						<input type="text" id="current-country-reloadly" value="<?php echo get_post_meta(get_the_ID(), '_country_in_reloadly', true);?>" disabled >
					</div>
					<div class="item">
						<span>Sender Fee</span>
						<input type="text" id="sender-fee-reloadly" value="<?php echo get_post_meta(get_the_ID(), '_fee_in_reloadly', true);?>" disabled >
					</div>
					<div class="item">
						<span>Sender Fee Percentage</span>
						<input type="text" id="sender-fee-percentage-reloadly" value="<?php echo get_post_meta(get_the_ID(), '_fee_percentage_in_reloadly', true);?>" disabled >
					</div>
					<div class="item">
						<span>Discount Percentage</span>
						<input type="text" id="discount-percentage-reloadly" value="<?php echo get_post_meta(get_the_ID(), '_discount_percentage_in_reloadly', true);?>" disabled >
					</div>
					<input type="hidden" name="" id="id_product_in_woocommerce" value="<?php echo get_the_ID(); ?>">
					<input type="hidden" name="" id="image_product_in_reloadly" >
				</div>
			</div>
			<div class="save">
				<a href="#" id="reloadly-save-field" class="button button-primary">Сохранить</a>
			</div>
		</div>
		
		<?php
	
	}


	public function reloadly_show_prices_tab() {
		// show tabs with pricing for reloadly products
	
		global $post, $product_object;
	
		if ( ! $post ) {
			return;
		}
	
		if ( 'product' !== get_post_type( $post ) ) {
			return;
		}
	
		$is_reloadly = $product_object && 'reloadly' === $product_object->get_type() ? true : false;
	
		echo "<script type='text/javascript'>
			jQuery(document).ready(function ( $ ) {
				$( '#general_product_data .pricing' ).addClass( 'show_if_reloadly' );";
	
				if ( $is_reloadly ) {
					echo "$( '#general_product_data .pricing' ).show();";
				}
	
		echo "});</script>";
	
	}

	public function reloadly_product_type_add_to_cart() {
		global $product;
	
		if ( 'reloadly' == $product->get_type() ) { 
			echo '<p class="cart">
				<a href="' . esc_url( $product->add_to_cart_url() ) . '" rel="nofollow" class="single_add_to_cart_button button alt">Добавить в корзину</a>
			</p>';
		}
	}

	public function add_img_for_product_from_reloadly($img, $product_id) {
		$file = $img;
		$filename = basename($file);
		$fileNameArray = explode('.', $filename);
		$arguments = array(
			'name' => $fileNameArray[0],
			'post_type'        => 'attachment',
		);
		$attachments = get_posts($arguments);
		$post_image = get_the_post_thumbnail( $product_id, 'thumbnail');
		if (empty($post_image)){
			if(!$attachments) {
				$upload_file = wp_upload_bits($filename, null, file_get_contents($file));
				if (!$upload_file['error']) {
					$wp_filetype = wp_check_filetype($filename, null );
					$attachment = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_parent' => $product_id,
						'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
						'post_content' => '',
						'post_status' => 'inherit'
					);
					$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $product_id );
					if (!is_wp_error($attachment_id)) {
						require_once(ABSPATH . "wp-admin" . '/includes/image.php');
						$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
						wp_update_attachment_metadata( $attachment_id,  $attachment_data );

						set_post_thumbnail( $product_id, $attachment_id );
					}
				}
			} else {
				set_post_thumbnail( $product_id, $attachments[0]->ID );
			}
		} else {
			echo 'Изображение уже установлено';
		}
	
	}

}
