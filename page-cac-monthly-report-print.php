<?php

if ( $_POST['action'] == 'Print Report' ) {
	require( 'wp-config.php' );
	require( 'wp-content/themes/txwgcap/fpdf17/fpdf.php' );

	$db = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

	$depts_table = 'wp_officers_types';
	$lead_table = 'wp_rg_lead';
	$lead_detail_table = 'wp_rg_lead_detail';
	$form_id = 3;
	$reporting_period_field_id = 1;
	$reporting_period = $db->real_escape_string( date( 'F Y', strtotime( $_POST['reporting_period'] . '-01' ) ) );

	$result = $db->query( sprintf( "	SELECT positionTypeName 
										FROM `%s` 
										ORDER BY positionTypeName",
							$depts_table ) );

	while ( $row = $result->fetch_assoc() ) {
		$departments[] = stripslashes( $row['positionTypeName'] );
	}

	ksort( $departments );

	$result = $db->query( sprintf( "	SELECT ldt.lead_id 
										FROM `%s` ldt 
										INNER JOIN `%s` lt ON lt.id = ldt.lead_id 
										WHERE ldt.form_id = %d 
											AND ldt.value = '%s' 
											AND lt.status = 'active'",
							$lead_detail_table, $lead_table, $form_id, $reporting_period ) );

	while ( $row = $result->fetch_assoc() ) {
		$leads[] = $row['lead_id'];
	}

	if ( !isset( $leads ) ) {
		$error = 'No reports found for requested reporting period.';
	}
}

