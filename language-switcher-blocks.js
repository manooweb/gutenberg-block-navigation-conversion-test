/**
 * Register language switcher block.
 *
 *  @package Polylang-Pro
 */

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType, createBlock } from '@wordpress/blocks';
import { Fragment} from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { addFilter } from "@wordpress/hooks";
import {
	Disabled,
	PanelBody,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { find } from 'lodash';

/**
 * Internal dependencies
 */
import { translation } from "./icons";
import { createLanguageSwitcherEdit } from './language-switcher-edit'

const blocktitle = __( 'Language switcher', 'polylang-pro' );
const descriptionTitle = __( 'Add a language switcher to allow your visitors to select their preferred language.', 'polylang-pro' );
const panelTitle = __( 'Language switcher Settings', 'polylang-pro' );

// Register the Language Switcher block as first level block in Block Editor.
registerBlockType(
	'polylang/language-switcher',
	{
		title: blocktitle,
		description: descriptionTitle,
		icon: translation,
		category: 'widgets',
		example: {},
		edit: ( props ) => {
			const { dropdown } = props.attributes;

			const {
				ToggleControlDropdown,
				ToggleControlShowNames,
				ToggleControlShowFlags,
				ToggleControlForceHome,
				ToggleControlHideCurrent,
				ToggleControlHideIfNoTranslations,
			} = createLanguageSwitcherEdit( props );

			return (
				<Fragment>
					<InspectorControls>
						<PanelBody title={ panelTitle }>
							<ToggleControlDropdown/>
							{ ! dropdown && <ToggleControlShowNames/> }
							{ ! dropdown &&	<ToggleControlShowFlags/> }
							<ToggleControlForceHome/>
							{ ! dropdown &&	<ToggleControlHideCurrent/>	}
							<ToggleControlHideIfNoTranslations/>
						</PanelBody>
					</InspectorControls>
					<Disabled>
						<ServerSideRender
							block="polylang/language-switcher"
							attributes={ props.attributes }
						/>
					</Disabled>
				</Fragment>
			);
		},
	}
);

// Register the Language Switcher block as child block of core/navigation block.
const navigationLanguageSwitcherName = 'polylang/navigation-language-switcher'
registerBlockType(
	navigationLanguageSwitcherName,
	{
		title: blocktitle,
		description: descriptionTitle,
		icon: translation,
		category: 'widgets',
		parent: [ 'core/navigation' ],
		transforms: {
			from: [
				{
					type: 'block',
					blocks: [ 'core/navigation-link' ],
					transform: () => createBlock( navigationLanguageSwitcherName )
				}
			]
		},
			example: {},
		edit: ( props ) => {
			const { dropdown } = props.attributes;

			const {
				ToggleControlDropdown,
				ToggleControlShowNames,
				ToggleControlShowFlags,
				ToggleControlForceHome,
				ToggleControlHideCurrent,
				ToggleControlHideIfNoTranslations,
			} = createLanguageSwitcherEdit( props );

			return (
				<Fragment>
					<InspectorControls>
						<PanelBody title={ panelTitle }>
							<ToggleControlDropdown/>
							<ToggleControlShowNames/>
							<ToggleControlShowFlags/>
							<ToggleControlForceHome/>
							{ ! dropdown && <ToggleControlHideCurrent/> }
							<ToggleControlHideIfNoTranslations/>
						</PanelBody>
					</InspectorControls>
					<Disabled>
						<ServerSideRender
							block={ navigationLanguageSwitcherName }
							attributes={ props.attributes }
						/>
					</Disabled>
				</Fragment>
			);
		},
	}
);

function mapBlockTree( blocks, menuItems, blocksMapping, mapper ) {
	return blocks.map(
		block => (
			{
				...mapper( block, menuItems, blocksMapping ),
				innerBlocks: mapBlockTree( block.innerBlocks, menuItems, blocksMapping, mapper )
			}
		)
	 );;
}

addFilter(
	'navigation.menuItemsToBlocks',
	'polylang/include-language-switcher',
	( blocks, menuItems ) => (
		{
			...blocks,
			innerBlocks: mapBlockTree(
				blocks.innerBlocks,
				menuItems,
				blocks.mapping,
				( block, menuItems, blocksMapping ) => {
					if( block.name === "core/navigation-link" && block.attributes?.url === "#pll_switcher" ) {
						const menuItem = find( menuItems, { url: '#pll_switcher' } ); // Get the corresponding menu item.
						const attributes = Object.assign( {}, menuItem.meta._pll_menu_item ); // Get its options.
						const newBlock = createBlock( navigationLanguageSwitcherName, Object.fromEntries( Object.entries( attributes).map( ( [ attributeName, attributeValue ] ) => [ attributeName, !! attributeValue ] ) ) );
						blocksMapping[ menuItem.id ] = newBlock.clientId;
						return newBlock;
					}
					return block;
				}
			)
	 	}
	)
);

