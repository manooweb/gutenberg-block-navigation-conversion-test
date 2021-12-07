<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
};

require_once 'block-editor-switcher-block.php';

add_action(
	'pll_init',
	function( $polylang ) {
		if ( $polylang->model->get_languages_list() ) {
			$polylang->switcher_block = new PLL_Block_Editor_Switcher_Block( $polylang );
		}
	}
);
