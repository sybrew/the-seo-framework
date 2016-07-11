<?php
$language = $this->google_language();

$page_on_front = $this->has_page_on_front();
$home_description_frompost = false;

$description_from_post_message = '';
$title_from_post_message  = '';

$title_i18n = __( 'Title', 'autodescription' );
$description_i18n = __( 'Description', 'autodescription' );
$home_page_i18n = __( 'Home Page', 'autodescription' );

$home_id = $this->get_the_front_page_ID();
$home_title = $this->escape_title( $this->get_option( 'homepage_title' ) );
$blog_description = $this->get_blogdescription();

/**
 * Home Page Tagline settings.
 * @since 2.3.8
 *
 * @param string $home_tagline The tagline option.
 * @param string $home_tagline_placeholder The option placeholder. Always defaults to description.
 * @param string|void $home_tagline_value The tagline input value.
 * @param string $blog_description Override blog description with option if applicable.
 */
$home_tagline = $this->get_field_value( 'homepage_title_tagline' );
$home_tagline_placeholder = $blog_description;
$home_tagline_value = $home_tagline ? $home_tagline : '';
$blog_description = $home_tagline_value ? $home_tagline_value : $blog_description;

/**
 * Create a placeholder for when there's no custom HomePage title found.
 * @since 2.2.4
 */
$home_title_args = $this->generate_home_title( true, '', '', true, false );
if ( $this->home_page_add_title_tagline() )
	$home_title_placeholder = $this->process_title_additions( $home_title_args['blogname'], $home_title_args['title'], $home_title_args['seplocation'] );
else
	$home_title_placeholder = $home_title_args['title'];

/**
 * If the home title is fetched from the post, notify about that instead.
 * @since 2.2.4
 *
 * Nesting often used translations
 */
if ( empty( $home_title ) && $page_on_front && $this->get_custom_field( '_genesis_title', $home_id ) ) {
	/* translators: 1: Option, 2: Page SEO Settings, 3: Home Page */
	$title_from_post_message = sprintf( __( 'Note: The %1$s is fetched from the %2$s on the %3$s.', 'autodescription' ), $title_i18n, __( 'Page SEO Settings', 'autodescription' ), $home_page_i18n );
}

/**
 * Check for options to calculate title length.
 *
 * @since 2.3.4
 */
if ( $home_title ) {
	$home_title_args = $this->generate_home_title();
	$tit_len_pre = $this->process_title_additions( $home_title_args['title'], $home_title_args['blogname'], $home_title_args['seplocation'] );
} else {
	$tit_len_pre = $home_title_placeholder;
}

//* Fetch the description from the home page.
$frompost_description = $page_on_front ? $this->get_custom_field( '_genesis_description', $home_id ) : '';

//* Fetch the HomePage Description option.
$home_description = $this->get_field_value( 'homepage_description' );

/**
 * Create a placeholder.
 * @since 2.3.4
 */
if ( $frompost_description ) {
	$description_placeholder = $frompost_description;
} else {
	$description_args = array(
		'id' => $home_id,
		'is_home' => true,
		'get_custom_field' => false
	);

	$description_placeholder = $this->generate_description( '', $description_args );
}

/**
 * Checks if the home is blog, the Home Page Metabox description and
 * the frompost description.
 * @since 2.3.4
 */
if ( empty( $home_description ) && $page_on_front && $frompost_description )
	$home_description_frompost = true;

/**
 *
 * If the HomePage Description empty, it will check for the InPost
 * Description set on the Home Page. And it will set the InPost
 * Description as placeholder.
 *
 * Nesting often used translations.
 *
 * Notify that the homepage is a blog.
 * @since 2.2.2
 */
if ( $home_description_frompost ) {
	$page_seo_settings_i18n = __( 'Page SEO Settings', 'autodescription' );
	/* translators: 1: Option, 2: Page SEO Settings, 3: Home Page */
	$description_from_post_message = sprintf( __( 'Note: The %1$s is fetched from the %2$s on the %3$s.', 'autodescription' ), $description_i18n, $page_seo_settings_i18n, $home_page_i18n );
}

$desc_len_pre = $home_description ? $home_description : $description_placeholder;

/**
 * Convert to what Google outputs.
 *
 * This will convert e.g. &raquo; to a single length character.
 * @since 2.3.4
 */
$tit_len = html_entity_decode( $this->escape_title( $tit_len_pre ) );
$desc_len = html_entity_decode( $this->escape_title( $desc_len_pre ) );

?>
<p>
	<label for="<?php $this->field_id( 'homepage_title_tagline' ); ?>" class="toblock">
		<strong><?php printf( __( 'Custom %s Title Tagline', 'autodescription' ), $home_page_i18n ); ?></strong>
	</label>
</p>
<p>
	<input type="text" name="<?php $this->field_name( 'homepage_title_tagline' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_title_tagline' ); ?>" placeholder="<?php echo $home_tagline_placeholder ?>" value="<?php echo esc_attr( $home_tagline_value ); ?>" />
</p>

<hr>

<p>
	<label for="<?php $this->field_id( 'homepage_title' ); ?>" class="toblock">
		<strong><?php printf( __( 'Custom %s Title', 'autodescription' ), $home_page_i18n ); ?></strong>
		<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#3' ); ?>" target="_blank" title="<?php _e( 'Recommended Length: 50 to 55 characters', 'autodescription' ) ?>">[?]</a>
		<span class="description theseoframework-counter"><?php printf( __( 'Characters Used: %s', 'autodescription' ), '<span id="' . $this->field_id( 'homepage_title', false ) . '_chars">'. mb_strlen( $tit_len ) .'</span>' ); ?></span>
	</label>
</p>
<p id="autodescription-title-wrap">
	<input type="text" name="<?php $this->field_name( 'homepage_title' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_title' ); ?>" placeholder="<?php echo $home_title_placeholder ?>" value="<?php echo esc_attr( $home_title ); ?>" />
	<span id="autodescription-title-offset" class="hide-if-no-js"></span><span id="autodescription-title-placeholder" class="hide-if-no-js"></span>
</p>
<?php
if ( $title_from_post_message ) {
	echo '<p class="description">' . $title_from_post_message . '</p>';
}
?>
<hr>

<p>
	<label for="<?php $this->field_id( 'homepage_description' ); ?>" class="toblock">
		<strong><?php printf( __( 'Custom %s Description', 'autodescription' ), $home_page_i18n ); ?></strong>
		<a href="<?php echo esc_url( 'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#1' ); ?>" target="_blank" title="<?php _e( 'Recommended Length: 145 to 155 characters', 'autodescription' ) ?>">[?]</a>
		<span class="description theseoframework-counter"><?php printf( __( 'Characters Used: %s', 'autodescription' ), '<span id="' . $this->field_id( 'homepage_description', false ) . '_chars">'. mb_strlen( $desc_len ) .'</span>' ); ?></span>
	</label>
</p>
<p>
	<textarea name="<?php $this->field_name( 'homepage_description' ); ?>" class="large-text" id="<?php $this->field_id( 'homepage_description' ); ?>" rows="3" cols="70"  placeholder="<?php echo $description_placeholder ?>"><?php echo esc_textarea( $home_description ); ?></textarea>
</p>
<?php
$this->description( __( 'The meta description can be used to determine the text used under the title on Search Engine results pages.', 'autodescription' ) );

if ( $description_from_post_message ) {
	echo '<p class="description">' . $description_from_post_message . '</p>';
}
