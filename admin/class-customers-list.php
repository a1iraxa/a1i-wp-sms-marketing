<?php
class DS_Customers_List
{

    // class instance
    static $instance;

    // customer WP_List_Table object
    public $customers_obj;

    /** Singleton instance */
    public static function get_instance()
    {

        if (!isset(self::$instance)) {

            self::$instance = new self();
        }

        return self::$instance;
    }

    // class constructor
    public function __construct()
    {

        add_filter('set-screen-option', [__CLASS__, 'customers_set_screen'], 10, 3);
        add_action('admin_menu', [$this, 'customer_menu']);
    }

    public static function customers_set_screen($status, $option, $value)
    {

        return $value;
    }

    public function customer_menu()
    {

        $hook = add_menu_page(
            'Customers',
            'Customers',
            'manage_options',
            'ds_sms_customers',
            [$this, 'customers_page']
        );

        add_action("load-$hook", [$this, 'customers_screen_option']);
    }
    
    /**
     * Plugin settings page
     */
    public function customers_page()
    {
        ?>
        <div class="wrap">
            <h2>Customers</h2>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <?php $__action = $this->customers_obj->current_action(); ?>

                            <?php if ($__action === 'bulk-type' || $__action === 'type_sms') : ?>

                                <?php
                                if (isset($_REQUEST['_wpnonce']) && !empty($_REQUEST['_wpnonce'])) {

                                    if ($__action === 'type_sms') {

                                        $customers = [$_REQUEST['customer']];

                                    } elseif ($__action === 'bulk-type') {

                                        $customers = $_REQUEST['customers'];
                                    }
                                    $phones = [];
                                    foreach ($customers as $customer_id) {
                                        $phones[] = trim(DS_Customers_Table::get_order_data($customer_id, '_billing_phone'));
                                    }
                                }
                                ?>

                                <form method="get" name="send-sms" id="dsm-type-form">
                                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                                    <input type="hidden" name="action" value="send-sms" />
                                    <input type="hidden" name="customers" value="<?php echo implode(',', $customers); ?>" />
                                    <input type="hidden" name="phones" value="<?php echo implode(',', $phones); ?>" />
                                    <textarea name="sms" id="sms" cols="30" rows="10" placeholder="Type your message here..."></textarea>
                                    <p class="submit">
                                        <input type="submit" name="submit-sms" id="submit" class="button button-primary submit dsm-send-sms" value="Send SMS">
                                    </p>
                                </form>

                                <div class="dsm-all-sent dsm-none">
                                    <a href="#" class="button dsm-all-sent__reset-list">New message to same recipient(s)</a>
                                    <h3 class="dsm-all-sent__msg">Sent to Customer(s)</h3>
                                </div>

                                <ul id="customers-list" class="customers-list">
                                    <?php foreach ($customers as $customer) : ?>

                                        <li id="<?php echo $customer; ?>" class='dsm-customers__mobile sending'><?php echo DS_Customers_Table::get_order_data($customer, '_billing_phone'); ?></li>

                                    <?php endforeach; ?>
                                </ul>

                            <?php else : ?>

                                <form method="get">
                                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                                    <?php

                                        $this->customers_obj->prepare_items();
                                        $this->customers_obj->search_box('search', 'search_id');
                                        $this->customers_obj->display();
                                    ?>
                                </form>

                            <?php endif ?>

                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
<?php
    }
    /**
     * Screen options
     */
    public function customers_screen_option()
    {

        $option = 'per_page';
        $args   = [
            'label'   => 'Customers',
            'default' => 5,
            'option'  => 'customers_per_page'
        ];

        add_screen_option($option, $args);

        $this->customers_obj = new DS_Customers_Table();
    }
}
