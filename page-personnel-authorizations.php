<?php

$args = array(	'taxonomy' => 'document_categories',
				'term' => 'wing-personnel-auth',
				'doc_number' => 'PA Number',
				'doc_opr' => NULL,
				'post_type' => 'document',
				'nopaging' => true,
				'orderby' => 'meta_value',
				'order' => 'ASC',
				'meta_key' => 'document_number'
			);

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'txwg_docs_list' );

genesis();
