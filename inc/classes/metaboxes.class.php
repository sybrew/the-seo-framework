<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

/**
 * Class The_SEO_Framework\Metaboxes
 *
 * Outputs Network and Site SEO settings meta boxes
 *
 * @since 2.8.0
 */
class Metaboxes extends Site_Options {

	/**
	 * Constructor, load parent constructor.
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Setting nav tab wrappers.
	 * Outputs Tabs and settings content.
	 *
	 * @since 2.3.6
	 * @since 2.6.0 Refactored.
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
	 * @param string $version the The SEO Framework version for debugging. May be emptied.
	 * @param bool $use_tabs Whether to output tabs, only works when $tabs is greater than 1.
	 */
	public function nav_tab_wrapper( $id, $tabs = array(), $version = '2.3.6', $use_tabs = true ) {

		//* Whether tabs are active.
		$use_tabs = $use_tabs && count( $tabs ) > 1;

		/**
		 * Start navigational tabs.
		 *
		 * Don't output navigation if $use_tabs is false and the amount of tabs is 1 or lower.
		 */
		if ( $use_tabs ) :
			?>
			<div class="tsf-nav-tab-wrapper hide-if-no-js" id="<?php echo \esc_attr( $id . '-tabs-wrapper' ); ?>">
				<?php
				$count = 1;
				foreach ( $tabs as $tab => $value ) :
					$dashicon = isset( $value['dashicon'] ) ? $value['dashicon'] : '';
					$name = isset( $value['name'] ) ? $value['name'] : '';

					$checked = 1 === $count ? 'checked' : '';
					$the_id = \esc_attr( $id . '-tab-' . $tab );
					$the_name = \esc_attr( $id . '-tabs' );

					//* All output below is escaped.
					?>
					<div class="tsf-tab">
						<input type="radio" class="tsf-tabs-radio" id="<?php echo $the_id; ?>" name="<?php echo $the_name; ?>" <?php echo $checked; ?>>
						<label for="<?php echo $the_id; ?>" class="tsf-nav-tab">
							<?php echo $dashicon ? '<span class="dashicons dashicons-' . \esc_attr( $dashicon ) . ' tsf-dashicons-tabs"></span>' : ''; ?>
							<?php echo $name ? '<span class="tsf-nav-desktop">' . \esc_attr( $name ) . '</span>' : ''; ?>
						</label>
					</div>
					<?php

					$count++;
				endforeach;
				?>
			</div>
			<?php
		endif;

		/**
		 * Start Content.
		 *
		 * The content is relative to the navigation and outputs navigational tabs too, but uses CSS to become invisible on JS.
		 */
		$count = 1;
		foreach ( $tabs as $tab => $value ) :

			$the_id = $id . '-tab-' . $tab . '-content';
			$the_name = $id . '-tabs-content';

			//* Current tab for JS.
			$current = 1 === $count ? ' tsf-active-tab-content' : '';

			?>
			<div class="tsf-tabs-content <?php echo \esc_attr( $the_name . $current ); ?>" id="<?php echo \esc_attr( $the_id ); ?>" >
				<?php
				//* No-JS tabs.
				if ( $use_tabs ) :
					$dashicon = isset( $value['dashicon'] ) ? $value['dashicon'] : '';
					$name = isset( $value['name'] ) ? $value['name'] : '';

					?>
					<div class="hide-if-js tsf-content-no-js">
						<div class="tsf-tab tsf-tab-no-js">
							<span class="tsf-nav-tab tsf-active-tab">
								<?php echo $dashicon ? '<span class="dashicons dashicons-' . \esc_attr( $dashicon ) . ' tsf-dashicons-tabs"></span>' : ''; ?>
								<?php echo $name ? '<span>' . \esc_attr( $name ) . '</span>' : ''; ?>
							</span>
						</div>
					</div>
					<?php
				endif;

				$callback = isset( $value['callback'] ) ? $value['callback'] : '';

				if ( $callback ) {
					$params = isset( $value['args'] ) ? $value['args'] : '';
					//* Should already be escaped.
					echo $this->call_function( $callback, $version, $params );
				}
				?>
			</div>
			<?php

			$count++;
		endforeach;
	}

