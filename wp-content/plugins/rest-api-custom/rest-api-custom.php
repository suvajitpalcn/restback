<?php
/*
Plugin Name: Rest API Custom
Plugin URI: http://crowdfavorite.com
Description: Add Rest API support for custom post types and custom taxonomy.
Version: 1.0.1
Author: Crowd Favorite
Author URI: https://crowdfavorite.com/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: rac
*/

/**
 * Add REST API support to an already registered post type.
 */
function rac_custom_post_type_rest_support() {
	global $wp_post_types;

	//be sure to set this to the name of your post type!
	$post_types = array( 'tribe_events', 'tribe_venue', 'tribe_organizer' );

	for( $i = 0; $i < count( $post_types ); $i++ ) {
		$post_type_name = $post_types[$i];
		if( isset( $wp_post_types[ $post_type_name ] ) ) {
			$wp_post_types[$post_type_name]->show_in_rest = true;
			$wp_post_types[$post_type_name]->rest_base = $post_type_name;
			$wp_post_types[$post_type_name]->rest_controller_class = 'WP_REST_Posts_Controller';
		}
	}

}
add_action( 'init', 'rac_custom_post_type_rest_support', 25 );

/**
 * Add REST API support to an already registered taxonomy.
 */
function rac_custom_taxonomy_rest_support() {
	global $wp_taxonomies;

	//be sure to set this to the name of your taxonomy!
	$taxonomies = array( 'tribe_events_cat' );

	for( $i = 0; $i < count( $taxonomies ); $i++ ) {
		$taxonomy_name = $taxonomies[$i];
		if ( isset( $wp_taxonomies[ $taxonomy_name ] ) ) {
			$wp_taxonomies[ $taxonomy_name ]->show_in_rest = true;
			$wp_taxonomies[ $taxonomy_name ]->rest_base = $taxonomy_name;
			$wp_taxonomies[ $taxonomy_name ]->rest_controller_class = 'WP_REST_Terms_Controller';
		}
	}


}

add_action( 'init', 'rac_custom_taxonomy_rest_support', 25 );

/**
 * Grab all events
 *
 */
function rac_rest_events() {
	$posts = get_posts( array(
		'post_type' => 'tribe_events',
	) );

	if ( empty( $posts ) ) {
		return null;
	}

	return $posts;
}

/**
 * Grab all event event categories
 *
 */
function rac_rest_event_categories() {
	$categories = get_terms( 'tribe_events_cat' );

	if ( empty( $categories ) ) {
		return null;
	}

	return $categories;
}

function rac_rest_custom_endpoints() {
	register_rest_route( 'wp/v2', '/events', array(
		'methods' => 'GET',
		'callback' => 'rac_rest_events',
	) );
	register_rest_route( 'wp/v2', '/event-categories', array(
		'methods' => 'GET',
		'callback' => 'rac_rest_event_categories',
	) );
}
add_action( 'rest_api_init', 'rac_rest_custom_endpoints' );

/**
 * Grab latest post title by an author!
 *
 * @param array $data Options for the function.
 * @return string|null Post title for the latest,  * or null if none.
 */
// function rac_test_func( $data ) {
// 	$posts = get_posts( array(
// 		'author' => $data['id'],
// 	) );

// 	if ( empty( $posts ) ) {
// 		return null;
// 	}

// 	return $posts[0]->post_title;
// }

// function rac_test_cus_end() {
// 	register_rest_route( 'wp/v2', '/author/(?P<id>\d+)', array(
// 		'methods' => 'GET',
// 		'callback' => 'rac_test_func',
// 	) );
// }
// add_action( 'rest_api_init', 'rac_test_cus_end' );

function rac_rest_prepare( $data, $post, $request ) {
	$_data = $data->data;
	$thumbnail_id = get_post_thumbnail_id( $post->ID );
	$thumbnail = wp_get_attachment_image_src( $thumbnail_id );
	$_data['_thumbnail_url'] = $thumbnail[0];
	$data->data = $_data;
	return $data;
}
add_filter( 'rest_prepare_post', 'rac_rest_prepare', 10, 3 );
add_filter( 'rest_prepare_page', 'rac_rest_prepare', 10, 3 );
