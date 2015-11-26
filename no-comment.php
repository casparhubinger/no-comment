<?php
/**
 * Plugin Name:       No Comment
 * Description:       A plugin to close, disable and remove comments from the WordPress admin UI.
 * Version:           0.6
 * Author:            Caspar Hübinger
 * Plugin URI:        https://github.com/glueckpress/no-comment/
 * GitHub Plugin URI: https://github.com/glueckpress/no-comment
 * Author URI:        https://profiles.wordpress.org/glueckpress
 * License: GNU       General Public License v3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       no-comment
 * Domain Path:       /l10n
 */


if( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Initiate plugin.
 *
 * @return void
 */
function no_comment() {

	/**
	 * Load translations.
	 */
	load_plugin_textdomain(
		'no-comment',
		false,
		trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) . 'l10n/'
	);

	/**
	 * Plugin status.
	 */
	$status = no_comment__status();

	// Activation has run before, stop here and keep the plugin going.
	if ( 'activated' === $status ) {
		include_once( 'inc/run.php' );
		return;
	}

	/**
	 * Plugin Activation:
	 *
	 * When first activated, the plugin doesn’t do anything.
	 * Instead it will display an admin notice informing the user
	 * what operations it is about to perform and prompt the user to
	 * either confirm or abort.
	 *
	 * In case the user confirms, the plugin will run once, including the
	 * performance of one-time operations (sending previously published comments
	 * to moderation, setting comment and ping status on all posts to closed).
	 * After its first loop, the plugin will display a success notice once and
	 * then keep running (while not performing its one-time operations again).
	 *
	 * In case the user aborts, the plugin will display a notice that no operations
	 * have been performed and the user can deactivate the plugin.
	 *
	 * Once aborted, the plugin needs to be deactivated and re-activated in order to show
	 * the activation dialogue again.
	 */
	include_once( 'inc/activation.php' );

	// Activation has not run yet, maybe run it now.
	if ( 'aborted' === $status ) {
		$maybe_run = 'abort';
	} else {
		$maybe_run = isset( $_GET[ 'no-comment' ] ) ? $_GET[ 'no-comment' ] : '';
	}

	// Activation dialogue.
	switch ( $maybe_run ) {

		// Run plugin and display message once.
		case 'run':
			include_once( 'inc/run.php' );
			add_action( 'admin_notices', function () {
				check_admin_referer( 'no_comment_activating' );
				$message  = no_comment__activation_success_message();
				no_comment__admin_notice( $message, 'notice-success is-dismissible' );
			} );
			update_option( 'no_comment__status', 'activated' );
			break;

		// Abort and display message once.
		case 'abort':
			add_action( 'admin_notices', function () {
				check_admin_referer( 'no_comment_aborting' );
				$message  = no_comment__activation_abort_message();
				no_comment__admin_notice( $message, 'notice-warning is-dismissible' );
			} );
			// No need to update with the same value.
			if ( 'aborted' !== $status ) {
				update_option( 'no_comment__status', 'aborted' );
			}
			break;

		// Display post-activation prompt message (run|abort).
		default:
			if ( ! $status ) {
				add_action( 'admin_notices', function () {
					$message = no_comment__get_activation_message();
					no_comment__admin_notice( $message, 'notice-info' );
				} );
			}
			break;
	}

}
add_action( 'plugins_loaded', 'no_comment' );


/**
 * Get plugin status.
 *
 * @return bool|string  false|[aborted|activating|activated]
 */
function no_comment__status() {
	return get_option( 'no_comment__status' );
}


/**
 * Print admin notice.
 *
 * @param  string  Message inside admin notice
 * @param  string  Class atrribute value
 * @return void
 */
function no_comment__admin_notice( $message = '', $class = 'notice-success' ) {
	if ( ! empty( $message ) ) {
		printf( '<div class="%2$s notice">%1$s</div>', $message, $class );
	}
}


/**
 * Filterable master priority for hooks.
 *
 * We want to be latest to the party in order to be able
 * to screw everyone else over. ;)
 *
 * @return integer
 */
function no_comment__ego() {
	$prio = apply_filters( 'no_comment__ego', 1000 );
	return absint( $prio );
}


/**
 * Leave neat and clean.
 *
 * @return void
 */
register_deactivation_hook( __FILE__, function () {
	delete_option( 'no_comment__status' );
	delete_option( 'no_comment__sent_comments_to_mod_once' );
	delete_option( 'no_comment__closed_comments_on_posts_once' );
} );
