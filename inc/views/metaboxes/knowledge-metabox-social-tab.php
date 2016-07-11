<h4><?php _e( 'Social Pages connected to this website', 'autodescription' ); ?></h4><?php
$this->description( __( "Don't have a page at a site or is the profile only privately accessible? Leave that field empty. Unsure? Fill it in anyway.", 'autodescription' ) );
$this->description( __( "Add the link that leads directly to the social page of this website.", 'autodescription' ) );

?><hr><?php

$connectedi18n = _x( 'RelatedProfile', 'No spaces. E.g. https://facebook.com/RelatedProfile', 'autodescription' );
$profile18n = _x( 'Profile', 'Social Profile', 'autodescription' );

/**
 * @todo maybe genericons?
 */

$socialsites = array(
	'facebook' => array(
		'option'		=> 'knowledge_facebook',
		'dashicon'		=> 'dashicons-facebook',
		'desc' 			=> 'Facebook ' . __( 'Page', 'autodescription' ),
		'placeholder'	=> 'http://www.facebook.com/' . $connectedi18n,
		'examplelink'	=> esc_url( 'https://facebook.com/me' ),
	),
	'twitter' => array(
		'option'		=> 'knowledge_twitter',
		'dashicon'		=> 'dashicons-twitter',
		'desc' 			=> 'Twitter ' . $profile18n,
		'placeholder'	=> 'http://www.twitter.com/' . $connectedi18n,
		'examplelink'	=> esc_url( 'https://twitter.com/home' ), // No example link available.
	),
	'gplus' => array(
		'option'		=> 'knowledge_gplus',
		'dashicon'		=> 'dashicons-googleplus',
		'desc' 			=>  'Google+ ' . $profile18n,
		'placeholder'	=> 'https://plus.google.com/' . $connectedi18n,
		'examplelink'	=> esc_url( 'https://plus.google.com/me' ),
	),
	'instagram' => array(
		'option'		=> 'knowledge_instagram',
		'dashicon'		=> 'genericon-instagram',
		'desc' 			=> 'Instagram ' . $profile18n,
		'placeholder'	=> 'http://instagram.com/' . $connectedi18n,
		'examplelink'	=> esc_url( 'https://instagram.com/' ), // No example link available.
	),
	'youtube' => array(
		'option'		=> 'knowledge_youtube',
		'dashicon'		=> 'genericon-youtube',
		'desc' 			=> 'Youtube ' . $profile18n,
		'placeholder'	=> 'http://www.youtube.com/' . $connectedi18n,
		'examplelink'	=> esc_url( 'https://www.youtube.com/user/%2f' ), // Yes a double slash.
	),
	'linkedin' => array(
		'option'		=> 'knowledge_linkedin',
		'dashicon'		=> 'genericon-linkedin-alt',
		'desc' 			=> 'LinkedIn ' . $profile18n . ' ID',
		'placeholder'	=> 'http://www.linkedin.com/profile/view?id=' . $connectedi18n,
		'examplelink'	=> esc_url( 'https://www.linkedin.com/profile/view' ), // This generates a query arg. We should allow that.
	),
	'pinterest' => array(
		'option'		=> 'knowledge_pinterest',
		'dashicon'		=> 'genericon-pinterest-alt',
		'desc' 			=> 'Pinterest ' . $profile18n,
		'placeholder'	=> 'https://www.pinterest.com/' . $connectedi18n . '/',
		'examplelink'	=> esc_url( 'https://www.pinterest.com/me/' ),
	),
	'soundcloud' => array(
		'option'		=> 'knowledge_soundcloud',
		'dashicon'		=> 'genericon-cloud', // I know, it's not the real one. D:
		'desc' 			=> 'SoundCloud ' . $profile18n,
		'placeholder'	=> 'https://soundcloud.com/' . $connectedi18n,
		'examplelink'	=> esc_url( 'https://soundcloud.com/you' ),
	),
	'tumblr' => array(
		'option'		=> 'knowledge_tumblr',
		'dashicon'		=> 'genericon-tumblr',
		'desc' 			=> 'Tumblr ' . __( 'Blog', 'autodescription' ),
		'placeholder'	=> 'https://tumblr.com/blog/' . $connectedi18n,
		'examplelink'	=> esc_url( 'https://www.tumblr.com/dashboard' ),  // No example link available.
	),
);

foreach ( $socialsites as $key => $value ) {
	?>
	<p>
		<label for="<?php $this->field_id( $value['option'] ); ?>">
			<strong><?php echo $value['desc'] ?></strong>
			<?php
			if ( $value['examplelink'] ) {
				?><a href="<?php echo esc_url( $value['examplelink'] ); ?>" target="_blank">[?]</a><?php
			}
			?>
		</label>
	</p>
	<p>
		<input type="text" name="<?php $this->field_name( $value['option'] ); ?>" class="large-text" id="<?php $this->field_id( $value['option'] ); ?>" placeholder="<?php echo esc_attr( $value['placeholder'] ) ?>" value="<?php echo esc_attr( $this->get_field_value( $value['option'] ) ); ?>" />
	</p>
	<?php
}
