<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_webmaster_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_webmaster_metabox_main':
		$site_url = $this->get_homepage_permalink();

		$bing_site_url   = 'https://www.bing.com/webmaster/home/addsite?addurl=' . rawurlencode( $site_url );
		$google_site_url = 'https://www.google.com/webmasters/verification/verification?siteUrl=' . rawurlencode( $site_url ) . '&tid=alternate&vtype=vmeta';
		$pint_site_url   = 'https://analytics.pinterest.com/';
		$yandex_site_url = 'https://webmaster.yandex.com/sites/add/?hostName=' . rawurlencode( $site_url );

		?>
		<h4><?php esc_html_e( 'Webmaster Integration Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( "When adding your website to Google, Bing and other Webmaster Tools, you'll be asked to add a code or file to your website for verification purposes. These options will help you easily integrate those codes.", 'autodescription' ) );
		$this->description( __( "Verifying your website has no SEO value whatsoever. But you might gain added benefits such as search ranking insights to help you improve your website's content.", 'autodescription' ) );

		?>
		<hr>

		<p>
			<label for="<?php $this->field_id( 'google_verification' ); ?>">
				<strong><?php esc_html_e( 'Google Search Console Verification Code', 'autodescription' ); ?></strong>
			</label>
			<?php
			$this->make_info(
				__( 'Get the Google verification code.', 'autodescription' ),
				$google_site_url
			);
			?>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'google_verification' ); ?>" class="large-text ltr" id="<?php $this->field_id( 'google_verification' ); ?>" placeholder="ABC1d2eFg34H5iJ6klmNOp7qRstUvWXyZaBc8dEfG9" value="<?php echo esc_attr( $this->get_option( 'google_verification' ) ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'bing_verification' ); ?>">
				<strong><?php esc_html_e( 'Bing Webmaster Verification Code', 'autodescription' ); ?></strong>
			</label>
			<?php
			$this->make_info(
				__( 'Get the Bing verification code.', 'autodescription' ),
				$bing_site_url
			);
			?>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'bing_verification' ); ?>" class="large-text ltr" id="<?php $this->field_id( 'bing_verification' ); ?>" placeholder="123A456B78901C2D3456E7890F1A234D" value="<?php echo esc_attr( $this->get_option( 'bing_verification' ) ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'yandex_verification' ); ?>">
				<strong><?php esc_html_e( 'Yandex Webmaster Verification Code', 'autodescription' ); ?></strong>
			</label>
			<?php
			$this->make_info(
				__( 'Get the Yandex verification code.', 'autodescription' ),
				$yandex_site_url
			);
			?>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'yandex_verification' ); ?>" class="large-text ltr" id="<?php $this->field_id( 'yandex_verification' ); ?>" placeholder="12345abc678901d2" value="<?php echo esc_attr( $this->get_option( 'yandex_verification' ) ); ?>" />
		</p>

		<p>
			<label for="<?php $this->field_id( 'pint_verification' ); ?>">
				<strong><?php esc_html_e( 'Pinterest Analytics Verification Code', 'autodescription' ); ?></strong>
			</label>
			<?php
			$this->make_info(
				__( 'Get the Pinterest verification code.', 'autodescription' ),
				$pint_site_url
			);
			?>
		</p>
		<p>
			<input type="text" name="<?php $this->field_name( 'pint_verification' ); ?>" class="large-text ltr" id="<?php $this->field_id( 'pint_verification' ); ?>" placeholder="123456a7b8901de2fa34bcdef5a67b98" value="<?php echo esc_attr( $this->get_option( 'pint_verification' ) ); ?>" />
		</p>
		<?php
		break;

	default:
		break;
endswitch;
