<?php
/**
 * @package The_SEO_Framework\Admin\Settings\Layout\Input
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework\Admin\Settings\Layout;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Data\Filter\Escape,
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
 * Meant for the SEO Settings, Post Edit, and Term Edit.
 * The `*field_*()` functions are meant for the SEO Settings only.
 *
 * @since 4.2.0
 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Interpreters`.
 *              2. Renamed from `Settings_Input`.
 *
 * @access private
 *         Everything in this class is subject to change or deletion.
 */
class Input {

	/**
	 * Helper function that constructs id attributes for use in form fields.
	 *
	 * @since 4.2.0
	 *
	 * @param string|string[] $id The field id, or a map of indexes therefor.
	 * @return string Full field id
	 */
	public static function get_field_id( $id ) {

		$field_id = \THE_SEO_FRAMEWORK_SITE_OPTIONS;

		foreach ( (array) $id as $subid )
			$field_id .= "[$subid]";

		return $field_id;
	}

	/**
	 * Echo constructed id attributes in form fields.
	 *
	 * @since 4.2.0
	 *
	 * @param string|string[] $id The field id, or a map of indexes therefor.
	 */
	public static function field_id( $id ) {
		echo \esc_attr( static::get_field_id( $id ) );
	}

	/**
	 * Helper function that constructs name attributes for use in form fields.
	 *
	 * Alias of field_id.
	 *
	 * @since 4.2.0
	 * @ignore
	 *
	 * @param string|string[] $name The field name, or a map of indexes therefor.
	 * @return string Full field name
	 */
	public static function get_field_name( $name ) {
		return static::get_field_id( $name );
	}

	/**
	 * Echo constructed name attributes in form fields.
	 *
	 * Alias of field_id.
	 *
	 * @since 4.2.0
	 * @ignore
	 *
	 * @param string|string[] $name The field name, or a map of indexes therefor.
	 */
	public static function field_name( $name ) {
		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- field_id escapes.
		echo static::field_id( $name );
	}

	/**
	 * Returns a chechbox wrapper.
	 *
	 * This is put in this class, instead of Form, because
	 * 1) Retrieves the global settings ID from input.
	 * 2) Retrieves the default-state of the checkbox via the global settings.
	 *
	 * @since 4.2.0
	 *
	 * @param array $args {
	 *     The checkbox creation arguments.
	 *
	 *     @type string|string[] $id          The option index or map of indexes therefor, used as field ID.
	 *     @type string          $class       The checkbox class.
	 *     @type string          $label       The checkbox label description, placed inline of the checkbox.
	 *     @type null|mixed      $value       The option value. If not set, it'll try to retrieve the value based on $id.
	 *     @type string          $description The checkbox additional description, placed underneat.
	 *     @type array           $data        The checkbox field data. Sub-items are expected to be escaped if they're not an array.
	 *     @type bool            $escape      Whether to enable escaping of the $label and $description.
	 *     @type bool            $disabled    Whether to disable the checkbox field.
	 * }
	 * @return string HTML checkbox output.
	 */
	public static function make_checkbox( $args = [] ) {

		$args += [
			'id'          => '',
			'class'       => '',
			'label'       => '',
			'value'       => null,
			'description' => '',
			'data'        => [],
			'escape'      => true,
			'disabled'    => false,
		];

		if ( $args['escape'] ) {
			$args['description'] = \esc_html( $args['description'] );
			$args['label']       = \esc_html( $args['label'] );
		}

		$field_id = $field_name = static::get_field_id( $args['id'] );
		$value    = $args['value'] ?? Data\Plugin::get_option( ...(array) $args['id'] );

		$cb_classes = [];

		if ( $args['class'] )
			$cb_classes[] = $args['class'];

		if ( $args['disabled'] ) {
			$cb_classes[] = 'tsf-disabled';
		} else {
			array_push( $cb_classes, ...static::get_conditional_checked_classes( ...(array) $args['id'] ) );
		}

		return \sprintf(
			'<span class=tsf-toblock>%s</span>%s',
			vsprintf(
				'<label for="%s"%s>%s</label>',
				[
					Escape::option_name_attribute( $field_id ),
					( $args['disabled'] ? ' class=tsf-disabled' : '' ),
					vsprintf(
						'<input type=checkbox class="%s" name="%s" id="%s" value=1 %s%s %s /> %s',
						[
							\esc_attr( implode( ' ', array_filter( $cb_classes ) ) ),
							Escape::option_name_attribute( $field_name ),
							Escape::option_name_attribute( $field_id ),
							\checked( $value, true, false ),
							( $args['disabled'] ? ' disabled' : '' ),
							HTML::make_data_attributes( $args['data'] ),
							$args['label'],
						],
					),
				],
			),
			$args['description']
				? \sprintf( '<p class="description tsf-option-spacer">%s</p>', $args['description'] )
				: '',
		);
	}

