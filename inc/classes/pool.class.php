<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Generate_Url
 * @subpackage The_SEO_Framework\Getters\URL
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Traits\Internal\Static_Deprecator;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
class Pool extends Legacy_API {

	/**
	 * @since 4.3.0
	 * @var class[] The class store. Used in favor of memo() for a chain would become expensive.
	 */
	private static $pool = [];

	/**
	 * Returns the Query class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Helper\Query
	 */
	public static function query() {
		return static::$pool['query'] ??= new class extends Helper\Query {
			use Static_Deprecator;

			// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
			private $colloquial_handle     = 'tsf()->query()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
			// phpcs:enable, Squiz.Commenting.VariableComment.Missing

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Helper\Query\Utils
			 */
			public static function utils() {
				return static::$subpool['utils'] ??= new class extends Helper\Query\Utils {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->query()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Helper\Query\Cache
			 */
			public static function cache() {
				return static::$subpool['cache'] ??= new class extends Helper\Query\Cache {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->query()->cache()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}
		};
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
	public static function post_types() {
		return static::$pool['post_types'] ??= new class extends Helper\Post_Types {
			use Static_Deprecator;

			// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
			private $colloquial_handle     = 'tsf()->post_types()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
			// phpcs:enable, Squiz.Commenting.VariableComment.Missing
		};
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
	public static function taxonomies() {
		return static::$pool['taxonomies'] ??= new class extends Helper\Taxonomies {
			use Static_Deprecator;

			// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
			private $colloquial_handle     = 'tsf()->taxonomies()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
			// phpcs:enable, Squiz.Commenting.VariableComment.Missing
		};
	}

	/**
	 * Returns the Robots API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Factory\Robots
	 */
	public static function robots() {
		return static::$pool['robots'] ??= new class extends Meta\Factory\Robots {
			use Static_Deprecator;

			// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
			private $colloquial_handle     = 'tsf()->robots()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
			// phpcs:enable, Squiz.Commenting.VariableComment.Missing
		};
	}

	/**
	 * Returns the URI API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Factory\URI
	 */
	public static function uri() {
		return static::$pool['uri'] ??= new class extends Meta\Factory\URI {
			use Static_Deprecator;

			// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
			private $colloquial_handle     = 'tsf()->uri()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
			// phpcs:enable, Squiz.Commenting.VariableComment.Missing

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Meta\Factory\URI\Utils
			 */
			public static function utils() {
				return static::$subpool['utils'] ??= new class extends Meta\Factory\URI\Utils {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->uri()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}
		};
	}

	/**
	 * Returns the Title API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Factory\Title
	 */
	public static function title() {
		return static::$pool['title'] ??= new class extends Meta\Factory\Title {
			use Static_Deprecator;

			// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
			private $colloquial_handle     = 'tsf()->title()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
			// phpcs:enable, Squiz.Commenting.VariableComment.Missing

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Meta\Factory\Title\Utils
			 */
			public static function utils() {
				return static::$subpool['utils'] ??= new class extends Meta\Factory\Title\Utils {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->title()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Meta\Factory\Title\Conditions
			 */
			public static function conditions() {
				return static::$subpool['conditions'] ??= new class extends Meta\Factory\Title\Conditions {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->title()->conditions()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}
		};
	}

	/**
	 * Returns the Description API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Factory\Description
	 */
	public static function description() {
		return static::$pool['description'] ??= new class extends Meta\Factory\Description {
			use Static_Deprecator;

			// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
			private $colloquial_handle     = 'tsf()->description()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
			// phpcs:enable, Squiz.Commenting.VariableComment.Missing

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Meta\Factory\Description\Excerpt
			 */
			public static function excerpt() {
				return static::$subpool['excerpt'] ??= new class extends Meta\Factory\Description\Excerpt {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->description()->excerpt()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}
		};
	}

	/**
	 * Returns the Open_Graph API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Factory\Open_Graph
	 */
	public static function open_graph() {
		return static::$pool['open_graph'] ??= new class extends Meta\Factory\Open_Graph {
			use Static_Deprecator;

			// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
			private $colloquial_handle     = 'tsf()->open_graph()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
			// phpcs:enable, Squiz.Commenting.VariableComment.Missing
		};
	}

	/**
	 * Returns the Facebook API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Factory\Facebook
	 */
	public static function facebook() {
		return static::$pool['facebook'] ??= new class extends Meta\Factory\Facebook {
			use Static_Deprecator;

			// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
			private $colloquial_handle     = 'tsf()->facebook()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
			// phpcs:enable, Squiz.Commenting.VariableComment.Missing
		};
	}

	/**
	 * Returns the Twitter API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Factory\Twitter
	 */
	public static function twitter() {
		return static::$pool['twitter'] ??= new class extends Meta\Factory\Twitter {
			use Static_Deprecator;

			// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
			private $colloquial_handle     = 'tsf()->twitter()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
			// phpcs:enable, Squiz.Commenting.VariableComment.Missing
		};
	}

	/**
	 * Returns the Image API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Factory\Image
	 */
	public static function image() {
		return static::$pool['image'] ??= new class extends Meta\Factory\Image {
			use Static_Deprecator;

			// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
			private $colloquial_handle     = 'tsf()->image()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
			// phpcs:enable, Squiz.Commenting.VariableComment.Missing

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Meta\Factory\Image\Utils
			 */
			public static function utils() {
				return static::$subpool['utils'] ??= new class extends Meta\Factory\Image\Utils {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->image()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}
		};
	}

	/**
	 * Returns the Breadcrumbs API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Factory\Breadcrumbs
	 */
	public static function breadcrumbs() {
		return static::$pool['breadcrumbs'] ??= new class extends Meta\Factory\Breadcrumbs {
			use Static_Deprecator;

			// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
			private $colloquial_handle     = 'tsf()->breadcrumbs()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
			// phpcs:enable, Squiz.Commenting.VariableComment.Missing
		};
	}

	/**
	 * Returns the Structured_Data API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Factory\Schema
	 */
	public static function schema() {
		return static::$pool['schema'] ??= new class extends Meta\Factory\Schema {
			use Static_Deprecator;

			// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
			private $colloquial_handle     = 'tsf()->schema()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
			// phpcs:enable, Squiz.Commenting.VariableComment.Missing

			/**
			 * @since 4.3.0
			 * @var array[string,string] A list of valid entity class names.
			 */
			public $entities = [
				'Author'         => Meta\Factory\Schema\Author::class,
				'BreadcrumbList' => Meta\Factory\Schema\BreadcrumbList::class,
				'Organization'   => Meta\Factory\Schema\Organization::class,
				'Person'         => Meta\Factory\Schema\Person::class,
				'WebPage'        => Meta\Factory\Schema\WebPage::class,
				'WebSite'        => Meta\Factory\Schema\WebSite::class,
				'Reference'      => Meta\Factory\Schema\Reference::class,
			];

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Meta\Factory\Schema\Utils
			 */
			public static function utils() {
				return static::$subpool['utils'] ??= new class extends Meta\Factory\Schema\Utils {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->schema()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}
		};
	}

	/**
	 * Returns a pool of Data classes as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 4.3.0
	 * @api Not used internally.
	 *
	 * @return anonymous An anonymous data class containing subpools.
	 */
	public static function data() {
		return static::$pool['data'] ??= new class {
			use Static_Deprecator;

			// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
			private $colloquial_handle     = 'tsf()->data()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
			// phpcs:enable, Squiz.Commenting.VariableComment.Missing

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Data\Blog
			 */
			public static function blog() {
				return static::$subpool['blog'] ??= new class extends Data\Blog {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->data()->blog()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Data\Network
			 */
			public static function network() {
				return static::$subpool['network'] ??= new class extends Data\Network {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->data()->network()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Data\Options
			 */
			public static function options() {
				return static::$subpool['options'] ??= new class extends Data\Options {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->data()->options()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Data\Post
			 */
			public static function post() {
				return static::$subpool['post'] ??= new class extends Data\Post {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->data()->post()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Data\Term
			 */
			public static function term() {
				return static::$subpool['term'] ??= new class extends Data\Term {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->data()->term()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Data\Transient
			 */
			public static function transient() {
				return static::$subpool['transient'] ??= new class extends Data\Transient {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->data()->transient()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}

			/**
			 * @since 4.3.0
			 * @return \The_SEO_Framework\Data\User
			 */
			public static function user() {
				return static::$subpool['user'] ??= new class extends Data\User {
					use Static_Deprecator;

					// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.
					private $colloquial_handle     = 'tsf()->data()->user()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
					// phpcs:enable, Squiz.Commenting.VariableComment.Missing
				};
			}
		};
	}
}
