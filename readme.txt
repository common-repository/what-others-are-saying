=== What Others Are Saying ===
Contributors: sarahg111
Donate link: http://www.stuffbysarah.net/blog/wordpress-plugins/#donate
Tags: blogroll, bookmarks, links, sidebar, rss
Requires at least: 2.0
Tested up to: 3.2.1
Stable tag: 1.7

Use the RSS field in your Blogroll and display the most recent post from sites that you link to.

== Description ==

This plugin allows you to display the most recent post of your favourite blogs somewhere on your site. 

It utilises the RSS link field from the blogroll for each of your links. If the RSS link exists it attempts to get the last post from it. 

It then displays the most recent X number of posts depending on your settings. Each blog can have one post in the list.

Why have a boring list of links when you can display each person's last post on that list?

== Installation ==

Installation Instructions:

1. Download the plugin and unzip it.
2. Put the 'other-posts.php' file into your wp-content/plugins/ directory.
3. Go to the Plugins page in your WordPress Administration area and click 'Activate' next to What Others Are Saying.
4. Go to the Options > What Others Say and configure your options.
5. Go to Manage - Links and either add RSS feed links to your existing links or add new links with their RSS feed (the RSS feed link goes into the Advanced section on the Manage - Links page).
6. For a non widget sidebar use the function below to display a list of results (see the plugin site for more code hints)

if (function_exists('what_others_are_saying')) what_others_are_saying();

7. For widget sidebars just go to your Widgets page in your admin and add the widget.

== Frequently Asked Questions ==

= Where are the RSS links added? =

Under your Blogroll, when you add a new link (or edit an existing one), look lower down the page under the advanced section. Here you can add RSS feed links.

= Not all items are appearing correctly =

Unfortunately not every site or software follows a standard RSS format, so I'm slowly adding other 'methods' in. Please let me know which feed URL isn't work and I'll get support for that added.

= Apostrophes in the post title display funny =

I know, I'm working on that.


== Screenshots ==

1. The admin settings panel
2. The RSS input box on the Manage - Links page

== Support ==

Support is provided at http://www.stuffbysarah.net/blog/wordpress-plugins/what-others-are-saying/