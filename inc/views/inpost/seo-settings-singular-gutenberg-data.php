<?php
/**
 * @package The_SEO_Framework\Views\Inpost
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

printf(
	'<div id=%s data-post-id=%d style=display:none></div>',
	'tsf-gutenberg-data-holder',
	$this->get_the_real_ID()
);
