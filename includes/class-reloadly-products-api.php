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

//  не забыть снизить количество запросов в бд


class Reloadly_Products_Api_Auth {
    protected $url =  'https://auth.reloadly.com/oauth/token';


    public function get_access_token() {
        if($this->is_plugin_active()) {
            if (($this->is_token_active() > 0) or $this->is_mode_changed()) {

                $curl = curl_init();
                global $wpdb;

                $table_name = $wpdb->prefix . 'reloadly_products_table';

                $results = $wpdb->get_results("SELECT * FROM  $table_name WHERE id = 0");
                $results = $results[0];
                $client_id = $results->client_id;
                $client_secret = $results->client_secret;
                $mode = $results->mode;
                $audience = 'https://giftcards.reloadly.com';
                if ($mode == 'sandbox') {
                    $audience = 'https://giftcards-sandbox.reloadly.com';
                }

                curl_setopt_array($curl, [
                    CURLOPT_URL => $this->url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => '{
                        "client_id": "' . $client_id . '",
                        "client_secret": "' . $client_secret . '",
                        "grant_type": "client_credentials",
                        "audience": "' . $audience . '"
                    }',
                    CURLOPT_HTTPHEADER => [
                        "Accept: application/json",
                        "Content-Type: application/json"
                    ],
                ]);

                $response = curl_exec($curl);
                $err = curl_error($curl);
                $response = json_decode($response, true);

                curl_close($curl);

                if ($err) {
                    echo "cURL Error #:" . $err;
                } else {
                    $wpdb->update( 
                        $table_name, 
                        array(
                            'access_token' => $response["access_token"],
                            'expire_in' => $response["expires_in"],
                            'token_timestamp' => time()
                        ),
                        array(
                            'id' => 0
                        )
                    
                    );
                    return $response["access_token"];
                }


            } else {
                global $wpdb;

                $table_name = $wpdb->prefix . 'reloadly_products_table';

                $results = $wpdb->get_results("SELECT * FROM  $table_name WHERE id = 0");
                $results = $results[0];

                $token = $results->access_token;

                return $token;
            }
        } 
    }


    public function is_token_active() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'reloadly_products_table';

        $results = $wpdb->get_results("SELECT * FROM  $table_name WHERE id = 0");
        $results = $results[0];

        $token = $results->access_token;
        $expire = $results->expire_in;
        $token_timestamp = $results->token_timestamp;
        $current_timestamp = time();
        $active_time = 0;
        if (!is_null($token)) {
            $active_time = $current_timestamp - ($expire + $token_timestamp);
            
        }

        return $active_time;
    }

    public function is_mode_changed() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'reloadly_products_table';

        $results = $wpdb->get_results("SELECT * FROM  $table_name WHERE id = 0");
        $results = $results[0];

        $token_timestamp = $results->token_timestamp;
        $mode_change_timaestamp = $results->mode_change_timastamp;
        $plugin_activate_timestamp = $results->activate_timastamp;

        return $token_timestamp < $mode_change_timaestamp or $token_timestamp < $plugin_activate_timestamp;
    }


    public function is_plugin_active() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'reloadly_products_table';

        $results = $wpdb->get_results("SELECT * FROM  $table_name WHERE id = 0");
        $results = $results[0];

        $mode = $results->mode;
        $client_id = $results->client_id;
        $client_secret = $results->client_secret;

        return !empty($mode) and !empty($client_id) and !empty($client_secret);
    }
}


class Reloadly_Products_Api {
    public $token;
    public function __construct($token){
        $this->token = $token;
    }

    public function get_countries() {
        $curl = curl_init();
        global $wpdb;

        $table_name = $wpdb->prefix . 'reloadly_products_table';

        $results = $wpdb->get_results("SELECT * FROM  $table_name WHERE id = 0");
        $results = $results[0];
        $mode = $results->mode;
        $audience = 'https://giftcards.reloadly.com/countries';
        if ($mode == 'sandbox') {
            $audience = 'https://giftcards-sandbox.reloadly.com/countries';
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $audience,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Accept: application/com.reloadly.giftcards-v1+json",
                "Authorization: Bearer " . $this->token . ""
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }   


    public function get_products($country, $nameProd) {
        $curl = curl_init();
        global $wpdb;

        $table_name = $wpdb->prefix . 'reloadly_products_table';

        $results = $wpdb->get_results("SELECT * FROM  $table_name WHERE id = 0");
        $results = $results[0];
        $mode = $results->mode;
        $audience = 'https://giftcards.reloadly.com/products?';
        if ($mode == 'sandbox') {
            $audience = 'https://giftcards-sandbox.reloadly.com/products?';
        }

        if ($country != '0') {
            $audience .= 'countryCode=' . $country . '&';
        }
        $audience .= 'productName=' . $this->change_str_reuest($nameProd) . '&size=20';


        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $audience,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Accept: application/com.reloadly.giftcards-v1+json",
                "Authorization: Bearer " . $this->token . ""
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }

    }

    private function change_str_reuest($str) {
        $str = mb_strtolower(trim($str));
        return str_replace(' ', '+', $str);
    }
}

?>