<?php
/**
 * Plugin Name: Resource Manager
 * Description: Manage resources and assign projects to resources in an organization
 * Version: 1.0.0.0
 * Text Domain: resource-manager
 *
 * @package resource-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'RM_PLUGIN_FOLDER' ) ) {
	define( 'RM_PLUGIN_FOLDER', 'resource-management' );
}

if ( ! defined( 'RM_PLUGIN_FILE' ) ) {
	define( 'RM_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'RM_PLUGIN_URL' ) ) {
	define( 'RM_PLUGIN_URL', plugin_dir_url( RM_PLUGIN_FILE ) );
}

if ( ! defined( 'RM_ABSPATH' ) ) {
	define( 'RM_ABSPATH', dirname( __FILE__ ) );
}

if ( ! defined( 'RM_PLUGIN_DIR' ) ) {
	define( 'RM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}


require_once RM_ABSPATH . '/includes/class-rm-loader.php';