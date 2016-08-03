<?php

/**
 * Display a settings page for Simple CDN Plugin
 *
 * @since 1.0
 */
class Envireit_Simple_CDN_Plugin_Main_Settings {
	/**
	 * Settings page identifier.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'simple-cdn-settings';
	
	/**
	 * Define our option array value.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	const OPTION_NAME = 'simple-cdn';

	/**
	 * The hook suffix assigned by add_submenu_page()
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	protected $hook_suffix = '';
	
	/**
	 * Initialize with an options array.
	 *
	 * @since 1.0
	 *
	 * @param array $options existing options
	 */
	public function __construct( $options = array() ) {
		if ( is_array( $options ) && ! empty( $options ) )
			$this->existing_options = $options;
		else
			$this->existing_options = array();
	}
	
	/**
	 * Add a menu item to WordPress admin.
	 *
	 * @since 1.0
	 *
	 * @uses add_utility_page()
	 * @return string page hook
	 */
	public static function menu_item() {
		$main_settings = new Envireit_Simple_CDN_Plugin_Main_Settings();
		$hook_suffix = add_options_page(
			esc_html( __( 'Simple CDN Settings', 'envireit-simple-cdn' ) ), // page <title>
			'Simple CDN', // menu title
			'manage_options', // capability needed
			self::PAGE_SLUG, // what should I call you?
			array( &$main_settings, 'settings_page' ), // pageload callback
			'none' // to be replaced by Simple CDN dashicon
		);
		// conditional load CSS, scripts
		if ( $hook_suffix ) {
			$main_settings->hook_suffix = $hook_suffix;
			register_setting( $hook_suffix, self::OPTION_NAME, array( 'Envireit_Simple_CDN_Plugin_Main_Settings', 'sanitize_options' ) );
			add_action( 'load-' . $hook_suffix, array( &$main_settings, 'onload' ) );
		}
		return $hook_suffix;
	}
	
	/**
	 * Load stored options and scripts on settings page view.
	 *
	 * @since 1.0
	 *
	 * @uses get_option() load existing options
	 * @return void
	 */
	public function onload() {
		$options = get_option( self::OPTION_NAME );
		if ( ! is_array( $options ) )
			$options = array();
		$this->existing_options = $options;
		$this->settings_api_init();
	}

	/**
	 * Load the settings page.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function settings_page() {
		if ( ! isset( $this->hook_suffix ) )
			return;

		add_action( 'simple_cdn_settings_after_header_' . $this->hook_suffix, array( 'Envireit_Simple_CDN_Plugin_Main_Settings', 'after_header' ) );
		Envireit_Simple_CDN_Plugin_Settings::settings_page_template( $this->hook_suffix, esc_html( __( 'Simple CDN Settings', 'envireit-simple-cdn' ) ) );
	}
	
	/**
	 * Simple CDN after header.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public static function after_header() {
		echo "";
	}
	
	/**
	 * Hook into the settings API.
	 *
	 * @since 1.0
	 *
	 * @uses add_settings_section()
	 * @uses add_settings_field()
	 * @return void
	 */
	private function settings_api_init() {
		if ( ! isset( $this->hook_suffix ) )
			return;

		// Simple CDN main settings
		$section = 'simple-cdn';
		add_settings_section(
			$section,
			'', // no title for main section
			array( &$this, 'section_header' ),
			$this->hook_suffix
		);
		
		add_settings_field(
			'css-url',
			_x( 'CSS URL', 'External CSS URL', 'envireit-simple-cdn' ),
			array( &$this, 'display_css_url' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'css-url' )
		);

		add_settings_field(
			'js-url',
			_x( 'JavaScript URL', 'External JavaScript URL', 'envireit-simple-cdn' ),
			array( &$this, 'display_js_url' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'js-url' )
		);

		$section = 'registered-styles';
		add_settings_section(
			$section,
			esc_html( __( 'Registered Styles', 'envireit-simple-cdn' ) ),
			array( &$this, 'section_header' ),
			$this->hook_suffix
		);

		add_settings_field(
			'existing-css',
			__( 'Styles', 'envireit-simple-cdn' ),
			array( &$this, 'display_styles' ),
			$this->hook_suffix,
			$section,
			array( 'class' => 'existing-files' )
		);

		$section = 'registered-scripts';
		add_settings_section(
			$section,
			esc_html( __( 'Registered Scripts', 'envireit-simple-cdn' ) ),
			array( &$this, 'section_header' ),
			$this->hook_suffix
		);

		add_settings_field(
			'existing-scripts',
			__( 'Scripts', 'envireit-simple-cdn' ),
			array( &$this, 'display_scripts' ),
			$this->hook_suffix,
			$section,
			array( 'class' => 'existing-files' )
		);
	}
	
