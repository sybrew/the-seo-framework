<?php
/**
 * @package The_SEO_Framework\Views\Debug
 */

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

if ( The_SEO_Framework\Debug::has_debug_output() ) :
	if ( $this->the_seo_framework_debug_hidden ) :
		echo "\r\n<!--\r\n:: THE SEO FRAMEWORK DEBUG :: \r\n";
		The_SEO_Framework\Debug::_output_debug();
		echo "\r\n:: / THE SEO FRAMEWORK DEBUG ::\r\n-->\r\n";
	else :

		$id = $this->get_the_real_ID();
		$mdash = ' &mdash; ';
		$term = $this->is_archive() ? $this->fetch_the_term( $id ) : '';
		$taxonomy = isset( $term->taxonomy ) ? $term->taxonomy : '';

		//* This will return 'Page' on all non-archive types (except the home page)
		$type = ! $this->is_archive() && $this->is_real_front_page() || $this->is_front_page_by_id( $id ) ? 'Front Page' : $this->get_the_term_name( $term );
		$cache_key = $this->generate_cache_key( $this->get_the_real_ID(), $taxonomy );

		if ( $this->is_admin() ) {
			?>
			<div style="color:#444;font-family:Georgio,sans-serif;font-size:14px;clear:both;float:left;position:relative;width:calc( 100% - 200px );min-height:700px;padding:0;margin:20px 20px 40px 180px;overflow:hidden;border:1px solid #ccc;border-radius:3px;line-height:18px">
				<h3 style="font-size:14px;padding:0 12px;margin:0;line-height:39px;border-bottom:2px solid #aaa;position:absolute;z-index:9002;width:100%;right:0;left:0;top:0;background:#fff;border-radius:3px 3px 0 0;height:39px;">
					SEO Debug Information
					<?php
					if ( $this->is_post_edit() || $this->is_term_edit() ) :
						echo ' :: ';
						echo esc_html( 'Type: ' . $type );
						echo esc_html( $mdash . 'ID: ' . $id );
						echo esc_html( $mdash . 'Cache key: ' . $cache_key );
						echo esc_html( $mdash . 'Plugin version: ' . THE_SEO_FRAMEWORK_VERSION );
						echo esc_html( $mdash . 'Plugin DB version: c' . get_option( 'the_seo_framework_upgraded_db_version' ) . ' | e' . THE_SEO_FRAMEWORK_DB_VERSION );
					endif;
					?>
				</h3>
				<div style="position:absolute;bottom:0;right:0;left:0;top:39px;margin:0;padding:0;background:#fff;border-radius:3px;overflow-x:hidden;z-index:9001">
					<?php
					The_SEO_Framework\Debug::_output_debug_header();
					The_SEO_Framework\Debug::_output_debug_query();
					The_SEO_Framework\Debug::_output_debug();
					?>
				</div>
			</div>
			<?php
		} else {
			?>
			<style type="text/css">.wp-ui-notification{color:#fff;background-color:#d54e21}.code.highlight{font-family:Consolas,Monaco,monospace;font-size:14px;}.theseoframework-debug h3{font-size:18px;margin:18px 0}</style>
			<div class="theseoframework-debug" style="color:#444;font-family:Georgio,sans-serif;font-size:14px;clear:both;float:left;position:relative;width:calc( 100% - 80px );min-height:700px;padding:0;margin:40px;overflow:hidden;border:1px solid #ccc;border-radius:3px;line-height:18px">
				<h3 style="font-size:14px;padding:0 12px;margin:0;line-height:39px;border-bottom:2px solid #aaa;position:absolute;z-index:9002;width:100%;right:0;left:0;top:0;background:#fff;border-radius:3px 3px 0 0;height:39px">
					SEO Debug Information
					<?php
					echo ' :: ';
					echo 'Type: ' . esc_html( $type );
					echo esc_html( $mdash . 'ID: ' . $id );
					echo esc_html( $mdash . 'Cache key: ' . $cache_key );
					echo esc_html( $mdash . 'Plugin version: ' . THE_SEO_FRAMEWORK_VERSION );
					echo esc_html( $mdash . 'Plugin DB version: c' . get_option( 'the_seo_framework_upgraded_db_version' ) . ' | e' . THE_SEO_FRAMEWORK_DB_VERSION );
					?>
				</h3>
				<div style="position:absolute;bottom:0;right:0;left:0;top:39px;margin:0;padding:0;background:#fff;border-radius:3px;overflow-x:hidden;z-index:9001">
					<?php
					The_SEO_Framework\Debug::_output_debug_header();
					?>
					<div style="width:50%;float:left;">
						<?php
						The_SEO_Framework\Debug::_output_debug_query_from_cache();
						?>
					</div><div style="width:50%;float:right;">
						<?php
						The_SEO_Framework\Debug::_output_debug_query();
						?>
					</div>
					<?php
					The_SEO_Framework\Debug::_output_debug();
					?>
				</div>
			</div>
			<?php
		}
	endif;
endif;
