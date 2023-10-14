<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\Jetpack
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

if ( Data\Plugin::get_option( 'og_tags' ) )
	\add_filter( 'jetpack_enable_open_graph', '__return_false' );

if ( Data\Plugin::get_option( 'twitter_tags' ) )
	\add_filter( 'jetpack_disable_twitter_cards', '__return_true' );
