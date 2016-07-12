<?php

wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'moment', get_stylesheet_directory_uri() . '/moment.min.js', array( 'jquery' ), '2.8.4' );
wp_enqueue_script( 'fullcalendar', get_stylesheet_directory_uri() . '/fullcalendar/fullcalendar.min.js', array( 'moment', 'jquery' ), '2.2.2' );
wp_enqueue_script( 'gcal', get_stylesheet_directory_uri() . '/fullcalendar/gcal.js', array( 'fullcalendar', 'moment', 'jquery' ), '2.2.2' );
wp_enqueue_style( 'fullcalendar', get_stylesheet_directory_uri() . '/fullcalendar/fullcalendar.min.css', false, '2.2.2' );

function txwg_render_calendar() {
	$txwg_cal = json_decode( file_get_contents( get_stylesheet_directory_uri() . '/calendars.json' ), TRUE );
?>
<script type='text/javascript'>

jQuery(document).ready(function($) {
    $('#calendar').fullCalendar({
    	googleCalendarApiKey: 'AIzaSyA7MUaSefXqgczAuD3ANa0RMGipr4fZPPY',
        eventSources: [
<?php foreach( $txwg_cal as $key => $val ) { ?>
			{
				googleCalendarId: '<?php echo $val['CalendarID']; ?>',
				className: '<?php echo $key; ?>'
			},
<?php } ?>
        ],
    });
});

</script>
<div id="calendar"></div>
<br />
<div>
	<h5>Link to Texas Wing Calendars</h5>
<?php foreach( $txwg_cal as $key => $val ) { ?>
	<div class="event_type_legend"><a href="<?php echo $val['xml_url']; ?>"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/xml.gif" /></a> 
		<a href="<?php echo $val['ical_url']; ?>"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/ical.gif" /></a> 
		<span class="<?php echo $key; ?>"><?php echo $val['name']; ?></span></div>
<?php } ?>
</div>
<?php
}

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'txwg_render_calendar' );

genesis();
