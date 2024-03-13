<?php
/**
 * @package The_SEO_Framework\Views\Debug
 * @subpackage The_SEO_Framework\Debug
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// Start timer.
$t = hrtime( true );

// I hate ob_* for this stuff.
ob_start();
Front\Meta\Head::print_wrap_and_tags();
$output  = ob_get_clean();
$gentime = number_format( ( hrtime( true ) - $t ) / 1e9, 5 );

// Escape it, replace EOL with breaks, and style everything between quotes (which are ending with space).
$output = strtr(
	str_replace( str_repeat( ' ', 4 ), str_repeat( '&nbsp;', 4 ), \esc_html( $output ) ),
	array_fill_keys( [ "\r\n", "\r", "\n" ], "<br>\n" ),
);
$output = preg_replace( '/(&quot;.*?&quot;)(&nbps;|[\s:])/', '<span style=color:#8bc34a>$1$2</span> ', $output );

$title = \is_admin() ? 'Expected SEO Output' : 'Determined SEO Output';

// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- All output is escaped above.
echo <<<HTML
<div style="font-family:unset;display:block;width:100%;background:#23282D;color:#ddd;border-bottom:1px solid #ccc">
	<div style="display:inline-block;width:100%;padding:20px;margin:0 auto;border-bottom:1px solid #ccc;">
		<h2 style="font-family:unset;color:#ddd;font-size:22px;padding:0;margin:0">$title</h2>
	</div>
	<div style="font-family:unset;display:inline-block;width:100%;padding:20px;border-bottom:1px solid #ccc">Generated in $gentime seconds</div>
	<div style="display:inline-block;width:100%;padding:20px;font-family:Consolas,Monaco,monospace;font-size:14px;">$output</div>
</div>
HTML;
