<?php
$ro_value = $robots['value'];
$ro_name = $robots['name'];
$ro_i18n = $robots['desc'];

?>
<h4><?php printf( __( '%s Robots Settings', 'autodescription' ), $ro_name ); ?></h4>
<?php $this->description( $ro_i18n ); ?>
<p class="theseoframework-fields">
	<?php

	$checkboxes = '';

	foreach ( $types as $type => $i18n ) {

		if ( 'site' === $type || 'attachment' === $type || 'search' === $type ) {
			//* Singular.
			/* translators: 1: Option, 2: Post Type */
			$label = sprintf( __( 'Apply %1$s to %2$s?', 'autodescription' ), $this->code_wrap( $ro_name ), $i18n );
		} else {
			//* Archive.
			/* translators: 1: Option, 2: Post Type */
			$label = sprintf( __( 'Apply %1$s to %2$s Archives?', 'autodescription' ), $this->code_wrap( $ro_name ), $i18n );
		}

		$id = $type . '_' . $ro_value;

		//* Add <hr> if it's 'site'
		$checkboxes .= ( 'site' === $type ) ? '<hr class="theseoframework-option-spacer">' : '';

		$checkboxes .= $this->make_checkbox( $id, $label, '' );
	}

	//* Echo checkboxes.
	$this->wrap_fields( $checkboxes, true );
	?>
</p>
<?php
