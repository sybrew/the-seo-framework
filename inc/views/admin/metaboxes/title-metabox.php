<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use The_SEO_Framework\Bridges\SeoSettings;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and the_seo_framework()->_verify_include_secret( $_secret ) or die;

// Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_title_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_title_metabox_main':
		$blogname = $this->get_blogname();
		$sep      = esc_html( $this->get_separator( 'title' ) );
		$showleft = 'left' === $this->get_option( 'title_location' );

		$additions_left  = '<span class=tsf-title-additions-js>' . $blogname . '<span class=tsf-sep-js>' . " $sep " . '</span></span>';
		$additions_right = '<span class=tsf-title-additions-js><span class=tsf-sep-js>' . " $sep " . '</span>' . $blogname . '</span>';

		$latest_post_id = $this->get_latest_post_id();
		$latest_cat_id  = $this->get_latest_category_id();

		// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- We don't expect users to set scripts in titles.
		$post_name  = strip_tags( get_the_title( $latest_post_id ) ) ?: __( 'Example Post', 'autodescription' );
		$post_title = $this->s_title( $this->hellip_if_over( $post_name, 60 ) );

		// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- We don't expect users to set scripts in titles.
		$cat_name   = strip_tags( get_cat_name( $latest_cat_id ) ?: __( 'Example Category', 'autodescription' ) );
		$cat_prefix = $this->s_title( $this->get_tax_type_label( 'category', true ) ?: __( 'Category', 'default' ) );
		$tax_title  = sprintf(
			'<span class=tsf-title-prefix-example style=display:%s>%s: </span> %s', // TODO RTL?
			$this->get_option( 'title_rem_prefixes' ) ? 'none' : 'inline',
			$cat_prefix,
			$this->s_title( $this->hellip_if_over( $cat_name, 60 - strlen( $cat_prefix ) ) )
		);

		$example_post_left  = '<em>' . $additions_left . $post_name . '</em>';
		$example_post_right = '<em>' . $post_name . $additions_right . '</em>';
		$example_tax_left   = '<em>' . $additions_left . $tax_title . '</em>';
		$example_tax_right  = '<em>' . $tax_title . $additions_right . '</em>';

		?>
		<h4><?php esc_html_e( 'Automated Title Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'The page title is prominently shown within the browser tab as well as within the search engine results pages.', 'autodescription' ) );

		?>
		<h4><?php esc_html_e( 'Example Page Title Output', 'autodescription' ); ?></h4>
		<p>
			<span class="tsf-title-additions-example-left" style="display:<?php echo $showleft ? 'inline' : 'none'; ?>">
				<?php
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
				echo $this->code_wrap_noesc( $example_post_left );
				?>
			</span>
			<span class="tsf-title-additions-example-right" style="display:<?php echo $showleft ? 'none' : 'inline'; ?>">
				<?php
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
				echo $this->code_wrap_noesc( $example_post_right );
				?>
			</span>
		</p>

		<h4><?php esc_html_e( 'Example Archive Title Output', 'autodescription' ); ?></h4>
		<p>
			<span class="tsf-title-additions-example-left" style="display:<?php echo $showleft ? 'inline' : 'none'; ?>">
				<?php
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
				echo $this->code_wrap_noesc( $example_tax_left );
				?>
			</span>
			<span class="tsf-title-additions-example-right" style="display:<?php echo $showleft ? 'none' : 'inline'; ?>">
				<?php
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
				echo $this->code_wrap_noesc( $example_tax_right );
				?>
			</span>
		</p>

		<hr>
		<?php
		if (
			! ( defined( 'TSF_DISABLE_SUGGESTIONS' ) && TSF_DISABLE_SUGGESTIONS )
			&& ! current_theme_supports( 'title-tag' )
			&& ! defined( 'TSFEM_E_TITLE_FIX' )
			&& current_user_can( 'install_plugins' )
		) {
			/* translators: %s = title-tag */
			$_h4 = sprintf( esc_html__( 'Theme %s Support Missing', 'autodescription' ), '<code>title-tag</code>' );
			?>
			<h4 class=attention><?php echo $_h4; // phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped ?></h4>
			<?php
			$this->description_noesc(
				$this->convert_markdown(
					sprintf(
						/* translators: 1: Extension name, 2: Extension link. Markdown!  */
						esc_html__( "The current theme doesn't support a feature that allows predictable output of titles. Consider installing [%1\$s](%2\$s) when you notice the title output in the browser-tab isn't as you have configured.", 'autodescription' ),
						'Title Fix',
						'https://theseoframework.com/?p=2298'
					),
					[ 'a' ],
					[ 'a_internal' => false ]
				)
			);
			?>
			<hr>
			<?php
		}

		$default_tabs = [
			'general'   => [
				'name'     => __( 'General', 'autodescription' ),
				'callback' => SeoSettings::class . '::_title_metabox_general_tab',
				'dashicon' => 'admin-generic',
			],
			'additions' => [
				'name'     => __( 'Additions', 'autodescription' ),
				'callback' => SeoSettings::class . '::_title_metabox_additions_tab',
				'dashicon' => 'plus',
				'args'     => [
					'examples' => [
						'left'  => $example_post_left,
						'right' => $example_post_right,
					],
				],
			],
			'prefixes'  => [
				'name'     => __( 'Prefixes', 'autodescription' ),
				'callback' => SeoSettings::class . '::_title_metabox_prefixes_tab',
				'dashicon' => 'plus-alt',
				'args'     => [
					'showleft' => $showleft,
				],
			],
		];

		/**
		 * @since 2.6.0
		 * @param array $defaults The default tabs.
		 * @param array $args     The args added on the callback.
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_title_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		SeoSettings::_nav_tab_wrapper( 'title', $tabs );
		break;

	case 'the_seo_framework_title_metabox_general':
		$title_separator         = $this->get_separator_list();
		$default_title_separator = $this->get_option( 'title_separator' );

		?>
		<fieldset>
			<legend>
				<h4><?php esc_html_e( 'Title Separator', 'autodescription' ); ?></h4>
			</legend>
			<?php
			$this->description( __( 'If the title consists of multiple parts, then the separator will go in-between them.', 'autodescription' ) );
			?>
			<p id="tsf-title-separator" class="tsf-fields">
			<?php
			foreach ( $title_separator as $name => $html ) {
				vprintf(
					'<input type=radio name="%1$s" id="%2$s" value="%3$s" %4$s %5$s /><label for="%2$s">%6$s</label>',
					[
						esc_attr( $this->get_field_name( 'title_separator' ) ),
						esc_attr( $this->get_field_id( 'title_separator_' . $name ) ),
						esc_attr( $name ),
						// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- make_data_attributes() escapes.
						$this->make_data_attributes( [ 'entity' => esc_html( $html ) ] ), // This will double escape, but we found no issues.
						checked( $default_title_separator, $name, false ),
						esc_html( $html ),
					]
				);
			}
			?>
			</p>
		</fieldset>

		<hr>

		<h4><?php esc_html_e( 'Automated Title Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'A title is generated for every page.', 'autodescription' ) );
		$this->description( __( 'Some titles may have HTML tags inserted by the author for styling.', 'autodescription' ) );

		$info = $this->make_info(
			sprintf(
				/* translators: %s = HTML tag example */
				__( 'This strips HTML tags, like %s, from the title. Disable this option to display generated HTML tags as plain text in meta titles.', 'autodescription' ),
				'<code>&amp;lt;strong&amp;gt;</code>' // Double escaped HTML (&amp;) for attribute display.
			),
			'',
			false
		);
		$this->wrap_fields(
			$this->make_checkbox(
				'title_strip_tags',
				esc_html__( 'Strip HTML tags from generated titles?', 'autodescription' ) . ' ' . $info,
				'',
				false
			),
			true
		);

		$this->description( __( 'Tip: It is a bad practice to style page titles with HTML as inconsistent behavior might occur.', 'autodescription' ) );
		break;

	case 'the_seo_framework_title_metabox_additions':
		$example_left  = $examples['left'];
		$example_right = $examples['right'];

		$homepage_has_option = __( 'This option does not affect the homepage; it uses a different one.', 'autodescription' );

		?>
		<fieldset>
			<legend>
				<h4><?php esc_html_e( 'Site Title Location', 'autodescription' ); ?></h4>
			</legend>
			<p id="tsf-title-location" class="tsf-fields">
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'title_location' ); ?>" id="<?php $this->field_id( 'title_location_left' ); ?>" value="left" <?php checked( $this->get_option( 'title_location' ), 'left' ); ?> />
					<label for="<?php $this->field_id( 'title_location_left' ); ?>">
						<span><?php esc_html_e( 'Left:', 'autodescription' ); ?></span>
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
						echo $this->code_wrap_noesc( $example_left );
						?>
					</label>
				</span>
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'title_location' ); ?>" id="<?php $this->field_id( 'title_location_right' ); ?>" value="right" <?php checked( $this->get_option( 'title_location' ), 'right' ); ?> />
					<label for="<?php $this->field_id( 'title_location_right' ); ?>">
						<span><?php esc_html_e( 'Right:', 'autodescription' ); ?></span>
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
						echo $this->code_wrap_noesc( $example_right );
						?>
					</label>
				</span>
			</p>
			<?php $this->description( $homepage_has_option ); ?>
		</fieldset>

		<hr>

		<h4><?php esc_html_e( 'Site Title', 'autodescription' ); ?></h4>
		<div id="tsf-title-additions-toggle">
			<?php
			$info = $this->make_info(
				__( 'Always brand your titles. Search engines may ignore your titles with this feature enabled.', 'autodescription' ),
				'https://support.google.com/webmasters/answer/35624#page-titles',
				false
			);

			$this->wrap_fields(
				$this->make_checkbox(
					'title_rem_additions',
					esc_html__( 'Remove site title from the title?', 'autodescription' ) . ' ' . $info,
					'',
					false
				),
				true
			);
			?>
		</div>
		<?php
		$this->attention_description( __( 'Note: Only use this option if you are aware of its SEO effects.', 'autodescription' ), false );
		echo ' ';
		$this->description( $homepage_has_option, false );
		break;

	case 'the_seo_framework_title_metabox_prefixes':
		?>
		<h4><?php esc_html_e( 'Title Prefix Options', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'For archives, a descriptive prefix may be added to generated titles.', 'autodescription' ) );

		?>
		<hr>

		<h4><?php esc_html_e( 'Archive Title Prefixes', 'autodescription' ); ?></h4>
		<div id="tsf-title-prefixes-toggle">
			<?php
			$info = $this->make_info(
				__( "The prefix helps visitors and search engines determine what kind of page they're visiting.", 'autodescription' ),
				'https://kb.theseoframework.com/?p=34',
				false
			);
			$this->wrap_fields(
				$this->make_checkbox(
					'title_rem_prefixes',
					esc_html__( 'Remove term type prefixes from generated archive titles?', 'autodescription' ) . ' ' . $info,
					'',
					false
				),
				true
			);
			?>
		</div>
		<?php
		break;

	default:
		break;
endswitch;
