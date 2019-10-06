<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

use The_SEO_Framework\Bridges\SeoSettings;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_sitemaps_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_sitemaps_metabox_main':
		$default_tabs = [
			'general'  => [
				'name'     => __( 'General', 'autodescription' ),
				'callback' => SeoSettings::class . '::_sitemaps_metabox_general_tab',
				'dashicon' => 'admin-generic',
			],
			'robots'   => [
				'name'     => 'Robots.txt',
				'callback' => SeoSettings::class . '::_sitemaps_metabox_robots_tab',
				'dashicon' => 'share-alt2',
			],
			'metadata' => [
				'name'     => __( 'Metadata', 'autodescription' ),
				'callback' => SeoSettings::class . '::_sitemaps_metabox_metadata_tab',
				'dashicon' => 'index-card',
			],
			'notify'   => [
				'name'     => _x( 'Ping', 'Ping or notify search engine', 'autodescription' ),
				'callback' => SeoSettings::class . '::_sitemaps_metabox_notify_tab',
				'dashicon' => 'megaphone',
			],
			'style'    => [
				'name'     => __( 'Style', 'autodescription' ),
				'callback' => SeoSettings::class . '::_sitemaps_metabox_style_tab',
				'dashicon' => 'art',
			],
		];

		/**
		 * @param array $defaults The default tabs.
		 * @param array $args     The args added on the callback.
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_sitemaps_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		SeoSettings::_nav_tab_wrapper( 'sitemaps', $tabs );
		break;

	case 'the_seo_framework_sitemaps_metabox_general':
		$sitemap_url        = \The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url();
		$has_sitemap_plugin = $this->detect_sitemap_plugin();
		$sitemap_detected   = $this->has_sitemap_xml();

		?>
		<h4><?php esc_html_e( 'Sitemap Integration Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'The sitemap is an XML file that lists indexable pages of your website along with optional metadata. This helps search engines find new and updated content more quickly.', 'autodescription' ) );

		$this->description( __( 'The sitemap does not contribute to ranking, only indexing. Therefore, it is perfectly fine not having every indexable page in the sitemap.', 'autodescription' ) );

		if ( $has_sitemap_plugin ) :
			$this->attention_description( __( 'Note: Another active sitemap plugin has been detected. This means that the sitemap functionality has been superseded and these settings have no effect.', 'autodescription' ) );
			echo '<hr>';
		elseif ( $sitemap_detected ) :
			$this->attention_description( __( 'Note: A sitemap has been detected in the root folder of your website. This means that these settings have no effect.', 'autodescription' ) );
			echo '<hr>';
		endif;
		?>
		<hr>

		<h4><?php esc_html_e( 'Sitemap Output', 'autodescription' ); ?></h4>
		<?php

		//* Echo checkbox.
		$this->wrap_fields(
			$this->make_checkbox(
				'sitemaps_output',
				__( 'Output sitemap?', 'autodescription' ),
				'',
				true
			),
			true
		);

		if ( ! $has_sitemap_plugin && ( $this->get_option( 'sitemaps_output' ) || ( $sitemap_detected && $this->pretty_permalinks ) ) ) {
			$this->description_noesc(
				sprintf(
					'<a href="%s" target=_blank rel=noopener>%s</a>',
					esc_url( \The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url(), [ 'https', 'http' ] ),
					esc_html__( 'View the base sitemap.', 'autodescription' )
				)
			);
		}

		?>
		<hr>

		<p>
			<label for="<?php $this->field_id( 'sitemap_query_limit' ); ?>">
				<strong><?php esc_html_e( 'Sitemap Query Limit', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		$this->description( __( 'The sitemap is generated with two queries: Hierarchical and non-hierarchical post types. This setting affects how many posts are requested from the database per query. The homepage and blog page are included separately.', 'autodescription' ) );

		if ( \has_filter( 'the_seo_framework_sitemap_post_limit' ) ) :
			?>
			<input type=hidden name="<?php $this->field_name( 'sitemap_query_limit' ); ?>" value="<?php echo absint( $this->get_sitemap_post_limit() ); ?>">
			<p>
				<input type="number" id="<?php $this->field_id( 'sitemap_query_limit' ); ?>" value="<?php echo absint( $this->get_sitemap_post_limit() ); ?>" disabled />
			</p>
			<?php
		else :
			?>
			<p>
				<input type="number" min=1 max=50000 name="<?php $this->field_name( 'sitemap_query_limit' ); ?>" id="<?php $this->field_id( 'sitemap_query_limit' ); ?>" placeholder="<?php echo absint( $this->get_default_option( 'sitemap_query_limit' ) ); ?>" value="<?php echo absint( $this->get_option( 'sitemap_query_limit' ) ); ?>" />
			</p>
			<?php
		endif;
		$this->description( __( 'Consider lowering this value when the sitemap shows a white screen or notifies you of memory exhaustion.', 'autodescription' ) );
		break;

	case 'the_seo_framework_sitemaps_metabox_robots':
		$show_settings = true;
		$robots_url    = $this->get_robots_txt_url();

		?>
		<h4><?php esc_html_e( 'Robots.txt Settings', 'autodescription' ); ?></h4>
		<?php

		if ( $this->has_robots_txt() ) :
			$this->attention_description(
				__( 'Note: A robots.txt file has been detected in the root folder of your website. This means these settings have no effect.', 'autodescription' )
			);
			echo '<hr>';
		elseif ( ! $robots_url ) :
			if ( $this->is_subdirectory_installation() ) {
				$this->attention_description(
					__( "Note: robots.txt files can't be generated or used on subdirectory installations.", 'autodescription' )
				);
				echo '<hr>';
			} elseif ( ! $this->pretty_permalinks ) {
				$this->attention_description(
					__( "Note: You're using the plain permalink structure; so, no robots.txt file can be generated.", 'autodescription' )
				);
				$this->description_noesc(
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

		$this->description( __( 'The robots.txt output is the first thing search engines look for before crawling your site. If you add the sitemap location in that output, then search engines may automatically access and index the sitemap.', 'autodescription' ) );
		$this->description( __( 'If you do not add the sitemap location to the robots.txt output, you should notify search engines manually through webmaster-interfaces provided by the search engines.', 'autodescription' ) );

		echo '<hr>';

		if ( $show_settings ) :
			printf(
				'<h4>%s</h4>',
				esc_html__( 'Sitemap Hinting', 'autodescription' )
			);
			$this->wrap_fields(
				$this->make_checkbox(
					'sitemaps_robots',
					esc_html__( 'Add sitemap location to robots.txt?', 'autodescription' ),
					esc_html__( 'This only works when the sitemap is active.', 'autodescription' ),
					false
				),
				true
			);
		endif;

		$robots_url = $this->get_robots_txt_url();

		if ( $robots_url ) {
			$this->description_noesc(
				sprintf(
					'<a href="%s" target=_blank rel=noopener>%s</a>',
					esc_url( $robots_url, [ 'https', 'http' ] ),
					esc_html__( 'View the robots.txt output.', 'autodescription' )
				)
			);
		}
		break;

	case 'the_seo_framework_sitemaps_metabox_metadata':
		?>
		<h4><?php esc_html_e( 'Timestamps Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'The modified time suggests to search engines where to look for content changes first.', 'autodescription' ) );

		//* Echo checkbox.
		$this->wrap_fields(
			$this->make_checkbox(
				'sitemaps_modified',
				$this->convert_markdown(
					/* translators: the backticks are Markdown! Preserve them as-is! */
					esc_html__( 'Add `<lastmod>` to the sitemap?', 'autodescription' ),
					[ 'code' ]
				),
				'',
				false
			),
			true
		);

		?>
		<hr>

		<h4><?php esc_html_e( 'Priority Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'The priority index suggests to search engines which pages are deemed more important. It has no known impact on the SEO value and it is generally ignored.', 'autodescription' ) );

		//* Echo checkbox.
		$this->wrap_fields(
			$this->make_checkbox(
				'sitemaps_priority',
				$this->convert_markdown(
					/* translators: the backticks are Markdown! Preserve them as-is! */
					esc_html__( 'Add `<priority>` to the sitemap?', 'autodescription' ),
					[ 'code' ]
				),
				'',
				false
			),
			true
		);
		break;

	case 'the_seo_framework_sitemaps_metabox_notify':
		?>
		<h4><?php esc_html_e( 'Ping Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Notifying search engines of a sitemap change is helpful to get your content indexed as soon as possible.', 'autodescription' ) );
		$this->description( __( 'By default this will happen at most once an hour.', 'autodescription' ) );

		$this->wrap_fields(
			$this->make_checkbox(
				'ping_use_cron',
				esc_html__( 'Use cron for pinging?', 'autodescription' )
					. ' ' . $this->make_info(
						__( 'This speeds up post and term saving processes, by offsetting pinging to a later time.', 'autodescription' ),
						'',
						false
					),
				'',
				false
			),
			true
		);

		?>
		<hr>

		<h4><?php esc_html_e( 'Notify Search Engines', 'autodescription' ); ?></h4>
		<?php

		$engines = [
			'ping_google' => 'Google',
			'ping_bing'   => 'Bing',
		];

		$ping_checkbox = '';

		foreach ( $engines as $option => $engine ) {
			/* translators: %s = Google */
			$ping_label     = sprintf( __( 'Notify %s about sitemap changes?', 'autodescription' ), $engine );
			$ping_checkbox .= $this->make_checkbox( $option, $ping_label, '', true );
		}

		//* Echo checkbox.
		$this->wrap_fields( $ping_checkbox, true );
		break;

	case 'the_seo_framework_sitemaps_metabox_style':
		?>
		<h4><?php esc_html_e( 'Sitemap Styling Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'You can style the sitemap to give it a more personal look for your visitors. Search engines do not use these styles.', 'autodescription' ) );
		$this->description( __( 'Note: Changes may not appear to have effect directly because the stylesheet is cached in the browser for 30 minutes.', 'autodescription' ) );
		?>
		<hr>

		<h4><?php esc_html_e( 'Enable styling', 'autodescription' ); ?></h4>
		<?php

		$this->wrap_fields(
			$this->make_checkbox(
				'sitemap_styles',
				esc_html__( 'Style Sitemap?', 'autodescription' ) . ' ' . $this->make_info( __( 'This makes the sitemap more readable for humans.', 'autodescription' ), '', false ),
				'',
				false
			),
			true
		);

		?>
		<hr>

		<h4><?php esc_html_e( 'Style configuration', 'autodescription' ); ?></h4>
		<?php

		$this->wrap_fields(
			$this->make_checkbox(
				'sitemap_logo',
				esc_html__( 'Add site logo?', 'autodescription' ) . ' ' . $this->make_info( __( 'The logo is set in Customizer.', 'autodescription' ), '', false ),
				'',
				false
			),
			true
		);

		$current_colors = $this->get_sitemap_colors();
		$default_colors = $this->get_sitemap_colors( true );

		?>
		<p>
			<label for="<?php $this->field_id( 'sitemap_color_main' ); ?>">
				<strong><?php esc_html_e( 'Sitemap header background color', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'sitemap_color_main' ); ?>" class="tsf-color-picker" id="<?php $this->field_id( 'sitemap_color_main' ); ?>" placeholder="<?php echo esc_attr( $default_colors['main'] ); ?>" value="<?php echo esc_attr( $current_colors['main'] ); ?>" data-tsf-default-color="<?php echo esc_attr( $default_colors['main'] ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'sitemap_color_accent' ); ?>">
				<strong><?php esc_html_e( 'Sitemap title and lines color', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'sitemap_color_accent' ); ?>" class="tsf-color-picker" id="<?php $this->field_id( 'sitemap_color_accent' ); ?>" placeholder="<?php echo esc_attr( $default_colors['accent'] ); ?>" value="<?php echo esc_attr( $current_colors['accent'] ); ?>" data-tsf-default-color="<?php echo esc_attr( $default_colors['accent'] ); ?>" />
		</p>
		<?php
		break;

	default:
		break;
endswitch;