	/**
	 * Introduction to the main settings section.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function section_header() {
		//echo "";
	}

	/**
	 * Display the CSS URL input field.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function display_css_url() {
		$key = 'css-url';

		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		$id = 'css-url';
		settings_errors( $id );
		echo '<input type="url" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $id . '"';
		if ( $existing_value )
			echo ' value="' . esc_attr( $existing_value ) . '"';
		echo ' maxlength="255" size="40" autocomplete="off" />';

		echo '<p class="description">' . esc_html( __( 'Please insert here an optional external CSS file URL.', 'envireit-simple-cdn' ) ) . '</p>';
	}

	/**
	 * Display the JavaScript URL input field.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function display_js_url() {
		$key = 'js-url';

		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		$id = 'js-url';
		settings_errors( $id );
		echo '<input type="url" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $id . '"';
		if ( $existing_value )
			echo ' value="' . esc_attr( $existing_value ) . '"';
		echo ' maxlength="255" size="40" autocomplete="off" />';

		echo '<p class="description">' . esc_html( __( 'Please insert here an optional external JavaScript file URL.', 'envireit-simple-cdn' ) ) . '</p>';
	}

	/**
	 * Display the Existing Styles table.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function display_styles() {
		$key = 'styles';

		echo '<table id="simple-cdn-styles" class="widefat files"><thead><tr>';
		echo '<td>' . esc_html( __( 'Handle', 'envireit-simple-cdn' ) ) . '</td>';
		echo '<td>' . esc_html( __( 'New URL', 'envireit-simple-cdn' ) ) . '</td>';
		echo '<td>' . esc_html( __( 'Deregister', 'envireit-simple-cdn' ) ) . '</td>';
		echo '</tr></thead><tbody>';

		// Initlialize the counter.	
		$c = 0;

		if ( isset( $this->existing_options[$key] ) && is_array( $this->existing_options[$key] ) ) {
			$existing_values = $this->existing_options[$key];

			// Loop through handles because it's the main key value
			foreach( $existing_values['handle'] as $i => $existing_value ) {
				$id = 'handle';
				if ( ! isset( $existing_value ) || $existing_value == '' )
					continue;

				settings_errors( $id );
				echo '<tr><td class="' . $id . '"><input type="text" name="' . self::OPTION_NAME . '[' . $key . '][' . $id . '][' . $i . ']" id="' . $id . '-' . $i . '"';
				if ( $existing_value )
					echo ' value="' . esc_attr( $existing_value ) . '"';
				echo ' maxlength="255" size="40" autocomplete="off" class="medium-text" /></td>';

				$id = 'newurl';
				if ( isset( $existing_values[$id][$i] ) && $existing_values[$id][$i] )
					$existing_value = $existing_values[$id][$i];
				else
					$existing_value = '';

				settings_errors( $id );
				echo '<td class="' . $id . '"><input type="text" name="' . self::OPTION_NAME . '[' . $key . '][' . $id . '][' . $i . ']" id="' . $id . '-' . $i . '"';
				if ( $existing_value )
					echo ' value="' . esc_attr( $existing_value ) . '"';
				echo ' maxlength="255" size="40" autocomplete="off" class="regular-text" /></td>';

				$id = 'deregister';
				if ( isset( $existing_values[$id][$i] ) && $existing_values[$id][$i] )
					$existing_value = $existing_values[$id][$i];
				else
					$existing_value = '';

				settings_errors( $id );
				echo '<td class="' . $id . '"><label class="screen-reader-text">';
				echo esc_html( __( 'Deregister this style', 'envireit-simple-cdn' ) );
				echo '</label> <input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . '][' . $id . '][' . $i . ']" id="' . $id . '-' . $i . '"';
				checked( $existing_value );
				echo ' /></td></tr>';

				$c++;
			}
		}
	
		$id = 'handle';
		echo '<tr><td class="' . $id . '"><input type="text" name="' . self::OPTION_NAME . '[' . $key . '][' . $id . '][' . $c . ']" id="' . $id . '-' . $c . '"';
		echo ' maxlength="255" size="40" autocomplete="off" class="medium-text" /></td>';

		$id = 'newurl';
		echo '<td class="' . $id . '"><input type="text" name="' . self::OPTION_NAME . '[' . $key . '][' . $id . '][' . $c . ']" id="' . $id . '-' . $c . '"';
		echo ' maxlength="255" size="40" autocomplete="off" class="regular-text" /></td>';

		$id = 'deregister';
		echo '<td class="' . $id . '"><label class="screen-reader-text">';
		echo esc_html( __( 'Deregister this style', 'envireit-simple-cdn' ) );
		echo '</label> <input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . '][' . $id . '][' . $c . ']" id="' . $id . '-' . $c . '" value="1"';
		echo ' /></td></tr>';

		echo '</tbody></table>';
		echo '<p class="add-button"><input id="add-style" class="button" type="button" value="' . esc_html( __( 'Add a new row', 'envireit-simple-cdn' ) ) . '"></p>';
	}

	/**
	 * Display the Existing Scripts table.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function display_scripts() {
		$key = 'scripts';

		echo '<table id="simple-cdn-scripts" class="widefat files"><thead><tr>';
		echo '<td>' . esc_html( __( 'Handle', 'envireit-simple-cdn' ) ) . '</td>';
		echo '<td>' . esc_html( __( 'New URL', 'envireit-simple-cdn' ) ) . '</td>';
		echo '<td>' . esc_html( __( 'In footer', 'envireit-simple-cdn' ) ) . '</td>';
		echo '<td>' . esc_html( __( 'Deregister', 'envireit-simple-cdn' ) ) . '</td>';
		echo '</tr></thead><tbody>';

		// Initlialize the counter.	
		$c = 0;

		if ( isset( $this->existing_options[$key] ) && is_array( $this->existing_options[$key] ) ) {
			$existing_values = $this->existing_options[$key];

			// Loop through handles because it's the main key value
			foreach( $existing_values['handle'] as $i => $existing_value ) {
				$id = 'handle';
				if ( ! isset( $existing_value ) || $existing_value == '' )
					continue;

				settings_errors( $id );
				echo '<tr><td class="' . $id . '"><input type="text" name="' . self::OPTION_NAME . '[' . $key . '][' . $id . '][' . $i . ']" id="' . $id . '-' . $i . '"';
				if ( $existing_value )
					echo ' value="' . esc_attr( $existing_value ) . '"';
				echo ' maxlength="255" size="40" autocomplete="off" class="medium-text" /></td>';

				$id = 'newurl';
				if ( isset( $existing_values[$id][$i] ) && $existing_values[$id][$i] )
					$existing_value = $existing_values[$id][$i];
				else
					$existing_value = '';

				settings_errors( $id );
				echo '<td class="' . $id . '"><input type="text" name="' . self::OPTION_NAME . '[' . $key . '][' . $id . '][' . $i . ']" id="' . $id . '-' . $i . '"';
				if ( $existing_value )
					echo ' value="' . esc_attr( $existing_value ) . '"';
				echo ' maxlength="255" size="40" autocomplete="off" class="regular-text" /></td>';

				$id = 'in-footer';
				if ( isset( $existing_values[$id][$i] ) && $existing_values[$id][$i] )
					$existing_value = $existing_values[$id][$i];
				else
					$existing_value = '';

				settings_errors( $id );
				echo '<td class="' . $id . '"><label class="screen-reader-text">';
				echo esc_html( __( 'Deregister this script', 'envireit-simple-cdn' ) );
				echo '</label> <input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . '][' . $id . '][' . $i . ']" id="' . $id . '-' . $i . '"';
				checked( $existing_value );
				echo ' /></td>';

				$id = 'deregister';
				if ( isset( $existing_values[$id][$i] ) && $existing_values[$id][$i] )
					$existing_value = $existing_values[$id][$i];
				else
					$existing_value = '';

				settings_errors( $id );
				echo '<td class="' . $id . '"><label class="screen-reader-text">';
				echo esc_html( __( 'Deregister this script', 'envireit-simple-cdn' ) );
				echo '</label> <input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . '][' . $id . '][' . $i . ']" id="' . $id . '-' . $i . '"';
				checked( $existing_value );
				echo ' /></td></tr>';

				$c++;
			}
		}
	
		$id = 'handle';
		echo '<tr><td class="' . $id . '"><input type="text" name="' . self::OPTION_NAME . '[' . $key . '][' . $id . '][' . $c . ']" id="' . $id . '-' . $c . '"';
		echo ' maxlength="255" size="40" autocomplete="off" class="medium-text" /></td>';

		$id = 'newurl';
		echo '<td class="' . $id . '"><input type="text" name="' . self::OPTION_NAME . '[' . $key . '][' . $id . '][' . $c . ']" id="' . $id . '-' . $c . '"';
		echo ' maxlength="255" size="40" autocomplete="off" class="regular-text" /></td>';

		$id = 'in-footer';
		echo '<td class="' . $id . '"><label class="screen-reader-text">';
		echo esc_html( __( 'In footer', 'envireit-simple-cdn' ) );
		echo '</label> <input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . '][' . $id . '][' . $c . ']" id="' . $id . '-' . $c . '" value="1"';
		echo ' /></td>';

		$id = 'deregister';
		echo '<td class="' . $id . '"><label class="screen-reader-text">';
		echo esc_html( __( 'Deregister this script', 'envireit-simple-cdn' ) );
		echo '</label> <input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . '][' . $id . '][' . $c . ']" id="' . $id . '-' . $c . '" value="1"';
		echo ' /></td></tr>';

		echo '</tbody></table>';
		echo '<p class="add-button"><input id="add-script" class="button" type="button" value="' . esc_html( __( 'Add a new row', 'envireit-simple-cdn' ) ) . '"></p>';
	}

	/**
	 * Clean user inputs before saving to database.
	 *
	 * @since 0.93
	 *
	 * @param array $options form options values
	 * @return array $options sanitized options
	 */
	public static function sanitize_options( $options ) {
		global $simple_cdn_plugin_loader;
		
		$clean_options = array(); // Fresh options
		$default_options = $simple_cdn_plugin_loader->simple_cdn_settings_defaults; // Default options
		
		$key = 'css-url';
		$clean_options[ $key ] = isset( $options[ $key ] ) ? $options[ $key ] : $default_options[ $key ];

		$key = 'js-url';
		$clean_options[ $key ] = isset( $options[ $key ] ) ? $options[ $key ] : $default_options[ $key ];

		$key = 'styles';
		$clean_options[ $key ] = isset( $options[ $key ] ) && is_array( $options[ $key ] ) ? $options[ $key ] : $default_options[ $key ];

		$key = 'scripts';
		$clean_options[ $key ] = isset( $options[ $key ] ) && is_array( $options[ $key ] ) ? $options[ $key ] : $default_options[ $key ];

		return $clean_options;
	}
}
?>