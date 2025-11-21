<?php
/**
 * @package The_SEO_Framework\Templates\List
 * @subpackage The_SEO_Framework\Admin\Edit\List
 */

namespace The_SEO_Framework;

( \defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

?>
<script type=text/html id=tmpl-tsf-primary-term-selector-quick>
	<div id="{{data.wrapId}}" class=tsf-primary-term-selector-wrap>
		<label for="{{data.selectId}}">{{data.i18n.selectPrimary}}</label>
		<select id="{{data.selectId}}" name="{{data.selectName}}"></select>
	</div>
</script>
<script type=text/html id=tmpl-tsf-primary-term-selector-bulk>
	<div id="{{data.wrapId}}" class=tsf-primary-term-selector-wrap>
		<label for="{{data.selectId}}">{{data.i18n.selectPrimary}}</label>
		<select id="{{data.selectId}}" name="{{data.selectName}}">
			<option value="nochange">— No Change —</option>
		</select>
	</div>
</script>
