<?php

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_sitemaps_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_sitemaps_metabox_main' :

		/**
		 * Parse tabs content
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *		array(
		 *			'name'     => The name
		 *			'callback' => The callback function, use array for method calling
		 *			'dashicon' => Desired dashicon
		 *		)
		 * }
		 *
		 * @since 2.2.9
		 */
		$default_tabs = array(
			'general' => array(
				'name'     => __( 'General', 'autodescription' ),
				'callback' => array( $this, 'sitemaps_metabox_general_tab' ),
				'dashicon' => 'admin-generic',
			),
			'robots' => array(
				'name'     => 'Robots.txt',
				'callback' => array( $this, 'sitemaps_metabox_robots_tab' ),
				'dashicon' => 'share-alt2',
			),
			'timestamps' => array(
				'name'     => __( 'Timestamps', 'autodescription' ),
				'callback' => array( $this, 'sitemaps_metabox_timestamps_tab' ),
				'dashicon' => 'backup',
			),
			'notify' => array(
				'name'     => _x( 'Ping', 'Ping or notify Search Engine', 'autodescription' ),
				'callback' => array( $this, 'sitemaps_metabox_notify_tab' ),
				'dashicon' => 'megaphone',
			),
			'style' => array(
				'name'     => __( 'Style', 'autodescription' ),
				'callback' => array( $this, 'sitemaps_metabox_style_tab' ),
				'dashicon' => 'art',
			),
		);

		/**
		 * Applies filters the_seo_framework_sitemaps_settings_tabs : array see $default_tabs
		 *
		 * Used to extend Knowledge Graph tabs
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_sitemaps_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );
		$use_tabs = true;

		$has_sitemap_plugin = $this->detect_sitemap_plugin();
		$sitemap_detected = $this->has_sitemap_xml();
		$robots_detected = $this->has_robots_txt();

		/**
		 * Remove the timestamps and notify submenus
		 * @since 2.5.2
		 */
		if ( $has_sitemap_plugin || $sitemap_detected ) {
			unset( $tabs['timestamps'] );
			unset( $tabs['notify'] );
		}

		$this->nav_tab_wrapper( 'sitemaps', $tabs, '2.2.8' );
		break;

	case 'the_seo_framework_sitemaps_metabox_general' :

		$sitemap_url = $this->get_sitemap_xml_url();
		$has_sitemap_plugin = $this->detect_sitemap_plugin();
		$sitemap_detected = $this->has_sitemap_xml();

		?>
		<h4><?php esc_html_e( 'Sitemap Integration Settings', 'autodescription' ); ?></h4>
		<?php

		if ( $has_sitemap_plugin ) :
			$this->description( __( 'Another active sitemap plugin has been detected. This means that the sitemap functionality has been replaced.', 'autodescription' ) );
		elseif ( $sitemap_detected ) :
			$this->description( __( 'A sitemap has been detected in the root folder of your website. This means that the sitemap functionality has no effect.', 'autodescription' ) );
		else :
			$this->description( __( 'The Sitemap is an XML file that lists pages and posts for your website along with optional metadata about each post or page. This helps Search Engines crawl your website more easily.', 'autodescription' ) );
			$this->description( __( 'The optional metadata include the post and page modified time and a page priority indication, which is automated.', 'autodescription' ) );

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
		endif;

		if ( ! $has_sitemap_plugin && ( $this->get_option( 'sitemaps_output' ) || $sitemap_detected ) ) {
			$here = '<a href="' . esc_url( $sitemap_url, array( 'http', 'https' ) ) . '" target="_blank" title="' . esc_attr__( 'View sitemap', 'autodescription' ) . '">' . esc_attr_x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';
			$this->description_noesc( sprintf( _x( 'The sitemap can be found %s.', '%s = here', 'autodescription' ), $here ) );
		}
		break;

	case 'the_seo_framework_sitemaps_metabox_robots' :

		$locate_url = true;

		?>
		<h4><?php esc_html_e( 'Robots.txt Settings', 'autodescription' ); ?></h4>
		<?php

		if ( $this->has_robots_txt() ) :
			$this->description( __( 'A robots.txt file has been detected in the root folder of your website; therefore no settings are able to alter its output.', 'autodescription' ) );
		elseif ( ! $this->pretty_permalinks ) :

			$permalink_settings_url = admin_url( 'options-permalink.php' );
			$here = '<a href="' . esc_url( $permalink_settings_url, array( 'http', 'https' ) ) . '" target="_blank" title="' . esc_attr__( 'Permalink Settings', 'autodescription' ) . '">' . esc_html_x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';

			?><h4><?php esc_html_e( "You're using the plain permalink structure.", 'autodescription' ); ?></h4><?php
			$this->description( __( "This means the robots.txt file can't be outputted through the WordPress rewrite rules.", 'autodescription' ) );
			?><hr><?php
			$this->description_noesc( sprintf( esc_html_x( 'Change your Permalink Settings %s (Recommended: "Post name").', '%s = here', 'autodescription' ), $here ) );

			$locate_url = false;
		elseif ( $this->can_do_sitemap_robots( false ) ) :
			$this->description( __( 'The robots.txt file is the first thing Search Engines look for. If you add the sitemap location in the robots.txt file, then Search Engines will look for and index the sitemap.', 'autodescription' ) );
			$this->description( __( 'If you do not add the sitemap location to the robots.txt file, you will need to notify Search Engines manually through the Webmaster Console provided by the Search Engines.', 'autodescription' ) );

			?>
			<hr>

			<h4><?php esc_html_e( 'Add sitemap location in robots.txt', 'autodescription' ); ?></h4>
			<?php

			//* Echo checkbox.
			$this->wrap_fields(
				$this->make_checkbox(
					'sitemaps_robots',
					esc_html__( 'Add sitemap location in robots?', 'autodescription' ) . ' ' . $this->make_info( __( 'This only has effect if the sitemap is active', 'autodescription' ), '', false ),
					'',
					false
				), true
			);
		else :
			if ( $this->is_subdirectory_installation() ) {
				$this->description( __( 'No robots.txt file can be generated on subdirectory installations.', 'autodescription' ) );
				$locate_url = false;
			} else {
				$this->description( __( 'Another robots.txt sitemap location addition has been detected.', 'autodescription' ) );
			}
		endif;

		if ( $locate_url ) {
			$robots_url = $this->get_robots_txt_url();
			$here = '<a href="' . esc_url( $robots_url, array( 'http', 'https' ) ) . '" target="_blank" title="' . esc_attr__( 'View robots.txt', 'autodescription' ) . '">' . esc_html_x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';

			$this->description_noesc( sprintf( esc_html_x( 'The robots.txt file can be found %s.', '%s = here', 'autodescription' ), $here ) );
		}
		break;

	case 'the_seo_framework_sitemaps_metabox_timestamps' :

		//* Sets timezone according to WordPress settings.
		$this->set_timezone();

		$timestamp_0 = date( 'Y-m-d' );

		/**
		 * @link https://www.w3.org/TR/NOTE-datetime
		 * We use the second expression of the time zone offset handling.
		 */
		$timestamp_1 = date( 'Y-m-d\TH:iP' );

		//* Reset timezone to previous value.
		$this->reset_timezone();

		?><h4><?php esc_html_e( 'Timestamps Settings', 'autodescription' ); ?></h4><?php
		$this->description( __( 'The modified time suggests to Search Engines where to look for content changes. It has no impact on the SEO value unless you drastically change pages or posts. It then depends on how well your content is constructed.', 'autodescription' ) );
		$this->description( __( "By default, the sitemap only outputs the modified date if you've enabled them within the Social Metabox. This setting overrides those settings for the Sitemap.", 'autodescription' ) );

		?>
		<hr>

		<h4><?php esc_html_e( 'Output Modified Date', 'autodescription' ); ?></h4>
		<?php

		//* Echo checkbox.
		$this->wrap_fields(
			$this->make_checkbox(
				'sitemaps_modified',
				sprintf( esc_html__( 'Add %s to the sitemap?', 'autodescription' ), $this->code_wrap( '<lastmod>' ) ),
				'',
				false
			), true
		);

		?>
		<hr>

		<fieldset>
			<legend><h4><?php esc_html_e( 'Timestamp Format Settings', 'autodescription' ); ?></h4></legend>
			<?php $this->description( __( 'Determines how specific the modification timestamp is.', 'autodescription' ) ); ?>

			<p id="sitemaps-timestamp-format" class="tsf-fields">
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'sitemap_timestamps' ); ?>" id="<?php $this->field_id( 'sitemap_timestamps_0' ); ?>" value="0" <?php checked( $this->get_field_value( 'sitemap_timestamps' ), '0' ); ?> />
					<label for="<?php $this->field_id( 'sitemap_timestamps_0' ); ?>">
						<span title="<?php esc_attr_e( 'Complete date', 'autodescription' ); ?>"><?php echo $this->code_wrap( $timestamp_0 ); ?> [?]</span>
					</label>
				</span>
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'sitemap_timestamps' ); ?>" id="<?php $this->field_id( 'sitemap_timestamps_1' ); ?>" value="1" <?php checked( $this->get_field_value( 'sitemap_timestamps' ), '1' ); ?> />
					<label for="<?php $this->field_id( 'sitemap_timestamps_1' ); ?>">
						<span title="<?php esc_attr_e( 'Complete date plus hours, minutes and timezone', 'autodescription' ); ?>"><?php echo $this->code_wrap( $timestamp_1 ); ?> [?]</span>
					</label>
				</span>
			</p>
		</fieldset>
		<?php
		break;

	case 'the_seo_framework_sitemaps_metabox_notify' :

		?><h4><?php esc_html_e( 'Ping Settings', 'autodescription' ); ?></h4><?php
		$this->description( __( 'Notifying Search Engines of a sitemap change is helpful to get your content indexed as soon as possible.', 'autodescription' ) );
		$this->description( __( 'By default this will happen at most once an hour.', 'autodescription' ) );

		?>
		<hr>

		<h4><?php esc_html_e( 'Notify Search Engines', 'autodescription' ); ?></h4>
		<?php

		$engines = array(
			'ping_google' => 'Google',
			'ping_bing'   => 'Bing',
			'ping_yandex' => 'Yandex',
		);

		$ping_checkbox = '';

		foreach ( $engines as $option => $engine ) {
			$ping_label = sprintf( __( 'Notify %s about sitemap changes?', 'autodescription' ), $engine );
			$ping_checkbox .= $this->make_checkbox( $option, $ping_label, '', true );
		}

		//* Echo checkbox.
		$this->wrap_fields( $ping_checkbox, true );
		break;

	case 'the_seo_framework_sitemaps_metabox_style' :

		?>
		<h4><?php esc_html_e( 'Sitemap Styling Settings', 'autodescription' ); ?></h4>
		<?php

		$this->description( __( 'You can style the sitemap to give it a more personal look. Styling the sitemap has no SEO value whatsoever.', 'autodescription' ) );

		?>
		<hr>

		<h4><?php esc_html_e( 'Enable styling', 'autodescription' ); ?></h4>
		<?php

		//* Echo checkboxes.
		$this->wrap_fields(
			$this->make_checkbox(
				'sitemap_styles',
				esc_html__( 'Style Sitemap?', 'autodescription' ) . ' ' . $this->make_info( __( 'This makes the sitemap more readable for humans', 'autodescription' ), '', false ),
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
					esc_html__( 'Add site logo?', 'autodescription' ) . ' ' . $this->make_info( __( 'The logo is set in Customizer', 'autodescription' ), '', false ),
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

	default :
		break;
endswitch;
