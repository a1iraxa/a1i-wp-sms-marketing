<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class DS_Customers_Table extends WP_List_Table
{

    public function __construct()
    {

        parent::__construct([
            'singular' => __('customer', 'tb'),
            'plural'   => __('customers', 'tb'),
            'ajax'     => false //should this table support ajax?
        ]);
    }

    public function get_columns()
    {

        $table_columns = array(
            'cb'    => '<input type="checkbox" />',
            // 'ID'   => __('ID', 'tb'),
            'first_name'   => __('First Name', 'tb'),
            'last_name'   => __('Last Name', 'tb'),
            'email'  => __('Email', 'tb'),
            'phone'  => __('Phone', 'tb'),
            'city'  => __('City', 'tb'),
            'user_type'  => __('Type', 'tb'),
            'action'  => __('Action', 'tb'),
        );

        return $table_columns;
    }

    public function no_items()
    {

        _e('No customer avaliable.', 'tb');
    }

    public function extra_tablenav($which)
    {

        ?>
        <?php if ($which == "top") : ?>
            <div class="alignleft actions">

                <select name="purchased_product" id="purchased_product" class="dropdown_purchased_product select2">
                    <?php
                        $args = array( 'numberposts' => -1, 'post_type'   => 'product' );
                        $products = get_posts($args);
                        $selected_product = isset($_REQUEST['purchased_product']) ? wp_unslash(trim($_REQUEST['purchased_product'])) : '';
                    ?>
                    <option value="" selected="selected">Filter by product</option>
                    <?php foreach ($products as $key => $product): ?>
                        <option class="level-0" <?php selected( $selected_product, $current = $product->ID, $echo = true ) ?> value="<?php echo $product->ID ?>"><?php echo $product->post_title." ".$product->ID; ?></option>
                    <?php endforeach ?>
                </select>

                <select name="city" id="city" class="dropdown_city select2">
                    <?php $cities = DS_Helpers::get_cities(); ?>
                    <?php $selected_city = isset($_REQUEST['city']) ? wp_unslash(trim($_REQUEST['city'])) : ''; ?>
                    <option value="" selected="selected">Filter by city</option>
                    <?php foreach ($cities as $key => $city): ?>
                        <option class="level-0" <?php selected( $selected_city, $current = $city, $echo = true ) ?> value="<?php echo $city ?>"><?php echo ucfirst($city); ?></option>
                    <?php endforeach ?>
                </select>

                <select name="duration" id="duration" class="dropdown_duration select2">
                    <?php $selected_duration = isset($_REQUEST['duration']) ? wp_unslash(trim($_REQUEST['duration'])) : ''; ?>
                    <option value="" selected="selected">Filter by order placed</option>
                    <option class="level-0" <?php selected( $selected_duration, $current = 1, $echo = true ) ?> value="1">Yesterday</option>
                    <option class="level-0" <?php selected( $selected_duration, $current = 7, $echo = true ) ?> value="7">07 Days</option>
                    <option class="level-0" <?php selected( $selected_duration, $current = 15, $echo = true ) ?> value="15">15 Days</option>
                    <option class="level-0" <?php selected( $selected_duration, $current = 30, $echo = true ) ?> value="30">30 Days</option>
                    <option class="level-0" <?php selected( $selected_duration, $current = 45, $echo = true ) ?> value="45">45 Days</option>
                    <option class="level-0" <?php selected( $selected_duration, $current = 60, $echo = true ) ?> value="60">60 Days</option>
                    <option class="level-0" <?php selected( $selected_duration, $current = 90, $echo = true ) ?> value="90">90 Days</option>
                </select>

                <input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter">

                <?php if (
                    (isset($_REQUEST['purchased_product']) && !empty( $_REQUEST['purchased_product'] )) ||
                    (isset($_REQUEST['duration']) && !empty( $_REQUEST['duration'] )) ||
                    (isset($_REQUEST['city']) && !empty( $_REQUEST['city'] ))
                ): ?>

                    <a href="<?php echo admin_url('admin.php?page=ds_sms_customers'); ?>" id="dsm-clear-filters" class="button-primary dsm-clear-filters">Clear All Filters</a>

                <?php endif ?>

            </div>
        <?php endif; ?>
        <?php
    }


    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $table_data = $this->fetch_table_data();

        $customer_search_key = isset($_REQUEST['s']) ? wp_unslash(trim($_REQUEST['s'])) : '';
        if ($customer_search_key) {

            $table_data = $this->search_table_data($table_data, $customer_search_key);
        }

        $customer_filter_key = isset($_REQUEST['filter_action']) ? wp_unslash(trim($_REQUEST['filter_action'])) : '';
        if ( $customer_filter_key ) {

            // filter by city
            $customer_by_city_key = isset($_REQUEST['city']) ? wp_unslash(trim($_REQUEST['city'])) : '';
            if ( $customer_by_city_key ) {
                // $table_data = $this->filter_city_table_data($table_data, $customer_by_city_key);
            }

            // filter by purchased product
            $customer_by_purchased_product_key = isset($_REQUEST['purchased_product']) ? wp_unslash(trim($_REQUEST['purchased_product'])) : '';
            if ( $customer_by_purchased_product_key ) {
                // $table_data = $this->filter_purchased_product_table_data($table_data, $customer_by_purchased_product_key);
            }

            // filter by duration
            $customer_by_order_duration_key = isset($_REQUEST['duration']) ? wp_unslash(trim($_REQUEST['duration'])) : '';
            if ( $customer_by_order_duration_key ) {
                // $table_data = $this->filter_duration_table_data($table_data, $customer_by_order_duration_key);
            }

        }

        $this->items = $table_data;

        $customers_per_page = $this->get_items_per_page('customers_per_page');
        $table_page = $this->get_pagenum();

        $this->items = array_slice($table_data, (($table_page - 1) * $customers_per_page), $customers_per_page);

        // set the pagination arguments
        $total_customers = count($table_data);
        $this->set_pagination_args(array(
            'total_items' => $total_customers,
            'per_page'    => $customers_per_page,
            'total_pages' => ceil($total_customers / $customers_per_page)
        ));
    }

    public function fetch_table_data()
    {
        $orders = [];
        $orderby = isset($_GET['orderby']) ? esc_sql($_GET['orderby']) : 'date';
        $order = isset($_GET['order']) ? esc_sql($_GET['order']) : 'ASC';
	    $city= isset($_GET['city']) ? esc_sql($_GET['city']) : '';
	    $duration= isset($_GET['duration']) ? esc_sql($_GET['duration']) : '7';
	    $purchased_product= isset($_GET['purchased_product']) ? esc_sql($_GET['purchased_product']) : '';

        $formated_customers = [];
        $order_ids_by_product = [];
	    $order_ids_by_city_duration = [];

        if ( $purchased_product ) {
	        global $wpdb;

	        $table_posts = $wpdb->prefix . "posts";
	        $table_items = $wpdb->prefix . "woocommerce_order_items";
	        $table_itemmeta = $wpdb->prefix . "woocommerce_order_itemmeta";

	        // Define HERE the orders status to include in  <==  <==  <==  <==  <==  <==  <==
	        $orders_statuses = "'wc-completed', 'wc-processing', 'wc-on-hold'";

	        # Requesting All defined statuses Orders IDs for a defined product ID
	        $orders_ids = $wpdb->get_col( "
                SELECT $table_items.order_id
                FROM $table_itemmeta, $table_items, $table_posts
                WHERE  $table_items.order_item_id = $table_itemmeta.order_item_id
                AND $table_items.order_id = $table_posts.ID
                AND $table_posts.post_status IN ( $orders_statuses )
                AND $table_itemmeta.meta_key LIKE '_product_id'
                AND $table_itemmeta.meta_value LIKE '$purchased_product'
                ORDER BY $table_items.order_item_id DESC"
                    );
            // return an array of Orders IDs for the given product ID
	        $order_ids_by_product = array_unique($orders_ids);

        }
        if( '' != $city || '' != $duration){
	        $dateBegin = date('Y-m-d', strtotime("-$duration days"));

	        $wp_order_query = new WC_Order_Query( array(
		        'limit'    => -1,
		        'orderby' => $orderby,
		        'order' => $order,
		        'billing_city' => $city,
		        'date_created' => '>' . $dateBegin,
		        'return' => 'ids',
	        ) );

	        $order_ids_by_city_duration = $wp_order_query->get_orders();
        }

        if ( !empty( $order_ids_by_product ) && !empty( $order_ids_by_city_duration )){
	        $orders = array_intersect($order_ids_by_product,$order_ids_by_city_duration);
        }else if ( !empty( $order_ids_by_product )){
		    $orders = $order_ids_by_product;
	    }else if (!empty( $order_ids_by_city_duration )){
		    $orders = $order_ids_by_city_duration;
	    }

         // echo "<pre>";print_r($orders);echo "</pre>";

        foreach ($orders as $key => $order_id) {

            $order = new WC_Order( $order_id );
            $items = $order->get_items();
            $products = [];

            foreach ( $items  as $item_id => $item) {
                $products[$item['product_id']] = $item['name'];
            }

            $phone = trim(self::get_order_data($order_id, '_billing_phone'));

            if ( array_key_exists( $phone, $formated_customers ) ) {
                $products = array_replace_recursive($products, $formated_customers[$phone]['products']);
            }

            $formated_customers[$phone] = array(
                'ID' => $order_id,
                'first_name'   => self::get_order_data($order_id, '_billing_first_name'),
                'last_name'   => self::get_order_data($order_id, '_billing_last_name'),
                'email'  => self::get_order_data($order_id, '_billing_email'),
                'phone'  => $phone,
                'city'  => self::get_order_data($order_id, '_billing_city'),
                'user_type'  => ( self::get_order_data($order_id, '_customer_user') ) ? self::get_order_data($order_id, '_customer_user') : 'Guest',
                'products'  => $products,
                'date_created'  => $order->get_date_created()->format ('Y-m-d'),
            );
        }

        return $formated_customers;
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'action':
                $type_sms_link = DS_Helpers::get_type_sms_url($item['ID']);
                return '<a class="button-primary" href="' . $type_sms_link . '">' . __('Type SMS', 'tb') . '</a>';
            default:
                return $item[$column_name];
        }
    }

    public static function get_user_data($user_id, $meta_key)
    {

        return get_user_meta($user_id, $meta_key, true);
    }

    public static function get_order_data($order_id, $meta_key)
    {

        return get_post_meta($order_id, $meta_key, true);
    }

    protected function column_cb($item)
    {

        return sprintf(
            "<input type='checkbox' name='customers[]' ID='customer_{$item['ID']}' value='{$item['ID']}' />"
        );
    }

    protected function column_ID($item)
    {

        $actions = [];
        $type_sms_link = DS_Helpers::get_type_sms_url($item['ID']);
        $actions['type_sms'] = '<a href="' . $type_sms_link . '">' . __('Type SMS', 'tb') . '</a>';
        $row_value = '<strong>' . ucfirst($item['ID']) . '</strong>';
        return $row_value . $this->row_actions($actions);
    }

    protected function get_sortable_columns()
    {

        $sortable_columns = array(
            'first_name'   => false,
            'last_name'   => false,
            'email'  => false,
            'phone'  => false,
            'city'  => false,
            'action'  => false,
        );

        return $sortable_columns;
    }

    public function search_table_data($table_data, $search_key)
    {
        $searched_table_data = array_values(array_filter($table_data, function ($row) use ($search_key) {
            foreach ($row as $row_val) {
                if ( !is_array( $row_val ) ) {
                    if (stripos($row_val, $search_key) !== false) {
                        return true;
                    }
                }
            }
        }));

        return $searched_table_data;
    }

    public function filter_city_table_data($table_data, $filter_city_key)
    {

        $filtered_by_city = array_filter($table_data, function ($row) use ($filter_city_key) {
            if ( strtolower($row['city']) == $filter_city_key ) {
                return true;
            }
        });

        return $filtered_by_city;

    }

    public function filter_purchased_product_table_data($table_data, $filter_purchased_product_key)
    {

        $filtered_by_purchased_product = array_filter($table_data, function ($row) use ($filter_purchased_product_key) {

            if ( array_key_exists( $filter_purchased_product_key , $row['products'])  ) {
                return true;
            }

            return DS_Helpers::has_bought_product($filter_purchased_product_key, $row['ID']);

        });

        return $filtered_by_purchased_product;
    }

    public function filter_duration_table_data($table_data, $filter_duration_key)
    {
        $dateBegin = date('Y-m-d', strtotime("-$filter_duration_key days"));
        $dateEnd = date('Y-m-d', strtotime(date('Y-m-d')));

        $filtered_by_duration = array_filter($table_data, function ($row) use ($dateBegin, $dateEnd) {

            $orrderDate = date('Y-m-d', strtotime(date($row['date_created'])));

            if (($orrderDate >= $dateBegin) && ($orrderDate <= $dateEnd)){
                // echo "($orrderDate) is between ($dateBegin) and ($dateEnd) <br>";
                return true;
            }

        });

        return $filtered_by_duration;
    }

    public function get_bulk_actions()
    {

        $actions = array(
            'bulk-type' => 'Type SMS',
        );

        return $actions;
    }

    public function handle_table_actions($request)
    {
        $customers = explode(',', $request['phones']);
        foreach ($customers as $key => $customer_id) {
            DS_TELENOR_API::send_sms($customer_id, $request['sms']);
        }
    }
}
