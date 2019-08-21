<?php
/**
 * @package The_SEO_Framework\Classes\Interpreters\SeoBar
 * @subpackage The_SEO_Framework\SeoBar
 */

namespace The_SEO_Framework\Interpreters;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Interprets the SEO Bar into an HTML item.
 *
 * @since 4.0.0
 * TODO @see \the_seo_framework()->get_new_seo_bar( $args ) for easy access. (name tbd)
 *
 * @access public
 *         Note that you can't instance this class. Only static methods and properties are accessible.
 */
final class SeoBar {
	use \The_SEO_Framework\Traits\Enclose_Stray_Private;

	const STATE_UNKNOWN = 0b0001;
	const STATE_BAD     = 0b0010;
	const STATE_OKAY    = 0b0100;
	const STATE_GOOD    = 0b1000;

	/**
	 * @since 4.0.0
	 * @var mixed $query The current SEO Bar's query items.
	 */
	public static $query = [];

	/**
	 * @since 4.0.0
	 * @var \The_SEO_Framework\Interpreters\SeoBar $instance The instance.
	 */
	private static $instance;

	/**
	 * @since 4.0.0
	 * @var array $item The current SEO Bar item list : {
	 *
	 * }
	 */
	private static $items = [];

	/**
	 * Constructor.
	 *
	 * @since 4.0.0
	 */
	private function __construct() {
		static::$instance = &$this;
	}

	/**
	 * Returns this instance.
	 *
	 * @since 4.0.0
	 *
	 * @return static
	 */
	private static function get_instance() {
		static::$instance instanceof static or new static;
		return static::$instance;
	}

	/**
	 * @since 4.0.0
	 *
	 * @param array $query : {
	 *   int    $id        : Required. The current post or term ID.
	 *   string $taxonomy  : Optional. If not set, this will interpret it as a post.
	 *   string $post_type : Optional. If not set, this will be automatically filled.
	 *                                 This parameter is ignored for taxonomies.
	 * }
	 * @return string The SEO Bar.
	 */
	public static function generate_bar( array $query ) {

		static::$query = array_merge(
			[
				'id'        => 0,
				'taxonomy'  => '',
				'post_type' => '',
			],
			$query
		);

		if ( ! static::$query['id'] ) return '';

		if ( ! static::$query['taxonomy'] )
			static::$query['post_type'] = static::$query['post_type'] ?: \get_post_type( static::$query['id'] );

		$instance = static::get_instance();
		$instance->store_default_bar_items();

		/**
		 * Add or adjust SEO Bar items here.
		 *
		 * @link Example: https://gist.github.com/sybrew/59130560fcbeb98f7580dc11c54ba174
		 * @since 4.0.0
		 * @param string $interpreter The interpreter class name.
		 */
		\do_action( 'the_seo_framework_seo_bar', static::class );

		$bar = $instance->create_seo_bar( static::$items );

		// There's no need to leak memory.
		$instance->clear_seo_bar_items();

		return $bar;
	}

	/**
	 * Passes the SEO Bar item collection by reference.
	 *
	 * @since 4.0.0
	 * @collector
	 *
	 * @return array SEO Bar items. Passed by reference.
	 */
	public function &collect_seo_bar_items() {
		return static::$items;
	}

	/**
	 * Registers or overwrites an SEO Bar item.
	 *
	 * @since 4.0.0
	 *
	 * @param string $key The item key.
	 * @param array  $item : {
	 *    string $symbol : Required. The displayed symbol that identifies your bar.
	 *    string $title  : Required. The title of the assessment.
	 *    string $status : Required. Accepts 'good', 'okay', 'bad', 'unknown'.
	 *    string $reason : Required. The final assessment: The reason for the $status.
	 *    string $assess : Required. The assessments on why the reason is set. Keep it short and concise!
	 *                               Does not accept HTML for performant ARIA support.
	 * }
	 */
	public static function register_seo_bar_item( $key, array $item ) {
		static::$items[ $key ] = $item;
	}

