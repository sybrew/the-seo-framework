<?php
/**
 * @package The_SEO_Framework\Classes\Interpreters\SEOBar
 * @subpackage The_SEO_Framework\SEOBar
 */

namespace The_SEO_Framework\Interpreters;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Interprets the SEO Bar into an HTML item.
 *
 * @since 4.0.0
 * @see \tsf()->get_generated_seo_bar( $args ) for easy access.
 *
 * @access public
 *         Note that you can't instance this class. Only static methods and properties are accessible.
 * @final Can't be extended.
 */
final class SEOBar {

	/**
	 * The recognized SEO Bar item states.
	 * Mixed types will fall back to 'undefined'.
	 *
	 * @since 4.1.0
	 * @access public
	 * @var int <bit    0> STATE_UNDEFINED
	 * @var int <bit    1> STATE_UNKNOWN
	 * @var int <bit   10> STATE_BAD
	 * @var int <bit  100> STATE_OKAY
	 * @var int <bit 1000> STATE_GOOD
	 */
	public const STATE_UNDEFINED = 0b0000;
	public const STATE_UNKNOWN   = 0b0001;
	public const STATE_BAD       = 0b0010;
	public const STATE_OKAY      = 0b0100;
	public const STATE_GOOD      = 0b1000;

	/**
	 * @since 4.0.0
	 * @var mixed $query The current SEO Bar's query items.
	 */
	public static $query = [];

	/**
	 * @since 4.0.0
	 * @var \The_SEO_Framework\Interpreters\SEOBar $instance The instance.
	 */
	private static $instance;

	/**
	 * @since 4.0.0
	 * @var array $item The current SEO Bar item list. {
	 *    string $symbol : The displayed symbol that identifies your bar.
	 *    string $title  : The title of the assessment.
	 *    string $status : Accepts 'good', 'okay', 'bad', 'unknown'.
	 *    string $reason : The final assessment: The reason for the $status.
	 *    string $assess : The assessments on why the reason is set. Keep it short and concise!
	 *                     Does not accept HTML for performant ARIA support.
	 * }
	 */
	private static $items = [];

	/**
	 * Returns this instance.
	 *
	 * @since 4.0.0
	 *
	 * @return static
	 */
	private static function get_instance() {
		return static::$instance ??= new static;
	}

	/**
	 * Generates the SEO Bar.
	 *
	 * @since 4.0.0
	 * @since 4.1.4 Now manages the builder, too.
	 *
	 * @param array $query : {
	 *   int    $id        : Required. The current post or term ID.
	 *   string $taxonomy  : Optional. If not set, this will interpret it as a post.
	 *   string $pta       : Not implemented. Do not populate.
	 *   string $post_type : Optional. If not set, this will be automatically filled.
	 *                                 This parameter is ignored for taxonomies.
	 *                                 This parameter will become obsolete once WP fixes its post cache.
	 *                                 <https://core.trac.wordpress.org/ticket/50567>
	 * }
	 * @return string The SEO Bar.
	 */
	public static function generate_bar( $query ) {

		static::$query = $query + [
			'id'        => 0,
			'taxonomy'  => '',
			'pta'       => '',
			'post_type' => '',
		];

		if ( ! static::$query['id'] ) return '';

		if ( ! static::$query['taxonomy'] )
			static::$query['post_type'] = static::$query['post_type'] ?: \get_post_type( static::$query['id'] );

		$builder = static::$query['taxonomy']
			? \The_SEO_Framework\Builders\SEOBar\Term::get_instance()
			: \The_SEO_Framework\Builders\SEOBar\Page::get_instance();

		$instance = static::get_instance();
		$instance->store_seo_bar_items( $builder );

		/**
		 * Add or adjust SEO Bar items here, after the tests have run.
		 *
		 * @link Example: https://gist.github.com/sybrew/59130560fcbeb98f7580dc11c54ba174
		 * @since 4.0.0
		 * @param string $interpreter The interpreter class name.
		 */
		\do_action( 'the_seo_framework_seo_bar', static::class );

		$bar = $instance->create_seo_bar( static::$items );

		// There's no need to leak memory.
		$instance->clear_seo_bar_items();
		$builder->clear_query_cache();

		return $bar;
	}

