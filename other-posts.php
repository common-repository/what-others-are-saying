<?php
/*
Plugin Name: What Others Are Saying
Plugin URI: http://www.stuffbysarah.net/blog/wordpress-plugins/what-others-are-saying/
Description: Use the RSS field in your Blogroll and display the most recent post from sites that you link to. 
Version: 1.7
Author: SarahG
Author URI: http://www.stuffbysarah.net

Installation Instructions:
http://www.stuffbysarah.net/blog/wordpress-plugins/what-others-are-saying/
*/

/*
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 2.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
*/

// admin page options
function woas_adminmenu() {
	add_options_page('What Others Are Saying', 'What Others Are Saying', 'update_plugins', 'what-others-are-saying', 'woas_options_page');
}
	 
function woas_options_page() {
	if (isset($_POST['woas_update_options'])):
		update_option('woas_filename', $_POST['woas_filename']);
		update_option('woas_waittime', (int)$_POST['woas_waittime']);
		update_option('woas_postCount', (int)$_POST['woas_postCount']);
		update_option('woas_follow', (int)$_POST['woas_follow']);
		update_option('woas_siteLinked', (int)$_POST['woas_siteLinked']);			 
		update_option('woas_display', (int)$_POST['woas_display']);

		?>
        <div id="message" class="updated fade"><p>Options Saved!</p></div>
		<?php
		endif;

		if (get_option('woas_siteLinked')) {
			$woas_siteLinked = 'checked="checked"';
		} else {
		    $woas_siteLinked = '';
		}
		if (get_option('woas_follow')) {
			$woas_follow = 'checked="checked"';
		} else {
		    $woas_follow = '';
		}
			
		$woas_filename = get_option('woas_filename');			
		$woas_waittime = get_option('woas_waittime');
		$woas_postCount = get_option('woas_postCount');
		$woas_display = get_option('woas_display');
		?>
		
	<div class="wrap">
	<h2>What Others Are Saying Options</h2>
    <p>Use this form to configure your plugin options.</p>
	<form id="woas_form" method="post" action="" class="form-table">
     <fieldset><legend>Available Options</legend>
		<div><label for="woas_waittime"><?php _e('Seconds between feed updates'); ?></label>
         <input type="text" size="25" id="woas_waittime" name="woas_waittime" value="<?php echo !empty($woas_waittime) ? $woas_waittime : 3600*3; ?>" /></div>
		<div><label for="woas_postCount"><?php _e('How many posts to display?'); ?></label>
         <input type="text" size="25" id="woas_postCount" name="woas_postCount" value="<?php echo !empty($woas_postCount) ? $woas_postCount : 5; ?>" /></div>
		<div><label for="woas_display"><?php _e('How to display the posts?'); ?></label>
         <select id="woas_display" name="woas_display">
		 	<option value="1"<?php echo $woas_display == 1 ? ' selected="selected"' : ''; ?>>Most Recent Posts</option>
			<option value="2"<?php echo $woas_display == 2 ? ' selected="selected"' : ''; ?>>Random Posts</option>
		 </select></div>
		<div><label for="woas_siteLinked"><?php _e('Link the Site as well?'); ?></label>
				<input type="checkbox" id="woas_siteLinked" name="woas_siteLinked" value="1" <?php echo $woas_siteLinked; ?> /></div>
		<div><label for="woas_follow"><?php _e('Set links as rel="nofollow"?'); ?></label>
				<input type="checkbox" id="woas_follow" name="woas_follow" value="1" <?php echo $woas_follow; ?> /></div>
	</fieldset>
    
	<div class="submit"><input type="submit" name="woas_update_options" value="Save Options"/></div>
     
    </form>
    </div>
<?php		
}	 
	
// CSS for styling options form
function woas_options_style() {
	?>
	<style type="text/css" media="screen">
  		#woas_form legend { display: none; } 
  		#woas_form fieldset { border: none; margin: 0; padding: 0; }
  		#woas_form label { width: 225px; float: left; font-weight:bold; }
  		#woas_form fieldset div { clear: both; margin-top: 5px; background-color: #eaf3fa; padding: 12px; }
	</style>
	<?php
}

