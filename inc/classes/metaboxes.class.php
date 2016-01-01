<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class AutoDescription_Metaboxes
 *
 * Outputs Network and Site SEO settings meta boxes
 *
 * @since 2.2.2
 */
class AutoDescription_Metaboxes extends AutoDescription_Networkoptions {

	/**
	 * List of Title Separators
	 *
	 * @since 2.2.2
	 *
	 * @var array Title Separator list
	 *
	 * @fixed Typo (seperator -> separator)
	 * @since 2.3.4
	 */
	protected $title_separator = array();

	/**
	 * List of Twitter Card types
	 *
	 * @since 2.2.2
	 *
	 * @var array Twitter Card types
	 */
	protected $twitter_card = array();

	/**
	 * Constructor, load parent constructor
	 *
	 * Cache various variables.
	 */
	public function __construct() {
		parent::__construct();

		$this->title_separator = array(
			'pipe'		=> '|',
			'dash'		=> '-',
			'ndash'		=> '&ndash;',
			'mdash'		=> '&mdash;',
			'bull'		=> '&bull;',
			'middot'	=> '&middot;',
			'lsaquo'	=> '&lsaquo;',
			'rsaquo'	=> '&rsaquo;',
			'frasl'		=> '&frasl;',
			'laquo'		=> '&laquo;',
			'raquo'		=> '&raquo;',
			'le'		=> '&le;',
			'ge'		=> '&ge;',
			'lt'		=> '&lt;',
			'gt'		=> '&gt;',
		);

		$this->twitter_card = array(
			'summary' 				=> 'summary',
			'summary_large_image'	=> 'summary-large-image',
			'photo' 				=> 'photo',
		);
	}

	/**
	 * Setting nav tab wrappers.
	 * Outputs Tabs and settings content.
	 *
	 * @param string $id The Nav Tab ID
	 * @param array $tabs the tab content {
	 *		$tabs = tab ID key = array(
	 *			$tabs['name'] => tab name
	 *			$tabs['callback'] => string|array callback function
	 *			$tabs['dashicon'] => string Dashicon
	 *			$tabs['args'] => mixed optional callback function args
	 *		)
	 *	}
	 * @param string $version the The SEO Framework version
	 *
	 * @since 2.3.6
	 */
	public function nav_tab_wrapper( $id, $tabs = array(), $version = '2.3.6' ) {
		/**
		 * Start navigation
		 */
		?>
		<h3 class="nav-tab-wrapper hide-if-no-js" id="<?php echo $id; ?>-tabs-js">
		<?php
			$count = 1;
			foreach ( $tabs as $tab => $value ) {

				$dashicon = isset( $value['dashicon'] ) ? $value['dashicon'] : '';
				$name = isset( $value['name'] ) ? $value['name'] : '';

				?>
				<span>
					<input type="radio" class="<?php echo $id; ?>-tabs-radio" id="<?php echo $id; ?>-tab-<?php echo $tab ?>" name="<?php echo $id; ?>-tabs" <?php echo $count == abs(1) ? 'checked' : ''; ?>>
					<label for="<?php echo $id; ?>-tab-<?php echo $tab ?>" class="nav-tab <?php echo $count == abs(1) ? 'nav-tab-active' : '' ?>">
						<?php echo !empty( $dashicon ) ? '<span class="dashicons dashicons-' . esc_attr( $dashicon ) . ' dashicons-tabs"></span>' : ''; ?>
						<?php echo !empty( $name ) ? '<span class="seoframework-nav-desktop">' . esc_attr( $name ) . '</span>' : ''; ?>
					</label>
				</span>
				<?php

				$count++;
			}
		?>
		</h3>
		<?php

		/**
		 * Start settings content
		 */
		$_count = 1;
		foreach ( $tabs as $tab => $value ) {

			$dashicon = isset( $value['dashicon'] ) ? $value['dashicon'] : '';
			$name = isset( $value['name'] ) ? $value['name'] : '';

			?>
			<div class="<?php echo $id; ?>-tab-content <?php echo $_count == abs(1) ? 'checked-tab' : ''; ?> <?php echo $_count != abs(1) ? 'hide-if-js' : ''; ?>" id="<?php echo $id; ?>-tab-<?php echo $tab ?>-box">
				<h3 class="nav-tab-wrapper hide-if-js">
					<span class="nav-tab nav-tab-active">
						<?php echo !empty( $dashicon ) ? '<span class="dashicons dashicons-' . esc_attr( $dashicon ) . ' dashicons-tabs"></span>' : ''; ?>
						<?php
						// This is no-javascript
						echo !empty( $name ) ? esc_attr( $name ) : '';
						?>
					</span>
				</h3>
			<?php
				$callback = isset( $value['callback'] ) ? $value['callback'] : '';

				if ( !empty( $callback ) ) {
					$params = isset( $value['args'] ) ? $value['args'] : '';
					$output = $this->call_function( $callback, $version, $params );
					echo $output;
				}

			?>
			</div>
			<?php

			$_count++;
		}
	}

	/**
	 * Title meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.2
	 *
	 * @see $this->title_metabox()	Callback for Title Settings box.
	 */
	public function title_metabox() {

		do_action( 'the_seo_framework_title_metabox_before' );

		$title_separator = $this->title_separator;

		$example_left = '';
		$example_right = '';
		$recommended = ' class="recommended" title="' . __( 'Recommended', 'autodescription' ) . '"';

		$latest_post_id = $this->get_latest_post_id();

		if ( !empty( $latest_post_id ) ) {
			$post = get_post( (int) $latest_post_id, OBJECT );
			$title = esc_attr( $post->post_title );
		} else {
			$title = __( 'Example Post Title', 'autodescription' );
		}

		$blogname = get_bloginfo( 'name', 'display' );

		$sep_option = $this->get_field_value( 'title_seperator' ); // Note: typo.
		$sep = array_search( $sep_option, array_flip( $title_separator ), false );

		$example_left = '<em>' . $blogname . '<span class="autodescription-sep-js"> ' . $sep . ' </span>' . $title . '</em>';
		$example_right = '<em>' . $title . '<span class="autodescription-sep-js"> ' . $sep . ' </span>' . $blogname . '</em>';

		?>
		<fieldset>
			<legend><h4><?php _e( 'Document Title Separator', 'autodescription' ); ?></h4></legend>
			<p id="title-separator" class="fields">
			<?php foreach ( $title_separator as $name => $html ) { ?>
				<input type="radio" name="<?php $this->field_name( 'title_seperator' ); ?>" id="<?php $this->field_id( 'title_seperator_' . $name ); ?>" value="<?php echo $name ?>" <?php checked( $this->get_field_value( 'title_seperator' ), $name ); ?> />
				<label for="<?php $this->field_id( 'title_seperator_' . $name ); ?>" <?php echo ( $name == 'pipe' || $name == 'dash' ) ? $recommended : ''; ?>><?php echo $html ?></label>
			<?php } ?>
			</p>
			<span class="description"><?php _e( 'If the title consists of two parts (original title and optional addition), then the separator will go in between them.', 'autodescription' ); ?></span>
		</fieldset>

		<hr>

		<fieldset>
			<legend><h4><?php _e( 'Document Title Additions Location', 'autodescription' ); ?></h4></legend>
			<span class="description"><?php _e( 'Determines which side the added title text will go on.', 'autodescription' ); ?></span>

			<p id="title-location" class="fields">
				<span>
					<input type="radio" name="<?php $this->field_name( 'title_location' ); ?>" id="<?php $this->field_id( 'title_location_left' ); ?>" value="left" <?php checked( $this->get_field_value( 'title_location' ), 'left' ); ?> />
					<label for="<?php $this->field_id( 'title_location_left' ); ?>">
						<span><?php _e( 'Left:', 'autodescription' ); ?></span>
						<?php echo ( $example_left ) ? $this->code_wrap_noesc( $example_left ) : ''; ?>
					</label>
				</span>
				<span>
					<input type="radio" name="<?php $this->field_name( 'title_location' ); ?>" id="<?php $this->field_id( 'title_location_right' ); ?>" value="right" <?php checked( $this->get_field_value( 'title_location' ), 'right' ); ?> />
					<label for="<?php $this->field_id( 'title_location_right' ); ?>">
						<span><?php _e( 'Right:', 'autodescription' ); ?></span>
						<?php echo ( $example_right ) ? $this->code_wrap_noesc( $example_right ) : ''; ?>
					</label>
				</span>
			</p>
			<span class="description"><?php _e( 'The Home Page has a specific option.', 'autodescription' ); ?></span>
		</fieldset>

		<?php

		do_action( 'the_seo_framework_title_metabox_after' );
	}

