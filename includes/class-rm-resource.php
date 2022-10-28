<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('RM_Resource')) {

    class RM_Resource {

        public function __construct() {
            add_action('init', [$this, 'register_resource_post_type']);
			add_action( 'after_delete_post', [$this,'rm_delete_resource'],10, 2 );
			add_action('transition_post_status', array( $this, 'so_post_40744782' ), 10, 3 );
        }

		/**
    	 * Inserting Resource Data to Resource Allocation Table on Resource Creating.
		 * */
		function so_post_40744782( $new_status, $old_status, $post ) {
			$post_type = get_post_type($post);
			if ( $new_status == 'publish' && $post_type == "resource" ) {	
				global $wpdb, $post;
				$resouurce_id = $post->ID;
				$post_title = $post->post_title;
				$resouurce_name = get_the_title($resouurce_id);

				$resources_allocation    = $wpdb->prefix.'resources_allocation';
                $resources_allocation_result = $wpdb->get_results( " SELECT * FROM $resources_allocation WHERE resource_id = $resouurce_id" );
                $resources_allocation_id = $resources_allocation_result[0]->ID;

				if($resources_allocation_id) {
					$insert_record = $wpdb->update(
						$resources_allocation, array(
							'resource_id'       => $resouurce_id,
							'resource_name'       => $resouurce_name,
						), array (
							'resource_id' => $resouurce_id 
						)
					);

					$projects_resources    = $wpdb->prefix.'projects_resources';					
					$wpdb->update(
						$projects_resources, array(
							'resource_name'       => $resouurce_name,
						), array (
							'resource_id' => $resouurce_id 
						)
					);
				}
				
				if(!$resources_allocation_id) {
					$resources_allocation    = $wpdb->prefix.'resources_allocation';	
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

		/**
    	 * Deleting Resource Data from Table when Resource Delete.
		 * */
		public function rm_delete_resource( $postid, $post ) {
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

		/**
    	 * Creating Resource Post Type.
		 * */
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
	}
}

$RM_Resource = new RM_Resource();



