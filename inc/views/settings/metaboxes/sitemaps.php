<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

use \The_SEO_Framework\Admin\Settings\Layout\{
	Form,
	HTML,
	Input,
};
use \The_SEO_Framework\Helper\{
	Compatibility,
	Format\Markdown,
	Query,
};

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2016 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// See _sitemaps_metabox et al.
[ $instance ] = $view_args;

switch ( $instance ) :
	case 'main':
		$tabs = [
			'general'  => [
				'name'     => \__( 'General', 'autodescription' ),
				'callback' => [ Admin\Settings\Plugin::class, '_sitemaps_metabox_general_tab' ],
				'dashicon' => 'admin-generic',
			],
			'robots'   => [
				'name'     => 'Robots.txt',
				'callback' => [ Admin\Settings\Plugin::class, '_sitemaps_metabox_robots_tab' ],
				'dashicon' => 'share-alt2',
			],
			'metadata' => [
				'name'     => \__( 'Metadata', 'autodescription' ),
				'callback' => [ Admin\Settings\Plugin::class, '_sitemaps_metabox_metadata_tab' ],
				'dashicon' => 'index-card',
			],
			'style'    => [
				'name'     => \__( 'Style', 'autodescription' ),
				'callback' => [ Admin\Settings\Plugin::class, '_sitemaps_metabox_style_tab' ],
				'dashicon' => 'art',
			],
		];

		Admin\Settings\Plugin::nav_tab_wrapper(
			'sitemaps',
			/**
			 * @since 2.6.0
			 * @param array $tabs The default tabs.
			 */
			(array) \apply_filters( 'the_seo_framework_sitemaps_settings_tabs', $tabs )
		);
		break;

	case 'general':
		$has_sitemap_plugin = Compatibility::get_active_conflicting_plugin_types()['sitemaps'];
		$sitemap_detected   = Sitemap\Utils::has_root_sitemap_xml();

		HTML::header_title( \__( 'Sitemap Integration Settings', 'autodescription' ) );
		HTML::description( \__( 'The sitemap is an XML file that lists indexable pages of your website along with optional metadata. It helps search engines find new and updated content quickly.', 'autodescription' ) );

		HTML::description_noesc(
			Markdown::convert(
				\sprintf(
					/* translators: %s = Learn more URL. Markdown! */
					\esc_html__( 'The sitemap does not contribute to ranking; [it can only help with indexing](%s). Search engines process smaller, less complicated sitemaps quicker, which shortens the time required for indexing pages.', 'autodescription' ),
					'https://kb.theseoframework.com/?p=119',
				),
				[ 'a' ],
				[ 'a_internal' => false ],
			),
		);

		if ( $has_sitemap_plugin ) {
			echo '<hr>';
			HTML::attention_description( \__( 'Note: Another active sitemap plugin has been detected, so the sitemap functionality has been replaced and these settings have no effect.', 'autodescription' ) );
		} elseif ( $sitemap_detected ) {
			echo '<hr>';
			HTML::attention_description( \__( 'Note: A sitemap has been detected in the root folder of your website, so these settings have no effect.', 'autodescription' ) );
		}
		?>
		<hr>
		<?php
		HTML::header_title( \__( 'Sitemap Output', 'autodescription' ) );

		HTML::description( \__( 'Search engines crawl links to discover all pages and archives without requiring a sitemap. The optimized sitemap only includes the latest pages and no archives, so search engines can find and process vital changes quickly.', 'autodescription' ) );

		if ( ! $has_sitemap_plugin && ! $sitemap_detected ) {
			HTML::description( \__( 'Disable the optimized sitemap to use complex sitemaps that include all links at the cost of indexing speed.', 'autodescription' ) );
		}

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'sitemaps_output',
				'label'  => \esc_html__( 'Output optimized sitemap?', 'autodescription' )
					. ' ' . HTML::make_info(
						\__( 'This sitemap is processed quicker by search engines.', 'autodescription' ),
						'',
						false,
					),
				'escape' => false,
			] ),
			true,
		);

		if ( ! $has_sitemap_plugin && ! $sitemap_detected ) {
			// Note to self: Do not toggle this condition in JS. The user would get a 404 message if the options have yet to be saved.
			if ( Data\Plugin::get_option( 'sitemaps_output' ) ) {
				HTML::description_noesc( \sprintf(
					'<a href="%s" target=_blank rel=noopener>%s</a>',
					\esc_url( Sitemap\Registry::get_expected_sitemap_endpoint_url(), [ 'https', 'http' ] ),
					\esc_html__( 'View the base sitemap.', 'autodescription' ),
				) );
				// TODO In settings generator (TSF 5.0): Overwrite this section for Polylang/WPML and output each sitemap language link respectively.
				// TODO Also add a link telling where why it may not work consistently ('try opening in another browser, incognito, etc.')
			} elseif ( Sitemap\Utils::use_core_sitemaps() ) {
				$_index_url = \get_sitemap_url( 'index' );
				if ( $_index_url )
					HTML::description_noesc( \sprintf(
						'<a href="%s" target=_blank rel=noopener>%s</a>',
						\esc_url( $_index_url, [ 'https', 'http' ] ),
						\esc_html__( 'View the sitemap index.', 'autodescription' ),
					) );
			}

			if ( Compatibility::get_active_conflicting_plugin_types()['multilingual'] ) {
				HTML::attention_noesc(
					// Markdown escapes.
					Markdown::convert(
						\sprintf(
							/* translators: %s = Documentation URL in markdown */
							\esc_html__( 'A multilingual plugin has been detected, so your site may have multiple sitemaps. [Learn more](%s).', 'autodescription' ),
							'https://kb.theseoframework.com/?p=104#same-site-sitemaps',
						),
						[ 'a' ],
						[ 'a_internal' => false ] // opens in new tab.
					),
				);
			}
		}
		?>
		<hr>

		<p>
			<label for="<?php Input::field_id( 'sitemap_query_limit' ); ?>">
				<strong><?php \esc_html_e( 'Sitemap Query Limit', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		HTML::description( \__( 'This setting affects how many pages are requested from the database per query.', 'autodescription' ) );

		?>
		<p>
			<input type=number min=1 max=50000 name="<?php Input::field_name( 'sitemap_query_limit' ); ?>" id="<?php Input::field_id( 'sitemap_query_limit' ); ?>" placeholder="<?= \absint( Data\Plugin\Setup::get_default_option( 'sitemap_query_limit' ) ) ?>" value="<?= \absint( Data\Plugin::get_option( 'sitemap_query_limit' ) ) ?>">
		</p>
		<?php
		HTML::description( \__( 'Consider lowering this value when the sitemap shows a white screen or notifies you of memory exhaustion.', 'autodescription' ) );
		?>
		<div id=tsf-sitemap-transient-cache-settings>
			<hr>
			<?php
			HTML::header_title( \__( 'Transient Cache Settings', 'autodescription' ) );
			HTML::description( \__( 'To improve performance, generated output can be stored in the database as transient cache.', 'autodescription' ) );

			HTML::wrap_fields(
				Input::make_checkbox( [
					'id'     => 'cache_sitemap',
					'label'  => \esc_html__( 'Enable sitemap generation cache?', 'autodescription' )
						. ' ' . HTML::make_info( \__( 'Generating the sitemap can use a lot of server resources.', 'autodescription' ), '', false ),
					'escape' => false,
				] ),
				true,
			);

			?>
			<div id=tsf-sitemap-prerendering-settings>
				<?php
				HTML::wrap_fields(
					Input::make_checkbox( [
						'id'          => 'sitemap_cron_prerender',
						'label'       => \esc_html__( 'Prerender sitemap via cron?', 'autodescription' )
							. ' ' . HTML::make_info(
								\__( 'This mitigates timeouts search engines may experience when waiting for the sitemap to render.', 'autodescription' ),
								'',
								false,
							),
						'description' => \esc_html__( 'Only enable prerendering when the sitemap takes longer than 60 seconds to generate.', 'autodescription' ),
						'escape'      => false,
					] ),
					true,
				);
				?>
			</div>
		</div>
		<?php
		break;

	case 'robots':
		$robots_url = RobotsTXT\Utils::get_robots_txt_url();

		HTML::header_title( \__( 'Robots.txt Settings', 'autodescription' ) );

		HTML::description( \__( 'The robots.txt output is the first thing search engines look for before crawling your site. If you add the sitemap location in that output, then search engines may automatically access and index the sitemap.', 'autodescription' ) );
		HTML::description( \__( 'If you do not add the sitemap location to the robots.txt output, you should notify search engines manually through webmaster-interfaces provided by the search engines.', 'autodescription' ) );

		echo '<hr>';

		if ( RobotsTXT\Utils::has_root_robots_txt() ) {
			HTML::attention_description(
				\__( 'Note: A robots.txt file has been detected in the root folder of your website, so these settings have no effect.', 'autodescription' )
			);
			echo '<hr>';
		} elseif ( ! $robots_url ) {
			if ( Data\Blog::is_subdirectory_installation() ) {
				HTML::attention_description(
					\__( 'Note: This site is installed in a subdirectory, so robots.txt files cannot be generated or used.', 'autodescription' )
				);
				echo '<hr>';
			} elseif ( ! Query\Utils::using_pretty_permalinks() ) {
				HTML::attention_description(
					\__( "Note: You're using the plain permalink structure; so, no robots.txt file can be generated.", 'autodescription' )
				);
				HTML::description_noesc(
					Markdown::convert(
						\sprintf(
							/* translators: 1 = Link to settings, Markdown. 2 = example input, also markdown! Preserve the Markdown as-is! */
							\esc_html__( 'Change your [Permalink Settings](%1$s). Recommended structure: `%2$s`.', 'autodescription' ),
							\esc_url( \admin_url( 'options-permalink.php' ), [ 'https', 'http' ] ),
							'/%category%/%postname%/',
						),
						[ 'code', 'a' ],
						[ 'a_internal' => false ], // open in new window.
					)
				);
				echo '<hr>';
			}
		}

		HTML::header_title( \__( 'Sitemap Hinting', 'autodescription' ) );
		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'    => 'sitemaps_robots',
				'label' => \__( 'Add sitemap location to robots.txt?', 'autodescription' ),
			] ),
			true,
		);

		if ( $robots_url ) {
			HTML::description_noesc( \sprintf(
				'<a href="%s" target=_blank rel=noopener>%s</a>',
				\esc_url( $robots_url, [ 'https', 'http' ] ),
				\esc_html__( 'View the robots.txt output.', 'autodescription' ),
			) );
		}
		break;

	case 'metadata':
		HTML::header_title( \__( 'Timestamps Settings', 'autodescription' ) );
		HTML::description_noesc( Markdown::convert(
			/* translators: the backticks are Markdown! Preserve them as-is! */
			\esc_html__( 'The `<lastmod>` tag shows the last updated time of a page. It helps search engines to quickly find content changes via the sitemap.', 'autodescription' )
		) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'sitemaps_modified',
				'label'  => Markdown::convert(
					/* translators: the backticks are Markdown! Preserve them as-is! */
					\esc_html__( 'Add `<lastmod>` to the sitemap?', 'autodescription' ),
					[ 'code' ],
				),
				'escape' => false,
			] ),
			true,
		);
		break;

	case 'style':
		HTML::header_title( \__( 'Sitemap Styling Settings', 'autodescription' ) );
		HTML::description( \__( 'You can style the sitemap to give it a more personal look for your visitors. Search engines do not use these styles.', 'autodescription' ) );
		HTML::description( \__( 'Note: Changes may not appear to have an effect directly because the stylesheet is cached in the browser for 30 minutes.', 'autodescription' ) );
		?>
		<hr>
		<?php
		HTML::header_title( \__( 'Enable Styling', 'autodescription' ) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'sitemap_styles',
				'label'  => \esc_html__( 'Style sitemap?', 'autodescription' ) . ' ' . HTML::make_info( \__( 'This makes the sitemap more readable for humans.', 'autodescription' ), '', false ),
				'escape' => false,
			] ),
			true,
		);

		?>
		<hr>
		<?php

		$current_colors = Sitemap\Utils::get_sitemap_colors();
		$default_colors = Sitemap\Utils::get_sitemap_colors( true );

		?>
		<p>
			<label for="<?php Input::field_id( 'sitemap_color_main' ); ?>">
				<strong><?php \esc_html_e( 'Sitemap Header Background Color', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p>
			<input type=text name="<?php Input::field_name( 'sitemap_color_main' ); ?>" class=tsf-color-picker id="<?php Input::field_id( 'sitemap_color_main' ); ?>" placeholder="<?= \esc_attr( $default_colors['main'] ) ?>" value="<?= \esc_attr( $current_colors['main'] ) ?>" data-tsf-default-color="<?= \esc_attr( $default_colors['main'] ) ?>">
		</p>

		<p>
			<label for="<?php Input::field_id( 'sitemap_color_accent' ); ?>">
				<strong><?php \esc_html_e( 'Sitemap Title and Lines Color', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p>
			<input type=text name="<?php Input::field_name( 'sitemap_color_accent' ); ?>" class=tsf-color-picker id="<?php Input::field_id( 'sitemap_color_accent' ); ?>" placeholder="<?= \esc_attr( $default_colors['accent'] ) ?>" value="<?= \esc_attr( $current_colors['accent'] ) ?>" data-tsf-default-color="<?= \esc_attr( $default_colors['accent'] ) ?>">
		</p>

		<hr>
		<?php
		HTML::header_title( \__( 'Header Title Logo', 'autodescription' ) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'    => 'sitemap_logo',
				'label' => \__( 'Show logo next to sitemap header title?', 'autodescription' ),
			] ),
			true,
		);

		$ph_id  = \get_theme_mod( 'custom_logo' ) ?: \get_option( 'site_icon' ) ?: 0;
		$ph_src = $ph_id ? \wp_get_attachment_image_src( $ph_id, [ 29, 29 ] ) : []; // TODO magic number "SITEMAP_LOGO_PX"

		$logo_placeholder = ! empty( $ph_src[0] ) ? $ph_src[0] : '';
		?>

		<p>
			<label for=sitemap_logo-url>
				<strong><?php \esc_html_e( 'Logo URL', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p class="hide-if-tsf-js attention"><?php \esc_html_e( 'Setting a logo requires JavaScript.', 'autodescription' ); ?></p>
		<p>
			<input class=large-text type=url readonly data-readonly=1 name="<?php Input::field_name( 'sitemap_logo_url' ); ?>" id=sitemap_logo-url placeholder="<?= \esc_url( $logo_placeholder ) ?>" value="<?= \esc_url( Data\Plugin::get_option( 'sitemap_logo_url' ) ) ?>">
			<input type=hidden name="<?php Input::field_name( 'sitemap_logo_id' ); ?>" id=sitemap_logo-id value="<?= \absint( Data\Plugin::get_option( 'sitemap_logo_id' ) ) ?>">
		</p>
		<p class=hide-if-no-tsf-js>
			<?php
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
			echo Form::get_image_uploader_form( [
				'id'   => 'sitemap_logo',
				'data' => [
					'inputType' => 'logo',
					'width'     => 512, // Magic number "CUSTOMIZER_LOGO_MAX" (should be defined in WP?)
					'height'    => 512, // Magic number
					'minWidth'  => 64, // Magic number "CUSTOMIZER_LOGO_MIN" (should be defined in WP?)
					'minHeight' => 64, // Magic number
					'flex'      => true,
				],
				'i18n' => [
					'button_title' => '',
					'button_text'  => \__( 'Select Logo', 'autodescription' ),
				],
			] );
			?>
		</p>
		<?php
endswitch;
