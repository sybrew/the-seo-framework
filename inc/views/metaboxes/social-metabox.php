<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Views\Metaboxes
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_social_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_social_metabox_main':
		/**
		 * Parse tabs content.
		 *
		 * @since 2.2.2
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *   array(
		 *       'name'     => The name
		 *       'callback' => The callback function, use array for method calling
		 *       'dashicon' => Desired dashicon
		 *   )
		 * }
		 */
		$default_tabs = [
			'general' => [
				'name'     => __( 'General', 'autodescription' ),
				'callback' => [ $this, 'social_metabox_general_tab' ],
				'dashicon' => 'admin-generic',
			],
			'facebook' => [
				'name'     => 'Facebook',
				'callback' => [ $this, 'social_metabox_facebook_tab' ],
				'dashicon' => 'facebook-alt',
			],
			'twitter' => [
				'name'     => 'Twitter',
				'callback' => [ $this, 'social_metabox_twitter_tab' ],
				'dashicon' => 'twitter',
			],
			'postdates' => [
				'name'     => __( 'Post Dates', 'autodescription' ),
				'callback' => [ $this, 'social_metabox_postdates_tab' ],
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

		$this->nav_tab_wrapper( 'social', $tabs, '2.2.2' );
		break;

	case 'the_seo_framework_social_metabox_general':
		?>
		<h4><?php esc_html_e( 'Social Meta Tags Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Output various meta tags for social site integration, among other 3rd party services.', 'autodescription' ) );

		?>
		<hr>
		<?php

		//* Echo Open Graph Tags checkboxes.
		$this->wrap_fields(
			$this->make_checkbox(
				'og_tags',
				__( 'Output Open Graph meta tags?', 'autodescription' ),
				__( 'Facebook, Twitter, Pinterest and many other social sites make use of these tags.', 'autodescription' ),
				true
			),
			true
		);

		if ( $this->detect_og_plugin() )
			$this->attention_description( __( 'Note: Another Open Graph plugin has been detected. These tags might conflict.', 'autodescription' ) );

		//* Echo Facebook Tags checkbox.
		$this->wrap_fields(
			$this->make_checkbox(
				'facebook_tags',
				__( 'Output Facebook meta tags?', 'autodescription' ),
				/* translators: %s = Facebook */
				sprintf( __( 'Output various tags targeted at %s.', 'autodescription' ), 'Facebook' ),
				true
			),
			true
		);

		//* Echo Twitter Tags checkboxes.
		$this->wrap_fields(
			$this->make_checkbox(
				'twitter_tags',
				__( 'Output Twitter meta tags?', 'autodescription' ),
				/* translators: %s = Facebook */
				sprintf( __( 'Output various tags targeted at %s.', 'autodescription' ), 'Twitter' ),
				true
			),
			true
		);

		if ( $this->detect_twitter_card_plugin() )
			$this->attention_description( __( 'Note: Another Twitter Card plugin has been detected. These tags might conflict.', 'autodescription' ) );

		?>
		<hr>

		<h4><?php esc_html_e( 'Social Image Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'A social image can be displayed when your website is shared. It is a great way to grab attention.', 'autodescription' ) );

		$image_placeholder = $this->get_social_image( [
			'post_id'    => 0,
			'disallowed' => [ 'homemeta', 'postmeta', 'featured' ],
			'escape'     => false,
		] );

		?>
		<p>
			<label for="tsf_fb_socialimage-url">
				<strong><?php esc_html_e( 'Social Image Fallback URL', 'autodescription' ); ?></strong>
				<?php $this->make_info( __( 'Set preferred Social Image fallback URL location.', 'autodescription' ), 'https://developers.facebook.com/docs/sharing/best-practices#images' ); ?>
			</label>
		</p>
		<p>
			<input class="large-text" type="url" name="<?php $this->field_name( 'social_image_fb_url' ); ?>" id="tsf_fb_socialimage-url" placeholder="<?php echo esc_url( $image_placeholder ); ?>" value="<?php echo esc_url( $this->get_option( 'social_image_fb_url' ) ); ?>" />
			<input type="hidden" name="<?php $this->field_name( 'social_image_fb_id' ); ?>" id="tsf_fb_socialimage-id" value="<?php echo absint( $this->get_option( 'social_image_fb_id' ) ); ?>" disabled class="tsf-enable-media-if-js" />
		</p>
		<p class="hide-if-no-js">
			<?php
			//* Already escaped.
			echo $this->get_social_image_uploader_form( 'tsf_fb_socialimage' );
			?>
		</p>
		<hr>

		<h4><?php esc_html_e( 'Site Shortlink Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'The shortlink tag can be manually used for microblogging services like Twitter, but it has no SEO value whatsoever.', 'autodescription' ) );

		//* Echo checkboxes.
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
		$fb_author = $this->get_option( 'facebook_author' );
		$fb_author_placeholder = _x( 'https://www.facebook.com/YourPersonalProfile', 'Example Facebook Personal URL', 'autodescription' );

		$fb_publisher = $this->get_option( 'facebook_publisher' );
		$fb_publisher_placeholder = _x( 'https://www.facebook.com/YourBusinessProfile', 'Example Facebook Business URL', 'autodescription' );

		$fb_appid = $this->get_option( 'facebook_appid' );
		$fb_appid_placeholder = '123456789012345';

		?>
		<h4><?php esc_html_e( 'Default Facebook Integration Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Facebook post sharing works mostly through Open Graph. However, you can also link your Business and Personal Facebook pages, among various other options.', 'autodescription' ) );
		$this->description( __( 'When these options are filled in, Facebook might link the Facebook profile to be followed and liked when your post or page is shared.', 'autodescription' ) );
		?>
		<hr>

		<p>
			<label for="<?php $this->field_id( 'facebook_appid' ); ?>">
				<strong><?php esc_html_e( 'Facebook App ID', 'autodescription' ); ?></strong>
			</label>
			<?php
			$this->make_info(
				__( 'Get Facebook App ID.', 'autodescription' ),
				'https://developers.facebook.com/apps'
			);
			?>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'facebook_appid' ); ?>" class="large-text ltr" id="<?php $this->field_id( 'facebook_appid' ); ?>" placeholder="<?php echo esc_attr( $fb_appid_placeholder ); ?>" value="<?php echo esc_attr( $fb_appid ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'facebook_publisher' ); ?>">
				<strong><?php esc_html_e( 'Facebook Publisher page', 'autodescription' ); ?></strong>
			</label>
			<?php
			$this->make_info(
				__( 'Only Facebook Business Pages are accepted.', 'autodescription' ),
				'https://www.facebook.com/business/learn/set-up-facebook-page'
			);
			?>
		</p>
		<p>
			<input type="url" name="<?php $this->field_name( 'facebook_publisher' ); ?>" class="large-text" id="<?php $this->field_id( 'facebook_publisher' ); ?>" placeholder="<?php echo esc_attr( $fb_publisher_placeholder ); ?>" value="<?php echo esc_attr( $fb_publisher ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'facebook_author' ); ?>">
				<strong><?php esc_html_e( 'Facebook Author Fallback Page', 'autodescription' ); ?></strong>
			</label>
			<?php
			$this->make_info(
				__( 'Your Facebook profile.', 'autodescription' ),
				'https://facebook.com/me'
			);
			?>
		</p>
		<?php $this->description( __( 'Authors can override this option on their profile page.', 'autodescription' ) ); ?>
		<p>
			<input type="url" name="<?php $this->field_name( 'facebook_author' ); ?>" class="large-text" id="<?php $this->field_id( 'facebook_author' ); ?>" placeholder="<?php echo esc_attr( $fb_author_placeholder ); ?>" value="<?php echo esc_attr( $fb_author ); ?>" />
		</p>
		<?php
		break;

	case 'the_seo_framework_social_metabox_twitter':
		$tw_site = $this->get_option( 'twitter_site' );
		$tw_site_placeholder = _x( '@your-site-username', 'Twitter @username', 'autodescription' );

		$tw_creator = $this->get_option( 'twitter_creator' );
		$tw_creator_placeholder = _x( '@your-personal-username', 'Twitter @username', 'autodescription' );

		$twitter_card = $this->get_twitter_card_types();

		?>
		<h4><?php esc_html_e( 'Default Twitter Integration Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Twitter post sharing works mostly through Open Graph. However, you can also link your Business and Personal Twitter pages, among various other options.', 'autodescription' ) );

		?>
		<hr>

		<fieldset id="tsf-twitter-cards">
			<legend><h4><?php esc_html_e( 'Twitter Card Type', 'autodescription' ); ?></h4></legend>
			<?php
			$this->description_noesc(
				sprintf(
					/* translators: %s = "summary" Twitter card type */
					esc_html__( 'What kind of Twitter card would you like to use? It will default to %s if no image is found.', 'autodescription' ),
					$this->code_wrap( 'summary' )
				)
			);
			?>

			<p class="tsf-fields">
			<?php
			foreach ( $twitter_card as $type => $name ) {
				?>
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'twitter_card' ); ?>" id="<?php $this->field_id( 'twitter_card_' . $type ); ?>" value="<?php echo esc_attr( $type ); ?>" <?php checked( $this->get_option( 'twitter_card' ), $type ); ?> />
					<label for="<?php $this->field_id( 'twitter_card_' . $type ); ?>">
						<span><?php echo $this->code_wrap( $name ); ?></span>
						<a class="description" href="<?php echo esc_url( 'https://dev.twitter.com/cards/types/' . $name ); ?>" target="_blank" title="Twitter Card <?php echo esc_attr( $name ) . ' ' . esc_attr__( 'Example', 'autodescription' ); ?>"><?php esc_html_e( 'Example', 'autodescription' ); ?></a>
					</label>
				</span>
				<?php
			}
			?>
			</p>
		</fieldset>

		<hr>

		<?php
		$this->description( __( 'When the following options are filled in, Twitter might link your Twitter Site or Author Profile when your post or page is shared.', 'autodescription' ) );
		?>

		<p>
			<label for="<?php $this->field_id( 'twitter_site' ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Website Twitter Profile', 'autodescription' ); ?></strong>
			</label>
			<?php
			$this->make_info(
				__( 'Find your @username.', 'autodescription' ),
				'https://twitter.com/home'
			);
			?>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'twitter_site' ); ?>" class="large-text ltr" id="<?php $this->field_id( 'twitter_site' ); ?>" placeholder="<?php echo esc_attr( $tw_site_placeholder ); ?>" value="<?php echo esc_attr( $tw_site ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'twitter_creator' ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Twitter Author Fallback Profile', 'autodescription' ); ?></strong>
			</label>
			<?php
			$this->make_info(
				__( 'Find your @username.', 'autodescription' ),
				'https://twitter.com/home'
			);
			?>
		</p>
		<?php $this->description( __( 'Authors can override this option on their profile page.', 'autodescription' ) ); ?>
		<p>
			<input type="text" name="<?php $this->field_name( 'twitter_creator' ); ?>" class="large-text ltr" id="<?php $this->field_id( 'twitter_creator' ); ?>" placeholder="<?php echo esc_attr( $tw_creator_placeholder ); ?>" value="<?php echo esc_attr( $tw_creator ); ?>" />
		</p>
		<?php
		break;

	case 'the_seo_framework_social_metabox_postdates':
		$posts_i18n = esc_html__( 'Posts', 'autodescription' );
		$home_i18n = esc_html__( 'Home Page', 'autodescription' );

		?>
		<h4><?php esc_html_e( 'Post Date Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Some social sites output the published date and modified date in the sharing snippet.', 'autodescription' ) );
		?>
		<hr>
		<?php

		/* translators: 1: Option, 2: Post Type */
		$post_publish_time_label = sprintf( esc_html__( 'Add %1$s to %2$s?', 'autodescription' ), $this->code_wrap( 'article:published_time' ), $posts_i18n );
		$post_publish_time_checkbox = $this->make_checkbox( 'post_publish_time', $post_publish_time_label, '', false );

		/* translators: 1: Option, 2: Post Type */
		$post_modify_time_label = sprintf( esc_html__( 'Add %1$s to %2$s?', 'autodescription' ), $this->code_wrap( 'article:modified_time' ), $posts_i18n );
		$post_modify_time_checkbox = $this->make_checkbox( 'post_modify_time', $post_modify_time_label, '', false );

		//* Echo checkboxes.
		$this->wrap_fields( $post_publish_time_checkbox . $post_modify_time_checkbox, true );
		break;

	default:
		break;
endswitch;
