<?php

// Custom post content
add_action( 'genesis_post_content', 'staff_listing' );
remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );

function available_equipment() {
	global $wpdb;

	$qry = $wpdb->get_results( "SELECT * FROM wp_capwatch_equipment WHERE wing='TX' AND unit='001' AND remarks LIKE 'AVAILABLE%' 
		ORDER BY noun, descrp, make, model, assetcd" );

?>
<h1 class="entry-title">Available Inventory</h1>

<p>Logistics maintains an inventory of surplus equipment at Wing Headquarters available for issuance to units on an as-needed basis. The list below 
	reflects the current inventory as of <strong><?php echo capwatch_lastUpdated( array( 'format' => 'd M Y' ) ); ?></strong>. Unit commanders or 
	logistics/supply officers may request a transfer of available equipment by submitting an email to <a href="mailto:logistics_@txwgcap.org">logistics_@txwgcap.org</a> 
	indicating the desired item(s) by description and asset number.</p>

<br />

<table class="docs_table">
	<tr>
		<th>Item Description</th>
		<th>Make &amp; Model</th>
		<th>Asset Number</th>
		<th>Remarks</th>
	</tr>
<?php

	foreach( $qry as $row ) {
	?>
		<tr>
			<td><?php echo $row->noun . ": " . $row->descrp; ?></td>
			<td><?php echo $row->make . " " . $row->model; ?></td>
			<td><?php echo $row->assetcd; ?></td>
			<td><?php echo $row->remarks; ?></td>
		</tr>
	<?php 
	}

?>
</table>
<?php 
}

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'available_equipment' );

genesis();
