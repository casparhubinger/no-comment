<?php
/**
 * We’ll use the "after_setup_theme" hook in this file quite a lot,
 * where usually other hooks would seem more appropriate.
 * This is just to be safe; people do the weirdest things with themes these days.
 */


/**
 * Set existing comments to unapproved.
 */
add_action( 'after_setup_theme', function () {

	// Stop here if we have done this before.
	if ( 'gotcha' === get_option( 'no_comment__sent_comments_to_mod_once' ) )
		return;

	$comments = get_comments();
	foreach ( $comments as $comment ) {
		$commentarr = array(
			'comment_ID'       => $comment->comment_ID,
			'comment_approved' => 0
		);
		wp_update_comment( $commentarr );
	}

	// No need doing this again, comments will be closed from now on.
	update_option( 'no_comment__sent_comments_to_mod_once', 'gotcha' );
}, no_comment__ego() );


/**
 * Close comments on all published posts of all post types.
 *
 * This may come costly on sites with many posts, but it seems more reliable than
 * comments_open/pings_open filters. After all, that’s why we’re promting the user
 * to confirm plugin operations after activation.
 */
add_action( 'after_setup_theme', function () {

	// Stop here if we have done this before.
	if ( 'gotcha' === get_option( 'no_comment__closed_comments_on_posts_once' ) )
		return;

	$posts = get_posts( array( 'post_type' => 'any', 'posts_per_page' => -1 ) );
	foreach ( $posts as $post ) {
		if ( 'closed' !== $post->comment_status || 'closed' !== $post->ping_status ) {
			$postarr = array(
				'ID'             => $post->ID,
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			);
			wp_update_post( $postarr );
		}
	}

	// No need doing this again, comments will be closed from now on.
	update_option( 'no_comment__closed_comments_on_posts_once', 'gotcha' );

}, no_comment__ego() );


/**
 * Set default comment/ping status to closed.
 */
add_action( 'after_setup_theme', function () {
	$status_types = array( 'default_comment_status', 'default_ping_status' );
	foreach ( $status_types as $status_type ) {
		if( 'closed' !== get_option( $status_type ) ) {

			// Try change this from the settings page, haha.
			update_option( $status_type, 'closed' );

			/**
			 * Users can still access discussions settings directly via URL,
			 * so we should say something in case these settings are edited manually,
			 * but get shut down automatically right away.
			 *
			 * But instead of pulling off a ton of transients fireworks, we’ll slack here
			 * and display a generic warning when hidden admin menu pages get accessed directly.
			 * See below…
			 */
		}
	}
}, no_comment__ego() );


/**
 * Remove comments and discussion menu pages from admin menu.
 */
add_action( 'admin_menu', function () {

	// Remove admin menu pages.
	remove_menu_page( 'edit-comments.php' );
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );

	/**
	 * Users can still access these pages directly via URL, so we should
	 * remind them about the plugin in case they find their way here.
	 */
	add_action( 'admin_notices', function () {
		$forbidden = array(
			'edit-comments.php',
			'options-discussion.php',
		);
		if ( ! in_array( $GLOBALS[ 'pagenow' ], $forbidden ) ) {
			return;
		}
		$message  = '<p>';
		$message .= sprintf(
			__( 'This page is currently hidden from the admin menu, although you can access it directly. Anything you edit here may not work as expected.<br />If you want to use comments or pings on your site, <a href="%s">deactivate the <strong>No Comment</strong> plugin first</a>.', 'no-comment' ),
			esc_url( add_query_arg( 'plugin_status', 'active', admin_url( 'plugins.php' ) ) )
		);
		$message .= '</p>';
		no_comment__admin_notice( $message, 'notice-warning is-dismissible' );
	} );
}, no_comment__ego() );


/**
 * Remove comment menu item from admin bar.
 */
add_action( 'wp_before_admin_bar_render', function () {
	$GLOBALS[ 'wp_admin_bar' ]->remove_menu( 'comments' );
}, no_comment__ego() );


/**
 * Removes post type support for comments from all registered post types.
 * Will also remove Allow Comments/Pings checkboxes from QuickEdit.
 */
add_action( 'init', function () {
	$post_types = get_post_types();
	$comment_types = array( 'comments', 'trackbacks' );
	foreach ( $post_types as $post_type ) {
		foreach ( $comment_types as $comment_type ) {
			if ( post_type_supports( $post_type, $comment_type ) ) {
				remove_post_type_support( $post_type, $comment_type );
			}
		}
	}
}, no_comment__ego() );


/**
 * Remove default comments widget.
 */
add_action( 'widgets_init', function() {
	unregister_widget( 'WP_Widget_Recent_Comments' );
}, no_comment__ego() );


/**
 * Remove comments from the Dashboard by cloning the Activity widget.
 */
add_action( 'wp_dashboard_setup', function () {
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');
	wp_add_dashboard_widget(
		'no_comment__dashboard_activity',
		__( 'Activity' ),
		function () {
			echo '<div id="activity-widget">';
			$future_posts = wp_dashboard_recent_posts( array(
				'max'     => 5,
				'status'  => 'future',
				'order'   => 'ASC',
				'title'   => __( 'Publishing Soon' ),
				'id'      => 'future-posts',
			) );
			$recent_posts = wp_dashboard_recent_posts( array(
				'max'     => 5,
				'status'  => 'publish',
				'order'   => 'DESC',
				'title'   => __( 'Recently Published' ),
				'id'      => 'published-posts',
			) );
			if ( ! $future_posts && ! $recent_posts ) {
				echo '<div class="no-activity">';
				echo '<p class="smiley"></p>';
				echo '<p>' . __( 'No activity yet!' ) . '</p>';
				echo '</div>';
			}
			echo '</div>';
		}
	);
} ); // No ego priority here, give peace a chance.


/**
 * Remove comment feeds from wp_head.
 */
add_action( 'after_setup_theme', function () {

	if ( version_compare( $GLOBALS[ 'wp_version' ], '4.4', '<' ) ) {
		/**
		 * Before WordPress 4.4 we have to replace the feed_links() core function
		 * to do this. Shuks!
		 *
		 * @link http://wordpress.stackexchange.com/a/190524/23011
		 */
		remove_action( 'wp_head', 'feed_links', 2 );
		add_action( 'wp_head', function() {
			printf(
				'<link rel="alternate" type="application/rss+xml" title="%1$s &raquo; Feed" href="%2$s" />' . "\n",
				esc_attr( get_bloginfo( 'name' ) ),
				esc_url( get_bloginfo( 'rss2_url' ) )
			);
		} );
	} else {
		/**
		 * From 4.4 on, this is so much more comfortable.
		 */
		add_filter( 'feed_links_show_comments_feed', '__return_false' );
	}
}, no_comment__ego() );
