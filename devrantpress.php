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
    overflow-x: hidden;
		}
		
		#devrantpress_widget.closed {
			    height: initial;
    			overflow: auto;
		}
		.devrant-container {
			display: flex;
		}
		
		.devrant-box {
			padding: 10px;
		}
		
		.devrant-textscore {
			font-weight: 700;
			font-size: 22px;
			margin-bottom: 5px;
			text-align: center;
		}
		
		.devrant-textlink a{
			color: #aaaab8;
		    font-size: 12px;
		    border: 2px solid #e3e3e3;
		    border-radius: 5px;
		    font-weight: bold;
		    background-color: #fff;
		    display: inline-block;
		    margin-top: 4px;
		    text-align: center;
		}
		
		.devrant-textlink a:hover {
			color: #d55161;
		    border-color: #d55161;
		    -webkit-transition: all .15s ease-in-out;
		    transition: all .15s ease-in-out;
		    cursor: pointer;
		}
		
		.devrant-name a {
			font-weight: bold;
		    color: #54556e;
		    font-size: 16px;
		    float: left;
		    -webkit-transition: color .15s ease-in-out;
		    transition: color .15s ease-in-out;
		}
		
		.devrant-name:hover,{
			    color: #d55161;
		}
		
		.devrant-userscore {
			background-color: #54556e;
		    color: #fff;
		    font-weight: bold;
		    font-size: 12px;
		    padding: 2px 4px;
		    border-radius: 4px;
		    display: inline-block;
		    margin-left: 7px;
		    -webkit-transition: background-color .15s ease-in-out;
		    transition: background-color .15s ease-in-out;
		}
		
		.devrant-userscore:hover{
			background-color: #d55161;
		}
		
		.devrant-text {
			margin: 8px 0 8px 0;
		}
		
		.devrant-tags{
			font-size: 11px;
    		color: #999;
		}
		
		.devrant-box img{
			width: 100%;
			border-radius: 5px;
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

function devrantpress_button(){

	if(null == get_option('devrantpress_sort')){update_option( 'devrantpress_sort', 'recent', true );}

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

	echo '<form action="index.php" method="post">';
	wp_nonce_field('test_button_clicked');
	echo '<input type="hidden" value="true" name="test_button" />';
	$options = get_option( 'devrantpress_sort' );
	echo '<label>top</label>';
	echo '<input type="radio" name="devrantpress_sort" value="top"'.checked($options['devrantpress_sort'], 'top', true  ).' />';
	echo '<label>recent</label>';
	echo '<input type="radio" name="devrantpress_sort" value="recent"'.checked( $options['devrantpress_sort'], 'recent', true ).' />';
	echo '<label>algo</label>';
	echo '<input type="radio" name="devrantpress_sort" value="algo"'.checked( $options['devrantpress_sort'], 'algo', true ).' />';

	submit_button('update devRant');
	echo '</form>';

	echo '</div>';

	// Check whether the button has been pressed
	if (isset($_POST['test_button']) && check_admin_referer('test_button_clicked')) {
		// the button has been pressed AND we've passed the security check
		$devrantstream = getthejson('https://www.devrant.io/api/devrant/rants?app=3&sort=recent');

		update_option( 'devrantpress_sort', $_POST['devrantpress_sort'], true );

		update_option( 'devrantpress_feedcache', $devrantstream, true );
	}
}

function devrantpressresults(){
	$rants = get_option('devrantpress_feedcache');
	echo '<div>';

	foreach ($rants['rants'] as $rant){
		echo '<div class="devrant-container">';
		echo '<div class="devrant-box left">';
			echo '<div class="devrant-textscore">'.$rant['score'].'</div>';
			echo '<div class="devrant-textlink"><a href="https://www.devrant.io/rants/'.$rant['id'].'">link</a></div>';
		echo '</div>';

		echo '<div class="devrant-box right">';
			echo '<div class="devrant-name"><a href="https://www.devrant.io/users/'.$rant['user_username'].'">'.$rant['user_username'].'</a><span class="devrant-userscore">'.$rant['user_score'].'</span></div>';
			echo '<div class="devrant-text">'.$rant['text'].'</div>';

			if($rant['attached_image']) {
				echo '<img src="'.$rant['attached_image']['url'].'" />';
			}
			if($rant['tags']) {
				echo '<div class="devrant-tags">tags: ' . implode( ', ', $rant['tags'] ) . '</div>';
			}
		echo '</div>';
		echo '</div>';

		echo '<hr>';
	}
	echo'</div>';

}

function devrantpress() {

	devrantpress_button();

	devrantpressresults();
}