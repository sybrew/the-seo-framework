<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\BuddyPress
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

\add_filter( 'the_seo_framework_meta_generator_pools', __NAMESPACE__ . '\\_buddypress_filter_generator_pools' );

/**
 * Changes the meta render data for BuddyPress profiles.
 *
 * @hook the_seo_framework_meta_generator_pools 10
 * @since 5.0.0
 * @access private
 *
 * @param string[] $generator_pools A list of tag pools requested for the current query.
 *                                  The tag pool names correspond directly to the classes'.
 *                                  Do not register new pools, it'll cause a fatal error.
 * @return string[] The adjusted tag pools
 */
function _buddypress_filter_generator_pools( $generator_pools ) {

	if ( \function_exists( 'is_buddypress' ) && \is_buddypress() ) {
		$generator_pools = array_diff(
			$generator_pools,
			[ 'Robots', 'URI', 'Open_Graph', 'Twitter', 'Schema' ],
		);
	}

	return $generator_pools;
}
