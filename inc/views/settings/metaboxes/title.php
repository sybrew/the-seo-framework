<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use The_SEO_Framework\Bridges\SeoSettings,
	The_SEO_Framework\Interpreters\HTML,
	The_SEO_Framework\Interpreters\Settings_Input as Input;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

switch ( $this->get_view_instance( 'title', $instance ) ) :
	case 'title_main':
		$blogname = esc_html( $this->get_blogname() );
		$sep      = esc_html( $this->get_separator( 'title' ) );

		$additions_left  = "<span class=tsf-title-additions-js><span class=tsf-site-title-js>$blogname</span><span class=tsf-sep-js> $sep </span></span>";
		$additions_right = "<span class=tsf-title-additions-js><span class=tsf-sep-js> $sep </span><span class=tsf-site-title-js>$blogname</span></span>";

		$latest_post_id = $this->get_latest_post_id();
		$latest_cat_id  = $this->get_latest_category_id();

		$post_title = $this->s_title( $this->hellip_if_over(
			// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- We don't expect users to set scripts in titles.
			esc_html( strip_tags( get_the_title( $latest_post_id ) ) ?: __( 'Example Post', 'autodescription' ) ),
			60
		) );

		$cat_prefix = $this->s_title( _x( 'Category:', 'category archive title prefix', 'default' ) );
		$cat_title  = $this->s_title( $this->hellip_if_over(
			// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- We don't expect users to set scripts in titles.
			strip_tags( get_cat_name( $latest_cat_id ) ?: __( 'Example Category', 'autodescription' ) ),
			60 - strlen( $cat_prefix )
		) );
		$cat_title_full = sprintf(
			/* translators: 1: Title prefix. 2: Title. */
			esc_html_x( '%1$s %2$s', 'archive title', 'default' ),
			$cat_prefix,
			$cat_title
		);

		$example_post_left      = "<em>{$additions_left}{$post_title}</em>";
		$example_post_right     = "<em>{$post_title}{$additions_right}</em>";
		$example_tax_left_full  = "<em>{$additions_left}{$cat_title_full}</em>";
		$example_tax_right_full = "<em>{$cat_title_full}{$additions_right}</em>";
		$example_tax_left       = "<em>{$additions_left}{$cat_title}</em>";
		$example_tax_right      = "<em>{$cat_title}{$additions_right}</em>";

		HTML::description( __( 'The page title is prominently shown within the browser tab as well as within the search engine results pages.', 'autodescription' ) );

		// Yes, this is a mess. But, we cannot circumvent this because we do not control the translations.
		?>
		<div class=hide-if-no-tsf-js>
			<?php
			HTML::header_title( __( 'Example Page Title Output', 'autodescription' ) );
			?>
			<p>
				<span class="tsf-title-additions-example-left hidden">
					<?php
					// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
					echo HTML::code_wrap_noesc( $example_post_left );
					?>
				</span>
				<span class="tsf-title-additions-example-right hidden">
					<?php
					// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
					echo HTML::code_wrap_noesc( $example_post_right );
					?>
				</span>
			</p>

			<?php HTML::header_title( __( 'Example Archive Title Output', 'autodescription' ) ); ?>
			<p>
				<span class="tsf-title-additions-example-left tsf-title-tax-prefix hidden">
					<?php
					// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
					echo HTML::code_wrap_noesc( $example_tax_left_full );
					?>
				</span>
				<span class="tsf-title-additions-example-right tsf-title-tax-prefix hidden">
					<?php
					// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
					echo HTML::code_wrap_noesc( $example_tax_right_full );
					?>
				</span>
				<span class="tsf-title-additions-example-left tsf-title-tax-noprefix hidden">
					<?php
					// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
					echo HTML::code_wrap_noesc( $example_tax_left );
					?>
				</span>
				<span class="tsf-title-additions-example-right tsf-title-tax-noprefix hidden">
					<?php
					// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
					echo HTML::code_wrap_noesc( $example_tax_right );
					?>
				</span>
			</p>
		</div>

		<hr>
		<?php
		if (
			   $this->_display_extension_suggestions()
			&& ! current_theme_supports( 'title-tag' )
			&& ! defined( 'TSFEM_E_TITLE_FIX' )
		) {
			?>
			<h4>
			<?php
			/* translators: %s = title-tag */
			printf( esc_html__( 'Theme %s Support Missing', 'autodescription' ), '<code>title-tag</code>' );
			?>
			</h4>
			<?php
			HTML::description_noesc(
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

		$_settings_class = SeoSettings::class;

		$tabs = [
			'general'   => [
				'name'     => __( 'General', 'autodescription' ),
				'callback' => [ $_settings_class, '_title_metabox_general_tab' ],
				'dashicon' => 'admin-generic',
			],
			'additions' => [
				'name'     => __( 'Additions', 'autodescription' ),
				'callback' => [ $_settings_class, '_title_metabox_additions_tab' ],
				'dashicon' => 'plus-alt2',
				'args'     => [
					'examples' => [
						'left'  => $example_post_left,
						'right' => $example_post_right,
					],
				],
			],
			'prefixes'  => [
				'name'     => __( 'Prefixes', 'autodescription' ),
				'callback' => [ $_settings_class, '_title_metabox_prefixes_tab' ],
				'dashicon' => 'plus-alt',
				'args'     => [],
			],
		];

		SeoSettings::_nav_tab_wrapper(
			'title',
			/**
			 * @since 2.6.0
			 * @param array $tabs The default tabs.
			 */
			(array) apply_filters( 'the_seo_framework_title_settings_tabs', $tabs )
		);
		break;

	case 'title_general_tab':
		$title_separator         = $this->get_separator_list();
		$default_title_separator = $this->get_option( 'title_separator' );

		?>
		<fieldset>
			<legend><?php HTML::header_title( __( 'Title Separator', 'autodescription' ) ); ?></legend>
			<?php
			HTML::description( __( 'If the title consists of multiple parts, then the separator will go in-between them.', 'autodescription' ) );
			?>
			<p id=tsf-title-separator class=tsf-fields>
			<?php
			foreach ( $title_separator as $name => $html ) {
				vprintf(
					'<input type=radio name="%1$s" id="%2$s" value="%3$s" %4$s %5$s /><label for="%2$s">%6$s</label>',
					[
						esc_attr( Input::get_field_name( 'title_separator' ) ),
						esc_attr( Input::get_field_id( "title_separator_{$name}" ) ),
						esc_attr( $name ),
						// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- make_data_attributes() escapes.
						HTML::make_data_attributes( [ 'entity' => esc_html( $html ) ] ), // This will double escape, but we found no issues.
						checked( $default_title_separator, $name, false ),
						esc_html( $html ),
					]
				);
			}
			?>
			</p>
		</fieldset>

		<hr>
		<?php
		HTML::header_title( __( 'Automated Title Settings', 'autodescription' ) );
		HTML::description( __( 'A title is generated for every page.', 'autodescription' ) );
		HTML::description( __( 'Some titles may have HTML tags inserted by the author for styling.', 'autodescription' ) );

		$info = HTML::make_info(
			sprintf(
				/* translators: %s = HTML tag example */
				__( 'This strips HTML tags, like %s, from the title. Disable this option to display generated HTML tags as plain text in meta titles.', 'autodescription' ),
				'<code>&amp;lt;strong&amp;gt;</code>' // Double escaped HTML (&amp;) for attribute display.
			),
			'',
			false
		);
		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'title_strip_tags',
				'label'  => esc_html__( 'Strip HTML tags from generated titles?', 'autodescription' ) . " $info",
				'escape' => false,
			] ),
			true
		);

		HTML::description( __( 'Tip: It is a bad practice to style page titles with HTML as inconsistent behavior might occur.', 'autodescription' ) );
		break;

	case 'title_additions_tab':
		?>
		<p>
			<label for="<?php Input::field_id( 'site_title' ); ?>" class=tsf-toblock>
				<strong><?php esc_html_e( 'Site Title', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p class=tsf-title-wrap>
			<input type=text name="<?php Input::field_name( 'site_title' ); ?>" class=large-text id="<?php Input::field_id( 'site_title' ); ?>" placeholder="<?= esc_attr( $this->s_title_raw( $this->get_filtered_raw_blogname() ) ) ?>" value="<?= $this->esc_attr_preserve_amp( $this->get_option( 'site_title' ) ) ?>" autocomplete=off />
		</p>
		<?php
		HTML::description( __( 'This option does not affect titles displayed directly on your website.', 'autodescription' ) );
		?>

		<hr>
		<?php
		$example_left  = $examples['left'];
		$example_right = $examples['right'];

		$homepage_has_option = __( 'This option does not affect the homepage; it uses a different one.', 'autodescription' );
		?>
		<fieldset>
			<legend><?php HTML::header_title( __( 'Site Title Location', 'autodescription' ) ); ?></legend>
			<p id=tsf-title-location class=tsf-fields>
				<span class=tsf-toblock>
					<input type=radio name="<?php Input::field_name( 'title_location' ); ?>" id="<?php Input::field_id( 'title_location_left' ); ?>" value=left <?php checked( $this->get_option( 'title_location' ), 'left' ); ?> />
					<label for="<?php Input::field_id( 'title_location_left' ); ?>">
						<span><?php esc_html_e( 'Left:', 'autodescription' ); ?></span>
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
						echo HTML::code_wrap_noesc( $example_left );
						?>
					</label>
				</span>
				<span class=tsf-toblock>
					<input type=radio name="<?php Input::field_name( 'title_location' ); ?>" id="<?php Input::field_id( 'title_location_right' ); ?>" value=right <?php checked( $this->get_option( 'title_location' ), 'right' ); ?> />
					<label for="<?php Input::field_id( 'title_location_right' ); ?>">
						<span><?php esc_html_e( 'Right:', 'autodescription' ); ?></span>
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
						echo HTML::code_wrap_noesc( $example_right );
						?>
					</label>
				</span>
			</p>
			<?php HTML::description( $homepage_has_option ); ?>
		</fieldset>

		<hr>

		<?php HTML::header_title( __( 'Site Title Removal', 'autodescription' ) ); ?>
		<div id=tsf-title-additions-toggle>
			<?php
			$info = HTML::make_info(
				__( 'Always brand your titles. Search engines may ignore your titles with this feature enabled.', 'autodescription' ),
				'https://developers.google.com/search/docs/advanced/appearance/title-link',
				false
			);

			HTML::wrap_fields(
				Input::make_checkbox( [
					'id'     => 'title_rem_additions',
					'label'  => esc_html__( 'Remove site title from the title?', 'autodescription' ) . " $info",
					'escape' => false,
				] ),
				true
			);
			?>
		</div>
		<?php
		HTML::attention_description( __( 'Note: Only use this option if you are aware of its SEO effects.', 'autodescription' ), false );
		echo ' ';
		HTML::description( $homepage_has_option, false );
		break;

	case 'title_prefixes_tab':
		HTML::header_title( __( 'Title Prefix Options', 'autodescription' ) );
		HTML::description( __( 'For archives, a descriptive prefix may be added to generated titles.', 'autodescription' ) );

		?>
		<hr>

		<?php HTML::header_title( __( 'Archive Title Prefixes', 'autodescription' ) ); ?>
		<div id=tsf-title-prefixes-toggle>
			<?php
			$info = HTML::make_info(
				__( "The prefix helps visitors and search engines determine what kind of page they're visiting.", 'autodescription' ),
				'https://kb.theseoframework.com/?p=34',
				false
			);
			HTML::wrap_fields(
				Input::make_checkbox( [
					'id'     => 'title_rem_prefixes',
					'label'  => esc_html__( 'Remove term type prefixes from generated archive titles?', 'autodescription' ) . " $info",
					'escape' => false,
				] ),
				true
			);
			?>
		</div>
		<?php
		break;

	default:
		break;
endswitch;