	/**
	 * Outputs General Settings meta box on the Site SEO Settings page.
	 *
	 * @since 2.8.0
	 *
	 * @param object|null $post The current post object.
	 * @param array $args The metabox arguments.
	 */
	public function general_metabox( $post = null, $args = array() ) {
		\do_action( 'the_seo_framework_general_metabox_before' );
		$this->get_view( 'metaboxes/general-metabox', $args );
		\do_action( 'the_seo_framework_general_metabox_after' );
	}

	/**
	 * Outputs General Settings meta box general tab.
	 *
	 * @since 2.8.0
	 * @see $this->general_metabox() : Callback for General Settings box.
	 */
	public function general_metabox_general_tab() {
		$this->get_view( 'metaboxes/general-metabox', array(), 'general' );
	}

	/**
	 * Outputs General Settings meta box layout tab.
	 *
	 * @since 2.8.0
	 * @see $this->general_metabox() : Callback for General Settings box.
	 */
	public function general_metabox_layout_tab() {
		$this->get_view( 'metaboxes/general-metabox', array(), 'layout' );
	}

	/**
	 * Outputs General Settings meta box performance tab.
	 *
	 * @since 2.8.0
	 * @see $this->general_metabox() : Callback for General Settings box.
	 */
	public function general_metabox_performance_tab() {
		$this->get_view( 'metaboxes/general-metabox', array(), 'performance' );
	}

	/**
	 * Outputs General Settings meta box canonical tab.
	 *
	 * @since 2.8.0
	 * @see $this->general_metabox() : Callback for General Settings box.
	 */
	public function general_metabox_canonical_tab() {
		$this->get_view( 'metaboxes/general-metabox', array(), 'canonical' );
	}

	/**
	 * Outputs General Settings meta box timestamps tab.
	 *
	 * @since 3.0.0
	 * @see $this->general_metabox() : Callback for General Settings box.
	 */
	public function general_metabox_timestamps_tab() {
		$this->get_view( 'metaboxes/general-metabox', array(), 'timestamps' );
	}

	/**
	 * Title meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.2
	 *
	 * @param object|null $post The current post object.
	 * @param array $args The metabox arguments.
	 */
	public function title_metabox( $post = null, $args = array() ) {
		\do_action( 'the_seo_framework_title_metabox_before' );
		$this->get_view( 'metaboxes/title-metabox', $args );
		\do_action( 'the_seo_framework_title_metabox_after' );
	}

	/**
	 * Title meta box general tab.
	 *
	 * @since 2.6.0
	 * @see $this->title_metabox() : Callback for Title Settings box.
	 */
	public function title_metabox_general_tab() {
		$this->get_view( 'metaboxes/title-metabox', array(), 'general' );
	}

	/**
	 * Title meta box general tab.
	 *
	 * @since 2.6.0
	 * @see $this->title_metabox() : Callback for Title Settings box.
	 *
	 * @param array $examples : array {
	 * 		'left'	=> Left Example
	 * 		'right'	=> Right Example
	 * }
	 */
	public function title_metabox_additions_tab( $examples = array() ) {
		$this->get_view( 'metaboxes/title-metabox', get_defined_vars(), 'additions' );
	}

	/**
	 * Title meta box prefixes tab.
	 *
	 * @since 2.6.0
	 * @see $this->title_metabox() : Callback for Title Settings box.
	 *
	 * @param array $additions : array {
	 * 		'left'	=> Left Example Addtitions
	 * 		'right'	=> Right Example Additions
	 * }
	 * @param bool $showleft The example location.
	 */
	public function title_metabox_prefixes_tab( $additions = array(), $showleft = false ) {
		$this->get_view( 'metaboxes/title-metabox', get_defined_vars(), 'prefixes' );
	}

	/**
	 * Description meta box on the Site SEO Settings page.
	 *
	 * @since 2.3.4
	 *
	 * @param object|null $post The current post object.
	 * @param array $args The metabox arguments.
	 */
	public function description_metabox( $post = null, $args = array() ) {
		\do_action( 'the_seo_framework_description_metabox_before' );
		$this->get_view( 'metaboxes/description-metabox', $args );
		\do_action( 'the_seo_framework_description_metabox_after' );
	}

	/**
	 * Description meta box general tab.
	 *
	 * @since 2.6.0
	 * @see $this->description_metabox() Callback for Description Settings box.
	 */
	public function description_metabox_general_tab() {
		$this->get_view( 'metaboxes/description-metabox', array(), 'general' );
	}

