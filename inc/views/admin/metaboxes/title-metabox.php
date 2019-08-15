<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

use The_SEO_Framework\Bridges\SeoSettings;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_title_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_title_metabox_main':
		$latest_post_id = $this->get_latest_post_id();
		$title          = '';

		if ( $latest_post_id ) {
			$title = $this->hellip_if_over( $this->get_filtered_raw_generated_title( [ 'id' => $latest_post_id ] ), 60 );
		}

		$title = $this->s_title( $title ?: __( 'Example Post Title', 'autodescription' ) );

		$blogname = $this->get_blogname();
		$sep      = esc_html( $this->get_separator( 'title' ) );

		$additions_left  = '<span class="tsf-title-additions-js">' . $blogname . '<span class="tsf-sep-js">' . " $sep " . '</span></span>';
		$additions_right = '<span class="tsf-title-additions-js"><span class="tsf-sep-js">' . " $sep " . '</span>' . $blogname . '</span>';

		$example_left  = '<em>' . $additions_left . $title . '</em>';
		$example_right = '<em>' . $title . $additions_right . '</em>';

		//* There's no need for "hide-if-no-tsf-js" here.
		//* Check left first, as right is default (and thus fallback).
		$showleft = 'left' === $this->get_option( 'title_location' );

		?>
		<h4><?php esc_html_e( 'Automated Title Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'The page title is prominently shown within the browser tab as well as within the search engine results pages.', 'autodescription' ) );

		?>
		<h4><?php esc_html_e( 'Example Automated Title Output', 'autodescription' ); ?></h4>
		<p>
			<span class="tsf-title-additions-example-left" style="display:<?php echo $showleft ? 'inline' : 'none'; ?>"><?php echo $this->code_wrap_noesc( $example_left ); ?></span>
			<span class="tsf-title-additions-example-right" style="display:<?php echo $showleft ? 'none' : 'inline'; ?>"><?php echo $this->code_wrap_noesc( $example_right ); ?></span>
		</p>

		<hr>
		<?php

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
						'left'  => $example_left,
						'right' => $example_right,
					],
				],
			],
			'prefixes'  => [
				'name'     => __( 'Prefixes', 'autodescription' ),
				'callback' => SeoSettings::class . '::_title_metabox_prefixes_tab',
				'dashicon' => 'plus-alt',
				'args'     => [
					'additions' => [
						'left'  => $additions_left,
						'right' => $additions_right,
					],
					'showleft'  => $showleft,
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
		?>
		<h4><?php esc_html_e( 'Automated Title Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( 'A title is generated for every page.', 'autodescription' );
		$this->description( 'Some titles may have HTML tags inserted by the author for styling.', 'autodescription' );

		$info = $this->make_info(
			sprintf(
				/* translators: %s = HTML tag example */
				__( 'This strips HTML tags, like %s, from the title.', 'autodescription' ),
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

		$homepage_has_option = __( 'The homepage has a specific option.', 'autodescription' );

		?>
		<fieldset>
			<legend>
				<h4><?php esc_html_e( 'Blog Name Location', 'autodescription' ); ?></h4>
			</legend>
			<p id="tsf-title-location" class="tsf-fields">
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'title_location' ); ?>" id="<?php $this->field_id( 'title_location_left' ); ?>" value="left" <?php checked( $this->get_option( 'title_location' ), 'left' ); ?> />
					<label for="<?php $this->field_id( 'title_location_left' ); ?>">
						<span><?php esc_html_e( 'Left:', 'autodescription' ); ?></span>
						<?php echo $this->code_wrap_noesc( $example_left ); ?>
					</label>
				</span>
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'title_location' ); ?>" id="<?php $this->field_id( 'title_location_right' ); ?>" value="right" <?php checked( $this->get_option( 'title_location' ), 'right' ); ?> />
					<label for="<?php $this->field_id( 'title_location_right' ); ?>">
						<span><?php esc_html_e( 'Right:', 'autodescription' ); ?></span>
						<?php echo $this->code_wrap_noesc( $example_right ); ?>
					</label>
				</span>
			</p>
			<?php $this->description( $homepage_has_option ); ?>
		</fieldset>

		<hr>
		<?php
		$title_separator         = $this->get_separator_list();
		$default_title_separator = $this->get_option( 'title_separator' );

		// FIXME: What a mess...
		?>
		<fieldset>
			<legend>
				<h4><?php esc_html_e( 'Title Separator', 'autodescription' ); ?></h4>
				<?php $this->description( __( 'If the title consists of multiple parts, then the separator will go in-between them.', 'autodescription' ) ); ?>
			</legend>
			<p id="tsf-title-separator" class="tsf-fields">
			<?php foreach ( $title_separator as $name => $html ) : ?>
				<input type="radio" name="<?php $this->field_name( 'title_separator' ); ?>" id="<?php $this->field_id( 'title_separator_' . $name ); ?>" value="<?php echo esc_attr( $name ); ?>" <?php checked( $default_title_separator, $name ); ?> />
				<label for="<?php $this->field_id( 'title_separator_' . $name ); ?>"><?php echo esc_html( $html ); ?></label>
			<?php endforeach; ?>
			</p>
		</fieldset>

		<hr>

		<h4><?php esc_html_e( 'Blog Name', 'autodescription' ); ?></h4>
		<div id="tsf-title-additions-toggle">
			<?php
			$info = $this->make_info(
				__( 'This might decouple your posts and pages from the rest of the website.', 'autodescription' ),
				'https://support.google.com/webmasters/answer/35624#page-titles',
				false
			);

			$this->wrap_fields(
				$this->make_checkbox(
					'title_rem_additions',
					esc_html__( 'Remove blog name from the title?', 'autodescription' ) . ' ' . $info,
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
		//* Get translated category label, if it exists. Otherwise, fallback to translation.
		$label = $this->get_tax_type_label( 'category', true ) ?: __( 'Category', 'default' );

		$cats = get_terms( [
			'taxonomy'   => 'category',
			'fields'     => 'ids',
			'hide_empty' => false,
			'order'      => 'ASC',
			'number'     => 1,
		] );
		if ( is_array( $cats ) && ! empty( $cats ) ) {
			//* Category should exist.
			$cat = reset( $cats );
		} else {
			//* Default fallback category.
			$cat = 1;
		}

		//* If cat is found, it will return its name. Otherwise it's an empty string.
		$cat_name = get_cat_name( $cat );
		$cat_name = $cat_name ?: __( 'Example Category', 'autodescription' );

		$title = sprintf(
			'<span class="tsf-title-prefix-example" style=display:%s>%s: </span> %s',
			$this->get_option( 'title_rem_prefixes' ) ? 'none' : 'inline',
			esc_html( $label ),
			esc_html( $cat_name )
		);

		$example_left  = '<em>' . $additions['left'] . $title . '</em>';
		$example_right = '<em>' . $title . $additions['right'] . '</em>';

		?>
		<h4><?php esc_html_e( 'Title Prefix Options', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'For archives, a descriptive prefix may be added to generated titles.', 'autodescription' ) );

		?>
		<h4><?php esc_html_e( 'Example Automated Archive Title Output', 'autodescription' ); ?></h4>
		<p>
			<span class="tsf-title-additions-example-left" style="display:<?php echo $showleft ? 'inline' : 'none'; ?>">
				<?php
				echo $this->code_wrap_noesc( $example_left );
				?>
			</span>
			<span class="tsf-title-additions-example-right" style="display:<?php echo $showleft ? 'none' : 'inline'; ?>">
				<?php
				echo $this->code_wrap_noesc( $example_right );
				?>
			</span>
		</p>

		<hr>

		<h4><?php esc_html_e( 'Archive Title Prefixes', 'autodescription' ); ?></h4>
		<div id="tsf-title-prefixes-toggle">
			<?php
			$info = $this->make_info(
				__( "The prefix helps visitors and search engines determine what kind of page they're visiting.", 'autodescription' ),
				'https://support.google.com/webmasters/answer/35624#page-titles',
				false
			);
			$this->wrap_fields(
				$this->make_checkbox(
					'title_rem_prefixes',
					esc_html__( 'Remove term type prefixes from title?', 'autodescription' ) . ' ' . $info,
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
