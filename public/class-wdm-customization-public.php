<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wisdmlabs.com
 * @since      1.0.0
 *
 * @package    Wdm_Customization
 * @subpackage Wdm_Customization/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wdm_Customization
 * @subpackage Wdm_Customization/public
 * @author     WisdmLabs <subhajit.bera@wisdmlabs.com>
 */
class Wdm_Customization_Public {

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
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wdm-customization-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wdm-customization-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Redirect non-logged in user to a specific page
	 * */
	public function redirect_non_logged_in_user() {
		global $post;
		$page_slug          = $post->post_name;
		$allowed_pages_slug = array( 'cart', 'checkout', 'login', '12-month-plan-save-25', 'signup', 'thank_you' );
		$redirected_page_id = (int) get_option( 'wdm_redirect_page_non_logged_in_user' );

		if ( 0 === $redirected_page_id ) {
			return;
		}

		if ( ! is_user_logged_in() && ! is_page( $redirected_page_id ) && ! in_array( $page_slug, $allowed_pages_slug, true ) ) {
			wp_safe_redirect( get_permalink( $redirected_page_id ), 302 );
			exit;
		}
	}

	/**
	 * Check "Nav menu roles" plugin is activated and add call the hooks to add WooCommerce membership plans as role
	 */
	public function wdm_membership_menus_public() {
		if ( ! is_plugin_active( 'woocommerce-memberships/woocommerce-memberships.php' ) || ! is_plugin_active( 'nav-menu-roles/nav-menu-roles.php' ) ) {
			return;
		}
		add_filter( 'nav_menu_roles_item_visibility', array( $this, 'wdm_nav_menu_visibility_check' ), 10, 2 );
	}

	/**
	 * Get all the membership plans and checked the visibility rules of each nav menu
	 *
	 * @param boolean $visible visibilty rule of the nav menu.
	 * @param array   $item nav menu item.
	 * @return boolean
	 */
	public function wdm_nav_menu_visibility_check( $visible, $item ) {

		if ( ! $visible && isset( $item->roles ) && is_array( $item->roles ) ) {
			// Get specific WooCommerce membership roles.
			$memberships = preg_grep( '/^wc_membership_*/', $item->roles );
			if ( count( $memberships ) > 0 ) {
				foreach ( $memberships as $membership ) {
					$visible = $this::wdm_is_user_have_membership( $membership );
					if ( $visible ) {
						break;
					}
				}
			}
		}
		return $visible;
	}

	/**
	 * Check if current user have active membership required for the nav menu
	 *
	 * @param array $membership membership rule required to view the current nav menu.
	 * @return boolean
	 */
	public function wdm_is_user_have_membership( $membership = false ) {
		$current_user_id = get_current_user_id();
		if ( ! $current_user_id || ! $membership ) {
			return false;
		}
		$membership_id = substr( $membership, 14 ); // Get only the membership id.
		return wc_memberships_is_user_active_member( $current_user_id, $membership_id );
	}

	/**
	 * Shortcode for product link redirected to checkout page
	 *
	 * @param array $atts - shortcode attributes.
	 */
	public function product_checkout_link( $atts ) {
		$atts       = shortcode_atts( array( 'product_id' => '0' ), $atts, 'wdm_wc_product_checkout' );
		$product_id = $atts['product_id'];
		$product    = wc_get_product( $product_id );
		if ( empty( $product ) ) {
			return '#';
		}

		$product_checkout_link = get_site_url() . '/checkout/?add-to-cart=' . $product_id;
		return $product_checkout_link;
	}

	/**
	 * Remove other items from the cart when new item is added and set the quantity to one.
	 */
	public function set_cart_item_quantity( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		$cart = WC()->cart;
		$cart_items = $cart->get_cart();
		// If cart has more than one product -> remove the first item (which is eventually item added in the past).
		if ( count( $cart_items ) > 1 ) {
			$first_item_key = array_key_first( $cart_items );
			$cart->remove_cart_item( $first_item_key );
		}

		// If cart has more than one quantity of one product -> reduced it to one.
		if ( $cart->get_cart_contents_count() > 1 ) {
			$cart_item_key = array_key_first( $cart->get_cart() );
			$cart->set_quantity( $cart_item_key, 1 );
		}
	}

	/**
	 * Remove Added to cart message.
	 */
	public function remove_added_to_cart_message( $message, $products, $show_qty ) {
		return false;
	}

}
