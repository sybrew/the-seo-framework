<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use The_SEO_Framework\Bridges\SeoSettings,
	The_SEO_Framework\Interpreters\HTML,
	The_SEO_Framework\Interpreters\Form,
	The_SEO_Framework\Interpreters\Settings_Input as Input;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

switch ( $this->get_view_instance( 'sitemaps', $instance ) ) :
	case 'sitemaps_main':
		$_settings_class = SeoSettings::class;

		$tabs = [
			'general'  => [
				'name'     => __( 'General', 'autodescription' ),
				'callback' => [ $_settings_class, '_sitemaps_metabox_general_tab' ],
				'dashicon' => 'admin-generic',
			],
			'robots'   => [
				'name'     => 'Robots.txt',
				'callback' => [ $_settings_class, '_sitemaps_metabox_robots_tab' ],
				'dashicon' => 'share-alt2',
			],
			'metadata' => [
				'name'     => __( 'Metadata', 'autodescription' ),
				'callback' => [ $_settings_class, '_sitemaps_metabox_metadata_tab' ],
				'dashicon' => 'index-card',
			],
			'notify'   => [
				'name'     => _x( 'Ping', 'Ping or notify search engine', 'autodescription' ),
				'callback' => [ $_settings_class, '_sitemaps_metabox_notify_tab' ],
				'dashicon' => 'megaphone',
			],
			'style'    => [
				'name'     => __( 'Style', 'autodescription' ),
				'callback' => [ $_settings_class, '_sitemaps_metabox_style_tab' ],
				'dashicon' => 'art',
			],
		];

		SeoSettings::_nav_tab_wrapper(
			'sitemaps',
			/**
			 * @since 2.6.0
			 * @param array $tabs The default tabs.
			 */
			(array) apply_filters( 'the_seo_framework_sitemaps_settings_tabs', $tabs )
		);
		break;

	case 'sitemaps_general_tab':
		$has_sitemap_plugin = $this->detect_sitemap_plugin();
		$sitemap_detected   = $this->has_sitemap_xml();

		HTML::header_title( __( 'Sitemap Integration Settings', 'autodescription' ) );
		HTML::description( __( 'The sitemap is an XML file that lists indexable pages of your website along with optional metadata. It helps search engines find new and updated content quickly.', 'autodescription' ) );

		HTML::description_noesc(
			$this->convert_markdown(
				sprintf(
					/* translators: %s = Learn more URL. Markdown! */
					esc_html__( 'The sitemap does not contribute to ranking; [it can only help with indexing](%s). Search engines process smaller, less complicated sitemaps quicker, which shortens the time required for indexing pages.', 'autodescription' ),
					'https://kb.theseoframework.com/?p=119'
				),
				[ 'a' ],
				[ 'a_internal' => false ]
			)
		);

		if ( $has_sitemap_plugin ) :
			echo '<hr>';
			HTML::attention_description( __( 'Note: Another active sitemap plugin has been detected. This means that the sitemap functionality has been superseded and these settings have no effect.', 'autodescription' ) );
		elseif ( $sitemap_detected ) :
			echo '<hr>';
			HTML::attention_description( __( 'Note: A sitemap has been detected in the root folder of your website. This means that these settings have no effect.', 'autodescription' ) );
		endif;
		?>
		<hr>
		<?php
		HTML::header_title( __( 'Sitemap Output', 'autodescription' ) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'sitemaps_output',
				'label'  => esc_html__( 'Output optimized sitemap?', 'autodescription' )
					. ' ' . HTML::make_info(
						__( 'This sitemap is processed quicker by search engines.', 'autodescription' ),
						'',
						false
					),
				'escape' => false,
			] ),
			true
		);

		if ( ! $has_sitemap_plugin && ! $sitemap_detected ) {
			if ( $this->get_option( 'sitemaps_output' ) ) {
				HTML::description_noesc(
					sprintf(
						'<a href="%s" target=_blank rel=noopener>%s</a>',
						esc_url( The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url(), [ 'https', 'http' ] ),
						esc_html__( 'View the base sitemap.', 'autodescription' )
					)
				);
				// TODO In settings generator (TSF 5.0): Overwite this section for Polylang/WPML and output each sitemap language link respectively.
				// TODO Also add a link telling where why it may not work consistently ('try opening in another browser, incognito, etc.')
			} elseif ( $this->use_core_sitemaps() ) {
				$_index_url = get_sitemap_url( 'index' );
				if ( $_index_url )
					HTML::description_noesc(
						sprintf(
							'<a href="%s" target=_blank rel=noopener>%s</a>',
							esc_url( $_index_url, [ 'https', 'http' ] ),
							esc_html__( 'View the sitemap index.', 'autodescription' )
						)
					);
			}

			/**
			 * @since 4.2.0
			 * @param bool $tell Whether to tell that there's a plugin active that can use multiple sitemaps.
			 */
			if ( apply_filters( 'the_seo_framework_tell_multilingual_sitemap', false ) ) {
				HTML::description_noesc(
					// Markdown escapes.
					$this->convert_markdown(
						sprintf(
							/* translators: %s = Documentation URL in markdown */
							esc_html__( 'A multilingual plugin has been detected, so your site may have multiple sitemaps. [Learn more](%s).', 'autodescription' ),
							'https://kb.theseoframework.com/?p=104#same-site-sitemaps'
						),
						[ 'a' ],
						[ 'a_internal' => false ] // opens in new tab.
					)
				);
			}
		}
		?>
		<hr>

		<p>
			<label for="<?php Input::field_id( 'sitemap_query_limit' ); ?>">
				<strong><?php esc_html_e( 'Sitemap Query Limit', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		HTML::description( __( 'This setting affects how many pages are requested from the database per query.', 'autodescription' ) );

		?>
		<p>
			<input type=number min=1 max=50000 name="<?php Input::field_name( 'sitemap_query_limit' ); ?>" id="<?php Input::field_id( 'sitemap_query_limit' ); ?>" placeholder="<?= absint( $this->get_default_option( 'sitemap_query_limit' ) ) ?>" value="<?= absint( $this->get_option( 'sitemap_query_limit' ) ) ?>" />
		</p>
		<?php
		HTML::description( __( 'Consider lowering this value when the sitemap shows a white screen or notifies you of memory exhaustion.', 'autodescription' ) );
		?>
		<hr>
		<?php
		HTML::header_title( __( 'Transient Cache Settings', 'autodescription' ) );
		HTML::description( __( 'To improve performance, generated output can be stored in the database as transient cache.', 'autodescription' ) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'cache_sitemap',
				'label'  => esc_html__( 'Enable optimized sitemap generation cache?', 'autodescription' )
					. ' ' . HTML::make_info( __( 'Generating the sitemap can use a lot of server resources.', 'autodescription' ), '', false ),
				'escape' => false,
			] ),
			true
		);
		break;

	case 'sitemaps_robots_tab':
		$show_settings = true;
		$robots_url    = $this->get_robots_txt_url();

		HTML::header_title( __( 'Robots.txt Settings', 'autodescription' ) );

		if ( $this->has_robots_txt() ) :
			HTML::attention_description(
				__( 'Note: A robots.txt file has been detected in the root folder of your website. This means these settings have no effect.', 'autodescription' )
			);
			echo '<hr>';
		elseif ( ! $robots_url ) :
			if ( $this->is_subdirectory_installation() ) {
				HTML::attention_description(
					__( "Note: robots.txt files can't be generated or used on subdirectory installations.", 'autodescription' )
				);
				echo '<hr>';
			} elseif ( ! $this->pretty_permalinks ) {
				HTML::attention_description(
					__( "Note: You're using the plain permalink structure; so, no robots.txt file can be generated.", 'autodescription' )
				);
				HTML::description_noesc(
					$this->convert_markdown(
						sprintf(
							/* translators: 1 = Link to settings, Markdown. 2 = example input, also markdown! Preserve the Markdown as-is! */
							esc_html__( 'Change your [Permalink Settings](%1$s). Recommended structure: `%2$s`.', 'autodescription' ),
							esc_url( admin_url( 'options-permalink.php' ), [ 'https', 'http' ] ),
							'/%category%/%postname%/'
						),
						[ 'code', 'a' ],
						[ 'a_internal' => false ] // open in new window.
					)
				);
				echo '<hr>';
			}
		endif;

		HTML::description( __( 'The robots.txt output is the first thing search engines look for before crawling your site. If you add the sitemap location in that output, then search engines may automatically access and index the sitemap.', 'autodescription' ) );
		HTML::description( __( 'If you do not add the sitemap location to the robots.txt output, you should notify search engines manually through webmaster-interfaces provided by the search engines.', 'autodescription' ) );

		echo '<hr>';

		if ( $show_settings ) :
			HTML::header_title( __( 'Sitemap Hinting', 'autodescription' ) );
			HTML::wrap_fields(
				Input::make_checkbox( [
					'id'    => 'sitemaps_robots',
					'label' => __( 'Add sitemap location to robots.txt?', 'autodescription' ),
				] ),
				true
			);
		endif;

		$robots_url = $this->get_robots_txt_url();

		if ( $robots_url ) {
			HTML::description_noesc(
				sprintf(
					'<a href="%s" target=_blank rel=noopener>%s</a>',
					esc_url( $robots_url, [ 'https', 'http' ] ),
					esc_html__( 'View the robots.txt output.', 'autodescription' )
				)
			);
		}
		break;

	case 'sitemaps_metadata_tab':
		HTML::header_title( __( 'Timestamps Settings', 'autodescription' ) );
		HTML::description( __( 'The modified time suggests to search engines where to look for content changes first.', 'autodescription' ) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'sitemaps_modified',
				'label'  => $this->convert_markdown(
					/* translators: the backticks are Markdown! Preserve them as-is! */
					esc_html__( 'Add `<lastmod>` to the sitemap?', 'autodescription' ),
					[ 'code' ]
				),
				'escape' => false,
			] ),
			true
		);
		break;

	case 'sitemaps_notify_tab':
		HTML::header_title( __( 'Ping Settings', 'autodescription' ) );
		HTML::description( __( 'Notifying search engines of a sitemap change is helpful to get your content indexed as soon as possible.', 'autodescription' ) );
		HTML::description( __( 'By default this will happen at most once an hour.', 'autodescription' ) );

		HTML::wrap_fields(
			[
				Input::make_checkbox( [
					'id'     => 'ping_use_cron',
					'label'  => esc_html__( 'Use cron for pinging?', 'autodescription' )
						. ' ' . HTML::make_info(
							__( 'This speeds up post and term saving processes, by offsetting pinging to a later time.', 'autodescription' ),
							'',
							false
						),
					'escape' => false,
				] ),
				Input::make_checkbox( [
					'id'          => 'ping_use_cron_prerender',
					'label'       => esc_html__( 'Prerender optimized sitemap before pinging via cron?', 'autodescription' )
						. ' ' . HTML::make_info(
							__( 'This mitigates timeouts some search engines may experience when waiting for the sitemap to render. Transient caching for the sitemap must be enabled for this to work.', 'autodescription' ),
							'',
							false
						),
					'description' => esc_html__( 'Only enable prerendering when generating the sitemap takes over 60 seconds.', 'autodescription' ),
					'escape'      => false,
				] ),
			],
			true
		);

		?>
		<hr>
		<?php
		HTML::header_title( __( 'Notify Search Engines', 'autodescription' ) );

		$engines = [
			'ping_google' => 'Google',
			'ping_bing'   => 'Bing',
		];

		$ping_checkbox = '';

		foreach ( $engines as $option => $engine ) {
			/* translators: %s = Google */
			$ping_label     = sprintf( __( 'Notify %s about sitemap changes?', 'autodescription' ), $engine );
			$ping_checkbox .= Input::make_checkbox( [
				'id'    => $option,
				'label' => $ping_label,
			] );
		}

		HTML::wrap_fields( $ping_checkbox, true );
		break;

	case 'sitemaps_style_tab':
		HTML::header_title( __( 'Optimized Sitemap Styling Settings', 'autodescription' ) );
		HTML::description( __( 'You can style the optimized sitemap to give it a more personal look for your visitors. Search engines do not use these styles.', 'autodescription' ) );
		HTML::description( __( 'Note: Changes may not appear to have an effect directly because the stylesheet is cached in the browser for 30 minutes.', 'autodescription' ) );
		?>
		<hr>
		<?php
		HTML::header_title( __( 'Enable Styling', 'autodescription' ) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'sitemap_styles',
				'label'  => esc_html__( 'Style sitemap?', 'autodescription' ) . ' ' . HTML::make_info( __( 'This makes the sitemap more readable for humans.', 'autodescription' ), '', false ),
				'escape' => false,
			] ),
			true
		);

		?>
		<hr>
		<?php

		$current_colors = $this->get_sitemap_colors();
		$default_colors = $this->get_sitemap_colors( true );

		?>
		<p>
			<label for="<?php Input::field_id( 'sitemap_color_main' ); ?>">
				<strong><?php esc_html_e( 'Sitemap Header Background Color', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p>
			<input type=text name="<?php Input::field_name( 'sitemap_color_main' ); ?>" class=tsf-color-picker id="<?php Input::field_id( 'sitemap_color_main' ); ?>" placeholder="<?= esc_attr( $default_colors['main'] ) ?>" value="<?= esc_attr( $current_colors['main'] ) ?>" data-tsf-default-color="<?= esc_attr( $default_colors['main'] ) ?>" />
		</p>

		<p>
			<label for="<?php Input::field_id( 'sitemap_color_accent' ); ?>">
				<strong><?php esc_html_e( 'Sitemap Title and Lines Color', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p>
			<input type=text name="<?php Input::field_name( 'sitemap_color_accent' ); ?>" class=tsf-color-picker id="<?php Input::field_id( 'sitemap_color_accent' ); ?>" placeholder="<?= esc_attr( $default_colors['accent'] ) ?>" value="<?= esc_attr( $current_colors['accent'] ) ?>" data-tsf-default-color="<?= esc_attr( $default_colors['accent'] ) ?>" />
		</p>

		<hr>
		<?php
		HTML::header_title( __( 'Header Title Logo', 'autodescription' ) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'    => 'sitemap_logo',
				'label' => __( 'Show logo next to sitemap header title?', 'autodescription' ),
			] ),
			true
		);

		$ph_id  = get_theme_mod( 'custom_logo' ) ?: get_option( 'site_icon' ) ?: 0;
		$ph_src = $ph_id ? wp_get_attachment_image_src( $ph_id, [ 29, 29 ] ) : []; // TODO magic number "SITEMAP_LOGO_PX"

		$logo_placeholder = ! empty( $ph_src[0] ) ? $ph_src[0] : '';
		?>

		<p>
			<label for=sitemap_logo-url>
				<strong><?php esc_html_e( 'Logo URL', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p class="hide-if-tsf-js attention"><?php esc_html_e( 'Setting a logo requires JavaScript.', 'autodescription' ); ?></p>
		<p>
			<input class=large-text type=url readonly data-readonly=1 name="<?php Input::field_name( 'sitemap_logo_url' ); ?>" id=sitemap_logo-url placeholder="<?= esc_url( $logo_placeholder ) ?>" value="<?= esc_url( $this->get_option( 'sitemap_logo_url' ) ) ?>" />
			<input type=hidden name="<?php Input::field_name( 'sitemap_logo_id' ); ?>" id=sitemap_logo-id value="<?= absint( $this->get_option( 'sitemap_logo_id' ) ) ?>" />
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
					'button_text'  => __( 'Select Logo', 'autodescription' ),
				],
			] );
			?>
		</p>
		<?php
		break;

	default:
		break;
endswitch;
