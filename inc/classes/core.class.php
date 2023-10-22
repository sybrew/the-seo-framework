<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Core
 * @see ./index.php for facade details.
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\Data;

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
 * Class The_SEO_Framework\Core
 *
 * Initializes the plugin & Holds plugin core functions.
 *
 * @since 2.8.0
 * @since 4.3.0 1. Deprecated $the_seo_framework_debug
 *              2. Deprecated $script_debug
 *              3. Deprecated $seo_settings_page_slug
 *              4. Deprecated $loaded
 */
class Core {

	/**
	 * Calling any top file without __construct() is forbidden.
	 */
	private function __construct() { }

	/**
	 * Handles unapproachable invoked properties.
	 *
	 * Makes sure deprecated properties are still overwritten.
	 * If the property never existed, default PHP behavior is invoked.
	 *
	 * @since 2.8.0
	 * @since 3.2.2 This method no longer allows to overwrite protected or private variables.
	 * @since 4.3.0 Now protects against fatal errors on PHP 8.2 or later.
	 *
	 * @param string $name  The property name.
	 * @param mixed  $value The property value.
	 */
	final public function __set( $name, $value ) {

		switch ( $name ) {
			case 'the_seo_framework_debug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; set constant THE_SEO_FRAMEWORK_DEBUG' );
				return false;
			case 'script_debug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; set constant SCRIPT_DEBUG' );
				return false;
			case 'seo_settings_page_slug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; set constant THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG' );
				return false;
		}

		/**
		 * For now, no deprecation is being handled; as no properties have been deprecated. Just removed.
		 */
		$this->_inaccessible_p_or_m( "tsf()->$name", 'unknown' );

		// Invoke default behavior: Write variable if it's not protected.
		if ( property_exists( $this, $name ) )
			$this->$name = $value;
	}

	/**
	 * Handles unapproachable invoked properties.
	 *
	 * Makes sure deprecated properties are still accessible.
	 *
	 * @since 2.7.0
	 * @since 3.1.0 Removed known deprecations.
	 * @since 3.2.2 This method no longer invokes PHP errors, nor returns protected values.
	 * @since 4.3.0 Removed 'load_option' deprecation.
	 *
	 * @param string $name The property name.
	 * @return mixed
	 */
	final public function __get( $name ) {

		switch ( $name ) {
			case 'inpost_nonce_field':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; you should make your own.' );
				return Data\Admin\Post::$nonce_action;
			case 'inpost_nonce_name':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; you should make your own.' );
				return Data\Admin\Post::$nonce_name;
			case 'is_headless':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; use function \The_SEO_Framework\is_headless()' );
				return is_headless();
			case 'loaded':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; you may drop the loaded check.' );
				return true;
			case 'pretty_permalinks':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; use tsf()->query()->utils()->using_pretty_permalinks()' );
				return $this->query()->utils()->using_pretty_permalinks();
			case 'script_debug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; use constant SCRIPT_DEBUG' );
				return \SCRIPT_DEBUG;
			case 'seo_settings_page_slug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; use constant THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG or The_SEO_Framework\Admin::get_top_menu_args()' );
				return \THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG;
			case 'seo_settings_page_hook':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; use `tsf()->admin()->menu()->get_page_hook_name()` instead.' );
				return Admin\Menu::get_page_hook_name();
			case 'the_seo_framework_debug':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; use constant THE_SEO_FRAMEWORK_DEBUG' );
				return \THE_SEO_FRAMEWORK_DEBUG;
			case 'the_seo_framework_use_transients':
				$this->_inaccessible_p_or_m( "tsf()->$name", 'since 4.3.0; with no alternative available' );
				return true;
		}

		$this->_inaccessible_p_or_m( "tsf()->$name", 'unknown' );
	}

	/**
	 * Handles unapproachable invoked methods.
	 *
	 * @since 2.7.0
	 *
	 * @param string $name      The method name.
	 * @param array  $arguments The method arguments.
	 * @return mixed|void
	 */
	final public function __call( $name, $arguments ) {

		static $depr_class = null;

		$depr_class ??= new Internal\Deprecated;

		if ( \is_callable( [ $depr_class, $name ] ) )
			return \call_user_func_array( [ $depr_class, $name ], $arguments );

		$this->_inaccessible_p_or_m( "tsf()->$name()" );
	}

	/**
	 * Whether to display Extension Manager suggestions to the user based on several conditions.
	 *
	 * @since 4.2.4
	 * @uses TSF_DISABLE_SUGGESTIONS Set that to true if you don't like us.
	 *
	 * @return bool
	 */
	public function _display_extension_suggestions() {
		return \current_user_can( 'install_plugins' ) && ! ( \defined( 'TSF_DISABLE_SUGGESTIONS' ) && \TSF_DISABLE_SUGGESTIONS );
	}
}
