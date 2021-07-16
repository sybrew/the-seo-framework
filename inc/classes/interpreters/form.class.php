<?php
/**
 * @package The_SEO_Framework\Classes\Interpreters\Form
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework\Interpreters;

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Interprets anything you send here into HTML. Or so it should.
 *
 * @since 4.1.4
 *
 * @access protected
 *         Everything in this class is subject to change or deletion.
 * @internal
 * @final Can't be extended.
 */
final class Form {

	/**
	 * Helper function that constructs header elements.
	 *
	 * @since 4.1.4
	 *
	 * @param string $title The header title.
	 */
	public static function header_title( $title ) {
		printf( '<h4>%s</h4>', \esc_html( $title ) );
	}

	/**
	 * Helper function that constructs name attributes for use in form fields.
	 *
	 * Other page implementation classes may wish to construct and use a
	 * get_field_id() method, if the naming format needs to be different.
	 *
	 * @since 4.1.4
	 *
	 * @param string $name Field name base
	 * @return string Full field name
	 */
	public static function get_field_name( $name ) {
		return sprintf( '%s[%s]', THE_SEO_FRAMEWORK_SITE_OPTIONS, $name );
	}

	/**
	 * Echo constructed name attributes in form fields.
	 *
	 * @since 4.1.4
	 * @uses static::get_field_name() Construct name attributes for use in form fields.
	 *
	 * @param string $name Field name base
	 */
	public static function field_name( $name ) {
		echo \esc_attr( static::get_field_name( $name ) );
	}

	/**
	 * Helper function that constructs id attributes for use in form fields.
	 *
	 * @since 4.1.4
	 *
	 * @param string $id Field id base
	 * @return string Full field id
	 */
	public static function get_field_id( $id ) {
		return sprintf( '%s[%s]', THE_SEO_FRAMEWORK_SITE_OPTIONS, $id );
	}

	/**
	 * Echo constructed id attributes in form fields.
	 *
	 * @since 4.1.4
	 * @uses static::get_field_id() Constructs id attributes for use in form fields.
	 *
	 * @param string  $id Field id base.
	 * @param boolean $echo Whether to escape echo or just return.
	 * @return string Full field id
	 */
	public static function field_id( $id, $echo = true ) {
		if ( $echo ) {
			echo \esc_attr( static::get_field_id( $id ) );
		} else {
			return static::get_field_id( $id );
		}
	}

