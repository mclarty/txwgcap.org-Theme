<?php

// Custom post content
add_action( 'genesis_before_post_content', 'weather_page' );
remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );

//This file is needed to be able to use the wp_rss() function.
include_once( ABSPATH.WPINC.'/rss.php' );

function getWeatherWalletFeeds( $basin, $wallet ) {

	$basin_lower = strtolower( $basin );
	$rss = fetch_feed( "http://www.nhc.noaa.gov/nhc_{$basin_lower}{$wallet}.xml" );

	if ( preg_match( '/^.* No current storm/', $rss->get_title() ) ) {
		return false;
	}

	$items = $rss->get_items();
	foreach ( $items as $item ) {
		if ( preg_match( '/^.* Public Advisory.*/', $item->get_title() ) ) {
			$description = $item->get_description();
		}
	}

	$feeds = array(	'Public Advisory' => "http://www.nhc.noaa.gov/text/MIATCP{$basin}{$wallet}.shtml",
					'Forecast/Advisory' => "http://www.nhc.noaa.gov/text/MIATCM{$basin}{$wallet}.shtml",
					'Forecast Discussion' => "http://www.nhc.noaa.gov/text/MIATCD{$basin}{$wallet}.shtml",
					'Wind Speed Probabilities' => "http://www.nhc.noaa.gov/text/MIAPWS{$basin}{$wallet}.shtml",
					'Graphics' => "http://www.nhc.noaa.gov/graphics_{$basin_lower}{$wallet}.shtml",
					);

	$html = '<h5>' . $rss->get_title() . '</h5>';
	$html .= $description ? "<strong>{$description}</strong>" : NULL;
	$html .= '<ul>';

	foreach ( $feeds as $feed_title => $feed_url ) {
		$html .= '<li><a href="' . $feed_url . '">' . $feed_title . '</a></li>';
	}

	return $html;

}


function weather_page() {

	echo '<h4>Atlantic Basin / Carribean Islands and Gulf of Mexico</h4>';

	echo '<table><tr style="vertical-align: top;"><td style="width: 50%;">';

	echo '<h6><a href="http://www.nhc.noaa.gov/pdf/aboutnames_pronounce_atlc.pdf">Tropical Cyclone Names for the Atlantic Basin</a></h6>';

	$html = NULL;

	for ( $i = 1; $i <= 5; $i++ ) {
		$html .= getWeatherWalletFeeds( 'AT', $i );
	}

	if ( !$html ) {
		$html = '<h5>No active storms.</h5>';
		$two_rss = fetch_feed( "http://www.nhc.noaa.gov/xml/TWOAT.xml" );
		$item = $two_rss->get_item();
		if ( preg_match( '/^.* Tropical Weather Outlook.*/', $item->get_title() ) ) {
			$html .= $item->get_description();
		}
	}

	echo $html;

	echo '</td>';

	echo '<td><p><img src="http://www.nhc.noaa.gov/gtwo/two_atl.gif" /></p></td>';

	echo '</tr></table>';


	echo '<h4>Eastern North Pacific Basin</h4>';

	echo '<table><tr style="vertical-align: top;"><td style="width: 50%;">';

	echo '<h6><a href="http://www.nhc.noaa.gov/pdf/aboutnames_pronounce_epac.pdf">Tropical Cyclone Names for the Eastern Pacific Basin</a></h6>';

	$html = NULL;

	for ( $i = 1; $i <= 5; $i++ ) {
		$html = getWeatherWalletFeeds( 'EP', $i );
	}

	if ( !$html ) {
		$html = '<h5>No active storms.</h5>';
		$two_rss = fetch_feed( "http://www.nhc.noaa.gov/xml/TWOEP.xml" );
		$item = $two_rss->get_item();
		if ( preg_match( '/^.* Tropical Weather Outlook.*/', $item->get_title() ) ) {
			$html .= $item->get_description();
		}
	}

	echo $html;

	echo '</td>';

	echo '<td><p><img src="http://www.nhc.noaa.gov/gtwo/two_epac.gif" /></p></td>';

	echo '</tr></table>';

}

genesis();
