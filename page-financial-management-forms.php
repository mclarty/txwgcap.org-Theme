<?php

$args = array(	'taxonomy' => 'document_categories',
				'term' => 'financial-management-forms',
				'doc_number' => NULL,
				'doc_opr' => 'OPR',
				'doc_title' => 'Form Title',
				'post_type' => 'document',
				'nopaging' => true,
				'orderby' => 'title',
				'order' => 'ASC'
			);

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'txwg_docs_list' );

genesis();
