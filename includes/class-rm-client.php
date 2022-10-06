<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('RM_Client')) {

    class RM_Client {

        public function __construct() {
            add_action('init', [$this, 'register_client_post_type']);
        }

        public function register_client_post_type(){
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