	/**
	 * Description meta box additions tab.
	 *
	 * @since 2.6.0
	 * @see $this->description_metabox() Callback for Description Settings box.
	 */
	public function description_metabox_additions_tab() {
		$this->get_view( 'metaboxes/description-metabox', array(), 'additions' );
	}

	/**
	 * Robots meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.2
	 *
	 * @param object|null $post The current post object.
	 * @param array $args The metabox arguments.
	 */
	public function robots_metabox( $post = null, $args = array() ) {
		\do_action( 'the_seo_framework_robots_metabox_before' );
		$this->get_view( 'metaboxes/robots-metabox', $args );
		\do_action( 'the_seo_framework_robots_metabox_after' );
	}

	/**
	 * Robots Metabox General Tab output.
	 *
	 * @since 2.2.4
	 * @see $this->robots_metabox() Callback for Robots Settings box.
	 */
	protected function robots_metabox_general_tab() {
		$this->get_view( 'metaboxes/robots-metabox', array(), 'general' );
	}

	/**
	 * Robots Metabox "No-: Index/Follow/Archive" Tab output.
	 *
	 * @since 2.2.4
	 * @see $this->robots_metabox() Callback for Robots Settings box.
	 *
	 * @param array $types The post types
	 * @param array $robots The robots option values : {
	 *		'value' string The robots option value.
	 *		'name' string The robots name.
	 *		'desc' string Explains what the robots type does.
	 * }
	 */
	protected function robots_metabox_no_tab( $types, $robots ) {
		$this->get_view( 'metaboxes/robots-metabox', get_defined_vars(), 'no' );
	}

	/**
	 * Home Page meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.2
	 *
	 * @param object|null $post The current post object.
	 * @param array $args The navigation tabs args.
	 */
	public function homepage_metabox( $post = null, $args = array() ) {
		\do_action( 'the_seo_framework_homepage_metabox_before' );
		$this->get_view( 'metaboxes/homepage-metabox', $args );
		\do_action( 'the_seo_framework_homepage_metabox_after' );
	}

	/**
	 * HomePage Metabox General Tab Output.
	 *
	 * @since 2.7.0
	 * @see $this->homepage_metabox() Callback for HomePage Settings box.
	 */
	public function homepage_metabox_general_tab() {
		$this->get_view( 'metaboxes/homepage-metabox', array(), 'general' );
	}

	/**
	 * HomePage Metabox Additions Tab Output.
	 *
	 * @since 2.7.0
	 * @see $this->homepage_metabox() Callback for HomePage Settings box.
	 */
	public function homepage_metabox_additions_tab() {
		$this->get_view( 'metaboxes/homepage-metabox', array(), 'additions' );
	}

	/**
	 * HomePage Metabox Robots Tab Output
	 *
	 * @since 2.7.0
	 * @see $this->homepage_metabox() Callback for HomePage Settings box.
	 */
	public function homepage_metabox_robots_tab() {
		$this->get_view( 'metaboxes/homepage-metabox', array(), 'robots' );
	}

	/**
	 * HomePage Metabox Social Tab Output
	 *
	 * @since 2.9.0
	 * @see $this->homepage_metabox() Callback for HomePage Settings box.
	 */
	public function homepage_metabox_social_tab() {
		$this->get_view( 'metaboxes/homepage-metabox', array(), 'social' );
	}

	/**
	 * Social meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.2
	 *
	 * @param object|null $post The current post object.
	 * @param array $args the social tabs arguments.
	 */
	public function social_metabox( $post = null, $args = array() ) {
		\do_action( 'the_seo_framework_social_metabox_before' );
		$this->get_view( 'metaboxes/social-metabox', $args );
		\do_action( 'the_seo_framework_social_metabox_after' );
	}

	/**
	 * Social Metabox General Tab output.
	 *
	 * @since 2.2.2
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	protected function social_metabox_general_tab() {
		$this->get_view( 'metaboxes/social-metabox', array(), 'general' );
	}

	/**
	 * Social Metabox Facebook Tab output.
	 *
	 * @since 2.2.2
	 *
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	protected function social_metabox_facebook_tab() {
		$this->get_view( 'metaboxes/social-metabox', array(), 'facebook' );
	}

	/**
	 * Social Metabox Twitter Tab output.
	 *
	 * @since 2.2.2
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	protected function social_metabox_twitter_tab() {
		$this->get_view( 'metaboxes/social-metabox', array(), 'twitter' );
	}

	/**
	 * Social Metabox PostDates Tab output.
	 *
	 * @since 2.2.4
	 * @see $this->social_metabox() Callback for Social Settings box.
	 */
	public function social_metabox_postdates_tab() {
		$this->get_view( 'metaboxes/social-metabox', array(), 'postdates' );
	}

