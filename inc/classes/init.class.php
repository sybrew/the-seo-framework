<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Init
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Front,
	\The_SEO_Framework\Meta,
	\The_SEO_Framework\Data;

use \The_SEO_Framework\Helper\{
	Post_Types,
	Query,
	Taxonomies,
};

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
 * Class The_SEO_Framework\Init
 *
 * Outputs all data in front-end header
 *
 * @since 2.8.0
 */
class Init extends Pool {


	/**
	 * Returns the document title.
	 *
	 * This method serves as a callback for filter `pre_get_document_title`.
	 * Use tsf()->get_title() instead.
	 *
	 * @since 3.1.0
	 * @since 4.3.0 Now escapes the filter output.
	 * @see $this->get_title()
	 *
	 * @param string $title The filterable title.
	 * @return string The document title
	 */
	public function get_document_title( $title = '' ) {

		if ( ! Query\Utils::query_supports_seo() )
			return $title;

		/**
		 * @since 3.1.0
		 * @param string $title The generated title.
		 * @param int    $id    The page or term ID.
		 */
		return \esc_attr( \apply_filters_ref_array(
			'the_seo_framework_pre_get_document_title',
			[
				Meta\Title::get_title(),
				Query::get_the_real_id(),
			]
		) );
	}

	/**
	 * Returns the document title.
	 *
	 * This method serves as a callback for filter `wp_title`.
	 * Use tsf()->get_title() instead.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Removed extraneous, unused parameters.
	 * @since 4.3.0 Now escapes the filter output.
	 * @see $this->get_title()
	 *
	 * @param string $title The filterable title.
	 * @return string $title
	 */
	public function get_wp_title( $title = '' ) {

		if ( ! Query\Utils::query_supports_seo() )
			return $title;

		/**
		 * @since 3.1.0
		 * @param string $title The generated title.
		 * @param int    $id    The page or term ID.
		 */
		return \esc_attr( \apply_filters_ref_array(
			'the_seo_framework_wp_title',
			[
				Meta\Title::get_title(),
				Query::get_the_real_id(),
			]
		) );
	}

	/**
	 * Redirects singular page to an alternate URL.
	 *
	 * @since 2.9.0
	 * @since 3.1.0 1. Now no longer redirects on preview.
	 *              2. Now listens to post type settings.
	 * @since 4.0.0 1. No longer tries to redirect on "search".
	 *              2. Added term redirect support.
	 *              3. No longer redirects on Customizer.
	 * @access private
	 *
	 * @return void early on non-singular pages.
	 */
	public function _init_custom_field_redirect() {

		if ( ! Query\Utils::query_supports_seo() ) return;

		$url = Meta\URI::get_redirect_url();

		if ( $url ) {
			/**
			 * @since 4.1.2
			 * @param string $url The URL we're redirecting to.
			 */
			\do_action( 'the_seo_framework_before_redirect', $url );

			$this->do_redirect( $url );
		}
	}

	/**
	 * Redirects vistor to input $url.
	 *
	 * @since 2.9.0
	 *
	 * @param string $url The redirection URL
	 * @return void Early if no URL is supplied.
	 */
	public function do_redirect( $url = '' ) {

		if ( 'template_redirect' !== \current_action() ) {
			$this->_doing_it_wrong( __METHOD__, 'Only use this method on action "template_redirect".', '2.9.0' );
			return;
		}

		// All WP defined protocols are allowed.
		$url = \sanitize_url( $url );

		if ( empty( $url ) ) {
			$this->_doing_it_wrong( __METHOD__, 'You need to supply an input URL.', '2.9.0' );
			return;
		}

		/**
		 * @since 2.8.0
		 * @param int <unsigned> $redirect_type
		 */
		$redirect_type = \absint( \apply_filters( 'the_seo_framework_redirect_status_code', 301 ) );

		if ( $redirect_type > 399 || $redirect_type < 300 )
			$this->_doing_it_wrong( __METHOD__, 'You should use 3xx HTTP Status Codes. Recommended 301 and 302.', '2.8.0' );

		if ( ! $this->allow_external_redirect() ) {
			// Only HTTP/HTTPS and home URLs are allowed.
			$path = $this->set_url_scheme( $url, 'relative' );
			$url  = \trailingslashit( Meta\URI\Utils::get_site_host() ) . ltrim( $path, ' /' );

			// Maintain current request's scheme.
			$scheme = Query::is_ssl() ? 'https' : 'http';

			\wp_safe_redirect( $this->set_url_scheme( $url, $scheme ), $redirect_type );
			exit;
		}

		// phpcs:ignore, WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- intended feature. Disable via $this->allow_external_redirect().
		\wp_redirect( $url, $redirect_type );
		exit;
	}

	/**
	 * Prepares feed modifications.
	 *
	 * @since 4.1.0
	 * @access private
	 */
	public function _init_feed() {
		\is_feed() and new Bridges\Feed;
	}

	/**
	 * Alters the oEmbed response data.
	 *
	 * @hook oembed_response_data 10
	 * @since 4.0.5
	 * @since 4.1.1 Now also alters titles and images.
	 * @access private
	 *
	 * @param array    $data   The response data.
	 * @param \WP_Post $post   The post object.
	 * @return array Possibly altered $data.
	 */
	public function _alter_oembed_response_data( $data, $post ) {

		if ( Data\Plugin::get_option( 'oembed_use_og_title' ) )
			$data['title'] = $this->get_open_graph_title( [ 'id' => $post->ID ] ) ?: $data['title'];

		if ( Data\Plugin::get_option( 'oembed_use_social_image' ) ) {
			$image_details = current( Meta\Image::get_image_details(
				[ 'id' => $post->ID ],
				true,
				'oembed'
			) );

			if ( $image_details && $image_details['url'] && $image_details['width'] && $image_details['height'] ) {
				// Override WordPress provided data.
				$data['thumbnail_url']    = $image_details['url'];
				$data['thumbnail_width']  = $image_details['width'];
				$data['thumbnail_height'] = $image_details['height'];
			}
		}

		if ( Data\Plugin::get_option( 'oembed_remove_author' ) )
			unset( $data['author_url'], $data['author_name'] );

		return $data;
	}
}
