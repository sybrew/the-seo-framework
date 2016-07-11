<?php
$language = $this->google_language();

$example_left = $examples['left'];
$example_right = $examples['right'];

$home_page_has_option = __( 'The Home Page has a specific option.', 'autodescription' );

?>
<fieldset>
	<legend><h4><?php _e( 'Document Title Additions Location', 'autodescription' ); ?></h4></legend>

	<?php $this->description( __( 'Determines which side the added title text will go on.', 'autodescription' ) ); ?>

	<p id="title-location" class="theseoframework-fields">
		<span class="toblock">
			<input type="radio" name="<?php $this->field_name( 'title_location' ); ?>" id="<?php $this->field_id( 'title_location_left' ); ?>" value="left" <?php checked( $this->get_field_value( 'title_location' ), 'left' ); ?> />
			<label for="<?php $this->field_id( 'title_location_left' ); ?>">
				<span><?php _e( 'Left:', 'autodescription' ); ?></span>
				<?php echo $this->code_wrap_noesc( $example_left ) ?>
			</label>
		</span>
		<span class="toblock">
			<input type="radio" name="<?php $this->field_name( 'title_location' ); ?>" id="<?php $this->field_id( 'title_location_right' ); ?>" value="right" <?php checked( $this->get_field_value( 'title_location' ), 'right' ); ?> />
			<label for="<?php $this->field_id( 'title_location_right' ); ?>">
				<span><?php _e( 'Right:', 'autodescription' ); ?></span>
				<?php echo $this->code_wrap_noesc( $example_right ); ?>
			</label>
		</span>
	</p>
	<?php $this->description( $home_page_has_option ); ?>
</fieldset>
<?php

//* Only add this option if the theme is doing it right.
if ( $this->can_manipulate_title() ) : ?>
	<hr>

	<h4><?php _e( 'Remove Blogname from Title', 'autodescription' ); ?></h4>
	<div id="title-additions-toggle">
		<?php
		$info = $this->make_info(
			__( 'This might decouple your posts and pages from the rest of the website.', 'autodescription' ),
			'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#3',
			false
		);

		$this->wrap_fields(
			$this->make_checkbox(
				'title_rem_additions',
				__( 'Remove Blogname from title?', 'autodescription' ) . ' ' . $info,
				''
			),
			true
		);
		?>
	</div>
	<?php $this->description( __( 'Only use this option if you are aware of its SEO effects.', 'autodescription' ), false ); ?>
	<?php $this->description( $home_page_has_option, false ); ?>
<?php endif;
