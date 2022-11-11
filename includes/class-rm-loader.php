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
             $this->includes();
            add_action( 'admin_init', [$this,'add_roles']);
            add_action('admin_menu',[$this, 'remove_menus']);
            add_action('admin_enqueue_scripts', [$this, 'admin_style_and_scripts']);
            add_action( 'admin_menu', [$this, 'resource_manager_admin_menu']);
            add_action( 'init', [$this, 'create_sql_tables']);
        }

        /**
         * Enqueue Scripts and Style.
         */
        public function admin_style_and_scripts() {
            wp_enqueue_style( 'rm-style', RM_PLUGIN_URL . 'assets/css/style.css' );
            wp_enqueue_script('rm-script', RM_PLUGIN_URL . 'assets/js/script.js', array('jquery'), wp_rand());
            wp_localize_script('rm-script', 'localize', array('ajaxurl' => admin_url('admin-ajax.php')));   
            wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0-rc.0');
            wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', 'jquery', '4.1.0-rc.0');
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
            if(current_user_can('administrator')){
                add_submenu_page( 'resource_manager', 'Resource Management', 'Resource Management', 'manage_options',"filter-resources",[$this, 'filter_resources_page'],0);
            }
        }

        /**
         * Include Template.
         */
        public function filter_resources_page(){
            global $wpdb;
            include RM_ABSPATH . '/templates/admin/resource-manager.php'; 
        }

        /**
         * Include Files depends on screen.
         */
        public function includes() {
                include_once 'class-rm-resource.php';
                include_once 'class-rm-project.php';
                // include_once 'class-rm-customfields.php';
                include_once 'class-rm-client.php';
                include_once 'class-rm-main.php';
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
                        resource_name VARCHAR(250),
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
                        `project_name` VARCHAR(225),
                        resource_id BIGINT(20) NOT NULL,
                        `resource_name` VARCHAR(225),     
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

        /**
         * Adding Custom Role 'Team Lead' 'Sales' and 'Project Manager'.
        */
        public function add_roles() {
            add_role( 'teamlead_rol', 'Team Lead', array('read' => true, 'level_1' => true ) );
            $role_obj= get_role('teamlead_rol');
            $role_obj->add_cap('manage_options');
            
            add_role( 'manager_rol', 'Project Manager', array('read' => true, 'level_1' => true ) );
            $role_obj= get_role('manager_rol');
            $role_obj->add_cap('manage_options');

            add_role( 'sales_role', 'Sales', array('read' => true, 'level_1' => true ) );
            $role_obj= get_role('sales_role');
            $role_obj->add_cap('manage_options'); 
        }

        /**
         * Rwmove Manu when user is not admin.
        */
        public function remove_menus(){
            if(!current_user_can('administrator')){
                remove_menu_page('export-personal-data.php');
                remove_menu_page('options-general.php');
                remove_menu_page('edit.php?post_type=acf-field-group');
                
            }   
        } 
    }
}
$RM_Loader = new RM_Loader();