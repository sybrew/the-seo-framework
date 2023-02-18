<?php
/**
 * @package The_SEO_Framework\Classes\Builders\Robots\Factory
 * @subpackage The_SEO_Framework\Getter\Robots
 */

namespace The_SEO_Framework\Builders\Robots;

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Factory engine for robots generator.
 *
 * @since 4.2.0
 * @access private
 *         Not part of the public API.
 */
class Factory {

	/**
	 * @since 4.2.0
	 * @param int The starter. A unique ID sent to start the generator switcher.
	 */
	public const START = 0b01000011011110010110001001110010;

	/**
	 * @since 4.2.0
	 * @param int The halter. A unique ID sent to stop the generator switcher.
	 */
	public const HALT = 0b01010111011010010111001001100101;

	/**
	 * @since 4.2.0
	 * @var \The_SEO_Framework\Load The SEO Framework class.
	 */
	protected static $tsf;

	/**
	 * @since 4.2.0
	 * @var array|null Null to autodetermine query, otherwise the query arguments. : {
	 *    int    $id       The Post, Page or Term ID to generate robots for.
	 *    string $taxonomy The taxonomy.
	 * }
	 */
	protected static $args;

	/**
	 * @since 4.2.0
	 * @var int Modifies return values/assertions. See const ROBOTS_* at /bootstrap/define.php
	 */
	protected static $options;

	/**
	 * Contructor, does nothing but instigate TSF.
	 *
	 * @since 4.2.0
	 */
	public function __construct() {
		static::$tsf = static::$tsf ?? \tsf();
	}

	/**
	 * Sets parameters.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @param null|array $args    The robots meta arguments, leave null to autodetermine query : {
	 *    int    $id       The Post, Page or Term ID to generate the URL for.
	 *    string $taxonomy The taxonomy.
	 * }
	 * @param int        $options Modifies return values/assertions. See const ROBOTS_* at /bootstrap/define.php
	 * @return Factory $this
	 */
	public function set( $args = null, $options = 0 ) {
		static::$args    = $args;
		static::$options = $options;
		return $this;
	}

	/**
	 * Generates robots assertions.
	 *
	 * @since 4.2.0
	 * @access private
	 * @generator
	 */
	public static function generator() {
		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition.Found -- Shhh. It's OK.
		while ( true ) switch ( $sender = yield static::START ) :
			case 'noindex':
			case 'nofollow':
			case 'noarchive':
				foreach ( static::assert_no( $sender ) as $key => $value ) {
					yield $key => $value;
					if ( $value ) {
						yield static::HALT;
						break;
					}
				}
				break;

			case 'max_snippet':
			case 'max_image_preview':
			case 'max_video_preview':
				yield from static::assert_copyright( $sender );
				yield static::HALT;
				break;

			default:
				static::$tsf->_doing_it_wrong(
					__METHOD__,
					sprintf( 'Unregistered robots-generator getter provided: <code>%s</code>.', \esc_html( $sender ) ),
					'4.2.0'
				);
				yield static::HALT;
				break;
		endswitch;
	}

	/**
	 * Generates robots assertions for copyright options.
	 *
	 * @since 4.2.0
	 * @access private
	 * @generator
	 *
	 * @param string $type The robots generator type (noindex, nofollow...).
	 */
	final protected static function assert_copyright( $type ) {

		// Remit FETCH_STATIC_PROP_R opcode calls every time we'd otherwise use static::$tsf hereinafter.
		$tsf = static::$tsf;

		$option = $type;

		if ( 'max_snippet' === $type )
			$option = 'max_snippet_length';

		$tsf->get_option( 'set_copyright_directives' )
			and yield 'globals_copyright' => $tsf->get_option( $option );
	}
}