	/**
	 * Description meta box on the Site SEO Settings page.
	 *
	 * @since 2.3.4
	 *
	 * @uses globals $wpdb fetch post for the example
	 *
	 * @see $this->description_metabox()	Callback for Description Settings box.
	 */
	public function description_metabox() {
		global $wpdb,$blog_id;

		do_action( 'the_seo_framework_description_metabox_before' );

		$language = $this->google_language();

		//* Let's use the same separators as for the title.
		$description_separator = $this->title_separator;

		$recommended = ' class="recommended" title="' . __( 'Recommended', 'autodescription' ) . '"';

		$blogname = get_bloginfo( 'name', 'display' );

		$sep_option = $this->get_field_value( 'description_separator' );
		$sep_from_options = $this->get_option( 'description_separator' );

		// Let's set a default.
		$sep_option = $sep_from_options ? $sep_option : 'pipe';

		$sep = array_search( $sep_option, array_flip( $description_separator ), false );

		/**
		 * Generate example.
		 */
		$page_title = __( 'Example Title', 'autodescription' );
		$on = _x( 'on', 'Placement. e.g. Post Title "on" Blog Name', 'autodescription' );
		$excerpt = __( 'This is an example description&#8230;', 'autodescription' );

		//* Put it together.
		$example 	= $page_title
					. '<span class="on-blogname-js">' . " $on " . $blogname . '</span>'
					. '<span class="autodescription-descsep-js">' . " $sep " . '</span>'
					. $excerpt
					;

		/**
		 * Generate no-JS example
		 * Fetch description additions.
		 */
		$description_additions = $this->get_option( 'description_blogname' );

		//* Add or remove additions based on option.
		$example_nojs_onblog = $description_additions ? " $on " . $blogname : '';

		$example_nojs = $page_title . $example_nojs_onblog . " $sep " . $excerpt;

		?>
		<h4><?php _e( 'Example Description Output', 'autodescription' ); ?></h4>
		<p class="hide-if-no-js"><?php echo $this->code_wrap_noesc( $example ); ?></p>
		<p class="hide-if-js"><?php echo $this->code_wrap( $example_nojs ); ?></p>

		<hr>

		<fieldset>
			<legend><h4><?php _e( 'Description Excerpt Separator', 'autodescription' ); ?></h4></legend>
			<p id="description-separator" class="fields">
			<?php foreach ( $description_separator as $name => $html ) { ?>
				<input type="radio" name="<?php $this->field_name( 'description_separator' ); ?>" id="<?php $this->field_id( 'description_separator' . $name ); ?>" value="<?php echo $name ?>" <?php checked( $sep_option, $name ); ?> />
				<label for="<?php $this->field_id( 'description_separator' . $name ); ?>" <?php echo ( $name == 'pipe' || $name == 'dash' ) ? $recommended : ''; ?>><?php echo $html ?></label>
			<?php } ?>
			</p>
			<span class="description"><?php _e( 'If the Automated Description consists of two parts (Title and excerpt), then the separator will go in between them.', 'autodescription' ); ?></span>
		</fieldset>

		<hr>

		<h4><?php _e( 'Add Blogname to Description', 'autodescription' ); ?></h4>
		<p id="description-onblogname-toggle">
			<label for="<?php $this->field_id( 'description_blogname' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'description_blogname' ); ?>" id="<?php $this->field_id( 'description_blogname' ); ?>" <?php $this->is_conditional_checked( 'description_blogname' ); ?> value="1" <?php checked( $this->get_field_value( 'description_blogname' ) ); ?> />
				<?php _e( 'Add blogname to automated description?', 'autodescription' ); ?>
			</label>
		</p>
		<?php

		do_action( 'the_seo_framework_description_metabox_after' );
	}

