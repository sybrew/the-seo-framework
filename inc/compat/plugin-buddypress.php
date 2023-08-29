<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\BuddyPress
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and \tsf()->_verify_include_secret( $_secret ) or die;

\add_filter( 'wp_head', __NAMESPACE__ . '\\_buddypress_init_compat', 0 );

/**
 * Initializes BuddyPress compatibility loader.
 * At wp_head, as BuddyPress loads very late.
 *
 * @hook wp_head 0
 * @since 3.0.0
 * @access private
 */
function _buddypress_init_compat() {
	if ( \is_buddypress() ) {
		// Remove TSF canonical URL, and let BuddyPress handle it.
		\add_filter( 'the_seo_framework_rel_canonical_output', '__return_empty_string' );
	}
}
