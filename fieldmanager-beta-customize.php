<?php
/**
 * Plugin Name:     Fieldmanager Beta: Customize
 * Plugin URI:      https://www.github.com/dlh01/fieldmanager-beta-customize-context
 * Description:     Add Fieldmanager fields to the Customizer.
 * Author:          David Herrera, Alley Interactive
 * Author URI:      https://www.alleyinteractive.com
 * Text Domain:     fieldmanager-beta-customizer
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Fieldmanager_Beta_Customize
 */

/**
 * Plugin directory path.
 */
define( 'FM_BETA_CUSTOMIZE_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'FM_BETA_CUSTOMIZE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin version.
 */
define( 'FM_BETA_CUSTOMIZE_VERSION', '0.1.0' );

/**
 * Calculate a Fieldmanager context for the Customizer.
 *
 * @param array $calculated_context Array of context and 'type' information.
 */
add_filter( 'fm_calculated_context', function ( $calculated_context ) {
	if ( isset( $_SERVER['PHP_SELF'] ) ) {
		$script = substr( sanitize_text_field( wp_unslash( $_SERVER['PHP_SELF'] ) ), strrpos( sanitize_text_field( wp_unslash( $_SERVER['PHP_SELF'] ) ), '/' ) + 1 );

		if ( ( 'customize.php' === $script ) || is_customize_preview() ) {
			$calculated_context = array( 'customize', null );
		}
	}

	return $calculated_context;
} );

/**
 * "Autoload" our classes.
 *
 * We aren't registering an autoloader because if Fieldmanager core's autoloader
 * is registered first, and it doesn't find a file, it throws an exception.
 */
add_action( 'fm_customize', function () {
	require_once( FM_BETA_CUSTOMIZE_PATH . 'php/context/class-fieldmanager-beta-context-customize.php' );
	require_once( FM_BETA_CUSTOMIZE_PATH . 'php/customize/class-fieldmanager-beta-customize-control.php' );
	require_once( FM_BETA_CUSTOMIZE_PATH . 'php/customize/class-fieldmanager-beta-customize-setting.php' );
	require_once( FM_BETA_CUSTOMIZE_PATH . 'php/demo/class-fieldmanager-beta-customize-demo.php' );
	require_once( FM_BETA_CUSTOMIZE_PATH . 'php/field/class-fieldmanager-beta-customize-richtextarea.php' );
}, 0 );

/**
 * Override the path to some Fieldmanager scripts to use updated versions from this plugin.
 *
 * @param array Arrays of script arguments. @see Fieldmanager_Util_Assets::add_script().
 */
add_filter( 'fm_enqueue_scripts', function ( $scripts ) {
	return array_map(
		function ( $script ) {
			switch ( $script['handle'] ) {
				case 'fm_autocomplete_js' :
				case 'fm_colorpicker' :
				case 'fm_datepicker' :
				case 'fieldmanager_script' :
				case 'fm_richtext' :
					$script['ver']  = FM_BETA_CUSTOMIZE_VERSION;
					$script['path'] = str_replace( fieldmanager_get_baseurl(), FM_BETA_CUSTOMIZE_URL, $script['path'] );
				break;
			}

			return $script;
		},
		$scripts
	);
} );

/**
 * Override the path to some Fieldmanager styles to use updated versions from this plugin.
 *
 * @param array Arrays of stylesheet arguments. @see Fieldmanager_Util_Assets::add_style().
 */
add_filter( 'fm_enqueue_styles', function ( $styles ) {
	return array_map(
		function ( $style ) {
			switch ( $style['handle'] ) {
				case 'fieldmanager_style' :
					$style['ver']  = FM_BETA_CUSTOMIZE_VERSION;
					$style['path'] = str_replace( fieldmanager_get_baseurl(), FM_BETA_CUSTOMIZE_URL, $style['path'] );
				break;
			}

			return $style;
		},
		$styles
	);
} );

/**
 * Enqueue assets managed by Fieldmanager_Util_Assets in the Customizer.
 */
add_action( 'plugins_loaded', function () {
	if ( class_exists( 'Fieldmanager_Util_Assets' ) ) {
		/*
		 * Use a later priority because 'customize_controls_enqueue_scripts'
		 * will, by default, be firing at the default priority when
		 * Fieldmanager_Beta_Customize_Control::enqueue() adds scripts.
		 */
		add_action( 'customize_controls_enqueue_scripts', array( Fieldmanager_Util_Assets::instance(), 'enqueue_assets' ), 20 );
	}
} );

/**
 * Add a field to the Customizer.
 *
 * @param string|array       $args @see Fieldmanager_Beta_Context_Customize.
 *                                 Pass a string to add a Customizer section for
 *                                 this field that uses the string for its title
 *                                 and context defaults for the remaining
 *                                 arguments. Or, pass a full array of arguments
 *                                 for the context.
 * @param Fieldmanager_Field $fm   Field object to add to the Customizer.
 */
function fm_beta_customize_add_to_customizer( $args = array(), $fm ) {
	if ( is_string( $args ) ) {
		$args = array( 'section_args' => array( 'title' => $args ) );
	}

	return new Fieldmanager_Beta_Context_Customize( $args, $fm );
}

/**
 * Instantiate the bundled context demos.
 */
function fm_beta_customize_demo() {
	Fieldmanager_Beta_Customize_Demo::instance();
}