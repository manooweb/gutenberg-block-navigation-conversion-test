<?php
/**
 * @package Polylang-Pro
 */

/**
 * Language switcher block
 *
 * @since 2.8
 */
class PLL_Block_Editor_Switcher_Block {
	/**
	 * @var PLL_Links
	 */
	protected $links;

	/**
	 * @var PLL_Model
	 */
	protected $model;

	/**
	 * Current lang to render the language switcher block in an admin context
	 *
	 * @since 2.8
	 *
	 * @var string
	 */
	public $admin_current_lang;

	/**
	 * Is the context block editor?
	 *
	 * @since 2.8
	 *
	 * @var bool
	 */
	public $is_block_editor = false;

	/**
	 * Constructor
	 *
	 * @since 2.8
	 *
	 * @param PLL_Frontend|PLL_Admin|PLL_Settings|PLL_REST_Request $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->model = &$polylang->model;
		$this->links = &$polylang->links;

		// Use rest_pre_dispatch_filter to get additionnal parameters for language switcher block.
		add_filter( 'rest_pre_dispatch', array( $this, 'get_rest_query_params' ), 10, 3 );
		// Register language switcher block.
		add_action( 'init', array( $this, 'register_block_polylang_language_switcher' ) );

		// add_action( 'rest_api_init', array( $this, 'register_switcher_menu_item_options_meta_rest_field' ) );

		add_filter( 'widget_types_to_hide_from_legacy_widget_block', array( $this, 'hide_legacy_widget' ) );
	}

	/**
	 * Renders the `polylang/language-switcher` block on server.
	 *
	 * @since 2.8
	 *
	 * @param array $attributes The block attributes.
	 * @return string Returns the language switcher.
	 */
	public function render_block_polylang_language_switcher( $attributes = array() ) {
		$attributes['echo'] = 0;

		static $dropdown_id = 0;
		$dropdown_id++;

		// Sets a unique id for dropdown in PLL_Switcher::the_language().
		$attributes['dropdown'] = empty( $attributes['dropdown'] ) ? 0 : $dropdown_id;

		if ( $this->is_block_editor ) {
			$attributes['admin_render'] = 1;
			$attributes['admin_current_lang'] = $this->admin_current_lang;
			$attributes['hide_if_empty'] = 0;
			$attributes['hide_if_no_translation'] = 0; // Force not to hide the language for the block preview even if the option is checked.
		}

		$switcher = new PLL_Switcher();
		$switcher_output = $switcher->the_languages( $this->links, $attributes );

		if ( $attributes['dropdown'] ) {
			$switcher_output = '<label class="screen-reader-text" for="' . esc_attr( 'lang_choice_' . $attributes['dropdown'] ) . '">' . __( 'Choose a language', 'polylang' ) . '</label>' . $switcher_output; //phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
		}

		$wrap_tag = '<ul %1$s>%2$s</ul>';
		if ( $attributes['dropdown'] ) {
			$wrap_tag = '<div %1$s>%2$s</div>';
		}

		$wrap_attributes = $this->get_block_wrapper_attributes( $attributes );

		if ( empty( $switcher_output ) ) {
			$render_language_switcher = '';
		} else {
			$render_language_switcher = sprintf( $wrap_tag, $wrap_attributes, $switcher_output );
		}
		return $render_language_switcher;
	}

	/**
	 * Renders the `polylang/navigation-language-switcher` block on server.
	 *
	 * Adds CSS classes specific to the `core/navigation` children on top of the Language Switcher HTML.
	 *
	 * @since 3.1
	 *
	 * @param array $attributes Block attributes, also contains CSS classes.
	 * @return string
	 */
	public function render_block_polylang_inner_language_switcher( $attributes = array() ) {
		$attributes['echo'] = 0;
		if ( $this->is_block_editor ) {
			$attributes['admin_render'] = 1;
			$attributes['admin_current_lang'] = $this->admin_current_lang;
			$attributes['hide_if_empty'] = 0;
			$attributes['hide_if_no_translation'] = 0; // Force not to hide the language for the block preview even if the option is checked.
		}

		$custom_class_names = isset( $attributes['className'] ) ? $attributes['className'] : '';

		if ( ! empty( $custom_class_names ) ) {
			$attributes['classes'] = array( 'wp-block-navigation-link', $custom_class_names );
		} else {
			$attributes['classes'] = array( 'wp-block-navigation-link' );
		}

		$attributes['link_classes'] = array( 'wp-block-navigation-link__content' );

		$switcher = new PLL_Switcher();
		// We want a list to display on the frontend.
		$switcher_output = $switcher->the_languages( $this->links, array_merge( $attributes, array( 'dropdown' => false ) ) );

		$wrap_tag = '<ul class="wp-block-navigation__container">%s</ul>';
		if ( $attributes['dropdown'] && ! $this->is_block_editor ) {
			// Wrap output in HTML similar to what Gutenberg generates from our legacy Language Switcher when theme supports the 'block-nav-menus' option {@see https://github.com/WordPress/gutenberg/blob/f2a2a6885dbeeecda5e7ae00437ff3d72e53c2f3/lib/navigation.php#L180 gutenberg_convert_menu_items_to_blocks()}.
			$classes = array( 'has-child' );

			if ( ! empty( $custom_class_names ) ) {
				$classes[] = $custom_class_names;
			}

			$args = array_merge_recursive(
				$attributes,
				array(
					'classes' => $classes,
					'raw' => true,
				)
			);

			$current_lang = array_filter(
				$switcher->the_languages( $this->links, $args ),
				function( $element ) {
					return true === $element['current_lang'];
				}
			);
			$current_lang = array_pop( $current_lang );
			// $args['raw'] will try to display our flag url {@see PLL_Switcher::get_elements()}.
			$current_lang['flag'] = $args['show_flags'] ? $this->model->get_language( PLL()->curlang )->get_display_flag() : '';
			// Default args are processed inside {@see PLL_Switcher::the_languages()}.
			$args = wp_parse_args( $args, PLL_Switcher::DEFAULTS );

			$wrapped_content = '';
			$walker = new PLL_Walker_List();
			$walker->start_el( $wrapped_content, (object) $current_lang, 1, $args );

			$wrapped_content = str_replace(
				'</li>',
				'<span class="wp-block-navigation-link__submenu-icon"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none" role="img" aria-hidden="true" focusable="false"><path d="M1.50002 4L6.00002 8L10.5 4" stroke-width="1.5"></path></svg></span><ul class="submenu-container">%s</ul></li>',
				$wrapped_content
			);
			$wrap_tag = sprintf( $wrap_tag, $wrapped_content );
		}

		if ( empty( $switcher_output ) ) {
			$render_language_switcher = '';
		} else {
			$render_language_switcher = sprintf( $wrap_tag, $switcher_output );
		}
		return $render_language_switcher;
	}

