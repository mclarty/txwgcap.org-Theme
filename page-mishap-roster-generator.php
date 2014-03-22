<?php

if ( $_POST['action'] == 'query_people' ) {
	$unit_cc = dutyList( $_POST['orgID'], FALSE, 'Commander' );
	$unit_cd = dutyList( $_POST['orgID'], FALSE, 'Deputy Commander' );
	$unit_se = dutyList( $_POST['orgID'], FALSE, 'Safety Officer' );

	$group_cc = dutyList( $_POST['orgID'], TRUE, 'Commander' );
	$group_cd = dutyList( $_POST['orgID'], TRUE, 'Deputy Commander' );
	$group_se = dutyList( $_POST['orgID'], TRUE, 'Safety Officer' );

	constructDropdown( 'Unit Commander', 'unit_cc', $unit_cc );
	constructDropdown( 'Deputy Commander', 'unit_cd', $unit_cd );
	constructDropdown( 'Safety Officer', 'unit_se', $unit_se );

	constructDropdown( 'Group Commander', 'group_cc', $group_cc );
	constructDropdown( 'Deputy Group Commander', 'group_cd', $group_cd );
	constructDropdown( 'Group Safety Officer', 'group_se', $group_se );

	die();
}

if ( $_POST['action'] == 'Generate Roster PDF' ) {
	header( 'Content-Type: application/vnd.adobe.xfdf' );
	header( 'Content-Disposition: attachment; filename="Mishap_Roster_' . time() . '.xfdf' );

	foreach( array( 'Unit_CC', 'Unit_CD', 'Unit_SE', 'Group_CC', 'Group_CD', 'Group_SE' ) as $k ) {
		$l = strtolower( $k );
		if ( $_POST[$l] ) {
			$roster[$k]['CAPID'] = $_POST[$l];
			$id_array[] .= $_POST[$l];
		}
	}

	$id_string = implode( ',', $id_array );

	$org = $wpdb->get_results( sprintf( "SELECT Unit, Name FROM wp_capwatch_org WHERE ORGID = %d", $_POST['orgID'] ) );

	$people = $wpdb->get_results( sprintf( "SELECT CAPID, Rank, NameLast, NameFirst FROM wp_capwatch_member WHERE CAPID IN (%s)", $id_string ) );

	$contacts = $wpdb->get_results( sprintf( "SELECT CAPID, Type, Priority, Contact FROM wp_capwatch_member_contact WHERE Type IN 
		('HOME PHONE', 'HOME FAX', 'CELL PHONE') AND CAPID IN (%s)", $id_string ) );

	foreach( $people as $person ) {
		$capid = $person->CAPID;
		foreach( $roster as $key => $val ) {
			if ( $val['CAPID'] == $capid ) {
				$roster[$key]['Name'] = "{$person->Rank} {$person->NameFirst} {$person->NameLast}";
			}
		}
	}

	foreach( $contacts as $person ) {
		$capid = $person->CAPID;
		foreach( $roster as $key => $val ) {
			if ( $val['CAPID'] == $capid ) {
				$type = $person->Type;
				$roster[$key][$type] = $person->Contact;
			}
		}
	}

	$xml = new SimpleXMLElement( '<xfdf xml:space="preserve" xmlns="http://ns.adobe.com/xfdf/"></xfdf>' );
	$f = $xml->addChild( 'f' );
	$f->addAttribute( 'href', 'http://www.txwgcap.org/wp-content/uploads/2013/10/TXWGF62-2-1_6Oct13_Fillable.pdf');
	$fields = $xml->addChild( 'fields' );

	foreach( array( 'Unit_Name' => $org[0]->Name, 'Unit_Charter' => $org[0]->Unit, 'Date' => date( 'd M Y' ) ) as $key => $val ) {
		$field = $fields->addChild( 'field' );
		$field->addAttribute( 'name', $key );
		$field->addChild( 'value', $val );
	}

	foreach( $roster as $key => $val ) {
		foreach( array( 'Name' => 'Name', 'Phone' => 'HOME PHONE', 'Mobile' => 'CELL PHONE', 'Fax' => 'HOME FAX' ) as $item => $attr ) {
			$field = $fields->addChild( 'field' );
			$field->addAttribute( 'name', "{$key}_{$item}" );
			if ( $item == 'Name' ) {
				$field->addChild( 'value', $val[$attr] );
			} else {
				$field->addChild( 'value', mishapRosterFormatPhoneNumber( $val[$attr] ) );
			}
		}
	}

	echo $xml->asXML();

	die();
}

function unitsList( $qry ) {
	global $wpdb;

	$where .= $qry['region'] ? sprintf( "Region='%s' AND ", $qry['region'] ) : NULL;
	$where .= $qry['wing'] ? sprintf( "Wing='%s' AND ", $qry['wing'] ) : NULL;
	$where .= $qry['unit'] ? sprintf( "Unit='%s' AND ", $qry['unit'] ) : NULL;

	$qry = $wpdb->get_results( sprintf( "SELECT ORGID, CONCAT(Region, '-', Wing, '-', Unit) AS Charter, Name FROM wp_capwatch_org 
		WHERE %s Scope = 'UNIT' ORDER BY Region, Wing, Unit", $where ) );

	return $qry;
}

function dutyList( $orgid, $nextLevel, $position ) {
	global $wpdb;

	$where = $nextLevel ? sprintf( "( SELECT NextLevel FROM wp_capwatch_org WHERE ORGID = %d )", $orgid ) : $orgid;

	$qry = $wpdb->get_results( sprintf( "SELECT CAPID, NameLast, NameFirst FROM wp_capwatch_member WHERE CAPID IN 
		( SELECT CAPID FROM wp_capwatch_duty_position WHERE ORGID = %s AND Duty LIKE '%s%%' ORDER BY Duty, Asst )", $where, $position ) );

	return $qry;
}

function constructDropdown( $label, $field, $var ) {
?>
	<p>
		<label for="<?php echo $var; ?>"><?php echo $label; ?>: </label>

		<select id="<?php echo $field; ?>" name="<?php echo $field; ?>">
<?php foreach( $var as $list ) { ?>
			<option value="<?php echo $list->CAPID; ?>"><?php echo $list->NameLast . ", " . $list->NameFirst; ?></option>
<?php } ?>
			<option value="">None of the above</option>
		</select>
	</p>
<?php 
}

function mishapRosterFormatPhoneNumber( $val ) {
	if ( strlen( $val ) == 10 ) {
		return substr( $val, 0, 3 ) . '.' . substr( $val, 3, 3 ) . '.' . substr( $val, 6, 4 );
	} else {
		return $val;
	}
}

function mishap_roster_generator_body() {
	$units = unitsList( array( 'wing' => 'TX' ) );

?>
<form action="<?php echo the_permalink(); ?>" method="post">
	<div id="unit">
		<p>
			<label for="orgID">Select Unit for Roster: </label>

			<select id="orgID" name="orgID">
	<?php foreach( $units as $unit ) { ?>
				<option value="<?php echo $unit->ORGID; ?>"><?php echo $unit->Charter . " " . $unit->Name; ?></option>
	<?php } ?>
			</select>
		</p>
	</div>

	<div id="people" style="display: none;">
		<h4>Loading...</h4>
	</div>
</form>

<script>
	jQuery("#orgID").change(function() {
		jQuery("#people").show();
		jQuery.post( '<?php echo the_permalink(); ?>', { 
			action: 'query_people',
			orgID: jQuery("#orgID").val() 
		}, function( data ) { 
			jQuery("#people").empty().append(data); 
			jQuery("#people").append('<input type="submit" name="action" value="Generate Roster PDF" />');
		});
	});
</script>
<?php 

}

add_action( 'genesis_post_content', 'mishap_roster_generator_body' );

genesis();