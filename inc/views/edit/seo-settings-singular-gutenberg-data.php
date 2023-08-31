<?php
/**
 * @package The_SEO_Framework\Views\Edit
 * @subpackage The_SEO_Framework\Admin\Edit\Post
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

use The_SEO_Framework\Helper\Query;

printf(
	'<div id=%s data-post-id=%d class=hidden></div>',
	'tsf-gutenberg-data-holder',
	Query::get_the_real_id() // phpcs:ignore, WordPress.Security.EscapeOutput -- printf casts to int.
);