if ( $_POST['action'] == 'Print Report' && isset( $leads ) ) {
	foreach( $leads as $lead ) {
		$result = $db->query( sprintf( "	SELECT * 
											FROM `%s` 
											WHERE lead_id = %d",
								$lead_detail_table, $lead ) );

		unset( $report_data, $row, $subrow );

		while ( $row = $result->fetch_assoc() ) {
			switch ( $row['field_number'] ) {
				case 1:
					$report_data['reporting_period'] = $row['value'];
					break;
				case 2:
					unset( $department, $capid, $person, $position );

					$report_data['department'] = $row['value'];
					$department = str_replace( ' ', NULL, $row['value'] );

					$query = sprintf( "	SELECT user_login 
										FROM wp_users 
										WHERE ID = ( 
											SELECT created_by 
											FROM `%s` 
											WHERE id = %d )",
								$lead_table, $lead );

					if ( $capid_result = $db->query( $query ) ) {
						$capid = $capid_result->fetch_assoc();
						$capid = $capid['user_login'];
					}

					$query = sprintf( "	SELECT CONCAT( Rank, ' ', NameFirst, ' ', NameLast ) AS Name 
										FROM wp_capwatch_member 
										WHERE CAPID = %d",
								$capid );

					if ( $person_result = $db->query( $query ) ) {
						$person = $person_result->fetch_assoc();
						$person = $person['Name'];
					}

					$escaped_department = $db->real_escape_string( $report_data['department'] );
					$escaped_department = $db->real_escape_string( $escaped_department );

					$query = sprintf( "	SELECT positionName, positionOrder 
										FROM wp_officers 
										WHERE positionCAPID = %d 
											AND positionType = ( 
												SELECT positionTypeID 
												FROM wp_officers_types 
												WHERE positionTypeName = '%s' )
										ORDER BY positionOrder",
								$capid, $escaped_department );

					if ( $position_result = $db->query( $query ) ) {
						$position = $position_result->fetch_assoc();
						$positionOrderTag = "Order " . str_pad( $position['positionOrder'], 2, '0', STR_PAD_LEFT );
						$position = $position['positionName'];
					}

					$report_data['reporting_official'] = $person;

					if ( isset( $position ) ) {
						$report_data['reporting_official'] .= ", " . $position;
					}

					break;
				case 4:
					$query = sprintf( "	SELECT value 
										FROM `%s_long` 
										WHERE lead_detail_id = %d",
								$lead_detail_table, $row['id'] );
					$subresult = $db->query( $query );
					$subrow = $subresult->fetch_assoc();
					$report_data['report_items'] = $subrow['value'] ? $subrow['value'] : $row['value'];
					break;
				case 5:
					$query = sprintf( "	SELECT value 
										FROM `%s_long` 
										WHERE lead_detail_id = %d",
								$lead_detail_table, $row['id'] );
					$subresult = $db->query( $query );
					$subrow = $subresult->fetch_assoc();
					$report_data['new_business'] = $subrow['value'] ? $subrow['value'] : $row['value'];
					break;
				case 6:
					$query = sprintf( "	SELECT value 
										FROM `%s_long` 
										WHERE lead_detail_id = %d",
								$lead_detail_table, $row['id'] );
					$subresult = $db->query( $query );
					$subrow = $subresult->fetch_assoc();
					$report_data['old_business'] = $subrow['value'] ? $subrow['value'] : $row['value'];
					break;
				case 7:
					$query = sprintf( "	SELECT value 
										FROM `%s_long` 
										WHERE lead_detail_id = %d",
								$lead_detail_table, $row['id'] );
					$subresult = $db->query( $query );
					$subrow = $subresult->fetch_assoc();
					$report_data['attachments'] = $subrow['value'] ? $subrow['value'] : $row['value'];
					break;
				default:
					break;
			}
		}

		$reports[$department][$positionOrderTag] = $report_data;
	}

	// Page Header / Footer
	class PDF extends FPDF {
		function Footer() {
			$page = $this->PageNo();

			$this->SetY( -0.5 );
			$this->SetFont( 'Arial', 'B', 9 );
			$this->Cell( NULL, 0.2, "Page {$page} of {nb}", NULL, NULL, 'R' );
		}

		function SectionTitle( $title ) {
			$this->SetFont( 'Arial', 'B', 11 );
			$this->Cell( NULL, 0.3, "{$title}:", NULL, 1 );
			$this->SetFont( 'Arial', NULL, 10 );
		}

		function ReportOfficial( $name ) {
			$this->SetFont( 'Arial', 'B', 10 );
			$this->Write( 0.3, "Reporting Official: " );
			$this->SetFont( 'Arial', NULL, 10 );
			$this->Cell( NULL, 0.3, $name, NULL, 1 );
		}

		function PrintAttachment( $url ) {
			$this->SetFont( 'Arial', 'U', 10 );
			$this->SetTextColor( 0, 0, 255 );
			$this->Write( 0.2, end( explode( '/', $url ) ), $url );
			$this->SetFont( 'Arial', NULL, 10 );
			$this->SetTextColor( 0, 0, 0 );
		}
	}

	$pdf = new PDF( 'P', 'in', 'Letter' );
	$pdf->AliasNbPages();
	$pdf->SetMargins( 0.5, 0.5 );
	$pdf->SetAutoPageBreak( TRUE, 0.5 );
	$pdf->SetAuthor( 'Texas Wing Civil Air Patrol' );
	$pdf->SetCreator( 'www.txwgcap.org' );
	$pdf->SetTitle( 'Texas Wing Staff Report - ' . $reporting_period );

	$pdf->AddPage();

	$pdf->SetFont( 'Arial', 'B', 14 );
	$pdf->Cell( NULL, 0.3, "Texas Wing Staff Report", NULL, 1, 'C' );
	$pdf->Cell( NULL, 0.3, $reporting_period, NULL, 1, 'C' );
	$pdf->Ln();

	foreach( $departments as $department ) {
		empty( $report );
		$attr = str_replace( ' ', NULL, $department );
		$dept_reports = $reports[$attr];

		$pdf->SetFont( 'Arial', 'B', 11 );
		$pdf->Cell( NULL, 0.2, $department, NULL, 1 );
		$pdf->SetFont( 'Arial', NULL, 10 );

		if ( !$dept_reports ) {
			if ( $department == 'Command Staff' ) {
				$pdf->Cell( NULL, 0.3, "Command staff updates may be provided during the staff meeting.", NULL, 1 );
			}

			elseif ( $department == 'Group Commanders' || $department == 'Headquarters Staff' || $department == 'Legal' ) {
				$pdf->Cell( NULL, 0.3, "{$department} does not normally provide written monthly staff reports.", NULL, 1 );
			}

			else {
				$pdf->Cell( NULL, 0.3, "No Report Received", NULL, 1 );
			}

			$pdf->Cell( NULL, 0.2, NULL, 'B', 1 );
			$pdf->Ln();
			continue;
		}

		ksort( $dept_reports );

		foreach( $dept_reports as $report ) {
			$pdf->ReportOfficial( $report['reporting_official'] );

			$pdf->SectionTitle( 'Report Items' );
			$pdf->Write( 0.2, $report['report_items'] );
			$pdf->Ln( 0.3 );

			if ( $report['new_business'] ) {
				$pdf->SectionTitle( 'New Business' );
				$pdf->Write( 0.2, $report['new_business'] );
				$pdf->Ln( 0.3 );
			}

			if ( $report['old_business'] ) {
				$pdf->SectionTitle( 'Old Business' );
				$pdf->Write( 0.2, $report['old_business'] );
				$pdf->Ln( 0.3 );
			}

			if ( $report['attachments'] ) {
				$pdf->SectionTitle( 'Supporting Documentation' );
				$attachments = json_decode( $report['attachments'] );
				foreach( $attachments as $attachment ) {
					$pdf->PrintAttachment( $attachment );
					$pdf->Ln();
				}
			}

			$pdf->Ln();
		}

		$pdf->Cell( NULL, 0.2, NULL, 'B', 1 );
		$pdf->Ln();
	}

	$pdf->Output( 'Wing_Staff_Report_' . str_replace( ' ', '_', $reporting_period ) . '.pdf', 'I' );

	die();
}

function staff_report_print_body() {
	global $error;

	if ( !is_user_logged_in() ) {
	?>
		<div class="content-box-red"><strong>You must be logged in to access wing staff reports</strong></div>
	<?php 
		return;
	}

	if ( $error ) { ?>
		<div class="content-box-red"><strong><?php echo $error; ?></strong></div>
	<?php } ?>

<form action="<?php echo the_permalink(); ?>" method="post">
	<div id="date_entry">
		<p>
			<label for="month">Select Month: </label>

			<input type="month" id="reporting_period" name="reporting_period" value="<?php echo date( 'Y-m' ); ?>" />
		</p>
	</div>

	<div id="submit">
		<p>
			<input type="submit" name="action" value="Print Report" />
		</p>
	</div>
</form>
<?php 

}

add_action( 'genesis_post_content', 'staff_report_print_body' );

genesis();
