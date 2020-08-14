<?php
class DS_Helpers {

    // class instance
    static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // class constructor
    public function __construct() {}

    public static function get_type_sms_url($order_id=''){

        if ( !empty( $order_id ) ) {

            $menu_page_url =  menu_page_url('ds_sms', false);
            $query_args_view_booking = array(
              'page'      =>  wp_unslash($_REQUEST['page']),
              'action'    => 'type_sms',
              'customer'   => absint( $order_id ),
              'order_id'   => absint( $order_id ),
              '_wpnonce'  => wp_create_nonce('send_sms_nonce'),
            );

            return add_query_arg($query_args_view_booking, $menu_page_url);
        }

        return '';
    }

    public static function get_array_from_xml($xmlString='') {

        $xmlString = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xmlString);

        $xml = simplexml_load_string($xmlString);

        $json = json_encode($xml);

        return json_decode($json,true);

    }
    public static function get_cities($key = '_billing_city')
    {

        global $wpdb;

        if (empty($key))
            return;

        $cities = $wpdb->get_col($wpdb->prepare("SELECT pm.meta_value FROM {$wpdb->postmeta} pm WHERE pm.meta_key = %s", $key));

        $cities = array_map('strtolower', $cities);

        sort($cities);

        return array_unique($cities);
    }

    public static function has_bought_product($product_id, $user_id)
    {

        $bought = false;

        // Set HERE ine the array your specific target product IDs
        $prod_arr = array($product_id);

        // Get all customer orders
        $customer_orders = get_posts(array(
            'numberposts' => -1,
            'meta_key'    => '_customer_user',
            'meta_value'  => $user_id,
            'post_type'   => 'shop_order', // WC orders post type
            'post_status' => 'wc-completed' // Only orders with status "completed"
        ));
        foreach ($customer_orders as $order) {
            // Updated compatibility with WooCommerce 3+
            $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
            $order = wc_get_order($order);

            // Iterating through each current customer products bought in the order
            foreach ($order->get_items() as $item) {
                // WC 3+ compatibility
                if (version_compare(WC_VERSION, '3.0', '<')){
                    $product_id = $item['product_id'];
                } else{
                    $product_id = $item->get_product_id();
                }


                // Your condition related to your 2 specific products Ids
                if (in_array($product_id, $prod_arr)){
                    $bought = true;
                }

            }
        }
        // return "true" if one the specifics products have been bought before by customer
        return $bought;
    }

    public static function get_orders_last_by_days($days = 30)
    {
        $customers = [];

        $args = array(
            'numberposts' => -1,
            'post_type'   => wc_get_order_types('view-orders'),
            'post_status' => array_keys(wc_get_order_statuses()),
            'orderby' => 'date',
            'orderby' => 'DESC',
            'date_query' => array(
                array(
                    'after' => date('Y-m-d', strtotime("-$days days")),
                    'before' => date('Y-m-d'),
                    'inclusive' => true,
                )
            )
        );

        $orders = get_posts($args);

        foreach ($orders as $order) {

            $customer_id = get_post_meta($order->ID, '_customer_user', true);

            if ($customer_id ) {
                $customers[$order->ID] = $customer_id;
            }

        }

        return $customers;
    }

}
new DS_Helpers();
