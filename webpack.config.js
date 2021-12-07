/**
 * External dependencies
 */
const path = require( 'path' );
const TerserPlugin = require( 'terser-webpack-plugin' );

function configureWebpack( options){
	const mode = options.mode;
	const isProduction = mode === 'production' || false;
	console.log('Webpack mode:', mode);
	console.log('isProduction:', isProduction);
	console.log('dirname:', __dirname);

	/**
	 *  Prepare Block editor bundle
	 */

	// Reference to external library dependencies.
	const externals = {
		react: 'React',
		lodash: 'lodash',
	};

	/**
	 * Given a string, returns a new string with dash separators converted to
	 * camelCase equivalent. This is not as aggressive as `_.camelCase` in
	 * converting to uppercase, where Lodash will also capitalize letters
	 * following numbers.
	 *
	 * @param {string} string Input dash-delimited string.
	 * @return {string} Camel-cased string.
	 */
	function camelCaseDash( string ) {
		return string.replace(
			/-([a-z])/g,
			( match, letter ) => letter.toUpperCase()
		);
	}

	// WordPress package dependencies declared as externals.
	const wpDependencies = [
		'api-fetch',
		'block-editor',
		'blocks',
		'components',
		'compose',
		'data',
		'date',
		'editor',
		'edit-post',
		'element',
		'hooks',
		'html-entities',
		'i18n',
		'keycodes',
		'plugins',
		'primitives',
		'server-side-render',
		'url',
		'utils'
	];
	wpDependencies.forEach(name => {
		externals[ `@wordpress/${ name }` ] = {
			this: ["wp", camelCaseDash( name ) ]
		};
	});

	/**
	 *  Prepare Block editor css file generation preprocess with sass and js file transpilation with babel.
	 */

	// Active source maps only for development mode.
	const devtool = ! isProduction ? 'source-map' : false;

	// Where to find external packages.
	const resolve = {
		modules: [__dirname, 'node_modules']
	};

	// Common Output configuration.
	const blockEditorOutputConfig = {
		path: __dirname,
		library: ['manooweb'],
		libraryTarget: 'this'
	}

	// Common configuration to minify js files.
	const blockEditorMinimizeOptimizationConfig = {
		minimize: true,
		minimizer: [
			new TerserPlugin(
				{
					terserOptions: {
						format: {
							comments: false,
						},
					},
					extractComments: false,
				}
			),
		]
	};

	// Common configuration not to minify js files.
	const blockEditorUnMinimizeOptimizationConfig = {
		minimize: false,
	};

	// Common configuration to transpile js files with babel.
	const blockEditorModuleTranspilationConfig = [
		{
			test: /\.js$/,
			exclude: /node_modules/,
			use: 'babel-loader'
		}
	];

	// Prepare Block editor both entry point sidebar and blocks config for minified files.
	const blockEditorMinifiedEntryPointConfig = [
		{
			entry: { 'language-switcher-blocks': 'language-switcher-blocks.js' },
			output: Object.assign( {}, { filename: './dist/[name].min.js' }, blockEditorOutputConfig ),
			externals,
			resolve,
			module: {
				rules: [
					...blockEditorModuleTranspilationConfig,
				]
			},
			plugins: [
			],
			devtool,
			optimization: blockEditorMinimizeOptimizationConfig,
		}
	];

	// Prepare Block editor both entry point sidebar and blocks config for unminified files.
	const blockEditorUnminifiedEntryPointConfig = [
		{
			entry: { 'language-switcher-blocks': 'language-switcher-blocks.js' },
			output: Object.assign( {}, { filename: './dist/[name].js' }, blockEditorOutputConfig ),
			externals,
			resolve,
			module: {
				rules: [
					...blockEditorModuleTranspilationConfig,
				],
			},
			plugins: [
			],
			devtool,
			optimization: blockEditorUnMinimizeOptimizationConfig,
		},
	];

	// Concatenate Block editor both entry point sidebar and blocks config for both minified and unminified files.
	const blockEditorEntryPointConfig = [
		...blockEditorMinifiedEntryPointConfig,
		...blockEditorUnminifiedEntryPointConfig,
	]

	// Make webpack configuration.
	const config = [
		...blockEditorEntryPointConfig, // Add config for Block editor entry points.
	];

	return config;
}

module.exports = ( env, options ) => {
	return configureWebpack( options );
}
