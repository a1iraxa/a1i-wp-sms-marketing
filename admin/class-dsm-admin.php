<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/a1iraxa
 * @since      1.0.0
 *
 * @package    Dsm
 * @subpackage Dsm/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Dsm
 * @subpackage Dsm/admin
 * @author     DigitSol <aligcs324@gmail.com>
 */
class Dsm_Admin {

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
		$this->includes();

		add_action('wp_ajax_send_sms', [$this, 'send_sms_hanlder']);
		add_action('wp_ajax_nopriv_send_sms', [$this, 'send_sms_hanlder']);

	}

	/**
	 * Send SMS
	 *
	 * @access public
	 * @return void
	 * @since 1.1
	 */
	public function send_sms_hanlder()
	{

		$posted = array();

		parse_str($_POST['form'], $posted);

		$response = [
			'success' => false,
			'redirect' => false,
			'msg' => 'Falied!',
		];

		$message_id = DS_TELENOR_API::send_sms($_POST['customer'], $_POST['message']);

		$response = [
			'success' => true,
			'redirect' => false,
			'msg' => $message_id,
			'data' => $_POST
		];
		wp_send_json($response);
	}
	/**
	 * Include classes
	 *
	 * @access public
	 * @return void
	 */
	public function includes() {}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style($this->plugin_name.'-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css', array(), $this->version, 'all' );
		// wp_enqueue_style($this->plugin_name.'-data-table', '//cdn.datatables.net/v/dt/dt-1.10.20/datatables.min.css', array(), $this->version, 'all' );

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/dsm-admin.css', array(), $this->version, 'all' );


	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// wp_enqueue_script($this->plugin_name.'-data-table', '//cdn.datatables.net/v/dt/dt-1.10.20/datatables.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script($this->plugin_name.'-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery'), $this->version, false );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/dsm-admin.js', array( 'jquery', $this->plugin_name.'-select2' ), $this->version, false );

		$dsm_ajax_data = array(
			'ajax_url' => admin_url('admin-ajax.php')
		);
		wp_localize_script( $this->plugin_name, 'dsm_ajax_object', $dsm_ajax_data );

	}

}
