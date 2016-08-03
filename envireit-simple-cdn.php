<?php

/*
Plugin Name: Envire.it Simple CDN Plugin
Plugin URI: 
Description: Envire.it Simple CDN Plugin
Author: Sergio De Falco
Version: 1.0
Author URI: http://www.envire.it/
Text Domain: envireit-simple-cdn
Domain Path: /languages/
License: GPL v3
*/

register_activation_hook( __FILE__ , array( 'Envireit_Simple_CDN_Plugin_Loader', 'activate_plugin' ) );		// Registering plugin activation hook.
register_deactivation_hook( __FILE__, array( 'Envireit_Simple_CDN_Plugin_Loader', 'deactivate_plugin' ) );	// Registering plugin deactivation hook.

/**
 * Load the Multipage Plugin
 *
 * @since 1.0
 */
class Envireit_Simple_CDN_Plugin_Loader {
	/**
	 * Uniquely identify plugin version
	 * Bust caches based on this value
	 *
	 * @since 1.0
	 * @var string
	 */
	const VERSION = '1.0';

	/**
	 * Define Simple CDN default settings.
	 *
	 * @since 1.0
	 *
	 * @var array {}
	 */
	public $simple_cdn_settings_defaults = array(
		'version' 	=> '1.0',
		'css-url'	=> '',
		'js-url'	=> '',
		'styles'	=> array(),
		'scripts'	=> array()
	);

	/**
	 * Let's get it started
	 *
	 * @since 1.0
	 */
	public function __construct() {
		global $wp_scripts;
		// load plugin files relative to this directory.
		$this->plugin_directory = dirname(__FILE__) . '/';

		// Load the textdomain for translations
		load_plugin_textdomain( 'envireit-simple-cdn', true, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Get options with default values.
		$this->options = get_option( 'simple-cdn', $this->simple_cdn_settings_defaults );
		// Maybe this will be replaced.
		$this->options = array_merge( $this->simple_cdn_settings_defaults, $this->options );

		// Check if is admin and intialize the administration.
		if ( is_admin() ) {
			$this->admin_init();
		} else {
			add_action( 'wp', array( &$this, 'public_init' ) );
		}
	}

	/**
	 * Handles actions for the plugin activation
	 *
	 * @since 1.0
	 */
	static function activate_plugin() {
	}

	/**
	 * Handles actions for the plugin deactivation
	 *
	 * @since 1.0
	 */
	static function deactivate_plugin() {
	}

	/**
	 * Intialize the public.
	 *
	 * @since 1.0
	 */
	public function public_init() {
		$styles = $this->options['styles'];
		$scripts = $this->options['scripts'];

		// Loop through handles because it's the main key value
		foreach( $scripts['handle'] as $i => $handle ) {
			wp_deregister_script( $handle );
			wp_register_script( $handle, $scripts['newurl'][ $i ], false, NULL );
			wp_enqueue_script( $handle );
		}
		
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
	}

	/**
	 * Initialize the backend
	 *
	 * @since 1.0
	 */
	public function admin_init() {
		$admin_dir = $this->plugin_directory . 'admin/';

		// Simple CDN Plugin settings loader
		if ( ! class_exists( 'Envireit_Simple_CDN_Plugin_Settings' ) )
			require_once( $admin_dir . 'settings.php' );
		Envireit_Simple_CDN_Plugin_Settings::init();
		
		// Add settings menu to Plugins Page. Check if is_admin is required.
		// ToDo: move inside the admin class
		add_filter( 'plugin_action_links', array( &$this, 'simple_cdn_add_action_links' ), 10, 5 );
	}

	/**
	 * Add Settings Menu inside the Plugins Page
	 *
	 * @since 1.0
	 */
	public function simple_cdn_add_action_links( $actions, $plugin_file ) {
		static $plugin;
		
		if (!isset( $plugin ))
			$plugin = plugin_basename(__FILE__);

		if ($plugin == $plugin_file) {
			$settings = array( 'settings' => '<a href="options-general.php?page=simple-cdn-settings">' . __( 'Settings' ) . '</a>' );
			//$support_link = array( 'support' => '<a href="https://www.envire.it/" target="_blank">' . __( 'Support' ) . '</a>' );					
    		$actions = array_merge( $settings, $actions );
			//$actions = array_merge( $support_link, $actions );
		}
		return $actions;
	}
	
	/**
	 * Load styles applied to public-facing pages
	 *
	 * @since 1.0
	 * @uses enqueue_styles()
	 */
	public function enqueue_scripts() {
		$simple_cdn_options = $this->options;
		// Check if we have an external JavaScript to load.
		if ( null !== $simple_cdn_options['js-url'] && $simple_cdn_options['js-url'] != '' ) {
			$handle   = 'simple-cdn-js';
			wp_enqueue_script( $handle, $simple_cdn_options['js-url'], array(), null, true );
		}

		// Check also if we have an external CSS to load.
		if ( null !== $simple_cdn_options['css-url'] && $simple_cdn_options['css-url'] != '' ) {
			$handle   = 'simple-cdn-css';
			wp_enqueue_style( $handle, $simple_cdn_options['css-url'], array(), self::VERSION, 'all' );
		}
	}
}

/**
 * Load plugin function during the WordPress init action
 *
 * @since 1.0
 */
function simple_cdn_plugin_loader_init() {
	global $simple_cdn_plugin_loader;

	$simple_cdn_plugin_loader = new Envireit_Simple_CDN_Plugin_Loader();
}
add_action( 'init', 'simple_cdn_plugin_loader_init', 0 ); // load before widgets_init at 1
