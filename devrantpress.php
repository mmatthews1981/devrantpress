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

// Function that outputs the contents of the dashboard widget
function dashboard_widget_function() {
    echo '<button id="devrantpress_top" >top</button>';
    echo '<button id="devrantpress_recent" >recent</button>';
    echo '<button id="devrantpress_algo" >algo</button>';
    echo '<div id="devrantpress_widget"></div>';
}

// Function used in the action hook
function add_dashboard_widgets() {
    wp_add_dashboard_widget('devrantpress', 'devrantpress', 'dashboard_widget_function');
}

// Register the new dashboard widget with the 'wp_dashboard_setup' action
add_action('wp_dashboard_setup', 'add_dashboard_widgets' );

add_action( 'admin_footer', 'my_action_javascript' ); // Write our JS below here

function my_action_javascript() { ?>
	<script type="text/javascript" >

	jQuery(document).ready(function($) {

		function mapallthethings(thing){
			$("#devrantpress_widget").empty();
				$.each(thing.rants, function(i, rant){
		        var thecode = '<div class="devrant-container">';
					thecode += '<div class="devrant-box left">';
						thecode += '<div class="devrant-textscore">'+rant.score+'</div>';
						thecode += '<div class="devrant-textlink"><a href="https://www.devrant.io/rants/'+rant.id+'">link</a></div>';
					thecode += '</div>';

					thecode += '<div class="devrant-box right">';
						thecode += '<div class="devrant-name"><a href="https://www.devrant.io/users/'+rant.user_username+'">'+rant.user_username+'</a><span class="devrant-userscore">'+rant.user_score+'</span></div>';
						thecode += '<div class="devrant-text">'+rant.text+'</div>';

						if(rant.attached_image) {
							thecode += '<img src="'+rant.attached_image.url+'" />';
						}
						if(rant.tags) {
							thecode += '<div class="devrant-tags">tags: ' + rant.tags.join(', ')+ '</div>';
						}
					thecode += '</div>';
					thecode += '</div>';

					thecode += '<hr>';
	        	
	            $("#devrantpress_widget").append(thecode);
	        });
		}

  		$.getJSON("https://crossorigin.me/https://www.devrant.io/api/devrant/rants?app=3&sort=recent", function(result){
  			return mapallthethings(result);
	    });

	    $("#devrantpress_top").click(function(){
	    	$.getJSON("https://crossorigin.me/https://www.devrant.io/api/devrant/rants?app=3&sort=top", function(result){
		        return mapallthethings(result);
	    	});
	    });

	    $("#devrantpress_recent").click(function(){
	    	$.getJSON("https://crossorigin.me/https://www.devrant.io/api/devrant/rants?app=3&sort=recent", function(result){
				return mapallthethings(result);
	    	});
	    });

	    $("#devrantpress_algo").click(function(){
	    	$.getJSON("https://crossorigin.me/https://www.devrant.io/api/devrant/rants?app=3&sort=algo", function(result){
				return mapallthethings(result);
	    	});
	    });

	});
	</script> <?php
}