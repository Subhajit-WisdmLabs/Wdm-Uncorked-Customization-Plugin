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
		$allowed_pages_slug = array( 'cart', 'checkout', 'my-account', '12-month-plan-save-25', 'signup', 'thank_you' );
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
		$cart       = WC()->cart;
		$cart_items = $cart->get_cart();
		// If cart has more than one product -> remove the first item (which is eventually item added in the past).
		if ( count( $cart_items ) > 1 ) {
			$first_item_key = array_key_first( $cart_items );
			$cart->remove_cart_item( $first_item_key );
		}

		// If cart has more than one quantity of one product -> reduced it to one.
		if ( $cart->get_cart_contents_count() > 1 ) {
			$cart_items_key = array_key_first( $cart->get_cart() );
			$cart->set_quantity( $cart_items_key, 1 );
		}
	}

	/**
	 * Remove Added to cart message.
	 */
	public function remove_added_to_cart_message( $message, $products, $show_qty ) {
		return false;
	}

	/**
	 * Filter the page restrition message to change the product link with add to cart link
	 *
	 * @param string $message_html The html content of the message.
	 *
	 * @param array  $message_args The argument of the content that being restricted.
	 */
	public function checkout_link_in_page_restriction_message2( $message_html, $message_args ) {
		$siteurl_array = explode( '/', get_site_url() );
		$siteurl_regex = '/';
		foreach ( $siteurl_array as $part ) {
			$siteurl_regex .= $part . '\/';
		}
		$siteurl_regex         .= 'product\/[^"]*/';
		$product_urls           = array();
		$product_link           = preg_match_all( $siteurl_regex, $message_html, $product_urls );
		$length_of_general_link = strlen( get_site_url() . '/product/' );
		foreach ( $product_urls[0] as $url ) {
			$product_name = substr( $url, $length_of_general_link, -1 );
			$args         = array(
				'fields'    => 'ids',
				'post_type' => 'product',
				'name'      => $product_name,
			);
			$query        = get_posts( $args );
			if ( 1 === count( $query ) ) {
				$checkout_link = get_site_url() . '/checkout/?add-to-cart=' . $query[0] . '&quantity=1';
				$message_html  = str_replace( $url, $checkout_link, $message_html );
			}
		}
		return $message_html;
	}

	/**
	 * Filter the page restrition message to change the product link with add to cart link
	 *
	 * @param string $products_merge_tag The html content of the message.
	 * @param array  $products The argument of the content that being restricted.
	 * @param string $message the current message where {products} is found.
	 * @param array  $args optional message arguments.
	 */
	public function checkout_link_in_page_restriction_message( $products_merge_tag, $products, $message, $args ) {
		$siteurl_array = explode( '/', get_site_url() );
		$siteurl_regex = '/';
		foreach ( $siteurl_array as $part ) {
			$siteurl_regex .= $part . '\/';
		}
		$siteurl_regex         .= 'product\/[^"]*/';
		$product_urls           = array();
		$product_link           = preg_match_all( $siteurl_regex, $products_merge_tag, $product_urls );
		$length_of_general_link = strlen( get_site_url() . '/product/' );
		$length                 = count( $product_urls );
		for ( $cnt = 0; $cnt < $length; $cnt++ ) {
			$checkout_link      = get_site_url() . '/checkout/?add-to-cart=' . $products[ $cnt ] . '&quantity=1';
			$products_merge_tag = str_replace( $product_urls[ $cnt ], $checkout_link, $products_merge_tag );
		}
		return $products_merge_tag;
	}

	/**
	 * Profile pic upload option in my account page
	 */
	public function add_profile_pic_upload_option_in_my_profile_tab() {
		global $wp;
		$current_url         = home_url( add_query_arg( array(), $wp->request ) );
		$my_account_page_url = wc_get_page_permalink( 'myaccount', false );

		$profile_fields_area_endpoint = get_option( 'woocommerce_myaccount_profile_fields_area_endpoint', false ); // my profile endpoint url.

		if ( ! is_plugin_active( 'woocommerce-memberships/woocommerce-memberships.php' ) || ! is_plugin_active( 'one-user-avatar/one-user-avatar.php' ) ) {
			return;
		}
		if ( ! $my_account_page_url || ! $profile_fields_area_endpoint ) {
			return;
		}

		$my_profile_url = $my_account_page_url . $profile_fields_area_endpoint;
		if ( $current_url === $my_profile_url ) {
			add_filter( 'the_content', array( $this, 'filter_the_content_to_add_profile_pic_upload_shortcode' ), 10, 1 );
		}
	}

	/**
	 * Placed the avatar upload shortcode om my account page.
	 *
	 * @param string $content content of the page.
	 */
	public function filter_the_content_to_add_profile_pic_upload_shortcode( $content ) {
		return $content . do_shortcode( '[avatar_upload]' );
	}
}
