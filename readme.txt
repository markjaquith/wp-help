=== WP Help ===
Contributors: markjaquith
Donate link: http://txfx.net/wordpress-plugins/donate
Tags: help, documentation, client sites, clients, docs
Requires at least: 3.1
Tested up to: 3.2
Stable tag: 0.1

Administrators can create detailed, hierarchical documentation for the site's authors and editors, viewable in the WordPress admin.

== Description ==

Administrators can create detailed, hierarchical documentation for the site's authors and editors, viewable in the WordPress admin. Powered by Custom Post Types, you get all the power of WordPress to create, edit, and arrange your documentation. Perfect for customized client sites. Never send another "here's how to use your site" e-mail again!

== Installation ==

1. Upload the `wp-help` folder to your `/wp-content/plugins/` directory

2. Activate the "WP Help" plugin in your WordPress administration interface

3. Go to Dashboard &rarr; Publishing Help to get started

== Frequently Asked Questions ==

= Who can view the help documents? =

Anyone who can publish posts. So by default, Authors, Editors, Administrators.

= Who can edit the help documents? =

Anyone who can manage WordPress options. So by default, Administrators.

= How do I reorder the documents? =

Just like you'd reorder pages. Change the `Order` setting for the page, in the `Attributes` meta box. To make something be first, give it a large negative number like `-100`.

= How do I link to another help page from a help page? =

Use WordPress' internal linking feature. When launched from a help document, it will only search for other help documents.

= How do I change the default help document? =

Edit the help document you want to be the default. Find the "WP Help" meta box. Check the checkbox, and save. This will now be the default document

== Screenshots ==

1. The Publishing Help screen, which lists and displays available help documents.

== Changelog ==

= 0.1 =
* Initial version