	/**
	 * Robots meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.2
	 *
	 * @see $this->robots_metabox()      Callback for Robots Settings box.
	 */
	public function robots_metabox( $args = array() ) {

		do_action( 'the_seo_framework_robots_metabox_before' );

		//* Robots types
		$types = array(
			'category' => __( 'Category', 'autodescription'),
			'tag' => __( 'Tag', 'autodescription'),
			'author' => __( 'Author', 'autodescription'),
			'date' => __( 'Date', 'autodescription'),
			'search' => __( 'Search Pages', 'autodescription'),
			'attachment' => __( 'Attachment Pages', 'autodescription'),
			'site' => __( 'the entire site', 'autodescription'),
		);

		//* Robots i18n
		$robots = array(
			'noindex' =>  array(
				'value' => 'noindex',
				'name' 	=> __( 'NoIndex', 'autodescription'),
				'desc' 	=> __( 'These options prevent indexing of the selected archives. If you enable this, the selected archives will be removed from search engine result pages.', 'autodescription' ),
			),
			'nofollow' =>  array(
				'value' => 'nofollow',
				'name'	=> __( 'NoFollow', 'autodescription'),
				'desc'	=> __( 'These options prevent links from being followed on the selected archives. If you enable this, the selected archives in-page links will gain no SEO value, including your own links.', 'autodescription' ),
			),
			'noarchive' =>  array(
				'value' => 'noarchive',
				'name'	=> __( 'NoArchive', 'autodescription'),
				'desc'	=> __( 'These options prevent caching of the selected archives. If you enable this, search engines will not create a cached copy of the selected archives.', 'autodescription' ),
			),
		);

		/**
		 * Parse tabs content
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *			array(
		 *				'name' 		=> The name
		 *				'callback'	=> function callback
		 *				'dashicon'	=> WordPress Dashicon
		 *				'args'		=> function args
		 *			)
		 * }
		 *
		 * @since 2.2.2
		 */
		$default_tabs = array(
				'general' => array(
					'name' 		=> __( 'General', 'autodescription' ),
					'callback'	=> array( $this, 'robots_metabox_general_tab' ),
					'dashicon'	=> 'admin-generic',
					'args'		=> '',
				),
				'index' => array(
					'name' 		=> __( 'Indexing', 'autodescription' ),
					'callback'	=> array( $this, 'robots_metabox_no_tab' ),
					'dashicon'	=> 'filter',
					'args'		=> array( $types, $robots['noindex'] ),
				),
				'follow' => array(
					'name'		=> __( 'Following', 'autodescription'),
					'callback'	=> array( $this, 'robots_metabox_no_tab' ),
					'dashicon'	=> 'editor-unlink',
					'args'		=> array( $types, $robots['nofollow'] ),
				),
				'archive' => array(
					'name'		=> __( 'Archiving', 'autodescription'),
					'callback'	=> array( $this, 'robots_metabox_no_tab' ),
					'dashicon'	=> 'download',
					'args'		=> array( $types, $robots['noarchive'] ),
				),
			);

		/**
		 * Filter robots_settings_tabs
		 *
		 * Used to extend Social tabs
		 * @since 2.2.4
		 *
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_robots_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'robots', $tabs, '2.2.4' );

		do_action( 'the_seo_framework_robots_metabox_after' );

	}

	/**
	 * Robots Metabox General Tab output
	 *
	 * @since 2.2.4
	 */
	protected function robots_metabox_general_tab() {
		?>
		<h4><?php printf( __( 'Open Directory Settings', 'autodescription' ) ); ?></h4>
		<p><span class="description"><?php printf( __( "Sometimes, search engines use resources from certain Directories to find titles and descriptions for your content. You generally don't want them to do so. Turn these options on to prevent them from doing so.", 'autodescription' ), $this->code_wrap( 'noodp' ), $this->code_wrap( 'noydir' ) ); ?></span></p>
		<p><span class="description"><?php printf( __( "The Open Directory Project and the Yahoo! Directory may contain outdated SEO values. Therefore, it's best to leave these options checked.", 'autodescription' ) ); ?></span></p>

		<p class="fields">
			<label for="<?php $this->field_id( 'noodp' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'noodp' ); ?>" id="<?php $this->field_id( 'noodp' ); ?>" <?php $this->is_conditional_checked( 'noodp' ); ?> value="1" <?php checked( $this->get_field_value( 'noodp' ) ); ?> />
				<?php printf( __( 'Apply %s to the entire site?', 'autodescription' ), $this->code_wrap( 'noodp' ) ) ?>
			</label>

			<br />

			<label for="<?php $this->field_id( 'noydir' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'noydir' ); ?>" id="<?php $this->field_id( 'noydir' ); ?>"  <?php $this->is_conditional_checked( 'noydir' ); ?> value="1" <?php checked( $this->get_field_value( 'noydir' ) ); ?> />
				<?php printf( __( 'Apply %s to the entire site?', 'autodescription' ), $this->code_wrap( 'noydir' ) ) ?>
			</label>
		</p>

		<hr>

		<h4><?php printf( __( 'Paged Archive Settings', 'autodescription' ) ); ?></h4>
		<p><span class="description"><?php printf( __( "Indexing the second or later page of any archive might cause duplication errors, search engines look down upon them. Therefore it's recommended to disable indexing of those pages.", 'autodescription' ), $this->code_wrap( 'noodp' ), $this->code_wrap( 'noydir' ) ); ?></span></p>
		<p class="fields">
			<label for="<?php $this->field_id( 'paged_noindex' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'paged_noindex' ); ?>" id="<?php $this->field_id( 'paged_noindex' ); ?>" <?php $this->is_conditional_checked( 'paged_noindex' ); ?> value="1" <?php checked( $this->get_field_value( 'paged_noindex' ) ); ?> />
				<?php printf( __( 'Apply %s to every second or later archive page?', 'autodescription' ), $this->code_wrap( 'noindex' ) ) ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Robots Metabox
	 *		No-: Index/Follow/Archive
	 * Tab output
	 *
	 * @since 2.2.4
	 */
	protected function robots_metabox_no_tab( $types, $robots ) {

		$ro_value = $robots['value'];
		$ro_name = $robots['name'];
		$ro_i18n = $robots['desc'];

		?>
		<h4><?php printf( __( '%s Robots Settings', 'autodescription' ), $ro_name ); ?></h4>
		<p><span class="description"><?php echo $ro_i18n ?></span></p>
		<p class="fields">
			<?php
			foreach ( $types as $type => $i18n ) {
				if ( $type == 'site' || $type == 'attachment' || $type == 'search' ) {

					//* Add <hr> if it's 'site'
					echo $type == 'site' ? '<hr>' : '';

					?>
					<label for="<?php $this->field_id( $type . '_' . $ro_value ); ?>">
						<input type="checkbox" name="<?php $this->field_name( $type . '_' . $ro_value ); ?>" <?php $this->is_conditional_checked( $type . '_' . $ro_value ); ?> id="<?php $this->field_id( $type . '_' . $ro_value ); ?>" value="1" <?php checked( $this->get_field_value( $type . '_' . $ro_value ) ); ?> />
						<?php printf( __( 'Apply %s to %s?', 'autodescription' ), $this->code_wrap( $ro_name ), $i18n ); ?>
					</label>
					<br />
					<?php
				} else {
					?>
					<label for="<?php $this->field_id( $type . '_' . $ro_value ); ?>">
						<input type="checkbox" name="<?php $this->field_name( $type . '_' . $ro_value ); ?>" <?php $this->is_conditional_checked( $type . '_' . $ro_value ); ?> id="<?php $this->field_id( $type . '_' . $ro_value ); ?>" value="1" <?php checked( $this->get_field_value( $type . '_' . $ro_value ) ); ?> />
						<?php printf( __( 'Apply %s to %s Archives?', 'autodescription' ), $this->code_wrap( $ro_name ), $i18n ); ?>
					</label>
					<br />
					<?php
				}
			}
			?>
		</p>
		<?php

	}

	/**
	 * Home Page meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.2
	 *
	 * @uses globals $wpdb fetch post for example
	 *
	 * @see $this->homepage_metabox()      Callback for Title Settings box.
	 */
	public function homepage_metabox() {

		do_action( 'the_seo_framework_homepage_metabox_before' );

		/**
		 * @param string $language The language for help pages. See $this->google_language();
		 */
		$language = $this->google_language();

		/**
		 * @param bool $home_is_blog_notify True if homepage is blog, false if single page/post
		 * @param bool $home_title_frompost True if home inpost title is filled in. False if not.
		 * @param bool $home_description_frompost True if home inpost title is filled in. False if not.
		 */
		$home_is_blog_notify = false;
		$home_title_frompost = false;
		$home_description_frompost = false;

		/**
		 * Notify the user that the data is pulled from the post.
		 */
		$description_from_post_message = '';
		$title_from_post_message  = '';

		// Setting up often used Translations
		$title_i18n = __( 'Title', 'autodescription' );
		$description_i18n = __( 'Description', 'autodescription' );
		$home_page_i18n = __( 'Home Page', 'autodescription' );

		//* Is the frontpage static or a blog?
		if ( 'page' == get_option( 'show_on_front' ) ) {
			$home_id = (int) get_option( 'page_on_front' );
		} else {
			$home_id = 0;
			$home_is_blog_notify = true;
		}

		// Get title separator
		$title_separator = $this->title_separator;
		$sep_option = $this->get_field_value( 'title_seperator' ); // Note: typo
		$sep = array_search( $sep_option, array_flip( $title_separator ), false );

		$home_title = $this->get_field_value( 'homepage_title' );
		$frompost_title = $home_is_blog_notify ? '' : $this->get_custom_field( '_genesis_title', $home_id );

		/**
		 * @since 2.2.4
		 *
		 * Reworked. It now checks if the home is blog, the Home Page Metabox
		 * title and the frompost title.
		 * @since 2.3.4
		 */
		if ( empty( $home_title ) && ! $home_is_blog_notify && !empty( $frompost_title ) )
			$home_title_frompost = true;

		//* Get blog tagline
		$blog_description = get_bloginfo( 'description', 'display' );

		/**
		 * Homepage Tagline settings.
		 * @since 2.3.8
		 *
		 * @param string $home_tagline The tagline option.
		 * @param string $home_tagline_placeholder The option placeholder. Always defaults to description.
		 * @param string|void $home_tagline_value The tagline input value.
		 * @param string $blog_description Override blog description with option if applicable.
		 */
		$home_tagline = $this->get_field_value( 'homepage_title_tagline' );
		$home_tagline_placeholder = $blog_description;
		$home_tagline_value = $home_tagline ? $home_tagline : '';
		$blog_description = $home_tagline_value ? $home_tagline_value : $blog_description;

		/**
		 * Create a placeholder if there's no custom HomePage title found.
		 * @since 2.2.4
		 *
		 * Reworked. Creates placeholders for when it's being emptied.
		 * @since 2.3.4
		 */
		if ( !empty( $frompost_title ) ) {
			//* Fetch frompost title.
			if ( $this->get_option( 'homepage_tagline' ) ) {
				$home_title_placeholder = $frompost_title . " $sep " . $blog_description;
			} else {
				$home_title_placeholder = $frompost_title;
			}
		} else if ( !empty( $home_title ) && empty( $frompost_title ) ) {
			//* Fetch default title
			$blogname = get_bloginfo( 'name', 'raw' );

			if ( $this->get_option( 'homepage_tagline' ) ) {
				$home_title_placeholder = $blogname . " $sep " . $blog_description;
			} else {
				$home_title_placeholder = $blogname;
			}
		} else {
			//* All is empty. Use default title.
			$home_title_placeholder = $this->title( '', '', '', array( 'page_on_front' => true ) );
		}

		/**
		 * If the home title is fetched from the post, notify about that instead.
		 * @since 2.2.4
		 *
		 * Added 'Note:'
		 * @since 2.2.5
		 *
		 * Nesting often used translations
		 */
		if ( $home_title_frompost )
			$title_from_post_message = __( 'Note:', 'autodescription' ) . ' ' . sprintf( __( 'The %s is fetched from the %s on the %s.', 'autodescription' ), $title_i18n, __( 'Page SEO Settings', 'autodescription' ), $home_page_i18n );

		/**
		 * Generate example for Title Additions Location
		 *
		 * Double check.
		 * @param string $frompost_title The possible title from the post.
		 */
		$title_example_pre = !empty( $home_title ) ? $home_title : $frompost_title;
		$title_example = !empty( $title_example_pre ) ? $title_example_pre : get_bloginfo( 'name', 'display' );

		/**
		 * Check for options to calculate title length.
		 *
		 * @since 2.3.4
		 */
		if ( $this->get_option( 'homepage_tagline' ) && !empty( $home_title ) ) {
			$tit_len_pre = $home_title . " $sep " . $blog_description;
		} else if ( $this->get_option( 'homepage_tagline' ) && empty( $home_title ) ) {
			$tit_len_pre = $home_title_placeholder;
		} else if ( ! $this->get_option( 'homepage_tagline' ) && !empty( $home_title ) ) {
			$tit_len_pre = $home_title;
		} else if ( ! $this->get_option( 'homepage_tagline' ) && empty( $home_title ) ) {
			$tit_len_pre = $home_title_placeholder; // dupe?
		} else if ( $home_title_frompost ) {
			$tit_len_pre = $home_title_placeholder;
		} else {
			$tit_len_pre = $home_title;
		}

		//* Fetch the description from the home page.
		$frompost_description = $home_is_blog_notify ? '' : $this->get_custom_field( '_genesis_description', $home_id );

		//* Fetch the HomePage Description option.
		$home_description = $this->get_field_value( 'homepage_description' );

		/**
		 * Create a placeholder if there's no custom HomePage description found.
		 * @since 2.2.4
		 *
		 * Reworked. Always create a placeholder.
		 * @since 2.3.4
		 */
		if ( !empty( $frompost_description ) ) {
			$description_placeholder = $frompost_description;
		} else {
			$description_placeholder = $home_is_blog_notify ? $this->generate_description( '', $home_id, '', true, false ) : $this->generate_description( '', $home_id, '', true, false );
		}

		/**
		 * Checks if the home is blog, the Home Page Metabox description and
		 * the frompost description.
		 * @since 2.3.4
		 */
		if ( empty( $home_description ) && ! $home_is_blog_notify && !empty( $frompost_description )  )
			$home_description_frompost = true;

		/**
		 *
		 * If the HomePage Description empty, it will check for the InPost
		 * Description set on the Home Page. And it will set the InPost
		 * Description as placeholder.
		 *
		 * Nesting often used translations.
		 *
		 * Notify that the homepage is a blog.
		 * @since 2.2.2
		 *
		 * If the home description is fetched from the post, notify about that instead.
		 * @since 2.2.4
		 *
		 * Added 'Note:'
		 * Removed notify that homepage is a blog.
		 * @since 2.2.5
		 */
		if ( $home_description_frompost )
			$description_from_post_message = __( 'Note:', 'autodescription' ) . ' ' . sprintf( __( 'The %s is fetched from the %s on the %s.', 'autodescription' ), $description_i18n, __( 'Page SEO Settings', 'autodescription' ), $home_page_i18n );

		$desc_len_pre = !empty( $home_description ) ? $home_description : $description_placeholder;

		/**
		 * Convert to what Google outputs.
		 *
		 * This will convert e.g. &raquo; to a single length character.
		 * @since 2.3.4
		 */
		$tit_len = html_entity_decode( $tit_len_pre );
		$desc_len = html_entity_decode( $desc_len_pre );

		/**
		 * Generate Examples for both left and right seplocations.
		 */
		$example_left = '<em><span class="custom-title-js">' . esc_attr( $title_example ) . '</span><span class="custom-blogname-js"><span class="autodescription-sep-js"> ' . esc_attr( $sep ) . ' </span><span class="custom-tagline-js">' . esc_attr( $blog_description ) . '</span></span></span>' . '</em>';
		$example_right = '<em>' . '<span class="custom-blogname-js"><span class="custom-tagline-js">' . esc_attr( $blog_description ) . '</span><span class="autodescription-sep-js"> ' . esc_attr( $sep ) . ' </span></span><span class="custom-title-js">' . esc_attr( $title_example ) . '</span></em>';

		$noindex_post = $this->get_custom_field( '_genesis_noindex', $home_id );
		$nofollow_post = $this->get_custom_field( '_genesis_nofollow', $home_id );
		$noarchive_post = $this->get_custom_field( '_genesis_noarchive', $home_id );

		/**
		 * Shows user that the setting is checked on the home page.
		 * Adds starting - with space to maintain readability.
		 *
		 * @since 2.2.4
		 */
		if ( $noindex_post || $nofollow_post || $noarchive_post ) {
			$checked_home = ' - <a href="' . admin_url( 'post.php?post=' . $home_id . '&action=edit#theseoframework-inpost-box' ) . '" target="_blank" class="attention" title="' . __( 'View Home Page Settings', 'autodescription' ) . '" >' . __( 'Checked in Page', 'autodescription' ) . '</a>';
		} else {
			$checked_home = '';
		}

		?>
		<p><span class="description"><?php printf( __( 'These settings will take precedence over the settings set within the home page page edit screen, if any.', 'autodescription' ) ); ?></span></p>

		<hr>

		<fieldset>
			<legend><h4><?php _e( 'Document Title Additions Location', 'autodescription' ); ?></h4></legend>
			<span class="description"><?php _e( 'Determines which side the added title text will go on.', 'autodescription' ); ?></span>

			<p id="home-title-location" class="fields">
				<span>
					<input type="radio" name="<?php $this->field_name( 'home_title_location' ); ?>" id="<?php $this->field_id( 'home_title_location_left' ); ?>" value="left" <?php checked( $this->get_field_value( 'home_title_location' ), 'left' ); ?> />
					<label for="<?php $this->field_id( 'home_title_location_left' ); ?>">
						<span><?php _e( 'Left:', 'autodescription' ); ?></span>
						<?php echo ( $example_left ) ? $this->code_wrap_noesc( $example_left ) : ''; ?>
					</label>
				</span>
				<span>
					<input type="radio" name="<?php $this->field_name( 'home_title_location' ); ?>" id="<?php $this->field_id( 'home_title_location_right' ); ?>" value="right" <?php checked( $this->get_field_value( 'home_title_location' ), 'right' ); ?> />
					<label for="<?php $this->field_id( 'home_title_location_right' ); ?>">
						<span><?php _e( 'Right:', 'autodescription' ); ?></span>
						<?php echo ( $example_right ) ? $this->code_wrap_noesc( $example_right ) : ''; ?>
					</label>
				</span>
			</p>
		</fieldset>

		<hr>

		<h4 style="margin-top:0;"><?php printf( __( '%s Tagline', 'autodescription' ), $home_page_i18n ); ?></h4>
		<p id="title-tagline-toggle">
			<label for="<?php $this->field_id( 'homepage_tagline' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'homepage_tagline' ); ?>" id="<?php $this->field_id( 'homepage_tagline' ); ?>" <?php $this->is_conditional_checked( 'homepage_tagline' ); ?> value="1" <?php checked( $this->get_field_value( 'homepage_tagline' ) ); ?> />
				<?php printf( __( 'Add site description (tagline) to the Title on the %s?', 'autodescription' ), $home_page_i18n ); ?>
			</label>
		</p>

		<p class="fields">
			<label for="<?php $this->field_id( 'homepage_title_tagline' ); ?>">
				<strong><?php printf( __( 'Custom %s Title Tagline', 'autodescription' ), $home_page_i18n ); ?></strong>
			</label>
		</p>
		<p class="fields">
			<input type="text" name="<?php $this->field_name( 'homepage_title_tagline' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_title_tagline' ); ?>" placeholder="<?php echo $home_tagline_placeholder ?>" value="<?php echo esc_attr( $home_tagline_value ); ?>" />
		</p>

		<hr>

		<p class="fields">
			<label for="<?php $this->field_id( 'homepage_title' ); ?>">
				<strong><?php printf( __( 'Custom %s Title', 'autodescription' ), $home_page_i18n ); ?></strong>
				<a href="https://support.google.com/webmasters/answer/35624?hl=<?php echo $language; ?>#3" target="_blank" title="<?php _e( 'Recommended Length: 50 to 55 characters', 'autodescription' ) ?>">[?]</a>
				<span class="description"><?php printf( __( 'Characters Used: %s', 'autodescription' ), '<span id="' . $this->field_id( 'homepage_title', false ) . '_chars">'. mb_strlen( $tit_len ) .'</span>' ); ?></span>
			</label>
		</p>
		<p class="fields">
			<input type="text" name="<?php $this->field_name( 'homepage_title' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_title' ); ?>" placeholder="<?php echo $home_title_placeholder ?>" value="<?php echo esc_attr( $home_title ); ?>" />
			<?php
			if ( $title_from_post_message ) {
				echo '<br /><span class="description">' . $title_from_post_message . '</span>';
			}
			?>
		</p>

		<hr>

		<p class="fields">
			<label for="<?php $this->field_id( 'homepage_description' ); ?>">
				<strong><?php printf( __( 'Custom %s Description', 'autodescription' ), $home_page_i18n ); ?></strong>
				<a href="https://support.google.com/webmasters/answer/35624?hl=<?php echo $language; ?>#1" target="_blank" title="<?php _e( 'Recommended Length: 145 to 155 characters', 'autodescription' ) ?>">[?]</a>
				<span class="description"><?php printf( __( 'Characters Used: %s', 'autodescription' ), '<span id="' . $this->field_id( 'homepage_description', false ) . '_chars">'. mb_strlen( $desc_len ) .'</span>' ); ?></span>
			</label>
		</p>
		<p>
			<textarea name="<?php $this->field_name( 'homepage_description' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_description' ); ?>" rows="3" cols="70"  placeholder="<?php echo $description_placeholder ?>"><?php echo esc_textarea( $home_description ); ?></textarea>
			<br />
			<span class="description"><?php _e( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' ); ?></span>
			<?php
			if ( $description_from_post_message ) {
				echo '<br /><span class="description">' . $description_from_post_message . '</span>';
			}
			?>

		</p>

		<hr>

		<h4><?php _e( 'Homepage Robots Meta Settings', 'autodescription' ); ?></h4>

		<p class="fields">
			<label for="<?php $this->field_id( 'homepage_noindex' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'homepage_noindex' ); ?>" id="<?php $this->field_id( 'homepage_noindex' ); ?>" <?php $this->is_conditional_checked( 'homepage_noindex' ); ?> value="1" <?php checked( $this->get_field_value( 'homepage_noindex' ) ); ?> />
				<?php printf( __( 'Apply %s to the %s?', 'autodescription' ), $this->code_wrap( 'noindex' ), $home_page_i18n ); ?>
				<a href="https://support.google.com/webmasters/answer/93710?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( __( 'Tell Search Engines not to show this page in their search results', 'autodescription' ) ) ?>">[?]</a>
				<?php echo $noindex_post ? $checked_home : ''; ?>
			</label>

			<br />

			<label for="<?php $this->field_id( 'homepage_nofollow' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'homepage_nofollow' ); ?>" id="<?php $this->field_id( 'homepage_nofollow' ); ?>" <?php $this->is_conditional_checked( 'homepage_nofollow' ); ?> value="1" <?php checked( $this->get_field_value( 'homepage_nofollow' ) ); ?> />
				<?php printf( __( 'Apply %s to the %s?', 'autodescription' ), $this->code_wrap( 'nofollow' ), $home_page_i18n ); ?>
				<a href="https://support.google.com/webmasters/answer/96569?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( __( 'Tell Search Engines not to follow links on this page', 'autodescription' ) ) ?>">[?]</a>
				<?php echo $nofollow_post ? $checked_home : ''; ?>
			</label>

			<br />

			<label for="<?php $this->field_id( 'homepage_noarchive' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'homepage_noarchive' ); ?>" id="<?php $this->field_id( 'homepage_noarchive' ); ?>" <?php $this->is_conditional_checked( 'homepage_noarchive' ); ?> value="1" <?php checked( $this->get_field_value( 'homepage_noarchive' ) ); ?> />
				<?php printf( __( 'Apply %s to the %s?', 'autodescription' ), $this->code_wrap( 'noarchive' ), $home_page_i18n ); ?>
				<a href="https://support.google.com/webmasters/answer/79812?hl=<?php echo $language; ?>" target="_blank" title="<?php printf( __( 'Tell Search Engines not to save a cached copy this page', 'autodescription' ) ) ?>">[?]</a>
				<?php echo $noarchive_post ? $checked_home : ''; ?>
			</label>
		</p>

		<?php
		// Add notice if any options are checked on the post.
		if ( $noindex_post || $nofollow_post || $noarchive_post ) {
			?><p><span class="description"><?php printf( __( 'Note: If any of these options are unchecked, but are checked on the homepage, they will be output regardless.', 'autodescription' ) ); ?></span></p><?php
		}

		do_action( 'the_seo_framework_homepage_metabox_after' );

	}

	/**
	 * Social meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.2
	 *
	 * @uses $this->social_metabox_general_tab()
	 * @uses $this->social_metabox_facebook_tab()
	 * @uses $this->social_metabox_twitter_tab()
	 *
	 * @applies filter 'social_settings_tabs'
	 *
	 * @param array $args the social tabs arguments
	 */
	public function social_metabox( $args = array() ) {

		do_action( 'the_seo_framework_social_metabox_before' );

		/**
		 * Parse tabs content
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *			array(
		 *				'name' 		=> The name
		 *				'callback' 	=> The callback function, use array for method calling (accepts $this, but isn't used here for optimization purposes)
		 *				'dashicon'	=> Desired dashicon
		 *			)
		 * }
		 *
		 * @since 2.2.2
		 */
		$default_tabs = array(
			'general' => array(
				'name' 		=> __( 'General', 'autodescription' ),
				'callback'	=> array( 'AutoDescription_Metaboxes', 'social_metabox_general_tab' ),
				'dashicon'	=> 'admin-generic',
			),
			'facebook' => array(
				'name'		=> 'Facebook',
				'callback'	=> array( 'AutoDescription_Metaboxes', 'social_metabox_facebook_tab' ),
				'dashicon'	=> 'facebook-alt',
			),
			'twitter' => array(
				'name'		=> 'Twitter',
				'callback'	=> array( 'AutoDescription_Metaboxes', 'social_metabox_twitter_tab' ),
				'dashicon'	=> 'twitter',
			),
			'postdates' => array(
				'name'		=> __( 'Post Dates', 'autodescription' ),
				'callback'	=> array( 'AutoDescription_Metaboxes', 'social_metabox_postdates_tab' ),
				'dashicon'	=> 'backup',
			),
			'relationships' => array(
				'name'		=> __( 'Link Relationships', 'autodescription' ),
				'callback'	=> array( 'AutoDescription_Metaboxes', 'social_metabox_relationships_tab' ),
				'dashicon'	=> 'leftright',
			),
		);

		/**
		 * Filter social_settings_tabs
		 *
		 * Used to extend Social tabs
		 *
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_social_settings_tabs', $default_tabs );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'social', $tabs, '2.2.2' );

		do_action( 'the_seo_framework_social_metabox_after' );

	}

	/**
	 * Social Metabox General Tab output
	 *
	 * @since 2.2.2
	 *
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	protected function social_metabox_general_tab() {

		?>
		<h4><?php _e( 'Site Shortlink Settings', 'autodescription' ); ?></h4>
		<p><span class="description"><?php printf( __( 'The shortlink tag might have some use for 3rd party service discoverability, but it has little to no SEO value whatsoever.', 'autodescription') ); ?></span></p>
		<p class="fields">
			<label for="<?php $this->field_id( 'shortlink_tag' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'shortlink_tag' ); ?>" id="<?php $this->field_id( 'shortlink_tag' ); ?>" <?php $this->is_conditional_checked( 'shortlink_tag' ); ?> value="1" <?php checked( $this->get_field_value( 'shortlink_tag' ) ); ?> />
				<?php _e( 'Include shortlink tag?', 'autodescription' ); ?>
			</label>
		</p>

		<hr>

		<h4><?php _e( 'Output Meta Tags', 'autodescription' ); ?></h4>
		<p><span class="description"><?php printf( __( 'Output various meta tags for social site integration, among other 3rd party services.', 'autodescription' ) ); ?></span></p>

		<hr>

		<p class="fields">
			<label for="<?php $this->field_id( 'og_tags' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'og_tags' ); ?>" id="<?php $this->field_id( 'og_tags' ); ?>" <?php $this->is_conditional_checked( 'og_tags' ); ?>  value="1" <?php checked( $this->get_field_value( 'og_tags' ) ); ?> />
				<?php _e( 'Output Open Graph meta tags?', 'autodescription' ); ?>
			</label>
			<p class="description"><?php _e( 'Facebook, Twitter, Pinterest and many other social sites make use of these tags.', 'autodescription' ); ?></p>
		</p>

		<hr>

		<p class="fields">
			<label for="<?php $this->field_id( 'facebook_tags' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'facebook_tags' ); ?>" id="<?php $this->field_id( 'facebook_tags' ); ?>" <?php $this->is_conditional_checked( 'facebook_tags' ); ?> value="1" <?php checked( $this->get_field_value( 'facebook_tags' ) ); ?> />
				<?php _e( 'Output Facebook meta tags?', 'autodescription' ); ?>
			</label>
			<p class="description"><?php printf( __( 'Output various tags targetted at %s.', 'autodescription' ), 'Facebook' ); ?></p>
		</p>

		<hr>

		<p class="fields">
			<label for="<?php $this->field_id( 'twitter_tags' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'twitter_tags' ); ?>" id="<?php $this->field_id( 'twitter_tags' ); ?>" <?php $this->is_conditional_checked( 'twitter_tags' ); ?> value="1" <?php checked( $this->get_field_value( 'twitter_tags' ) ); ?> />
				<?php _e( 'Output Twitter meta tags?', 'autodescription' ); ?>
				<p class="description"><?php printf( __( 'Output various tags targetted at %s.', 'autodescription' ), 'Twitter' ); ?></p>
			</label>
		</p>
		<?php
	}

	/**
	 * Social Metabox Open Graph Tab Output
	 *
	 * @since 2.2.2
	 * @TODO
	 *
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	protected function social_metabox_opengraph_tab() {
		?><h4>Coming soon!</h4><?php
	}

	/**
	 * Social Metabox Facebook Tab Output
	 *
	 * @since 2.2.2
	 *
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	protected function social_metabox_facebook_tab() {

		$fb_author = $this->get_field_value( 'facebook_author' );
		$fb_author_placeholder = empty( $fb_publisher ) ? _x( 'http://www.facebook.com/YourPersonalProfile', 'Example Facebook Personal URL', 'autodescription' ) : '';

		$fb_publisher = $this->get_field_value( 'facebook_publisher' );
		$fb_publisher_placeholder = empty( $fb_publisher ) ? _x( 'http://www.facebook.com/YourVerifiedBusinessProfile', 'Example Verified Facebook Business URL', 'autodescription' ) : '';

		$fb_appid = $this->get_field_value( 'facebook_appid' );
		$fb_appid_placeholder = empty( $fb_appid ) ? '123456789012345' : '';

		?>
		<h4><?php _e( 'Default Facebook Integration Settings', 'autodescription' ); ?></h4>
		<p><span class="description"><?php printf( __( 'Facebook post sharing works mostly through Open Graph. However, you can also link your Business and Personal Facebook pages, among various other options.', 'autodescription' ) ); ?></span></p>
		<p><span class="description"><?php printf( __( 'When these options are filled in, Facebook might link your Facebook Profiles to be followed and liked when your post or page is shared.', 'autodescription' ) ); ?></span></p>

		<hr>

		<p class="fields">
			<label for="<?php $this->field_id( 'facebook_author' ); ?>">
				<strong><?php _e( 'Article Author Facebook URL', 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( 'https://facebook.com/me' ); ?>" class="description" target="_blank" title="<?php _e( 'Your Facebook profile.', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p class="fields">
			<input type="text" name="<?php $this->field_name( 'facebook_author' ); ?>" class="large-text" id="<?php $this->field_id( 'facebook_author' ); ?>" placeholder="<?php echo $fb_author_placeholder ?>" value="<?php echo esc_attr( $fb_author ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'facebook_publisher' ); ?>">
				<strong><?php _e( 'Article Publisher Facebook URL', 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( 'https://instantarticles.fb.com/' ); ?>" class="description" target="_blank" title="<?php _e( 'To use this, you need to be a verified business.', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p class="fields">
			<input type="text" name="<?php $this->field_name( 'facebook_publisher' ); ?>" class="large-text" id="<?php $this->field_id( 'facebook_publisher' ); ?>" placeholder="<?php echo $fb_publisher_placeholder ?>" value="<?php echo esc_attr( $fb_publisher ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'facebook_appid' ); ?>">
				<strong><?php _e( 'Facebook App ID', 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( 'https://developers.facebook.com/apps' ); ?>" target="_blank" class="description" title="<?php _e( 'Get Facebook App ID', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p class="fields">
			<input type="text" name="<?php $this->field_name( 'facebook_appid' ); ?>" class="large-text" id="<?php $this->field_id( 'facebook_appid' ); ?>" placeholder="<?php echo $fb_appid_placeholder ?>" value="<?php echo esc_attr( $fb_appid ); ?>" />
		</p>
		<?php

	}

	/**
	 * Social Metabox Twitter Tab Output
	 *
	 * @since 2.2.2
	 *
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	protected function social_metabox_twitter_tab() {

		$tw_site = $this->get_field_value( 'twitter_site' );
		$tw_site_placeholder = empty( $tw_site ) ? _x( '@your-site-username', 'Twitter @username', 'autodescription' ) : '';

		$tw_creator = $this->get_field_value( 'twitter_creator' );
		$tw_creator_placeholder = empty( $tw_creator ) ? _x( '@your-personal-username', 'Twitter @username', 'autodescription' ) : '';

		?>
		<h4><?php _e( 'Default Twitter Integration Settings', 'autodescription' ); ?></h4>
		<p><span class="description"><?php printf( __( 'Twitter post sharing works mostly through Open Graph. However, you can also link your Business and Personal Twitter pages, among various other options.', 'autodescription' ) ); ?></span></p>

		<hr>

		<fieldset id="twitter-cards">
			<legend><h4><?php _e( 'Twitter Card Type', 'autodescription' ); ?></h4></legend>
			<span class="description"><?php printf( __( 'What kind of Twitter card would you like to use? It will default to %s if no image is found.', 'autodescription' ), $this->code_wrap( 'Summary' ) ); ?></span>

			<?php
			$twitter_card = $this->twitter_card;
			foreach ( $twitter_card as $type => $name ) {
				?>
				<p>
					<span>
						<input type="radio" name="<?php $this->field_name( 'twitter_card' ); ?>" id="<?php $this->field_id( 'twitter_card_' . $type ); ?>" value="<?php echo $type ?>" <?php checked( $this->get_field_value( 'twitter_card' ), $type ); ?> />
						<label for="<?php $this->field_id( 'twitter_card_' . $type ); ?>">
							<span><?php echo $this->code_wrap( ucfirst( $name ) ); ?></span>
							<a class="description" href="<?php echo esc_url('https://dev.twitter.com/cards/types/' . $name ); ?>" target="_blank" title="Twitter Card <?php echo ucfirst( $name ) . ' ' . __( 'Example', 'autodescription' ); ?>"><?php _e( 'Example', 'autodescription' ); ?></a>
						</label>
					</span>
				</p>
				<?php
			}
			?>
		</fieldset>

		<hr>

		<p><span class="description"><?php printf( __( 'When the following options are filled in, Twitter might link your Twitter Site or Personal Profile when your post or page is shared.', 'autodescription' ) ); ?></span></p>
		<p>
			<label for="<?php $this->field_id( 'twitter_site' ); ?>">
				<strong><?php _e( "Your Website's Twitter Profile", 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( 'https://twitter.com/home' ); ?>" target="_blank" class="description" title="<?php _e( 'Find your @username', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p class="fields">
			<input type="text" name="<?php $this->field_name( 'twitter_site' ); ?>" class="large-text" id="<?php $this->field_id( 'twitter_site' ); ?>" placeholder="<?php echo $tw_site_placeholder ?>" value="<?php echo esc_attr( $tw_site ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'twitter_creator' ); ?>">
				<strong><?php _e( 'Your Personal Twitter Profile', 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( 'https://twitter.com/home' ); ?>" target="_blank" class="description" title="<?php _e( 'Find your @username', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p class="fields">
			<input type="text" name="<?php $this->field_name( 'twitter_creator' ); ?>" class="large-text" id="<?php $this->field_id( 'twitter_creator' ); ?>" placeholder="<?php echo $tw_creator_placeholder ?>" value="<?php echo esc_attr( $tw_creator ); ?>" />
		</p>
		<?php

	}

	/**
	 * Social Metabox PostDates Tab Output
	 *
	 * @since 2.2.4
	 *
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	public function social_metabox_postdates_tab() {

		$pages_i18n = __( 'Pages', 'autodescription' );
		$posts_i18n = __( 'Posts', 'autodescription' );
		$home_i18n = __( 'Home Page', 'autodescription' );

		?>
		<h4><?php printf( __( 'Post Dates Settings', 'autodescription' ) ); ?></h4>
		<p><span class="description"><?php _e( "Some Search Engines output the publishing date and modified date next to the search results. These help search engines find new content and could impact SEO value.", 'autodescription' ); ?></span></p>
		<p><span class="description"><?php _e( "It's recommended on posts, it's not recommended on pages unless you modify or create new pages frequently.", 'autodescription' ); ?></span></p>
		<p class="fields">
			<label for="<?php $this->field_id( 'post_publish_time' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'post_publish_time' ); ?>" id="<?php $this->field_id( 'post_publish_time' ); ?>" <?php $this->is_conditional_checked( 'post_publish_time' ); ?> value="1" <?php checked( $this->get_field_value( 'post_publish_time' ) ); ?> />
				<?php printf( __( 'Add %s to %s?', 'autodescription' ), $this->code_wrap( 'article:published_time' ), $posts_i18n ); ?>
			</label>
			<br />
			<label for="<?php $this->field_id( 'page_publish_time' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'page_publish_time' ); ?>" id="<?php $this->field_id( 'page_publish_time' ); ?>" <?php $this->is_conditional_checked( 'page_publish_time' ); ?> value="1" <?php checked( $this->get_field_value( 'page_publish_time' ) ); ?> />
				<?php printf( __( 'Add %s to %s?', 'autodescription' ), $this->code_wrap( 'article:published_time' ), $pages_i18n ); ?>
			</label>
		</p>
		<p class="fields">
			<label for="<?php $this->field_id( 'post_modify_time' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'post_modify_time' ); ?>" id="<?php $this->field_id( 'post_modify_time' ); ?>" <?php $this->is_conditional_checked( 'post_modify_time' ); ?> value="1" <?php checked( $this->get_field_value( 'post_modify_time' ) ); ?> />
				<?php printf( __( 'Add %s to %s?', 'autodescription' ), $this->code_wrap( 'article:modified_time' ), $posts_i18n ); ?>
			</label>
			<br />
			<label for="<?php $this->field_id( 'page_modify_time' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'page_modify_time' ); ?>" id="<?php $this->field_id( 'page_modify_time' ); ?>" <?php $this->is_conditional_checked( 'page_modify_time' ); ?> value="1" <?php checked( $this->get_field_value( 'page_modify_time' ) ); ?> />
				<?php printf( __( 'Add %s to %s?', 'autodescription' ), $this->code_wrap( 'article:modified_time' ), $pages_i18n ); ?>
			</label>
		</p>

		<hr>

		<h4><?php printf( __( 'Home Page', 'autodescription' ) ); ?></h4>
		<p><span class="description"><?php _e( "Because you only publish the home page once, Search Engines might think your site is outdated. This can be prevented by disabling the following options.", 'autodescription' ); ?></span></p>
		<p class="fields">
			<label for="<?php $this->field_id( 'home_publish_time' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'home_publish_time' ); ?>" id="<?php $this->field_id( 'home_publish_time' ); ?>" <?php $this->is_conditional_checked( 'home_publish_time' ); ?> value="1" <?php checked( $this->get_field_value( 'home_publish_time' ) ); ?> />
				<?php printf( __( 'Add %s to the %s?', 'autodescription' ), $this->code_wrap( 'article:published_time' ), $home_i18n ); ?>
			</label>
			<br />
			<label for="<?php $this->field_id( 'home_modify_time' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'home_modify_time' ); ?>" id="<?php $this->field_id( 'home_modify_time' ); ?>" <?php $this->is_conditional_checked( 'home_modify_time' ); ?> value="1" <?php checked( $this->get_field_value( 'home_modify_time' ) ); ?> />
				<?php printf( __( 'Add %s to the %s?', 'autodescription' ), $this->code_wrap( 'article:modified_time' ), $home_i18n ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Social Metabox Relationships Tab Output
	 *
	 * @since 2.2.4
	 *
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	public function social_metabox_relationships_tab() {

		?>
		<h4><?php printf( __( 'Link Relationship Settings', 'autodescription' ) ); ?></h4>
		<p><span class="description"><?php _e( "Some Search Engines look for relations between content of your pages. If you have multiple pages for a single Post or Page, or have archives indexed, this option will help Search Engines look for the right page to display in the Search Results.", 'autodescription' ); ?></span></p>
		<p><span class="description"><?php _e( "It's recommended to turn this option on for better SEO consistency and to prevent duplicated content errors.", 'autodescription' ); ?></span></p>
		<hr>
		<p class="fields">
			<label for="<?php $this->field_id( 'prev_next_posts' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'prev_next_posts' ); ?>" id="<?php $this->field_id( 'prev_next_posts' ); ?>" <?php $this->is_conditional_checked( 'prev_next_posts' ); ?> value="1" <?php checked( $this->get_field_value( 'prev_next_posts' ) ); ?> />
				<?php printf( __( 'Add %s link tags to Posts and Pages?', 'autodescription' ), $this->code_wrap( 'rel' ) ); ?>
			</label>
			<br />
			<label for="<?php $this->field_id( 'prev_next_archives' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'prev_next_archives' ); ?>" id="<?php $this->field_id( 'prev_next_archives' ); ?>" <?php $this->is_conditional_checked( 'prev_next_archives' ); ?> value="1" <?php checked( $this->get_field_value( 'prev_next_archives' ) ); ?> />
				<?php printf( __( 'Add %s link tags to Archives?', 'autodescription' ), $this->code_wrap( 'rel' ) ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Webmaster meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.4
	 *
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	public function webmaster_metabox() {

		do_action( 'the_seo_framework_webmaster_metabox_before' );

		$site_url = $this->the_home_url_from_cache();
		$language = $this->google_language();

		$bing_site_url = "https://www.bing.com/webmaster/configure/verify/ownership?url=" . urlencode( $site_url );
		$google_site_url = "https://www.google.com/webmasters/verification/verification?hl=" . $language . "&siteUrl=" . $site_url;

		?>
		<h4><?php _e( 'Webmaster Integration Settings', 'autodescription' ); ?></h4>
		<p><span class="description"><?php printf( __( "When adding your site to Google or Bing Webmaster Tools, you'll be asked to add a code or file to your site for verification purposes. These options will help you easily integrate those codes.", 'autodescription' ) ); ?></span></p>
		<p><span class="description"><?php printf( __( "Verifying your website has no SEO value whatsoever. But you might gain added benefits such as search ranking insights to help you improve your Website's content.", 'autodescription' ) ); ?></span></p>

		<hr>

		<p>
			<label for="<?php $this->field_id( 'google_verification' ); ?>">
				<strong><?php _e( "Google Webmaster Verification Code", 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( $google_site_url ); ?>" target="_blank" class="description" title="<?php _e( 'Get the Google Verification code.', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p class="fields">
			<input type="text" name="<?php $this->field_name( 'google_verification' ); ?>" class="large-text" id="<?php $this->field_id( 'google_verification' ); ?>" placeholder="ABC1d2eFg34H5iJ6klmNOp7qRstUvWXyZaBc8dEfG9" value="<?php echo esc_attr( $this->get_field_value( 'google_verification' ) ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'bing_verification' ); ?>">
				<strong><?php _e( "Bing Webmaster Verification Code", 'autodescription' ); ?></strong>
				<a href="<?php echo esc_url( $bing_site_url ); ?>" target="_blank" class="description" title="<?php _e( 'Get Bing Verification Code', 'autodescription' ); ?>">[?]</a>
			</label>
		</p>
		<p class="fields">
			<input type="text" name="<?php $this->field_name( 'bing_verification' ); ?>" class="large-text" id="<?php $this->field_id( 'bing_verification' ); ?>" placeholder="123A456B78901C2D3456E7890F1A234D" value="<?php echo esc_attr( $this->get_field_value( 'bing_verification' ) ); ?>" />
		</p>
		<?php

		do_action( 'the_seo_framework_webmaster_metabox_after' );

	}

	/**
	 * Knowlegde Graph metabox on the Site SEO Settings page.
	 *
	 * @since 2.2.8
	 *
	 * @see $this->knowledge_metabox() Callback for Social Settings box.
	 */
	public function knowledge_metabox( $args = array() ) {

		do_action( 'the_seo_framework_knowledge_metabox_before' );

		/**
		 * Parse tabs content
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *			array(
		 *				'name' 		=> The name
		 *				'callback' 	=> The callback function, use array for method calling (accepts $this, but isn't used here for optimization purposes)
		 *				'dashicon'	=> Desired dashicon
		 *			)
		 * }
		 *
		 * @since 2.2.8
		 */
		$default_tabs = array(
			'general' => array(
				'name' 		=> __( 'General', 'autodescription' ),
				'callback'	=> array( 'AutoDescription_Metaboxes', 'knowledge_metabox_general_tab' ),
				'dashicon'	=> 'admin-generic',
			),
			'website' => array(
				'name'		=> __( 'Website', 'autodescription' ),
				'callback'	=> array( 'AutoDescription_Metaboxes', 'knowledge_metabox_about_tab' ),
				'dashicon'	=> 'admin-home',
			),
			'social' => array(
				'name'		=> 'Social Sites',
				'callback'	=> array( 'AutoDescription_Metaboxes', 'knowledge_metabox_social_tab' ),
				'dashicon'	=> 'networking',
			),
		);

		/**
		 * Applies filter knowledgegraph_settings_tabs
		 *
		 * Used to extend Knowledge Graph tabs
		 *
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_knowledgegraph_settings_tabs', $default_tabs );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'knowledge', $tabs, '2.2.8' );

		do_action( 'the_seo_framework_knowledge_metabox_after' );

	}

	/**
	 * Knowledge Graph Metabox General Tab Output
	 *
	 * @since 2.2.8
	 *
	 * @see $this->knowledge_metabox() Callback for Knowledge Graph Settings box.
	 */
	public function knowledge_metabox_general_tab() {

		?>
		<h4><?php _e( 'Knowledge Graph Settings', 'autodescription' ); ?></h4>
		<p><span class="description"><?php printf( __( "The Knowledge Graph lets Google and other Search Engines know where to find you or your organization and its relevant content.", 'autodescription' ) ); ?></span></p>
		<p><span class="description"><?php printf( __( "Google is becoming more of an 'Answer Engine' than a 'Search Engine'. Setting up these options has a huge positive impact on the SEO value of your website.", 'autodescription' ) ); ?></span></p>

		<p class="fields">
			<label for="<?php $this->field_id( 'knowledge_output' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'knowledge_output' ); ?>" id="<?php $this->field_id( 'knowledge_output' ); ?>" <?php $this->is_conditional_checked( 'knowledge_output' ); ?> value="1" <?php checked( $this->get_field_value( 'knowledge_output' ) ); ?> />
				<?php _e( 'Output Knowledge tags?', 'autodescription' ); ?>
			</label>
		</p>

		<?php
		if ( $this->wp_version( '4.3.0', '>=' ) ) :
		?>
			<hr>

			<h4><?php printf( _x( "Website logo", 'WordPress Customizer', 'autodescription' ) ); ?></h4>
			<p class="fields">
				<label for="<?php $this->field_id( 'knowledge_logo' ); ?>">
					<input type="checkbox" name="<?php $this->field_name( 'knowledge_logo' ); ?>" id="<?php $this->field_id( 'knowledge_logo' ); ?>" <?php $this->is_conditional_checked( 'knowledge_logo' ); ?> value="1" <?php checked( $this->get_field_value( 'knowledge_logo' ) ); ?> />
					<?php _e( 'Use the Favicon from Customizer as Organization Logo?', 'autodescription' ); ?>
				</label>
			</p>
			<p><span class="description"><?php printf( __( "This option only has effect when this site represents an Organization. If left disabled, Search Engines will look elsewhere for a logo, if it exists and is assigned as a logo.", 'autodescription' ) ); ?></span></p>

		<?php
		endif;

	}

	/**
	 * Knowledge Graph Metabox About Tab Output
	 *
	 * @since 2.2.8
	 *
	 * @see $this->knowledge_metabox() Callback for Knowledge Graph Settings box.
	 */
	public function knowledge_metabox_about_tab() {
		$blogname = get_bloginfo( 'name', 'raw' );

		?>
		<h4><?php _e( 'About this website', 'autodescription' ); ?></h4>
		<p><span class="description"><?php printf( __( 'About who or what is your website?', 'autodescription' ) ); ?></span></p>

		<hr>

		<p>
			<label for="<?php $this->field_id( 'knowledge_type' ); ?>"><?php _ex( 'This website represents:', '...Organization or Person.', 'autodescription' ); ?></label>
			<select name="<?php $this->field_name( 'knowledge_type' ); ?>" id="<?php $this->field_id( 'knowledge_type' ); ?>">
			<?php
			$knowledge_type = (array) apply_filters(
				'the_seo_framework_knowledge_types',
				array(
					'organization'	=> __( 'An Organization', 'autodescription' ),
					'person' 		=> __( 'A Person', 'autodescription' ),
				)
			);
			foreach ( $knowledge_type as $value => $name )
				echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->get_field_value( 'knowledge_type' ), esc_attr( $value ), false ) . '>' . esc_html( $name ) . '</option>' . "\n";
			?>
			</select>
		</p>

		<hr>

		<p>
			<label for="<?php $this->field_id( 'knowledge_name' ); ?>">
				<strong><?php _e( "The organization or personal name", 'autodescription' ); ?></strong>
			</label>
		</p>
		<p class="fields">
			<input type="text" name="<?php $this->field_name( 'knowledge_name' ); ?>" class="large-text" id="<?php $this->field_id( 'knowledge_name' ); ?>" placeholder="<?php echo esc_attr( $blogname ) ?>" value="<?php echo esc_attr( $this->get_field_value( 'knowledge_name' ) ); ?>" />
		</p>
		<?php

	}

	/**
	 * Knowledge Graph Metabox Social Tab Output
	 *
	 * @since 2.2.8
	 *
	 * @see $this->knowledge_metabox() Callback for Knowledge Graph Settings box.
	 */
	public function knowledge_metabox_social_tab() {

		?>
		<h4><?php _e( 'Social Pages connected to this website', 'autodescription' ); ?></h4>
		<p><span class="description"><?php _e( "Don't have a page at a site or is the profile only privately accessible? Leave that field empty. Unsure? Fill it in anyway.", 'autodescription' ); ?></span></p>
		<p><span class="description"><?php _e( "Add the link that leads directly to the social page of this website.", 'autodescription' ); ?></span></p>

		<hr>

		<?php
		$connectedi18n = _x( 'RelatedProfile', 'Example link placeholder for a social profile', 'autodescription' );
		$profile18n = _x( 'Profile', 'Social Profile', 'autodescription' );

		/**
		 * @todo maybe genericons?
		 */

		$socialsites = array(
			'facebook' => array(
				'option'		=> 'knowledge_facebook',
				'dashicon'		=> 'dashicons-facebook',
				'desc' 			=> 'Facebook ' . __( 'Page', 'autodescription' ),
				'placeholder'	=> 'http://www.facebook.com/' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://facebook.com/me' ),
			),
			'twitter' => array(
				'option'		=> 'knowledge_twitter',
				'dashicon'		=> 'dashicons-twitter',
				'desc' 			=> 'Twitter ' . $profile18n,
				'placeholder'	=> 'http://www.twitter.com/' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://twitter.com/home' ), // No example link available.
			),
			'gplus' => array(
				'option'		=> 'knowledge_gplus',
				'dashicon'		=> 'dashicons-googleplus',
				'desc' 			=>  'Google+ ' . $profile18n,
				'placeholder'	=> 'https://plus.google.com/' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://plus.google.com/me' ),
			),
			'instagram' => array(
				'option'		=> 'knowledge_instagram',
				'dashicon'		=> 'genericon-instagram',
				'desc' 			=> 'Instagram ' . $profile18n,
				'placeholder'	=> 'http://instagram.com/' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://instagram.com/' ), // No example link available.
			),
			'youtube' => array(
				'option'		=> 'knowledge_youtube',
				'dashicon'		=> 'genericon-youtube',
				'desc' 			=> 'Youtube ' . $profile18n,
				'placeholder'	=> 'http://www.youtube.com/' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://www.youtube.com/user/%2f' ), // Yes a double slash.
			),
			'linkedin' => array(
				'option'		=> 'knowledge_linkedin',
				'dashicon'		=> 'genericon-linkedin-alt',
				'desc' 			=> 'LinkedIn ' . $profile18n . ' ID',
				'placeholder'	=> 'http://www.linkedin.com/profile/view?id=' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://www.linkedin.com/profile/view' ), // This generates a query arg. We should allow that.
			),
			'pinterest' => array(
				'option'		=> 'knowledge_pinterest',
				'dashicon'		=> 'genericon-pinterest-alt',
				'desc' 			=> 'Pinterest ' . $profile18n,
				'placeholder'	=> 'https://www.pinterest.com/' . $connectedi18n . '/',
				'examplelink'	=> esc_url( 'https://www.pinterest.com/me/' ),
			),
			'soundcloud' => array(
				'option'		=> 'knowledge_soundcloud',
				'dashicon'		=> 'genericon-cloud', // I know, it's not the real one. D:
				'desc' 			=> 'SoundCloud ' . $profile18n,
				'placeholder'	=> 'https://soundcloud.com/' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://soundcloud.com/you' ),
			),
			'tumblr' => array(
				'option'		=> 'knowledge_tumblr',
				'dashicon'		=> 'genericon-tumblr',
				'desc' 			=> 'Tumblr ' . __( 'Blog', 'autodescription' ),
				'placeholder'	=> 'https://tumblr.com/blog/' . $connectedi18n,
				'examplelink'	=> esc_url( 'https://www.tumblr.com/dashboard' ),  // No example link available.
			),
		);

		foreach ( $socialsites as $key => $value ) {
			?>
			<p>
				<label for="<?php $this->field_id( $value['option'] ); ?>">
					<strong><?php echo $value['desc'] ?></strong>
					<?php
					if ( !empty( $value['examplelink'] ) ) {
						?><a href="<?php echo esc_url( $value['examplelink'] ); ?>" target="_blank">[?]</a><?php
					}
					?>
				</label>
			</p>
			<p class="fields">
				<input type="text" name="<?php $this->field_name( $value['option'] ); ?>" class="large-text" id="<?php $this->field_id( $value['option'] ); ?>" placeholder="<?php echo esc_attr( $value['placeholder'] ) ?>" value="<?php echo esc_attr( $this->get_field_value( $value['option'] ) ); ?>" />
			</p>
			<?php
		}

	}

	/**
	 * Sitemaps meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.9
	 *
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox( $args = array() ) {

		do_action( 'the_seo_framework_sitemaps_metabox_before' );

		if ( get_option( 'permalink_structure' ) == '' ) {

			$permalink_settings_url = esc_url( admin_url( 'options-permalink.php' ) );
			$here = '<a href="' . $permalink_settings_url  . '" target="_blank" title="' . __( 'Permalink settings', 'autodescription' ) . '">' . _x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';

			?>
			<h4><?php _e( "You're using the default permalink structure.", 'autodescription' ); ?></h4>
			<p><span class="description"><?php _e( "This means we can't output the sitemap through WordPress rewrite.", 'autodescription' ); ?></span></p>
			<hr>
			<p><span class="description"><?php printf( _x( "Change your permalink settings %s (we recommend 'postname').", '%s = here', 'autodescription' ), $here ); ?></span></p>
			<?php

		} else {

			/**
			 * Parse tabs content
			 *
			 * @param array $default_tabs { 'id' = The identifier =>
			 *			array(
			 *				'name' 		=> The name
			 *				'callback' 	=> The callback function, use array for method calling (accepts $this, but isn't used here for optimization purposes)
			 *				'dashicon'	=> Desired dashicon
			 *			)
			 * }
			 *
			 * @since 2.2.9
			 */
			$default_tabs = array(
				'general' => array(
					'name' 		=> __( 'General', 'autodescription' ),
					'callback'	=> array( 'AutoDescription_Metaboxes', 'sitemaps_metabox_general_tab' ),
					'dashicon'	=> 'admin-generic',
				),
				'robots' => array(
					'name'		=> 'Robots.txt',
					'callback'	=> array( 'AutoDescription_Metaboxes', 'sitemaps_metabox_robots_tab' ),
					'dashicon'	=> 'share-alt2',
				),
				'timestamps' => array(
					'name'		=> __( 'Timestamps', 'autodescription' ),
					'callback'	=> array( 'AutoDescription_Metaboxes', 'sitemaps_metabox_timestamps_tab' ),
					'dashicon'	=> 'backup',
				),
				'notify' => array(
					'name'		=> _x( 'Ping', 'Ping or notify search engine', 'autodescription' ),
					'callback'	=> array( 'AutoDescription_Metaboxes', 'sitemaps_metabox_notify_tab' ),
					'dashicon'	=> 'megaphone',
				),
			);

			/**
			 * Applies filter the_seo_framework_sitemaps_settings_tabs
			 *
			 * Used to extend Knowledge Graph tabs
			 *
			 * New filter.
			 * @since 2.3.0
			 *
			 * Removed previous filter.
			 * @since 2.3.5
			 */
			$defaults = (array) apply_filters( 'the_seo_framework_sitemaps_settings_tabs', $default_tabs );

			$tabs = wp_parse_args( $args, $defaults );

			$this->nav_tab_wrapper( 'sitemaps', $tabs, '2.2.8' );

		}

		do_action( 'the_seo_framework_sitemaps_metabox_after' );

	}

	/**
	 * Sitemaps Metabox General Tab Output
	 *
	 * @since 2.2.9
	 *
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox_general_tab() {

		$site_url = $this->the_home_url_from_cache( true );

		$sitemap_url = $site_url . 'sitemap.xml';

		?>
		<h4><?php _e( 'Sitemap Integration Settings', 'autodescription' ); ?></h4>
		<p><span class="description"><?php printf( __( "The Sitemap is an XML file that lists pages and posts for your site along with optional metadata about each post or page. This helps Search Engines crawl your site easier.", 'autodescription' ) ); ?></span></p>
		<p><span class="description"><?php printf( __( "The optional metadata include the post and page modified time and a page priority indication, which is automated.", 'autodescription' ) ); ?></span></p>

		<hr>

		<h4 style="margin-top:0;"><?php printf( __( 'Sitemap Output', 'autodescription' ) ); ?></h4>
		<p>
			<label for="<?php $this->field_id( 'sitemaps_output' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'sitemaps_output' ); ?>" id="<?php $this->field_id( 'sitemaps_output' ); ?>" <?php $this->is_conditional_checked( 'sitemaps_output' ); ?> value="1" <?php checked( $this->get_field_value( 'sitemaps_output' ) ); ?> />
				<?php printf( __( 'Output Sitemap?', 'autodescription' ) ); ?>
			</label>
		</p>
		<?php

		if ( $this->get_option( 'sitemaps_output') ) :
			$here =  '<a href="' . $sitemap_url  . '" target="_blank" title="' . __( 'View sitemap', 'autodescription' ) . '">' . _x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';

			?><p><span class="description"><?php printf( _x( 'The sitemap can be found %s.', '%s = here', 'autodescription' ), $here ); ?></span></p><?php
		endif;

	}

	/**
	 * Sitemaps Metabox Robots Tab Output
	 *
	 * @since 2.2.9
	 *
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox_robots_tab() {

		$site_url = $this->the_home_url_from_cache( true );

		$robots_url = trailingslashit( $site_url ) . 'robots.txt';
		$here =  '<a href="' . $robots_url  . '" target="_blank" title="' . __( 'View robots.txt', 'autodescription' ) . '">' . _x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';

		?>
		<h4><?php printf( __( 'Robots.txt Settings', 'autodescription' ) ) ?></h4>
		<p><span><?php printf( __( 'The robots.txt file is the first thing Search Engine look for. If you add the sitemap location in the robots.txt file, then search engines will look for and index the sitemap.', 'autodescription' ) ); ?></span></p>
		<p><span><?php printf( __( 'If you do not add the sitemap location to the robots.txt file, you will need to notify search engines manually through the Webmaster Console provided by the search engines.', 'autodescription' ) ); ?></span></p>

		<hr>

		<h4><?php printf( __( 'Add sitemap location in robots.txt', 'autodescription' ) ); ?></h4>
		<p>
			<label for="<?php $this->field_id( 'sitemaps_robots' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'sitemaps_robots' ); ?>" id="<?php $this->field_id( 'sitemaps_robots' ); ?>" <?php $this->is_conditional_checked( 'sitemaps_robots' ); ?> value="1" <?php checked( $this->get_field_value( 'sitemaps_robots' ) ); ?> />
				<?php printf( __( 'Add sitemap location in robots?', 'autodescription' ) ); ?>
			</label>
		</p>

		<hr>

		<p><span class="description"><?php printf( _x( 'The robots.txt file can be found %s.', '%s = here', 'autodescription' ), $here ); ?></span></p>
		<?php

	}

	/**
	 * Sitemaps Metabox Timestamps Tab Output
	 *
	 * @since 2.2.9
	 *
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox_timestamps_tab() {

		?>
		<h4><?php printf( __( 'Timestamps Settings', 'autodescription' ) ); ?></h4>
		<p><span class="description"><?php printf( __( 'The modified time hint Search Engines where to look for content changes. It has no impact on SEO value unless you drastically change pages or posts. It then depends on how well your content is constructed.', 'autodescription' ) ); ?></span></p>
		<p><span class="description"><?php printf( __( "By default, the sitemap only outputs the modified date if you've enabled them within the Social Metabox. This setting overrides those settings for the Sitemap.", 'autodescription' ) ); ?></span></p>

		<hr>

		<h4><?php printf( __( 'Output Modified Date', 'autodescription' ) ); ?></h4>
		<p>
			<label for="<?php $this->field_id( 'sitemaps_modified' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'sitemaps_modified' ); ?>" id="<?php $this->field_id( 'sitemaps_modified' ); ?>" <?php $this->is_conditional_checked( 'sitemaps_modified' ); ?> value="1" <?php checked( $this->get_field_value( 'sitemaps_modified' ) ); ?> />
				<?php printf( __( 'Add %s to the sitemap?', 'autodescription' ), $this->code_wrap( '<lastmod>' ) ); ?>
			</label>
		</p>
		<?php

	}

	/**
	 * Sitemaps Metabox Notify Tab Output
	 *
	 * @since 2.2.9
	 *
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox_notify_tab() {

		?>
		<h4><?php printf( __( 'Ping Settings', 'autodescription' ) ); ?></h4>
		<p><span class="description"><?php printf( __( "Notifying Search Engines of a sitemap change is helpful to get your content indexed as soon as possible.", 'autodescription' ) ); ?></span></p>
		<p><span class="description"><?php printf( __( "By default this will happen at most once an hour.", 'autodescription' ) ); ?></span></p>

		<hr>

		<h4><?php printf( __( 'Notify Search Engines', 'autodescription' ) ); ?></h4>
		<p class="fields">
			<label for="<?php $this->field_id( 'ping_google' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'ping_google' ); ?>" id="<?php $this->field_id( 'ping_google' ); ?>" <?php $this->is_conditional_checked( 'ping_google' ); ?> value="1" <?php checked( $this->get_field_value( 'ping_google' ) ); ?> />
				<?php printf( __( 'Notify %s about sitemap changes?', 'autodescription' ), 'Google' ); ?>
			</label>
			<br />
			<label for="<?php $this->field_id( 'ping_bing' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'ping_bing' ); ?>" id="<?php $this->field_id( 'ping_bing' ); ?>" <?php $this->is_conditional_checked( 'ping_bing' ); ?> value="1" <?php checked( $this->get_field_value( 'ping_bing' ) ); ?> />
				<?php printf( __( 'Notify %s about sitemap changes?', 'autodescription' ), 'Bing' ); ?>
			</label>
			<br />
			<label for="<?php $this->field_id( 'ping_yahoo' ); ?>">
				<input type="checkbox" name="<?php $this->field_name( 'ping_yahoo' ); ?>" id="<?php $this->field_id( 'ping_yahoo' ); ?>" <?php $this->is_conditional_checked( 'ping_yahoo' ); ?> value="1" <?php checked( $this->get_field_value( 'ping_yahoo' ) ); ?> />
				<?php printf( __( 'Notify %s about sitemap changes?', 'autodescription' ), 'Yahoo' ); ?>
			</label>
		</p>
		<?php

	}

}