	/**
	 * Returns the HTML class wrap for warning/default Checkbox options.
	 *
	 * @since 4.2.0
	 *
	 * @param string ...$key Required. The option name, or a list of indexes therefor.
	 * @return string[] The conditional checked classes.
	 */
	public static function get_conditional_checked_classes( ...$key ) {
		return [
			Data\Plugin\Setup::get_default_option( ...$key ) ? 'tsf-default-selected' : '',
			Data\Plugin\Setup::get_warned_option( ...$key ) ? 'tsf-warning-selected' : '',
		];
	}

	/**
	 * Outputs reference description HTML elements for JavaScript for a specific ID.
	 *
	 * @since 4.1.0
	 * @since 4.1.2 Now prevents wp-emoji.js parsing the references and data.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string $id The input ID.
	 * @param array  $data The input data.
	 */
	public static function output_js_title_data( $id, $data ) {
		vprintf(
			implode(
				'',
				[
					'<span id="tsf-title-reference_%1$s" class="tsf-title-reference hidden wp-exclude-emoji" data-for="%1$s"></span>',
					'<span id="tsf-title-noadditions-reference_%1$s" class="tsf-title-noadditions-reference hidden wp-exclude-emoji" data-for="%1$s"></span>',
					'<span class=tsf-title-offset-wrap><span id="tsf-title-offset_%1$s" class="tsf-title-offset wp-exclude-emoji hide-if-no-tsf-js" data-for="%1$s"></span></span>',
					'<span id="tsf-title-placeholder-additions_%1$s" class="tsf-title-placeholder-additions wp-exclude-emoji hide-if-no-tsf-js" data-for="%1$s"></span>',
					'<span id="tsf-title-placeholder-prefix_%1$s" class="tsf-title-placeholder-prefix wp-exclude-emoji hide-if-no-tsf-js" data-for="%1$s"></span>',
					'<span id="tsf-title-data_%1$s" class="hidden wp-exclude-emoji" data-for="%1$s" %2$s></span>',
				],
			),
			[
				\esc_attr( $id ),
				// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
				HTML::make_data_attributes( $data ),
			],
		);
	}

	/**
	 * Outputs reference social HTML elements for JavaScript for a specific ID.
	 *
	 * @since 4.2.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string       $group    The social input group ID.
	 * @param array[og,tw] $settings The input settings data.
	 */
	public static function output_js_social_data( $group, $settings ) {
		vprintf(
			'<span id="tsf-social-data_%1$s" class="hidden wp-exclude-emoji" data-group="%1$s" %2$s></span>',
			[
				\esc_attr( $group ),
				// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
				HTML::make_data_attributes( [ 'settings' => $settings ] ),
			],
		);
	}

	/**
	 * Outputs reference description HTML elements for JavaScript for a specific ID.
	 *
	 * @since 4.1.0
	 * @since 4.1.2 Now prevents wp-emoji.js parsing the references and data.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @param string $id   The description input ID.
	 * @param array  $data The input data.
	 */
	public static function output_js_description_data( $id, $data ) {
		vprintf(
			implode(
				'',
				[
					'<span id="tsf-description-reference_%1$s" class="hidden wp-exclude-emoji" data-for="%1$s" ></span>',
					'<span id="tsf-description-data_%1$s" class="hidden wp-exclude-emoji" data-for="%1$s" %2$s ></span>',
				],
			),
			[
				\esc_attr( $id ),
				// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
				HTML::make_data_attributes( $data ),
			],
		);
	}

	/**
	 * Outputs reference canonical HTML elements for JavaScript for a specific ID.
	 *
	 * @since 5.1.0
	 *
	 * @param string $id   The canonical URL input ID.
	 * @param array  $data The input data.
	 */
	public static function output_js_canonical_data( $id, $data ) {
		vprintf(
			'<span id="tsf-canonical-data_%1$s" class="hidden wp-exclude-emoji" data-for="%1$s" %2$s ></span>',
			[
				\esc_attr( $id ),
				// phpcs:ignore, WordPress.Security.EscapeOutput -- make_data_attributes escapes.
				HTML::make_data_attributes( $data ),
			],
		);
	}
}
