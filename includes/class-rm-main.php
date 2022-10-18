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
            add_action('wp_ajax_rm_unassign_resource' , [ $this, 'rm_unassign_resource' ]);
            add_action('wp_ajax_rm_resource_data_searching' , [ $this, 'rm_resource_data_searching' ]);
            add_action('wp_ajax_rm_resource_designation' , [ $this, 'rm_resource_designation' ]);
	
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
                    // $designation = get_the_terms($id,'designation');
                    $designation = wp_get_post_terms($id, 'designation');
                    $all_posts[$id] = $title;
                }
                return $all_posts;
        }

        /**
         * get designation of resources.
         **/
        public static function get_designation_data() {

            $wp_categories = get_categories(
				array(
					'taxonomy' => 'designation',
					'limit'    => -1,
				)
			);
			$categories    = array();

			if ( ! empty( $wp_categories ) ) {
				foreach ( $wp_categories as $wp_category ) {
					$categories[ $wp_category->term_id ] = $wp_category->name;
				}
			}
			return $categories;
       }

        /**
         * Assign a project to resource
         * */
        public function assign_project() {

            if(!$this->empty_element_exists($_POST)){
                $resource_id = $this->rm_validate_input($_POST['resource']);
                $project_id = $this->rm_validate_input($_POST['project']);
                $allocation = $this->rm_validate_input($_POST['allocation']);
                $resource_name = get_the_title($resource_id);
                $project_name = get_the_title($project_id);

                

                global $wpdb;
                $projects_resources    = $wpdb->prefix.'projects_resources';
                $projects_resources_result = $wpdb->get_results( " SELECT * FROM $projects_resources WHERE resource_id = $resource_id and project_id = $project_id and status = 1" ); 
                $projects_resources_id = $projects_resources_result[0]->ID;
                $project_allocation = $projects_resources_result[0]->allocation;
                $project_status = $projects_resources_result[0]->status;

                $resources_allocation    = $wpdb->prefix.'resources_allocation';
                $resources_allocation_result = $wpdb->get_results( " SELECT * FROM $resources_allocation WHERE resource_id = $resource_id" );
                $resources_allocation_id = $resources_allocation_result[0]->ID;
                $resource_allocation = $resources_allocation_result[0]->allocation;

                if(!$projects_resources_result){

                    $insert_record = $wpdb->insert(
                        $projects_resources, array(
                            'project_id'        => $project_id,
                            'project_name'      => $project_name,
                            'resource_id'       => $resource_id,
                            'resource_name'     => $resource_name,
                            'allocation'        => $allocation,
                            'status'            => 1,
                        ), 
                    );

                    $updated_allocation = $resource_allocation + $allocation ;

                    $insert_record = $wpdb->update(
                        $resources_allocation, array(
                            'allocation'        => $updated_allocation,
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
                    // if(){ }
                    $response['message'] = "Please un-assign '{$resource_name}' from '{$project_name}' first"; 
                    $response['status'] = false; 
                }
            }
            else{
                $response['message'] = "One or more field is required"; 
                $response['status'] = false; 
            }
            // print_r($resource_id);
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

        /**
         * Resource Data Searching.
         * */
        public function rm_resource_data_searching() {
            $resource_name = $_REQUEST['resource_name'];
			$project_name = $_REQUEST['project_name'];
            $availability = $_REQUEST['availability'];
            
            if($availability=="checked"){
                
                global $wpdb;
                $resources_allocation    = $wpdb->prefix.'resources_allocation';
                $quary = "SELECT * FROM $resources_allocation where resource_name = '{$resource_name}'"; 
                $resources_allocation_results = $wpdb->get_results($quary);

                if(!$resources_allocation_results && $resource_name == ""){
                    $quary = "SELECT * FROM $resources_allocation"; 
                    $resources_allocation_results = $wpdb->get_results($quary);
                }    
                
                foreach($resources_allocation_results as $key => $resources_allocation_result){

                    $resource_id = $resources_allocation_result->resource_id; 
                    $allocation = $resources_allocation_result->allocation;
                    $resource_name = get_the_title($resource_id);
                    $designation_array = get_the_terms($resource_id,'designation');
                    $designation = $designation_array[0]->name;

                    if($allocation > 100) { 
                        $availability = $allocation - $allocation;
                    }
                    if($allocation < 100){
                        $availability = 100 - $allocation;
                    }                    

                    if($availability >= 75){
                        $availability_class = 'avail-green ';
                    }
                    if($availability <= 75 && $availability >= 50){
                        $availability_class = 'avail-orange';
                    }
                    if($availability <= 50 && $availability >= 0){
                        $availability_class = 'avail-red';
                    }

                    if($allocation >= 75){
                        $allocation_class = 'avail-red ';
                    }
                    if($allocation <= 75 && $allocation >= 50){
                        $allocation_class = 'avail-orange';
                    }
                    if($allocation <= 50 && $allocation >= 0){
                        $allocation_class = 'avail-green';
                    }   
                    // print_r($resources_allocation_results); 
                    $response['table'] .= "
                    
                    <tr>
                        <td > {$resource_name} </td>
                        <td > {$designation} </td>
                        <td class='{$allocation_class}'> {$allocation} % Allocated </td>
                        <td class='{$availability_class}'> {$availability} % Avaialble  </td>
                    </tr> 
                    
                    ";
                }

                if(!$resources_allocation_results) {
                     
                    $response['table'] .= "
                    <tr>
                        <td class='manage-column' colspan='4'> Record Not Found</td>                    
                    </tr> 
                    "; 
                }

                return $this->response_json($response);
            }

            if($availability=="Unchecked") {

                global $wpdb;
                $projects_resources = $wpdb->prefix.'projects_resources';
                $quary = "SELECT * FROM $projects_resources WHERE resource_name = '{$resource_name}' and project_name = '{$project_name}'";
                $projects_resources_results = $wpdb->get_results($quary);

                if(!$projects_resources_results) {
                    $quary = "SELECT * FROM $projects_resources WHERE resource_name = '{$resource_name}' or project_name = '{$project_name}'";
                    $projects_resources_results = $wpdb->get_results($quary);
                }               

                foreach($projects_resources_results as $key => $projects_resources_result){

                    $resource_id = $projects_resources_result->resource_id;
                    $resourse_name = $projects_resources_result->resource_name;
                    $project_id = $projects_resources_result->project_id;
                    $post_id = $project_id;
                    $deadline = get_post_meta($post_id,"deadline",true);
                    $project_name = $projects_resources_result->project_name;
                    $status = $projects_resources_result->status;
                    $allocation = $projects_resources_result->allocation;
                    $date_deadline = date('m-d-Y ',strtotime($deadline));
                    $designation = get_the_terms($resource_id,'designation');        
                    
                    if($status == 1){
                        $response['table'] .= "
        
                        <tr>
                            <td class='manage-column'> {$resourse_name} </td>
                            <td class='manage-column'> {$designation[0]->name} </td>
                            <td class='manage-column'> {$project_name} </td>
                            <td class='manage-column'> {$date_deadline} </td>                       
                            <td class='manage-column'> Working </td>
                            <td class='manage-column'> {$allocation} </td>
                        </tr> 
                        ";                
                    } 
                    if($status == 0){
                        $response['table'] .= "
        
                        <tr>
                            <td class='manage-column'> {$resourse_name} </td>
                            <td class='manage-column'> {$designation[0]->name} </td>
                            <td class='manage-column'> {$project_name} </td>
                            <td class='manage-column'> {$date_deadline} </td>  
                            <td class='manage-column'> Not Working </td>
                            <td class='manage-column'> {$allocation} </td>
                        </tr> 
                        ";          
                    } 
                }
                
                if(!$projects_resources_results) {
                    $response['table'] .= "
                        <tr>
                            <td class='manage-column' colspan='6'> Record Not Found </td>
                        </tr> 
                    "; 
                }

                return $this->response_json($response);
            }          
        }   
        
        /**
         * Ajax Designation Filters.
         * */
        public function rm_resource_designation() {

            $designation = $_REQUEST['designation'];
            $availability = $_REQUEST['availability'];

            if($availability=="Unchecked") {
          
                $args = [
                    'post_type' => 'resource',
                    'tax_query' => [
                        [
                            'taxonomy' => 'designation',
                            'terms' => $designation,

                        ],
                    ],
                ];
                $posts = get_posts($args);

                foreach($posts as $post) {

                $resourc_id = $post->ID;
                global $wpdb;
                $projects_resources = $wpdb->prefix.'projects_resources';
                $quary = "SELECT * FROM $projects_resources WHERE resource_id = $resourc_id";
                $projects_resources_results = $wpdb->get_results($quary);
                
                    foreach($projects_resources_results as $projects_resources_result){
                        // print_r($quary); print_r($projects_resources_results); exit;
                        $resourse_id = $projects_resources_result->resource_id;
                        $resourse_name = $projects_resources_result->resource_name;
                        $project_id = $projects_resources_result->project_id;
                        $post_id = $project_id;
                        $deadline = get_post_meta($post_id,"deadline",true);
                        $project_name = $projects_resources_result->project_name;
                        $allocation = $projects_resources_result->allocation;
                        $date_deadline = date('m-d-Y ',strtotime($deadline));
                        $designation = get_the_terms($resourc_id,'designation');

                        if($status == 1){
                            $response['table'] .= "
                                <tr>
                                    <td class='manage-column'> {$resourse_name} </td>
                                    <td class='manage-column'> {$designation[0]->name}  </td>
                                    <td class='manage-column'> {$project_name} </td>
                                    <td class='manage-column'> {$date_deadline} </td>  
                                    <td class='manage-column'> Working </td>
                                    <td class='manage-column'> {$allocation} </td>
                                </tr> 
                            ";
                        }
                        if($status == 0){
                            $response['table'] .= "
                                <tr>
                                    <td class='manage-column'> {$resourse_name} </td>
                                    <td class='manage-column'> {$designation[0]->name}  </td>
                                    <td class='manage-column'> {$project_name} </td>
                                    <td class='manage-column'> {$date_deadline} </td>  
                                    <td class='manage-column'> Not Working </td>
                                    <td class='manage-column'> {$allocation} </td>
                                </tr> 
                            ";
                        }  
                    }

                    if(!$projects_resources_results) {
                        
                        $response['table'] .= "
                            <tr>
                                <td class='manage-column' colspan='6'> Record Not Found </td>
                            </tr> 
                        "; 
                    }
                    return $this->response_json($response);                    
                }
            }

            if($availability=="checked") {
                
                $args = [
                    'post_type' => 'resource',
                    'tax_query' => [
                        [
                            'taxonomy' => 'designation',
                            'terms' => $designation,

                        ],
                    ],
                ];
                $posts = get_posts($args);
                
                foreach($posts as $post) {
                    
                $resourc_id = $post->ID;
                global $wpdb;
                $resource_allocation = $wpdb->prefix.'resources_allocation';
                $quary = "SELECT * FROM $resource_allocation WHERE resource_id = $resourc_id";
                $resources_allocation_results = $wpdb->get_results($quary);
                // print_r($resource_allocation_results); exit; 
                foreach($resources_allocation_results as $resources_allocation_result){
                    
                    $resource_id = $resources_allocation_result->resource_id; 
                    $allocation = $resources_allocation_result->allocation;
                    $resource_name = get_the_title($resource_id);
                    $designation_array = get_the_terms($resource_id,'designation');
                    $designation = $designation_array[0]->name;
                    // print_r($resource_name); exit; 
                    if($allocation > 100) { 
                        $availability = $allocation - $allocation;
                    }
                    if($allocation < 100){
                        $availability = 100 - $allocation;
                    }                    

                    if($availability >= 75){
                        $availability_class = 'avail-green ';
                    }
                    if($availability <= 75 && $availability >= 50){
                        $availability_class = 'avail-orange';
                    }
                    if($availability <= 50 && $availability >= 0){
                        $availability_class = 'avail-red';
                    }

                    if($allocation >= 75){
                        $allocation_class = 'avail-red ';
                    }
                    if($allocation <= 75 && $allocation >= 50){
                        $allocation_class = 'avail-orange';
                    }
                    if($allocation <= 50 && $allocation >= 0){
                        $allocation_class = 'avail-green';
                    }   
                   
                    $response['table'] .= "
                    
                    <tr>
                        <td > {$resource_name} </td>
                        <td > {$designation} </td>
                        <td class='{$allocation_class}'> {$allocation} % Allocated </td>
                        <td class='{$availability_class}'> {$availability} % Avaialble  </td>
                    </tr> 
                    
                    ";
                }

                    if(!$resources_allocation_results) {
                        
                        $response['table'] .= "
                        <tr>
                            <td class='manage-column' colspan='4'> Record Not Found</td>                    
                        </tr> 
                        "; 
                    }

                    return $this->response_json($response);                    
                }
            }

        }    
    }
}
$RM_Main = new RM_Main();

