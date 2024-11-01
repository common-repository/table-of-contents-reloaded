=== Table of Contents Reloaded ===
Contributors: yehudah
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=yehuda@myinbox.in&item_name=Donation+for+PostSMTP
Tags: table of contents, indexes, toc, sitemap, cms, options, list, page listing, category listing
Requires at least: 3.2
Tested up to: 5.3
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A powerful yet user friendly plugin that automatically creates a table of contents. Can also output a sitemap listing all pages and categories.


== Description ==

A powerful yet user friendly plugin that automatically creates a context specific index or table of contents (TOC) for long pages (and custom post types).  More than just a table of contents plugin, this plugin can also output a sitemap listing pages and/or categories across your entire site.

Built from the ground up and with Wikipedia in mind, the table of contents by default appears before the first heading on a page.  This allows the author to insert lead-in content that may summarise or introduce the rest of the page.  It also uses a unique numbering scheme that doesn't get lost through CSS differences across themes.

This plugin is a great companion for content rich sites such as content management system oriented configurations.  That said, bloggers also have the same benefits when writing long structured articles.  [Discover how Google](http://dublue.com/2012/05/12/another-benefit-to-structure-your-web-pages/) uses this index to provide 'Jump To' links to your content.

Includes an administration options panel where you can customise settings like display position, define the minimum number of headings before an index is displayed, other appearance, and more.  For power users, expand the advanced options to further tweak its behaviour - eg: exclude undesired heading levels like h5 and h6 from being included; disable the output of the included CSS file; adjust the top offset and more.  Using shortcodes, you can override default behaviour such as special exclusions on a specific page or even to hide the table of contents altogether.

Prefer to include the index in the sidebar?  Go to Appearance > Widgets and drag the TOC+ to your desired sidebar and position.

Custom post types are supported, however, auto insertion works only when the_content() has been used by the custom post type.  Each post type will appear in the options panel, so enable the ones you want.

= Credit =
Table Of Contents Reloaded is a fork of Table Of Contents Plus developed by Michael Tran.
It was not maintained in the last four years so I continue his development here.

== Screenshots ==

1. An example of the table of contents, positioned at the top, right aligned, and a width of 275px
2. An example of the sitemap_pages shortcode
3. An example of the sitemap_posts shortcode
4. The options panel found in Settings > TOC+
5. Some advanced options
6. The sitemap tab


== Installation ==

The normal plugin install process applies, that is search for `table of contents reloaded` from your plugin screen or via the manual method:

1. Upload the `table-of-contents-plus` folder into your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

That's it!  The table of contents will appear on pages with at least four or more headings.

You can change the default settings and more under Settings > TOC+

This plugin requires PHP 5.


== Shortcodes ==
The plugin was designed to be as seamless and painfree as possible and did not require you to insert a shortcode for operation.  However, using the shortcode allows you to fully control the position of the table of contents within your page.  The following shortcodes are available with this plugin.

When attributes are left out for the shortcodes below, they will fallback to the settings you defined under Settings > TOC+.  The following are detailed in the help tab.

= [toc] =
Lets you generate the table of contents at the preferred position.  Useful for sites that only require a TOC on a small handful of pages.  Supports the following attributes:
* "label": text, title of the table of contents
* "no_label": true/false, shows or hides the title
* "wrapping": text, either "left" or "right"
* "heading_levels": numbers, this lets you select the heading levels you want included in the table of contents.  Separate multiple levels with a comma.  Example: include headings 3, 4 and 5 but exclude the others with `heading_levels="3,4,5"`
* "class": text, enter CSS classes to be added to the container. Separate multiple classes with a space.

= [no_toc] =
Allows you to disable the table of contents for the current post, page, or custom post type.

= [sitemap] =
Produces a listing of all pages and categories for your site. You can use this on any post, page or even in a text widget.  Note that this will not include an index of posts so use sitemap_posts if you need this listing.

= [sitemap_pages] =
Lets you print out a listing of only pages. The following attributes are accepted:
* "heading": number between 1 and 6, defines which html heading to use
* "label": text, title of the list
* "no_label": true/false, shows or hides the list heading
* "exclude": IDs of the pages or categories you wish to exclude
* "exclude_tree": ID of the page or category you wish to exclude including its all descendants

= [sitemap_categories] =
Same as `[sitemap_pages]` but for categories.

= [sitemap_posts] =
This lets you print out an index of all published posts on your site.  By default, posts are listed in alphabetical order grouped by their first letters.  The following attributes are accepted:
* "order": text, either ASC or DESC
* "orderby": text, popular options include "title", "date", "ID", and "rand". See [WP_Query](https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters) for a list.
* "separate": true/false (defaults to true), does not separate the lists by first letter when set to false.
Use the following CSS classes to customise the appearance of your listing:
* toc_sitemap_posts_section
* toc_sitemap_posts_letter
* toc_sitemap_posts_list


== I love it, how can I show my appreciation? ==
If you have been impressed with this plugin and would like to somehow show some appreciation, rather than send a donation my way, please donate to your charity of choice.

I will never ask for any form of reward or compensation.  Helping others achieve their goals is satisfying for me :)


== Changelog ==
= 1.0 =
* First world release (functional & feature packed)


== Frequently Asked Questions ==

Check out the FAQs / Scenarios at [https://wpplugins.net/table-of-contents-plus/#Scenarios_FAQs](https://wpplugins.net/table-of-contents-plus/#Scenarios_FAQs)


== Upgrade Notice ==

Update folder with the latest files.  All previous options will be saved.