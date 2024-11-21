<?php
/**
 * @package The_SEO_Framework\Admin\Settings\Layout\Form
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework\Admin\Settings\Layout;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data\Filter\Escape,
	Helper\Format\Arrays,
	Helper\Query,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

/**
 * Interprets anything you send here into Form HTML. Or so it should.
 *
 * @since 4.1.4
 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Interpreters`.
 *              2. The methods herein are now considered public.
 * @NOTE This and the methods herein will likely be deprecated in the future.
 *
 * @access protected
 *         Use tsf()->admin()->layout()->form() instead.
 */
class Form {

	/**
	 * Returns a HTML select form elements for qubit options: -1, 0, or 1.
	 * Does not support "multiple" field selections.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 'default' is now synonymous to 'selected'. 'default' is no longer promoted.
	 *
	 * @param array $args {
	 *     The select field creation arguments.
	 *
	 *     @type string     $id       The select field ID.
	 *     @type string     $class    The div wrapper class.
	 *     @type string     $name     The option name.
	 *     @type int|string $selected The selected option value.
	 *     @type array      $options  The select option values : { value => name }
	 *     @type string     $label    The option label.
	 *     @type bool       $labelstrong Whether the label should be strong.
	 *     @type bool       $required Whether the field must be required.
	 *     @type array      $data     The select field data. Sub-items are expected to be escaped if they're not an array.
	 *     @type array      $info     Extra info field data.
	 * }
	 * @return string The option field.
	 */
	public static function make_single_select_form( $args ) {

		$args += [
			'id'          => '',
			'class'       => '',
			'name'        => '',
			'selected'    => $args['default'] ?? '',
			'options'     => [],
			'label'       => '',
			'labelstrong' => false,
			'required'    => false,
			'data'        => [],
			'info'        => [],
		];

		// The walk below would mangle the option index. Assign it to a new var to prevent confusion later.
		$html_options = $args['options'];

		array_walk(
			$html_options,
			/**
			 * @param string $name     The option name. Passed by reference, returned as the HTML option item.
			 * @param mixed  $value    The option value.
			 * @param mixed  $selected The current selected value.
			 */
			function ( &$name, $value, $selected ) {
				$name = \sprintf(
					'<option value="%s"%s>%s</option>',
					\esc_attr( $value ),
					(string) $value === (string) $selected ? ' selected' : '',
					\esc_html( $name )
				);
			},
			$args['selected'],
		);

		return vsprintf(
			\sprintf(
				'<div class="%s">%s</div>',
				\esc_attr( $args['class'] ),
				\is_rtl() ? '%2$s%1$s%3$s' : '%1$s%2$s%3$s',
			),
			[
				$args['label'] ? \sprintf(
					'<label for="%s">%s</label> ', // superfluous space!
					Escape::option_name_attribute( $args['id'] ),
					\sprintf(
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
						Escape::option_name_attribute( $args['id'] ),
						\esc_attr( $args['name'] ),
						$args['required'] ? ' required' : '',
						HTML::make_data_attributes( $args['data'] ),
						implode( $html_options ),
					],
				),
			],
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
				\sprintf(
					/* translators: %s = number */
					\esc_html__( 'Characters: %s', 'autodescription' ),
					\sprintf(
						'<span id="%s">0</span>',
						\esc_attr( "{$for}_chars" ),
					),
				),
			],
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
				\sprintf(
					'<div id="%s" class=tsf-tooltip-wrap>%s</div>',
					\esc_attr( "{$for}_pixels" ),
					'<span class="tsf-pixel-counter-bar tsf-tooltip-item" aria-label data-desc tabindex=0><span class=tsf-pixel-counter-fluid></span></span>',
				),
				\sprintf(
					'<div class=tsf-pixel-shadow-wrap><span class="tsf-pixel-counter-shadow %s"></span></div>',
					\esc_attr( "tsf-{$type}-pixel-counter-shadow" )
				),
			],
		);
	}

	/**
	 * Returns image uploader form button.
	 * Also registers additional i18n strings for JS, and registers a tooltip for image preview.
	 *
	 * The default arguments conform to Facebook and Twitter image sharing.
	 *
	 * @since 4.1.4
	 * @since 4.2.8 Added 'button_class' as a supported index for `$args`.
	 * @since 5.1.0 Now also outputs a warning icon placeholder.
	 *
	 * @param array $args {
	 *     The image uploader arguments.
	 *
	 *     @type string $id                Required. The HTML input id to pass URL into.
	 *     @type int    $post_id           Optional. The Post ID to bind the uploaded file to. Default current post ID.
	 *     @type array  $data              {
	 *         Optional. The data attributes for the image uploader.
	 *
	 *         @type string $inputType      Optional. Whether the upload type is 'social' or 'logo' for i18n. Default 'social'.
	 *         @type int    $width          Optional. The suggested image width. Default 1200.
	 *         @type int    $height         Optional. The suggested image height. Default 630.
	 *         @type int    $minWidth       Optional. The minimum image width. Default 200.
	 *         @type int    $minHeight      Optional. The minimum image height. Default 200.
	 *         @type bool   $flex           Optional. Whether the image W:H ratio may be changed. Default true.
	 *     },
	 *     @type array  $i18n              {
	 *         Optional. The internationalization strings.
	 *
	 *         @type string $button_title   Optional. The image-select button on-hover title for accessibility. Default ''.
	 *                                   Tip: Only fill if 'button_text' is ambiguous.
	 *         @type string $button_text    Optional. The image-select button title. Defaults l10n 'Select Image'.
	 *     },
	 *     @type array  $button_class      {
	 *         Optional. The button classes.
	 *
	 *         @type array $set             Optional. The image set button classes.
	 *         @type array $remove          Optional. The image removal button classes.
	 *     },
	 * }
	 * @return string The image uploader button.
	 */
	public static function get_image_uploader_form( $args ) {

		// Required.
		if ( empty( $args['id'] ) ) return '';

		$args = Arrays::array_merge_recursive_distinct(
			[
				'id'           => '',
				'post_id'      => Query::get_the_real_id(), // This will bind the uploade file to the current post.
				'data'         => [
					'inputType' => 'social',
					'width'     => 1200, // TODO make 1280 - 80px overflow margin? It'd be better for mixed platforms.
					'height'    => 630,  // TODO make  640 - 80px overflow margin? It'd be better for mixed platforms.
					'minWidth'  => 200,
					'minHeight' => 200,
					'flex'      => true,
				],
				'i18n'         => [
					'button_title' => '', // Redundant.
					'button_text'  => \__( 'Select Image', 'autodescription' ),
				],
				'button_class' => [
					'set'    => [
						'button',
						'button-primary',
						'button-small',
					],
					'remove' => [
						'button',
						'button-small',
					],
				],
			],
			$args,
		);

		$s_id = \esc_attr( $args['id'] );

		$content = vsprintf(
			'<button type=button data-href="%s" class="tsf-set-image-button %s" title="%s" id="%s-select" %s>%s</button>',
			[
				\esc_url( \get_upload_iframe_src( 'image', $args['post_id'] ) ),
				\esc_attr( implode( ' ', (array) $args['button_class']['set'] ) ),
				\esc_attr( $args['i18n']['button_title'] ),
				$s_id,
				HTML::make_data_attributes(
					[ 'inputId' => $args['id'] ]
					+ $args['data']
					+ [ 'buttonClass' => $args['button_class'] ],
				),
				\esc_html( $args['i18n']['button_text'] ),
			],
		);

		$content .= <<<HTML
			<span class=tsf-image-notifications data-for="{$s_id}"><span class=tsf-tooltip-wrap><span id="{$s_id}-preview" class="tsf-image-preview tsf-tooltip-item hidden" tabindex=0></span></span><span class=tsf-tooltip-wrap><span id="{$s_id}-image-warning" class="tsf-image-warning tsf-tooltip-item hidden" tabindex=0></span></span></span>
		HTML;

		return $content;
	}
}
