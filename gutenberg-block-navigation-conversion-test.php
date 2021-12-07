<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
};

require_once 'block-editor-switcher-block.php';

add_action(
	'pll_init',
	function( $polylang ) {
		if ( $polylang->model->get_languages_list() ) {
			// if ( $polylang instanceof PLL_Admin ) {
			// 	$polylang->block_editor_plugin = new PLL_Block_Editor_Plugin( $polylang );
			// }

			// if ( $polylang instanceof PLL_Frontend ) {
			// 	$polylang->filters_widgets_blocks = new PLL_Frontend_Filters_Widgets_Blocks( $polylang );
			// }

			// $polylang->widget_editor = new PLL_Widget_Editor_Language_Attribute();
			$polylang->switcher_block = new PLL_Block_Editor_Switcher_Block( $polylang );
		}
	}
);
