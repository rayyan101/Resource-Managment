<?php
/**
 * Main Functions.
 *
 * @package pong-space-customization
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('RM_Main')) {

    /**
     * Class RM_Main.
     */

    class RM_Main {
        public function __construct() {
            add_filter('parent_file', [$this, 'fix_admin_parent_file']);
            add_action( 'wp_ajax_assign_project', [$this, 'assign_project'] );
        }

        /**
         * activate current post type menu
         * */
        public function fix_admin_parent_file($parent_file){
            global $submenu_file, $current_screen;
            // Set correct active/current menu and submenu in the WordPress Admin menu for the "example_cpt" Add-New/Edit/List

            if($current_screen->post_type == 'project') {
                $submenu_file = 'edit.php?post_type=project';
                $parent_file = 'resource_manager';
            }
            if($current_screen->post_type == 'client') {
                $submenu_file = 'edit.php?post_type=client';
                $parent_file = 'resource_manager';
            }
            if($current_screen->post_type == 'resource') {
                $submenu_file = 'edit.php?post_type=resource';
                $parent_file = 'resource_manager';
            }
            return $parent_file;
        }

        /**
         * get data from cpt
         **/
        public static function get_cpt_data($post_type) {
             $args = array(
                    'posts_per_page'   => -1,
                    'post_type'        => $post_type,
                );
             $the_query = new WP_Query( $args );
             $all_posts=[];
                while ( $the_query->have_posts() ) {
                     $the_query->the_post();
                    $id = get_the_ID();
                    $title = get_the_title();
                    $all_posts[$id] = $title;
                }
                return $all_posts;
        }

        /**
         * Assign a project to resource
         * */
        public function assign_project() {

            if(!$this->empty_element_exists($_POST)){
                $resource_id = $this->rm_validate_input($_POST['resource']);
                $project_id = $this->rm_validate_input($_POST['project']);
                $allocation = $this->rm_validate_input($_POST['allocation']);

                global $wpdb;
                $projects_resources    = $wpdb->prefix.'projects_resources';
                $projects_resources_result = $wpdb->get_results( " SELECT ID, allocation FROM $projects_resources WHERE resource_id = $resource_id and project_id = $project_id" );
                
                $projects_resources_id = $projects_resources_result[0]->ID;
                $project_allocation = $projects_resources_result[0]->allocation;
              
                if(!$projects_resources_id) {
                    $insert_record = $wpdb->insert(
                        $projects_resources, array(
                            'project_id'        => $project_id,
                            'resource_id'       => $resource_id,
                            'allocation'        => $allocation,
                            'status'            => 1,
                        ), array(
                            '%d',
                            '%d',
                            '%d',
                            '%d',
                        )
                    );
                }

                if($projects_resources_id) {
                    $insert_record = $wpdb->update(
                        $projects_resources, array(
                            'allocation'        => $allocation,
                            'status'            => 1,
                        ), array( 
                            'id' => $projects_resources_id 
                        )
                    );
                }

                $resources_allocation    = $wpdb->prefix.'resources_allocation';
                $resources_allocation_result = $wpdb->get_results( " SELECT * FROM $resources_allocation WHERE resource_id = $resource_id" );
                $resources_allocation_id = $resources_allocation_result[0]->ID;
                $resource_allocation = $resources_allocation_result[0]->allocation;

                if(!$resources_allocation_id) {
                
                    $insert_record = $wpdb->insert(
                        $resources_allocation, array(
                            'resource_id'       => $resource_id,
                            'allocation'        => $allocation,
                        ), array(
                            '%d',
                            '%d',
                        )
                    );
                }

                $updated_allocation = $resource_allocation + $allocation ;
                $fianal_allocation = $updated_allocation- $project_allocation;

                if($resources_allocation_id) {
                    $insert_record = $wpdb->update(
                        $resources_allocation, array(
                            'allocation'        => $fianal_allocation,
                        ), array( 
                            'ID' => $resources_allocation_id 
                        )
                    );
                }
            
                if ($insert_record) {
                    $response['message'] = "Project successfully assigned"; 
                    $response['status'] = true; 
                }
                else{
                    $response['message'] = "Error inserting into database"; 
                    $response['status'] = false; 
                }
            }
            else{
                $response['message'] = "One or more field is required"; 
                $response['status'] = false; 
            }

            return $this->response_json($response);
        }

        /**
         * Parse string to json on ajax call
         * */
        public function response_json($data) {
            header('Content-Type: application/json');
            echo json_encode($data);
            wp_die();
        }
        /**
         * Validate variable on form submission
         * */
        public function rm_validate_input($input) {
            $input = trim($input);
            $input = stripslashes($input);
            $input = htmlspecialchars($input);
            return $input;
        }

        /**
         * Check if any element is empty in array
         * */
        public function empty_element_exists($arr) {
          return array_search("", $arr) !== false;
        }

       
    }
}
$RM_Main = new RM_Main();