<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Admin\Template::verify_secret( $secret ) or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2017 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

?>
<div class="metabox-holder columns-2">
	<div class=postbox-container-1>
		<?php
		\do_action( 'the_seo_framework_before_siteadmin_metaboxes', \tsf()->seo_settings_page_hook );

		\do_meta_boxes( \tsf()->seo_settings_page_hook, 'main', null );

		if ( isset( $GLOBALS['wp_meta_boxes'][ \tsf()->seo_settings_page_hook ]['main_extra'] ) )
			\do_meta_boxes( \tsf()->seo_settings_page_hook, 'main_extra', null );

		\do_action( 'the_seo_framework_after_siteadmin_metaboxes', \tsf()->seo_settings_page_hook );
		?>
	</div>
	<div class=postbox-container-2>
		<?php
		\do_action( 'the_seo_framework_before_siteadmin_metaboxes_side', \tsf()->seo_settings_page_hook );

		/**
		 * @TODO fill this in...?
		 */

		\do_action( 'the_seo_framework_after_siteadmin_metaboxes_side', \tsf()->seo_settings_page_hook );
		?>
	</div>
</div>
<?php
