<?php
/*
Plugin Name: Kin Visitantes
Plugin URI: http://www.kinwebdesign.com
Description: Track visitors to your website easily and effectively. For more information visit, <a href="http://www.kinwebdesign.com">Kin Web Design</a>.
Author: Kin Web Design
Author URI: http://www.kinwebdesign.com
Version: 2.4
*/

/*
  _  _______ _   _  __          ________ ____    _____  ______  _____ _____ _____ _   _ 
 | |/ /_   _| \ | | \ \        / /  ____|  _ \  |  __ \|  ____|/ ____|_   _/ ____| \ | |
 | ' /  | | |  \| |  \ \  /\  / /| |__  | |_) | | |  | | |__  | (___   | || |  __|  \| |
 |  <   | | | . ` |   \ \/  \/ / |  __| |  _ <  | |  | |  __|  \___ \  | || | |_ | . ` |
 | . \ _| |_| |\  |    \  /\  /  | |____| |_) | | |__| | |____ ____) |_| || |__| | |\  |
 |_|\_\_____|_| \_|     \/  \/   |______|____/  |_____/|______|_____/|_____\_____|_| \_|
*/

function kin_visitantes_initialize() {
	$ignored_ip=$_SERVER['REMOTE_ADDR'];
	add_option('ignored_ip',$ignored_ip);
	global $wpdb;
	$table_name=$wpdb->prefix . 'kin_visitantes';
	$charset_collate=$wpdb->get_charset_collate();
	$sql="CREATE TABLE $table_name (
		ID BIGINT (20) AUTO_INCREMENT,
		date_time VARCHAR (20),
		language VARCHAR (10),
		browser VARCHAR (20),
		os VARCHAR (50),
		ip VARCHAR (50),
		source VARCHAR (2000),
		content VARCHAR (2000),
		PRIMARY KEY (ID)
	) $charset_collate;";
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}
register_activation_hook( __FILE__,'kin_visitantes_initialize');

