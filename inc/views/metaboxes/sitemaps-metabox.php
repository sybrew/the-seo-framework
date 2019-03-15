<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Views\Metaboxes
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_sitemaps_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_sitemaps_metabox_main':
		/**
		 * Parse tabs content
		 *
		 * @param array $default_tabs {
		 *    'id' = The identifier => array(
		 *       'name'     => The name
		 *       'callback' => The callback function, use array for method calling
		 *       'dashicon' => Desired dashicon
		 *    )
		 * }
		 *
		 * @since 2.2.9
		 */
		$default_tabs = [
			'general' => [
				'name'     => __( 'General', 'autodescription' ),
				'callback' => [ $this, 'sitemaps_metabox_general_tab' ],
				'dashicon' => 'admin-generic',
			],
			'robots' => [
				'name'     => 'Robots.txt',
				'callback' => [ $this, 'sitemaps_metabox_robots_tab' ],
				'dashicon' => 'share-alt2',
			],
			'metadata' => [
				'name'     => __( 'Metadata', 'autodescription' ),
				'callback' => [ $this, 'sitemaps_metabox_metadata_tab' ],
				'dashicon' => 'index-card',
			],
			'notify' => [
				'name'     => _x( 'Ping', 'Ping or notify search engine', 'autodescription' ),
				'callback' => [ $this, 'sitemaps_metabox_notify_tab' ],
				'dashicon' => 'megaphone',
			],
			'style' => [
				'name'     => __( 'Style', 'autodescription' ),
				'callback' => [ $this, 'sitemaps_metabox_style_tab' ],
				'dashicon' => 'art',
			],
		];

		/**
		 * @param array $defaults The default tabs.
		 * @param array $args     The args added on the callback.
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_sitemaps_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'sitemaps', $tabs, '2.2.8' );
		break;

	case 'the_seo_framework_sitemaps_metabox_general':
		$sitemap_url = $this->get_sitemap_xml_url();
		$has_sitemap_plugin = $this->detect_sitemap_plugin();
		$sitemap_detected = $this->has_sitemap_xml();

		?>
		<h4><?php esc_html_e( 'Sitemap Integration Settings', 'autodescription' ); ?></h4>
		<?php

		if ( $has_sitemap_plugin ) :
			$this->attention_description( __( 'Note: Another active sitemap plugin has been detected. This means that the sitemap functionality has been superseded and these settings have no effect.', 'autodescription' ) );
			echo '<hr>';
		elseif ( $sitemap_detected ) :
			$this->attention_description( __( 'Note: A sitemap has been detected in the root folder of your website. This means that these settings have no effect.', 'autodescription' ) );
			echo '<hr>';
		endif;

		$this->description( __( 'The sitemap is an XML file that lists posts from your website along with additional metadata. This helps search engines crawl your website more easily.', 'autodescription' ) );
		?>
		<hr>

		<h4><?php esc_html_e( 'Sitemap Output', 'autodescription' ); ?></h4>
		<?php

		//* Echo checkbox.
		$this->wrap_fields(
			$this->make_checkbox(
				'sitemaps_output',
				__( 'Output Sitemap?', 'autodescription' ),
				'',
				true
			), true
		);

		if ( ! $has_sitemap_plugin && ( $this->get_option( 'sitemaps_output' ) || $sitemap_detected ) ) {
			$here = '<a href="' . esc_url( $sitemap_url, [ 'http', 'https' ] ) . '" target="_blank" title="' . esc_attr__( 'View sitemap', 'autodescription' ) . '">' . esc_attr_x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';
			/* translators: %s = here */
			$this->description_noesc( sprintf( esc_html__( 'The sitemap can be found %s.', 'autodescription' ), $here ) );
		}

		?>
		<hr>

		<p>
			<label for="<?php $this->field_id( 'sitemap_query_limit' ); ?>">
				<strong><?php esc_html_e( 'Sitemap Query Limit', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		$this->description( __( 'The sitemap is generated with three queries: Pages, posts, and other post types. This setting affects how many posts are requested from the database per query. The homepage and blog page are included separately.', 'autodescription' ) );

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
				<input type="number" min=1 max=50000 name="<?php $this->field_name( 'sitemap_query_limit' ); ?>" id="<?php $this->field_id( 'sitemap_query_limit' ); ?>" placeholder="<?php echo $this->get_default_option( 'sitemap_query_limit' ); ?>" value="<?php echo absint( $this->get_option( 'sitemap_query_limit' ) ); ?>" />
			</p>
			<?php
		endif;
		$this->description( __( 'Consider lowering this value when the sitemap shows a white screen or notifies you of memory exhaustion.', 'autodescription' ) );

		break;

	case 'the_seo_framework_sitemaps_metabox_robots':
		$locate_url = true;
		$show_settings = true;

		?>
		<h4><?php esc_html_e( 'Robots.txt Settings', 'autodescription' ); ?></h4>
		<?php

		if ( $this->has_robots_txt() ) :
			$this->attention_description( __( 'Note: A robots.txt file has been detected in the root folder of your website. This means these settings have no effect.', 'autodescription' ) );
			echo '<hr>';
		elseif ( ! $this->pretty_permalinks ) :
			$locate_url = false;

			$this->attention_description( __( "Note: You're using the plain permalink structure.", 'autodescription' ) );
			$this->description( __( "This means the robots.txt file can't be outputted via the WordPress rewrite rules.", 'autodescription' ) );
			echo '<hr>';
			$this->description_noesc(
				sprintf(
					esc_html_x( 'Change your Permalink Settings %s (Recommended: "Post name").', '%s = here', 'autodescription' ),
					sprintf(
						'<a href="%s" target="_blank" title="%s">%s</a>',
						esc_url( admin_url( 'options-permalink.php' ), [ 'http', 'https' ] ),
						esc_attr__( 'Permalink Settings', 'autodescription' ),
						esc_html_x( 'here', 'The sitemap can be found %s.', 'autodescription' )
					)
				)
			);
			echo '<hr>';
		elseif ( ! $this->can_do_sitemap_robots( false ) ) :
			if ( $this->is_subdirectory_installation() ) {
				$this->attention_description( __( "Note: robots.txt files can't be generated or used on subdirectory installations.", 'autodescription' ) );
				$locate_url = false;
				$show_settings = false;
			} else {
				$this->attention_description( __( 'Note: Another robots.txt sitemap location addition has been detected. This means these settings have no effect.', 'autodescription' ) );
			}
			echo '<hr>';
		endif;

		$this->description( __( 'The robots.txt file is the first thing search engines look for. If you add the sitemap location in the robots.txt file, then search engines will look for and index the sitemap.', 'autodescription' ) );
		$this->description( __( 'If you do not add the sitemap location to the robots.txt file, you will need to notify search engines manually through the Webmaster Console provided by the search engines.', 'autodescription' ) );

		echo '<hr>';

		if ( $show_settings ) :
			printf(
				'<h4>%s</h4>',
				esc_html__( 'Add sitemap location in robots.txt', 'autodescription' )
			);
			$this->wrap_fields(
				$this->make_checkbox(
					'sitemaps_robots',
					esc_html__( 'Add sitemap location in robots?', 'autodescription' ) . ' ' . $this->make_info( __( 'This only has effect when the sitemap is active.', 'autodescription' ), '', false ),
					'',
					false
				), true
			);
		endif;

		if ( $locate_url ) {
			$robots_url = $this->get_robots_txt_url();
			$here = '<a href="' . esc_url( $robots_url, [ 'http', 'https' ] ) . '" target="_blank" title="' . esc_attr__( 'View robots.txt', 'autodescription' ) . '">' . esc_html_x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';

			$this->description_noesc( sprintf( esc_html_x( 'The robots.txt file can be found %s.', '%s = here', 'autodescription' ), $here ) );
		}
		break;

	case 'the_seo_framework_sitemaps_metabox_metadata':
		?>
		<h4><?php esc_html_e( 'Timestamps Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'The modified time suggests to search engines where to look for content changes. It has no impact on the SEO value unless you drastically change pages or posts. It then depends on how well your content is constructed.', 'autodescription' ) );

		//* Echo checkbox.
		$this->wrap_fields(
			$this->make_checkbox(
				'sitemaps_modified',
				/* translators: %s = An XML tag example */
				sprintf( esc_html__( 'Add %s to the sitemap?', 'autodescription' ), $this->code_wrap( '<lastmod>' ) ),
				'',
				false
			), true
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
				/* translators: %s = An XML tag example */
				sprintf( esc_html__( 'Add %s to the sitemap?', 'autodescription' ), $this->code_wrap( '<priority>' ) ),
				'',
				false
			), true
		);
		break;

	case 'the_seo_framework_sitemaps_metabox_notify':
		?>
		<h4><?php esc_html_e( 'Ping Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Notifying search engines of a sitemap change is helpful to get your content indexed as soon as possible.', 'autodescription' ) );
		$this->description( __( 'By default this will happen at most once an hour.', 'autodescription' ) );

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
		$this->description( __( 'You can style the sitemap to give it a more personal look. Styling the sitemap has no SEO value whatsoever.', 'autodescription' ) );
		$this->description( __( 'Note: Changes might not appear to have effect directly because the stylesheet is cached in the browser for 30 minutes.', 'autodescription' ) );
		?>
		<hr>

		<h4><?php esc_html_e( 'Enable styling', 'autodescription' ); ?></h4>
		<?php

		//* Echo checkboxes.
		$this->wrap_fields(
			$this->make_checkbox(
				'sitemap_styles',
				esc_html__( 'Style Sitemap?', 'autodescription' ) . ' ' . $this->make_info( __( 'This makes the sitemap more readable for humans.', 'autodescription' ), '', false ),
				'',
				false
			), true
		);

		?>
		<hr>

		<h4><?php esc_html_e( 'Style configuration', 'autodescription' ); ?></h4>
		<?php

		if ( $this->can_use_logo() ) :
			//* Echo checkbox.
			$this->wrap_fields(
				$this->make_checkbox(
					'sitemap_logo',
					esc_html__( 'Add site logo?', 'autodescription' ) . ' ' . $this->make_info( __( 'The logo is set in Customizer.', 'autodescription' ), '', false ),
					'',
					false
				), true
			);
		endif;

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
