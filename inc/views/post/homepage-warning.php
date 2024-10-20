<?php
/**
 * @package The_SEO_Framework\Views\Post
 * @subpackage The_SEO_Framework\Admin\Post
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
<div class="tsf-flex-setting tsf-flex" id=tsf-is-homepage-warning>
	<div class="tsf-flex-setting-input tsf-flex">
		<div class="tsf-flex-setting-input-inner-wrap tsf-flex">
			<div class="tsf-flex-setting-input-item tsf-flex">
				<span>
					<?php
					\esc_html_e( 'The fields below may be overwritten by the Homepage Settings found on the SEO Settings page.', 'autodescription' );
					if ( \current_user_can( \THE_SEO_FRAMEWORK_SETTINGS_CAP ) ) {
						echo ' &mdash; ';
						printf(
							'<a href="%s" target=_blank>%s</a>',
							// phpcs:ignore, WordPress.Security.EscapeOutput -- menu_page_url() escapes
							\menu_page_url( \THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG, false ) . '#autodescription-homepage-settings',
							\esc_html__( 'Edit those settings instead.', 'autodescription' ),
						);
					}
					?>
				</span>
			</div>
		</div>
	</div>
</div>
