<?php
/**
 * Template Name: Conference Page with Countdown Timer
 */

add_action( 'wp_enqueue_scripts', 'txwgcap_conference_enqueue_scripts' );
add_action( 'genesis_before_post_content', 'txwgcap_conference_countdown_timer' );
add_action( 'genesis_post_content', 'txwgcap_conference_body' );
add_action( 'genesis_after_post_content', 'txwgcap_conference_js' );

function txwgcap_conference_enqueue_scripts() {
	wp_enqueue_script( 'lwtCoutndown', get_stylesheet_directory_uri() . '/lwtCountdown/js/jquery.lwtCountdown-1.0.js', array( 'jquery' ) );
	wp_enqueue_script( 'lwtCoutndown-misc', get_stylesheet_directory_uri() . '/lwtCountdown/js/misc.js' );
	wp_enqueue_style( 'lwtCountdown-main', get_stylesheet_directory_uri() . '/lwtCountdown/style/main.css' );
}

function txwgcap_conference_countdown_timer( $post ) {
?>
<div id="countdown_dashboard" style="margin: 20px auto 10px auto; width: 650px;">
	<div class="dash weeks_dash">
		<span class="dash_title">weeks</span>
		<div class="digit">0</div>
		<div class="digit">0</div>
	</div>

	<div class="dash days_dash">
		<span class="dash_title">days</span>
		<div class="digit">0</div>
		<div class="digit">0</div>
	</div>

	<div class="dash hours_dash">
		<span class="dash_title">hours</span>
		<div class="digit">0</div>
		<div class="digit">0</div>
	</div>

	<div class="dash minutes_dash">
		<span class="dash_title">minutes</span>
		<div class="digit">0</div>
		<div class="digit">0</div>
	</div>

	<div class="dash seconds_dash">
		<span class="dash_title">seconds</span>
		<div class="digit">0</div>
		<div class="digit">0</div>
	</div>
</div>
<div style="clear: both;"></div>
<?php 
}

function txwgcap_conference_js( $post ) {
?>
<script language="javascript" type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#countdown_dashboard').countDown({
			targetDate: {
				'day': 		13,
				'month': 	4,
				'year': 	2016,
				'hour': 	8,
				'min': 		0,
				'sec': 		0
			}
		});
		
		jQuery('#email_field').focus(email_focus).blur(email_blur);
		jQuery('#subscribe_form').bind('submit', function() { return false; });
	});
</script>
<?php 
}

function txwgcap_conference_body( $post ) {
	return $post->the_content;
}

genesis();
