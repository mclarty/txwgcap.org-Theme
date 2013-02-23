<?php
/** Start the engine */
require_once( get_template_directory() . '/lib/init.php' );

/** Child theme (do not remove) */
define( 'CHILD_THEME_NAME', 'Education Theme' );
define( 'CHILD_THEME_URL', 'http://www.studiopress.com/themes/education' );

/** Enqueue the admin.css **/

function txwgcap_admin_init() {
	wp_register_style( 'txwgcap-admin-css', get_stylesheet_directory_uri() . '/admin.css' );
	wp_enqueue_style( 'txwgcap-admin-css' );
}

add_action( 'admin_init', 'txwgcap_admin_init' );


/** Create additional color style options */
add_theme_support( 'genesis-style-selector', array( 
	'education-black'	=> __( 'Black' , 'education' ), 
	'education-green'	=> __( 'Green' , 'education' ), 
	'education-purple'	=> __( 'Purple' , 'education' ), 
	'education-red'		=> __( 'Red' , 'education' ), 
	'education-teal'		=> __( 'Teal' , 'education' ) 
) );

add_action( 'genesis_meta', 'education_add_viewport_meta_tag' );
/** Add Viewport meta tag for mobile browsers */
function education_add_viewport_meta_tag() {

    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
    
}

add_action( 'genesis_meta', 'txwgcap_hack_ie' );

function txwgcap_hack_ie() { ?>

<!--[if lt IE 9]> <html class="lt-ie9"> <![endif]-->

<?php }


/** Add new image sizes */
add_image_size( 'featured-image', 150, 100, TRUE );

/** Add file types to media manager */
function txwg_mime_types( $mime_types ) {
	$mime_types['pub'] = 'application/x-mspublisher';	// Add Microsoft Publisher (.PUB)
	return $mime_types;
}
add_filter( 'upload_mimes', 'txwg_mime_types', 1, 1 );

/** Add structural wraps */
add_theme_support( 'genesis-structural-wraps', array(
	'header', 
	'nav', 
	'subnav', 
	'inner', 
	'footer-widgets', 
	'footer' 
) );

/** Add support for custom background */
add_theme_support( 'custom-background' );

/** Add support for custom header */
add_theme_support( 'genesis-custom-header', array( 
	'width' => 1140, 
	'height' => 120 
) );

/** Add support for 3-column footer widgets */
// Disabled by Nick McLarty
//add_theme_support( 'genesis-footer-widgets', 3 );

/** Reposition Primary Navigation */
remove_action( 'genesis_after_header', 'genesis_do_nav' );
add_action( 'genesis_before_content_sidebar_wrap', 'genesis_do_nav' );

/** Reposition Secondary Navigation */
remove_action( 'genesis_after_header', 'genesis_do_subnav' );
add_action( 'genesis_before_content_sidebar_wrap', 'genesis_do_subnav' );

/** Reposition Breadcrumbs */
remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
add_action( 'genesis_before_content', 'genesis_do_breadcrumbs' );

add_filter( 'genesis_comment_list_args', 'child_comment_list_args' );
/** Change avatar size */
function child_comment_list_args( $args ) {

    $args = array(
			'type' => 'comment',
			'avatar_size' => 33,
			'callback' => 'genesis_comment_callback'
		);
		
		return $args;
		
}

/** Register widget areas */
genesis_register_sidebar( array(
	'id'			=>	'slider',
	'name'			=>	__( 'Slider', 'education' ),
	'description'	=>	__( 'This is the slider section.', 'education' ),
) );
genesis_register_sidebar( array(
	'id'			=> 	'home_left',
	'name'			=>	__( 'Home Left', 'education' ),
	'description'	=>	__( 'This is the home left section displayed below the slider.', 'education' ),
) );
genesis_register_sidebar( array(
	'id'			=> 	'home_right',
	'name'			=>	__( 'Home Right', 'education' ),
	'description'	=>	__( 'This is the home right section displayed below the slider for authenticated users.', 'education' ),
) );
genesis_register_sidebar( array(
	'id'			=> 	'public_home_right',
	'name'			=>	__( 'Home Right (Public)', 'education' ),
	'description'	=>	__( 'This is the home right section displayed below the slider for public visitors.', 'education' ),
) );
genesis_register_sidebar( array(
	'id'			=> 	'intro',
	'name'			=>	__( 'Intro', 'education' ),
	'description'	=>	__( 'This is the intro section displayed below the slider.', 'education' ),
) );
genesis_register_sidebar( array(
	'id'			=>	'featured',
	'name'			=>	__( 'Featured', 'education' ),
	'description'	=>	__( 'This is the featured section displayed below the intro.', 'education' ),
) );
genesis_register_sidebar( array(
	'id'			=>	'call-to-action',
	'name'			=>	__( 'Call To Action', 'education' ),
	'description'	=>	__( 'This is the call to action banner.', 'education' ),
) );


