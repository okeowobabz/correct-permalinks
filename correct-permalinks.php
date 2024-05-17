<?php
/*
Plugin Name: Correct Permalinks Plugin
Plugin URI:  http://wordpress.com
Description: A plugin to correct the permalinks of an existing custom post type - mailing_list.
Version:     1.0.0
Author:      Okeowo Babatunde
Author URI:  http://okeowobabz.com
License:     GPL2
*/

defined( 'ABSPATH' ) or die( 'Unauthorize access!' );

function update_permalinks() {

// Query for posts
$query = new WP_Query(array(
    'post_type'      => 'mailing_list',
    'posts_per_page' => -1,
));

// regex pattern to match extensions
$pattern = '/\/[^\/]+(\.[^\/]+)$/i';

// Function to check if URL exists
function cpt_url_exists($url) {
    $headers = @get_headers($url);
    return is_array($headers) && strpos($headers[0], '200');
}



// Get and change affected permalinks 
if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $post = get_post($post_id);
        $permalink = get_permalink($post_id);
        $post_name = $post->post_name;

        
        // Check if the permalink is valid
        if (!cpt_is_valid_url($permalink)) {
            // If the permalink is not valid, redirect to homepage
            wp_update_post(array(
                'ID' => $post_id,
                'post_name' => home_url()
            ));
        } else if (empty($post_name)){
            wp_update_post(array(
                'ID' => $post_id,
                'post_name' => home_url()
            ));
        }else if(preg_match($pattern, $post_name)){
            // Remove the file extension
            $new_slug = preg_replace($pattern, '', $post_name);

            // Update the post slug
            wp_update_post(array(
                'ID' => $post_id,
                'post_name' => $new_slug
            ));
        } else if (strpos($permalink, 'http') === 0){
            $new_permalink = str_replace('http', 'https', $permalink);

            // Update the post slug
            wp_update_post(array(
                'ID' => $post_id,
                'post_name' => $new_permalink
            ));
        }
    }
    wp_reset_postdata();
    }
}

add_action('admin_init', 'check_and_convert_permalinks')


?>