<?php
$language = $this->google_language();
$home_page_i18n = __( 'Home Page', 'autodescription' );

//* Get home page ID. If blog on front, it's 0.
$home_id = $this->get_the_front_page_ID();

$noindex_post = $this->get_custom_field( '_genesis_noindex', $home_id );
$nofollow_post = $this->get_custom_field( '_genesis_nofollow', $home_id );
$noarchive_post = $this->get_custom_field( '_genesis_noarchive', $home_id );

$checked_home = '';
/**
 * Shows user that the setting is checked on the home page.
 * Adds starting - with space to maintain readability.
 *
 * @since 2.2.4
 */
if ( $noindex_post || $nofollow_post || $noarchive_post ) {
	$checked_home = ' - <a href="' . esc_url( admin_url( 'post.php?post=' . $home_id . '&action=edit#theseoframework-inpost-box' ) ) . '" target="_blank" class="attention" title="' . __( 'View Home Page Settings', 'autodescription' ) . '" >' . __( 'Checked in Page', 'autodescription' ) . '</a>';
}

?><h4><?php _e( 'Home Page Robots Meta Settings', 'autodescription' ); ?></h4><?php

$noindex_note = $noindex_post ? $checked_home : '';
$nofollow_note = $nofollow_post ? $checked_home : '';
$noarchive_note = $noarchive_post ? $checked_home : '';

//* Index label.
/* translators: 1: Option, 2: Location */
$i_label	= sprintf( __( 'Apply %1$s to the %2$s?', 'autodescription' ), $this->code_wrap( 'noindex' ), $home_page_i18n );
$i_label	.= ' ';
$i_label	.= $this->make_info(
					__( 'Tell Search Engines not to show this page in their search results', 'autodescription' ),
					'https://support.google.com/webmasters/answer/93710?hl=' . $language,
					false
				)
			. $noindex_note;

//* Follow label.
/* translators: 1: Option, 2: Location */
$f_label	= sprintf( __( 'Apply %1$s to the %2$s?', 'autodescription' ), $this->code_wrap( 'nofollow' ), $home_page_i18n );
$f_label	.= ' ';
$f_label	.= $this->make_info(
					__( 'Tell Search Engines not to follow links on this page', 'autodescription' ),
					'https://support.google.com/webmasters/answer/96569?hl=' . $language,
					false
				)
			. $nofollow_note;

//* Archive label.
/* translators: 1: Option, 2: Location */
$a_label	= sprintf( __( 'Apply %1$s to the %2$s?', 'autodescription' ), $this->code_wrap( 'noarchive' ), $home_page_i18n );
$a_label	.= ' ';
$a_label	.=	$this->make_info(
				__( 'Tell Search Engines not to save a cached copy of this page', 'autodescription' ),
				'https://support.google.com/webmasters/answer/79812?hl=' . $language,
				false
			)
			. $noarchive_note;

//* Echo checkboxes.
$this->wrap_fields(
	array(
		$this->make_checkbox(
			'homepage_noindex',
			$i_label,
			''
		),
		$this->make_checkbox(
			'homepage_nofollow',
			$f_label,
			''
		),
		$this->make_checkbox(
			'homepage_noarchive',
			$a_label,
			''
		),
	),
	true
);

// Add notice if any options are checked on the post.
if ( $noindex_post || $nofollow_post || $noarchive_post ) {
	$this->description( __( 'Note: If any of these options are unchecked, but are checked on the Home Page, they will be outputted regardless.', 'autodescription' ) );
}
?>

<hr>

<h4><?php _e( 'Home Page Pagination Robots Settings', 'autodescription' ); ?></h4>
<?php $this->description( __( "If your Home Page is paginated and outputs content that's also found elsewhere on the website, enabling this option might prevent duplicate content.", 'autodescription' ) ); ?>

<?php
//* Echo checkbox.
$this->wrap_fields(
	$this->make_checkbox(
		'home_paged_noindex',
		/* translators: 1: Option, 2: Location */
		sprintf( __( 'Apply %1$s to every second or later page on the %2$s?', 'autodescription' ), $this->code_wrap( 'noindex' ), $home_page_i18n ),
		''
	),
	true
);