	/**
	 * Passes the SEO Bar item collection by reference.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 Is now static.
	 * @collector
	 *
	 * @return array SEO Bar items. Passed by reference. {
	 *    string $symbol : The displayed symbol that identifies your bar.
	 *    string $title  : The title of the assessment.
	 *    string $status : Accepts 'good', 'okay', 'bad', 'unknown'.
	 *    string $reason : The final assessment: The reason for the $status.
	 *    string $assess : The assessments on why the reason is set. Keep it short and concise!
	 *                     Does not accept HTML for performant ARIA support.
	 * }
	 */
	public static function &collect_seo_bar_items() {
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
	public static function register_seo_bar_item( $key, $item ) {
		static::$items[ $key ] = $item;
	}

	/**
	 * Passes an SEO Bar item by reference.
	 *
	 * @since 4.0.0
	 * @collector
	 *
	 * @param string $key The item key.
	 * @return array Single SEO Bar item. Passed by reference.
	 */
	public static function &edit_seo_bar_item( $key ) {

		/**
		 * The void. If an item key doesn't exist, all values are put in here,
		 * only to be obliterated, annihilated, extirpated, eradicated, etc., when called later.
		 * Also, you may be able to spawn an Ender Dragon if you pass four End Crystals.
		 */
		static $_void = [];

		if ( isset( static::$items[ $key ] ) ) { // Do not write to referenced var before this is tested!
			$_item = &static::$items[ $key ];
		} else {
			$_void = [];
			$_item = &$_void;
		}

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
	 * @since 4.1.4 Offloaded the builder's instantiation.
	 * @factory
	 *
	 * @param \The_SEO_Framework\Builders\SEOBar\Main $builder The builder instance.
	 */
	private function store_seo_bar_items( $builder ) {

		/**
		 * Adjust interpreter and builder items here, before the tests have run.
		 *
		 * The only use we can think of here is removing items from `$builder::$tests`,
		 * and reading `$builder::$query{_cache}`. Do not add tests here. Do not alter the query.
		 *
		 * @link Example: https://gist.github.com/sybrew/03dd428deadc860309879e1d5208e1c4
		 * @see related (recommended) action 'the_seo_framework_seo_bar'
		 * @since 4.0.0
		 * @param string                                  $interpreter The current class name.
		 * @param \The_SEO_Framework\Builders\SEOBar\Main $builder     The builder object.
		 */
		\do_action_ref_array( 'the_seo_framework_prepare_seo_bar', [ static::class, $builder ] );

		$items = &$this->collect_seo_bar_items();

		foreach ( $builder->_run_all_tests( static::$query ) as $key => $data )
			$items[ $key ] = $data;
	}

	/**
	 * Converts registered items to a full HTML SEO Bar.
	 *
	 * @since 4.0.0
	 * @since 4.2.8 1. Now returns a div wrap instead of a span, so we can bypass lack of display-inside browser support.
	 *              2. Added tsf-tooltip-super-wrap said div wrap.
	 *
	 * @param iterable $items The SEO Bar items.
	 * @return string The SEO Bar
	 */
	private function create_seo_bar( $items ) {

		$blocks = [];

		foreach ( $this->generate_seo_bar_blocks( $items ) as $block )
			$blocks[] = $block;

		// Always return the wrap, may it be filled in via JS in the future.
		return sprintf(
			'<div class="tsf-seo-bar tsf-tooltip-super-wrap"><span class=tsf-seo-bar-inner-wrap>%s</span></div>',
			implode( $blocks )
		);
	}

	/**
	 * Generates SEO Bar single HTML block content.
	 *
	 * @since 4.0.0
	 * @generator
	 * FIXME? The data herein is obtained via `builders/seobar-{type}.php`. If they escape their cache before we do here, it'd be much quicker.
	 *        Provided, however, that there are fewer items cached (130~137) than SEOBar blocks outputted (240 on most sites).
	 *        Moreover, all data provided comes from trusted sources. Nevertheless, we should escape as late as possible.
	 *        WordPress still hangs on tight to their PHP5.2 roots, where HTML4+ escaping wasn't supported well. Updating that requires
	 *        a whole lot of time, and paves way for potential security issues due to oversight. But, that'd speed up escaping for everyone.
	 *
	 * @param iterable $items The SEO Bar items.
	 * @yield The SEO Bar HTML item.
	 */
	private function generate_seo_bar_blocks( $items ) {
		foreach ( $items as $item )
			yield vsprintf(
				'<span class="tsf-seo-bar-section-wrap tsf-tooltip-wrap"><span class="tsf-seo-bar-item tsf-tooltip-item tsf-seo-bar-%1$s" title="%2$s" aria-label="%2$s" data-desc="%3$s" tabindex=0>%4$s</span></span>',
				[
					$this->interpret_status_to_class_suffix( $item ),
					\esc_attr( $this->build_item_description( $item, 'aria' ) ),
					\esc_attr( $this->build_item_description( $item, 'html' ) ),
					\esc_html( $this->interpret_status_to_symbol( $item ) ),
				]
			);
	}

	/**
	 * Builds the SEO Bar item description, in either HTML or plaintext.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Removed accidental duplicated call to enumerate_assessment_list()
	 *
	 * @param array  $item See `$this->register_seo_bar_item()`
	 * @param string $type The description type. Accepts 'html' or 'aria'.
	 * @return string The SEO Bar item description.
	 */
	private function build_item_description( $item, $type ) {

		static $gettext = null;
		if ( null === $gettext ) {
			$gettext = [
				/* translators: 1 = SEO Bar type title, 2 = Status reason. 3 = Assessments */
				'aria' => \_x( '%1$s: %2$s %3$s', 'SEO Bar ARIA assessment enumeration', 'autodescription' ),
			];
		}

		if ( 'aria' === $type ) {
			return sprintf(
				$gettext['aria'],
				$item['title'],
				$item['reason'],
				$this->enumerate_assessment_list( $item )
			);
		} else {
			$assess = '<ol>';
			foreach ( $item['assess'] as $_a ) {
				$assess .= "<li>$_a</li>";
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
	 *
	 * @param array $item See `$this->register_seo_bar_item()`
	 * @return string The SEO Bar item assessment, in plaintext.
	 */
	private function enumerate_assessment_list( $item ) {

		$count       = \count( $item['assess'] );
		$assessments = [];

		static $gettext = null;

		if ( null === $gettext ) {
			$gettext = [
				/* translators: 1 = Assessment number (mind the %d (D)), 2 = Assessment explanation */
				'enum'        => \_x( '%1$d: %2$s', 'assessment enumeration', 'autodescription' ),
				/* translators: 1 = 'Assessment(s)', 2 = A list of assessments. */
				'list'        => \_x( '%1$s: %2$s', 'assessment list', 'autodescription' ),
				'assessment'  => \__( 'Assessment', 'autodescription' ),
				'assessments' => \__( 'Assessments', 'autodescription' ),
			];
		}

		if ( $count < 2 ) {
			$assessments[] = reset( $item['assess'] );
		} else {
			$i = 0;
			foreach ( $item['assess'] as $text ) {
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
	 * @since 4.1.0 1. Added 'undefined' support.
	 *              2. Now defaults to 'undefined'.
	 *
	 * @param array $item See `$this->register_seo_bar_item()`
	 * @return string The HTML class-suffix.
	 */
	private function interpret_status_to_class_suffix( $item ) {

		switch ( $item['status'] ) {
			case static::STATE_GOOD:
				$status = 'good';
				break;

			case static::STATE_OKAY:
				$status = 'okay';
				break;

			case static::STATE_BAD:
				$status = 'bad';
				break;

			case static::STATE_UNKNOWN:
				$status = 'unknown';
				break;

			case static::STATE_UNDEFINED:
			default:
				$status = 'undefined';
		}

		return $status;
	}

	/**
	 * Enumerates the assessments in a plaintext format.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 1. Added 'undefined' support.
	 *              2. Now defaults to 'undefined'.
	 *
	 * @param array $item See `$this->register_seo_bar_item()`
	 * @return string The SEO Bar item assessment, in plaintext.
	 */
	private function interpret_status_to_symbol( $item ) {

		static $use_symbols;

		$use_symbols ??= (bool) \tsf()->get_option( 'seo_bar_symbols' );

		if ( $use_symbols && $item['status'] ^ static::STATE_GOOD ) {
			switch ( $item['status'] ) {
				case static::STATE_OKAY:
					$symbol = '!?';
					break;

				case static::STATE_BAD:
					$symbol = '!!';
					break;

				case static::STATE_UNKNOWN:
					$symbol = '??';
					break;

				case static::STATE_UNDEFINED:
				default:
					$symbol = '--';
			}

			return $symbol;
		}

		return $item['symbol'];
	}
}
