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
 * Holds a pool of proxied composite objects, so we can keep the facade tsf().
 * The objects are decorated with Static Deprecator, allowing us to deprecate
 * methods and properties quickly.
 *
 * @todo: If the subobjects require complex fallbacks, put them in a new \Internal
 *        subobject. Create private class constant to hold that class location.
 *
 * @since 4.3.0
 * @link https://en.wikipedia.org/wiki/Object_pool_pattern
 */
class Pool extends Core {

	/**
	 * Returns the Query class as instantiated object with deprecation capabilities.
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

	/**
	 * Returns the Query_Utils class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Helper\Query_Utils
	 */
	public function query_utils() {
		return memo() ?? memo(
			new class extends Helper\Query_Utils {
				use Traits\Internal\Static_Deprecator;

				// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
				private $colloquial_handle     = 'tsf()->query_utils()';
				private $deprecated_methods    = [];
				private $deprecated_properties = [];
				// phpcs:enable, Squiz.Commenting.VariableComment.Missing
			}
		);
	}

	/**
	 * Returns the Post_Types class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Helper\Post_Types
	 */
	public function post_types() {
		return memo() ?? memo(
			new class extends Helper\Post_Types {
				use Traits\Internal\Static_Deprecator;

				// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
				private $colloquial_handle     = 'tsf()->post_types()';
				private $deprecated_methods    = [];
				private $deprecated_properties = [];
				// phpcs:enable, Squiz.Commenting.VariableComment.Missing
			}
		);
	}

	/**
	 * Returns the Taxonomies class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Helper\Taxonomies
	 */
	public function taxonomies() {
		return memo() ?? memo(
			new class extends Helper\Taxonomies {
				use Traits\Internal\Static_Deprecator;

				// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
				private $colloquial_handle     = 'tsf()->taxonomies()';
				private $deprecated_methods    = [];
				private $deprecated_properties = [];
				// phpcs:enable, Squiz.Commenting.VariableComment.Missing
			}
		);
	}

	/**
	 * Returns the Robots class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Factory\Robots\API
	 */
	public function robots() {
		return memo() ?? memo(
			new class extends Meta\Factory\Robots\API {
				use Traits\Internal\Static_Deprecator;

				// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
				private $colloquial_handle     = 'tsf()->robots()';
				private $deprecated_methods    = [];
				private $deprecated_properties = [];
				// phpcs:enable, Squiz.Commenting.VariableComment.Missing
			}
		);
	}
}
