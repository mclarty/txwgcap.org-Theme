<?php

$excluded_units = array( '000' );

wp_enqueue_script( 'stupidtable', get_stylesheet_directory_uri() . '/stupidtable.min.js', array( 'jquery' ) );

function finance_annual_report() {
	global $wpdb, $user, $excluded_units, $by_ref_array;
}

add_action( 'genesis_post_content', 'finance_annual_report' );

function recursive_key_sort(&$by_ref_array)
{
    ksort($by_ref_array, SORT_REGULAR );
    foreach ($by_ref_array as $key => $value) {
        if (is_array($value))
        {
            recursive_key_sort($by_ref_array[$key]);
        }
    }
}

function txwgcap_finance_reports_list() {
	global $wpdb, $excluded_units;

	if ( !is_user_logged_in() ) {
	?>
		<div class="content-box-red"><strong>You must be logged in to access financial report information</strong></div>
	<?php 
		return;
	}

	$qry = "SELECT Unit, UPPER( CONCAT( Region, '-', Wing, '-', Unit, ' (', Name, ')' ) ) AS financeunit 
						FROM wp_capwatch_org 
						WHERE wp_capwatch_org.Wing = 'TX'
						AND wp_capwatch_org.Unit NOT IN (" . implode(',', $excluded_units ) . ")
						ORDER BY Unit";

	$units = $wpdb->get_results( $qry );

	?>
		<hr />
		<h4>Finance Reports</h4>
	<?php

	$form_id = 12;
	$lead_table_name = GFFormsModel::get_lead_table_name();
	$qry = "SELECT COUNT(id) AS count FROM $lead_table_name WHERE form_id = $form_id";
	$leads_count = $wpdb->get_results( $qry );
	if ( $_GET['debug'] ) {
		echo "<pre>" . print_r( $leads_count, TRUE ) . "</pre>";
	}
	$leads = GFFormsModel::get_leads( $form_id, NULL, NULL, NULL, 0, $leads_count[0]->count );

	foreach( $leads as $lead ) {
		$year = $lead[2];
		$unit = $lead[1];
		$dateSubmitted = $lead[5];
		$reports[$year][$unit][$dateSubmitted] = $lead;
//		$reports[$year][$unit] = $lead;

//		if ( $_GET['debug'] ) {
//			echo "<pre>" . print_r( $lead, TRUE ) . "</pre>";
//		}
	}

	recursive_key_sort($reports);
	krsort( $reports);

//	if ( $_GET['debug'] ) {
//		echo "<pre>" . print_r( $reports, TRUE ) . "</pre>";
//	}

	foreach( $reports as $year => $data ) {
	?>
		<div id="<?php echo $year; ?>-header" class="year-header" style="cursor: pointer; padding-left: 10px; background: url('<?php echo get_stylesheet_directory_uri(); ?>/images/icon-right.png') no-repeat left center;">
			<h4><?php echo $year; ?></h4>
		</div>
		<div id="<?php echo $year; ?>-body" class="year-body" style="display: none;">
			<table style="width: 100%; text-align: center; border: 1px;">
				<thead>
					<tr>
						<th data-sort="string" style="width: 50%;">Unit #</th>
						<th style="width: 25%;">Date Submitted</th>
						<th>CAPF 172</th>
						<th>Budget</th>
					</tr>
				</thead>
				<tbody>
		<?php

			foreach( $units as $unit ) {
				if ( in_array( $unit->Unit, $excluded_units ) ) {    // Should never get here from exclusion in SQL statement, but whatever
					continue;
				}
				$unitID = $unit->Unit;
				$unitName = $unit->financeunit;
				$dateSubmitted = NULL;				

				if ( array_key_exists( $unit->Unit, $reports[$year] ) ) {     // Loop through multiple entries for a unit
					$firsttime = TRUE;
					foreach( $reports[$year][$unitID] as $dateSubmitted => $data) {
						$row = $reports[$year][$unitID][$dateSubmitted];
						$bgcolor = $row ? '#aaffaa' : '#ffaaaa';
						?>
					<tr style="background-color: <?php echo $bgcolor; ?>; line-height: 18px;">
						<td><?php if ( $firsttime ) { echo $unitName; } ?></td>
						<td><?php echo $dateSubmitted; ?></td>
						<td><?php if ( $row ) { ?><a href="<?php echo $row[3]; ?>"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/pdficon_small.png" /></a><?php } ?></td>
						<td><?php if ( $row ) { if ($row[4] ) { ?><a href="<?php echo $row[4]; ?>"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/pdficon_small.png" /></a><?php } } ?></td>
					</tr>
						<?php
						$firsttime = NULL;
					}
				} else {
					$row = $reports[$year][$unitID];
					$bgcolor = $row ? '#aaffaa' : '#ffaaaa';
					?>
					<tr style="background-color: <?php echo $bgcolor; ?>; line-height: 18px;">
						<td><?php echo $unitName; ?></td>
						<td><?php echo $dateSubmitted; ?></td>
						<td><?php if ( $row ) { ?><a href="<?php echo $row[3]; ?>"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/pdficon_small.png" /></a><?php } ?></td>
						<td><?php if ( $row ) { if ($row[4] ) { ?><a href="<?php echo $row[4]; ?>"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/pdficon_small.png" /></a><?php } } ?></td>
					</tr>
					<?php
				}
			}

		?>
				</tbody>
			</table>
		</div>

		<br />

		<script type="text/javascript">
			jQuery( "#<?php echo $year; ?>-header" ).click( function() {
				jQuery( ".year-body" ).slideUp();
				jQuery( ".year-header" ).css( "background", "url('<?php echo get_stylesheet_directory_uri(); ?>/images/icon-right.png') no-repeat left center" );
				jQuery( "#<?php echo $year; ?>-header" ).css( "background", "url('<?php echo get_stylesheet_directory_uri(); ?>/images/icon-down.png') no-repeat left center" );
				jQuery( "#<?php echo $year; ?>-body" ).slideDown();
			});
		</script>

	<?php 
	}
	?>
		<script type="text/javascript">
			jQuery( "table" ).stupidtable();

			jQuery( document ).ready( function() {
				jQuery( ".year-header:first" ).css( "background", "url('<?php echo get_stylesheet_directory_uri(); ?>/images/icon-down.png') no-repeat left center" );
				jQuery( ".year-body:first" ).slideDown();
			});
		</script>

<?php 
}

add_action( 'genesis_loop', 'txwgcap_finance_reports_list' );

genesis();
?>