	/**
	 * Passes an SEO Bar item by reference.
	 *
	 * @since 4.0.0
	 * @collector
	 * @staticvar $_void The void. If an item doesn't exist, it's put in here,
	 *                   only to be obliterated, annihilated, extirpated, eradicated, etc.
	 *                   Also, you may be able to spawn an Ender Dragon if you pass four End Crystals.
	 *
	 * @param string $key The item key.
	 * @return array Single SEO Bar item. Passed by reference.
	 */
	public static function &edit_seo_bar_item( $key ) {

		static $_void = [];

		if ( isset( static::$items[ $key ] ) ) :
			$_item = &static::$items[ $key ];
		else :
			$_void = [];
			$_item = &$_void;
		endif;

		return $_item;
	}

	/**
	 * Clears the SEO Bar items.
	 *
	 * @since 4.0.0
	 */
	private function clear_seo_bar_items() {
		static::$items = [];
	}

	/**
	 * Stores the SEO Bar items.
	 *
	 * @since 4.0.0
	 * @factory
	 */
	private function store_default_bar_items() {

		if ( static::$query['taxonomy'] ) {
			$builder = \The_SEO_Framework\Builders\SeoBar_Term::get_instance();
		} else {
			$builder = \The_SEO_Framework\Builders\SeoBar_Page::get_instance();
		}

		/**
		 * Adjust interpreter and builder items here.
		 *
		 * The only use we can think of here is removing items from `$builder::$tests`,
		 * and reading `$interpreter::$query`. Do not add tests here. Do not alter the query.
		 *
		 * @link Example: https://gist.github.com/sybrew/03dd428deadc860309879e1d5208e1c4
		 * @since 4.0.0
		 * @param string                             $interpreter The current class name.
		 * @param \The_SEO_Framework\Builders\SeoBar $builder     The builder object.
		 */
		\do_action_ref_array( 'the_seo_framework_prepare_seo_bar', [ static::class, $builder ] );

		$items = &$this->collect_seo_bar_items();

		foreach ( $builder->_run_test( $builder::$tests, static::$query ) as $key => $data )
			$items[ $key ] = $data;
	}

	/**
	 * Converts registered items to a full HTML SEO Bar.
	 *
	 * @since 4.0.0
	 *
	 * @param array $items The SEO Bar items.
	 * @return string The SEO Bar
	 */
	private function create_seo_bar( array $items ) {

		$blocks = [];

		foreach ( $this->generate_seo_bar_blocks( $items ) as $block )
			$blocks[] = $block;

		// Always return the wrap, may it be filled in via JS in the future.
		return sprintf(
			'<span class="tsf-seo-bar clearfix"><span class="tsf-seo-bar-inner-wrap">%s</span></span>',
			implode( $blocks )
		);
	}

	/**
	 * Generates SEO Bar single HTML block content.
	 *
	 * @since 4.0.0
	 * @generator
	 *
	 * @param array $items The SEO Bar items.
	 * @yield The SEO Bar HTML item.
	 */
	private function generate_seo_bar_blocks( array $items ) {
		foreach ( $items as $item )
			yield vsprintf(
				'<span class="tsf-seo-bar-section-wrap tsf-tooltip-wrap"><span class="tsf-seo-bar-item tsf-tooltip-item tsf-seo-bar-%1$s" title="%2$s" aria-label="%2$s" data-desc="%3$s" tabindex=0>%4$s</span></span>',
				[
					$this->interpret_status_to_class_suffix( $item ),
					\esc_attr( $this->build_item_description( $item, 'aria' ) ),
					\esc_attr( $this->build_item_description( $item, 'html' ) ),
					$this->interpret_status_to_symbol( $item ),
				]
			);
	}

