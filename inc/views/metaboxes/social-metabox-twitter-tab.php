<?php
$tw_site = $this->get_field_value( 'twitter_site' );
$tw_site_placeholder = empty( $tw_site ) ? _x( '@your-site-username', 'Twitter @username', 'autodescription' ) : '';

$tw_creator = $this->get_field_value( 'twitter_creator' );
$tw_creator_placeholder = empty( $tw_creator ) ? _x( '@your-personal-username', 'Twitter @username', 'autodescription' ) : '';

$twitter_card = $this->get_twitter_card_types();

?><h4><?php _e( 'Default Twitter Integration Settings', 'autodescription' ); ?></h4><?php
$this->description( __( 'Twitter post sharing works mostly through Open Graph. However, you can also link your Business and Personal Twitter pages, among various other options.', 'autodescription' ) );

?>
<hr>

<fieldset id="twitter-cards">
	<legend><h4><?php _e( 'Twitter Card Type', 'autodescription' ); ?></h4></legend>
	<?php $this->description_noesc( printf( __( 'What kind of Twitter card would you like to use? It will default to %s if no image is found.', 'autodescription' ), $this->code_wrap( 'Summary' ) ) ); ?>

	<p class="theseoframework-fields">
	<?php
		foreach ( $twitter_card as $type => $name ) {
			?>
				<span class="toblock">
					<input type="radio" name="<?php $this->field_name( 'twitter_card' ); ?>" id="<?php $this->field_id( 'twitter_card_' . $type ); ?>" value="<?php echo $type ?>" <?php checked( $this->get_field_value( 'twitter_card' ), $type ); ?> />
					<label for="<?php $this->field_id( 'twitter_card_' . $type ); ?>">
						<span><?php echo $this->code_wrap( ucfirst( $name ) ); ?></span>
						<a class="description" href="<?php echo esc_url('https://dev.twitter.com/cards/types/' . $name ); ?>" target="_blank" title="Twitter Card <?php echo ucfirst( $name ) . ' ' . __( 'Example', 'autodescription' ); ?>"><?php _e( 'Example', 'autodescription' ); ?></a>
					</label>
				</span>
			<?php
		}
	?>
	</p>
</fieldset>

<hr>

<?php $this->description( __( 'When the following options are filled in, Twitter might link your Twitter Site or Personal Profile when your post or page is shared.', 'autodescription' ) ); ?>

<p>
	<label for="<?php $this->field_id( 'twitter_site' ); ?>" class="toblock">
		<strong><?php _e( "Your Website's Twitter Profile", 'autodescription' ); ?></strong>
		<a href="<?php echo esc_url( 'https://twitter.com/home' ); ?>" target="_blank" class="description" title="<?php _e( 'Find your @username', 'autodescription' ); ?>">[?]</a>
	</label>
</p>
<p>
	<input type="text" name="<?php $this->field_name( 'twitter_site' ); ?>" class="large-text" id="<?php $this->field_id( 'twitter_site' ); ?>" placeholder="<?php echo $tw_site_placeholder ?>" value="<?php echo esc_attr( $tw_site ); ?>" />
</p>

<p>
	<label for="<?php $this->field_id( 'twitter_creator' ); ?>" class="toblock">
		<strong><?php _e( 'Your Personal Twitter Profile', 'autodescription' ); ?></strong>
		<a href="<?php echo esc_url( 'https://twitter.com/home' ); ?>" target="_blank" class="description" title="<?php _e( 'Find your @username', 'autodescription' ); ?>">[?]</a>
	</label>
</p>
<p>
	<input type="text" name="<?php $this->field_name( 'twitter_creator' ); ?>" class="large-text" id="<?php $this->field_id( 'twitter_creator' ); ?>" placeholder="<?php echo $tw_creator_placeholder ?>" value="<?php echo esc_attr( $tw_creator ); ?>" />
</p>
<?php
