<?php
/**
 * Template Name: Page with Related Links and/or Pages
 */

/*
// Two-column

function txwgcap_home_layout( $layout ) {
	return 'content-sidebar';
}

add_filter( 'genesis_pre_get_option_site_layout', 'txwgcap_home_layout' );
*/

/*
// Custom body class
add_filter( 'body_class', 'add_body_class' );

function add_body_class( $classes ) {
	$classes[] = 'department-home';
	return $classes;
}
*/

// Custom post content
add_action( 'genesis_post_content', 'department_body' );
add_action( 'genesis_post_content', 'department_related_items' );
//add_action( 'genesis_before_sidebar_widget_area', 'department_sidebar' );
//remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );


function department_body( $post ) {
	return $post->the_content;
}

genesis();