	/**
	 * Builds the SEO Bar item description, in either HTML or plaintext.
	 *
	 * @since 4.0.0
	 * @staticvar array $gettext Cached gettext calls.
	 *
	 * @param array  $item See `$this->register_seo_bar_item()`
	 * @param string $type The description type. Accepts 'html' or 'aria'.
	 * @return string The SEO Bar item description.
	 */
	private function build_item_description( array $item, $type ) {

		static $gettext = null;
		if ( null === $gettext ) {
			$gettext = [
				/* translators: 1 = SEO Bar type title, 2 = Status reason. 3 = Assessments */
				'aria' => \__( '%1$s: %2$s %3$s', 'autodescription' ),
			];
		}

		if ( 'aria' === $type ) {
			$assess = $this->enumerate_assessment_list( $item );

			return sprintf(
				$gettext['aria'],
				$item['title'],
				$item['reason'],
				$this->enumerate_assessment_list( $item )
			);
		} else {
			$assess = '<ol>';
			foreach ( $item['assess'] as $_a ) {
				$assess .= sprintf( '<li>%s</li>', $_a );
			}
			$assess .= '</ol>';

			return sprintf(
				'<strong>%s:</strong> %s<br>%s',
				$item['title'],
				$item['reason'],
				$assess
			);
		}
	}

	/**
	 * Enumerates the assessments in a plaintext format.
	 *
	 * @since 4.0.0
	 * @staticvar array $gettext Cached gettext calls.
	 *
	 * @param array $item See `$this->register_seo_bar_item()`
	 * @return string The SEO Bar item assessment, in plaintext.
	 */
	private function enumerate_assessment_list( array $item ) {

		$count       = count( $item['assess'] );
		$assessments = [];

		static $gettext = null;

		if ( null === $gettext ) {
			$gettext = [
				/* translators: 1 = Assessment number (mind the %d (D)), 2 = Assessment explanation */
				'enum'        => \__( '%1$d: %2$s', 'autodescription' ),
				/* translators: 1 = 'Assessment(s)', 2 = A list of assessments. */
				'list'        => \__( '%1$s: %2$s', 'autodescription' ),
				'assessment'  => \__( 'Assessment', 'autodescription' ),
				'assessments' => \__( 'Assessments', 'autodescription' ),
			];
		}

		if ( $count < 2 ) {
			$assessments[] = reset( $item['assess'] );
		} else {
			$i = 0;
			foreach ( $item['assess'] as $key => $text ) {
				$assessments[] = sprintf( $gettext['enum'], ++$i, $text );
			}
		}

		return sprintf(
			$gettext['list'],
			$count < 2 ? $gettext['assessment'] : $gettext['assessments'],
			implode( ' ', $assessments )
		);
	}

	/**
	 * Interprets binary status to a SEO Bar HTML class suffix.
	 *
	 * TODO instead of going over them in a switch, allow adding the binary data?
	 *      This would meant hat we use the & logical operator, instead.
	 *
	 * @since 4.0.0
	 *
	 * @param array $item See `$this->register_seo_bar_item()`
	 * @return string The HTML class-suffix.
	 */
	private function interpret_status_to_class_suffix( $item ) {

		switch ( $item['status'] ) :
			case static::STATE_GOOD:
				$status = 'good';
				break;

			case static::STATE_OKAY:
				$status = 'okay';
				break;

			case static::STATE_BAD:
				$status = 'bad';
				break;

			default:
			case static::STATE_UNKNOWN:
				$status = 'unknown';
				break;
		endswitch;

		return $status;
	}

	/**
	 * Enumerates the assessments in a plaintext format.
	 *
	 * @since 4.0.0
	 * @staticvar bool $use_symbols
	 *
	 * @param array $item See `$this->register_seo_bar_item()`
	 * @return string The SEO Bar item assessment, in plaintext.
	 */
	private function interpret_status_to_symbol( array $item ) {

		static $use_symbols = null;

		if ( null === $use_symbols )
			$use_symbols = (bool) \the_seo_framework()->get_option( 'seo_bar_symbols' );

		if ( $use_symbols && $item['status'] ^ static::STATE_GOOD ) {
			switch ( $item['status'] ) :
				case static::STATE_OKAY:
					$symbol = '!?';
					break;

				case static::STATE_BAD:
					$symbol = '!!';
					break;

				default:
				case static::STATE_UNKNOWN:
					$symbol = '??';
					break;
			endswitch;

			return $symbol;
		}

		return \esc_html( $item['symbol'] );
	}
}
