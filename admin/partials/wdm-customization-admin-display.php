<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wisdmlabs.com
 * @since      1.0.0
 *
 * @package    Wdm_Customization
 * @subpackage Wdm_Customization/admin/partials
 */

?>

<?php
/**
 * Redirect non-logged in user to a specific page*/
function menu_page_callback() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<form method="post" action="options.php">
			<?php
					settings_fields( 'wdm_customization_settings' );
					do_settings_sections( 'wdm_customization' );
					submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Callback of the setting section*/
function wdm_redirect_non_logged_in_user_section_callback() {
	// Optional function.
}

/**
 * Setting Default value of the option
 **/
function wdm_select_redirect_page_default_value() {
	$page_id = get_option( 'page_on_front' );
	if ( 0 === $page_id ) {
		$page_id = get_option( 'page_for_posts' );
	}
	return $page_id;
}

/**
 * Callback to display the setting field
 **/
function wdm_select_redirect_page_callback() {
	$redirect_page = get_option( 'wdm_redirect_page_non_logged_in_user', wdm_select_redirect_page_default_value() );
	$redirect_page = (int) $redirect_page;
	?>
	<select name="wdm_redirect_page_non_logged_in_user">
		<?php
		$pages = get_pages();
		if ( null !== $pages ) {
			foreach ( $pages as $page ) {
				$selected = ( $page->ID === $redirect_page ) ? 'selected' : '';
				echo '<option value="' . esc_attr( $page->ID ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( $page->post_title ) . '</option>';
			}
		}
		?>
	</select>
	<?php
}
/**
 * Add membership roles in menu option
 *
 * @param array $roles roles of the "Nav Menu Roles" plugin.
 * @return array
 */
function wdm_get_membership_plans( $roles ) {
	$memberships      = wc_memberships_get_membership_plans();
	$membership_roles = array();
	if ( ! empty( $memberships ) ) {
		foreach ( $memberships as $membership ) {
			$key = 'wc_membership_' . $membership->id;
			$membership_roles[ $key ] = $membership->name;
		}
	}
	return array_merge( $roles, $membership_roles );
}

?>
