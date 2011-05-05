=== MoviesForMyBlog ===
Contributors: Jason Deatherage
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=U6XN67BLCPNZE
Tags: moviesformyblog, netflix, movies, entertainment, videos
Requires at least: 2.9.2
Tested up to: 3.1.2
Stable tag: 1.2

Displays the most recent instant play movies and/or DVD's and Blu-Ray's shipped out on the netflix account.


== Description ==

This is a plugin for your blog to display the last instant played movies and/or DVD's and Blu-Ray's that have been shipped on your account. 

It utilizes the netflix API and webservices to tranfer the information and Jquery to display it on your blog.



== Installation ==

This section describes how to install the plugin and get it working.


1. Upload `moviesformyblog` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add the plugin to the sidebar under the widget options through the `Appearance` menu and drag it to the location of your choice.
1. Set the title of the Plugin to what you would like to call it.  Preferably MoviesForMyBlog.
1. Go to the MFMB Settings page under the `Settings` menu.
1. Click the `Get Login Tokens` link to get your initial Login Tokens.
1. Click the `Authorize Tokens` link to take you to a netflix authorization page to allow this plugin to authorize the OAuth tokens to request an ID to communicate with your Netflix account. After logging into netflix it will redirect you to the homepage of your WordPress Blog.
1. Go back to the site admin section and navigate to the MFMB Settings page under the `Settings` menu.
1. Click the `Get Access Tokens` link to get your final authorization tokens for secure authentication to netflix without your username and password.
1. Select the options to display and type a quantity to retrieve.  I recommend not going over 50 for each option selected because it doesn't support more than that for this implementation.
1. Select in whole numbers how often you want the data to be refreshed.  I recommend every 1 to 2 hours.  Netflix does limit the amount of requests your account can make on a daily basis.  This also helps lower the load on my servers to request data to frequently.
1. Last click the `Save Changes` button to make your first secure authenticated request.  
1. The CSS of your theme out of the box might make the widget look different.  You might have to make a few css changes to the plugin in regards to the width and height of the sidebar you added it to.  I have put those settings at the top of the css file.  Feel free to customize as needed.
1. You should now have the plugin initialized on your WordPress Blog.


== Frequently Asked Questions ==

= How does this widget authenticate to Netflix? =

Netflix API's utilize [OAuth](http://oauth.net/ "OAuth") so I built an intermediary to provide that secure connection to Netflix so many people could utilize this nifty widget.



== Changelog ==
= 1.2 =
* Updated the webservice url's

= 1.1 =
* Fixed the headers already sent error.
* Changed the default css to match the default theme in version 3.0.

= 1.0 =
* Added the functionality to be able to view both Instant Play and DVD on the blog.
* Displays how it was rated or the predicted rating.
* Allows you to choose how many rentals will be displayed.
* Allows you to choose how often the data is refreshed.



== Upgrade Notice ==
*NA

== Screenshots ==
*NA