<?php
$blogname = $this->get_blogname();

?>
<h4><?php _e( 'About this website', 'autodescription' ); ?></h4>
<p><span class="description"><?php printf( __( 'Who or what is your website about?', 'autodescription' ) ); ?></span></p>

<hr>

<p>
	<label for="<?php $this->field_id( 'knowledge_type' ); ?>"><?php _ex( 'This website represents:', '...Organization or Person.', 'autodescription' ); ?></label>
	<select name="<?php $this->field_name( 'knowledge_type' ); ?>" id="<?php $this->field_id( 'knowledge_type' ); ?>">
	<?php
	$knowledge_type = (array) apply_filters(
		'the_seo_framework_knowledge_types',
		array(
			'organization'	=> __( 'An Organization', 'autodescription' ),
			'person' 		=> __( 'A Person', 'autodescription' ),
		)
	);
	foreach ( $knowledge_type as $value => $name )
		echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->get_field_value( 'knowledge_type' ), esc_attr( $value ), false ) . '>' . esc_html( $name ) . '</option>' . "\n";
	?>
	</select>
</p>

<hr>

<p>
	<label for="<?php $this->field_id( 'knowledge_name' ); ?>">
		<strong><?php _e( "The organization or personal name", 'autodescription' ); ?></strong>
	</label>
</p>
<p>
	<input type="text" name="<?php $this->field_name( 'knowledge_name' ); ?>" class="large-text" id="<?php $this->field_id( 'knowledge_name' ); ?>" placeholder="<?php echo esc_attr( $blogname ) ?>" value="<?php echo esc_attr( $this->get_field_value( 'knowledge_name' ) ); ?>" />
</p>
<?php
