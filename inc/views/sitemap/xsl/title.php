<?php
/**
 * @package The_SEO_Framework\Views\Sitemap\XSL\Table
 * @subpackage The_SEO_Framework\Sitemap\XSL
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

$title    = __( 'XML Sitemap', 'autodescription' );
$sep      = $this->get_title_separator();
$addition = $this->get_blogname();

?>
<title><?php echo esc_xml( $this->s_title_raw( "$title $sep $addition" ) ); ?></title>
