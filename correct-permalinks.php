<?php
/**
Plugin Name: Correct Permalinks Plugin
Plugin URI:  https://www.wordpress.com
Description: A plugin to correct the permalinks of an existing custom post type - mailing_list.
Version:     1.0.0
Author:      Okeowo Babatunde
Author URI:  https://www.okeowobabz.com
License:     GPL2

@package hghd.
 */

defined( 'ABSPATH' ) or die( 'Unauthorize access!' );

/**Permalink.**/
function update_permalinks() {

	// Query for posts.
	$query = new WP_Query(
		array(
			'post_type'      => 'mailing_list',
			'posts_per_page' => -1,
		)
	);

	// regex pattern to match extensions.
	$pattern = '/\/[^\/]+(\.[^\/]+)$/i';

	/**
	 * Function to check if URL exists.
	 *
	 * @param int $url jhgfdh.
	 */
	function cpt_url_exists( $url ) {
		$headers = @get_headers( $url );
		return is_array( $headers ) && strpos( $headers[0], '200' );
	}

	// Get and change affected permalinks.
	if ( $query->have_posts() ) {

		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id   = get_the_ID();
			$post      = get_post( $post_id );
			$permalink = get_permalink( $post_id );
			$post_name = $post->post_name;

			// Check if the permalink is valid.
			if ( ! cpt_url_exists( $permalink ) || empty( $permalink ) ) {
				// If the permalink is not valid, redirect to homepage or empty.
				wp_update_post(
					array(
						'ID'        => $post_id,
						'post_name' => home_url(),
					)
				);
			} elseif ( preg_match( $pattern, $post_name ) ) {
				// Remove the file extension.
				$new_slug = preg_replace( $pattern, '', $post_name );
				// Update the post slug.
				wp_update_post(
					array(
						'ID'        => $post_id,
						'post_name' => $new_slug,
					)
				);
			} elseif ( strpos( $permalink, 'http://' ) === 0 ) {
				// Check if the permalink starts with 'http'.
				$new_permalink = str_replace( 'http://', 'https://', $permalink );
				// Update the post slug.
				wp_update_post(
					array(
						'ID'        => $post_id,
						'post_name' => $new_permalink,
					)
				);
			}
		}
		wp_reset_postdata();
	}
}

add_action( 'admin_init', 'update_permalinks' );