	/**
	 * Returns a chechbox wrapper.
	 *
	 * @since 4.1.4
	 *
	 * @param array $args : {
	 *    string $id          The option name, used as field ID.
	 *    string $class       The checkbox class.
	 *    string $index       The option index, used when the option is an array.
	 *    string $label       The checkbox label description, placed inline of the checkbox.
	 *    string $description The checkbox additional description, placed underneat.
	 *    array  $data        The checkbox field data. Sub-items are expected to be escaped if they're not an array.
	 *    bool   $escape      Whether to enable escaping of the $label and $description.
	 *    bool   $disabled    Whether to disable the checkbox field.
	 *    bool   $default     Whether to display-as-default. This is autodetermined when no $index is set.
	 *    bool   $warned      Whether to warn the checkbox field value.
	 * }
	 * @return string HTML checkbox output.
	 */
	public static function make_checkbox( array $args = [] ) {

		$args = array_merge(
			[
				'id'          => '',
				'class'       => '',
				'index'       => '',
				'label'       => '',
				'description' => '',
				'data'        => [],
				'escape'      => true,
				'disabled'    => false,
				'default'     => false,
				'warned'      => false,
			],
			$args
		);

		if ( $args['escape'] ) {
			$args['description'] = \esc_html( $args['description'] );
			$args['label']       = \esc_html( $args['label'] );
		}

		$tsf = \the_seo_framework();

		$index = $args['index'] ? $tsf->s_field_id( $args['index'] ?: '' ) : '';

		$field_id = $field_name = \esc_attr( sprintf(
			'%s%s',
			Form::get_field_id( $args['id'] ),
			$index ? sprintf( '[%s]', $index ) : ''
		) );

		$value = $tsf->get_option( $args['id'] );
		if ( $index ) {
			$value = isset( $value[ $index ] ) ? $value[ $index ] : '';
		}

		$cb_classes = [];

		if ( $args['class'] ) {
			$cb_classes[] = $args['class'];
		}

		if ( $args['disabled'] ) {
			$cb_classes[] = 'tsf-disabled';
		} elseif ( ! $args['index'] ) {
			// Can't fetch conditionals in index.
			$cb_classes[] = static::get_is_conditional_checked( $args['id'], false );
		} else {
			if ( $args['default'] ) {
				$cb_classes[] = 'tsf-default-selected';
			} elseif ( $args['warned'] ) {
				$cb_classes[] = 'tsf-warning-selected';
			}
		}

		$output = sprintf(
			'<span class="tsf-toblock">%s</span>',
			vsprintf(
				'<label for="%s" %s>%s</label>',
				[
					$field_id,
					( $args['disabled'] ? 'class="tsf-disabled"' : '' ),
					vsprintf(
						'<input type=checkbox class="%s" name="%s" id="%s" value="1" %s %s %s /> %s',
						[
							\esc_attr( implode( ' ', $cb_classes ) ),
							$field_name,
							$field_id,
							\checked( $value, true, false ),
							( $args['disabled'] ? 'disabled' : '' ),
							$args['data'] ? HTML::make_data_attributes( $args['data'] ) : '',
							$args['label'],
						]
					),
				]
			)
		);

		$output .= $args['description'] ? sprintf( '<p class="description tsf-option-spacer">%s</p>', $args['description'] ) : '';

		return $output;
	}

	/**
	 * Returns a HTML select form elements for qubit options: -1, 0, or 1.
	 * Does not support "multiple" field selections.
	 *
	 * @since 4.1.4
	 * @TODO allow arrays as index, so we can support multidimensional options easily? @see is_conditional_checked
	 *
	 * @param array $args : {
	 *    string     $id       The select field ID.
	 *    string     $class    The div wrapper class.
	 *    string     $name     The option name.
	 *    int|string $default  The current option value.
	 *    array      $options  The select option values : { value => name }
	 *    string     $label    The option label.
	 *    string     $required Whether the field must be required.
	 *    array      $data     The select field data. Sub-items are expected to be escaped if they're not an array.
	 *    array      $info     Extra info field data.
	 * }
	 * @return string The option field.
	 */
	public static function make_single_select_form( array $args ) {

		$defaults = [
			'id'       => '',
			'class'    => '',
			'name'     => '',
			'default'  => '',
			'options'  => [],
			'label'    => '',
			'required' => false,
			'data'     => [],
			'info'     => [],
		];

		$args = array_merge( $defaults, $args );

		// The walk below destroys the option array. Assign it to a new var to prevent confusion later.
		$html_options = $args['options'];
		/**
		 * @param string $name    The option name. Passed by reference, returned as the HTML option item.
		 * @param mixed  $value
		 * @param mixed  $default
		 */
		$create_option = static function( &$name, $value, $default ) {
			$name = sprintf(
				'<option value="%s"%s>%s</option>',
				\esc_attr( $value ),
				(string) $value === (string) $default ? ' selected' : '',
				\esc_html( $name )
			);
		};
		array_walk( $html_options, $create_option, $args['default'] );

		$tsf = \the_seo_framework();

		return vsprintf(
			sprintf( '<div class="%s">%s</div>',
				\esc_attr( $args['class'] ),
				( \is_rtl() ? '%2$s%1$s%3$s' : '%1$s%2$s%3$s' )
			),
			[
				$args['label'] ? sprintf(
					'<label for=%s>%s</label> ', // NOTE: extra space!
					$tsf->s_field_id( $args['id'] ),
					\esc_html( $args['label'] )
				) : '',
				$args['info'] ? ' ' . HTML::make_info(
					$args['info'][0],
					isset( $args['info'][1] ) ? $args['info'][1] : '',
					false
				) : '',
				vsprintf(
					'<select id=%s name=%s %s %s>%s</select>',
					[
						$tsf->s_field_id( $args['id'] ),
						\esc_attr( $args['name'] ),
						$args['required'] ? 'required' : '',
						$args['data'] ? HTML::make_data_attributes( $args['data'] ) : '',
						implode( $html_options ),
					]
				),
			]
		);
	}

