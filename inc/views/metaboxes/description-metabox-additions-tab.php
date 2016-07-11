<?php
$language = $this->google_language();
$google_explanation = esc_url( 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#1' );

?>
<h4><?php _e( 'Description Additions Settings', 'autodescription' ); ?></h4>
<?php
$this->description( __( "To create a more organic description, a small introduction can be added before the description.", 'autodescription' ) );
$this->description( __( "The introduction consists of the title and optionally the blogname.", 'autodescription' ) );
?>

<hr>

<h4><?php _e( 'Add descriptive Additions to Description', 'autodescription' ); ?></h4>
<p id="description-additions-toggle">
	<label for="<?php $this->field_id( 'description_additions' ); ?>" class="toblock">
		<input type="checkbox" name="<?php $this->field_name( 'description_additions' ); ?>" id="<?php $this->field_id( 'description_additions' ); ?>" <?php $this->is_conditional_checked( 'description_additions' ); ?> value="1" <?php checked( $this->get_field_value( 'description_additions' ) ); ?> />
		<?php _e( 'Add Additions to automated description?', 'autodescription' ); ?>
		<a href="<?php echo esc_url( $google_explanation ); ?>" target="_blank" class="description" title="<?php _e( 'This creates good meta descriptions', 'autodescription' ); ?>">[?]</a>
	</label>
</p>

<h4><?php _e( 'Add Blogname to Additions', 'autodescription' ); ?></h4>
<p id="description-onblogname-toggle">
	<label for="<?php $this->field_id( 'description_blogname' ); ?>" class="toblock">
		<input type="checkbox" name="<?php $this->field_name( 'description_blogname' ); ?>" id="<?php $this->field_id( 'description_blogname' ); ?>" <?php $this->is_conditional_checked( 'description_blogname' ); ?> value="1" <?php checked( $this->get_field_value( 'description_blogname' ) ); ?> />
		<?php _e( 'Add Blogname to automated description additions?', 'autodescription' ); ?>
	</label>
</p>
<?php
