<?php

/**
 * Template Name: Department Home Page
 */

// Two-column

function txwgcap_home_layout( $layout ) {
	return 'content-sidebar';
}

add_filter( 'genesis_pre_get_option_site_layout', 'txwgcap_home_layout' );


// Custom body class
add_filter( 'body_class', 'add_body_class' );

function add_body_class( $classes ) {
	$classes[] = 'department-home';
	return $classes;
}

// Custom post content
add_action( 'genesis_post_content', 'department_body' );
add_action( 'genesis_post_content', 'department_related_items' );
add_action( 'genesis_before_sidebar_widget_area', 'department_sidebar' );
remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );


function department_body( $post ) {
	return $post->the_content;
}

function department_sidebar() {
	global $post, $wpdb;
	
	$qry = "SELECT wp_officers.*, (SELECT Rank FROM wp_capwatch_member WHERE CAPID = wp_officers.positionCAPID LIMIT 1) AS Rank, ";
	$qry.= "(SELECT CONCAT(NameFirst, ' ', NameLast) FROM wp_capwatch_member WHERE CAPID = wp_officers.positionCAPID LIMIT 1) AS Name, ";
	$qry.= "(SELECT Contact FROM wp_capwatch_member_contact WHERE Type = 'EMAIL' AND Priority = 'PRIMARY' AND CAPID = wp_officers.positionCAPID LIMIT 1) AS Email ";
	$qry.= "FROM wp_officers WHERE positionShortname = '{$post->post_name}'";

	$officersSelection = $wpdb->get_results( $qry );

	if ( $officersSelection[0]->positionCAPID || $officersSelection[0]->positionOfficer ) {
		$officersSelection[0]->positionOfficer = $officersSelection[0]->positionOfficer ? $officersSelection[0]->positionOfficer : 
			$officersSelection[0]->Name;
	} else {
		$officersSelection[0]->positionOfficer = 'Vacant';
	}

	$officersSelection[0]->positionEmail = $officersSelection[0]->positionEmail ? $officersSelection[0]->positionEmail : $officersSelection[0]->Email;

?>
	<div id="dept_sidebar" class="widget widget_text">
		<div id="dept_head">
			<?php if ( $officersSelection[0]->positionPhoto ) { ?>
			<div id="dept_insignia_photo">
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/badges/<?php echo $post->post_name; ?>.png" />
			</div>
			<div id="dept_head_photo">
				<img src="<?php echo $officersSelection[0]->positionPhoto; ?>" />
			</div>
			<div class="clear"></div>
			<?php } else { ?>
			<div id="dept_insignia">
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/badges/<?php echo $post->post_name; ?>.png" />
			</div>
			<?php } ?>
			<h2><?php echo $officersSelection[0]->positionName; ?></h2>
			<h3><?php echo stripslashes( $officersSelection[0]->Rank . ' ' . $officersSelection[0]->positionOfficer ); ?></h3>
			<?php if ( is_user_logged_in() ) { ?><a href="mailto:<?php echo $officersSelection[0]->positionEmail; ?>">Email</a><?php } else { ?>
				<a href="/contact?officer=<?php echo $post->post_name; ?>">Email</a>
			<?php } ?>
		</div>
		<?php

		$excludeOPR = explode( ',', get_option( 'txwgcap_exclude_opr' ) );

		if ( !in_array( $officersSelection[0]->positionType, array( 1, 22, 23 ) ) && !in_array( $post->post_name, $excludeOPR ) ) {
			$qry = "SELECT wp_officers.*, (SELECT Rank FROM wp_capwatch_member WHERE CAPID = wp_officers.positionCAPID) AS Rank, ";
			$qry.= "(SELECT CONCAT(NameFirst, ' ', NameLast) FROM wp_capwatch_member WHERE CAPID = wp_officers.positionCAPID) AS Name, ";
			$qry.= "(SELECT Contact FROM wp_capwatch_member_contact WHERE Type = 'EMAIL' AND Priority = 'PRIMARY' AND CAPID = wp_officers.positionCAPID) ";
			$qry.= "AS Email FROM wp_officers WHERE positionType = {$officersSelection[0]->positionType} AND positionOrder > 1 ";
		} else {
			$qry = "SELECT wp_officers.*, (SELECT Rank FROM wp_capwatch_member WHERE CAPID = wp_officers.positionCAPID) AS Rank, ";
			$qry.= "(SELECT CONCAT(NameFirst, ' ', NameLast) FROM wp_capwatch_member WHERE CAPID = wp_officers.positionCAPID) AS Name, ";
			$qry.= "(SELECT Contact FROM wp_capwatch_member_contact WHERE Type = 'EMAIL' AND Priority = 'PRIMARY' AND CAPID = wp_officers.positionCAPID) ";
			$qry.= "AS Email FROM wp_officers WHERE positionShortname LIKE '{$officersSelection[0]->positionShortname}-%' AND positionOrder > 1 ";
		}

		if ( !is_user_logged_in() ) {
			$qry .= "AND positionPublic = 1 ";
		}

		$qry .= "ORDER BY positionOrder";

		$officersSelection = $wpdb->get_results( $qry );

		if ( count( $officersSelection ) ) {

			echo '<div id="dept_staff">';

			foreach( $officersSelection as $officer ) {
				if ( $officer->positionCAPID || $officer->positionOfficer ) {
					$officer->positionOfficer = $officer->positionOfficer ? $officer->positionOfficer : 
						$officer->Name;
				} else {
					$officer->positionOfficer = 'Vacant';
				}

				$officer->positionEmail = $officer->positionEmail ? $officer->positionEmail : $officer->Email;

				echo '<p>';
				echo "<h2>{$officer->positionName}</h2>";
				echo "<h3>{$officer->Rank} " . stripslashes( $officer->positionOfficer ) . "</h3>";
				if ( is_user_logged_in() ) { 
					if ( $officer->positionEmail ) echo "<a href=\"mailto:{$officer->positionEmail}\">Email</a>"; 
				} else {
					if ( $officer->positionEmail ) echo "<a href=\"/contact?officer={$officer->positionShortname}\">Email</a>";
				}
				echo '</p>';
			}

			echo '</div>';

		}

?>
	</div>

<?php

}

genesis();
