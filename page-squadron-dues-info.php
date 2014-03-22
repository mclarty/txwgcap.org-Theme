<?php

$user = wp_get_current_user();
$approvers = explode( ',', get_option( 'txwgcap_squadron_dues_approvers' ) );

if ( $_GET['approve'] ) {
	if ( !in_array( $user->user_login, $approvers ) ) {
		continue;
	}

	$wpdb->insert( 'wp_rg_lead_detail', array(
			'lead_id' => $_GET['approve'],
			'form_id' => 4,
			'field_number' => 3,
			'value' => date( 'Y-m-d H:i:s' )
			) );

	$wpdb->insert( 'wp_rg_lead_detail', array(
			'lead_id' => $_GET['approve'],
			'form_id' => 4,
			'field_number' => 4,
			'value' => $user->display_name
			) );
}

function squadron_dues_info() {
	global $wpdb, $user, $approvers;

	if ( !is_user_logged_in() ) {
	?>
		<div class="content-box-red"><strong>You must be logged in to access squadron dues information</strong></div>
	<?php 
		return;
	}
	?>

<table class="dues-list">
	<tr>
		<th>Unit Name</th>
		<th>Senior Dues</th>
		<th>Cadet Dues</th>
		<th>Submitted By</th>
		<th>Submitted Date</th>
		<th>Approved By</th>
		<th>Approved Date</th>
	</tr>
	<?php

	$unitListing = $wpdb->get_results( "	SELECT CONCAT( Region, '-', Wing, '-', Unit, ' ', Name ) AS UnitName 
											FROM wp_capwatch_org 
											WHERE Wing = 'TX' 
											AND Unit NOT IN ( '000', '999' ) 
											AND Scope = 'UNIT' 
											ORDER BY UnitName" );

	$lead_qry = $wpdb->get_results( "SELECT * FROM wp_rg_lead WHERE form_id = 4 ORDER BY id" );
	foreach( $lead_qry as $row ) {
		unset( $fields );
		$qry = $wpdb->get_results( sprintf( "SELECT * FROM wp_rg_lead_detail WHERE lead_id = %d", $row->id ) );
		foreach( $qry as $subrow ) {
			$fields[$subrow->field_number] = $subrow;
		}
		$unit = $row;
		$unit->UnitName = $fields[1]->value;
		$unit->SeniorDues = $fields[2]->value;
		$unit->ApprovedDate = $fields[3]->value ? date( 'Y-m-d', strtotime( $fields[3]->value ) ) : NULL;
		$unit->ApprovedBy = $fields[4]->value;
		$unit->SubmittedBy = $fields[5]->value;
		$unit->SubmittedDate = $row->date_created ? date( 'Y-m-d', strtotime( $row->date_created ) ) : NULL;
		$unit->CadetDues = $fields[6]->value;
		$units[$unit->UnitName] = $unit;
	}

	foreach( $unitListing as $row ) {
		echo "<tr style='text-align: center;'>";
		echo "<td>{$row->UnitName}</td>";
		if ( $unitData = $units[$row->UnitName] ) {
			echo "<td>{$unitData->SeniorDues}</td>";
			echo "<td>{$unitData->CadetDues}</td>";
			echo "<td>{$unitData->SubmittedBy}</td>";
			echo "<td>{$unitData->SubmittedDate}</td>";
			if ( !$unitData->ApprovedDate && in_array( $user->user_login, $approvers ) ) {
				echo "	<td colspan='2'>
							<input type='button' value='Approve' 
							onclick=\"window.location.href='" . $_SERVER['REQUEST_URI'] . "?approve={$unitData->id}'\" />
						</td>";
			} else {
				echo "<td>{$unitData->ApprovedBy}</td>";
				echo "<td>{$unitData->ApprovedDate}</td>";
			}
		} else {
			echo "<td colspan='6'>No Data Submitted</td>";
		}
		echo "</tr>";
	}
	
	?>
</table>
<?php 

}

add_action( 'genesis_post_content', 'squadron_dues_info' );

genesis();
