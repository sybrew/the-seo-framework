<?php
/**
 * @package The_SEO_Framework\Classes\Interpreters\Settings_Input
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
 * Meant for the SEO Settings page, only.
 *
 * @since 4.2.0
 *
 * @access protected
 *         Everything in this class is subject to change or deletion.
 * @internal
 * @final Can't be extended.
 */
final class Settings_Input {

	/**
	 * Helper function that constructs id attributes for use in form fields.
	 *
	 * One-liner I forwent:
	 * return THE_SEO_FRAMEWORK_SITE_OPTIONS . '['. implode( '][', $id ) . ']';
	 *
	 * @since 4.2.0
	 *
	 * @param string|string[] $id The field id, or a map of indexes therefor.
	 * @return string Full field id
	 */
	public static function get_field_id( $id ) {

		$field_id = THE_SEO_FRAMEWORK_SITE_OPTIONS;

		foreach ( (array) $id as $subid )
			$field_id .= "[$subid]";

		return $field_id;
	}

	/**
	 * Echo constructed id attributes in form fields.
	 *
	 * @since 4.2.0
	 * @uses static::get_field_id() Constructs id attributes for use in form fields.
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
	 * @uses static::get_field_name() Construct name attributes for use in form fields.
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
	 * @param array $args : {
	 *    string|map $id          The option index or map of indexes therefor, used as field ID.
	 *    string     $class       The checkbox class.
	 *    string     $label       The checkbox label description, placed inline of the checkbox.
	 *    null|mixed $value       The option value. If not set, it'll try to retrieve the value based on $id.
	 *    string     $description The checkbox additional description, placed underneat.
	 *    array      $data        The checkbox field data. Sub-items are expected to be escaped if they're not an array.
	 *    bool       $escape      Whether to enable escaping of the $label and $description.
	 *    bool       $disabled    Whether to disable the checkbox field.
	 * }
	 * @return string HTML checkbox output.
	 */
	public static function make_checkbox( $args = [] ) {

		$args = array_merge(
			[
				'id'          => '',
				'class'       => '',
				'label'       => '',
				'value'       => null,
				'description' => '',
				'data'        => [],
				'escape'      => true,
				'disabled'    => false,
			],
			$args
		);

		if ( $args['escape'] ) {
			$args['description'] = \esc_html( $args['description'] );
			$args['label']       = \esc_html( $args['label'] );
		}

		$tsf = \tsf();

		$field_id = $field_name = static::get_field_id( $args['id'] );
		$value    = $args['value'] ?? $tsf->get_option( $args['id'] );

		$cb_classes = [];

		if ( $args['class'] )
			$cb_classes[] = $args['class'];

		if ( $args['disabled'] ) {
			$cb_classes[] = 'tsf-disabled';
		} else {
			array_push( $cb_classes, ...static::get_conditional_checked_classes( $args['id'] ) );
		}

		$output = sprintf(
			'<span class="tsf-toblock">%s</span>',
			vsprintf(
				'<label for="%s"%s>%s</label>',
				[
					$tsf->s_field_id( $field_id ),
					( $args['disabled'] ? ' class="tsf-disabled"' : '' ),
					vsprintf(
						'<input type=checkbox class="%s" name="%s" id="%s" value="1" %s%s %s /> %s',
						[
							\esc_attr( implode( ' ', array_filter( $cb_classes ) ) ),
							$tsf->s_field_id( $field_name ),
							$tsf->s_field_id( $field_id ),
							\checked( $value, true, false ),
							( $args['disabled'] ? ' disabled' : '' ),
							HTML::make_data_attributes( $args['data'] ),
							$args['label'],
						]
					),
				]
			)
		);

		return $output .= (
			$args['description']
				? sprintf( '<p class="description tsf-option-spacer">%s</p>', $args['description'] )
				: ''
		);
	}

	/**
	 * Returns the HTML class wrap for warning/default Checkbox options.
	 *
	 * @since 4.2.0
	 *
	 * @param string|string[] $key Required. The option name, or a map of indexes therefor.
	 * @return string[] The conditional checked classes.
	 */
	public static function get_conditional_checked_classes( $key ) {
		$tsf = \tsf();
		return [
			$tsf->get_default_option( $key ) ? 'tsf-default-selected' : '',
			$tsf->get_warned_option( $key ) ? 'tsf-warning-selected' : '',
		];
	}
}
