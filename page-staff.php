<?php

// Custom post content
add_action( 'genesis_post_content', 'staff_listing' );
remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );


function staff_listing() {
	global $wpdb;

	// Build columns list ( array key: title, value: array( attribute, public_view ) )

	$functionalAreasSelection = $wpdb->get_results( "SELECT * FROM wp_officers_types ORDER BY positionTypeOrder" );

?>
<table id="officersdirtable">
	<tr>
<?php

	$cols = array(  'positionName' => array( 'Position', TRUE ),
					'positionOfficer' => array( 'Officer', TRUE ),
					'Phone' => array( 'Phone', FALSE ),
					'Email' => array( 'Email', TRUE )
			);


	foreach ( $cols as $col => $args ) {
		if ( $args[1] || is_user_logged_in() ) {
			echo "<th class='{$col}'>{$args[0]}</th>";
		}
	}

?>
	</tr>
<?php 

	foreach ( $functionalAreasSelection as $function ) {

		if ( $function->positionTypeName == 'Group Commanders' ) {
			$function->positionTypeName .= ' (<a href="' . get_option( 'txwgcap_group_map_url' ) . '" rel="lightbox" title="Texas Wing Groups">Groups Map</a>)';
		}

		echo "<tr><td><strong>" . stripslashes( $function->positionTypeName ) . "</strong></td></tr>";
		echo "<tbody class='officer-row'>";

		$qry  = "SELECT wp_officers.*, 
				(SELECT Rank FROM wp_capwatch_member WHERE CAPID = wp_officers.positionCAPID) AS Rank, 
				(SELECT CONCAT(NameFirst, ' ', NameLast) FROM wp_capwatch_member WHERE CAPID = wp_officers.positionCAPID) AS Name, 
				(SELECT DISTINCT Contact FROM wp_capwatch_member_contact WHERE Type = 'CELL PHONE' AND Priority = 'PRIMARY' AND DoNotContact = 'False' AND CAPID = wp_officers.positionCAPID LIMIT 1) AS CellPhone, 
				(SELECT DISTINCT Contact FROM wp_capwatch_member_contact WHERE Type = 'HOME PHONE' AND Priority = 'PRIMARY' AND DoNotContact = 'False' AND CAPID = wp_officers.positionCAPID LIMIT 1) AS HomePhone, 
				(SELECT DISTINCT Contact FROM wp_capwatch_member_contact WHERE Type = 'EMAIL' AND Priority = 'PRIMARY' AND DoNotContact = 'False' AND CAPID = wp_officers.positionCAPID LIMIT 1) AS Email 
				FROM wp_officers WHERE positionType = {$function->positionTypeID} ";
		if ( !is_user_logged_in() ) {
			$qry .= "AND positionPublic = 1 ";
		}
		$qry .= "ORDER BY positionOrder, positionShortname";

		$officersSelection = $wpdb->get_results( $qry );

		foreach ( $officersSelection as $officer ) {
			if ( $officer->positionCAPID || $officer->positionOfficer ) {
				$officer->positionOfficer = $officer->positionOfficer ? $officer->positionOfficer : 
					$officer->Name;
			} else {
				$officer->positionOfficer = 'Vacant';
			}

			$officer->Email = $officer->positionEmail ? strtolower( $officer->positionEmail ) : strtolower( $officer->Email );
			$officer->Phone = $officer->CellPhone ? $officer->CellPhone : $officer->HomePhone;
			$officer->Phone = $officer->positionPhone ? $officer->positionPhone : $officer->Phone;
			echo "<tr>";
			foreach ( $cols as $col => $args ) {
				if ( $args[1] || is_user_logged_in() ) {
					echo "<td class='{$col}''>";
					switch ( $col ) {
						case 'positionOfficer':
							echo stripslashes( $officer->Rank . ' ' . $officer->positionOfficer );
							break;
						case 'Phone':
							if ( $officer->$col ) {
								echo formatPhoneNumber( $officer->$col );
							} else {
								echo $officer->positionOfficer ? "N/A" : NULL;
							}
							break;
						case 'Email':	// Create email links for guest / authenticated user
							if ( is_user_logged_in() ) {
								echo "<a href=\"mailto:{$officer->$col}\">{$officer->$col}</a>";
							} else {
								echo "<a href=\"/contact?officer={$officer->positionShortname}\">Email</a>";
							}
							break;
						default:
							if ( $officer->$col ) {
								echo $officer->$col;
							} else {
								echo $officer->positionOfficer ? "N/A" : NULL;
							}
							break;
					}
					echo "</td>";
				}
			}
			echo "</tr>";
		}

		echo "</tbody>";
		echo "<tr><td>&nbsp;</td></tr>";

	}

?>
</table>

<p><em>Contact data current as of <?php echo date( 'd F Y', get_option( 'capwatch_lastUpdated' ) ); ?></em></p>

<?php if ( is_user_logged_in() ) { echo show_pii_disclaimer(); } 

}

genesis();