add_action('admin_head', 'woas_options_style');
add_action('admin_menu', 'woas_adminmenu');	

register_activation_hook( __FILE__, 'woas_activate' );

// activation function
function woas_activate () {
	global $wpdb;
	
	// this is here as it won't connect if it's already connected, but some people had problems accessing the database
	mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	mysql_select_db(DB_NAME);

	// check to see if the database table exists, if it doesn't then create it
	if ($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."linkposts'") != $wpdb->prefix."linkposts") :
		$sql = "CREATE TABLE ".$wpdb->prefix."linkposts (
	 		id int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  			link_id int(5) UNSIGNED NOT NULL DEFAULT '0',
  			title tinytext NOT NULL,
  			link tinytext NOT NULL,
  			issued varchar(50) NOT NULL default '',
			PRIMARY KEY  (id)
			);";
	
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	endif;
	
	// options probably aren't set either
	add_option('woas_lastCache', mktime()-(3600*24));
	add_option('woas_waittime', 3600*3);
	add_option('woas_display', 1);
	add_option('woas_postCount', 5);
	add_option('woas_siteLinked', 0);
	add_option('woas_follow', 0);
}

// front end code
function what_others_are_saying() {
	global $wpdb;

	// get the options from the options table
	if (get_option('woas_siteLinked')) :
		$siteLinked = TRUE;
	else :
   		$siteLinked = FALSE;
	endif;
			
	$woas_follow = get_option('woas_follow');
	$woas_waittime = get_option('woas_waittime');
	$woas_postCount = get_option('woas_postCount');
	$woas_display = get_option('woas_display');
	$woas_lastCache = get_option('woas_lastCache');

	$waittime = !empty($woas_waittime) ? $woas_waittime : 3600*3;
	$postCount = !empty($woas_postCount) ? $woas_postCount : 5;
	$postDisplay = !empty($woas_display) ? $woas_display : 1;
	$cachetime = !empty($woas_lastCache) ? $woas_lastCache : mktime()-(3600*24);
	$nofollow = $woas_follow == 1 ? TRUE : FALSE;

	// check whether there are posts in the table
	$postExist = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."linkposts");

	define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');

	// check that the cache time exists and make sure that the cache time plus the delay is less than right now
	// also run if there are no posts in the linkpost table
	if ((!empty($cachetime) && $cachetime+$waittime < mktime()) || $postExist == 0) :
   		// load the magpie rss fetch file
	 	// check for the rss file
	 	if (file_exists(ABSPATH . WPINC . '/rss.php')) :
	 		include_once (ABSPATH . WPINC . '/rss.php');
	 	else :
	    	include_once (ABSPATH . WPINC . '/rss-functions.php');
	 	endif;
	 
	 	// select all of the links and their RSS links out of the bookmark table for those links that have their RSS set
	 	$sql = $wpdb->get_results("SELECT link_id, link_rss, link_name FROM ".$wpdb->links." WHERE link_rss != '' GROUP BY link_rss");
	 
	 	if (@count($sql)) :
	 		// empty out the temporary linkposts table to keep things clear
	 		$wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."linkposts");
	 
	 		// this just sets the months as the FeedBurner feed stores them as short names instead of numbers
	 		$months = array("Jan" => "01", "Feb" => "02", "Mar" => "03", "Apr" => "04", "May" => "05", "Jun" => "06", "Jul" => "07", "Aug" => "08", "Sep" => "09", "Oct" => "10", "Nov" => "11", "Dec" => "12");

	 		// run through all the sites and get their last feed
	 		foreach ($sql as $site) :
				@$rss = fetch_rss($site->link_rss);

				// check that the $rss array got some data!
				if (is_array($rss->items)) :
			    	foreach ($rss->items as $item) :
						$title = str_replace(' & ', ' &amp; ', $item['title']);
						$url   = $item['guid'];
							
						// not a feedburner feed so try getting the link instead
						if (empty($url) || (substr($url, 0, 4) != "http")) $url = $item['link'];
							
						// if the feed is a Feedburner RSS feed then the pubdate should exist
						if (!empty($item['pubdate'])) :
							$issued = $item['pubdate'];
							
							$issued = substr($issued, 5);
							$day = substr($issued, 0, 2);
							$month = substr($issued, 3, 3);
							$year = substr($issued, 7, 4);
							$ptime = substr($issued, 12, 8);
								 
							$month = $months[$month];
								 
							$issued = $year."-".$month."-".$day." ".$ptime;
						else :
							// if the feed isn't RSS then one of the following may work!
							if (!empty($item['published'])) :
								$issued = $item['published'];
							elseif (!empty($item['dc']['date'])) :
							    $issued = $item['dc']['date'];
							endif;
								 
							$issdate = substr($issued, 0, 10);
							$isstime = substr($issued, 11, 8);
								 
							$issued = $issdate." ".$isstime; 
						endif;
							
						// if the URL is empty then there's no point in adding it.
						if (!empty($url)) :
							// insert the entry into the database table
							$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."linkposts (link_id, title, link, issued) VALUES (%d, %s, %s, %s)", $site->link_id, $title, $url, $issued));
						endif;
							
						// break after one result as we only want the last one. Remove this if you just want the 5 most recent from any blog.
						break;
			      	endforeach;
				endif;
			endforeach;

			// update the cache time
			update_option('woas_lastCache', mktime());
		endif;
	endif;

	// Retrieve the most recent X posts
	$orderby = $postDisplay == 2 ? "RAND()" : "issued DESC";
	$sql = $wpdb->get_results("SELECT link, title, link_name, link_url FROM ".$wpdb->prefix."links, ".$wpdb->prefix."linkposts WHERE ".$wpdb->prefix."links.link_id = ".$wpdb->prefix."linkposts.link_id GROUP BY link ORDER BY ".$orderby." LIMIT ".$postCount);

	/* This is the output to the page so change this if you want it displaying different. */
	foreach ($sql AS $site) :
		echo "<li><a href='".$site->link."'".($nofollow ? ' rel="nofollow"' : '').">".$site->title."</a><br />\n";
			
		if ($siteLinked) :
			 echo "<a href='".$site->link_url."'".($nofollow ? ' rel="nofollow"' : '').">".$site->link_name."</a></li>\n";
		else :
		   echo $site->link_name."</li>\n";
		endif;
	endforeach;
}

