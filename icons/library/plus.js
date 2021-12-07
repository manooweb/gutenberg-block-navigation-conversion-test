/**
 * Plus icon - plus Dashicon.
 *
 * @package Polylang-Pro
 */

/**
 * WordPress dependencies
 */
import { SVG, Path } from '@wordpress/primitives';
import { isUndefined } from 'lodash';

const isPrimitivesComponents = ! isUndefined( wp.primitives );

const plus = isPrimitivesComponents ?
	(
	<SVG width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
		<Path d="M17 7v3h-5v5h-3v-5h-5v-3h5v-5h3v5h5z"></Path>
	</SVG>
	)
	: 'plus';

export default plus;
