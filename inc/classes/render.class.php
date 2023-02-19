<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Render
 * @subpackage The_SEO_Framework\Front
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Render
 *
 * Puts all data into HTML valid meta tags.
 *
 * @since 2.8.0
 */
class Render extends Admin_Init {

	/**
	 * Returns the document title.
	 *
	 * This method serves as a callback for filter `pre_get_document_title`.
	 * Use tsf()->get_title() instead.
	 *
	 * @since 3.1.0
	 * @see $this->get_title()
	 *
	 * @param string $title The filterable title.
	 * @return string The document title
	 */
	public function get_document_title( $title = '' ) {

		if ( ! $this->query_supports_seo() )
			return $title;

		/**
		 * @since 3.1.0
		 * @param string $title The generated title.
		 * @param int    $id    The page or term ID.
		 */
		return \apply_filters_ref_array(
			'the_seo_framework_pre_get_document_title',
			[
				$this->get_title(),
				$this->get_the_real_ID(),
			]
		);
	}

	/**
	 * Returns the document title.
	 *
	 * This method serves as a callback for filter `wp_title`.
	 * Use tsf()->get_title() instead.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Removed extraneous, unused parameters.
	 * @see $this->get_title()
	 *
	 * @param string $title The filterable title.
	 * @return string $title
	 */
	public function get_wp_title( $title = '' ) {

		if ( ! $this->query_supports_seo() )
			return $title;

		/**
		 * @since 3.1.0
		 * @param string $title The generated title.
		 * @param int    $id    The page or term ID.
		 */
		return \apply_filters_ref_array(
			'the_seo_framework_wp_title',
			[
				$this->get_title(),
				$this->get_the_real_ID(),
			]
		);
	}

	/**
	 * Caches current Image URL in static variable.
	 * To be used on the front-end only.
	 *
	 * @since 2.2.2
	 * @since 2.7.0 $get_id parameter has been added.
	 * @since 4.0.0 Now uses the new image generator.
	 * @since 4.1.2 Now forwards the `multi_og_image` option to the generator. Although
	 *              it'll always use just one image, we read this option so we'll only
	 *              use a single cache instance internally with the generator.
	 *
	 * @return string The image URL.
	 */
	public function get_image_from_cache() {

		foreach ( $this->get_image_details_from_cache( ! $this->get_option( 'multi_og_image' ) ) as $image ) {
			$url = $image['url'];
			if ( $url ) break;
		}

		return $url ?? '';
	}

	/**
	 * Returns the current Twitter card type.
	 * Memoizes the return value.
	 *
	 * @since 2.8.2
	 * @since 3.1.0 Filter has been moved to generate_twitter_card_type()
	 *
	 * @return string The cached Twitter card.
	 */
	public function get_current_twitter_card_type() {
		return memo() ?? memo( $this->generate_twitter_card_type() );
	}

