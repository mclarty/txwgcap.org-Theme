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

<table width="100%">
	<tr>
		<th>Unit Name</th>
		<th>Dues Amount</th>
		<th>Submitted By</th>
		<th>Submitted Date</th>
		<th>Approved By</th>
		<th>Approved Date</th>
	</tr>
	<?php

	$lead_qry = $wpdb->get_results( "SELECT * FROM wp_rg_lead WHERE form_id = 4" );
	foreach( $lead_qry as $row ) {
		$qry = $wpdb->get_results( sprintf( "SELECT * FROM wp_rg_lead_detail WHERE lead_id = %d", $row->id ) );
		foreach( $qry as $subrow ) {
			$fields[$subrow->field_number] = $subrow;
		}
		$unit = $row;
		$unit->UnitName = $fields[1]->value;
		$unit->Dues = $fields[2]->value;
		$unit->ApprovedBy = $fields[4]->value;
		$unit->ApprovedDate = $fields[3]->value;
		$unit->SubmittedBy = $fields[5]->value;
		$units[$unit->UnitName] = $unit;
	}

	if ( $units ) {
		sort( $units );
		foreach ( $units as $unit ) {
			echo "<tr style='text-align: center;'>";
			echo "<td>{$unit->UnitName}</td>";
			echo "<td>{$unit->Dues}</td>";
			echo "<td>{$unit->SubmittedBy}</td>";
			echo "<td>{$unit->date_created}</td>";
			if ( !$unit->ApprovedDate && in_array( $user->user_login, $approvers ) ) {
				echo "	<td colspan='2'>
							<input type='button' 
							value='Approve' 
							onclick=\"window.location.href='" . $_SERVER['REQUEST_URI'] . "?approve={$unit->id}'\" />
						</td>";
			} else {
				echo "<td>{$unit->ApprovedBy}</td>";
				echo "<td>{$unit->ApprovedDate}</td>";
			}
			echo "</tr>";
		}
	}
	
	?>
</table>
<?php 

}

add_action( 'genesis_post_content', 'squadron_dues_info' );

genesis();
