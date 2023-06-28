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
	if ( ! current_user_can( 'manage_options' )) return;
	?>
	<form method="post" action="options.php">
		<?php
				settings_fields( 'wdm-customization-settings' );
				do_settings_sections( 'wdm-customization-setting-page' );
				submit_button();
		?>
	</form>
	<?php
}
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