	/**
	 * Renders an XHTML element. Sane drop-in for DOMDocument and whatnot.
	 *
	 * Even though most (if not all) WordPress sites use HTML5, we expect some still use XHTML.
	 * We expect HTML5 fully on the back-end.
	 *
	 * This method should not be used by you. Eventually, it'll print or spawn demigods.
	 *
	 * @since 4.1.4
	 * @access protected
	 *         Not finished for 'public' use; method may (will) change unannounced.
	 * @internal
	 * @link <https://github.com/sybrew/the-seo-framework/commit/894d7d3a74e0ed6890b6e8851ef0866df15ea522>
	 *       Which is something we eventually want to go to, but that's not ready yet.
	 *
	 * @param array       $attributes Associative array of tag names and tag values : {
	 *    string $name => string $value
	 * }
	 * @param string      $tag      The element's tag-name.
	 * @param bool|string $text     The element's contents, if any.
	 * @param bool        $new_line Whether to add a new line to the end of the element.
	 */
	public function render_element( $attributes = [], $tag = 'meta', $text = false, $new_line = true ) {

		$attr = '';

		foreach ( $attributes as $_name => $_value ) {

			switch ( $_name ) {
				case 'href':
				case 'xlink:href':
				case 'src':
					$_secure_attr_value = \esc_url_raw( $_value );
					break;
				default:
					$_secure_attr_value = \esc_attr( $_value );
					break;
			}

			// phpcs:disable -- Security hint for later, left code intact; Redundant, internal... for now.
			// elseif ( \in_array(
			// 	$_name,
			// 	/** @link <https://www.w3.org/TR/2011/WD-html5-20110525/elements.html> */
			// 	[ 'onabort', 'onblur', 'oncanplay', 'oncanplaythrough', 'onchange', 'onclick', 'oncontextmenu', 'oncuechange', 'ondblclick', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'ondurationchange', 'onemptied', 'onended', 'onerror', 'onfocus', 'oninput', 'oninvalid', 'onkeydown', 'onkeypress', 'onkeyup', 'onload', 'onloadeddata', 'onloadedmetadata', 'onloadstart', 'onmousedown', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onpause', 'onplay', 'onplaying', 'onprogress', 'onratechange', 'onreadystatechange', 'onreset', 'onscroll', 'onseeked', 'onseeking', 'onselect', 'onshow', 'onstalled', 'onsubmit', 'onsuspend', 'ontimeupdate', 'onvolumechange', 'onwaiting' ],
			// 	true
			// ) ) {
			// 	// Nope. Not this function.
			// 	continue;
			// }
			// phpcs:enable

			$attr .= sprintf(
				' %s="%s"',
				/**
				 * @link <https://www.w3.org/TR/2011/WD-html5-20110525/syntax.html#attributes-0>
				 * This will strip "safe" characters outside of the alphabet, 0-9, and :_-.
				 * I don't want angry parents ringing me at home for their site didn't
				 * support proper UTF. We can afford empty tags in rare situations -- not here.
				 */
				preg_replace( '/[^a-zA-Z0-9:_-]+/', '', $_name ),
				$_secure_attr_value
			);
		}

		if ( $text ) {
			$el = vsprintf(
				'<%1$s%2$s>%3$s</%1$s>',
				[
					/** @link <https://www.w3.org/TR/2011/WD-html5-20110525/syntax.html#syntax-tag-name> */
					preg_replace( '/[^0-9a-zA-Z]+/', '', $tag ),
					$attr,
					\esc_html( $text ),
				]
			);
		} else {
			$el = sprintf(
				'<%s%s />',
				/** @link <https://www.w3.org/TR/2011/WD-html5-20110525/syntax.html#syntax-tag-name> */
				preg_replace( '/[^0-9a-zA-Z]+/', '', $tag ),
				$attr
			);
		}

		return $el . ( $new_line ? "\n" : '' );
	}

	/**
	 * Renders the 'tsf:aqp' meta tag. Useful for identifying when query-exploit detection
	 * is triggered.
	 *
	 * @since 4.1.4
	 *
	 * @return string The advanced query protection (aqp) identifier.
	 */
	public function advanced_query_protection() {
		return $this->render_element( [
			'name'  => 'tsf:aqp',
			'value' => '1',
		] );
	}

	/**
	 * Renders the description meta tag.
	 *
	 * @since 1.3.0
	 * @since 3.0.6 No longer uses $this->description_from_cache()
	 * @since 3.1.0 No longer checks for SEO plugin presence.
	 * @uses $this->get_description()
	 *
	 * @return string The description meta tag.
	 */
	public function the_description() {

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $description The generated description.
		 * @param int    $id          The page or term ID.
		 */
		$description = (string) \apply_filters_ref_array(
			'the_seo_framework_description_output',
			[
				$this->get_description(),
				$this->get_the_real_ID(),
			]
		);

		return $description ? $this->render_element( [
			'name'    => 'description',
			'content' => $description,
		] ) : '';
	}

	/**
	 * Renders og:description meta tag
	 *
	 * @since 1.3.0
	 * @since 3.0.4 No longer uses $this->description_from_cache()
	 * @uses $this->get_open_graph_description()
	 *
	 * @return string The Open Graph description meta tag.
	 */
	public function og_description() {

		if ( ! $this->use_og_tags() )
			return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $description The generated Open Graph description.
		 * @param int    $id          The page or term ID.
		 */
		$description = (string) \apply_filters_ref_array(
			'the_seo_framework_ogdescription_output',
			[
				$this->get_open_graph_description(),
				$this->get_the_real_ID(),
			]
		);

		return $description ? $this->render_element( [
			'property' => 'og:description',
			'content'  => $description,
		] ) : '';
	}

	/**
	 * Renders the OG locale meta tag.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Open Graph locale meta tag.
	 */
	public function og_locale() {

		if ( ! $this->use_og_tags() )
			return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $locale The generated locale field.
		 * @param int    $id     The page or term ID.
		 */
		$locale = (string) \apply_filters_ref_array(
			'the_seo_framework_oglocale_output',
			[
				$this->fetch_locale(),
				$this->get_the_real_ID(),
			]
		);

		return $locale ? $this->render_element( [
			'property' => 'og:locale',
			'content'  => $locale,
		] ) : '';
	}

