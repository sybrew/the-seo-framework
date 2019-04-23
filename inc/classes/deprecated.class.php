<?php
/**
 * @package The_SEO_Framework\Classes
 * @subpackage Classes\Deprecated
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Deprecated
 *
 * Contains all deprecated functions.
 *
 * @since 2.8.0
 * @since 3.1.0: Removed all methods deprecated in 3.0.0.
 * @since 3.3.0: Removed all methods deprecated in 3.1.0.
 * @ignore
 */
final class Deprecated {

	/**
	 * Constructor. Does nothing.
	 */
	public function __construct() { }


	/**
	 * Returns a filterable sequential array of default scripts.
	 *
	 * @since 3.2.2
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 *    Use `the_seo_framework()->ScriptsLoader()::get_default_scripts();` instead.
	 *
	 * @return array
	 */
	public function get_default_scripts() {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->get_default_scripts()', '3.3.0' );

		//! PHP 5.4 compat: put in var.
		$loader = $tsf->ScriptsLoader();
		return $loader::get_default_scripts();
	}

	/**
	 * Enqueues Gutenberg-related scripts.
	 *
	 * @since 3.2.0
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return void Early if already enqueued.
	 */
	public function enqueue_gutenberg_compat_scripts() {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->enqueue_gutenberg_compat_scripts()', '3.3.0' );

		if ( \The_SEO_Framework\_has_run( __METHOD__ ) ) return;

		//! PHP 5.4 compat: put in var.
		$scripts = $tsf->Scripts();
		$loader  = $tsf->ScriptsLoader();
		$scripts::register( $loader::get_gutenberg_compat_scripts() );
	}

	/**
	 * Enqueues Media Upload and Cropping scripts.
	 *
	 * @since 3.1.0
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return void Early if already enqueued.
	 */
	public function enqueue_media_scripts() {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->enqueue_media_scripts()', '3.3.0' );

		if ( \The_SEO_Framework\_has_run( __METHOD__ ) ) return;

		$args = [];
		if ( $tsf->is_post_edit() ) {
			$args['post'] = $tsf->get_the_real_admin_ID();
		}
		\wp_enqueue_media( $args );

		//! PHP 5.4 compat: put in var.
		$scripts = $tsf->Scripts();
		$loader  = $tsf->ScriptsLoader();
		$scripts::register( $loader::get_media_scripts() );
	}

	/**
	 * Enqueues Primary Term Selection scripts.
	 *
	 * @since 3.1.0
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return void Early if already enqueued.
	 */
	public function enqueue_primaryterm_scripts() {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->enqueue_primaryterm_scripts()', '3.3.0' );

		if ( \The_SEO_Framework\_has_run( __METHOD__ ) ) return;

		//! PHP 5.4 compat: put in var.
		$scripts = $tsf->Scripts();
		$loader  = $tsf->ScriptsLoader();
		$scripts::register( $loader::get_primaryterm_scripts() );
	}
}
