<?php
/**
 * @package The_SEO_Framework\Traits\Overload
 */

namespace The_SEO_Framework\Traits;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Legend/Definitions:
 *
 * - Facade Legend/Definitions:
 *      Choose one per trait.
 *
 *      - Core: Final instance.
 *         - No parents.
 *         - Maybe children.
 *         - All methods are protected.
 *
 *      - Master: First instance of facade. Calls all the shots.
 *         - Expects parent class.
 *         - Has no child class.
 *         - All methods are public.
 *            - Except for subconstructor.
 *         - Expects class to be labelled "final".
 *
 *      - Sub: Sub instance.
 *         - Expects child class.
 *         - Expects parent class.
 *
 *      - Child: Child instance.
 *         - Synonymous to "static".
 *         - Expects parent class.
 *         - Could have child class.
 *         - Prevents object calling.
 *
 *      - Stray: Expects nothing.
 *         - Maybe child.
 *         - Maybe parent.
 *
 * - Visibility Legend/Definitions:
 *      These can be combined.
 *
 *      - Final: Final instance.
 *         - Expects children classes not to contain same methods.
 *         - All methods are labelled "final".
 *         - Expects class to be labelled "final".
 *
 *      - Solo: Single object.
 *         - Expects no parents.
 *         - Expects no children.
 *         - All methods are labelled "final".
 *         - Expects class to be labelled "final".
 *         - Prevents facade pattern.
 *         - All methods could be public.
 *
 *      - Static: Expects class not to be initiated.
 *         - Synonymous to "child".
 *         - Prevents object calling.
 *         - All public methods are static.
 *
 *      - Once: Expects class to be called at most once.
 *         - Caches method calls.
 *         - Exits PHP on second call.
 *
 *      - Interface: Contains abstract methods.
 *
 *      - Private: All methods are private.
 *
 *      - <No keyword>: Expects nothing.
 *         - All methods are "protected".
 *
 *      - Public: All methods are public.
 *
 * - Type Legend/Definitions:
 *      Choose one per trait.
 *
 *      - Enclose: Prevents common hacking methods through magic method nullification.
 *
 *      - Construct: Holds constructor.
 *         - When interface: Holds subsconstructor.
 *            - Make sure the subconstructor is private. Otherwise late static binding will kick in.
 *
 *      - Destruct: Holds destructor and keeps track of destruct calling.
 *
 *      - Ignore_Properties_Core_Public_Final: Ignores invalid property calling. Prevents PHP warning messages.
 *
 *      - <No keyword>: Should not exist.
 */

// phpcs:disable, Squiz.Commenting.FunctionComment.Missing -- The trait doc explains it.
// phpcs:disable, Generic.Files.OneObjectStructurePerFile.MultipleFound -- This is a collective, preloaded file for all overloading.

/**
 * Holds private overloading functions to prevent injection or abstraction.
 *
 * @since 4.0.0
 * @access private
 */
trait Enclose_Stray_Private {

	private function __clone() { }

	private function __wakeup() { }
}

/**
 * Forces all classes and subclasses to prevent injection or abstraction.
 *
 * @since 4.0.0
 * @access private
 */
trait Enclose_Core_Final {

	final protected function __clone() { }

	final protected function __wakeup() { }
}
