<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Render
 * @subpackage The_SEO_Framework\Front
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * Use the_seo_framework()->get_title() instead.
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
	 * Use the_seo_framework()->get_title() instead.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Removed extraneous, unused parameters.
	 * @see $this->get_title()
	 *
	 * @param string $title       The filterable title.
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

		$url = '';

		foreach ( $this->get_image_details_from_cache( ! $this->get_option( 'multi_og_image' ) ) as $image ) {
			$url = $image['url'];
			if ( $url ) break;
		}

		return $url;
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

		static $cache = null;

		return isset( $cache ) ? $cache : $cache = $this->generate_twitter_card_type();
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
		 * @since 2.7.0 : Added output within filter.
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

		if ( $description )
			return '<meta name="description" content="' . \esc_attr( $description ) . '" />' . "\r\n";

		return '';
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

		if ( $description )
			return '<meta property="og:description" content="' . \esc_attr( $description ) . '" />' . "\r\n";

		return '';
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

		if ( $locale )
			return '<meta property="og:locale" content="' . \esc_attr( $locale ) . '" />' . "\r\n";

		return '';
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

		if ( $title )
			return '<meta property="og:title" content="' . \esc_attr( $title ) . '" />' . "\r\n";

		return '';
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

		if ( $type )
			return '<meta property="og:type" content="' . \esc_attr( $type ) . '" />' . "\r\n";

		return '';
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
			$output .= '<meta property="og:image" content="' . \esc_attr( $image['url'] ) . '" />' . "\r\n";

			if ( $image['height'] && $image['width'] ) {
				$output .= '<meta property="og:image:width" content="' . \esc_attr( $image['width'] ) . '" />' . "\r\n";
				$output .= '<meta property="og:image:height" content="' . \esc_attr( $image['height'] ) . '" />' . "\r\n";
			}

			if ( $image['alt'] ) {
				$output .= '<meta property="og:image:alt" content="' . \esc_attr( $image['alt'] ) . '" />' . "\r\n";
			}

			if ( ! $multi )
				break;
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

		if ( $sitename )
			return '<meta property="og:site_name" content="' . \esc_attr( $sitename ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders Open Graph URL meta tag.
	 *
	 * @since 1.3.0
	 * @since 2.9.3 Added filter
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

		// TODO add esc_attr()? The URL is already safe for attribute usage... I'm not sure if that'll potentially break the URL.
		if ( $url )
			return '<meta property="og:url" content="' . $url . '" />' . "\r\n";

		return '';
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

		if ( $card )
			return '<meta name="twitter:card" content="' . \esc_attr( $card ) . '" />' . "\r\n";

		return '';
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

		if ( $site )
			return '<meta name="twitter:site" content="' . \esc_attr( $site ) . '" />' . "\r\n";

		return '';
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
		 * @param string $twitter_page The Twitter page creator.
		 * @param int    $id           The current page or term ID.
		 */
		$twitter_page = (string) \apply_filters_ref_array(
			'the_seo_framework_twittercreator_output',
			[
				$this->get_current_author_option( 'twitter_page' ) ?: $this->get_option( 'twitter_creator' ),
				$this->get_the_real_ID(),
			]
		);

		if ( $twitter_page )
			return '<meta name="twitter:creator" content="' . \esc_attr( $twitter_page ) . '" />' . "\r\n";

		return '';
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

		if ( $title )
			return '<meta name="twitter:title" content="' . \esc_attr( $title ) . '" />' . "\r\n";

		return '';
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

		if ( $description )
			return '<meta name="twitter:description" content="' . \esc_attr( $description ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders Twitter Image meta tag.
	 *
	 * @since 2.2.2
	 * @since 4.1.2 Now forwards the `multi_og_image` option to the generator. Although
	 *              it'll always use just one image, we read this option so we'll only
	 *              use a single cache instance internally with the generator.
	 *
	 * @return string The Twitter Image meta tag.
	 */
	public function twitter_image() {

		if ( ! $this->use_twitter_tags() ) return '';

		$output = '';

		foreach ( $this->get_image_details_from_cache( ! $this->get_option( 'multi_og_image' ) ) as $image ) {
			$output .= '<meta name="twitter:image" content="' . \esc_attr( $image['url'] ) . '" />' . "\r\n";

			if ( $image['height'] && $image['width'] ) {
				$output .= '<meta name="twitter:image:width" content="' . \esc_attr( $image['width'] ) . '" />' . "\r\n";
				$output .= '<meta name="twitter:image:height" content="' . \esc_attr( $image['height'] ) . '" />' . "\r\n";
			}

			if ( $image['alt'] ) {
				$output .= '<meta name="twitter:image:alt" content="' . \esc_attr( $image['alt'] ) . '" />' . "\r\n";
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

		$output = '';

		$theme_color = $this->get_option( 'theme_color' );

		if ( $theme_color )
			$output = '<meta name="theme-color" content="' . \esc_attr( $theme_color ) . '" />' . "\r\n";

		return $output;
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
				$this->get_current_author_option( 'facebook_page' ) ?: $this->get_option( 'facebook_author' ),
				$this->get_the_real_ID(),
			]
		);

		if ( $facebook_page )
			return '<meta property="article:author" content="' . \esc_attr( \esc_url_raw( $facebook_page, [ 'https', 'http' ] ) ) . '" />' . "\r\n";

		return '';
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

		if ( $publisher )
			return '<meta property="article:publisher" content="' . \esc_attr( \esc_url_raw( $publisher, [ 'https', 'http' ] ) ) . '" />' . "\r\n";

		return '';
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

		if ( $app_id )
			return '<meta property="fb:app_id" content="' . \esc_attr( $app_id ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders Article Publishing Time meta tag.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Returns empty on product pages.
	 * @since 3.0.0 : 1. Now checks for 0000 timestamps.
	 *                2. Now uses timestamp formats.
	 *                3. Now uses GMT time.
	 *
	 * @return string The Article Publishing Time meta tag.
	 */
	public function article_published_time() {

		if ( ! $this->output_published_time() ) return '';

		$id   = $this->get_the_real_ID();
		$post = \get_post( $id );

		$post_date_gmt = $post->post_date_gmt;

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

		if ( $time )
			return '<meta property="article:published_time" content="' . \esc_attr( $time ) . '" />' . "\r\n";

		return '';
	}

	/**
	 * Renders Article Modified Time meta tag.
	 * Also renders the Open Graph Updated Time meta tag if Open Graph tags are enabled.
	 *
	 * @since 2.2.2
	 * @since 2.7.0 Listens to $this->get_the_real_ID() instead of WordPress Core ID determination.
	 * @since 2.8.0 Returns empty on product pages.
	 * @since 3.0.0 : 1. Now checks for 0000 timestamps.
	 *                2. Now uses timestamp formats.
	 *
	 * @return string The Article Modified Time meta tag, and optionally the Open Graph Updated Time.
	 */
	public function article_modified_time() {

		if ( ! $this->output_modified_time() ) return '';

		$id = $this->get_the_real_ID();

		$post              = \get_post( $id );
		$post_modified_gmt = $post->post_modified_gmt;

		if ( '0000-00-00 00:00:00' === $post_modified_gmt )
			return '';

		/**
		 * @since 2.3.0
		 * @since 2.7.0 Added output within filter.
		 * @param string $time The article modified time.
		 * @param int    $id   The current page or term ID.
		 */
		$time = (string) \apply_filters_ref_array(
			'the_seo_framework_modifiedtime_output',
			[
				$this->gmt2date( $this->get_timestamp_format(), $post_modified_gmt ),
				$id,
			]
		);

		if ( $time ) {
			$output = '<meta property="article:modified_time" content="' . \esc_attr( $time ) . '" />' . "\r\n";

			if ( $this->use_og_tags() )
				$output .= '<meta property="og:updated_time" content="' . \esc_attr( $time ) . '" />' . "\r\n";

			return $output;
		}

		return '';
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

		// TODO add esc_attr()? The URL is already safe for attribute usage... I'm not sure if that'll potentially break the URL.
		if ( $url )
			return '<link rel="canonical" href="' . $url . '" />' . PHP_EOL;

		return '';
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

		if ( $code )
			return '<meta name="google-site-verification" content="' . \esc_attr( $code ) . '" />' . PHP_EOL;

		return '';
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

		if ( $code )
			return '<meta name="msvalidate.01" content="' . \esc_attr( $code ) . '" />' . PHP_EOL;

		return '';
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

		if ( $code )
			return '<meta name="yandex-verification" content="' . \esc_attr( $code ) . '" />' . PHP_EOL;

		return '';
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

		if ( $code )
			return '<meta name="baidu-site-verification" content="' . \esc_attr( $code ) . '" />' . PHP_EOL;

		return '';
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

		if ( $code )
			return '<meta name="p:domain_verify" content="' . \esc_attr( $code ) . '" />' . PHP_EOL;

		return '';
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

		if ( empty( $meta ) )
			return '';

		return sprintf( '<meta name="robots" content="%s" />' . PHP_EOL, \esc_attr( implode( ',', $meta ) ) );
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

		static $cache = null;

		/**
		 * @since 2.6.0
		 * @param array $meta The robots meta.
		 * @param int   $id   The current post or term ID.
		 */
		return isset( $cache ) ? $cache : $cache = (array) \apply_filters_ref_array(
			'the_seo_framework_robots_meta',
			[
				$this->robots_meta(),
				$this->get_the_real_ID(),
			]
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

		if ( $url )
			return '<link rel="shortlink" href="' . $url . '" />' . PHP_EOL;

		return '';
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

		$id = $this->get_the_real_ID();

		$paged_urls = $this->get_paged_urls();

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

		$output = '';

		if ( $prev )
			$output .= '<link rel="prev" href="' . $prev . '" />' . PHP_EOL;

		if ( $next )
			$output .= '<link rel="next" href="' . $next . '" />' . PHP_EOL;

		return $output;
	}

	/**
	 * Returns the plugin hidden HTML indicators.
	 * Memoizes the filter outputs.
	 *
	 * @since 2.9.2
	 * @since 4.0.0 Added boot timers.
	 *
	 * @param string $where  Determines the position of the indicator.
	 *                       Accepts 'before' for before, anything else for after.
	 * @param int    $timing Determines when the output started.
	 * @return string The SEO Framework's HTML plugin indicator.
	 */
	public function get_plugin_indicator( $where = 'before', $timing = 0 ) {

		static $cache;

		if ( ! $cache ) {
			$cache = [
				/**
				 * @since 2.0.0
				 * @param bool $run Whether to run and show the plugin indicator.
				 */
				'run'        => (bool) \apply_filters( 'the_seo_framework_indicator', true ),
				/**
				 * @since 2.4.0
				 * @param bool $sybre Whether to show the author name in the indicator.
				 */
				// phpcs:ignore, WordPress.NamingConventions.ValidHookName -- Easter egg.
				'author'     => (bool) \apply_filters( 'sybre_waaijer_<3', true ) ? \esc_html__( 'by Sybre Waaijer', 'autodescription' ) : '',
				/**
				 * @since 2.4.0
				 * @param bool $show_timer Whether to show the generation time in the indicator.
				 */
				'show_timer' => (bool) \apply_filters( 'the_seo_framework_indicator_timing', true ),
			];
		}

		if ( false === $cache['run'] )
			return '';

		if ( 'before' === $where ) {
			/* translators: 1 = The SEO Framework, 2 = 'by Sybre Waaijer */
			$output = sprintf( '%1$s %2$s', 'The SEO Framework', $cache['author'] );

			return sprintf( '<!-- %s -->', trim( $output ) ) . PHP_EOL;
		} else {
			if ( $cache['show_timer'] && $timing ) {
				$timers = sprintf(
					' | %s meta | %s boot',
					number_format( ( microtime( true ) - $timing ) * 1e3, 2 ) . 'ms',
					number_format( _bootstrap_timer() * 1e3, 2 ) . 'ms'
				);
			} else {
				$timers = '';
			}
			/* translators: 1 = The SEO Framework, 2 = 'by Sybre Waaijer */
			$output = sprintf( '%1$s %2$s', 'The SEO Framework', $cache['author'] ) . $timers;

			return sprintf( '<!-- / %s -->', trim( $output ) ) . PHP_EOL;
		}
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
	 * @since 3.1.4 : 1. Added filter.
	 *                2. Reintroduced cache because of filter.
	 * @TODO add facebook validation.
	 *
	 * @return bool
	 */
	public function use_og_tags() {
		static $cache;
		/**
		 * @since 3.1.4
		 * @param bool $use
		 */
		return isset( $cache ) ? $cache : $cache = (bool) \apply_filters(
			'the_seo_framework_use_og_tags',
			(bool) $this->get_option( 'og_tags' )
		);
	}

	/**
	 * Determines whether we can use Facebook tags on the front-end.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Removed cache.
	 * @since 3.1.4 : 1. Added filter.
	 *                2. Reintroduced cache because of filter.
	 *
	 * @return bool
	 */
	public function use_facebook_tags() {
		static $cache;
		/**
		 * @since 3.1.4
		 * @param bool $use
		 */
		return isset( $cache ) ? $cache : $cache = (bool) \apply_filters(
			'the_seo_framework_use_facebook_tags',
			(bool) $this->get_option( 'facebook_tags' )
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
		static $cache;
		/**
		 * @since 3.1.4
		 * @param bool $use
		 */
		return isset( $cache ) ? $cache : $cache = (bool) \apply_filters(
			'the_seo_framework_use_twitter_tags',
			$this->get_option( 'twitter_tags' ) && $this->get_current_twitter_card_type()
		);
	}
}
