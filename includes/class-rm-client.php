<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('RM_Client')) {

    class RM_Client {

        public function __construct() {
            add_action('init', [$this, 'register_client_post_type']);
			add_action( 'add_meta_boxes', [$this, 'client_meta_box']) ;
        }

		/**
    	 * Create meta box to display all projects of this Client.
    	 * */
    	public function client_meta_box(){
    		global $post;
			$screen = 'client';
			add_meta_box('my-meta-box-id','All Project of Client',[$this, 'assigned_resources_list'],$screen,'normal','high');
    	}
    	
    	/**
    	 * View All Projects of Client on Client Page.
    	 * */
    	public function assigned_resources_list($project){
    		
			global $wpdb ,$post;
			$args= array(
				'post_type' => 'project',
				'meta_query' => array(
					array(
						'key' => 'client',
						'value' => $post->ID,
					)
				)
			);
		
			$projects_query = new WP_Query( $args );
			?>
			<table class ="project-data-table">
				<tbody>	
					<?php
						if( $projects_query->post_count){
							foreach($projects_query->posts as $project){	
								?> 
								<tr>
									<td> <h1>
										<?php
											echo $project->post_title;  
										?> </h1>
									</td> 
								</tr>
								<?php		
							}
						}
					?>
				</tbody>
			</table>
			<?php					
		}

		/**
    	 * Creating Client Post Type.
    	 * */
        public function register_client_post_type(){
			if(!current_user_can('administrator')){
				return;
				}
			$supports = array(
		        'title', // post title
		        'editor', // post content
		        // 'author', // post author
		        // 'thumbnail', // featured images
		        'custom-fields', // custom fields
		        'revisions', // post revisions
		        'post-formats', // post formats
	    	);

		    $labels = array(
			    'add_new_item'      => _x('Add New Client', 'singular'),
			    'name'              => _x('Clients', 'plural'),
			    'singular_name'     => _x('Client', 'singular'),
			    'menu_name'         => _x('Clients', 'menu-name'),
			    'name_admin_bar'    => _x('Clients', 'admin bar'),
			    'view_item'         => __('View Clients Property'),
			    'all_items'         => __('All Clients'),
			    'search_items'      => __('Search Clients'),
			    'not_found'         => __('No Clients Found.'),
		    );

		    $args = array(
			    'supports'          => $supports,
			    'labels'            => $labels,
			    'public'            => true,
			    'query_var'         => true,
			    'rewrite'           => array('slug' => 'client'),
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

			register_post_type('client', $args);
    	}
	}
}

$RM_Client = new RM_Client();