	/**
	 * Renders the language switcher with the given attributes.
	 *
	 * @since 3.1
	 *
	 * @param array  $attributes Array of arguments to pass to {@see PLL_Switcher::the_languages()}.
	 * @param string $wrap_tag   Optional HTML elements to wrap the switcher in. Should include the '%s' replacement character at the place the switcher elements are expected.
	 * @return string
	 */
	/**
	 * Registers the `polylang/language-switcher` block.
	 *
	 * @since 2.8
	 *
	 * @return void
	 */
	public function register_block_polylang_language_switcher() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$script_filename = '/dist/language-switcher-blocks' . $suffix . '.js';
		$script_handle = 'pll_blocks';
		wp_register_script(
			$script_handle,
			WPMU_PLUGIN_URL .  $script_filename,
			array(
				'wp-block-editor',
				'wp-blocks',
				'wp-components',
				'wp-element',
				'wp-i18n',
				'wp-server-side-render',
			),
			'1.0.0',
			true
		);

		wp_localize_script( $script_handle, 'pll_block_editor_blocks_settings', PLL_Switcher::get_switcher_options( 'block', 'string' ) );

		$attributes = array(
			'className'   => array(
				'type' => 'string',
			),
		);
		foreach ( PLL_Switcher::get_switcher_options( 'block', 'default' ) as $option => $default ) {
			$attributes[ $option ] = array(
				'type'    => 'boolean',
				'default' => $default,
			);
		};

		register_block_type(
			'polylang/language-switcher',
			array(
				'editor_script' => $script_handle,
				'attributes' => $attributes,
				'render_callback' => array( $this, 'render_block_polylang_language_switcher' ),
			)
		);

		register_block_type(
			'polylang/navigation-language-switcher',
			array(
				'editor_script' => $script_handle,
				'attributes' => $attributes,
				'render_callback' => array( $this, 'render_block_polylang_inner_language_switcher' ),
			)
		);

		// Translated strings used in JS code
		wp_set_script_translations( $script_handle, 'polylang-pro' );
	}

	/**
	 * Get REST parameters for language switcher block
	 *
	 * @see WP_REST_Server::dispatch()
	 *
	 * @since 2.8
	 *
	 * @param mixed           $result  Response to replace the requested version with. Can be anything
	 *                                 a normal endpoint can return, or null to not hijack the request.
	 * @param WP_REST_Server  $server  Server instance.
	 * @param WP_REST_Request $request Request used to generate the response.
	 * @return mixed
	 */
	public function get_rest_query_params( $result, $server, $request ) {
		if ( ! empty( $request->get_param( 'is_block_editor' ) ) ) {
			$this->is_block_editor = $request->get_param( 'is_block_editor' );
			$this->admin_current_lang = $request->get_param( 'lang' );
		}
		return $result;
	}

	/**
	 * Unoffers the language switcher from the legacy widget block.
	 *
	 * @since 3.1
	 *
	 * @param string[] $widgets An array of excluded widget-type IDs.
	 * @return string[]
	 */
	public function hide_legacy_widget( $widgets ) {
		return array_merge( $widgets, array( 'polylang' ) );
	}

	/**
	 * Add custom class names if exist with a backward compatibility for WP<5.6.
	 * Also add "wp-block-polylang-language-switcher" class, like WP do since v5.6.
	 *
	 * @since 3.2
	 *
	 * @param array $attributes The block attributes containg user custom class names.
	 * @return string formated class names
	 */
	private function get_block_wrapper_attributes( $attributes ) {
		if ( function_exists( 'get_block_wrapper_attributes' ) ) {
			return get_block_wrapper_attributes();
		}
		return isset( $attributes['className'] ) ? 'class="' . $attributes['className'] . ' wp-block-polylang-language-switcher"' : 'class="wp-block-polylang-language-switcher"';
	}

	/**
	 * Register switcher menu item meta options as a REST API field.
	 *
	 * @since 3.2
	 */
	public function register_switcher_menu_item_options_meta_rest_field() {
		$return = register_meta(
			'post',
			'_pll_menu_item',
			array(
				'object_subtype' => 'nav_menu_item',
				'description' => __( 'Language switcher menu item options.', 'polylang-pro' ),
				'single' => true,
				'show_in_rest' => array(
					'schema' => array(
						'type' => 'object',
						'additionalProperties' => array(
							'type' => 'integer',
						),
					)
				),
			)
		);
	}
}
