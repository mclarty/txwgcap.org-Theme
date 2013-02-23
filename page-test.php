<pre>
	<?php

/** Start the engine */
require_once( get_template_directory() . '/lib/init.php' );

$qry = "SELECT * FROM wp_capwatch_member";

$rs = $wpdb->get_results( $qry );

foreach( $rs as $row ) {
	echo print_r( $row, TRUE );
}