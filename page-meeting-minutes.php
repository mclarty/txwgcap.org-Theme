<?php

$args = array(	'taxonomy' => 'document_categories',
				'term' => 'meeting-minutes',
				'doc_number' => NULL,
				'doc_opr' => NULL,
				'doc_title' => 'Title',
				'post_type' => 'document',
				'nopaging' => true,
				'orderby' => 'date',
				'order' => 'DEC'
			);

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'txwg_docs_list' );

genesis();
