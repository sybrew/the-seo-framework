<?php

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

foreach ( $tabs as $id => $tab ) {
	printf(
		'<script id="tsf-gutenberg-tmpl-%s" type="text/html">',
		\esc_attr( $id )
	);
	$this->gutenberg_flex_tab_wrapper( 'inpost', [ $id => $tab ] );
	echo '</script>';
}
?>
<script id=tsf-gutenberg-data>
/* <![CDATA[ */
		<?php
		$_data = [];
		foreach ( $tabs as $id => $tab ) {
			$_data[] = [
				'data' => [
					'components' => [
						'className'   => 'tsf-gutenberg-row-' . \esc_attr( $id ),
						'initialOpen' => 'general' === $id,
						'title'       => \esc_html( $tab['name'] ),
					],
					'tmpl' => 'tsf-gutenberg-tmpl-' . \esc_attr( $id ),
				],
			];
		}

		foreach ( (array) $_data as $key => &$value ) {
			if ( ! is_scalar( $value ) )
				continue;
			$value = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
		}

		echo 'var TSFPanels = ' . \wp_json_encode( $_data ) . ';' . PHP_EOL;
		?>
/* ]]> */
</script>
<?php