/** Texas Wing Widgets **/

function add_read_private_cap_to_subscribers() {
	$role = get_role( 'subscriber' );
	$role->add_cap( 'read_private_posts' );
	$role->add_cap( 'read_private_pages' );
}

add_action( 'admin_init', 'add_read_private_cap_to_subscribers' );


function child_pages_shortcode( $atts = NULL ) {
   global $post;

   extract( shortcode_atts( array( 
	   		'echo' => 0,
	   		'depth' => 0,
	   		'title_li' => NULL,
	   		'child_of' => $post->ID
	   		), $atts
   		)
	);

    $vars = "echo={$echo}&depth={$depth}&title_li={$title_li}&child_of={$child_of}";

    if ( is_user_logged_in() ) {
    	$vars .= "&post_status=publish,private";
    }

    $childPages = wp_list_pages( $vars );

    if ( $childPages ) {
    	return $childPages;
    }
	
}

add_shortcode( 'children', 'child_pages_shortcode' );


function show_pii_disclaimer() {
	return '<h5 style="font-size: 11px; text-align: center;">Warning: The information you  are receiving is protected 
	from interception or disclosure.<br />
	Any person who intentionally distributes, reproduces or discloses its contents is subject to the<br />
	penalties set forth in 18 USC 2511 and/or related state and federal laws of the United States.</h5>';
}

function fouo_post_footer( $content ) {
	global $post;

	if ( get_post_status( $post->ID ) == 'private' ) {
		$content .= show_pii_disclaimer();
	}

	return $content;
}

add_filter( 'the_content', 'fouo_post_footer' );


function show_link_disclaimer() {
	return '<h5 style="font-size: 12px; text-align: center;">LINKS OR REFERENCES TO INDIVIDUALS OR COMPANIES DOES NOT 
	CONSTITUTE AN ENDORSEMENT<br />OF ANY INFORMATION, PRODUCT OR SERVICE YOU MAY RECEIVE FROM SUCH SOURCES.</h5>';
}

add_shortcode( 'link_disclaimer', 'show_link_disclaimer' );


function txwgcap_swap_private_header( $title ) {
	$title = str_replace( 'Private:', '(FOUO)', $title );

	return $title;
}

add_filter( 'the_title', 'txwgcap_swap_private_header' );


function txwgcap_membership( $atts ) {
	global $wpdb;

	extract( shortcode_atts( array(
			'type' => NULL,
			), $atts
		)
	);

	$qry = "SELECT COUNT(*) AS Count FROM wp_capwatch_member WHERE Wing = 'TX' AND STR_TO_DATE( Expiration, '%m/%d/%Y' ) >= CURDATE() AND ";

	switch( $type ) {
		case 'SENIOR':
			$qry .= "Type <> 'CADET'";
			break;
		case 'CADET':
			$qry .= "Type = 'CADET'";
			break;
		default:
			$qry .= "1";
			break;
	}

	$rs = $wpdb->get_results( $qry );

	return number_format( $rs[0]->Count );
}

add_shortcode( 'txwgcap_membership', 'txwgcap_membership' );


function capwatch_lastUpdated( $atts ) {
	extract( shortcode_atts( array(
			'format' => 'r',
			), $atts
		)
	);

	return date( $format, get_option( 'capwatch_lastUpdated' ) );
}

add_shortcode( 'capwatch_lastupdated', 'capwatch_lastUpdated' );


class UpdatesWidget extends WP_Widget {

	
	function UpdatesWidget() {
			parent::__construct( 'updates_widget', 'Updates Widget' );
	
		}


	
	function widget( $args, $instance ) {
	
		$post_args['category'] = is_user_logged_in() ? 35 : 34;
		$post_args['numberposts'] = 1;

		$post_array = get_posts( $post_args );

		foreach ( $post_array as $post ) : setup_postdata( $post );

			echo '<div class="updates_widget"><h2>' . $post->post_title . '</h2>' . 
				apply_filters( 'the_content', $post->post_content ) . '</div>';

		endforeach;

	}

}


