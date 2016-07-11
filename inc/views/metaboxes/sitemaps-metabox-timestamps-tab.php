<?php
//* Sets timezone according to WordPress settings.
$this->set_timezone();

$timestamp_0 = date( 'Y-m-d' );

/**
 * @link https://www.w3.org/TR/NOTE-datetime
 * We use the second expression of the time zone offset handling.
 */
$timestamp_1 = date( 'Y-m-d\TH:iP' );

//* Reset timezone to previous value.
$this->reset_timezone();

?><h4><?php _e( 'Timestamps Settings', 'autodescription' ); ?></h4><?php
$this->description( __( 'The modified time suggests to Search Engines where to look for content changes. It has no impact on the SEO value unless you drastically change pages or posts. It then depends on how well your content is constructed.', 'autodescription'  ) );
$this->description( __( "By default, the sitemap only outputs the modified date if you've enabled them within the Social Metabox. This setting overrides those settings for the Sitemap.", 'autodescription' ) );

?>
<hr>

<h4><?php _e( 'Output Modified Date', 'autodescription' ); ?></h4>
<?php

//* Echo checkbox.
$this->wrap_fields(
	$this->make_checkbox(
		'sitemaps_modified',
		sprintf( __( 'Add %s to the sitemap?', 'autodescription' ), $this->code_wrap( '<lastmod>' ) ),
		''
	), true
);

?>
<hr>

<fieldset>
	<legend><h4><?php _e( 'Timestamp Format Settings', 'autodescription' ); ?></h4></legend>
	<?php $this->description( __( 'Determines how specific the modification timestamp is.', 'autodescription' ) ); ?>

	<p id="sitemaps-timestamp-format" class="theseoframework-fields">
		<span class="toblock">
			<input type="radio" name="<?php $this->field_name( 'sitemap_timestamps' ); ?>" id="<?php $this->field_id( 'sitemap_timestamps_0' ); ?>" value="0" <?php checked( $this->get_field_value( 'sitemap_timestamps' ), '0' ); ?> />
			<label for="<?php $this->field_id( 'sitemap_timestamps_0' ); ?>">
				<span title="<?php _e( 'Complete date', 'autodescription' ); ?>"><?php echo $this->code_wrap( $timestamp_0 ) ?> [?]</span>
			</label>
		</span>
		<span class="toblock">
			<input type="radio" name="<?php $this->field_name( 'sitemap_timestamps' ); ?>" id="<?php $this->field_id( 'sitemap_timestamps_1' ); ?>" value="1" <?php checked( $this->get_field_value( 'sitemap_timestamps' ), '1' ); ?> />
			<label for="<?php $this->field_id( 'sitemap_timestamps_1' ); ?>">
				<span title="<?php _e( 'Complete date plus hours, minutes and timezone', 'autodescription' ); ?>"><?php echo $this->code_wrap( $timestamp_1 ); ?> [?]</span>
			</label>
		</span>
	</p>
</fieldset>
<?php
