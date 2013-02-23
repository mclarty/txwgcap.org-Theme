<?php

$args = array(	'taxonomy' => 'document_categories',
				'term' => 'wing-sups',
				'doc_number' => 'TXWG Sup to',
				'doc_opr' => 'OPR',
				'post_type' => 'document',
				'nopaging' => true,
				'orderby' => 'meta_value',
				'order' => 'ASC',
				'meta_key' => 'document_number'
			);

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'txwg_docs_list' );

genesis();
