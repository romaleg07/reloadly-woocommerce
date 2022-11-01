<?php 
    class WC_Product_Reloadly extends WC_Product {
		
        public function get_type() {
            return 'reloadly'; 
        }

        public function add_to_cart_url() {
            $url = $this->is_purchasable() && $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id );
            return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
        }

    }
?>