	/**
	 * Renders the Open Graph title meta tag.
	 *
	 * @since 2.0.3
	 * @since 3.0.4 No longer uses $this->title_from_cache()
	 * @uses $this->get_open_graph_title()
	 *
	 * @return string The Open Graph title meta tag.
	 */
	public function og_title() {

		if ( ! $this->use_og_tags() )
			return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $title The generated Open Graph title.
		 * @param int    $id    The page or term ID.
		 */
		$title = (string) \apply_filters_ref_array(
			'the_seo_framework_ogtitle_output',
			[
				$this->get_open_graph_title(),
				$this->get_the_real_ID(),
			]
		);

		return $title ? $this->render_element( [
			'property' => 'og:title',
			'content'  => $title,
		] ) : '';
	}

	/**
	 * Renders the Open Graph type meta tag.
	 *
	 * @since 1.1.0
	 *
	 * @return string The Open Graph type meta tag.
	 */
	public function og_type() {

		if ( ! $this->use_og_tags() )
			return '';

		$type = $this->get_og_type();

		return $type ? $this->render_element( [
			'property' => 'og:type',
			'content'  => $type,
		] ) : '';
	}

	/**
	 * Renders Open Graph image meta tag.
	 *
	 * @since 1.3.0
	 * @since 2.6.0 Added WooCommerce gallery images.
	 * @since 2.7.0 Added image dimensions if found.
	 * @since 4.1.2 Now forwards the `multi_og_image` option to the generator to
	 *              reduce processing power.
	 *
	 * @return string The Open Graph image meta tag.
	 */
	public function og_image() {

		if ( ! $this->use_og_tags() ) return '';

		$output = '';

		$multi = (bool) $this->get_option( 'multi_og_image' );

		foreach ( $this->get_image_details_from_cache( ! $multi ) as $image ) {
			$output .= $this->render_element( [
				'property' => 'og:image',
				'content'  => $image['url'],
			] );

			if ( $image['height'] && $image['width'] ) {
				$output .= $this->render_element( [
					'property' => 'og:image:width',
					'content'  => $image['width'],
				] );
				$output .= $this->render_element( [
					'property' => 'og:image:height',
					'content'  => $image['height'],
				] );
			}

			if ( $image['alt'] ) {
				$output .= $this->render_element( [
					'property' => 'og:image:alt',
					'content'  => $image['alt'],
				] );
			}

			// Redundant?
			if ( ! $multi ) break;
		}

		return $output;
	}

