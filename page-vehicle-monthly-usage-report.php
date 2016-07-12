<?php

$excluded_vehicles = array( '42000', '42101', '42103', '42110', '42117', '42119', '42120', '42130', '42132', '42137', '42298', '42501',
					 '42502', '42503', '42504', '42505', '42506', '42507', '42508', '42509', '42510', '42511', '42990', '42991' );

wp_enqueue_script( 'stupidtable', get_stylesheet_directory_uri() . '/stupidtable.min.js', array( 'jquery' ) );

add_action( 'genesis_post_content', 'vehicle_monthly_usage_report' );

function txwgcap_vehicle_reports_list() {
	global $wpdb, $excluded_vehicles;

	if ( !is_user_logged_in() ) {
	?>
		<div class="content-box-red"><strong>You must be logged in to access vehicle usage report information</strong></div>
	<?php 
		return;
	}

	$qry = "SELECT cap_id, UPPER( CONCAT( yr_mfgr, ' ', make, ' ', veh_type, ' (', Region, '-', Wing, '-', Unit, ')' ) ) AS vehicle 
			FROM wp_capwatch_vehicles 
			WHERE cap_id NOT IN (" . implode( ',', $excluded_vehicles ) . ") 
			ORDER BY cap_id";

	$vehicles = $wpdb->get_results( $qry );

	?>
		<hr />

		<h4>Vehicle Reports</h4>
	<?php

	$form_id = 5;
	$lead_table_name = GFFormsModel::get_lead_table_name();
	$qry = "SELECT COUNT(id) AS count FROM $lead_table_name WHERE form_id = $form_id";
	$leads_count = $wpdb->get_results( $qry );
	$leads = GFFormsModel::get_leads( $form_id, NULL, NULL, NULL, 0, $leads_count[0]->count );
	
	if ( $_GET['debug'] ) {
		echo "<pre>" . print_r( $leads, TRUE ) . "</pre>";
	}

	foreach( $leads as $lead ) {
		$month = date( 'Y-m', strtotime( $lead[5] ) );
		$vehicle = $lead[1];
		$reports[$month][$vehicle] = $lead;
	}

	krsort( $reports );

	foreach( $reports as $month => $data ) {
	?>

		<div id="<?php echo $month; ?>-header" class="month-header" style="cursor: pointer; padding-left: 10px; background: url('<?php echo get_stylesheet_directory_uri(); ?>/images/icon-right.png') no-repeat left center;">
			<h4><?php echo $month; ?></h4>
		</div>

		<div id="<?php echo $month; ?>-body" class="month-body" style="display: none;">
			<table style="width: 100%; text-align: center; border: 1px;">
				<thead>
					<tr>
						<th data-sort="string" style="width: 25%;">Vehicle #</th>
						<th style="width: 50%;">Description</th>
						<th>CAPF 73</th>
						<th>TXWGF 77</th>
					</tr>
				</thead>
				<tbody>
		<?php

			foreach( $vehicles as $vehicle ) {
				if ( in_array( $vehicle->cap_id, $excluded_vehicles ) ) {
					continue;
				}
				$vehicleID = $vehicle->cap_id;
				$vehicleDesc = $vehicle->vehicle;
				$row = $reports[$month][$vehicleID];
				$bgcolor = $row ? '#aaffaa' : '#ffaaaa';

		?>
					<tr style="background-color: <?php echo $bgcolor; ?>; line-height: 18px;">
						<td><?php echo $vehicleID; ?></td>
						<td><?php echo $vehicleDesc; ?></td>
						<td><?php if ( $row ) { ?><a href="<?php echo $row[3]; ?>"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/pdficon_small.png" /></a><?php } ?></td>
						<td><?php if ( $row ) { if ($row[4] ) { ?><a href="<?php echo $row[4]; ?>"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/pdficon_small.png" /></a><?php } } ?></td>
					</tr>
		<?php

			}

		?>
				</tbody>
			</table>
		</div>

		<br />

		<script type="text/javascript">
			jQuery( "#<?php echo $month; ?>-header" ).click( function() {
				jQuery( ".month-body" ).slideUp();
				jQuery( ".month-header" ).css( "background", "url('<?php echo get_stylesheet_directory_uri(); ?>/images/icon-right.png') no-repeat left center" );
				jQuery( "#<?php echo $month; ?>-header" ).css( "background", "url('<?php echo get_stylesheet_directory_uri(); ?>/images/icon-down.png') no-repeat left center" );
				jQuery( "#<?php echo $month; ?>-body" ).slideDown();
			});
		</script>

	<?php 

	}

	?>

		<script type="text/javascript">
			jQuery( "table" ).stupidtable();

			jQuery( document ).ready( function() {
				jQuery( ".month-header:first" ).css( "background", "url('<?php echo get_stylesheet_directory_uri(); ?>/images/icon-down.png') no-repeat left center" );
				jQuery( ".month-body:first" ).slideDown();
			});
		</script>

	<?php 

}

add_action( 'genesis_loop', 'txwgcap_vehicle_reports_list' );

genesis();
