/**
 * Pencil icon - edit Dashicon.
 *
 * @package Polylang-Pro
 */

/**
 * WordPress dependencies
 */
import { SVG, Path } from '@wordpress/primitives';
import { isUndefined } from 'lodash';

const isPrimitivesComponents = ! isUndefined( wp.primitives );

const pencil = isPrimitivesComponents ?
	(
		<SVG width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
			<Path d="M13.89 3.39l2.71 2.72c0.46 0.46 0.42 1.24 0.030 1.64l-8.010 8.020-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.030c0.39-0.39 1.22-0.39 1.68 0.070zM11.16 6.18l-5.59 5.61 1.11 1.11 5.54-5.65zM8.19 14.41l5.58-5.6-1.070-1.080-5.59 5.6z"></Path>
		</SVG>
	)
	: 'edit';

export default pencil;
