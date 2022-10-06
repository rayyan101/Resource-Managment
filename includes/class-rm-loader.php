<?php

/**
 * Main Loader.
 *
 * @package pong-space-customization
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('RM_Loader')) {

    /**
     * Class RM_Loader.
     */

    class RM_Loader {

        public function __construct() { 
            // $resource_id = 194;
            // $project_id = 195;
            // global $wpdb;
            
            // $projects_resources    = $wpdb->prefix.'projects_resources';
            // $projects_resources_data = $wpdb->get_results( " SELECT allocation FROM $projects_resources WHERE resource_id = $resource_id and project_id = $project_id" );
            // $project_allocation = $projects_resources_data[0]->allocation;
          
            
            // echo "<pre>";
            // print_r($project_allocation); 
            // echo "</pre>";
            
            // die();

            $this->includes();
            add_action('admin_enqueue_scripts', [$this, 'admin_style_and_scripts']);
            add_action( 'admin_menu', [$this, 'resource_manager_admin_menu']);
            add_action( 'init', [$this, 'create_sql_tables']);
        }

        public function admin_style_and_scripts() {
            wp_enqueue_style( 'rm-style', RM_PLUGIN_URL . 'assets/css/style.css' );
            wp_enqueue_script('rm-script', RM_PLUGIN_URL . 'assets/js/script.js', array('jquery'), wp_rand());
            wp_localize_script('rm-script', 'localize', array('ajaxurl' => admin_url('admin-ajax.php')));       
        }

        /**
         * add menu and sub menu pages
         * */
        public function resource_manager_admin_menu(){
            add_menu_page( 
                __( 'RM Codup', 'resource-manager' ),
                'RM Codup',
                'manage_options',
                'resource_manager',
                [$this, 'filter_resources_page'],
                'dashicons-menu',
                6
            ); 
            add_submenu_page( 'resource_manager', 'Resource Management', 'Resource Management', 'manage_options',"filter-resources",[$this, 'filter_resources_page'],0);
            add_submenu_page('resource_manager', 'Designation', 'Designation', 'edit_posts', 'edit-tags.php?taxonomy=designation&post_type=resource',false,2 );

        }

        public function filter_resources_page(){
            global $wpdb;

            // $curentpage = get_query_var('paged');
            // $resources = get_posts([
            //     'post_type' => 'resource',
            //     'post_status' => 'publish',
            //     'numberposts' => -1,
            //     'posts_per_page' => '3',
            //     'paged' => $curentpage
            //     // 'order'    => 'ASC'
            //   ]);
              


            // $resources_allocation    = $wpdb->prefix.'resources_allocation';
            // $results = $wpdb->get_results( "SELECT * FROM $resources_allocation" );
            include RM_ABSPATH . '/templates/admin/resource-manager.php'; 
        }
        /**
         * Include Files depends on screen.
         */
        public function includes() {
            if (is_admin()) {
                // inluding all frontend classes here.
                include_once 'class-rm-resource.php';
                include_once 'class-rm-project.php';
                // include_once 'class-rm-customfields.php';
                include_once 'class-rm-client.php';
                include_once 'class-rm-main.php';
            }
            //  elseif (wp_doing_ajax()) {
            //     // including all ajax classes here.
            //     include_once 'ajax/class-psc-front-ajax.php';
            // } else {
            //     // inluding all admin classes here.
            //     include_once 'admin/class-psc-woocommerce-product.php';
            //     include_once 'admin/class-psc-user-profile.php';
            // }
        }
        /**
         * Create a bridge table to store projects and
         *  there resources alligned ${wpdb->prefix}projects_resources
         * 
         * Create a table to store availability of a resource 
         * ${wpdb->prefix}resources_allocation
        */
        public function create_sql_tables(){

        global $wpdb;
        $table_name_1 = $wpdb->prefix . "resources_allocation";
        $table_name_2 = $wpdb->prefix . "projects_resources";

        $charset_collate = $wpdb->get_charset_collate();

            if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name_1}'" ) != $table_name_1 ) {
                $sql = "CREATE TABLE $table_name_1 (
                        ID BIGINT(20) NOT NULL AUTO_INCREMENT,
                        `resource_id` BIGINT(20) NOT NULL,
                        `allocation` VARCHAR(225),
                        updated_at TIMESTAMP,
                        created_at TIMESTAMP,
                        PRIMARY KEY  (ID)
                )    $charset_collate;";

                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta( $sql );
            }

            if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name_2}'" ) != $table_name_2 ) {
                $sql2 = "CREATE TABLE $table_name_2 (
                        ID BIGINT(20) NOT NULL AUTO_INCREMENT,
                        project_id BIGINT(20) NOT NULL,
                        resource_id BIGINT(20) NOT NULL,
                        `allocation` VARCHAR(225),
                        `status` VARCHAR(225),
                        updated_at TIMESTAMP,
                        created_at TIMESTAMP,
                         PRIMARY KEY (ID)
                )    $charset_collate;";

                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta( $sql2 );
            }
        }
     
    }
}
$RM_Loader = new RM_Loader();