	/**
	 * Renders Open Graph sitename meta tag.
	 *
	 * @since 1.3.0
	 * @since 3.1.0 Now uses $this->get_blogname(), which trims the output.
	 *
	 * @return string The Open Graph sitename meta tag.
	 */
	public function og_sitename() {

		if ( ! $this->use_og_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $sitename The generated Open Graph site name.
		 * @param int    $id       The page or term ID.
		 */
		$sitename = (string) \apply_filters_ref_array(
			'the_seo_framework_ogsitename_output',
			[
				$this->get_blogname(),
				$this->get_the_real_ID(),
			]
		);

		return $sitename ? $this->render_element( [
			'property' => 'og:site_name',
			'content'  => $sitename,
		] ) : '';
	}

	/**
	 * Renders Open Graph URL meta tag.
	 *
	 * @since 1.3.0
	 * @since 2.9.3 Added filter
	 * @since 4.1.4 Now uses `render_element()`, which applies `esc_attr()` on the URL.
	 * @uses $this->get_current_canonical_url()
	 *
	 * @return string The Open Graph URL meta tag.
	 */
	public function og_url() {

		if ( ! $this->use_og_tags() ) return '';

		/**
		 * @since 2.9.3
		 * @param string $url The canonical/Open Graph URL. Must be escaped.
		 * @param int    $id  The current page or term ID.
		 */
		$url = (string) \apply_filters_ref_array(
			'the_seo_framework_ogurl_output',
			[
				$this->get_current_canonical_url(),
				$this->get_the_real_ID(),
			]
		);

		return $url ? $this->render_element( [
			'property' => 'og:url',
			'content'  => $url,
		] ) : '';
	}

	/**
	 * Renders the Twitter Card type meta tag.
	 *
	 * @since 2.2.2
	 *
	 * @return string The Twitter Card meta tag.
	 */
	public function twitter_card() {

		if ( ! $this->use_twitter_tags() ) return '';

		$card = $this->get_current_twitter_card_type();

		return $card ? $this->render_element( [
			'name'    => 'twitter:card',
			'content' => $card,
		] ) : '';
	}

	/**
	 * Renders the Twitter Site meta tag.
	 *
	 * @since 2.2.2
	 *
	 * @return string The Twitter Site meta tag.
	 */
	public function twitter_site() {

		if ( ! $this->use_twitter_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $site The Twitter site owner tag.
		 * @param int    $id   The current page or term ID.
		 */
		$site = (string) \apply_filters_ref_array(
			'the_seo_framework_twittersite_output',
			[
				$this->get_option( 'twitter_site' ),
				$this->get_the_real_ID(),
			]
		);

		return $site ? $this->render_element( [
			'name'    => 'twitter:site',
			'content' => $site,
		] ) : '';
	}

	/**
	 * Renders The Twitter Creator meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.9.3 No longer has a fallback to twitter:site:id
	 *              @link https://dev.twitter.com/cards/getting-started
	 * @since 3.0.0 Now uses author meta data.
	 *
	 * @return string The Twitter Creator or Twitter Site ID meta tag.
	 */
	public function twitter_creator() {

		if ( ! $this->use_twitter_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $creator The Twitter page creator.
		 * @param int    $id      The current page or term ID.
		 */
		$creator = (string) \apply_filters_ref_array(
			'the_seo_framework_twittercreator_output',
			[
				$this->get_current_post_author_meta_item( 'twitter_page' ) ?: $this->get_option( 'twitter_creator' ),
				$this->get_the_real_ID(),
			]
		);

		return $creator ? $this->render_element( [
			'name'    => 'twitter:creator',
			'content' => $creator,
		] ) : '';
	}

	/**
	 * Renders Twitter Title meta tag.
	 *
	 * @since 2.2.2
	 * @since 3.0.4 No longer uses $this->title_from_cache()
	 * @uses $this->get_twitter_title()
	 *
	 * @return string The Twitter Title meta tag.
	 */
	public function twitter_title() {

		if ( ! $this->use_twitter_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $title The generated Twitter title.
		 * @param int    $id    The current page or term ID.
		 */
		$title = (string) \apply_filters_ref_array(
			'the_seo_framework_twittertitle_output',
			[
				$this->get_twitter_title(),
				$this->get_the_real_ID(),
			]
		);

		return $title ? $this->render_element( [
			'name'    => 'twitter:title',
			'content' => $title,
		] ) : '';
	}

	/**
	 * Renders Twitter Description meta tag.
	 *
	 * @since 2.2.2
	 * @since 3.0.4 No longer uses $this->description_from_cache()
	 * @uses $this->get_twitter_description()
	 *
	 * @return string The Twitter Descritpion meta tag.
	 */
	public function twitter_description() {

		if ( ! $this->use_twitter_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $description The generated Twitter description.
		 * @param int    $id          The current page or term ID.
		 */
		$description = (string) \apply_filters_ref_array(
			'the_seo_framework_twitterdescription_output',
			[
				$this->get_twitter_description(),
				$this->get_the_real_ID(),
			]
		);

		return $description ? $this->render_element( [
			'name'    => 'twitter:description',
			'content' => $description,
		] ) : '';
	}

	/**
	 * Renders Twitter Image meta tag.
	 *
	 * @since 2.2.2
	 * @since 4.1.2 Now forwards the `multi_og_image` option to the generator. Although
	 *              it'll always use just one image, we read this option so we'll only
	 *              use a single cache instance internally with the generator.
	 * @since 4.2.8 Removed support for the long deprecated `twitter:image:height` and `twitter:image:width`.
	 *
	 * @return string The Twitter Image meta tag.
	 */
	public function twitter_image() {

		if ( ! $this->use_twitter_tags() ) return '';

		$output = '';

		foreach ( $this->get_image_details_from_cache( ! $this->get_option( 'multi_og_image' ) ) as $image ) {
			$output .= $this->render_element( [
				'name'    => 'twitter:image',
				'content' => $image['url'],
			] );

			if ( $image['alt'] ) {
				$output .= $this->render_element( [
					'name'    => 'twitter:image:alt',
					'content' => $image['alt'],
				] );
			}

			// Only grab a single image. Twitter grabs the final (less favorable) image otherwise.
			break;
		}

		return $output;
	}

	/**
	 * Renders Theme Color meta tag.
	 *
	 * @since 4.0.5
	 *
	 * @return string The Theme Color meta tag.
	 */
	public function theme_color() {

		$theme_color = $this->get_option( 'theme_color' );

		return $theme_color ? $this->render_element( [
			'name'    => 'theme-color',
			'content' => $theme_color,
		] ) : '';
	}

	/**
	 * Renders Facebook Author meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Returns empty on og:type 'website' or 'product'
	 * @since 3.0.0 Fetches Author meta data.
	 *
	 * @return string The Facebook Author meta tag.
	 */
	public function facebook_author() {

		if ( ! $this->use_facebook_tags() ) return '';
		if ( 'article' !== $this->get_og_type() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $facebook_page The generated Facebook author page URL.
		 * @param int    $id            The current page or term ID.
		 */
		$facebook_page = (string) \apply_filters_ref_array(
			'the_seo_framework_facebookauthor_output',
			[
				$this->get_current_post_author_meta_item( 'facebook_page' ) ?: $this->get_option( 'facebook_author' ),
				$this->get_the_real_ID(),
			]
		);

		return $facebook_page ? $this->render_element( [
			'property' => 'article:author',
			'content'  => $facebook_page,
		] ) : '';
	}

	/**
	 * Renders Facebook Publisher meta tag.
	 *
	 * @since 2.2.2
	 * @since 3.0.0 No longer outputs tag when "og:type" isn't 'article'.
	 *
	 * @return string The Facebook Publisher meta tag.
	 */
	public function facebook_publisher() {

		if ( ! $this->use_facebook_tags() ) return '';
		if ( 'article' !== $this->get_og_type() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $publisher The Facebook publisher page URL.
		 * @param int    $id        The current page or term ID.
		 */
		$publisher = (string) \apply_filters_ref_array(
			'the_seo_framework_facebookpublisher_output',
			[
				$this->get_option( 'facebook_publisher' ),
				$this->get_the_real_ID(),
			]
		);

		return $publisher ? $this->render_element( [
			'property' => 'article:publisher',
			'content'  => $publisher,
		] ) : '';
	}

	/**
	 * Renders Facebook App ID meta tag.
	 *
	 * @since 2.2.2
	 *
	 * @return string The Facebook App ID meta tag.
	 */
	public function facebook_app_id() {

		if ( ! $this->use_facebook_tags() ) return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $app_id The Facebook app ID.
		 * @param int    $id     The current page or term ID.
		 */
		$app_id = (string) \apply_filters_ref_array(
			'the_seo_framework_facebookappid_output',
			[
				$this->get_option( 'facebook_appid' ),
				$this->get_the_real_ID(),
			]
		);

		return $app_id ? $this->render_element( [
			'property' => 'fb:app_id',
			'content'  => $app_id,
		] ) : '';
	}

	/**
	 * Renders Article Publishing Time meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Returns empty on product pages.
	 * @since 3.0.0 1. Now checks for 0000 timestamps.
	 *              2. Now uses timestamp formats.
	 *              3. Now uses GMT time.
	 *
	 * @return string The Article Publishing Time meta tag.
	 */
	public function article_published_time() {

		if ( ! $this->output_published_time() ) return '';

		$id            = $this->get_the_real_ID();
		$post_date_gmt = \get_post( $id )->post_date_gmt ?? '0000-00-00 00:00:00';

		if ( '0000-00-00 00:00:00' === $post_date_gmt )
			return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $time The article published time.
		 * @param int    $id   The current page or term ID.
		 */
		$time = (string) \apply_filters_ref_array(
			'the_seo_framework_publishedtime_output',
			[
				$this->gmt2date( $this->get_timestamp_format(), $post_date_gmt ),
				$id,
			]
		);

		return $time ? $this->render_element( [
			'property' => 'article:published_time',
			'content'  => $time,
		] ) : '';
	}

	/**
	 * Renders Article Modified Time meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.7.0 Listens to $this->get_the_real_ID() instead of WordPress Core ID determination.
	 * @since 2.8.0 Returns empty on product pages.
	 * @since 3.0.0 1. Now checks for 0000 timestamps.
	 *              2. Now uses timestamp formats.
	 * @since 4.1.4 No longer renders the Open Graph Updated Time meta tag.
	 * @see og_updated_time()
	 *
	 * @return string The Article Modified Time meta tag
	 */
	public function article_modified_time() {

		if ( ! $this->output_modified_time() ) return '';

		$time = $this->get_modified_time();

		return $time ? $this->render_element( [
			'property' => 'article:modified_time',
			'content'  => $time,
		] ) : '';
	}

	/**
	 * Renders the Open Graph Updated Time meta tag.
	 *
	 * @since 4.1.4
	 *
	 * @return string The Article Modified Time meta tag, and optionally the Open Graph Updated Time.
	 */
	public function og_updated_time() {

		if ( ! $this->use_og_tags() ) return '';
		if ( ! $this->output_published_time() ) return '';

		$time = $this->get_modified_time();

		return $time ? $this->render_element( [
			'property' => 'og:updated_time',
			'content'  => $time,
		] ) : '';
	}

	/**
	 * Renders Canonical URL meta tag.
	 *
	 * @since 2.0.6
	 * @since 3.0.0 Deleted filter `the_seo_framework_output_canonical`.
	 * @since 3.2.4 Now no longer returns a value when the post is not indexed with a non-custom URL.
	 * @uses $this->get_current_canonical_url()
	 *
	 * @return string The Canonical URL meta tag.
	 */
	public function canonical() {

		$_url = $this->get_current_canonical_url();

		/**
		 * @since 2.6.5
		 * @param string $url The canonical URL. Must be escaped.
		 * @param int    $id  The current page or term ID.
		 */
		$url = (string) \apply_filters_ref_array(
			'the_seo_framework_rel_canonical_output',
			[
				$_url,
				$this->get_the_real_ID(),
			]
		);

		// If the page should not be indexed, consider removing the canonical URL.
		if ( \in_array( 'noindex', $this->get_robots_meta(), true ) ) {
			// If the URL is filtered, don't empty it.
			// If a custom canonical URL is set, don't empty it.
			if ( $url === $_url && ! $this->has_custom_canonical_url() ) {
				$url = '';
			}
		}

		return $url ? $this->render_element(
			[
				'rel'  => 'canonical',
				'href' => $url,
			],
			'link'
		) : '';
	}

	/**
	 * Renders LD+JSON Schema.org scripts.
	 *
	 * @uses $this->render_ld_json_scripts()
	 *
	 * @since 1.2.0
	 * @since 3.1.0 No longer returns early on search, 404 or preview.
	 * @return string The LD+json Schema.org scripts.
	 */
	public function ld_json() {
		/**
		 * @since 2.6.0
		 * @param string $json The JSON output. Must be escaped.
		 * @param int    $id   The current page or term ID.
		 */
		$json = (string) \apply_filters_ref_array(
			'the_seo_framework_ldjson_scripts',
			[
				$this->render_ld_json_scripts(),
				$this->get_the_real_ID(),
			]
		);

		return $json;
	}

	/**
	 * Renders Google Site Verification Code meta tag.
	 *
	 * @since 2.2.4
	 *
	 * @return string The Google Site Verification code meta tag.
	 */
	public function google_site_output() {
		/**
		 * @since 2.6.0
		 * @param string $code The Google verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_ref_array(
			'the_seo_framework_googlesite_output',
			[
				$this->get_option( 'google_verification' ),
				$this->get_the_real_ID(),
			]
		);

		return $code ? $this->render_element( [
			'name'    => 'google-site-verification',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Renders Bing Site Verification Code meta tag.
	 *
	 * @since 2.2.4
	 *
	 * @return string The Bing Site Verification Code meta tag.
	 */
	public function bing_site_output() {
		/**
		 * @since 2.6.0
		 * @param string $code The Bing verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_ref_array(
			'the_seo_framework_bingsite_output',
			[
				$this->get_option( 'bing_verification' ),
				$this->get_the_real_ID(),
			]
		);

		return $code ? $this->render_element( [
			'name'    => 'msvalidate.01',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Renders Yandex Site Verification code meta tag.
	 *
	 * @since 2.6.0
	 *
	 * @return string The Yandex Site Verification code meta tag.
	 */
	public function yandex_site_output() {
		/**
		 * @since 2.6.0
		 * @param string $code The Yandex verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_ref_array(
			'the_seo_framework_yandexsite_output',
			[
				$this->get_option( 'yandex_verification' ),
				$this->get_the_real_ID(),
			]
		);

		return $code ? $this->render_element( [
			'name'    => 'yandex-verification',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Renders Baidu Site Verification code meta tag.
	 *
	 * @since 4.0.5
	 *
	 * @return string The Baidu Site Verification code meta tag.
	 */
	public function baidu_site_output() {
		/**
		 * @since 4.0.5
		 * @param string $code The Baidu verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_ref_array(
			'the_seo_framework_baidusite_output',
			[
				$this->get_option( 'baidu_verification' ),
				$this->get_the_real_ID(),
			]
		);

		return $code ? $this->render_element( [
			'name'    => 'baidu-site-verification',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Renders Pinterest Site Verification code meta tag.
	 *
	 * @since 2.5.2
	 *
	 * @return string The Pinterest Site Verification code meta tag.
	 */
	public function pint_site_output() {
		/**
		 * @since 2.6.0
		 * @param string $code The Pinterest verification code.
		 * @param int    $id   The current post or term ID.
		 */
		$code = (string) \apply_filters_ref_array(
			'the_seo_framework_pintsite_output',
			[
				$this->get_option( 'pint_verification' ),
				$this->get_the_real_ID(),
			]
		);

		return $code ? $this->render_element( [
			'name'    => 'p:domain_verify',
			'content' => $code,
		] ) : '';
	}

	/**
	 * Renders Robots meta tags.
	 * Returns early if blog isn't public. WordPress Core will then output the meta tags.
	 *
	 * @since 2.0.0
	 * @since 4.0.2 Thanks to special tags, output escaping has been added precautionarily.
	 *
	 * @return string The Robots meta tags.
	 */
	public function robots() {

		// Don't do anything if the blog isn't set to public.
		if ( false === $this->is_blog_public() ) return '';

		$meta = $this->get_robots_meta();

		return $meta ? $this->render_element( [
			'name'    => 'robots',
			'content' => implode( ',', $meta ),
		] ) : '';
	}

	/**
	 * Returns the robots meta array.
	 * Memoizes the return value.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function get_robots_meta() {
		return memo() ?? memo(
			/**
			 * @since 2.6.0
			 * @param array $meta The robots meta.
			 * @param int   $id   The current post or term ID.
			 */
			(array) \apply_filters_ref_array(
				'the_seo_framework_robots_meta',
				[
					$this->generate_robots_meta(),
					$this->get_the_real_ID(),
				]
			)
		);
	}

	/**
	 * Renders Shortlink meta tag
	 *
	 * @since 2.2.2
	 * @since 2.9.3 Now work when homepage is a blog.
	 * @uses $this->get_shortlink()
	 *
	 * @return string The Shortlink meta tag.
	 */
	public function shortlink() {

		/**
		 * @since 2.6.0
		 * @param string $url The generated shortlink URL.
		 * @param int    $id  The current post or term ID.
		 */
		$url = (string) \apply_filters_ref_array(
			'the_seo_framework_shortlink_output',
			[
				$this->get_shortlink(),
				$this->get_the_real_ID(),
			]
		);

		return $url ? $this->render_element(
			[
				'rel'  => 'shortlink',
				'href' => $url,
			],
			'link'
		) : '';
	}

	/**
	 * Renders Prev/Next Paged URL meta tags.
	 *
	 * @since 2.2.2
	 * @uses $this->get_paged_urls()
	 *
	 * @return string The Prev/Next Paged URL meta tags.
	 */
	public function paged_urls() {

		$paged_urls = $this->get_paged_urls();
		$id         = $this->get_the_real_ID();

		/**
		 * @since 2.6.0
		 * @param string $next The next-page URL.
		 * @param int    $id   The current post or term ID.
		 */
		$next = (string) \apply_filters_ref_array(
			'the_seo_framework_paged_url_output_next',
			[
				$paged_urls['next'],
				$id,
			]
		);
		/**
		 * @since 2.6.0
		 * @param string $next The previous-page URL.
		 * @param int    $id   The current post or term ID.
		 */
		$prev = (string) \apply_filters_ref_array(
			'the_seo_framework_paged_url_output_prev',
			[
				$paged_urls['prev'],
				$id,
			]
		);

		$output  = $prev ? $this->render_element(
			[
				'rel'  => 'prev',
				'href' => $prev,
			],
			'link'
		) : '';
		$output .= $next ? $this->render_element(
			[
				'rel'  => 'next',
				'href' => $next,
			],
			'link'
		) : '';

		return $output;
	}

	/**
	 * Returns the plugin hidden HTML indicators.
	 * Memoizes the filter outputs.
	 *
	 * @since 2.9.2
	 * @since 4.0.0 Added boot timers.
	 * @since 4.2.0 1. The annotation is translatable again (regressed in 4.0.0).
	 *              2. Is now a protected function.
	 * @access private
	 *
	 * @param string $where                 Determines the position of the indicator.
	 *                                      Accepts 'before' for before, anything else for after.
	 * @param float  $meta_timer            Total meta time in seconds.
	 * @param float  $bootstrap_timer       Total bootstrap time in seconds.
	 * @return string The SEO Framework's HTML plugin indicator.
	 */
	protected function get_plugin_indicator( $where = 'before', $meta_timer = 0, $bootstrap_timer = 0 ) {

		$cache = memo() ?? memo( [
			/**
			 * @since 2.0.0
			 * @param bool $run Whether to run and show the plugin indicator.
			 */
			'run'        => (bool) \apply_filters( 'the_seo_framework_indicator', true ),
			/**
			 * @since 2.4.0
			 * @param bool $show_timer Whether to show the generation time in the indicator.
			 */
			'show_timer' => (bool) \apply_filters( 'the_seo_framework_indicator_timing', true ),
			'annotation' => \esc_html( trim( vsprintf(
				/* translators: 1 = The SEO Framework, 2 = 'by Sybre Waaijer */
				\__( '%1$s %2$s', 'autodescription' ),
				[
					'The SEO Framework',
					/**
					 * @since 2.4.0
					 * @param bool $sybre Whether to show the author name in the indicator.
					 */
					\apply_filters( 'sybre_waaijer_<3', true ) // phpcs:ignore, WordPress.NamingConventions.ValidHookName -- Easter egg.
						? \__( 'by Sybre Waaijer', 'autodescription' )
						: '',
				]
			) ) ),
		] );

		if ( ! $cache['run'] ) return '';

		switch ( $where ) :
			case 'before':
				return "<!-- {$cache['annotation']} -->\n";

			case 'after':
			default:
				if ( $cache['show_timer'] && $meta_timer && $bootstrap_timer ) {
					$timers = sprintf(
						' | %s meta | %s boot',
						number_format( $meta_timer * 1e3, 2, null, '' ) . 'ms',
						number_format( $bootstrap_timer * 1e3, 2, null, '' ) . 'ms'
					);
				} else {
					$timers = '';
				}

				return "<!-- / {$cache['annotation']}{$timers} -->\n";
		endswitch;
	}

	/**
	 * Determines if modified time should be used in the current query.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Removed caching.
	 *
	 * @return bool
	 */
	public function output_modified_time() {

		if ( 'article' !== $this->get_og_type() )
			return false;

		return (bool) $this->get_option( 'post_modify_time' );
	}

	/**
	 * Determines if published time should be used in the current query.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Removed caching.
	 *
	 * @return bool
	 */
	public function output_published_time() {

		if ( 'article' !== $this->get_og_type() )
			return false;

		return (bool) $this->get_option( 'post_publish_time' );
	}

	/**
	 * Determines whether we can use Open Graph tags on the front-end.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Removed cache.
	 * @since 3.1.4 1. Added filter.
	 *              2. Reintroduced cache because of filter.
	 * @TODO add facebook validation? -> Not all services that use OG tags are called Facebook.
	 *       And not all of those services require the same standards as Facebook.
	 *
	 * @return bool
	 */
	public function use_og_tags() {
		return memo() ?? memo(
			/**
			 * @since 3.1.4
			 * @param bool $use
			 */
			(bool) \apply_filters(
				'the_seo_framework_use_og_tags',
				(bool) $this->get_option( 'og_tags' )
			)
		);
	}

	/**
	 * Determines whether we can use Facebook tags on the front-end.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Removed cache.
	 * @since 3.1.4 1. Added filter.
	 *              2. Reintroduced cache because of filter.
	 *
	 * @return bool
	 */
	public function use_facebook_tags() {
		return memo() ?? memo(
			(bool) \apply_filters(
				'the_seo_framework_use_facebook_tags',
				(bool) $this->get_option( 'facebook_tags' )
			)
		);
	}

	/**
	 * Determines whether we can use Twitter tags on the front-end.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 2.8.2 Now also considers Twitter card type output.
	 * @since 3.1.4 Added filter.
	 *
	 * @return bool
	 */
	public function use_twitter_tags() {
		return memo() ?? memo(
			(bool) \apply_filters(
				'the_seo_framework_use_twitter_tags',
				$this->get_option( 'twitter_tags' ) && $this->get_current_twitter_card_type()
			)
		);
	}
}
