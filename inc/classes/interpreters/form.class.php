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
 * Interprets anything you send here into Form HTML. Or so it should.
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
	 * Returns a HTML select form elements for qubit options: -1, 0, or 1.
	 * Does not support "multiple" field selections.
	 *
	 * @since 4.1.4
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
	public static function make_single_select_form( $args ) {

		$defaults = [
			'id'          => '',
			'class'       => '',
			'name'        => '',
			'default'     => '',
			'options'     => [],
			'label'       => '',
			'labelstrong' => false,
			'required'    => false,
			'data'        => [],
			'info'        => [],
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

		$tsf = \tsf();

		return vsprintf(
			sprintf( '<div class="%s">%s</div>',
				\esc_attr( $args['class'] ),
				( \is_rtl() ? '%2$s%1$s%3$s' : '%1$s%2$s%3$s' )
			),
			[
				$args['label'] ? sprintf(
					'<label for="%s">%s</label> ', // superfluous space!
					$tsf->s_field_id( $args['id'] ),
					sprintf(
						$args['labelstrong'] ? '<strong>%s</strong>' : '%s',
						\esc_html( $args['label'] )
					)
				) : '',
				$args['info'] ? HTML::make_info(
					$args['info'][0],
					$args['info'][1] ?? '',
					false
				) . ' ' : '',
				vsprintf(
					'<select id="%s" name="%s"%s %s>%s</select>',
					[
						$tsf->s_field_id( $args['id'] ),
						\esc_attr( $args['name'] ),
						$args['required'] ? ' required' : '',
						HTML::make_data_attributes( $args['data'] ),
						implode( $html_options ),
					]
				),
			]
		);
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
	 * @param bool   $display Whether to display the counter. (Used as options page gimmick)
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
	public static function get_image_uploader_form( $args ) {

		// Required.
		if ( empty( $args['id'] ) ) return '';

		$tsf = \tsf();

		$args = $tsf->array_merge_recursive_distinct(
			[
				'id'      => '',
				'post_id' => $tsf->get_the_real_ID(), // TODO why? Introduced <https://github.com/sybrew/the-seo-framework/commit/6ca4425abf3edafd75d7d47e60e54eb8bca91cc2>
				'data'    => [
					'inputType' => 'social',
					'width'     => 1200, // TODO make 1280 - 80px overflow margin? It'd be better for mixed platforms.
					'height'    => 630,  // TODO make  640 - 80px overflow margin? It'd be better for mixed platforms.
					'minWidth'  => 200,
					'minHeight' => 200,
					'flex'      => true,
				],
				'i18n'    => [
					'button_title' => '', // Redundant.
					'button_text'  => \__( 'Select Image', 'autodescription' ),
				],
			],
			$args
		);

		$content = vsprintf(
			'<button type=button data-href="%s" class="tsf-set-image-button button button-primary button-small" title="%s" id="%s-select" %s>%s</button>',
			[
				\esc_url( \get_upload_iframe_src( 'image', $args['post_id'] ) ),
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