class WingCCWidget extends WP_Widget {

	
	function WingCCWidget() {
			parent::__construct( 'wing_cc_msg', 'Message from the Wing Commander' );
	
		}


	
	function widget( $args, $instance ) {
	
		$post_args['category'] = is_user_logged_in() ? 27 : 3;
		$post_args['numberposts'] = 1;

		$cat_slug = is_user_logged_in() ? 'private-wing-cc-msgs' : 'public-wing-cc-msgs';

		$post_array = get_posts( $post_args );

		foreach ( $post_array as $post ) : setup_postdata( $post );

			if ( $post->post_excerpt ) {
				$content = apply_filters( 'the_excerpt', $post->post_excerpt ) .
				' [<a href="' . get_permalink( $post->ID ) . '">Read More</a>]';
			} else {
				$content = apply_filters( 'the_content', $post->post_content );
			}

			echo '<div class="wing_cc_msg"><h2>Message from the Wing Commander</h2>' . 
				get_the_post_thumbnail( $post->ID, array( 200, 200 ) ) . 
				$content . '</div>';

		endforeach;

	}

}


class NTASWidget extends WP_Widget {

	function NTASWidget() {
		parent::__construct( 'ntas_widget', 'National Terrorism Advisory System' );
	}

	function widget( $args, $instance ) {

?> 
<div class="widget widget-wrap">
	<h4>National Terrorism<br />Advisory System</h4>
	<a href="http://www.dhs.gov/alerts">
		<img src="http://www.dhs.gov/xlibrary/graphics/ntas/dhs-ntas-badge-small.jpg" alt="National Terrorism Advisory System (NTAS) check current status" />
	</a>
</div> 
<?php

	}
}


class UnitsMapWidget extends WP_Widget {

	function UnitsMapWidget() {
		$widget_opts = array();
		$control_opts = array();
		$this->WP_Widget( 'units-map-widget', 'Texas Wing Units Map Widget', $widget_opts, $control_opts );
	}

	function form( $instance ) {
		?>
		<p>
			<label for="<?php echo $this->get_field_name( 'google_maps_api' ); ?>">Google Maps API Key: </label>
			<input class="widefat" id="<?php echo $this->get_field_name( 'google_maps_api' ); ?>" name="<?php echo $this->get_field_name( 'google_maps_api' ); ?>" 
				type="text" value="<?php echo $instance['google_maps_api']; ?>" />
		</p>
		<?php 
	}

	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['google_maps_api'] = $new_instance['google_maps_api'];

		return $instance;
	}

	function widget( $args, $instance ) {
		$key = $instance['google_maps_api'];
		wp_enqueue_script( "google_maps_api", "http://maps.googleapis.com/maps/api/js?key={$key}&amp;sensor=false" );
		?>
		<script type="text/javascript">
		<!--
			function UnitsMapInit() {
				var mapOptions = {
					center: new google.maps.LatLng( 31, -99.5 ),
					zoom: 6,
					mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				var map = new google.maps.Map( document.getElementById( "map_canvas" ),
					mapOptions );
				var georssLayer = new google.maps.KmlLayer( "<?php echo site_url( '/units-kml' ); ?>" );
				georssLayer.setMap( map );
			}

			window.onload = UnitsMapInit;
		-->
		</script>
		<div id="map_canvas" style="height: 500px;"></div>
		<?php 
	}

}

function txwg_units_map() {
	ob_start();
	the_widget( 'UnitsMapWidget', $instance );
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}
add_shortcode('unitsmap', 'txwg_units_map');

function txwg_register_widgets() {
	register_widget( 'WingCCWidget' );
	register_widget( 'NTASWidget' );
	register_widget( 'UnitsMapWidget' );
	register_widget( 'UpdatesWidget' );
}

add_action( 'widgets_init', 'txwg_register_widgets' );


/** Document Category Custom Taxonomy **/

add_action( 'init', 'txwg_register_taxonomy_document_categories' );



function txwg_register_taxonomy_document_categories() {

    
	$labels = array( 
	'name' => _x( 'Document Categories', 'document_categories' ),
						'singular_name' => _x( 'Document Category', 'document_categories' ),
						'search_items' => _x( 'Search Document Categories', 'document_categories' ),
						'popular_items' => _x( 'Popular Document Categories', 'document_categories' ),
						'all_items' => _x( 'All Document Categories', 'document_categories' ),
						'parent_item' => _x( 'Parent Document Category', 'document_categories' ),
						'parent_item_colon' => _x( 'Parent Document Category:', 'document_categories' ),
						'edit_item' => _x( 'Edit Document Category', 'document_categories' ),
						'update_item' => _x( 'Update Document Category', 'document_categories' ),
						'add_new_item' => _x( 'Add New Document Category', 'document_categories' ),
						'new_item_name' => _x( 'New Document Category', 'document_categories' ),
						'separate_items_with_commas' => _x( 'Separate document categories with commas', 'document_categories' ),
						'add_or_remove_items' => _x( 'Add or remove document categories', 'document_categories' ),
						'choose_from_most_used' => _x( 'Choose from the most used document categories', 'document_categories' ),
						'menu_name' => _x( 'Document Categories', 'document_categories' ),
    
					);
	$args = array(		'labels' => $labels,
						'public' => true,
						'show_in_nav_menus' => true,
						'show_ui' => true,
						'show_tagcloud' => true,
						'hierarchical' => true,
						'rewrite' => true,
						'query_var' => true
				
    );

	register_taxonomy( 'document_categories', array('document'), $args );
}


