=== IntenseDebate XML Importer (Blogger to Wordpress) ===
Contributors: Swashata, GautamGupta
Donate link: http://www.intechgrity.com/about/buy-us-some-beer/
Tags: intense debate, id import, intense debate comment import, blogger to wordpress comment import
Requires at least: 2.8
Tested up to: 3.0.1
Stable tag: trunk

Import all Comments from Blogger Intense Debate account to Wordpress

== Description ==

Have you ever been on Blogger and used Intense Debate as your Blogger Commenting system? Now when you are moving from WordPress to Blogger you might be thinking of losing all your comments, right?

This is where IntenseDebate XML Importer saves you! Although Intense Debate had a plugin to do this they have disabled the plugin due to system maintenance...

My bad luck was that I shifted my Blogger blog to WP at the time when ID disabled their existing plugin! But I did not want to lose my comments...

So I looked into their plugin code and their export XML file and wrote this plugin to import all the comments from the backup XML to my WP Blog! Have a look at the Installation page to get more idea!

== Installation ==

###Uploading The Plugin###

Extract all files from the ZIP file, **making sure to keep the file/folder structure intact**, and then upload it to `/wp-content/plugins/`.

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

*Actually this instruction portion is copied from another WP Plugin :P*

###Plugin Activation###

Go to the admin area of your WordPress install and click on the "Plugins" menu. Click on "Activate" for the "IntenseDebate XML Importer" plugin.

###Plugin Usage###

This is pretty much straight forward...

*   Go to your [Intense Debate](http://www.intensedebate.com) Dashboard and Export the XML file for the blog you want to process
*   Now go to the Plugins Settings page from the *Settings tab* and browse and upload the XML file
*   Wait for sometime until it imports all the comments
*   Once done check your blog... If there remains some error then you have to fix that manually! In future release we may add some automated option
*   Finally disabled the Plugin! Hey... You need it only once ;)


== Frequently Asked Questions ==

= From where can I download the XML file? =
From [Intense Debate](http://intensedebate.com/userDash) Dashboard navigate to your Site. Then from the sitebar Click on **XML Export**. From there save the generated XML file.

= Can I use Intense Debate as my WP commenting system as well? =
Of course you can! But before installing ID to your WP blog, make sure to run this plugin once! Else the comments for older posts won't appear!

= How can I move my Blogger Blog to WP without changing the Permalink structure? =
Quite offtopic! But still... [HERE](http://devilsworkshop.org/moving-from-blogger-to-wordpress-maintaining-permalinks-traffic-seo/) is the perfect guide for you! Even I have followed the same.

= So if I maintain the Permalinks, will the existing Intense Debate Account work? =
Quite intelligent question! Even I thought the same. But the reality is it won't work! When you install ID comment system from Blogger to WP then the comments according to the Permalink, remains stored inside ID database, not on WP database. So the comments would come on widgets by Intense Debate. But won't be shown on the actual post pages. So, you should import the comments, then use delete the existing ID site, then reinstall to make it fully compatible!

== Screenshots ==

You can check our Blog... [InTechgrity](http://www.intechgrity.com/?p=267)

== ChangeLog ==

= Version 1.0.1 =

* Initial release!

== Upgrade Notice ==
= 1.0.1 =
The initial release! That's why!!!! *PS: I wouldn have included this if WP validator had not notified me that I am missing this section!*