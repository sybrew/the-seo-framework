<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * This file contains most functions that have been deprecated.
 *
 * @since 2.1.6
 *
 * Emptied.
 * @since 2.3.5 ( ~2.5 months later )
 */

/**
 * @deprecated
 *
 * @since 2.3.7
 *
 * @see the_seo_framework_load();
 */
function hmpl_ad_load() {
	_deprecated_function( __FUNCTION__, $this->the_seo_framework_version( '2.3.7' ), 'the_seo_framework_load()' );

	return the_seo_framework_load();
}
