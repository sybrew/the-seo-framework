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
$instance = $this->get_view_instance( 'the_seo_framework_social_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_social_metabox_main':
		$default_tabs = [
			'general'   => [
				'name'     => __( 'General', 'autodescription' ),
				'callback' => SeoSettings::class . '::_social_metabox_general_tab',
				'dashicon' => 'admin-generic',
			],
			'facebook'  => [
				'name'     => 'Facebook',
				'callback' => SeoSettings::class . '::_social_metabox_facebook_tab',
				'dashicon' => 'facebook-alt',
			],
			'twitter'   => [
				'name'     => 'Twitter',
				'callback' => SeoSettings::class . '::_social_metabox_twitter_tab',
				'dashicon' => 'twitter',
			],
			'oembed'    => [
				'name'     => 'oEmbed',
				'callback' => SeoSettings::class . '::_social_metabox_oembed_tab',
				'dashicon' => 'share-alt2',
			],
			'postdates' => [
				'name'     => __( 'Post Dates', 'autodescription' ),
				'callback' => SeoSettings::class . '::_social_metabox_postdates_tab',
				'dashicon' => 'backup',
			],
		];

		/**
		 * @since 2.2.2
		 * @param array $defaults The default tabs.
		 * @param array $args     The args added on the callback.
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_social_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		SeoSettings::_nav_tab_wrapper( 'social', $tabs );
		break;

	case 'the_seo_framework_social_metabox_general':
		?>
		<h4><?php esc_html_e( 'Social Meta Tags Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Output various meta tags for social site integration, among other third-party services.', 'autodescription' ) );

		?>
		<hr>
		<?php

		// Echo Open Graph Tags checkboxes.
		$this->wrap_fields(
			$this->make_checkbox(
				'og_tags',
				__( 'Output Open Graph meta tags?', 'autodescription' ),
				__( 'Facebook, Twitter, Pinterest and many other social sites make use of these meta tags.', 'autodescription' ),
				true
			),
			true
		);
		if ( $this->detect_og_plugin() )
			$this->attention_description( __( 'Note: Another Open Graph plugin has been detected. These meta tags might conflict.', 'autodescription' ) );

		// Echo Facebook Tags checkbox.
		$this->wrap_fields(
			$this->make_checkbox(
				'facebook_tags',
				__( 'Output Facebook meta tags?', 'autodescription' ),
				__( 'Output various meta tags targeted at Facebook.', 'autodescription' ),
				true
			),
			true
		);

		// Echo Twitter Tags checkboxes.
		$this->wrap_fields(
			$this->make_checkbox(
				'twitter_tags',
				__( 'Output Twitter meta tags?', 'autodescription' ),
				__( 'Output various meta tags targeted at Twitter.', 'autodescription' ),
				true
			),
			true
		);
		if ( $this->detect_twitter_card_plugin() )
			$this->attention_description( __( 'Note: Another Twitter Card plugin has been detected. These meta tags might conflict.', 'autodescription' ) );

		// Echo oEmbed scripts checkboxes.
		$this->wrap_fields(
			$this->make_checkbox(
				'oembed_scripts',
				__( 'Output oEmbed scripts?', 'autodescription' ),
				__( 'WordPress, Discord, Drupal, Squarespace, and many other clients can make use of these scripts.', 'autodescription' ),
				true
			),
			true
		);
		?>
		<hr>

		<h4><?php esc_html_e( 'Social Title Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Most social sites and third-party services automatically include the website URL inside their embeds. When the site title is described well in the site URL, including it in the social title will be redundant.', 'autodescription' ) );

		$info = $this->make_info(
			__( 'When you provide a custom Open Graph or Twitter title, the site title will be omitted automatically.', 'autodescription' ),
			'',
			false
		);

		$this->wrap_fields(
			$this->make_checkbox(
				'social_title_rem_additions',
				esc_html__( 'Remove site title from generated social titles?', 'autodescription' ) . ' ' . $info,
				'',
				false
			),
			true
		);
		?>
		<hr>

		<h4><?php esc_html_e( 'Social Image Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'A social image can be displayed when your website is shared. It is a great way to grab attention.', 'autodescription' ) );

		$this->wrap_fields(
			$this->make_checkbox(
				'multi_og_image',
				__( 'Output multiple Open Graph image tags?', 'autodescription' ),
				__( 'This enables users to select any image attached to the page shared on social networks, like Facebook.', 'autodescription' ),
				true
			),
			true
		);
		?>
		<p>
			<label for="tsf_fb_socialimage-url">
				<strong><?php esc_html_e( 'Social Image Fallback URL', 'autodescription' ); ?></strong>
				<?php $this->make_info( __( 'When no image is available from the page or term, this fallback image will be used instead.', 'autodescription' ), 'https://developers.facebook.com/docs/sharing/best-practices#images' ); ?>
			</label>
		</p>
		<p>
			<input class="large-text" type="url" name="<?php $this->field_name( 'social_image_fb_url' ); ?>" id="tsf_fb_socialimage-url" value="<?php echo esc_url( $this->get_option( 'social_image_fb_url' ) ); ?>" />
			<input type="hidden" name="<?php $this->field_name( 'social_image_fb_id' ); ?>" id="tsf_fb_socialimage-id" value="<?php echo absint( $this->get_option( 'social_image_fb_id' ) ); ?>" disabled class="tsf-enable-media-if-js" />
		</p>
		<p class="hide-if-no-tsf-js">
			<?php
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
			echo $this->get_social_image_uploader_form( 'tsf_fb_socialimage' );
			?>
		</p>
		<hr>

		<h4><?php esc_html_e( 'Theme Color Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Discord styles embeds with the theme color. The theme color can also affect the tab-color in some browsers.', 'autodescription' ) );
		?>
		<p>
			<label for="<?php $this->field_id( 'theme_color' ); ?>">
				<strong><?php esc_html_e( 'Theme Color', 'autodescription' ); ?></strong>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'theme_color' ); ?>" class="tsf-color-picker" id="<?php $this->field_id( 'theme_color' ); ?>" value="<?php echo esc_attr( $this->get_option( 'theme_color' ) ); ?>" data-tsf-default-color="" />
		</p>
		<hr>

		<h4><?php esc_html_e( 'Site Shortlink Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'The shortlink tag can be manually used for microblogging services like Twitter, but it has no SEO value whatsoever.', 'autodescription' ) );

		$this->wrap_fields(
			$this->make_checkbox(
				'shortlink_tag',
				__( 'Output shortlink tag?', 'autodescription' ),
				'',
				true
			),
			true
		);
		break;

	case 'the_seo_framework_social_metabox_facebook':
		$fb_author             = $this->get_option( 'facebook_author' );
		$fb_author_placeholder = _x( 'https://www.facebook.com/YourPersonalProfile', 'Example Facebook Personal URL', 'autodescription' );

		$fb_publisher             = $this->get_option( 'facebook_publisher' );
		$fb_publisher_placeholder = _x( 'https://www.facebook.com/YourBusinessProfile', 'Example Facebook Business URL', 'autodescription' );

		$fb_appid             = $this->get_option( 'facebook_appid' );
		$fb_appid_placeholder = '123456789012345';

		?>
		<h4><?php esc_html_e( 'Facebook Integration Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Facebook post sharing works mostly through Open Graph. However, you can also link your Business and Personal Facebook pages, among various other options.', 'autodescription' ) );
		$this->description( __( 'When these options are filled in, Facebook might link the Facebook profile to be followed and liked when your post or page is shared.', 'autodescription' ) );
		?>
		<hr>

		<p>
			<label for="<?php $this->field_id( 'facebook_appid' ); ?>">
				<strong><?php esc_html_e( 'Facebook App ID', 'autodescription' ); ?></strong>
				<?php
				echo ' ';
				$this->make_info(
					__( 'Get Facebook App ID.', 'autodescription' ),
					'https://developers.facebook.com/apps'
				);
				?>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'facebook_appid' ); ?>" class="large-text ltr" id="<?php $this->field_id( 'facebook_appid' ); ?>" placeholder="<?php echo esc_attr( $fb_appid_placeholder ); ?>" value="<?php echo esc_attr( $fb_appid ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'facebook_publisher' ); ?>">
				<strong><?php esc_html_e( 'Facebook Publisher page', 'autodescription' ); ?></strong>
				<?php
				echo ' ';
				$this->make_info(
					__( 'Only Facebook Business Pages are accepted.', 'autodescription' ),
					'https://www.facebook.com/business/pages/set-up'
				);
				?>
			</label>
		</p>
		<p>
			<input type="url" name="<?php $this->field_name( 'facebook_publisher' ); ?>" class="large-text" id="<?php $this->field_id( 'facebook_publisher' ); ?>" placeholder="<?php echo esc_attr( $fb_publisher_placeholder ); ?>" value="<?php echo esc_attr( $fb_publisher ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'facebook_author' ); ?>">
				<strong><?php esc_html_e( 'Facebook Author Fallback Page', 'autodescription' ); ?></strong>
				<?php
				echo ' ';
				$this->make_info(
					__( 'Your Facebook profile.', 'autodescription' ),
					'https://facebook.com/me'
				);
				?>
			</label>
		</p>
		<?php $this->description( __( 'Authors can override this option on their profile page.', 'autodescription' ) ); ?>
		<p>
			<input type="url" name="<?php $this->field_name( 'facebook_author' ); ?>" class="large-text" id="<?php $this->field_id( 'facebook_author' ); ?>" placeholder="<?php echo esc_attr( $fb_author_placeholder ); ?>" value="<?php echo esc_attr( $fb_author ); ?>" />
		</p>
		<?php
		break;

	case 'the_seo_framework_social_metabox_twitter':
		$tw_site             = $this->get_option( 'twitter_site' );
		$tw_site_placeholder = _x( '@your-site-username', 'Twitter @username', 'autodescription' );

		$tw_creator             = $this->get_option( 'twitter_creator' );
		$tw_creator_placeholder = _x( '@your-personal-username', 'Twitter @username', 'autodescription' );

		$twitter_card = $this->get_twitter_card_types();

		?>
		<h4><?php esc_html_e( 'Twitter Integration Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Twitter post sharing works mostly through Twitter Cards, and may fall back to use Open Graph. However, you can also link your Business and Personal Twitter pages, among various other options.', 'autodescription' ) );

		?>
		<hr>

		<fieldset id="tsf-twitter-cards">
			<legend><h4><?php esc_html_e( 'Twitter Card Type', 'autodescription' ); ?></h4></legend>
			<?php
			$this->description(
				__( 'The Twitter Card type may have the image highlighted, either small at the side or large above.', 'autodescription' )
			);
			?>

			<p class="tsf-fields">
			<?php
			foreach ( $twitter_card as $type => $name ) {
				?>
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'twitter_card' ); ?>" id="<?php $this->field_id( 'twitter_card_' . $type ); ?>" value="<?php echo esc_attr( $type ); ?>" <?php checked( $this->get_option( 'twitter_card' ), $type ); ?> />
					<label for="<?php $this->field_id( 'twitter_card_' . $type ); ?>">
						<span>
							<?php
							echo $this->code_wrap( $name ); // phpcs:ignore, WordPress.Security.EscapeOutput
							echo ' ';
							$this->make_info(
								__( 'Learn more about this card.', 'autodescription' ),
								'https://dev.twitter.com/cards/types/' . $name
							);
							?>
						</span>
					</label>
				</span>
				<?php
			}
			?>
			</p>
		</fieldset>

		<hr>

		<h4><?php esc_html_e( 'Card and Content Attribution', 'autodescription' ); ?></h4>
		<?php
		/* source: https://developer.twitter.com/en/docs/tweets/optimize-with-cards/guides/getting-started#attribution */
		$this->description( __( 'Twitter claims users will be able to follow and view the profiles of attributed accounts directly from the card when these fields are filled in.', 'autodescription' ) );
		$this->description( __( 'However, for now, these fields seem to have no discernible effect.', 'autodescription' ) );
		?>

		<p>
			<label for="<?php $this->field_id( 'twitter_site' ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Website Twitter Profile', 'autodescription' ); ?></strong>
				<?php
				echo ' ';
				$this->make_info(
					__( 'Find your @username.', 'autodescription' ),
					'https://twitter.com/home'
				);
				?>
			</label>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'twitter_site' ); ?>" class="large-text ltr" id="<?php $this->field_id( 'twitter_site' ); ?>" placeholder="<?php echo esc_attr( $tw_site_placeholder ); ?>" value="<?php echo esc_attr( $tw_site ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'twitter_creator' ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Twitter Author Fallback Profile', 'autodescription' ); ?></strong>
				<?php
				echo ' ';
				$this->make_info(
					__( 'Find your @username.', 'autodescription' ),
					'https://twitter.com/home'
				);
				?>
			</label>
		</p>
		<?php $this->description( __( 'Authors can override this option on their profile page.', 'autodescription' ) ); ?>
		<p>
			<input type="text" name="<?php $this->field_name( 'twitter_creator' ); ?>" class="large-text ltr" id="<?php $this->field_id( 'twitter_creator' ); ?>" placeholder="<?php echo esc_attr( $tw_creator_placeholder ); ?>" value="<?php echo esc_attr( $tw_creator ); ?>" />
		</p>
		<?php
		break;

	case 'the_seo_framework_social_metabox_oembed':
		?>
		<h4><?php esc_html_e( 'oEmbed Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Some social sharing services and clients, like WordPress, LinkedIn, and Discord, obtain the linked page information via oEmbed.', 'autodescription' ) );
		?>
		<hr>
		<?php

		// Split the wraps--the informational messages make for bad legibility otherwise.
		$this->wrap_fields(
			$this->make_checkbox(
				'oembed_use_og_title',
				__( 'Use Open Graph title?', 'autodescription' ),
				__( 'Check this option if you want to replace page titles with Open Graph titles in embeds.', 'autodescription' ),
				true
			),
			true
		);
		$_info = $this->make_info(
			__( 'Only custom social images that are selected via the Media Library are considered.', 'autodescription' ),
			'',
			false
		);
		$this->wrap_fields(
			$this->make_checkbox(
				'oembed_use_social_image',
				esc_html__( 'Use social image?', 'autodescription' ) . ' ' . $_info,
				esc_html__( "LinkedIn displays the post's featured image in embeds. Check this option if you want to replace it with the social image.", 'autodescription' ),
				false
			),
			true
		);
		$this->wrap_fields(
			$this->make_checkbox(
				'oembed_remove_author',
				__( 'Remove author name?', 'autodescription' ),
				__( "Discord shows the page author's name above the sharing embed. Check this option if you find this undesirable.", 'autodescription' ),
				true
			),
			true
		);

		break;
	case 'the_seo_framework_social_metabox_postdates':
		$posts_i18n = esc_html__( 'Posts', 'autodescription' );

		?>
		<h4><?php esc_html_e( 'Post Date Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( "Some social sites output the shared post's publishing and modified data in the sharing snippet.", 'autodescription' ) );
		?>
		<hr>
		<?php

		$this->wrap_fields(
			[
				$this->make_checkbox(
					'post_publish_time',
					$this->convert_markdown(
						/* translators: the backticks are Markdown! Preserve them as-is! */
						esc_html__( 'Add `article:published_time` to posts?', 'autodescription' ),
						[ 'code' ]
					),
					'',
					false
				),
				$this->make_checkbox(
					'post_modify_time',
					$this->convert_markdown(
						/* translators: the backticks are Markdown! Preserve them as-is! */
						esc_html__( 'Add `article:modified_time` to posts?', 'autodescription' ),
						[ 'code' ]
					),
					'',
					false
				),
			],
			true
		);
		break;

	default:
		break;
endswitch;
