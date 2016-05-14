<?php
/*
Plugin Name: devRantPress
Plugin URI: https://github.com/mmatthews1981/devrantpress
Description: Adds devRant to the WordPress Dashboard, visible to admin users only.
Author: m.matthews
Version: 0.1
Author URI: https://github.com/mmatthews1981
*/

add_action('admin_head', 'my_custom_admin_css');

function my_custom_admin_css() {
	echo '<style>
		#devrantpress_widget {
			width: inherit;
    height: 350px;
    overflow: scroll;
		}
		
		#devrantpress_widget.closed {
			    height: initial;
    			overflow: auto;
		}
  </style>';
}

add_action('wp_dashboard_setup', 'devrantpress_dashboard_widget');

function devrantpress_dashboard_widget() {
	add_meta_box('devrantpress_widget', 'devRantPress', 'devrantpress', 'dashboard', 'normal','core');
}

function getthejson($url){
	$json_url = $url;
	$ch = curl_init($json_url);
	$options = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => array('Accept: application/json')
	);
	curl_setopt_array($ch, $options);
	return json_decode(curl_exec($ch), true);
}

function update_devrantpress(){
	$devrantstream = getthejson('https://www.devrant.io/api/devrant/rants?app=3&sort=recent');

	update_option( 'devrantpress_feedcache', $devrantstream, true );
}

function devrantpress_button(){
	// This function creates the output for the admin page.
	// It also checks the value of the $_POST variable to see whether
	// there has been a form submission.

	// The check_admin_referer is a WordPress function that does some security
	// checking and is recommended good practice.

	// General check for user permissions.
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient pilchards to access this page.')    );
	}

	// Start building the page

	echo '<div class="wrap">';

	// Check whether the button has been pressed AND also check the nonce
	if (isset($_POST['test_button']) && check_admin_referer('test_button_clicked')) {
		// the button has been pressed AND we've passed the security check
		update_devrantpress();
	}

	echo '<form action="index.php" method="post">';

	// this is a WordPress security feature - see: https://codex.wordpress.org/WordPress_Nonces
	wp_nonce_field('test_button_clicked');
	echo '<input type="hidden" value="true" name="test_button" />';
	submit_button('update devRant');
	echo '</form>';

	echo '</div>';
}

function devrantpressresults(){
	$rants = get_option('devrantpress_feedcache');
	echo '<div>';

	foreach ($rants['rants'] as $rant){
		echo '<div>'.$rant['user_username'].'</div>';
		echo '<div>'.$rant['text'].'</div>';
		echo '<div>'.implode(', ', $rant['tags']).'</div>';
		echo '<hr>';
	}
	echo'</div>';

}

function devrantpress() {

	devrantpress_button();

	devrantpressresults();
}