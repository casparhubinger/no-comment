<?php
/**
 * Plugin Name:       No Comment
 * Description:       A plugin to close, disable and remove comments from the WordPress admin UI.
 * Version:           0.2
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
 * Disable Comments everywhere.
 *
 * @return void
 */
function no_comment() {

	// Load l10n files.
	load_plugin_textdomain(
		'no-comment',
		false,
		trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) . 'l10n/'
	);

	/**
	 * Set existing comments to unapproved.
	 *
	 * @return void
	 */
	add_action( 'after_setup_theme', function () {
		$comments = get_comments();
		foreach ( $comments as $comment ) {
			$commentarr = array(
				'comment_ID'       => $comment->comment_ID,
				'comment_approved' => 0
			);
			wp_update_comment( $commentarr );
		}
	}, 1000 );

	/**
	 * Close comments on all published posts of all post types.
	 *
	 * @return void
	 */
	add_action( 'after_setup_theme', function () {
		$posts = get_posts( array( 'post_type' => 'any', 'posts_per_page' => -1 ) );
		foreach ( $posts as $post ) {
			if ( $post->comment_status !== 'closed' ) {
				$postarr = array(
					'ID'             => $post->ID,
					'comment_status' => 'closed'
				);
				wp_update_post( $postarr );
			}
		}
	}, 1000 );

	/**
	 * Set default comment status to closed.
	 *
	 * @return void
	 */
	add_action( 'after_setup_theme', function () {
		if( 'closed' !== get_option( 'default_comment_status' ) ) {
			update_option( 'default_comment_status', 'closed' );
		}
	} );

	/**
	 * Remove comments and discussion menu pages from admin menu.
	 *
	 * @return void
	 */
	add_action( 'admin_menu', function () {
		remove_menu_page( 'edit-comments.php' );
		remove_submenu_page( 'options-general.php', 'options-discussion.php' );
	} );

	/**
	 * Remove comment menu item from admin bar.
	 *
	 * @return void
	 */
	add_action( 'wp_before_admin_bar_render', function () {
		$GLOBALS[ 'wp_admin_bar' ]->remove_menu( 'comments' );
	} );

	/**
	 * Removes post type support for comments from all registered post types.
	 *
	 * @return void
	 */
	add_action( 'init', function () {
		$post_types = get_post_types();
		foreach ( $post_types as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) )
				remove_post_type_support( $post_type, 'comments' );
		}
	}, 1000 );

	/**
	 * Remove default comments widget.
	 *
	 * @return void
	 */
	add_action( 'widgets_init', function() {
		unregister_widget( 'WP_Widget_Recent_Comments' );
	}, 1000 );

	/**
	 * Remove comments from the Dashboard by cloning the Activity widget.
	 *
	 * @return void
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
	} );

	/**
	 * Display admin notice after activation.
	 */
	add_action( 'admin_notices', function () {
		$message = get_transient( 'no_comment_plugin_activation' );
		no_comment__admin_notice( $message );
	} );

}
add_action( 'plugins_loaded', 'no_comment' );

/* Activation/deactivation business. */
include_once( 'inc/activation.php' );
register_activation_hook( __FILE__, 'no_comment__activate' );
