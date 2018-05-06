/**
 * This file holds the TSF's TSFEM installer JS code.
 * Serve JavaScript as an addition, not as a means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link https://wordpress.org/plugins/autodescription/
 */

'use strict';

/**
 * Hooks into WordPress' updates handler.
 * This is a self-constructed function assigned as an object.
 *
 * @since 3.0.6
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfinstaller = function( $ ) {

	var $document = $( document );

	/**
	 * Updates the UI appropriately after a successful TSFEM install.
	 *
	 * @since 3.0.6
	 * @credit wp.updates.installImporterSuccess
	 *
	 * @function
	 * @typedef {object} installTsfemSuccess
	 * @param {object} response             Response from the server.
	 * @param {string} response.slug        Slug of the installed plugin.
	 * @param {string} response.pluginName  Name of the installed plugin.
	 * @param {string} response.activateUrl URL to activate the just installed plugin.
	 */
	const installTsfemSuccess = function( response ) {
		wp.updates.addAdminNotice( {
			id:        'install-success',
			className: 'notice-success is-dismissible',
			message:   wp.updates.l10n.installedMsg.replace( '%s', response.activateUrl + '&from=plugins' )
		} );

		$( '[data-slug="' + response.slug + '"]' )
			.removeClass( 'install-now updating-message' )
			.addClass( 'activate-now' )
			.attr({
				'href': response.activateUrl + '&from=plugins',
				'aria-label': wp.updates.l10n.activatePluginLabel.replace( '%s', response.pluginName )
			})
			.text( wp.updates.l10n.activatePlugin );

		wp.a11y.speak( wp.updates.l10n.installedMsg, 'polite' );

		$document.trigger( 'tsf-tsfem-install-success', response );
	}

	/**
	 * Updates the UI appropriately after a failed TSFEM install.
	 *
	 * @since 3.0.6
	 * @credit wp.updates.installImporterError
	 *
	 * @function
	 * @typedef {object} installTsfemError
	 * @param {object}  response              Response from the server.
	 * @param {string}  response.slug         Slug of the plugin to be installed.
	 * @param {string=} response.pluginName   Optional. Name of the plugin to be installed.
	 * @param {string}  response.errorCode    Error code for the error that occurred.
	 * @param {string}  response.errorMessage The error that occurred.
	 */
	const installTsfemError = function( response ) {
		var errorMessage = wp.updates.l10n.installFailed.replace( '%s', response.errorMessage ),
			$installLink = $( '[data-slug="' + response.slug + '"]' ),
			pluginName = $installLink.data( 'name' );

		if ( ! wp.updates.isValidResponse( response, 'install' ) ) {
			return;
		}

		if ( wp.updates.maybeHandleCredentialError( response, 'install-plugin' ) ) {
			return;
		}

		wp.updates.addAdminNotice( {
			id:        response.errorCode,
			className: 'notice-error is-dismissible',
			message:   errorMessage
		} );

		$installLink
			.removeClass( 'updating-message' )
			.text( wp.updates.l10n.installNow )
			.attr( 'aria-label', wp.updates.l10n.pluginInstallNowLabel.replace( '%s', pluginName ) );

		wp.a11y.speak( errorMessage, 'assertive' );

		$document.trigger( 'tsf-tsfem-install-error', response );
	}

	/**
	 * Adds installation hooks on DOMContentLoaded.
	 *
	 * @since 3.0.6
	 *
	 * @function
	 * @param {event} event
	 */
	const onReady = ( event ) => {
		if ( ! wp || ! wp.updates ) return;

		var prev_addCallbacks = wp.updates._addCallbacks;

		/**
		 * Hooks into the installation button, to prevent redirect.
		 *
		 * WordPress normally enforces a redirect when the actionable page uses index.php.
		 * Indicating that it's not a "valid" installation page. Which is odd, as
		 * it perfectly allows any other action. Plus it allows this action on any other page,
		 * but the admin "main === index.php" dashboard page, too.
		 *
		 * @source https://github.com/WordPress/WordPress/blob/4.9-branch/wp-admin/js/updates.js#L2395-L2415
		 */
		$( '#plugin_install_from_iframe' ).on( 'click', function( event ) {
			var target = window.parent === window ? null : window.parent,
				install;

			//= Let the default handler take over.
			if ( -1 === window.parent.location.pathname.indexOf( 'index.php' ) )
				return;

			//= Only enact when the slug matches.
			if ( $( this ).data( 'slug' ) !== tsfinstallerL10n.slug )
				return;

			$.support.postMessage = !! window.postMessage;

			if ( false === $.support.postMessage || null === target ) {
				return;
			}

			event.preventDefault();

			install = {
				action: 'install-plugin',
				data:   {
					slug: $( this ).data( 'slug' )
				}
			};

			target.postMessage( JSON.stringify( install ), window.location.origin );
		} );

		//= Direct attach as WP is using preventDefault() when capturing.
		$( '#tsf-tsfem-tb' ).on( 'click', function( event ) {
			var canReset = false;

			/**
			 * Overwrite installer callback catcher.
			 *
			 * This could ONLY possibly conflict with import.php as of WP-4.6.0~4.9.6.
			 * Even then, it recovers itself via the resetter (fail-secures/safes).
			 * Making only super-humans (if even) viable for a UI bug.
			 */
			wp.updates._addCallbacks = function( data, action ) {
				if ( 'install-plugin' === action && tsfinstallerL10n.slug === data.slug ) {
					data.success = installTsfemSuccess;
					data.error   = installTsfemError;

					let $button = $( '[data-slug="' + data.slug + '"]' );
					$button
						.addClass( 'updating-message' )
						.attr( 'aria-label', wp.updates.l10n.pluginInstallingLabel.replace( '%s', $button.data( 'name' ) ) )
						.text( wp.updates.l10n.installing );

					canReset = true;
					// console.log( 'overwritten' );
				}
				// console.log( 'slug: ' + data.slug );

				return data;
			}

			// Thread lightly: Pure magic below.
			$( window ).on( 'message', ( event ) => {
				let message;
				try {
					message = $.parseJSON( event.originalEvent.data );
				} catch ( e ) {
					return;
				}
				if ( ! message || 'undefined' === typeof message.action ) {
					return;
				}
				if ( message.action === 'install-plugin' ) {
					//= Fail safe.
					canReset = false;
				} else {
					//= Fail secure.
					canReset = true;
				}
			} );
			var resetTicker, cbs;
			const resetCb = () => {
				wp.updates._addCallbacks = prev_addCallbacks;
				clearInterval( resetTicker );
				$document.off( cbs, resetCb );
				// console.log( 'reset' );
			}
			const checkReset = () => {
				canReset && resetCb;
				// console.log( 'checked' );
			}
			const prepareReset = () => {
				resetTicker = setInterval( checkReset, 100 );
				setTimeout( resetCb, 750 );
				// console.log( 'prepared' );
			}
			cbs = 'wp-plugin-installing wp-plugin-install-error wp-plugin-install-success';
			//= Fail secure.
			$( 'body' ).one( 'thickbox:removed', prepareReset );
			$document.one( cbs, resetCb );
		} );

		$document.on( 'click', '#tsf-tsfem-install', function( event ) {
			var $button = $( event.target );

			if ( $button.hasClass( 'activate-now' ) ) {
				//? Follow link, activating the plugin.
				return;
			}
			event.preventDefault();

			if ( $button.hasClass( 'updating-message' ) || $button.hasClass( 'button-disabled' ) ) {
				return;
			}

			if ( $button.html() !== wp.updates.l10n.installing ) {
				$button.data( 'originaltext', $button.html() );
			}

			$button
				.addClass( 'updating-message' )
				.attr( 'aria-label', wp.updates.l10n.pluginInstallingLabel.replace( '%s', $button.data( 'name' ) ) )
				.text( wp.updates.l10n.installing );

			if ( wp.updates.shouldRequestFilesystemCredentials && ! wp.updates.ajaxLocked ) {
				wp.updates.requestFilesystemCredentials( event );

				$document.on( 'credential-modal-cancel', function() {
					$button
						.removeClass( 'updating-message' )
						.text( wp.updates.l10n.installNow )
						.attr( 'aria-label', wp.updates.l10n.installNowLabel.replace( '%s', $button.data( 'name' ) ) );

					wp.a11y.speak( wp.updates.l10n.updateCancel, 'polite' );
				} );
			}

			wp.updates.installPlugin( {
				slug:    $button.data( 'slug' ),
				pagenow: pagenow,
				success: installTsfemSuccess,
				error:   installTsfemError
			} );
		} );
	}

	return {
		/**
		 * Runs this script on DOMContentLoaded when WordPress Shiny Updates is
		 * available.
		 *
		 * @since 3.0.6
		 *
		 * @function
		 */
		load: function() {
			tsfinstallerL10n.canEnhance
				&& $( document.body ).ready( onReady );
		}
	};
}( jQuery );
//= Run before jQuery.ready() === DOMContentLoaded
jQuery( window.tsfinstaller.load );
