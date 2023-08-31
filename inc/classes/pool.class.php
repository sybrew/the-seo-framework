<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Generate_Url
 * @subpackage The_SEO_Framework\Getters\URL
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\memo;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Pool
 *
 * Holds a pool of subobjects.
 *
 * @since 4.3.0
 * @link https://en.wikipedia.org/wiki/Object_pool_pattern
 */
class Pool extends Core {

	/**
	 * Returns the query class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Helper\Query
	 */
	public function query() {
		return memo() ?? memo(
			new class extends Helper\Query {
				use Traits\Internal\Static_Deprecator;

				// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
				private $colloquial_handle     = 'tsf()->query()';
				private $deprecated_methods    = [];
				private $deprecated_properties = [];
				// phpcs:enable, Squiz.Commenting.VariableComment.Missing
			}
		);
	}
}
