<?php
/**
 * Remove data written by the Simple CDN Plugin for WordPress after an administrative user clicks "Delete" from the plugin management page in the WordPress administrative interface (wp-admin).
 *
 * @since 1.3
 */

// only execute as part of an uninstall script
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();
?>