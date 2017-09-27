<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\User_Data
 *
 * Holds and maintains User data.
 *
 * @since 3.0.0
 */
class User_Data extends Term_Data {

	/**
	 * Constructor, load parent constructor
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Returns default user meta.
	 *
	 * @since 3.0.0
	 *
	 * @return array The default user meta index and values.
	 */
	public function get_default_user_data() {
		return array(
			'counter_type' => 3,
			'facebook_page' => '',
			'twitter_page' => '',
		);
	}

	/**
	 * Returns the current post author ID.
	 *
	 * @since 3.0.0
	 * @staticvar $cache
	 *
	 * @return int|bool Post author on success, false on failure.
	 */
	public function get_current_post_author_id() {

		static $cache;

		if ( isset( $cache ) )
			return $cache;

		$post = \get_post( $this->get_the_real_ID() );

		return $cache = isset( $post->post_author ) ? (int) $post->post_author : false;
	}

	/**
	 * Sets up user ID and returns it if user is found.
	 * To be used in AJAX, back-end and front-end.
	 *
	 * @since 2.7.0
	 *
	 * @return int $user_id : 0 if user is not found.
	 */
	public function get_user_id() {

		static $user_id = null;

		if ( isset( $user_id ) )
			return $user_id;

		$user = \wp_get_current_user();

		return $user_id = $user->exists() ? (int) $user->ID : 0;
	}

	/**
	 * Fetches The SEO Framework usermeta.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 Always returns array, even if no value is assigned.
	 * @staticvar array $usermeta_cache
	 *
	 * @param int $user_id The user ID.
	 * @param string $key The user metadata key. Leave empty to fetch all data.
	 * @param bool $use_cache Whether to store and use options from cache.
	 * @return array The user SEO meta data.
	 */
	public function get_user_meta( $user_id, $key = THE_SEO_FRAMEWORK_USER_OPTIONS, $use_cache = true ) {

		if ( false === $use_cache )
			return ( $meta = \get_user_meta( $user_id, $key, true ) ) && is_array( $meta ) ? $meta : array();

		static $usermeta_cache = array();

		if ( isset( $usermeta_cache[ $user_id ][ $key ] ) )
			return $usermeta_cache[ $user_id ][ $key ];

		return $usermeta_cache[ $user_id ][ $key ] = ( $meta = \get_user_meta( $user_id, $key, true ) ) && is_array( $meta ) ? $meta : array();
	}

	/**
	 * Returns current post author option.
	 *
	 * @since 3.0.0
	 *
	 * @param int $author_id The author ID. When empty, it will return $default.
	 * @param string $option The option name. When empty, it will return $default.
	 * @param mixed $default The default value to return when the data doesn't exist.
	 * @return mixed The metadata value
	 */
	public function get_author_option( $author_id, $option, $default = null ) {

		if ( ! $author_id || ! $option )
			return $default;

		return $this->get_user_option( $author_id, $option, $default );
	}

	/**
	 * Returns current post author option.
	 *
	 * @since 3.0.0
	 *
	 * @param string $option The option name.
	 * @param mixed $default The default value to return when the data doesn't exist.
	 * @return mixed The metadata value
	 */
	public function get_current_author_option( $option, $default = null ) {
		return $this->get_author_option( $this->get_current_post_author_id(), $option, $default );
	}

	/**
	 * Fetches user SEO user meta data by name.
	 * Caches all meta data per $user_id.
	 *
	 * If no $user_id is supplied, it will fetch the current logged in user ID.
	 *
	 * @since 2.7.0
	 * @since 3.0.0 1. Default is no longer cached.
	 *              2. Now always fallbacks to $default.
	 *              3. Added not-found cache.
	 * @staticvar array $options_cache
	 * @staticvar array $notfound_cache
	 *
	 * @param int $user_id The user ID. When empty, it will try to fetch the current user.
	 * @param string $option The option name.
	 * @param mixed $default The default value to return when the data doesn't exist.
	 * @return mixed The metadata value.
	 */
	public function get_user_option( $user_id = 0, $option, $default = null ) {

		if ( ! $option )
			return $default;

		if ( empty( $user_id ) )
			$user_id = $this->get_user_id();

		if ( ! $user_id )
			return $default;

		static $options_cache = array(),
		       $notfound_cache = array();

		if ( isset( $options_cache[ $user_id ][ $option ] ) )
			return $options_cache[ $user_id ][ $option ];

		if ( isset( $notfound_cache[ $user_id ][ $option ] ) )
			return $default;

		$usermeta = $this->get_user_meta( $user_id );

		if ( isset( $usermeta[ $option ] ) ) {
			return $options_cache[ $user_id ][ $option ] = $usermeta[ $option ];
		} else {
			$notfound_cache[ $user_id ][ $option ] = true;
		}

		return $default;
	}

	/**
	 * Updates user SEO option.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 New users now get a new array assigned.
	 *
	 * @param int $user_id The user ID.
	 * @param string $option The user's SEO metadata option.
	 * @param mixed $value The escaped option value.
	 * @return bool True on success. False on failure.
	 */
	public function update_user_option( $user_id = 0, $option, $value ) {

		if ( ! $option )
			return false;

		if ( empty( $user_id ) )
			$user_id = $this->get_user_id();

		if ( empty( $user_id ) )
			return false;

		$meta = $this->get_user_meta( $user_id, THE_SEO_FRAMEWORK_USER_OPTIONS, false );

		/**
		 * @since 2.8.0 initializes new array on empty values.
		 */
		is_array( $meta ) or $meta = array();

		$meta[ $option ] = $value;

		return \update_user_meta( $user_id, THE_SEO_FRAMEWORK_USER_OPTIONS, $meta );
	}
}
