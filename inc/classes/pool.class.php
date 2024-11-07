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

/**
 * Class The_SEO_Framework\Pool
 *
 * Holds a pool of proxied composite objects, so we can keep the facade tsf().
 * The objects are decorated with Static Deprecator, allowing us to deprecate
 * methods and properties quickly.
 *
 * @NOTE: STATIC pools and their STATIC functions MUST BE CALLED in a NON-STATIC manner.
 *        Do NOT use   tsf()::admin()::layout()::make_single_select_form();
 *        Instead, use tsf()->admin()->layout()->make_single_select_form();
 *        Failing to do so might result in a crash when we need to deprecate a call,
 *        defeating the purpose of the static deprecator.
 *
 * @todo: If the subobjects require complex fallbacks, put them in a new \Internal
 *        subobject. Create private class constant to hold that class location.
 *
 * @since 5.0.0
 * @link https://en.wikipedia.org/wiki/Object_pool_pattern
 * You can access these methods via `tsf()` and `the_seo_framework()`.
 */
class Pool extends Legacy_API {

	/**
	 * @since 5.0.0
	 * @var class[] The class store. Used in favor of memo() for a chain would become expensive.
	 */
	private static $pool = [];

	// phpcs:disable, Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.

	/**
	 * Returns a pool of Admin classes as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \Closure An anononymous class with subpools.
	 */
	public static function admin() {
		return static::$pool['admin'] ??= new class {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->admin()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \Closure An anononymous class with subpools.
			 */
			public static function layout() {
				return static::$subpool['layout'] ??= new class {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->admin()->layout()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];

					/**
					 * @since 5.0.0
					 * @return \The_SEO_Framework\Admin\Settings\Layout\HTML
					 */
					public static function form() {
						return static::$subpool['form'] ??= new class extends Admin\Settings\Layout\Form {
							use Static_Deprecator;

							private $colloquial_handle     = 'tsf()->admin()->layout()->form()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}

					/**
					 * @since 5.0.0
					 * @return \The_SEO_Framework\Admin\Settings\Layout\HTML
					 */
					public static function html() {
						return static::$subpool['html'] ??= new class extends Admin\Settings\Layout\HTML {
							use Static_Deprecator;

							private $colloquial_handle     = 'tsf()->admin()->layout()->html()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Admin\Menu
			 */
			public static function menu() {
				return static::$subpool['menu'] ??= new class extends Admin\Menu {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->admin()->menu()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Admin\Notice
			 */
			public static function notice() {
				return static::$subpool['notice'] ??= new class extends Admin\Notice {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->admin()->notice()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];

					/**
					 * @since 5.0.0
					 * @return \The_SEO_Framework\Admin\Notice\Persistent
					 */
					public static function persistent() {
						return static::$subpool['exclusion'] ??= new class extends Admin\Notice\Persistent {
							use Static_Deprecator;

							private $colloquial_handle     = 'tsf()->admin()->notice()->persistent()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Admin\Settings\Layout\HTML
			 */
			public static function utils() {
				return static::$subpool['utils'] ??= new class extends Admin\Utils {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->admin()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Breadcrumbs API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Breadcrumbs
	 */
	public static function breadcrumbs() {
		return static::$pool['breadcrumbs'] ??= new class extends Meta\Breadcrumbs {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->breadcrumbs()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns a pool of Data classes as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \Closure An anononymous class with subpools.
	 */
	public static function data() {
		return static::$pool['data'] ??= new class {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->data()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Data\Blog
			 */
			public static function blog() {
				return static::$subpool['blog'] ??= new class extends Data\Blog {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->data()->blog()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Data\Plugin
			 */
			public static function plugin() {
				return static::$subpool['plugin'] ??= new class extends Data\Plugin {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->data()->plugin()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];

					/**
					 * @since 5.0.0
					 * @return \The_SEO_Framework\Data\Plugin\Filter
					 */
					public static function filter() {
						return static::$subpool['filter'] ??= new class extends Data\Plugin\Filter {
							use Static_Deprecator;

							private $colloquial_handle     = 'tsf()->data()->plugin()->filter()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}
					/**
					 * @since 5.0.0
					 * @return \The_SEO_Framework\Data\Plugin\Helper
					 */
					public static function helper() {
						return static::$subpool['helper'] ??= new class extends Data\Plugin\Helper {
							use Static_Deprecator;

							private $colloquial_handle     = 'tsf()->data()->plugin()->helper()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}

					/**
					 * @since 5.0.0
					 * @return \The_SEO_Framework\Data\Plugin\Post
					 */
					public static function post() {
						return static::$subpool['post'] ??= new class extends Data\Plugin\Post {
							use Static_Deprecator;

							private $colloquial_handle     = 'tsf()->data()->plugin()->post()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}

					/**
					 * @since 5.0.0
					 * @return \The_SEO_Framework\Data\Plugin\PTA
					 */
					public static function pta() {
						return static::$subpool['pta'] ??= new class extends Data\Plugin\PTA {
							use Static_Deprecator;

							private $colloquial_handle     = 'tsf()->data()->plugin()->pta()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}

					/**
					 * @since 5.0.0
					 * @return \The_SEO_Framework\Data\Plugin\Setup
					 */
					public static function setup() {
						return static::$subpool['setup'] ??= new class extends Data\Plugin\Setup {
							use Static_Deprecator;

							private $colloquial_handle     = 'tsf()->data()->plugin()->setup()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}

					/**
					 * @since 5.0.0
					 * @return \The_SEO_Framework\Data\Plugin\Term
					 */
					public static function term() {
						return static::$subpool['term'] ??= new class extends Data\Plugin\Term {
							use Static_Deprecator;

							private $colloquial_handle     = 'tsf()->data()->plugin()->term()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}

					/**
					 * @since 5.0.0
					 * @return \The_SEO_Framework\Data\Plugin\User
					 */
					public static function user() {
						return static::$subpool['user'] ??= new class extends Data\Plugin\User {
							use Static_Deprecator;

							private $colloquial_handle     = 'tsf()->data()->plugin()->user()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Data\Post
			 */
			public static function post() {
				return static::$subpool['post'] ??= new class extends Data\Post {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->data()->post()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Data\Term
			 */
			public static function term() {
				return static::$subpool['term'] ??= new class extends Data\Term {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->data()->term()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Data\User
			 */
			public static function user() {
				return static::$subpool['user'] ??= new class extends Data\User {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->data()->user()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Description API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Description
	 */
	public static function description() {
		return static::$pool['description'] ??= new class extends Meta\Description {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->description()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Meta\Description\Excerpt
			 */
			public static function excerpt() {
				return static::$subpool['excerpt'] ??= new class extends Meta\Description\Excerpt {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->description()->excerpt()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Escape API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Data\Filter\Escape
	 */
	public static function escape() {
		return static::$pool['escape'] ??= new class extends Data\Filter\Escape {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->filter()->escape()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Facebook API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Facebook
	 */
	public static function facebook() {
		return static::$pool['facebook'] ??= new class extends Meta\Facebook {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->facebook()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns a pool of Format classes as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \Closure An anononymous class with subpools.
	 */
	public static function format() {
		return static::$pool['format'] ??= new class {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->format()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Helper\Format\Arrays
			 */
			public static function arrays() {
				return static::$subpool['arrays'] ??= new class extends Helper\Format\Arrays {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->format()->arrays()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Helper\Format\Color
			 */
			public static function color() {
				return static::$subpool['color'] ??= new class extends Helper\Format\Color {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->format()->color()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.5
			 * @return \The_SEO_Framework\Helper\Format\Minify
			 */
			public static function minify() {
				return static::$subpool['minify'] ??= new class extends Helper\Format\Minify {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->format()->minify()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Helper\Format\HTML
			 */
			public static function html() {
				return static::$subpool['html'] ??= new class extends Helper\Format\HTML {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->format()->html()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Helper\Format\Markdown
			 */
			public static function markdown() {
				return static::$subpool['markdown'] ??= new class extends Helper\Format\Markdown {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->format()->markdown()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Helper\Format\Strings
			 */
			public static function strings() {
				return static::$subpool['strings'] ??= new class extends Helper\Format\Strings {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->format()->strings()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Helper\Format\Time
			 */
			public static function time() {
				return static::$subpool['time'] ??= new class extends Helper\Format\Time {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->format()->time()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Guidelines API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Helper\Guidelines
	 */
	public static function guidelines() {
		return static::$pool['guidelines'] ??= new class extends Helper\Guidelines {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->guidelines()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the HTTP Headers API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Helper\Headers
	 */
	public static function headers() {
		return static::$pool['headers'] ??= new class extends Helper\Headers {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->headers()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Image API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Image
	 */
	public static function image() {
		return static::$pool['image'] ??= new class extends Meta\Image {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->image()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Meta\Image\Utils
			 */
			public static function utils() {
				return static::$subpool['utils'] ??= new class extends Meta\Image\Utils {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->image()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Open_Graph API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Open_Graph
	 */
	public static function open_graph() {
		return static::$pool['open_graph'] ??= new class extends Meta\Open_Graph {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->open_graph()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Post_Type class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Helper\Post_Type
	 */
	public static function post_type() {
		return static::$pool['post_type'] ??= new class extends Helper\Post_Type {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->post_type()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Query class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Helper\Query
	 */
	public static function query() {
		return static::$pool['query'] ??= new class extends Helper\Query {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->query()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Helper\Query\Cache
			 */
			public static function cache() {
				return static::$subpool['cache'] ??= new class extends Helper\Query\Cache {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->query()->cache()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Helper\Query\Exclusion
			 */
			public static function exclusion() {
				return static::$subpool['exclusion'] ??= new class extends Helper\Query\Exclusion {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->query()->exclusion()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Helper\Query\Utils
			 */
			public static function utils() {
				return static::$subpool['utils'] ??= new class extends Helper\Query\Utils {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->query()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Robots API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Robots
	 */
	public static function robots() {
		return static::$pool['robots'] ??= new class extends Meta\Robots {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->robots()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Robots TXT API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\RobotsTXT\Main
	 */
	public static function robotstxt() {
		return static::$pool['robotstxt'] ??= new class extends RobotsTXT\Main {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->robotstxt()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\RobotsTXT\Utils
			 */
			public static function utils() {
				return static::$subpool['utils'] ??= new class extends RobotsTXT\Utils {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->robotstxt()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Sanitize API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Data\Filter\Sanitize
	 */
	public static function sanitize() {
		return static::$pool['sanitize'] ??= new class extends Data\Filter\Sanitize {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->filter()->sanitize()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Structured_Data API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Schema
	 */
	public static function schema() {
		return static::$pool['schema'] ??= new class extends Meta\Schema {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->schema()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @since 5.1.0 Now actually lists the existing class names.
			 * @readonly
			 * @var array[string,string] A list of accessible entity class names.
			 */
			public $entities = [
				'Author'         => Meta\Schema\Entities\Author::class,
				'BreadcrumbList' => Meta\Schema\Entities\BreadcrumbList::class,
				'Organization'   => Meta\Schema\Entities\Organization::class,
				'Person'         => Meta\Schema\Entities\Person::class,
				'Reference'      => Meta\Schema\Entities\Reference::class,
				'WebPage'        => Meta\Schema\Entities\WebPage::class,
				'WebSite'        => Meta\Schema\Entities\WebSite::class,
			];
		};
	}

	/**
	 * Returns a pool of Sitemap classes as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \Closure An anononymous class with subpools.
	 */
	public static function sitemap() {
		return static::$pool['sitemap'] ??= new class {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->sitemap()';
			private $deprecated_methods    = [
				'ping' => [
					'since'    => '5.0.5',
					'fallback' => '\The_SEO_Framework\Internal\Silencer::instance',
				],
			];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Sitemap\Cache
			 */
			public static function cache() {
				return static::$subpool['cache'] ??= new class extends Sitemap\Cache {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->sitemap()->cache()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.5
			 * @return \The_SEO_Framework\Sitemap\Cron
			 */
			public static function cron() {
				return static::$subpool['cron'] ??= new class extends Sitemap\Cron {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->sitemap()->cron()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Sitemap\Lock
			 */
			public static function lock() {
				return static::$subpool['lock'] ??= new class extends Sitemap\Lock {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->sitemap()->lock()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Sitemap\Registry
			 */
			public static function registry() {
				return static::$subpool['registry'] ??= new class extends Sitemap\Registry {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->sitemap()->registry()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Sitemap\Utils
			 */
			public static function utils() {
				return static::$subpool['utils'] ??= new class extends Sitemap\Utils {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->sitemap()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Taxonomy class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Helper\Taxonomy
	 */
	public static function taxonomy() {
		return static::$pool['taxonomy'] ??= new class extends Helper\Taxonomy {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->taxonomy()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Theme Color API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.1
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Theme_Color
	 */
	public static function theme_color() {
		return static::$pool['theme_color'] ??= new class extends Meta\Theme_Color {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->theme_color()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Title API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Title
	 */
	public static function title() {
		return static::$pool['title'] ??= new class extends Meta\Title {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->title()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Meta\Title\Conditions
			 */
			public static function conditions() {
				return static::$subpool['conditions'] ??= new class extends Meta\Title\Conditions {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->title()->conditions()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Meta\Title\Utils
			 */
			public static function utils() {
				return static::$subpool['utils'] ??= new class extends Meta\Title\Utils {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->title()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Twitter API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\Twitter
	 */
	public static function twitter() {
		return static::$pool['twitter'] ??= new class extends Meta\Twitter {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->twitter()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the URI API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \The_SEO_Framework\Meta\URI
	 */
	public static function uri() {
		return static::$pool['uri'] ??= new class extends Meta\URI {
			use Static_Deprecator;

			private $colloquial_handle     = 'tsf()->uri()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \The_SEO_Framework\Meta\URI\Utils
			 */
			public static function utils() {
				return static::$subpool['utils'] ??= new class extends Meta\URI\Utils {
					use Static_Deprecator;

					private $colloquial_handle     = 'tsf()->uri()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}
}
