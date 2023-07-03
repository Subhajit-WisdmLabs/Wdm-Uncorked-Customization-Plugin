<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wisdmlabs.com
 * @since      1.0.0
 *
 * @package    Wdm_Customization
 * @subpackage Wdm_Customization/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wdm_Customization
 * @subpackage Wdm_Customization/admin
 * @author     WisdmLabs <subhajit.bera@wisdmlabs.com>
 */
class Wdm_Customization_Admin {

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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 *
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wdm_Customization_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wdm_Customization_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wdm-customization-admin.css', array(), $this->version, 'all' );

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
		 * defined in Wdm_Customization_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wdm_Customization_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wdm-customization-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Adding a menu page
	 */
	public function add_plugin_menu_page() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/wdm-customization-admin-display.php';
		add_menu_page(
			'WisdmLabs Customization',
			__( 'WisdmLabs Customization', 'wdm-customization' ),
			'manage_options',
			'wdm_customization',
			'menu_page_callback',
			'dashicons-admin-generic',
			25
		);
	}

	/**
	 * Register settings and field
	 */
	public function add_menu_settings() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/wdm-customization-admin-display.php';
		add_settings_section(
			'wdm_redirect_non_logged_in_user', // Section ID.
			__( 'Redirect Non-LoggedIn User', 'wdm-customization' ), // Section Title.
			'wdm_redirect_non_logged_in_user_section_callback', // Section Callback.
			'wdm_customization' // Page Slug of the Settings Page.
		);

		add_settings_field(
			'wdm_select_redirect_page', // Field ID.
			'Select the page to redirect the non logged in users', // Field Title.
			'wdm_select_redirect_page_callback', // Callback function to display.
			'wdm_customization', // Page slug of the settings page.
			'wdm_redirect_non_logged_in_user' // Section Id to which the field belongs.
		);

		register_setting( 'wdm_customization_settings', 'wdm_redirect_page_non_logged_in_user' );
	}

	/**
	 * Check "Nav menu roles" plugin is activated and add call the hooks to add WooCommerce membership plans as role
	 */
	public function wdm_membership_menus_admin() {
		if ( ! is_plugin_active( 'woocommerce-memberships/woocommerce-memberships.php' ) || ! is_plugin_active( 'nav-menu-roles/nav-menu-roles.php' ) ) {
			return;
		}

		add_filter( 'nav_menu_roles', 'wdm_get_membership_plans' );
	}

}
