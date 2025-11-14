<?php
/**
 * @package The_SEO_Framework\Templates\Inpost
 * @subpackage The_SEO_Framework\Admin\Edit\Inpost
 */

namespace The_SEO_Framework;

( \defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

?>
<script type=text/html id=tmpl-tsf-primary-term-selector-le>
	<div class="tsf-primary-term-selector-wrap">
		<label for="{{data.selectId}}">{{data.i18n.selectPrimary}}</label>
		<select id="{{data.selectId}}" name="{{data.selectName}}"></select>
	</div>
</script>
<script type=text/html id=tmpl-tsf-primary-term-selector-le-bulk>
	<div class="tsf-primary-term-selector-wrap">
		<label for="{{data.selectId}}">{{data.i18n.selectPrimary}}</label>
		<select id="{{data.selectId}}" name="{{data.selectName}}">
			<option value="nochange">— No Change —</option>
			<option value="0">None (Clear primary {{data.taxonomyName}})</option>
		</select>
	</div>
</script>
