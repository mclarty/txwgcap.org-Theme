<?php

add_action( 'genesis_meta', 'education_home_genesis_meta' );
/**
 * Add widget support for homepage. If no widgets active, display the default loop.
 *
 */
function education_home_genesis_meta() {
	
	global $paged;
	
	if( $paged < 1 ) {
		if ( is_active_sidebar( 'slider' ) || is_active_sidebar( 'intro' ) || is_active_sidebar( 'featured' ) || is_active_sidebar( 'call-to-action' ) ) {
		
			add_action( 'genesis_before_content', 'education_home_loop_helper', 1 );
	
		}
	}
	
}

function education_home_loop_helper() {
		
		echo '<div id="home-featured">';
		
		genesis_widget_area( 'slider', array( 
		
			'before'	=>	'<div class="slider widget-area">' 
		
		) );

		genesis_widget_area( 'intro', array( 
		
			'before'	=> 	'<div class="intro widget-area"><div class="inner">', 
			'after'	=>	'<div class="clear"></div></div></div><!-- end .intro -->' 
		
		) );
		
		genesis_widget_area( 'featured', array(
		 
			'before'	=>	'<div class="featured widget-area"><div class="inner">', 
			'after'	=>	'<div class="clear"></div></div></div><!-- end .featured -->' 
			
		) );
		
		genesis_widget_area( 'call-to-action', array(
		
			'before'	=>	'<div class="call-to-action"><div class="banner-left"></div>', 
			'after'	=>	'<div class="banner-right"></div></div><!-- end .call-to-action -->'
		
		) );		

		echo '</div>';
		
}

function txwgcap_home_layout( $layout ) {
	return 'content-sidebar';
}

function txwgcap_home_loop_helper() {
	
		genesis_widget_area( 'home_left', array(
		
			'before'	=> NULL,
			'after'		=> NULL
			
		) );
		
}

function txwgcap_home_left_bottom_helper() {
	
		genesis_widget_area( 'home_left_bottom', array(
			'before'	=> '<div class="updates_widget">',
			'after'		=> '</div>'
		) );
		
}

function txwgcap_home_sidebar_helper() {

	if ( is_user_logged_in() ) {
		genesis_widget_area( 'home_right', array(
			'before'	=> '<div id="sidebar" class="sidebar widget-area">',
			'after'		=> '</div>'
		) );
	} else {
		genesis_widget_area( 'public_home_right', array(
			'before'	=> '<div id="sidebar" class="sidebar widget-area">',
			'after'		=> '</div>'
		) );
	}
		
}

if ( is_home() ) {
	add_filter( 'genesis_pre_get_option_site_layout', 'txwgcap_home_layout' );
	add_action( 'genesis_before_loop', 'txwgcap_home_loop_helper' );
	add_action( 'genesis_after_content', 'txwgcap_home_sidebar_helper' );
	remove_action( 'genesis_loop', 'genesis_do_loop' );
	remove_action( 'genesis_after_content', 'genesis_get_sidebar' );
}


genesis();