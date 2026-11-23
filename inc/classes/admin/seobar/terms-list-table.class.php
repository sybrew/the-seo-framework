<?php
/**
 * @package The_SEO_Framework\Classes\Admin\SEOBar\Terms_List_Table
 * @subpackage The_SEO_Framework\SEOBar
 */

namespace The_SEO_Framework\Admin\SEOBar;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function The_SEO_Framework\flag;

use The_SEO_Framework\Helper\Taxonomy\Base_URL\Conflicts;

/**
 * The SEO Framework plugin
 * Copyright (C) 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Batches shortened URL conflict checks for the rows Core selects.
 *
 * @since 5.2.2
 * @access private
 */
final class Terms_List_Table extends \WP_Terms_List_Table {

	/**
	 * @since 5.2.2
	 * @var ?array The term and depth for each row while Core selects the rows.
	 */
	private $rows;

	/**
	 * Checks the visible terms before rendering their SEO Bars.
	 *
	 * Let Core select the rows and any repeated ancestors. Its hierarchy walk
	 * emits indentation, and single_row() handles the row markup. Clear the shared
	 * conflict map after the last row is rendered.
	 *
	 * @since 5.2.2
	 */
	public function display_rows_or_placeholder() {

		if ( ! isset( $this->get_column_info()[0][ ListTable::COLUMN_NAME ] ) ) {
			parent::display_rows_or_placeholder();

			return;
		}

		$this->rows = [];
		parent::display_rows_or_placeholder();

		$rows       = $this->rows;
		$this->rows = null;

		if ( ! $rows ) return;

		flag(
			'term_list_url_conflicts',
			// Each row holds the term at index 0 and its display depth at index 1.
			Conflicts::get_term_url_conflicts( array_column( $rows, 0 ) ),
		);

		foreach ( $rows as [ $term, $level ] )
			parent::single_row( $term, $level );

		flag( 'term_list_url_conflicts', [] );
	}

	/**
	 * Collects or renders a row according to the current stage of display.
	 *
	 * @since 5.2.2
	 *
	 * @param \WP_Term $tag   The term for this row.
	 * @param int      $level The term's level in the hierarchy.
	 */
	public function single_row( $tag, $level = 0 ) {

		if ( null === $this->rows ) {
			parent::single_row( $tag, $level );
		} else {
			$this->rows[] = [ $tag, $level ];
		}
	}
}
