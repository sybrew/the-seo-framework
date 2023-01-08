<?php
/**
 * @package The_SEO_Framework\Views\Debug
 * @subpackage The_SEO_Framework\Debug
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use The_SEO_Framework\Internal\Debug;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

$id        = $this->get_the_real_ID();
$mdash     = ' &mdash; ';
$taxonomy  = $this->get_current_taxonomy();
$post_type = $this->get_current_post_type();

// This will return 'Page' on all non-archive types (except the homepage)
if ( $this->is_real_front_page() ) {
	$type = 'Front Page';
} elseif ( $taxonomy ) {
	$type = $this->get_tax_type_label( $taxonomy );
} elseif ( $post_type ) {
	$type = $this->get_post_type_label( $post_type );
} else {
	$type = 'Unknown';
}

$cache_key = $this->generate_cache_key( $id, $taxonomy );

if ( is_admin() ) {
	$bstyle = is_rtl()
		? 'direction:ltr;color:#444;font-family:Georgio,sans-serif;font-size:14px;clear:both;float:left;position:relative;width:calc( 100% - 200px );min-height:700px;padding:0;margin:20px 180px 40px 20px;overflow:hidden;border:1px solid #ccc;border-radius:3px;line-height:18pxfont-feature-settings:normal;font-variant:normal'
		: 'direction:ltr;color:#444;font-family:Georgio,sans-serif;font-size:14px;clear:both;float:left;position:relative;width:calc( 100% - 200px );min-height:700px;padding:0;margin:20px 20px 40px 180px;overflow:hidden;border:1px solid #ccc;border-radius:3px;line-height:18pxfont-feature-settings:normal;font-variant:normal';
	?>
	<div style="<?= $bstyle // phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
		<h3 style="font-family:unset;font-size:14px;padding:0 12px;margin:0;line-height:39px;border-bottom:2px solid #aaa;position:absolute;z-index:9002;width:100%;right:0;left:0;top:0;background:#fff;border-radius:3px 3px 0 0;height:39px;">
			SEO Debug Information
			<?php
			if ( $this->is_post_edit() || $this->is_term_edit() ) :
				echo ' :: ';
				echo esc_html( "Type: $type" );
				echo esc_html( $mdash . 'ID: ' . $id );
				echo esc_html( $mdash . 'Cache key: ' . ( $cache_key ?: 'N/A' ) );
				echo esc_html( $mdash . 'Plugin version: ' . THE_SEO_FRAMEWORK_VERSION );
				echo esc_html( $mdash . 'Plugin DB version: c' . get_option( 'the_seo_framework_upgraded_db_version' ) . ' | e' . THE_SEO_FRAMEWORK_DB_VERSION );
			endif;
			?>
		</h3>
		<div style="position:absolute;bottom:0;right:0;left:0;top:39px;margin:0;padding:0;background:#fff;border-radius:3px;overflow-x:hidden;z-index:9001">
			<?php
			Debug::_output_debug_header();
			Debug::_output_debug_query();
			?>
		</div>
	</div>
	<?php
} else {
	?>
	<style>.wp-ui-notification{color:#fff;background-color:#d54e21}.code.highlight{font-family:Consolas,Monaco,monospace;font-size:14px;}.theseoframework-debug h3{font-size:18px;margin:18px 0}</style>
	<div class=theseoframework-debug style="direction:ltr;color:#444;font-family:Georgio,sans-serif;font-size:14px;clear:both;float:left;position:relative;width:calc( 100% - 80px );min-height:700px;padding:0;margin:40px;overflow:hidden;border:1px solid #ccc;border-radius:3px;line-height:18px;font-feature-settings:normal;font-variant:normal">
		<h3 style="font-family:unset;font-size:14px;padding:0 12px;margin:0;line-height:39px;border-bottom:2px solid #aaa;position:absolute;z-index:9002;width:100%;right:0;left:0;top:0;background:#fff;border-radius:3px 3px 0 0;height:39px">
			SEO Debug Information
			<?php
			echo ' :: ';
			echo 'Type: ' . esc_html( $type );
			echo esc_html( $mdash . 'ID: ' . $id );
			echo esc_html( $mdash . 'Cache key: ' . ( $cache_key ?: 'N/A' ) );
			echo esc_html( $mdash . 'Plugin version: ' . THE_SEO_FRAMEWORK_VERSION );
			echo esc_html( $mdash . 'Plugin DB version: c' . get_option( 'the_seo_framework_upgraded_db_version' ) . ' | e' . THE_SEO_FRAMEWORK_DB_VERSION );
			?>
		</h3>
		<div style="position:absolute;bottom:0;right:0;left:0;top:39px;margin:0;padding:0;background:#fff;border-radius:3px;overflow-x:hidden;z-index:9001">
			<?php
			Debug::_output_debug_header();
			?>
			<div style="width:50%;float:left;">
				<?php
				Debug::_output_debug_query_from_cache();
				?>
			</div><div style="width:50%;float:right;">
				<?php
				Debug::_output_debug_query();
				?>
			</div>
		</div>
	</div>
	<?php
}
