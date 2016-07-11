<?php
/**
 * Generate example for Title Additions Location.
 */
$title_args = $this->generate_home_title();
$title = $title_args['title'];
$blogname = $title_args['blogname'];
$sep = $this->get_separator( 'title', true );

$example_left = '<em><span class="custom-title-js">' . esc_attr( $title ) . '</span><span class="custom-blogname-js"><span class="autodescription-sep-js"> ' . esc_attr( $sep ) . ' </span><span class="custom-tagline-js">' . esc_attr( $blogname ) . '</span></span></span></em>';
$example_right = '<em><span class="custom-blogname-js"><span class="custom-tagline-js">' . esc_attr( $blogname ) . '</span><span class="autodescription-sep-js"> ' . esc_attr( $sep ) . ' </span></span><span class="custom-title-js">' . esc_attr( $title ) . '</span></em>';

$home_page_i18n = __( 'Home Page', 'autodescription' );

?>
<fieldset>
	<legend><h4><?php _e( 'Document Title Additions Location', 'autodescription' ); ?></h4></legend>
	<?php $this->description( __( 'Determines which side the added title text will go on.', 'autodescription' ) ); ?>

	<p id="home-title-location" class="theseoframework-fields">
		<span class="toblock">
			<input type="radio" name="<?php $this->field_name( 'home_title_location' ); ?>" id="<?php $this->field_id( 'home_title_location_left' ); ?>" value="left" <?php checked( $this->get_field_value( 'home_title_location' ), 'left' ); ?> />
			<label for="<?php $this->field_id( 'home_title_location_left' ); ?>">
				<span><?php _e( 'Left:', 'autodescription' ); ?></span>
				<?php echo ( $example_left ) ? $this->code_wrap_noesc( $example_left ) : ''; ?>
			</label>
		</span>
		<span class="toblock">
			<input type="radio" name="<?php $this->field_name( 'home_title_location' ); ?>" id="<?php $this->field_id( 'home_title_location_right' ); ?>" value="right" <?php checked( $this->get_field_value( 'home_title_location' ), 'right' ); ?> />
			<label for="<?php $this->field_id( 'home_title_location_right' ); ?>">
				<span><?php _e( 'Right:', 'autodescription' ); ?></span>
				<?php echo ( $example_right ) ? $this->code_wrap_noesc( $example_right ) : ''; ?>
			</label>
		</span>
	</p>
</fieldset>

<hr>
<?php
/**
 * @TODO work on this checkbox.
 * @priority low 2.6.x
 */
?>
<h4><?php printf( __( '%s Tagline', 'autodescription' ), $home_page_i18n ); ?></h4>
<p id="title-tagline-toggle">
	<label for="<?php $this->field_id( 'homepage_tagline' ); ?>" class="toblock">
		<input type="checkbox" name="<?php $this->field_name( 'homepage_tagline' ); ?>" id="<?php $this->field_id( 'homepage_tagline' ); ?>" <?php $this->is_conditional_checked( 'homepage_tagline' ); ?> value="1" <?php checked( $this->get_field_value( 'homepage_tagline' ) ); ?> />
		<?php printf( __( 'Add site description (tagline) to the Title on the %s?', 'autodescription' ), $home_page_i18n ); ?>
	</label>
</p>
<?php