	/**
	 * Returns the HTML class wrap for default Checkbox options.
	 *
	 * This function does nothing special. But is merely a simple wrapper.
	 * Just like code_wrap.
	 *
	 * @since 4.1.4
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public static function is_default_checked( $key, $wrap = true, $echo = true ) {

		$class = '';

		$default = \the_seo_framework()->get_default_settings( $key );

		if ( 1 === $default )
			$class = 'tsf-default-selected';

		if ( $echo ) {
			if ( $wrap ) {
				printf( 'class="%s"', \esc_attr( $class ) );
			} else {
				echo \esc_attr( $class );
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}
	}

	/**
	 * Returns the HTML class wrap for warning Checkbox options.
	 *
	 * @since 4.1.4
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public static function is_warning_checked( $key, $wrap = true, $echo = true ) {

		$class = '';

		$warned = \the_seo_framework()->get_warned_settings( $key );

		if ( 1 === $warned )
			$class = 'tsf-warning-selected';

		if ( $echo ) {
			if ( $wrap ) {
				printf( 'class="%s"', \esc_attr( $class ) );
			} else {
				echo \esc_attr( $class );
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}
	}

	/**
	 * Returns the HTML class wrap for warning/default Checkbox options.
	 *
	 * @since 4.1.4
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 */
	public static function get_is_conditional_checked( $key, $wrap = true ) {
		return static::is_conditional_checked( $key, '', $wrap, false );
	}

	/**
	 * Returns the HTML class wrap for warning/default Checkbox options.
	 *
	 * @since 4.1.4
	 *
	 * @param string $key        The option name which returns boolean.
	 * @param bool   $wrap       Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo       Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public static function is_conditional_checked( $key, $wrap = true, $echo = true ) {

		$class = '';

		$default = static::is_default_checked( $key, false, false );
		$warned  = static::is_warning_checked( $key, false, false );

		if ( '' !== $default && '' !== $warned ) {
			$class = $default . ' ' . $warned;
		} elseif ( '' !== $default ) {
			$class = $default;
		} elseif ( '' !== $warned ) {
			$class = $warned;
		}

		if ( $echo ) {
			if ( $wrap ) {
				printf( 'class="%s"', \esc_attr( $class ) );
			} else {
				echo \esc_attr( $class );
			}
		} else {
			if ( $wrap )
				return sprintf( 'class="%s"', $class );

			return $class;
		}
	}

	/**
	 * Outputs character counter wrap for both JavaScript and no-Javascript.
	 *
	 * @since 4.1.4
	 *
	 * @param string $for     The input ID it's for.
	 * @param bool   $display Whether to display the counter. (options page gimmick)
	 */
	public static function output_character_counter_wrap( $for, $display = true ) {
		vprintf(
			'<div class="tsf-counter-wrap hide-if-no-tsf-js" %s><span class=tsf-counter title="%s">%s</span><span class=tsf-ajax></span></div>',
			[
				( $display ? '' : 'style=display:none;' ),
				\esc_attr__( 'Click to change the counter type', 'autodescription' ),
				sprintf(
					/* translators: %s = number */
					\esc_html__( 'Characters: %s', 'autodescription' ),
					sprintf(
						'<span id="%s">%s</span>',
						\esc_attr( "{$for}_chars" ),
						0
					)
				),
			]
		);
	}

