<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Interface The_SEO_Framework\Interpreter_Form_Interface
 *
 * Sets public debug functions.
 *
 * @since 3.3.0
 * @see \The_SEO_Framework\Interpreters\Form
 */
interface Interpreter_Form_Interface {

	/**
	 * Returns the GET/POST/REQUEST (i.e. POST) index.
	 *
	 * This index corresponds to the POST value sent to the server when the form
	 * is submitted.
	 *
	 * @return string The options index.
	 */
	public function get_request_index();

}
