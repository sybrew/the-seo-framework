<?php

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_general_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_general_metabox_main' :

		$default_tabs = array(
			'general' => array(
				'name'     => __( 'General', 'autodescription' ),
				'callback' => array( $this, 'general_metabox_general_tab' ),
				'dashicon' => 'admin-generic',
			),
			'performance' => array(
				'name'     => __( 'Performance', 'autodescription' ),
				'callback' => array( $this, 'general_metabox_performance_tab' ),
				'dashicon' => 'performance',
			),
			'layout' => array(
				'name'     => __( 'Layout', 'autodescription' ),
				'callback' => array( $this, 'general_metabox_layout_tab' ),
				'dashicon' => 'screenoptions',
			),
			'canonical' => array(
				'name'     => __( 'Canonical', 'autodescription' ),
				'callback' => array( $this, 'general_metabox_canonical_tab' ),
				'dashicon' => 'external',
			),
		);

		/**
		 * Applies filters `the_seo_framework_general_settings_tabs` : Array
		 * Used to extend or minimize General Settings tabs
		 * @since 2.8.0
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_general_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'general', $tabs, '2.8.0' );
		break;

	case 'the_seo_framework_general_metabox_general' :
		echo 'Nothing to see here yet.';
		break;

	case 'the_seo_framework_general_metabox_performance' :

		?><h4><?php esc_html_e( 'Performance Settings', 'autodescription' ); ?></h4><?php
		$this->description( __( 'In order to improve performance, generated SEO output is stored in the database as transient cache.', 'autodescription' ) );
		$this->description( __( 'If your website has thousands of pages, or if other forms of caching are used, you might wish to adjust these options.', 'autodescription' ) );

		?>
		<hr>

		<h4><?php esc_html_e( 'Transient Cache Settings', 'autodescription' ); ?></h4>
		<?php
		$this->wrap_fields(
			array(
				$this->make_checkbox(
					'cache_meta_description',
					esc_html__( 'Enable automated description output cache?', 'autodescription' )
					. ' ' . $this->make_info( __( 'Description generation can use a lot of server resources when it reads the page content.', 'autodescription' ), '', false ),
					'',
					false
				),
				$this->make_checkbox(
					'cache_meta_schema',
					esc_html__( 'Enable automated Schema output cache?', 'autodescription' )
					. ' ' . $this->make_info( __( 'Schema.org output generally makes multiple calls to the database.', 'autodescription' ), '', false ),
					'',
					false
				),
				$this->make_checkbox(
					'cache_sitemap',
					esc_html__( 'Enable sitemap generation cache?', 'autodescription' )
					. ' ' . $this->make_info( __( 'Generating the sitemap can use a lot of server resources.', 'autodescription' ), '', false ),
					'',
					false
				),
			),
			true
		);
		break;

	case 'the_seo_framework_general_metabox_layout' :
		?><h4><?php esc_html_e( 'Administrative Layout Settings', 'autodescription' ); ?></h4><?php
		$this->description( __( 'SEO hints can be visually displayed throughout the dashboard.', 'autodescription' ) );

		?>
		<hr>

		<h4><?php esc_html_e( 'SEO Bar Settings', 'autodescription' ); ?></h4>
		<?php
		$this->wrap_fields(
			array(
				$this->make_checkbox(
					'display_seo_bar_tables',
					esc_html__( 'Display the SEO Bar in overview tables?', 'autodescription' ),
					'',
					false
				),
				$this->make_checkbox(
					'display_seo_bar_metabox',
					esc_html__( 'Display the SEO Bar in the SEO Settings metabox?', 'autodescription' ),
					'',
					false
				),
			),
			true
		);
		break;

	case 'the_seo_framework_general_metabox_canonical' :

		?><h4><?php esc_html_e( 'Canonical URL Settings', 'autodescription' ); ?></h4><?php
		$this->description( __( 'The canonical URL meta tag forces Search Engines to go to the outputted URL.', 'autodescription' ) );
		$this->description( __( 'If the canonical URL meta tag represents the visited page, then the Search Engine will crawl the visited page. Otherwise, the Search Engine will try to go to the outputted URL.', 'autodescription' ) );
		$this->description( __( 'Only adjust these options if you are aware of its SEO effects.', 'autodescription' ) );
		?>
		<hr>

		<p>
			<h4><?php esc_html_e( 'Scheme Settings', 'autodescription' ); ?></h4>
			<?php
			$this->description( __( 'If your website is both accessible on HTTP as HTTPS, set this to HTTPS in order to prevent duplicated content.', 'autodescription' ) );
			$this->description( __( 'Otherwise, automatic detection is recommended.', 'autodescription' ) );
			?>
			<label for="<?php $this->field_id( 'canonical_scheme' ); ?>"><?php echo esc_html_x( 'Preferred canonical URL scheme:', '= Detect Automatically, HTTPS, HTTP', 'autodescription' ); ?></label>
			<select name="<?php $this->field_name( 'canonical_scheme' ); ?>" id="<?php $this->field_id( 'canonical_scheme' ); ?>">
				<?php
				$scheme_types = (array) apply_filters(
					'the_seo_framework_canonical_scheme_types',
					array(
						'automatic' => __( 'Detect automatically', 'autodescription' ),
						'http'      => 'HTTP',
						'https'     => 'HTTPS',
					)
				);
				foreach ( $scheme_types as $value => $name )
					echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->get_field_value( 'canonical_scheme' ), esc_attr( $value ), false ) . '>' . esc_html( $name ) . '</option>' . "\n";
				?>
			</select>
		</p>
		<?php
		break;

	default :
		break;
endswitch;
