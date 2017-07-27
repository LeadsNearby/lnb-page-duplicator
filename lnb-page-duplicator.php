<?php
/*
Plugin Name: LeadsNearby Page Duplicator
Plugin URI: http://leadsnearby.com
Description: Basic Page Duplicator
Version: 1.0.0
Author: Leads Nearby
Author URI: http://leadsnerby.com
License: GPLv2
*/

require_once( plugin_dir_path( __FILE__ ) . '/inc/class-page-duplicator.php' );

// Add the duplicate link to action list for post_row_actions
function lnb_page_duplicator_link( $actions, $post ) {

	if( current_user_can( 'edit_posts' ) ) {

		$actions['duplicate'] = '<a href="admin.php?action=lnb_duplicate_post&amp;post=' . $post->ID . '" title="Clone this item" rel="permalink">Clone</a>';

	}

return $actions;

}
	 
add_filter( 'post_row_actions', 'lnb_page_duplicator_link', 10, 2 );
add_filter( 'page_row_actions', 'lnb_page_duplicator_link', 10, 2);

function lnb_page_duplicator_init() {

	$post_id = $_GET['post'];

	if( ! ( $post_id && ( $_REQUEST['action'] && 'lnb_duplicate_post' == $_REQUEST['action'] ) ) ) {

		LeadsNearby_Page_Duplicator::throw_error( 'No post to duplicate has been supplied!' );

	} else {

		$draft = new LeadsNearby_Page_Duplicator( $post_id );

	}

}

add_action( 'admin_action_lnb_duplicate_post', 'lnb_page_duplicator_init' );