function show_visitors() {
    echo '
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(drawChart);
    function drawChart() {
    	var data = google.visualization.arrayToDataTable([
        	[\'Date\', \'Pageviews\'],
        	';
		    $textual_datetime=array('-6 days','-5 days','-4 days','-3days','-2 days','-1 days','now');
		    $x=7;
		    while($x>0) {
		    	$formated_month=date('m',strtotime('-' . $x . ' days'));
		    	$formated_day_of_month=date('d',strtotime('-' . $x . ' days'));
		    	$formated_year=date('Y',strtotime('-' . $x . ' days'));
			    $results=mysql_num_rows(mysql_query("SELECT * FROM wp_kin_visitantes WHERE FROM_UNIXTIME(date_time, '%m') = $formated_month AND FROM_UNIXTIME(date_time, '%d') = $formated_day_of_month AND FROM_UNIXTIME(date_time, '%Y') = $formated_year"));
		    	echo '[\'' . date('m/d/Y',strtotime('-' . $x . ' days')) . '\', ' . $results . '],';
		    	$x--;
		    }
		echo '  
        ]);
        var options = {
        	titlePosition: \'none\',
        	axisTitlesPosition: \'in\',
        	hAxis: {textPosition: \'in\'},
        	vAxis: {textPosition: \'in\'},
        	chartArea: {width: \'100%\', height: \'100%\'},
        	legend: {position:\'none\'},
        	colors:[\'#2EA2CC\']
        };
        var chart=new google.visualization.AreaChart(document.getElementById(\'chart_div\'));
        chart.draw(data,options);
    }
    </script>
    <div id="chart_div" style="width: 100%; height: 200px;"></div>
    <div>&nbsp;</div>
    <div><a href="' . admin_url('admin.php?page=kin-visitantes/data.php') . '"><strong>See All</strong></a></div>
    ';
}
function visitantes_add_dashboard_widgets() {
	wp_add_dashboard_widget('visitantes_dashboard_widget','Pageviews This Week','show_visitors');	
}
add_action('wp_dashboard_setup','visitantes_add_dashboard_widgets');

function kin_visitantes_collect_info() {
	if (get_option('ignored_ip')!=$_SERVER['REMOTE_ADDR']) {
		$language=explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$user_agent=$_SERVER['HTTP_USER_AGENT'];
		/* Detect Browser */
		$user_browser='Unknown';
		/* List based on http://www.useragentstring.com */
		$browsers=array(
			'Chrome',
			'MSIE',
			'Firefox',
			'Safari',
			'Opera',
			'Netscape',
			'AOL',
		);
		foreach ($browsers as $browser) {
			if (preg_match('/' . $browser . '/i',$user_agent)) {
				$user_browser=$browser;
			}
		}
		/* Detect OS */
		$user_os='Unknown';
		$operating_systems=array(
			'Windows',
			'Mac',
		);
		foreach ($operating_systems as $operating_system) {
			if (preg_match('/' . $operating_system . '/i',$user_agent)) {
				$user_os=$operating_system;
			}
		}
		$ip=$_SERVER['REMOTE_ADDR'];
		$source=$_SERVER['HTTP_REFERER'];
		$content=$_SERVER['REQUEST_URI'];
		global $wpdb;
		$table_name=$wpdb->prefix.'kin_visitantes';
		$wpdb->insert( 
			$table_name, 
			array(
				'date_time'=>time(),
				'language'=>$language[0],
				'browser'=>$user_browser,
				'os'=>$user_os,
				'ip'=>$ip,
				'source'=>$source,
				'content'=>$content,
			)
		);
	}
}
add_action('wp_footer','kin_visitantes_collect_info');

function show_options() {
	if (!empty($_POST['ignored_ip'])) {
		$ignored_ip=$_POST['ignored_ip'];
		update_option('ignored_ip',$ignored_ip);
	}
	echo '
	<div class="wrap">
		<h2>Kin Visitantes Options</h2>
		<p>&nbsp;</p>
		<form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
			<div>
				<div style="color:#222222; font-size:14px; font-weight:600; line-height:1.3; width:200px; float:left;">Ignored IP Address</div>
				<div style="float:left;">
					<input type="text" name="ignored_ip" value="' . get_option('ignored_ip') . '">
				</div>
			</div>
			<div style="clear:both;">
				<div style="width:200px; float:left;">&nbsp;</div>
				<div style="font-style:italic; float:left;">
					<p>Defaults to your IP Address</p>
					<p>Leave blank for none</p>
				</div>
			</div>
			<div style="clear:both;">
				<input type="submit" value="Save Changes" class="button-primary">
			</div>
		</form>
	</div>
	';
}
function show_support_center() {
	global $current_user;
    get_currentuserinfo();
	echo '
	<div class="wrap">
		<h2>Email</h2>
		<p>Get support, news, and resources for business owner and designers.</p>
		<div id="mc_embed_signup">
			<form action="//kinwebdesign.us9.list-manage.com/subscribe/post?u=12cda8b61e1eeaa288e85418f&amp;id=c032dfcbfe" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
				<div id="mc_embed_signup_scroll">
					<div class="mc-field-group" style="clear:both;">
						<div style="width:200px; float:left;"><label for="mce-FNAME"><strong>First Name</strong></label></div>
						<div style="width:200px; float:left;"><input type="text" value="' . $current_user->user_firstname . '" name="FNAME" class="" id="mce-FNAME"></div>
					</div>
					<div class="mc-field-group" style="clear:both;">
						<div style="width:200px; float:left;"><label for="mce-LNAME"><strong>Last Name</strong></label></div>
						<div style="width:200px; float:left;"><input type="text" value="' . $current_user->user_lastname . '" name="LNAME" class="" id="mce-LNAME"></div>
					</div>
					<div class="mc-field-group" style="clear:both;">
						<div style="width:200px; float:left;"><label for="mce-EMAIL"><strong>Email Address</strong></label></div>
						<div style="width:200px; float:left;"><input type="email" value="' . get_bloginfo('admin_email') . '" name="EMAIL" class="required email" id="mce-EMAIL"></div>
					</div>
					<div id="mce-responses" class="clear">
						<div class="response" id="mce-error-response" style="display:none"></div>
						<div class="response" id="mce-success-response" style="display:none"></div>
					</div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
				    <div style="position: absolute; left: -5000px;"><input type="text" name="b_12cda8b61e1eeaa288e85418f_c032dfcbfe" tabindex="-1" value=""></div>
				    <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
			    </div>
			</form>
		</div>
		<h2>Ask a Question</h2>
		<p>Have a question or want to propose a change or new feature? Help other plugin users, by using the <a href="https://wordpress.org/support/plugin/kin-visitantes"><strong>plugin support forum</strong></a></p>
		<p>or email <a href="mailto:sayhello@kinwebdesign.com">sayhello@kinwebdesign.com</a>
		<h2>Suggested Resources</h2>
		<p>Visit <a href="http://www.kinwebdesign.com">Kin Web Design</a> for professional help or the <a href="http://www.kinwebdesign.com/blog">Blog</a> for business and designer resources.</p>
		<p><a href="http://www.huffingtonpost.com/feeds/verticals/small-business/index.xml">Huffington Post</a>
		<p><a href="http://www.kaushik.net/avinash/getting-started-with-web-analytics-step-one-glean-macro-insights" target="_blank">Getting Started With Web Analytics</a></p>
		<p><a href="http://blog.civicplus.com/blog/bid/303443/Your-Key-to-Basic-Web-Analytics" target="_blank">Your Key to Basic Web Analytics</a></p>
	';
}
function register_kin_visitantes_menus() {
	add_menu_page(
		'Visitantes',
		'Visitantes',
		'manage_options',
		'kin-visitantes/dashboard.php',
		'',
		plugins_url('kin-visitantes/images/menu.png')
	);
	add_submenu_page(
		'kin-visitantes/dashboard.php',
		'Kin Visitantes Options',
		'Options',
		'manage_options',
		'options',
		'show_options'
	);
	add_submenu_page(
		'kin-visitantes/dashboard.php',
		'Kin Visitantes Support and Resources',
		'Support/Resources',
		'manage_options',
		'support_resources',
		'show_support_center'
	);
}
add_action('admin_menu','register_kin_visitantes_menus');
?>