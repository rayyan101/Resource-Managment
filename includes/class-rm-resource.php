<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('RM_Resource')) {

    class RM_Resource {

        public function __construct() {
            add_action('init', [$this, 'register_resource_post_type']);
			add_action( 'save_post_resource', [$this, 'saving_cpt_resources_to_allocation_table'], 10, 3 );
			add_action( 'after_delete_post', [$this,'wpdocs_my_func'],10, 2 );
        }
		
		public function wpdocs_my_func( $postid, $post ) {

			// We check if the global post type isn't ours and just return
			global $post_type, $wpdb;   
			
			if ( 'resource' !== $post->post_type ) {
				return;
			}

			$resources_allocation    = $wpdb->prefix.'resources_allocation';
			$wpdb->delete(
				$resources_allocation,
				array(
					'resource_id' => $post->ID,
				)
			);

			$projects_resources    = $wpdb->prefix.'projects_resources';
			$wpdb->delete(
				$projects_resources,
				array(
					'resource_id' => $post->ID,
				)
			);
			
		}

        public function register_resource_post_type(){
    	  	$supports = array(
		        'title', // post title
		        'editor', // post content
		        // 'author', // post author
		        // 'thumbnail', // featured images
		        'custom-fields', // custom fields
		        'post-formats', // post formats
	    	);

		    $labels = array(
			    'add_new_item'      => _x('Add New Resource', 'singular'),
			    'name'              => _x('Resources', 'plural'),
			    'singular_name'     => _x('Resource', 'singular'),
			    'menu_name'         => _x('Resources', 'menu-name'),
			    'name_admin_bar'    => _x('Resources', 'admin bar'),
			    'view_item'         => __('View Resources Property'),
			    'all_items'         => __('All Resources'),
			    'search_items'      => __('Search Resources Properties'),
			    'not_found'         => __('No Resources Found.'),
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
                'taxonomies'   => array(
                    'designation'
                ),
			    'capabilities'      => array(
			                'create_posts' => true
			            )
	    	);

		register_post_type('resource', $args);

        $args_taxonomy = array(
            'name'              => _x('Resource Designation', 'plural'),
            'menu_name' => __('Resource Designation', 'resource-management'),
            'add_new_item'      => __( 'Add New Designation', 'resource-management' ),
            'search_items'      => __( 'Search Designation', 'resource-management' ),
            'parent_item'  => null,
            'parent_item_colon' => null,
        );

        register_taxonomy('designation',array('resource'), array(
            'hierarchical' => false,
            'labels'    => $args_taxonomy,
            'public'    => true,
            'show_ui'   => true,
            'show_in_menu'       => 'my_plugin',
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => 'resource' ),
            )
        );

    	}

		public function saving_cpt_resources_to_allocation_table($post_id, $post, $update){
			
			if ( $update ) {	

				global $wpdb, $post;
				$resources_allocation    = $wpdb->prefix.'resources_allocation';
				$resouurce_id = $post_id;
				$resouurce_name = get_the_title($post_id);
				
					$insert_record = $wpdb->update(
						$resources_allocation, array(
							'resource_id'       => $resouurce_id,
							'resource_name'       => $resouurce_name,
						), array (
							'resource_id' => $resouurce_id 
						)
					);

				$projects_resources    = $wpdb->prefix.'projects_resources';
				$resouurce_id = $post_id;
				$resouurce_name = get_the_title($post_id);
					
					$insert_record = $wpdb->update(
						$projects_resources, array(
							'resource_name'       => $resouurce_name,
						), array (
							'resource_id' => $resouurce_id 
						)
					);

				return;
			}
			global $wpdb, $post;
			
			$resources_allocation    = $wpdb->prefix.'resources_allocation';
			$resouurce_id = $post_id;
			$resouurce_name = get_the_title($post_id);
			
				$insert_record = $wpdb->insert(
					$resources_allocation, array(
						'resource_id'       => $resouurce_id,
						'resource_name'       => $resouurce_name,

						'allocation'        => 0,
					),
				);
		}
	}
}

$RM_Resource = new RM_Resource();



