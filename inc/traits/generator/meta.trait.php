<?php
/**
 * @package The_SEO_Framework\Traits
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

trait Meta_Generator {

	private $data     = [];
	private $metatags = [];
	private $failure  = '';

	/** @var bool $can_fail Set to true to halt all class output on failure. */
	protected $can_fail = false;

	protected $tsf;
	protected $id;
	protected $taxonomy;

	abstract public function build();

	final public function __get( $key ) {
		return $this->$key;
	}

	final public function __toString() {
		$string = implode( PHP_EOL, $this->_normalize_tags() );
		return $string ? $string . PHP_EOL : '';
	}

	/**
	 * Initializes class vars.
	 *
	 * This is needed because we can't generate a predefined __construct() via a
	 * trait prior PHP 5.5.21 or 5.6.5
	 */
	final protected function init() {
		$this->tsf      = \the_seo_framework();
		$this->id       = $this->tsf->get_the_real_ID() ?: 0;
		$this->taxonomy = $this->tsf->get_current_taxonomy() ?: '';
	}


	final public function &_collect_data() {
		return $this->data;
	}

	final public function create_metatags() {
		$tags = &$this->_collect_tags();
		foreach ( $this->_read_data() as $type => $data ) {
			$tags[ $type ] = $this->_create_metatags_deep( $data );
		}
	}

	final private function _create_metatags_deep( array $data ) {

		$tags = [];

		if ( isset( $data['@complex'] ) ) {
			unset( $data['@complex'] );
			foreach ( $data as $it ) {
				if ( isset( $it['@complex'] ) ) {
					$tags[] = $this->_create_metatags_deep( $it );
				} else {
					$tags[] = $this->make_tag( $it );
				}
			}
		} else {
			$tags = $this->make_tag( $data );
		}

		return $tags;
	}

	final public function _read_data() {
		return $this->data;
	}

	final protected function set_failure( $reason ) {
		$this->failure = $reason;
	}

	final protected function parse_simple( array $meta ) {

		$_data = &$this->_collect_data();

		foreach ( $meta as $key => $cb ) {
			if ( $val = $this->{$cb}() ) {
				$_data[ $key ] = $val;
			} elseif ( $this->can_fail ) {
				return false;
			}
		}
		return true;
	}

	final protected function parse_complex( array $meta ) {

		$_data = &$this->_collect_data();

		foreach ( $meta as $key => $cb ) {
			if ( $_val = $this->{$cb}() ) {
				if ( $_val = $this->_parse_complex_deep( '@', $_val )['@'] ) {
					$_data[ $key ] = $_val;
				} elseif ( $this->can_fail ) {
					return false;
				}
			} elseif ( $this->can_fail ) {
				return false;
			}
		}
		return true;
	}

	final protected function _parse_complex_deep( $key, array $val ) {

		// Test if deeper: Associative.
		if ( array_values( $val ) === $val ) {
			$data = [];
			foreach ( $val as $sub => $val ) {
				$data[ $key ][ $sub ] = $this->_parse_complex_deep( '@', $val )['@'];
				$data[ $key ][ $sub ]['@complex'] = true;
			}
			$data[ $key ]['@complex'] = true;
			return $data;
		}

		foreach ( $val as $k => $v ) {
			if ( $v ) {
				$data[ $key ][ $k ] = $v;
			} elseif ( $this->can_fail ) {
				return false;
			}
		}
		$data[ $key ]['@complex'] = true;
		return $data;
	}

	final private function &_collect_tags() {
		return $this->metatags;
	}

	/* Generator syntax please... I want PHP 5.5+ :( */
	final private function _normalize_tags() {

		$tags = [];

		foreach ( $this->metatags as $tag ) {
			if ( is_array( $tag ) ) {
				foreach ( array_reduce( $tag, 'array_merge', [] ) as $_tag ) {
					$tags[] = $_tag;
				}
			} else {
				$tags[] = $tag;
			}
		}

		return $tags;
	}

	final private function make_tag( array $data ) {

		$tag = $data['@tag'];
		unset( $data['@tag'] );

		$content = '';
		foreach ( $data as $a => $b )
			$content .= sprintf( '%s="%s" ', $a, \esc_attr( $b ) );

		return sprintf( '<%s %s />', $tag, trim( $content ) );
	}
}
