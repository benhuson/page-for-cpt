Page for Custom Post Type
=========================

Specify a page to use for the base URL of a custom post type via your WordPress Reading Settings admin page.
Works in a similar way to how you can set a page for posts in the WordPress reading settings.

On a post type archive page, if your theme's archive template uses the following recommended teplate tags then your page content will be output in place:

 - `the_archive_title()` : The page title
 - `the_archive_description()` : The page content

This plugin may not work with post types that specify their own custom permalink structure.

Installation
------------

1. Upload the `page-for-cpt` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit `Admin > Settings > Reading` in the WordPress admin to set which page to use for each post type.


Changelog
---------

View a list of all plugin changes in [CHANGELOG.md].

[CHANGELOG.md]: https://github.com/benhuson/page-for-cpt/blob/master/CHANGELOG.md
