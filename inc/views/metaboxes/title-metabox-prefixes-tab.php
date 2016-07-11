<?php
//* Get translated category label, if it exists. Otherwise, fallback to translation.
$term_labels = $this->get_tax_labels( 'category' );
$label = isset( $term_labels->singular_name ) ? $term_labels->singular_name : __( 'Category', 'autodescription' );

$cats = get_terms( array( 'taxonomy' => 'category', 'fields' => 'ids', 'hide_empty' => false, 'order' => 'ASC', 'number' => 1 ) );
if ( is_array( $cats ) && ! empty( $cats ) ) {
	//* Category should exist.
	$cat = reset( $cats );
} else {
	//* Default fallback category.
	$cat = 1;
}
//* If cat is found, it will return its name. Otherwise it's an empty string.
$cat_name = get_cat_name( $cat );
$cat_name = $cat_name ? $cat_name : __( 'Example Category', 'autodescription' );

$display_prefix = $this->is_option_checked( 'title_rem_prefixes' ) ? 'none' : 'inline';
$title = '<span class="title-prefix-example" style="display:' . $display_prefix . '">' . $label . ': </span>' . $cat_name;

$left_additions = $additions['left'];
$right_additions = $additions['right'];

$example_left = '<em>' . $left_additions . $title . '</em>';
$example_right = '<em>' . $title . $right_additions . '</em>';

$language = $this->google_language();

/**
 * @todo use checkbox function
 * @priority low 2.6.x
 */

?>
<h4><?php _e( 'Title prefix options', 'autodescription' ); ?></h4>
<p><span class="description"><?php _e( "On archives a descriptive prefix may be added to the title.", 'autodescription' ); ?></span></p>

<h4><?php _e( 'Example Automated Archive Title Output', 'autodescription' ); ?></h4>
<p>
	<span class="title-additions-example-left" style="display:<?php echo $showleft ? 'inline' : 'none'; ?>"><?php echo $this->code_wrap_noesc( $example_left ); ?></span>
	<span class="title-additions-example-right" style="display:<?php echo $showleft ? 'none' : 'inline'; ?>"><?php echo $this->code_wrap_noesc( $example_right ); ?></span>
</p>

<hr>

<h4><?php _e( 'Remove Archive Title Prefixes', 'autodescription' ); ?></h4>
<p id="title-prefixes-toggle">
	<label for="<?php $this->field_id( 'title_rem_prefixes' ); ?>">
		<input type="checkbox" name="<?php $this->field_name( 'title_rem_prefixes' ); ?>" id="<?php $this->field_id( 'title_rem_prefixes' ); ?>" <?php $this->is_conditional_checked( 'title_rem_prefixes' ); ?> value="1" <?php checked( $this->get_field_value( 'title_rem_prefixes' ) ); ?> />
		<?php _e( 'Remove Prefixes from title?', 'autodescription' ); ?>
	</label>
	<?php
	$this->make_info(
		__( "The prefix helps visitors and Search Engines determine what kind of page they're visiting", 'autodescription' ),
		'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#3',
		true
	);
	?>
</p>
<?php
