<?php
/**
 * Store settings related to the Envire.it Simple CDN Plugin
 * @since 1.0
 */
class Envireit_Simple_CDN_Plugin_Settings {

	/**
	 * All plugin features supported
	 *
	 * @since 1.0
	 * @var array
	 */
	public static $features = array( 'simple-cdn' => true );

	/**
	 * Add hooks
	 *
	 * @since 1.0
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( 'Envireit_Simple_CDN_Plugin_Settings', 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( 'Envireit_Simple_CDN_Plugin_Settings', 'settings_menu_item' ) );
	}
	
	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 0.93
	 *
	 * @uses wp_enqueue_style()
	 * @return void
	 */
	public static function enqueue_scripts() {
		wp_enqueue_script( 'simple-cdn-admin-js', plugins_url( 'static/js/admin/simple-cdn-admin' . ( ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min' ) . '.js', dirname( __FILE__ ) ), array(), '1.0',  true );
		wp_enqueue_style( 'simple-cdn-admin-css', plugins_url( 'static/css/admin/simple-cdn-admin' . ( ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min' ) . '.css', dirname( __FILE__ ) ), array(), '1.0' );
	}

	/**
	 * Add Multipage Plugin settings to the WordPress administration menu.
	 *
	 * @since 1.0
	 *
	 * @global Envireit_Simple_CDN_Plugin_Loader
	 * @global $submenu array submenu created for the menu slugs
	 * @return void
	 */
	public static function settings_menu_item() {
		// main settings page
		if ( ! class_exists( 'Envireit_Simple_CDN_Plugin_Main_Settings' ) )
			require_once( dirname( __FILE__ ) . '/settings-main.php' );
		
		$menu_hook = Envireit_Simple_CDN_Plugin_Main_Settings::menu_item();
		if ( ! $menu_hook )
			return;
	}
	
	/**
	 * Standardize the form flow.
	 *
	 * @since 1.0
	 *
	 * @uses settings_fields()
	 * @uses do_settings_sections()
	 * @param string $page_slug constructs custom actions. passed to Settings API functions
	 * @param string $page_title placed in a <h2> at the top of the page
	 * @return void
	 */
	public static function settings_page_template( $page_slug, $page_title ) {
		echo '<div class="wrap">';

		/**
		 * Echo content before the page header.
		 *
		 * @since 1.0
		 */
		do_action( 'simple_cdn_settings_before_header_' . $page_slug );
		echo '<h1>' . esc_html( $page_title ) . '</h1>';
		/**
		 * Echo content after the page header.
		 *
		 * @since 1.0
		 */
		do_action( 'simple_cdn_settings_after_header_' . $page_slug );

		// handle general messages such as settings updated up top
		// place individual settings errors alongside their fields
		//settings_errors( 'general' ); /* Commented because this displays two times settings saved */

		echo '<div id="simple-cdn-settings">';
		echo '<form method="post" action="options.php">';

		settings_fields( $page_slug );
		do_settings_sections( $page_slug );

		submit_button();

		echo '</form>';
		echo '</div><!-- #simple-cdn-settings-->';
		echo '<div id="simple-cdn-sidebar">';
		echo '</div><!-- #simple-cdn-sidebar-->';
		echo '</div>';

		/**
		 * Echo content at the bottom of the page.
		 *
		 * @since 1.0
		 */
		do_action( 'simple_cdn_settings_footer_' . $page_slug );
	}
}
?>