<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework;

( \defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

use The_SEO_Framework\Admin\Settings\Layout\{
	Form,
	HTML,
	Input,
};
use The_SEO_Framework\Helper\{
	Compatibility,
	Format\Markdown,
	Post_Type,
	Taxonomy,
};

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2016 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// See _title_metabox et al.
[ $instance ] = $view_args;

switch ( $instance ) :
	case 'main':
		HTML::header_title( \__( 'Schema.org Output Settings', 'autodescription' ) );

		if ( Compatibility::get_active_conflicting_plugin_types()['schema'] )
			HTML::attention_description( \__( 'Another Schema.org plugin has been detected. These markup settings might conflict.', 'autodescription' ) );

		HTML::description( \__( 'The Schema.org markup is a standard way of annotating structured data for search engines. This markup is represented within hidden scripts throughout the website.', 'autodescription' ) );
		HTML::description( \__( 'When your web pages include structured data markup, search engines can use that data to index your content better, present it more prominently in search results, and use it in several different applications.', 'autodescription' ) );
		HTML::description( \__( 'This is also known as the "Knowledge Graph" and "Structured Data", which is under heavy active development by several search engines. Therefore, the usage of the outputted markup is not guaranteed.', 'autodescription' ) );

		$tabs = [
			'general'     => [
				'name'     => \__( 'General', 'autodescription' ),
				'callback' => [ Admin\Settings\Plugin::class, '_schema_metabox_general_tab' ],
				'dashicon' => 'admin-generic',
			],
			'presence'    => [
				'name'     => \__( 'Presence', 'autodescription' ),
				'callback' => [ Admin\Settings\Plugin::class, '_schema_metabox_presence_tab' ],
				'dashicon' => 'networking',
			],
			'breadcrumbs' => [
				'name'     => \__( 'Breadcrumbs', 'autodescription' ),
				'callback' => [ Admin\Settings\Plugin::class, '_schema_metabox_breadcrumbs_tab' ],
				'dashicon' => 'ellipsis',
			],
		];

		Admin\Settings\Plugin::nav_tab_wrapper(
			'schema',
			/**
			 * @since 2.8.0
			 * @since 5.0.0 Removed the 'structure' index and added the 'general' index.
			 * @param array $defaults The default tabs.
			 */
			(array) \apply_filters( 'the_seo_framework_schema_settings_tabs', $tabs ),
		);
		break;

	case 'general':
		HTML::header_title( \__( 'Structured Data Output', 'autodescription' ) );
		HTML::description( \__( 'Output supplementary information about your site and every page, such as the title, description, URLs, and language, using a standard search engines can easily understand.', 'autodescription' ) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'    => 'ld_json_enabled',
				'label' => \__( 'Output structured data?', 'autodescription' ),
			] ),
			true,
		);

		?>
		<div id=tsf-advanced-structured-data-settings-wrapper>
			<hr>
			<?php
			HTML::header_title( \__( 'Advanced Structured Data', 'autodescription' ) );

			$info = HTML::make_info(
				\__( 'Learn how this data is used.', 'autodescription' ),
				'https://developers.google.com/search/docs/beginner/establish-business-details',
				false,
			);
			HTML::wrap_fields(
				Input::make_checkbox( [
					'id'          => 'knowledge_output',
					'label'       => \esc_html__( 'Add authorized presence?', 'autodescription' ) . " $info",
					'description' => \esc_html__( 'This tells search engines about the website ownership, its logo, and its social pages.', 'autodescription' ),
					'escape'      => false,
				] ),
				true,
			);

			$info = HTML::make_info(
				\__( 'Learn how this data is used.', 'autodescription' ),
				'https://developers.google.com/search/docs/advanced/structured-data/breadcrumb',
				false,
			);
			HTML::wrap_fields(
				Input::make_checkbox( [
					'id'          => 'ld_json_breadcrumbs',
					'label'       => \esc_html__( 'Add breadcrumbs?', 'autodescription' ) . " $info",
					'description' => \esc_html__( "Breadcrumbs help search engines understand the site's hierarchy.", 'autodescription' ),
					'escape'      => false,
				] ),
				true,
			);

			$info = HTML::make_info(
				\__( 'Learn how this data is used.', 'autodescription' ),
				'https://developers.google.com/search/docs/advanced/structured-data/sitelinks-searchbox',
				false,
			);
			HTML::wrap_fields(
				Input::make_checkbox( [
					'id'          => 'ld_json_searchbox',
					'label'       => \esc_html_x( 'Add Sitelinks Search Box?', 'Sitelinks Search Box is a product name', 'autodescription' ) . " $info",
					'description' => \esc_html__( "This tells search engines how to use the site's built-in search engine.", 'autodescription' ),
					'escape'      => false,
				] ),
				true,
			);
			?>
		</div>
		<?php
		break;

	case 'presence':
		HTML::header_title( \__( 'About this website', 'autodescription' ) );
		?>
		<p>
			<label for="<?php Input::field_id( 'knowledge_type' ); ?>"><?= \esc_html_x( 'This website represents:', '...Organization or Person.', 'autodescription' ) ?></label>
			<select name="<?php Input::field_name( 'knowledge_type' ); ?>" id="<?php Input::field_id( 'knowledge_type' ); ?>">
				<?php
				/**
				 * Values other than `organization` and `person` aren't saved.
				 *
				 * @since 2.4.3
				 * @param array $types The knowledge type options as option value => label.
				 */
				$knowledge_type = (array) \apply_filters(
					'the_seo_framework_knowledge_types',
					[
						'organization' => \__( 'An Organization', 'autodescription' ),
						'person'       => \__( 'A Person', 'autodescription' ),
					],
				);
				$_current       = Data\Plugin::get_option( 'knowledge_type' );
				foreach ( $knowledge_type as $value => $name )
					printf(
						'<option value="%s" %s>%s</option>',
						\esc_attr( $value ),
						\selected( $_current, \esc_attr( $value ), false ),
						\esc_html( $name ),
					);
				?>
			</select>
		</p>

		<p>
			<label for="<?php Input::field_id( 'knowledge_name' ); ?>">
				<strong><?php \esc_html_e( 'The organization or personal name', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p>
			<input type=text name="<?php Input::field_name( 'knowledge_name' ); ?>" class=large-text id="<?php Input::field_id( 'knowledge_name' ); ?>" placeholder="<?= \esc_attr( Data\Blog::get_public_blog_name() ) ?>" value="<?= \esc_attr( Data\Plugin::get_option( 'knowledge_name' ) ) ?>" autocomplete=off>
		</p>
		<div id=tsf-logo-structured-data-settings-wrapper>
			<hr>
			<?php
			HTML::header_title( \__( 'Organization logo', 'autodescription' ) );
			$info = HTML::make_info(
				\__( 'Learn how this data is used.', 'autodescription' ),
				'https://developers.google.com/search/docs/advanced/structured-data/logo',
				false,
			);
			HTML::wrap_fields(
				Input::make_checkbox( [
					'id'     => 'knowledge_logo',
					'label'  => \esc_html__( 'Add logo?', 'autodescription' ) . " $info",
					'escape' => false,
				] ),
				true,
			);

			$logo_placeholder = Meta\Image::get_first_generated_image_url( [ 'id' => 0 ], 'organization' );
			?>
			<div id=tsf-logo-upload-structured-data-settings-wrapper>
				<p>
					<label for=knowledge_logo-url>
						<strong><?php \esc_html_e( 'Logo URL', 'autodescription' ); ?></strong>
					</label>
				</p>
				<p>
					<input class=large-text type=url name="<?php Input::field_name( 'knowledge_logo_url' ); ?>" id=knowledge_logo-url placeholder="<?= \esc_url( $logo_placeholder ) ?>" value="<?= \esc_url( Data\Plugin::get_option( 'knowledge_logo_url' ) ) ?>">
					<input type=hidden name="<?php Input::field_name( 'knowledge_logo_id' ); ?>" id=knowledge_logo-id value="<?= \absint( Data\Plugin::get_option( 'knowledge_logo_id' ) ) ?>">
				</p>
				<p class=hide-if-no-tsf-js>
					<?php
					// phpcs:disable WordPress.Security.EscapeOutput -- already escaped.
					echo Form::get_image_uploader_form( [
						'id'   => 'knowledge_logo',
						'data' => [
							'inputType' => 'logo',
							'width'     => 512, // Magic number -> Google requirement? "MAGIC::GOOGLE->LOGO_MAX"?
							'height'    => 512, // Magic number
							'minWidth'  => 112, // Magic number -> Google requirement? "MAGIC::GOOGLE->LOGO_MIN"?
							'minHeight' => 112, // Magic number
							'flex'      => true,
						],
						'i18n' => [
							'button_title' => '',
							'button_text'  => \__( 'Select Logo', 'autodescription' ),
						],
					] );
					// phpcs:enable WordPress.Security.EscapeOutput
					?>
				</p>
			</div>
		</div>
		<?php

		$connectedi18n = \_x( 'RelatedProfile', 'No spaces. E.g. https://facebook.com/RelatedProfile', 'autodescription' );
		/**
		 * @todo maybe genericons?
		 */
		$socialsites = [
			'facebook'   => [
				'option'      => 'knowledge_facebook',
				'dashicon'    => 'dashicons-facebook',
				'desc'        => \__( 'Facebook Page', 'autodescription' ),
				'example'     => "https://www.facebook.com/$connectedi18n",
				'examplelink' => 'https://www.facebook.com/me',
			],
			'twitter'    => [
				'option'      => 'knowledge_twitter',
				'dashicon'    => 'dashicons-twitter',
				'desc'        => \__( 'X Profile', 'autodescription' ),
				'example'     => "https://x.com/$connectedi18n",
				'examplelink' => 'https://x.com/home', // No example link available.
			],
			'instagram'  => [
				'option'      => 'knowledge_instagram',
				'dashicon'    => 'genericon-instagram',
				'desc'        => \__( 'Instagram Profile', 'autodescription' ),
				'example'     => "https://instagram.com/$connectedi18n",
				'examplelink' => 'https://instagram.com/', // No example link available.
			],
			'youtube'    => [
				'option'      => 'knowledge_youtube',
				'dashicon'    => 'genericon-youtube',
				'desc'        => \__( 'Youtube Profile', 'autodescription' ),
				'example'     => "https://www.youtube.com/channel/$connectedi18n",
				'examplelink' => 'https://www.youtube.com/user/',
			],
			'linkedin'   => [
				'option'      => 'knowledge_linkedin',
				'dashicon'    => 'genericon-linkedin-alt',
				'desc'        => \__( 'LinkedIn Profile', 'autodescription' ),
				'example'     => "https://www.linkedin.com/in/$connectedi18n/",
				'examplelink' => 'https://www.linkedin.com/profile/view',
			],
			'pinterest'  => [
				'option'      => 'knowledge_pinterest',
				'dashicon'    => 'genericon-pinterest-alt',
				'desc'        => \__( 'Pinterest Profile', 'autodescription' ),
				'example'     => "https://www.pinterest.com/$connectedi18n/",
				'examplelink' => 'https://www.pinterest.com/me/',
			],
			'soundcloud' => [
				'option'      => 'knowledge_soundcloud',
				'dashicon'    => 'genericon-cloud', // I know, it's not the real one. D:
				'desc'        => \__( 'SoundCloud Profile', 'autodescription' ),
				'example'     => "https://soundcloud.com/$connectedi18n",
				'examplelink' => 'https://soundcloud.com/you',
			],
			'tumblr'     => [
				'option'      => 'knowledge_tumblr',
				'dashicon'    => 'genericon-tumblr',
				'desc'        => \__( 'Tumblr Blog', 'autodescription' ),
				'example'     => "https://www.tumblr.com/blog/$connectedi18n",
				'examplelink' => 'https://www.tumblr.com/dashboard',  // No example link available.
			],
		];

		?>
		<hr>
		<?php
		HTML::header_title( \__( 'Connected Social Pages', 'autodescription' ) );
		HTML::description( \__( 'Add links that lead directly to the connected social pages of this website.', 'autodescription' ) );
		HTML::description( \__( 'Leave the fields empty if the social pages are not publicly accessible.', 'autodescription' ) );
		HTML::description( \__( 'These settings do not affect sharing behavior with the social networks.', 'autodescription' ) );

		foreach ( $socialsites as $sc ) {
			?>
			<p>
				<label for="<?php Input::field_id( $sc['option'] ); ?>">
					<strong><?= \esc_html( $sc['desc'] ) ?></strong>
					<?php
					if ( $sc['examplelink'] ) {
						HTML::make_info(
							\__( 'View your profile.', 'autodescription' ),
							$sc['examplelink'],
						);
					}
					?>
				</label>
			</p>
			<p>
				<input type=url name="<?php Input::field_name( $sc['option'] ); ?>" class=large-text id="<?php Input::field_id( $sc['option'] ); ?>" value="<?= \esc_attr( Data\Plugin::get_option( $sc['option'] ) ) ?>" autocomplete=off>
			</p>
			<?php
			HTML::description_noesc( \sprintf(
				/* translators: %s = example value */
				\esc_html__( 'Example: %s', 'autodescription' ),
				HTML::code_wrap( $sc['example'] ),
			) );
		}
		break;

	case 'breadcrumbs':
		HTML::header_title( \__( 'Breadcrumbs Output Settings', 'autodescription' ) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'          => 'breadcrumb_use_meta_title',
				'label'       => \esc_html__( 'Use meta titles for breadcrumbs?', 'autodescription' ),
				'description' => \esc_html__( 'Meta titles are the custom SEO titles inputted via the SEO settings. If disabled, page titles from the editor will be used.', 'autodescription' ),
			] ),
			true,
		);

		$taxonomy_fields = [];
		$archive_fields  = [];

		foreach ( Post_Type::get_all_public() as $post_type ) {
			$pt_taxonomies = array_values( array_intersect(
				Taxonomy::get_hierarchical( 'names', $post_type ),
				Taxonomy::get_all_public(),
			) );

			if ( $pt_taxonomies ) {
				$options = [
					'' => \sprintf(
						/* translators: %s = taxonomy name */
						\__( 'Default (%s)', 'autodescription' ),
						Taxonomy::get_label( $pt_taxonomies[0], false ) ?: $pt_taxonomies[0],
					),
					-1 => \__( '— Exclude taxonomy crumb —', 'autodescription' ),
				];

				foreach ( $pt_taxonomies as $taxonomy )
					$options[ $taxonomy ] = Taxonomy::get_label( $taxonomy, false ) ?: $taxonomy;

				$field_id = Input::get_field_id( [
					'breadcrumb_taxonomy',
					$post_type,
				] );

				$taxonomy_fields[] = \sprintf(
					'<p><div class=tsf-select-block><label for="%1$s">%2$s &ndash; <code>%3$s</code></label> %4$s</div></p>',
					\esc_attr( $field_id ),
					\esc_html( Post_Type::get_label( $post_type, false ) ),
					\esc_html( $post_type ),
					Form::make_single_select_form( [
						'id'       => $field_id,
						'name'     => Input::get_field_name( [
							'breadcrumb_taxonomy',
							$post_type,
						] ),
						'selected' => Data\Plugin::get_option( 'breadcrumb_taxonomy', $post_type ) ?? '',
						'options'  => $options,
					] ),
				);
			}

			if ( \get_post_type_object( $post_type )->has_archive ?? false ) {
				$archive_fields[] = \sprintf(
					'<input type=hidden name="%s" value=0>',
					\esc_attr( Input::get_field_name( [
						'breadcrumb_archive',
						$post_type,
					] ) ),
				) . Input::make_checkbox( [
					'id'     => [
						'breadcrumb_archive',
						$post_type,
					],
					'label'  => \sprintf(
						'%s &ndash; <code>%s</code>',
						\esc_html( Post_Type::get_label( $post_type, false ) ),
						\esc_html( $post_type ),
					),
					'escape' => false,
					// A missing key includes the archive.
					'value'  => Data\Plugin::get_option( 'breadcrumb_archive', $post_type ) ?? 1,
				] );
			}
		}

		if ( $taxonomy_fields ) {
			echo '<hr>';
			HTML::header_title( \__( 'Taxonomy Crumb', 'autodescription' ) );
			HTML::description( \__( 'The primary term trail on singular pages.', 'autodescription' ) );
			HTML::wrap_fields( $taxonomy_fields, true );
		}

		if ( $archive_fields ) {
			echo '<hr>';
			HTML::header_title( \__( 'Post Type Archive Crumb', 'autodescription' ) );
			HTML::description( \__( 'Clear a checkbox to omit that archive from singular breadcrumbs.', 'autodescription' ) );
			HTML::wrap_fields( $archive_fields, true );
		}

		echo '<hr>';
		HTML::description_noesc(
			Markdown::convert(
				\sprintf(
					/* translators: %s = Documentation URL in Markdown */
					\esc_html__( 'You can also use a shortcode to output breadcrumbs. [Learn more](%s).', 'autodescription' ),
					'https://kb.theseoframework.com/?p=212',
				),
				[ 'a' ],
				[ 'a_internal' => false ],
			),
		);
endswitch;
