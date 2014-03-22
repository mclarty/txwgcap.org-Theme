<?php

function txwgcap_get_unit_data( $query ) {
	global $wpdb;

	$result = $wpdb->get_results( "	SELECT wp_capwatch_org.ORGID, 
							wp_capwatch_org.Wing, 
							wp_capwatch_org.Unit, 
							wp_capwatch_org.NextLevel, 
							wp_capwatch_org.Name, 
							(SELECT Addr1 
								FROM wp_capwatch_org_address 
								WHERE wp_capwatch_org.ORGID = wp_capwatch_org_address.ORGID 
									AND wp_capwatch_org_address.Type = 'MAIL' 
									AND wp_capwatch_org_address.Priority = 'PRIMARY') 
								AS Address1, 
							(SELECT CONCAT(City, ', ', State, ' ', LEFT(Zip, 5)) 
								FROM wp_capwatch_org_address 
								WHERE wp_capwatch_org.ORGID = wp_capwatch_org_address.ORGID 
									AND wp_capwatch_org_address.Type = 'MAIL' 
									AND wp_capwatch_org_address.Priority = 'PRIMARY') 
								AS Address2, 
							(SELECT CAPID 
								FROM wp_capwatch_commanders 
								WHERE wp_capwatch_org.ORGID = wp_capwatch_commanders.ORGID) 
								AS CommanderCAPID, 
							(SELECT CONCAT(Rank, ' ', NameFirst, ' ', NameMiddle, ' ', NameLast) 
								FROM wp_capwatch_commanders 
								WHERE wp_capwatch_org.ORGID = wp_capwatch_commanders.ORGID) 
								AS CommanderName, 
							(SELECT Contact 
								FROM wp_capwatch_member_contact 
								WHERE wp_capwatch_member_contact.CAPID = CommanderCAPID 
									AND wp_capwatch_member_contact.Type = 'EMAIL' 
									AND wp_capwatch_member_contact.Priority = 'PRIMARY') 
								AS CommanderEmail, 
							(SELECT Contact 
								FROM wp_capwatch_org_contact 
								WHERE wp_capwatch_org.ORGID = wp_capwatch_org_contact.ORGID 
									AND wp_capwatch_org_contact.Type = 'EMAIL' 
									AND wp_capwatch_org_contact.Priority = 'PRIMARY') 
								AS UnitEmail,
							(SELECT Contact 
								FROM wp_capwatch_org_contact 
								WHERE wp_capwatch_org.ORGID = wp_capwatch_org_contact.ORGID 
									AND wp_capwatch_org_contact.Type = 'URL' 
									AND wp_capwatch_org_contact.Priority = 'PRIMARY') 
								AS URL 
							FROM wp_capwatch_org 
							WHERE " . $query );

	return $result;
}

function txwgcap_make_unit_row( $unit, $header = NULL ) {
	$table_args = array( 	'Charter' => '5%',
					'Unit Name' => '20%',
					'Address' => '18%',
					'City/State/Zip' => '18%',
					'Commander' => '17%',
					'Unit Email' => '18%'
				);

	$charter = strtoupper( $unit->Wing . $unit->Unit );

	if ( $header ) {
		echo '<tr>';
		foreach ( $table_args as $key => $val ) {
			if ( !in_array( $key, array( 'Address', 'City/State/Zip' ) ) || is_user_logged_in() ) {
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
			echo '<td style="width: ' . $table_args['Address'] . ';">' . strtoupper( $unit->Address1 ) . '</td>';
			echo '<td style="width: ' . $table_args['City/State/Zip'] . ';">' . strtoupper( $unit->Address2 ) . '</td>';
		}
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
		$squadrons = txwgcap_get_unit_data( "NextLevel=" . $unit->ORGID . " ORDER BY Unit" );
		foreach( $squadrons as $squadron ) {
			txwgcap_make_unit_row( $squadron );
		}
		echo '</table>';
	}

	echo '<br /><p><em>Organization data current as of ' . date( 'd F Y', get_option( 'capwatch_lastUpdated' ) ) . '</em></p>';
}

add_action( 'genesis_loop', 'txwgcap_units_list' );

genesis();