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
            add_action('wp_ajax_rm_resource_status' , [ $this, 'rm_resource_status' ]);
	
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
                    $designation = wp_get_post_terms($id, 'designation');
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
                $resource_name = get_the_title($resource_id);
                $project_name = get_the_title($project_id);

                $projects_resources    = $wpdb->prefix.'projects_resources';
                $projects_resources_result = $wpdb->get_results( " SELECT * FROM $projects_resources WHERE resource_id = $resource_id and project_id = $project_id and status = 1" ); 
                $projects_resources_id = $projects_resources_result[0]->ID;
                $project_allocation = $projects_resources_result[0]->allocation;
                $project_status = $projects_resources_result[0]->status;

                $resources_allocation    = $wpdb->prefix.'resources_allocation';
                $resources_allocation_result = $wpdb->get_results( " SELECT * FROM $resources_allocation WHERE resource_id = $resource_id" );
                $resources_allocation_id = $resources_allocation_result[0]->ID;
                $resource_allocation = $resources_allocation_result[0]->allocation;
                $project_status = get_post_meta($project_id,"project_status",true);

                if(!$projects_resources_result){ 
                    if($project_status != "Schedule"){ 
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
    
                        $updated_allocation = $resource_allocation + $allocation;
    
                        $insert_record = $wpdb->update(
                            $resources_allocation, array(
                                'allocation'        => $updated_allocation,
                            ), array( 
                                'ID' => $resources_allocation_id 
                            )
                        );
                    }      
                }

                $projects_resources    = $wpdb->prefix.'projects_resources';
                $projects_resources_resultt = $wpdb->get_results( " SELECT * FROM $projects_resources WHERE resource_id = $resource_id and project_id = $project_id and status = 2" ); 
                if(!$projects_resources_resultt){ 
                    
                    if($project_status == "Schedule"){ 
                        $insert_record = $wpdb->insert(
                            $projects_resources, array(
                                'project_id'        => $project_id,
                                'project_name'      => $project_name,
                                'resource_id'       => $resource_id,
                                'resource_name'     => $resource_name,
                                'allocation'        => $allocation,
                                'status'            => 2,
                            ), 
                        );
                    }
                }
            
                if ($insert_record) {
                    $response['message'] = "Project successfully assigned"; 
                    $response['status'] = true; 
                }
                else{
                    $response['message'] = "Value is incorrect or Already Inserted"; 
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
                $quary = "SELECT * FROM $resources_allocation where resource_name like '%$resource_name%'";
                $resources_allocation_results = $wpdb->get_results($quary);

                if(!$resources_allocation_results && $resource_name == ""){
                    $quary = "SELECT * FROM $resources_allocation"; 
                    $resources_allocation_results = $wpdb->get_results($quary);
                }    
                
                foreach($resources_allocation_results as $key => $resources_allocation_result){
                    $resource_id = $resources_allocation_result->resource_id; 
                    $allocation = $resources_allocation_result->allocation;
                    $resource_name = get_the_title($resource_id);
                    $designation = get_post_meta($resource_id,"resource_position",true);
                    $level = get_post_meta($resource_id,"level",true);
                    $manager_name = get_post_meta($resource_id,"manager_name",true);

                    if($allocation >= 100) { 
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
                            <td > {$designation} ($level) </td>
                            <td > {$manager_name} </td>
                            <td class='{$allocation_class}'> {$allocation} % Allocated </td>
                            <td class='{$availability_class}'> {$availability} % Available  </td>
                        </tr> 
                    ";
                }

                if(!$resources_allocation_results) {   
                    $response['table'] .= "
                    <tr>
                        <td class='manage-column' colspan='5'> Record Not Found</td>                    
                    </tr> 
                    "; 
                }

                return $this->response_json($response);
            }
            
            if($availability=="Unchecked" && $resource_name == "" && $project_name == "") {
                global $wpdb;
                $projects_resources = $wpdb->prefix.'projects_resources';
                $quary = "SELECT * FROM $projects_resources WHERE status = 1";
                $projects_resources_results = $wpdb->get_results($quary);

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
                    $designation = get_post_meta($resource_id,"resource_position",true);
                    $level = get_post_meta($resource_id,"level",true);
                    $manager_name = get_post_meta($resource_id,"manager_name",true);

                        $response['table'] .= "
                            <tr>
                                <td class='manage-column'> {$resourse_name} </td>
                                <td class='manage-column'> {$designation} ($level) </td>
                                <td class='manage-column'> {$manager_name} </td>
                                <td class='manage-column'> {$project_name} </td>
                                <td class='manage-column'> {$date_deadline} </td>                       
                                <td class='manage-column'> Assigned </td>
                                <td class='manage-column'> {$allocation} </td>
                            </tr> 
                        ";                
                }
                
                if(!$projects_resources_results) {
                    $response['table'] .= "
                        <tr>
                            <td class='manage-column' colspan='7'> Record Not Found </td>
                        </tr> 
                    "; 
                }

                return $this->response_json($response);
            }

            if($availability=="Unchecked") {
                global $wpdb;
                $projects_resources = $wpdb->prefix.'projects_resources';
                $quary = "SELECT * FROM $projects_resources WHERE status = 1 and resource_name = '{$resource_name}' and project_name = '{$project_name}'";
                $projects_resources_results = $wpdb->get_results($quary);

                if(!$projects_resources_results) {
                    $quary = "SELECT * FROM $projects_resources WHERE status = 1 and (resource_name = '{$resource_name}' or project_name = '{$project_name}')";
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
                    $designation = get_post_meta($resource_id,"resource_position",true);
                    $level = get_post_meta($resource_id,"level",true);
                    $manager_name = get_post_meta($resource_id,"manager_name",true);

                        $response['table'] .= "
                            <tr>
                                <td class='manage-column'> {$resourse_name} </td>
                                <td class='manage-column'> {$designation} ($level) </td>
                                <td class='manage-column'> {$manager_name} </td>
                                <td class='manage-column'> {$project_name} </td>
                                <td class='manage-column'> {$date_deadline} </td>                       
                                <td class='manage-column'> Assigned </td>
                                <td class='manage-column'> {$allocation} </td>
                            </tr> 
                        ";                
                }
                
                if(!$projects_resources_results) {
                    $response['table'] .= "
                        <tr>
                            <td class='manage-column' colspan='7'> Record Not Found </td>
                        </tr> 
                    "; 
                }

                return $this->response_json($response);
            }          
        }   
        
        /**
         * Data Searching by Designation Filters.
         * */
        public function rm_resource_designation() {

            $availability = $_REQUEST['availability'];
            $designation = $_REQUEST['designation'];
            if($designation == 'pm'){ $designation = "Project Manager"; }
            if($designation == 'bd'){ $designation = "Backend Developer"; }
            if($designation == 'fd'){ $designation = "Frontend Developer"; }
            if($designation == 'sqa'){ $designation = "Software Quality Assurance"; }
            
            if($availability=="Unchecked") {     
                global $wpdb;
                $args = array (
                    'post_type'              => 'resource',
                    'meta_query' => array(
                        array(
                            'key' => 'resource_position',
                            'value' => $designation,
                        )
                    )
                );
                $post_query = new WP_Query( $args );                
              
                foreach($post_query->posts as $resource) {
                $resourc_id = $resource->ID;
                global $wpdb;
                $projects_resources = $wpdb->prefix.'projects_resources';
                $quary = "SELECT * FROM $projects_resources WHERE status = 1 and resource_id = $resourc_id";
                $projects_resources_results = $wpdb->get_results($quary);

                    foreach($projects_resources_results as $projects_resources_result){
                        $resource_id = $projects_resources_result->resource_id;
                        $resource_name = $projects_resources_result->resource_name;
                        $project_id = $projects_resources_result->project_id;
                        $post_id = $project_id;
                        $deadline = get_post_meta($post_id,"deadline",true);
                        $project_name = $projects_resources_result->project_name;
                        $allocation = $projects_resources_result->allocation;
                        $date_deadline = date('m-d-Y ',strtotime($deadline));
                        $designation = get_post_meta($resource_id,"resource_position",true);
                        $level = get_post_meta($resource_id,"level",true);
                        $manager_name = get_post_meta($resource_id,"manager_name",true);
                        
                            $response['table'] .= "
                                <tr>
                                    <td class='manage-column'> {$resource_name} </td>
                                    <td class='manage-column'> {$designation} ($level)   </td>
                                    <td class='manage-column'> {$manager_name} </td>
                                    <td class='manage-column'> {$project_name} </td>
                                    <td class='manage-column'> {$date_deadline} </td>  
                                    <td class='manage-column'> Assigned </td>
                                    <td class='manage-column'> {$allocation} </td>
                                </tr> 
                            ";
                    }
                                                     
                }
                
                if(!$projects_resources_results && $response == null) {
                    $response['table'] .= "
                        <tr>
                            <td class='manage-column' colspan='7'> Record Not Found </td>
                        </tr> 
                    "; 
                }
                return $this->response_json($response);
            }

            if($availability=="checked") {
                global $wpdb;
                $args = array (
                    'post_type'              => 'resource',
                    'meta_query' => array(
                        array(
                            'key' => 'resource_position',
                            'value' => $designation,
                        )
                    )
                );
                $post_query = new WP_Query( $args );  
                
                foreach($post_query->posts as $resource) {
                    $resourc_id = $resource->ID;
                    global $wpdb;
                    $resource_allocation = $wpdb->prefix.'resources_allocation';
                    $quary = "SELECT * FROM $resource_allocation WHERE resource_id = $resourc_id";
                    $resources_allocation_results = $wpdb->get_results($quary);
               
                    foreach($resources_allocation_results as $resources_allocation_result){  
                        $resource_id = $resources_allocation_result->resource_id; 
                        $allocation = $resources_allocation_result->allocation;
                        $resource_name = get_the_title($resource_id);
                        $designation = get_post_meta($resource_id,"resource_position",true);
                        $level = get_post_meta($resource_id,"level",true);
                        $manager_name = get_post_meta($resource_id,"manager_name",true);

                        if($allocation >= 100) { 
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
                                    <td > {$designation} ($level) </td>
                                    <td > {$manager_name} </td>
                                    <td class='{$allocation_class}'> {$allocation} % Allocated </td>
                                    <td class='{$availability_class}'> {$availability} % Available  </td>
                                </tr> 
                            ";
                    }                      
                }

                if(!$resources_allocation_results) {
                        
                    $response['table'] .= "
                    <tr>
                        <td class='manage-column' colspan='5'> Record Not Found</td>                    
                    </tr> 
                    "; 
                }
                return $this->response_json($response);  
            }
        }  
        
        /**
         * Ajax Resource Status Filters.
         * */
        public function rm_resource_status() {
            
            $status = $_REQUEST['status'];   
           
            if($status == 0){
                
                global $wpdb;
                $projects_resources = $wpdb->prefix.'projects_resources';
                $quary = "SELECT * FROM $projects_resources WHERE status = $status";
                $projects_resources_results = $wpdb->get_results($quary);
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
                    $designation = get_post_meta($resource_id,"resource_position",true);
                    $level = get_post_meta($resource_id,"level",true);
                    $manager_name = get_post_meta($resource_id,"manager_name",true);
    
                        $response['table'] .= "
        
                            <tr>
                                <td class='manage-column'> {$resourse_name} </td>
                                <td class='manage-column'> {$designation} ($level) </td>
                                <td class='manage-column'> {$manager_name} </td>
                                <td class='manage-column'> {$project_name} </td>
                                <td class='manage-column'> {$date_deadline} </td>                       
                                <td class='manage-column'> Un Assigned </td>
                                <td class='manage-column'> {$allocation} </td>
                            </tr> 
                        ";                
                }
                
                if(!$projects_resources_results) {
                    $response['table'] .= "
                        <tr>
                            <td class='manage-column' colspan='7'> Record Not Found </td>
                        </tr> 
                    "; 
                }
                return $this->response_json($response);
            }
        }
    }
}
$RM_Main = new RM_Main();

