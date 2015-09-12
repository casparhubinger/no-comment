<?php
/**
 * Abort message.
 *
 * @return string  Message text (HTML, translatable)
 */
function no_comment__activation_abort_message() {
	$message = sprintf(
		'<p>%s</p>',
		__( 'As you’ve wished, <strong>No Comment has taken no action up to this point</strong>. You can deactivate the plugin now. If you change your mind, deactivate and re-activate the plugin to see the initial activation dialogue again.', 'no-comment' )
	);

	return $message;
}


/**
 * Success message.
 *
* @return string  Message text (HTML, translatable)
 */
function no_comment__activation_success_message() {
	$message = sprintf( '<p>%s</p>', __( '<strong>Trés bien!</strong> Comments are banned from your site now.', 'no-comment' ) );

	// Smile while you still can.
	$random   = no_comment__get_random_message();
	$message .= sprintf( '<p><small><em>%s</em></small></p>', $random );

	return $message;
}


/**
 * Activation message.
 *
 * @return string  Message text (HTML, translatable)
 */
function no_comment__get_activation_message() {
	$message  = sprintf( '<p>%s</p>', __( '<strong>No Comment</strong> has been activated and is about to perform the following operations:', 'no-comment' ) );
	$message .= '<ul class="ul-square">';
	$message .= sprintf( '<li>%s</li>', __( 'Set previously published comments to unapproved. (One-time operation)', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Close comments and pings on all published posts of all post types once. (One-time operation)', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Set global default comment and ping status to closed.', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Remove comments and discussion menu pages from admin menu.', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Remove the comment menu item from admin bar.', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Remove post type support for comments and trackbacks from all registered post types.', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Remove the default comments widget.', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Remove comments from the Dashboard by cloning the Activity widget.', 'no-comment' ) );
	$message .= sprintf( '<li>%s</li>', __( 'Remove the comment feed link from the <head> section of your site.', 'no-comment' ) );
	$message .= '</ul>';
	$message .= sprintf( '<p>%s</p>', __( '<strong>TL;DR</strong>: All things comments/pings will be closed, removed or hidden. Comments published up to this moment will be send back to moderation queue (which then will be hidden by the plugin). When you deactivate the plugin some day, previously published comments will reappear as <em>pending</em>.', 'no-comment' ) );

	$message .= sprintf(
		'<p><a href="%1$s" class="button-primary">%2$s</a>&#160;&#160;&#160;<a href="%3$s" class="button-secondary">%4$s</a></p>',
		esc_url_raw( add_query_arg( array( 'no-comment' => 'run' ) ) ),
		__( 'Yes, let’s do this', 'no-comment' ),
		esc_url_raw( add_query_arg( array( 'no-comment' => 'abort' ) ) ),
		__( 'No, abort', 'no-comment' )
	);

	return $message;
}


/**
 * Random message from a predefined set. Hello, Dolly…
 *
 * @return string  Message text (HTML, translatable)
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
