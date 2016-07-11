<?php
$title_separator = $this->get_separator_list();
$recommended = ' class="recommended" title="' . esc_attr__( 'Recommended', 'autodescription' ) . '"';

?>
<fieldset>
	<legend><h4><?php _e( 'Document Title Separator', 'autodescription' ); ?></h4></legend>
	<p id="title-separator" class="theseoframework-fields">
	<?php foreach ( $title_separator as $name => $html ) { ?>
		<input type="radio" name="<?php $this->field_name( 'title_seperator' ); ?>" id="<?php $this->field_id( 'title_seperator_' . $name ); ?>" value="<?php echo $name ?>" <?php checked( $this->get_field_value( 'title_seperator' ), $name ); ?> />
		<label for="<?php $this->field_id( 'title_seperator_' . $name ); ?>" <?php echo ( $name === 'pipe' || $name === 'dash' ) ? $recommended : ''; ?>><?php echo $html ?></label>
	<?php } ?>
	</p>
	<?php $this->description( __( 'If the title consists of two parts (original title and optional addition), then the separator will go in-between them.', 'autodescription' ) ); ?>
</fieldset>
<?php
