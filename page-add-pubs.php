<?php

$args = array(	'taxonomy' => 'document_categories',
				'term' => 'wing-add-pubs',
				'doc_number' => NULL,
				'doc_opr' => 'OPR',
				'doc_title' => 'Policy Letter Subject',
				'post_type' => 'document',
				'nopaging' => true,
				'orderby' => 'title',
				'order' => 'ASC'
			);

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'txwg_docs_list' );

genesis();