	/**
	 * Webmaster meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.4
	 *
	 * @param object|null $post The current post object.
	 * @param array $args the social tabs arguments.
	 */
	public function webmaster_metabox( $post = null, $args = array() ) {
		\do_action( 'the_seo_framework_webmaster_metabox_before' );
		$this->get_view( 'metaboxes/webmaster-metabox', $args );
		\do_action( 'the_seo_framework_webmaster_metabox_after' );
	}

	/**
	 * Sitemaps meta box on the Site SEO Settings page.
	 *
	 * @since 2.2.9
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 *
	 * @param object|null $post The current post object.
	 * @param array $args the social tabs arguments.
	 */
	public function sitemaps_metabox( $post = null, $args = array() ) {
		\do_action( 'the_seo_framework_sitemaps_metabox_before' );
		$this->get_view( 'metaboxes/sitemaps-metabox', $args );
		\do_action( 'the_seo_framework_sitemaps_metabox_after' );
	}

	/**
	 * Sitemaps Metabox General Tab output.
	 *
	 * @since 2.2.9
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox_general_tab() {
		$this->get_view( 'metaboxes/sitemaps-metabox', array(), 'general' );
	}

	/**
	 * Sitemaps Metabox Robots Tab output.
	 *
	 * @since 2.2.9
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox_robots_tab() {
		$this->get_view( 'metaboxes/sitemaps-metabox', array(), 'robots' );
	}

	/**
	 * Sitemaps Metabox Timestamps Tab output.
	 *
	 * @since 2.2.9
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox_timestamps_tab() {
		$this->get_view( 'metaboxes/sitemaps-metabox', array(), 'timestamps' );
	}

	/**
	 * Sitemaps Metabox Notify Tab output.
	 *
	 * @since 2.2.9
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox_notify_tab() {
		$this->get_view( 'metaboxes/sitemaps-metabox', array(), 'notify' );
	}

	/**
	 * Sitemaps Metabox Style Tab output.
	 *
	 * @since 2.8.0
	 * @see $this->sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public function sitemaps_metabox_style_tab() {
		$this->get_view( 'metaboxes/sitemaps-metabox', array(), 'style' );
	}

	/**
	 * Feed Metabox on the Site SEO Settings page.
	 *
	 * @since 2.5.2
	 *
	 * @param object|null $post The current post object.
	 * @param array $args the social tabs arguments.
	 */
	public function feed_metabox( $post = null, $args = array() ) {
		\do_action( 'the_seo_framework_feed_metabox_before' );
		$this->get_view( 'metaboxes/feed-metabox', $args );
		\do_action( 'the_seo_framework_feed_metabox_after' );
	}

	/**
	 * Schema Metabox on the Site SEO Settings page.
	 *
	 * @since 2.6.0
	 *
	 * @param object|null $post The current post object.
	 * @param array $args the social tabs arguments.
	 */
	public function schema_metabox( $post = null, $args = array() ) {
		\do_action( 'the_seo_framework_schema_metabox_before' );
		$this->get_view( 'metaboxes/schema-metabox', $args );
		\do_action( 'the_seo_framework_schema_metabox_after' );
	}

	/**
	 * Schema Metabox General Tab output.
	 *
	 * @since 2.8.0
	 * @since 3.0.0 No longer used.
	 * @see $this->schema_metabox() Callback for Schema.org Settings box.
	 */
	public function schema_metabox_general_tab() {
		$this->get_view( 'metaboxes/schema-metabox', array(), 'general' );
	}

	/**
	 * Schema Metabox Structure Tab output.
	 *
	 * @since 2.8.0
	 * @see $this->schema_metabox() Callback for Schema.org Settings box.
	 */
	public function schema_metabox_structure_tab() {
		$this->get_view( 'metaboxes/schema-metabox', array(), 'structure' );
	}

	/**
	 * Schema Metabox PResence Tab output.
	 *
	 * @since 2.8.0
	 * @see $this->schema_metabox() Callback for Schema.org Settings box.
	 */
	public function schema_metabox_presence_tab() {
		$this->get_view( 'metaboxes/schema-metabox', array(), 'presence' );
	}
}