/** Document Custom Meta **/

add_action( 'add_meta_boxes', 'txwg_add_document_data_metabox' );



function txwg_add_document_data_metabox() {
	add_meta_box(	'document_data_metabox',
					'Form/Publication Data',
					'txwg_add_document_data_metabox_html',
					'document',
					'side',
					'high'
				);
}

function txwg_add_document_data_metabox_html() {
	global $post;
	
	$document_number = get_post_meta( $post->ID, 'document_number', TRUE );
	$document_date = get_post_meta( $post->ID, 'document_date', TRUE );
	$document_opr = get_post_meta( $post->ID, 'document_opr', TRUE );
	
	echo '<label for="document_number">Form/Publication Number: </label><input type="text" name="document_number" size="10" 
		value="' . $document_number . '" /><br />';
	echo '<label for="document_date">Form/Publication Date: </label><input type="text" name="document_date" size="10" 
		value="' . @date( 'd M Y', $document_date ) . '" /><br />';
	echo '<label for="document_opr">Form/Publication OPR: </label><input type="text" name="document_opr" size="10" 
		value="' . $document_opr . '" />';	
}

add_action( 'save_post', 'txwg_save_document_data' );

function txwg_save_document_data( $post_id ) {
	if ( 'document' != $_POST['post_type'] ) {
		return;
	}

	if ( !current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$meta['document_number'] = $_POST['document_number'];
	$meta['document_date'] = $_POST['document_date'] ? strtotime( $_POST['document_date'] ) : NULL;
	$meta['document_opr'] = $_POST['document_opr'];
	
	foreach ( $meta as $key => $val ) {
		update_post_meta( $post_id, $key, $val );
		if ( !$val ) delete_post_meta( $post_id, $key );
	}
}


/** Documents Listing **/

function txwg_docs_list() {
	global $args;
	$docs_array = get_documents( $args );
?>
<h2><?php the_title(); ?></h2>
<table class="docs_table">
	<tr>
		<?php if ( $args['doc_number'] ) { ?><th class="doc_number_col"><?php echo $args['doc_number']; ?></th><?php } ?>
		<th class="doc_date_col">Date</th>
		<?php if ( $args['doc_opr'] ) { ?><th class="doc_opr_col">OPR</th><?php } ?>
		<th class="doc_title_col"><?php if ( $args['doc_title'] ) echo $args['doc_title']; else echo 'Title'; ?></th>
	</tr>
<?php foreach ( $docs_array as $doc ) : ?>
	<tr>
		<?php if ( $args['doc_number'] ) { ?>
		<td class="doc_number_col">
			<a href="<?php echo get_permalink( $doc->ID ); ?>"><?php echo get_post_meta( $doc->ID, 'document_number', TRUE ); ?></a>
		</td>
		<?php } ?>
		<td class="doc_date_col"><?php echo @date( 'd M Y', get_post_meta( $doc->ID, 'document_date', TRUE ) ); ?></td>
		<?php if ( $args['doc_opr'] ) { ?>
		<td class="doc_opr_col"><?php echo get_post_meta( $doc->ID, 'document_opr', TRUE ); ?></td>
		<?php } ?>
		<td class="doc_title_col">
			<?php if ( !$args['doc_number'] ) { ?><a href="<?php echo get_permalink( $doc->ID ); ?>"><?php } ?>
			<?php echo $doc->post_title; ?>
			<?php if ( !$args['doc_number'] ) { ?></a><?php } ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php
}


/** Convert numbers to roman numerals for the group listings **/

function convertToRoman( $int ) {
	$romans = array( 'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1 );

	foreach ( $romans as $letter => $number ) {
		while ( $int >= $number ) {
			$str .= $letter;
			$int -= $number;
		}
	}

	return $str;
}


/** Format phone numbers **/

function formatPhoneNumber( $phoneNumber ) {
	if ( strlen( $phoneNumber ) == 10 ) {
		return sprintf( '(%s) %s-%s', substr( $phoneNumber, 0, 3), substr( $phoneNumber, 3, 3), substr( $phoneNumber, 6, 4) );
	} else {
		return $phoneNumber;
	}
}


/** Set up KML for unit locations **/

function generateUnitKML() {
	global $wpdb;

	$rows = $wpdb->get_results( "SELECT wp_capwatch_org.ORGID, 
										wp_capwatch_org.Name, 
										wp_capwatch_org.Wing, 
										wp_capwatch_org.Unit, 
										wp_capwatch_org_address.Addr1, 
										wp_capwatch_org_address.Addr2, 
										wp_capwatch_org_address.City, 
										wp_capwatch_org_address.State, 
										wp_capwatch_org_address.Zip, 
										wp_capwatch_org_address.Latitude,
										wp_capwatch_org_address.Longitude 
								FROM wp_capwatch_org 
								INNER JOIN wp_capwatch_org_address ON wp_capwatch_org.ORGID = wp_capwatch_org_address.ORGID 
								WHERE wp_capwatch_org.Wing = 'TX' AND wp_capwatch_org_address.Type = 'MEETING' 
								ORDER BY wp_capwatch_org.Unit
								" );

	$kml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$kml .= "<kml xmlns=\"http://www.opengis.net/kml/2.2\">\n";
	$kml .= "  <Folder id=\"TXWG_Units\">\n";
	$kml .= "    <name>Texas Wing Units</name>\n";
	$kml .= "    <open>1</open>\n";

	foreach ( $rows as $row ) {
		$long = trim( $row->Longitude );
		$lat = trim( $row->Latitude );
		$desc = $row->Name . "\n" . $row->Addr1;
		$desc .= $row->Addr2 ? "\n" . $row->Addr2 : NULL;
		$desc .= "\n" . $row->City . ", " . $row->State . " " . substr( $row->Zip, 0, 5 );

		if ( $long < -90 && $lat > 20 ) {
			$kml .= "    <Placemark id=\"{$row->ORGID}\">\n";
			$kml .= "      <name>{$row->Wing}-{$row->Unit}</name>\n";
			$kml .= "      <description>{$desc}</description>\n";
			$kml .= "      <Point>\n";
			$kml .= "        <coordinates>{$long},{$lat}</coordinates>\n";
			$kml .= "      </Point>\n";
			$kml .= "    </Placemark>\n";
		}
	}

	$kml .= "  </Folder>\n";
	$kml .= "</kml>";

	return $kml;
}


/** Texas Wing Options Dashboard **/

function show_txwgcap_menu() {
	global $wpdb;

	if ( $_POST['exclude_opr'] ) {
		$data = strtolower( $_POST['exclude_opr'] );
		update_option( 'txwgcap_exclude_opr', $data );
		echo '<div class="updated">Excluded office symbols updated.</div>';
	}

	if ( $_POST['group_map_url'] ) {
		$data = $_POST['group_map_url'];
		update_option( 'txwgcap_group_map_url', $data );
		echo '<div class="updated">Group map URL updated.</div>';
	}

	?>
	<div id="wpbody">
		<div id="wpbody-content">
			<div class="wrap">

				<div id="icon-edit" class="icon32 icon32-txwgcap-options"><br /></div>

				<h2>Texas Wing Options Dashboard</h2>

				<div class="metabox-holder postbox">
					<h3 class="hdnle">Texas Wing Options</h3>
					<div class="inside">

						<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
							<label for="exclude_orgs">Office symbols of department pages to exclude department-wide staff list (comma delimited)</label>
							<input type="text" name="exclude_opr" id="exclude_opr" value="<?php echo get_option( 'txwgcap_exclude_opr' ); ?>" />
							<input type="submit" value="Update" />
						</form>

						<br />

						<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
							<label for="group_map_url">Groups Map URL</label>
							<input type="text" style="width: 500px;" name="group_map_url" id="group_map_url" value="<?php echo get_option( 'txwgcap_group_map_url' ); ?>" />
							<input type="submit" value="Update" />
						</form>

					</div>

				</div>

			</div>
		</div>
	</div>
	<?php 
}

add_action( 'admin_menu', 'register_txwgcap_menu' );

function register_txwgcap_menu() {
	add_menu_page( 'Texas Wing Options Dashboard', 'TXWG Options', 'manage_options', 'txwgcap', 'show_txwgcap_menu', '/wp-content/themes/txwgcap/images/favicon.ico', 51 );
}