	/**
	 * Outputs pixel counter wrap for javascript.
	 *
	 * @since 4.1.4
	 *
	 * @param string $for  The input ID it's for.
	 * @param string $type Whether it's a 'title' or 'description' counter.
	 * @param bool   $display Whether to display the counter. (options page gimmick)
	 */
	public static function output_pixel_counter_wrap( $for, $type, $display = true ) {
		vprintf(
			'<div class="tsf-pixel-counter-wrap hide-if-no-tsf-js" %s>%s%s</div>',
			[
				( $display ? '' : 'style="display:none;"' ),
				sprintf(
					'<div id="%s" class="tsf-tooltip-wrap">%s</div>',
					\esc_attr( "{$for}_pixels" ),
					'<span class="tsf-pixel-counter-bar tsf-tooltip-item" aria-label="" data-desc="" tabindex=0><span class="tsf-pixel-counter-fluid"></span></span>'
				),
				sprintf(
					'<div class="tsf-pixel-shadow-wrap"><span class="tsf-pixel-counter-shadow %s"></span></div>',
					\esc_attr( "tsf-{$type}-pixel-counter-shadow" )
				),
			]
		);
	}

	/**
	 * Returns image uploader form button.
	 * Also registers additional i18n strings for JS, and registers a tooltip for image preview.
	 *
	 * The default arguments conform to Facebook and Twitter image sharing.
	 *
	 * @since 4.1.4
	 *
	 * @param array $args Required. The image uploader arguments : {
	 *   'id'      => string Required. The HTML input id to pass URL into.
	 *   'post_id' => int    Optional. The Post ID to bind the uploaded file to. Default current post ID.
	 *   'data'    => [
	 *      'inputType' => string Optional. Whether the upload type is 'social' or 'logo' for i18n. Default 'social'.
	 *      'width'     => int    Optional. The suggested image width. Default 1200.
	 *      'height'    => int    Optional. The suggested image height. Default 630.
	 *      'minWidth'  => int    Optional. The minimum image width. Default 200.
	 *      'minHeight' => int    Optional. The minimum image height. Default 200.
	 *      'flex'      => bool   Optional. Whether the image W:H ratio may be changed. Default true.
	 *   ],
	 *   'i18n'    => [
	 *      'button_title' => string Optional. The image-select button on-hover title for accessibility. Default ''.
	 *                                         Tip: Only fill if 'button_text' is ambiguous.
	 *      'button_text'  => string Optional. The image-select button title. Defaults l10n 'Select Image',
	 *   ],
	 * }
	 * @return string The image uploader button.
	 */
	public static function get_image_uploader_form( array $args ) {

		static $image_input_id = 0;
		$image_input_id++;

		$tsf = \the_seo_framework();

		$defaults = [
			'id'      => null,
			'post_id' => $tsf->get_the_real_ID(),
			'data'    => [
				'inputType' => 'social',
				'width'     => 1200, // TODO make 1280 - 80px overflow margin? It'd be better for mixed platforms.
				'height'    => 630,  // TODO make  640 - 80px overflow margin? It'd be better for mixed platforms.
				'minWidth'  => 200,
				'minHeight' => 200,
				'flex'      => true,
			],
			'i18n'    => [
				'button_title' => '',
				'button_text'  => \__( 'Select Image', 'autodescription' ),
			],
		];

		$args = $tsf->array_merge_recursive_distinct( $defaults, $args );

		if ( ! $args['id'] ) return '';

		$content = vsprintf(
			'<button type=button data-href="%s" class="tsf-set-image-button button button-primary button-small" title="%s" id="%s-select" %s>%s</button>',
			[
				\esc_url( \get_upload_iframe_src( 'image', $defaults['post_id'] ) ),
				\esc_attr( $args['i18n']['button_title'] ),
				\esc_attr( $args['id'] ),
				HTML::make_data_attributes(
					[ 'inputId' => $args['id'] ]
					+ $args['data']
				),
				\esc_html( $args['i18n']['button_text'] ),
			]
		);

		$content .= sprintf(
			'<span class="tsf-tooltip-wrap"><span id="%1$s-preview" class="tsf-image-preview tsf-tooltip-item dashicons dashicons-format-image" data-for="%1$s" tabindex=0></span></span>',
			\esc_attr( $args['id'] )
		);

		return $content;
	}
}
