<?php

wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'fullcalendar', get_stylesheet_directory_uri() . '/fullcalendar/fullcalendar.js' );
wp_enqueue_script( 'gcal', get_stylesheet_directory_uri() . '/fullcalendar/gcal.js' );
wp_enqueue_style( 'fullcalendar', get_stylesheet_directory_uri() . '/fullcalendar/fullcalendar.css' );

function txwg_render_calendar() {
?>
<script type='text/javascript'>

jQuery(document).ready(function($) {
    $('#calendar').fullCalendar({
        events: 'http://www.google.com/calendar/feeds/texaswingcalendar%40gmail.com/public/basic'
    });
});

</script>
<div id="calendar"></div>
<?php
}

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'txwg_render_calendar' );

genesis();
