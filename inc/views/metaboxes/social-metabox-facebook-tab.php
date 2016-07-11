<?php
$fb_author = $this->get_field_value( 'facebook_author' );
$fb_author_placeholder = empty( $fb_publisher ) ? _x( 'http://www.facebook.com/YourPersonalProfile', 'Example Facebook Personal URL', 'autodescription' ) : '';

$fb_publisher = $this->get_field_value( 'facebook_publisher' );
$fb_publisher_placeholder = empty( $fb_publisher ) ? _x( 'http://www.facebook.com/YourVerifiedBusinessProfile', 'Example Verified Facebook Business URL', 'autodescription' ) : '';

$fb_appid = $this->get_field_value( 'facebook_appid' );
$fb_appid_placeholder = empty( $fb_appid ) ? '123456789012345' : '';

?><h4><?php _e( 'Default Facebook Integration Settings', 'autodescription' ); ?></h4><?php
$this->description( __( 'Facebook post sharing works mostly through Open Graph. However, you can also link your Business and Personal Facebook pages, among various other options.', 'autodescription' ) );
$this->description( __( 'When these options are filled in, Facebook might link your Facebook profile to be followed and liked when your post or page is shared.', 'autodescription' ) );

?>
<hr>

<p>
	<label for="<?php $this->field_id( 'facebook_author' ); ?>">
		<strong><?php _e( 'Article Author Facebook URL', 'autodescription' ); ?></strong>
		<a href="<?php echo esc_url( 'https://facebook.com/me' ); ?>" class="description" target="_blank" title="<?php _e( 'Your Facebook Profile', 'autodescription' ); ?>">[?]</a>
	</label>
</p>
<p>
	<input type="text" name="<?php $this->field_name( 'facebook_author' ); ?>" class="large-text" id="<?php $this->field_id( 'facebook_author' ); ?>" placeholder="<?php echo $fb_author_placeholder ?>" value="<?php echo esc_attr( $fb_author ); ?>" />
</p>

<p>
	<label for="<?php $this->field_id( 'facebook_publisher' ); ?>">
		<strong><?php _e( 'Article Publisher Facebook URL', 'autodescription' ); ?></strong>
		<a href="<?php echo esc_url( 'https://instantarticles.fb.com/' ); ?>" class="description" target="_blank" title="<?php _e( 'To use this, you need to be a verified business', 'autodescription' ); ?>">[?]</a>
	</label>
</p>
<p>
	<input type="text" name="<?php $this->field_name( 'facebook_publisher' ); ?>" class="large-text" id="<?php $this->field_id( 'facebook_publisher' ); ?>" placeholder="<?php echo $fb_publisher_placeholder ?>" value="<?php echo esc_attr( $fb_publisher ); ?>" />
</p>

<p>
	<label for="<?php $this->field_id( 'facebook_appid' ); ?>">
		<strong><?php _e( 'Facebook App ID', 'autodescription' ); ?></strong>
		<a href="<?php echo esc_url( 'https://developers.facebook.com/apps' ); ?>" target="_blank" class="description" title="<?php _e( 'Get Facebook App ID', 'autodescription' ); ?>">[?]</a>
	</label>
</p>
<p>
	<input type="text" name="<?php $this->field_name( 'facebook_appid' ); ?>" class="large-text" id="<?php $this->field_id( 'facebook_appid' ); ?>" placeholder="<?php echo $fb_appid_placeholder ?>" value="<?php echo esc_attr( $fb_appid ); ?>" />
</p>
<?php
