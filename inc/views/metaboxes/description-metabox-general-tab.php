<?php
//* Let's use the same separators as for the title.
$description_separator = $this->get_separator_list();
$sep_option = $this->get_option( 'description_separator' );
$sep_option = $sep_option ? $sep_option : 'pipe';

$recommended = ' class="recommended" title="' . __( 'Recommended', 'autodescription' ) . '"';

?>
<fieldset>
	<legend><h4><?php _e( 'Description Excerpt Separator', 'autodescription' ); ?></h4></legend>
	<p id="description-separator" class="theseoframework-fields">
	<?php foreach ( $description_separator as $name => $html ) { ?>
		<input type="radio" name="<?php $this->field_name( 'description_separator' ); ?>" id="<?php $this->field_id( 'description_separator' . $name ); ?>" value="<?php echo $name ?>" <?php checked( $sep_option, $name ); ?> />
		<label for="<?php $this->field_id( 'description_separator' . $name ); ?>" <?php echo ( 'pipe' === $name || 'dash' === $name ) ? $recommended : ''; ?>><?php echo $html ?></label>
	<?php } ?>
	</p>
	<span class="description"><?php _e( 'If the Automated Description consists of two parts (title and excerpt), then the separator will go in-between them.', 'autodescription' ); ?></span>
</fieldset>
<?php