// creates widget//
function wp_widget_woas($args) {
	extract($args);
	$options = get_option('widget_woas');
	$title = $options['title'];
	if (empty($title))
		$title = 'What Others Are Saying';
		echo $before_widget;
		$title ? print($before_title . $title . $after_title) : null;

		if (function_exists('what_others_are_saying')) :
			echo "<ul>\n";	
			what_others_are_saying();
			echo "</ul>\n";
		endif;
		
		echo $after_widget;
}

function wp_widget_woas_control() {
	$options = $newoptions = get_option('widget_woas');
	if ($_POST["woas_submit"]) {
		$newoptions['title'] = strip_tags(stripslashes($_POST["woas_title"]));
	}
	if ($options != $newoptions) {
		$options = $newoptions;
		update_option('widget_woas', $options);
	}
	$title = attribute_escape($options['title']);
?>
	<p><label for="woas_title"><?php _e('Title:'); ?> <input id="woas_title" name="woas_title" type="text" size="40" value="<?php echo $title; ?>" /></label></p>
	<input type="hidden" id="woas_submit" name="woas_submit" value="1" />
<?php
}

function wp_widget_woas_register() {
	register_sidebar_widget('What Others Are Saying', 'wp_widget_woas', 'widget_woas');
	register_widget_control('What Others Are Saying','wp_widget_woas_control', 100, 300);
}

add_action('plugins_loaded','wp_widget_woas_register');

?>