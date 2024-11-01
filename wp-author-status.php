<?php
/*
Plugin Name: WP Author Status
Plugin URI: http://wordpress.org/plugins/wp-author-status/
Description: WP Author Status plugin allows authors to easily update their status to WordPress site.
Author: moviehour
Author URI: https://profiles.wordpress.org/moviehour/
Version: 1.0
*/

/*  Copyright 2014  Iftekhar  (email : realwebcare@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(is_admin()) {
	add_action('wp_dashboard_setup', 'add_author_status_widgets' );
	
	function add_author_status_widgets() {
		if ( !current_user_can( 'edit_posts' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		wp_add_dashboard_widget('author_status_widget', 'Author Status Widget', 'admin_wp_author_status');
	}
}

function author_status_style_admin() {
	wp_enqueue_style('adminstatus', WP_PLUGIN_URL .'/wp-author-status/css/admin_status.css?v=1.0');
}
add_action('admin_init', 'author_status_style_admin');

function author_status_style_user() {
	wp_enqueue_style('authstatus', WP_PLUGIN_URL .'/wp-author-status/css/author_status.css?v=1.0');
}
add_action('wp_enqueue_scripts', 'author_status_style_user');

function create_wp_author_status() {
	global $wpdb;
	$user_id = get_current_user_id();
	$user_info = get_userdata($user_id);
    $status_key = 'author_status';
	$date_key = 'status_date';
	$author_status = get_user_meta($user_id, $status_key, true);
	$status_date = get_user_meta($user_id, $date_key, true);
	
    if($author_status == '' || $status_date == '') {
		if($author_status == '') {
			$author_status = 'Dear Authority, I am ' . $user_info->nickname . ' I have been associated to your network to present you nice and quality articles';
	   	    delete_user_meta($user_id, $status_key);
	       	add_user_meta($user_id, $status_key, $author_status);
		}
		if($status_date == '') {
			$status_date = current_time( 'mysql' );
	        delete_user_meta($user_id, $date_key);
	   	    add_user_meta($user_id, $date_key, $status_date);
		}
		$defaults = array(	'status' => $author_status,
							'status_date' => $status_date,
							'status_row' => 5,
							'status_col' => 60);
		if (!is_array($author_status)) $author_status = array();
		$author_status = array_merge( $defaults, $author_status );
    }else{
		$defaults = array(	'status' => $author_status,
							'status_date' => current_time( 'mysql' ),
							'status_row' => 5,
							'status_col' => 60);
		if (!is_array($author_status)) $author_status = array();
		$author_status = array_merge( $defaults, $author_status );
	}
	return $author_status;
}

function admin_wp_author_status() {
	$author_status = create_wp_author_status();
	$userID = get_current_user_id();
	if (!empty($_POST['author_status_submit']) ) {			
		if ( current_user_can('edit_posts') ) { $author_status['status'] =  stripslashes($_POST['author_status']); }
		else { $author_status['status'] = stripslashes( wp_filter_post_kses( $_POST['author_status'] ) ); }

		$status = $author_status['status'];
		$status_date = $author_status['status_date'];
		update_user_meta($userID, 'author_status', $status);
		update_user_meta($userID, 'status_date', $status_date);
	}
	$statusdate = get_user_meta($userID, 'status_date', true);
	$statusdate = strtotime($statusdate);
	$current_date = current_time('timestamp');
	$form = '<div class="author_status">';
	$form .= '<div class="status_display">';
	$form .= '<h3>Author Status</h3>';
	$form .= '<div id="status-body">';
	$form .= '<form method="post" action="">';
	$form .= '<textarea id="author_status" name="author_status" rows="'.(int)$author_status['status_row'].'"  cols="'.(int)$author_status['status_col'].'"';
	if (!current_user_can('edit_posts')) $form.= ' readonly="readonly"';
	$form .= '>'. esc_textarea($author_status['status']).'</textarea>';
	if (current_user_can('edit_posts')) $form .= '<p><input type="submit" value="' . __('Update Status') . '" class="button-primary" /></p> 
		<input type="hidden" name="author_status_submit" value="true" />';
	$form .= '</form>';
	$form .= '<div class="status-date-show">' . human_time_diff($statusdate, $current_date) . ' ago</div>';
	$form .= '</div>';
	$form .= '</div>';
	$form .= '</div>';
 	echo $form;
}

function show_author_status() {
	$userid = get_the_author_meta('ID');
	$status = get_user_meta($userid, 'author_status', true);
	$status_date = get_user_meta($userid, 'status_date', true);
	$status_date = strtotime($status_date);
	$current_date = current_time('timestamp');
	if ($status != '') $author_status = wp_kses_post($status);
?>
<div id="author_status">
	<div class="status_name"><?php echo get_the_author_meta('user_nicename'); ?></div>
	<div class="status_counts"><?php the_author_posts(); ?> Post(s)</div>
	<div class="author-avatar"><?php echo get_avatar( get_the_author_email(), '100', '', get_the_author_meta('user_nicename') ); ?></div>
	<div class="status_info">
		<div class="status-text">
			<p><?php echo $author_status; ?></p>
		</div>
		<p class="status-time"><?php echo human_time_diff($status_date, $current_date); ?> ago</p>
	</div>
</div>
<?php
}
?>