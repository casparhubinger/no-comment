<?php
/**
 * Set transient with admin notice for 10s after activation.
 *
 * @return void
 */
function no_comment__activate() {
	$message = no_comment__get_activation_message();
	set_transient( 'no_comment_plugin_activation', $message, 5 );
}

/**
 * Delete transient (just in case plugin gets deactivated earlier than 10s after acitvation).
 *
 * @return void
 */
function no_comment__deactivate() {
	delete_transient( 'no_comment_plugin_activation' );
}
register_deactivation_hook( __FILE__, 'no_comment__deactivate' );

/**
 * Print admin notice.
 *
 * @param  string Message inside admin notice
 * @return void
 */
function no_comment__admin_notice( $message = '' ) {
	if ( ! empty( $message ) ) {
		printf( '<div class="updated notice is-dismissible">%s</div>', $message );
	}
}

/**
 * Compose activation message.
 *
 * @return string Inner admin notice HTML with message text.
 */
function no_comment__get_activation_message() {
	$message  = sprintf( '<p>%s</p>', __( '<strong>No Comment</strong> has been activated and has performed the following operations:', 'no-comment' ) );
	$message .= '<ul class="ul-square">';
	$message .= sprintf( '<li>%s</li>', __( 'Set previously published comments to unapproved.', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Closed comments and pings on all published posts of all post types once. (Future status handled by global default status.)', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Set global default comment and ping status to closed.', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Removed comments and discussion menu pages from admin menu.', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Removed the comment menu item from admin bar.', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Removed post type support for comments and trackbacks from all registered post types.', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Removed the default comments widget.', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Removed comments from the Dashboard by cloning the Activity widget.', 'no-comment' ) );
	$message .= '</ul>';
	$message .= sprintf( '<p>%s</p>', __( '<strong>TL;DR</strong>: Previously published comments have not been deleted. When you deactivate the plugin, they will reappear as <em>unapproved</em> in your moderation queue.', 'no-comment' ) );

	// Smile while you still can.
	$random   = no_comment__get_random_message();
	$message .= sprintf( '<p><small><em>%s</em></small></p>', $random );

	return $message;
}

/**
 * Fetch a random message from a predefined set of messages. Hello, Dolly!
 *
 * @return string Random message text.
 */
function no_comment__get_random_message() {
	$random = array(
		__( 'Hey, neat hairdo!', 'no-comment' ),
		__( 'Is it tea time yet?', 'no-comment' ),
		__( '“Where have all the comments gone, long time passing…”', 'no-comment' ),
		__( 'Hey, you’ve nailed this. How about giving yourself a treat?', 'no-comment' ),
		__( 'Ok, moar coffee now…', 'no-comment' ),
		__( 'Imagine: Somewhere someone is tearing their hair out over a problem right now, and it’s not you!', 'no-comment' ),
		__( 'So the plural of “status” is… “status”. You knew that, didn’t you?', 'no-comment' ),
	);
	shuffle( $random );
	$randomized = mt_rand( 0, count( $random ) - 1 );

	return $random[ $randomized ];
}
