<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('RM_Project')) {

    class RM_Project extends RM_Loader{

        public function __construct() {
            add_action('init', [$this, 'register_project_post_type']);
            add_action( 'add_meta_boxes', [$this, 'projects_meta_box']) ;
			add_action('wp_ajax_rm_unassign_resource' , [ $this, 'rm_unassign_resource' ]);
			add_action('wp_ajax_nopriv_rm_unassign_resource' , [ $this, 'rm_unassign_resource']);	
			add_action('acf/save_post', [$this, 'project_status_change']);
        }

		/**
    	 * Creating Project Post Type.
    	 * */
        public function register_project_post_type(){
			if(!current_user_can('administrator')){
				return;
				}
			$supports = array(
		        'title', // post title
		        'editor', // post content
		        // 'author', // post author
		        'thumbnail', // featured images
		        'custom-fields', // custom fields
		        'revisions', // post revisions
		        'post-formats', // post formats
	    	);

		    $labels = array(
			    'add_new_item'      => _x('Add New Project', 'singular'),
			    'name'              => _x('Projects', 'plural'),
			    'singular_name'     => _x('Project', 'singular'),
			    'menu_name'         => _x('Projects', 'menu-name'),
			    'name_admin_bar'    => _x('Projects', 'admin bar'),
			    'view_item'         => __('View Projects Property'),
			    'all_items'         => __('All Projects'),
			    'search_items'      => __('Search Projects'),
			    'not_found'         => __('No Projects Found.'),
		    );

		    $args = array(
			    'supports'          => $supports,
			    'labels'            => $labels,
			    'public'            => true,
			    'query_var'         => true,
			    'rewrite'           => array('slug' => 'resource'),
			    'has_archive'       => true,
			    // 'show_in_admin_bar' => false,
			    'show_in_menu' 		=> 'resource_manager',
			    // 'show_in_nav_menus' => false,
			    'hierarchical'      => false,
			    'map_meta_cap'      => true,
			    'capabilities'      => array(
			                'create_posts' => true
			            )
	    	);

			register_post_type('project', $args);
    	}
    	/**
    	 * Create meta box to display resources of current project
    	 * */
    	public function projects_meta_box(){
    		global $post;
			$screen = 'project';
			add_meta_box('my-meta-box-id','Resources on This Project',[$this, 'assigned_resources_list'],$screen,'normal','high');
    	}
    	
    	/**
    	 * data view of assigned resources to a project
    	 * */
    	public function assigned_resources_list($project){
    		
			global $wpdb ,$post;
			$projects_resources =  $wpdb->prefix . 'projects_resources';
			$project_id = $post->ID;
            $projects_resources = $wpdb->get_results( "SELECT * FROM $projects_resources where project_id = $project_id");
			?>
			<table class="resource-project-data-table">
				<thead>
					<tr>
						<th style="width:9%">
							<h3> Resource ID </h3>
						</th>
						<th style="width:9%">
							<h3> Resource Name </h3>
						</th>
						<th style="width:9%">
							<h3> Allocation % </h3>
						</th>
						<th style="width:9%">
							<h3> Assign Date </h3>
						</th>
						<th  style="width:9%">
							<h3> UnAssign </h3>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php
					foreach($projects_resources as $key => $value) {
						$resource_id = $value->resource_id;
						$allocation = $value->allocation; 
						$assign_date = $value->created_at;
						$status = $value->status;
						$resource_name = get_the_title( $resource_id ); 
						?>
							<tr>
								<td  style="text-align:center;"> 
									<?php echo $resource_id ?> 
								</td>
								<td  style="text-align:center;"> 
									<?php echo $resource_name ?> 
								</td>
								<td  style="text-align:center;"> 
									<?php echo $allocation.' %' ?> 
								</td>
								<td  style="text-align:center;"> 
									<?php echo $assign_date ?> 
								</td>
								<?php 
								if($status == 1){  ?>
									<td  style="width:9%; text-align:center;"> 
										<a class="button button-primary un_assign"  data-pro-id="<?php echo $project_id ?>"   data-res-id="<?php echo $resource_id ?>">Un Assign</a> 
									</td>
								<?php } 
								if($status == 0) { ?>
									<td  style="width:9%; text-align:center;"> 
										<a class="button button-primary"  disabled>Not Working</a>
									</td>
								<?php } ?>
							</tr>
						<?php
					} ?>	
				</tbody>
			</table> 
		<?php 
		}

		/**
    	 * Unassign Resource from project.
    	 * */
		public function rm_unassign_resource() {

			$resource_id = $_REQUEST['resourse_id'];
			$project_id = $_REQUEST['project_id'];
			global $wpdb;
			$projects_resources    = $wpdb->prefix.'projects_resources';
				
				$wpdb->update(
					$projects_resources, array(
						'status'            => 0,
					), array( 
						'resource_id' => $resource_id,
						'project_id' => $project_id
					)
				);
			
			$projects_resources_result = $wpdb->get_results( " SELECT allocation FROM $projects_resources WHERE resource_id = $resource_id and project_id = $project_id  ORDER BY updated_at DESC" );
			$project_allocation = $projects_resources_result[0]->allocation;

			$resources_allocation    = $wpdb->prefix.'resources_allocation';
            $resources_allocation_result = $wpdb->get_results( " SELECT * FROM $resources_allocation WHERE resource_id = $resource_id" );
            
			$resources_allocation_id = $resources_allocation_result[0]->ID;
            $resource_allocation = $resources_allocation_result[0]->allocation;
            $fianal_allocation = $resource_allocation - $project_allocation;

                if($resources_allocation_id) {
					$update_record = $wpdb->update(
                        $resources_allocation, array(
                            'allocation'        => $fianal_allocation,
                        ), array( 
                            'ID' => $resources_allocation_id 
                        )
                    );
                }
		}

		/**
    	 * Releasing All resources from project on Project status changing to Complete.
    	 * 
		 * */
		public function project_status_change($post_id){
			

			global $wpdb, $post;
			$project_status = get_post_meta($post_id,"project_status",true);
		
			if($project_status == "In-Progress"){	
				$project_id = $post_id;
				$projects_resources    = $wpdb->prefix.'projects_resources';
				$quary = "SELECT * FROM $projects_resources WHERE status = 2 and project_id = $project_id";
                $projects_resources_results = $wpdb->get_results($quary);

				foreach($projects_resources_results as $projects_resources_results) {
		
					$resource_id = $projects_resources_results->resource_id;
					$project_allocation = $projects_resources_results->allocation;

						$update_record = $wpdb->update(
							$projects_resources, array(
								'status'       => 1,
							), array (
								'resource_id' => $resource_id,
								'project_id' => $project_id,
							)
						);
				

					$resources_allocation    = $wpdb->prefix.'resources_allocation';
					$quary = "SELECT allocation FROM $resources_allocation where resource_id = $resource_id";
                	$total_allocation_result = $wpdb->get_results($quary);
					$total_allocation = $total_allocation_result[0]->allocation; 
					$allocation = $total_allocation + $project_allocation;

						$update_record = $wpdb->update(
							$resources_allocation, array(
								'allocation'       => $allocation,
		
							), array (
								'resource_id' => $resource_id 
							)
						);
					}
			}
			if($project_status == "Complete"){	
				
				$project_id = $post_id;
				$projects_resources    = $wpdb->prefix.'projects_resources';
				$quary = "SELECT * FROM $projects_resources WHERE status = 1 and project_id = $project_id";
                $projects_resources_results = $wpdb->get_results($quary);
					 
				foreach($projects_resources_results as $projects_resources_results) {
		
					$resource_id = $projects_resources_results->resource_id;
					$project_allocation = $projects_resources_results->allocation;

						$update_record = $wpdb->update(
							$projects_resources, array(
								'status'       => 0,
							), array (
								'resource_id' => $resource_id,
								'project_id' => $project_id,
							)
						);

					$resources_allocation    = $wpdb->prefix.'resources_allocation';
					$quary = "SELECT allocation FROM $resources_allocation where resource_id = $resource_id";
                	$total_allocation_result = $wpdb->get_results($quary);
					$total_allocation = $total_allocation_result[0]->allocation; 
					$allocation = $total_allocation - $project_allocation;

						$update_record = $wpdb->update(
							$resources_allocation, array(
								'allocation'       => $allocation,
		
							), array (
								'resource_id' => $resource_id 
							)
						);
				}
			}
		}
	}         
}

$RM_Project = new RM_Project();



