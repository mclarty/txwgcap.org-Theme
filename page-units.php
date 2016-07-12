<?php

if ( $_GET['query'] ) {
	$result = $wpdb->get_results( sprintf( "SELECT * FROM wp_capwatch_org_contact WHERE ORGID = %d", mysql_real_escape_string( $_GET['query'] ) ) );

	echo print_r( $result, TRUE );

	die();
}

function txwgcap_get_unit_data( $query ) {
	global $wpdb;

	$result = $wpdb->get_results( "	SELECT wp_capwatch_org.ORGID, 
							wp_capwatch_org.Wing, 
							wp_capwatch_org.Unit, 
							wp_capwatch_org.NextLevel, 
							wp_capwatch_org.Name, 
							(SELECT DISTINCT Addr1 
								FROM wp_capwatch_org_address 
								WHERE wp_capwatch_org.ORGID = wp_capwatch_org_address.ORGID 
									AND wp_capwatch_org_address.Type = 'MAIL' 
									AND wp_capwatch_org_address.Priority = 'PRIMARY' 
									LIMIT 1) 
								AS MailingAddress1, 
							(SELECT DISTINCT CONCAT(City, ', ', State, ' ', LEFT(Zip, 5)) 
								FROM wp_capwatch_org_address 
								WHERE wp_capwatch_org.ORGID = wp_capwatch_org_address.ORGID 
									AND wp_capwatch_org_address.Type = 'MAIL' 
									AND wp_capwatch_org_address.Priority = 'PRIMARY' 
									LIMIT 1) 
								AS MailingAddress2, 
							(SELECT DISTINCT Addr1 
								FROM wp_capwatch_org_address 
								WHERE wp_capwatch_org.ORGID = wp_capwatch_org_address.ORGID 
									AND wp_capwatch_org_address.Type = 'MEETING' 
									AND wp_capwatch_org_address.Priority = 'PRIMARY' 
									LIMIT 1) 
								AS MeetingAddress1, 
							(SELECT DISTINCT CONCAT(City, ', ', State, ' ', LEFT(Zip, 5)) 
								FROM wp_capwatch_org_address 
								WHERE wp_capwatch_org.ORGID = wp_capwatch_org_address.ORGID 
									AND wp_capwatch_org_address.Type = 'MEETING' 
									AND wp_capwatch_org_address.Priority = 'PRIMARY' 
									LIMIT 1) 
								AS MeetingAddress2, 
							(SELECT CAPID 
								FROM wp_capwatch_commanders 
								WHERE wp_capwatch_org.ORGID = wp_capwatch_commanders.ORGID) 
								AS CommanderCAPID, 
							(SELECT CONCAT(Rank, ' ', NameFirst, ' ', NameMiddle, ' ', NameLast) 
								FROM wp_capwatch_commanders 
								WHERE wp_capwatch_org.ORGID = wp_capwatch_commanders.ORGID) 
								AS CommanderName, 
							(SELECT DISTINCT Contact 
								FROM wp_capwatch_member_contact 
								WHERE wp_capwatch_member_contact.CAPID = CommanderCAPID 
									AND wp_capwatch_member_contact.Type = 'EMAIL' 
									AND wp_capwatch_member_contact.Priority = 'PRIMARY' 
									LIMIT 1) 
								AS CommanderEmail, 
							(SELECT DISTINCT Contact 
								FROM wp_capwatch_org_contact 
								WHERE wp_capwatch_org.ORGID = wp_capwatch_org_contact.ORGID 
									AND wp_capwatch_org_contact.Type = 'EMAIL' 
									AND wp_capwatch_org_contact.Priority = 'PRIMARY' 
									LIMIT 1) 
								AS UnitEmail,
							(SELECT DISTINCT Contact 
								FROM wp_capwatch_org_contact 
								WHERE wp_capwatch_org.ORGID = wp_capwatch_org_contact.ORGID 
									AND wp_capwatch_org_contact.Type = 'URL' 
									AND wp_capwatch_org_contact.Priority = 'PRIMARY' 
									LIMIT 1) 
								AS URL 
							FROM wp_capwatch_org 
							WHERE " . $query );

	return $result;
}

function txwgcap_make_unit_row( $unit, $header = NULL ) {
	$table_args = array( 	'Charter' => '5%',
					'Unit Name' => '20%',
					'Mailing Address' => '18%',
					'Meeting Address' => '18%',
					'Commander' => '17%',
					'Unit Email' => '18%'
				);

	$charter = strtoupper( $unit->Wing . $unit->Unit );

	if ( $header ) {
		echo '<tr>';
		foreach ( $table_args as $key => $val ) {
			if ( !in_array( $key, array( 'Mailing Address' ) ) || is_user_logged_in() ) {
				echo '<th style="width: ' . $val . ';">' . $key . '</th>';
			}
		}
		echo '</tr>';
	} else {
		echo '<tr>';
		echo '<td style="text-align: center; width: ' . $table_args['Charter'] . ';" title="' . $unit->ORGID . '">' . $charter . '</td>';
		echo '<td style="width: ' . $table_args['Unit Name'] . ';">';
		if ( $unit->URL ) echo '<a href="' . $unit->URL . '" target="_blank">';
		echo strtoupper( preg_replace( '/ SQ/', NULL, preg_replace( '/ (SQDN|SQUADRON)/', NULL, $unit->Name ) ) );
		if ( $unit->URL ) echo '</a>';
		echo '</td>';
		if ( is_user_logged_in() ) {
			echo '<td style="width: ' . $table_args['Mailing Address'] . ';">' . strtoupper( $unit->MailingAddress1 ) . '<br />' . strtoupper( $unit->MailingAddress2 ) . '</td>';
		}
		echo '<td style="width: ' . $table_args['Meeting Address'] . ';">' . strtoupper( $unit->MeetingAddress1 ) . '<br />' . strtoupper( $unit->MeetingAddress2 ) . '</td>';
		if ( is_user_logged_in() && $unit->CommanderEmail ) {
			echo '<td style="width: ' . $table_args['Commander'] . ';"><a href="mailto:' . $unit->CommanderEmail . '">' . $unit->CommanderName . '</a></td>';
		} else {
			echo '<td style="width: ' . $table_args['Commander'] . ';">' . $unit->CommanderName . '</td>';
		}
		echo '<td style="width: ' . $table_args['Unit Email'] . ';"><a href="mailto:' . $unit->UnitEmail . '">' . $unit->UnitEmail . '</a></td></tr>';
	}
}

function txwgcap_units_list() {
	global $wpdb;

	$excludedUnits = get_option( 'capwatch_exclude_orgs_unitlist' );

	echo '<table class="unit_list_table unit_list_table_header">';
	txwgcap_make_unit_row( NULL, TRUE );
	echo '</table>';

	echo '<br /><h4>Wing Headquarters</h4>';

	echo '<table class="unit_list_table">';
	$wingHQ = txwgcap_get_unit_data( "Wing='TX' AND (Scope='WING' OR (Scope='UNIT' AND NextLevel=362 AND Unit > 0)) ORDER BY Unit" );
	foreach( $wingHQ as $unit ) {
		txwgcap_make_unit_row( $unit );
	}
	echo '</table>';

	$groupHQ = txwgcap_get_unit_data( "Wing='TX' AND Scope='GROUP' ORDER BY Unit" );
	foreach( $groupHQ as $unit ) {
		echo '<br /><h4>Group ' . convertToRoman( substr( $unit->Unit, 1, 1 ) ) . '</h4>';
		echo '<table class="unit_list_table">';
		txwgcap_make_unit_row( $unit );
		$query = "NextLevel=" . $unit->ORGID;
		$query .= $excludedUnits ? " AND ORGID NOT IN (" . $excludedUnits . ")" : NULL;
		$query .= " ORDER BY Unit";
		$squadrons = txwgcap_get_unit_data( $query );
		foreach( $squadrons as $squadron ) {
			txwgcap_make_unit_row( $squadron );
		}
		echo '</table>';
	}

	echo '<br /><p><em>Organization data current as of ' . date( 'd F Y', get_option( 'capwatch_lastUpdated' ) ) . '</em></p>';
}

add_action( 'genesis_loop', 'txwgcap_units_list' );

genesis();