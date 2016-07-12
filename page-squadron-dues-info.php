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
		<th>New Seniors</th>
		<th>Renewal Seniors</th>
		<th>New Cadets</th>
		<th>Renewal Cadets</th>
		<th>Submitted</th>
		<th>Approved</th>
	</tr>
	<?php

	$unitListing = $wpdb->get_results( "	SELECT CONCAT( Region, '-', Wing, '-', Unit, ' ', Name ) AS UnitName 
											FROM wp_capwatch_org 
											WHERE Wing = 'TX' 
											AND Unit NOT IN ( '000', '999' ) 
											AND Scope = 'UNIT' 
											ORDER BY UnitName" );

	$form_id = 4;
	$lead_table_name = GFFormsModel::get_lead_table_name();
	$qry = "SELECT COUNT(id) AS count FROM $lead_table_name WHERE form_id = $form_id";
	$leads_count = $wpdb->get_results( $qry );
	$leads = GFFormsModel::get_leads( $form_id, NULL, NULL, NULL, 0, $leads_count[0]->count );
	foreach( $leads as $row ) {
		$unit = NULL;
		$unit['id'] = $row['id'];
		$unit['UnitName'] = $row[1];
		$unit['RenewalSeniorDues'] = $row[2];
		$unit['ApprovedDate'] = $row[3] ? date( 'Y-m-d', strtotime( $row[3] ) ) : NULL;
		$unit['ApprovedBy'] = $row[4];
		$unit['SubmittedBy'] = $row[5];
		$unit['SubmittedDate'] = $row['date_created'] ? date( 'Y-m-d', strtotime( $row['date_created'] ) ) : NULL;
		$unit['RenewalCadetDues'] = $row[6];
		$unit['NewSeniorDues'] = $row[7];
		$unit['NewCadetDues'] = $row[8];
		$units[$unit['UnitName']] = $unit;
	}

	foreach( $unitListing as $row ) {
		echo "<tr style='text-align: center;'>";
		echo "<td>{$row->UnitName}</td>";
		if ( $unitData = $units[$row->UnitName] ) {
			echo "<td>{$unitData['NewSeniorDues']}</td>";
			echo "<td>{$unitData['RenewalSeniorDues']}</td>";
			echo "<td>{$unitData['NewCadetDues']}</td>";
			echo "<td>{$unitData['RenewalCadetDues']}</td>";
			echo "<td><a title='{$unitData['SubmittedBy']}'>{$unitData['SubmittedDate']}</a></td>";
			if ( !$unitData->ApprovedDate && in_array( $user->user_login, $approvers ) ) {
				echo "	<td colspan='2'>
							<input type='button' value='Approve' 
							onclick=\"window.location.href='" . $_SERVER['REQUEST_URI'] . "?approve={$unitData['id']}'\" />
						</td>";
			} else {
				echo "<td><a title='{$unitData['ApprovedBy']}'>{$unitData['ApprovedDate']}</a></td>";
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
