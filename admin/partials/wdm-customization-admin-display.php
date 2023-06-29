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
	<form method="post" action="options.php">
		<?php
				settings_fields( 'wdm_redirect_page_non_logged_in_user' );
				do_settings_sections( 'wdm-customization' );
				submit_button();
		?>
	</form>
	<?php
}

/**
 * Callback of the setting section*/
function wdm_redirect_non_logged_in_user_section_callback() {
	// Optional function.
}

/**
 * Callback to display the setting field*/
function wdm_select_redirect_page_callback() {
	$redirect_page = get_option( 'wdm_redirect_page_non_logged_in_user' );
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
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
