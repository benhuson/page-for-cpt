# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [0.6] - 2018-04-25

### Added
- Add "Edit Page" toolbar item when viewing a post type archive with associated page.
- Add "{Post Type} Page" label to archive pages in admin pages list view.

### Fixed
- Get the correct post type on archive pages, even if there are no posts.
- Automatically flush rewrite rules after settings are changed.

## [0.5] - 2017-04-27

### Fixed
- Add the wp-editor back into the page for posts after it was removed in WordPress 4.2.2.

## [0.4] - 2016-09-15

### Added
- Add and use `get_public_post_types()` method.
- Add and use `get_page_for_cpt_option()` method.
- Add support for `Page_For_CPT::get_the_archive_title()` and `Page_For_CPT::get_the_archive_description()` on post archives.

### Changed
- Init all hooks after plugins are loaded.
- Remove `TEXTDOMAIN` as language domains must be a string not a variable.
- Deprecate and remove plugin directory and url path constants and remove with class methods.
- Use `register_post_type_args` hook if WordPress 4.4+
- Don't filter built-in post type args.
- Only load admin functionality in admin.

## [0.3] - 2015-02-26

### Added
- Filter archive title and description to show page title and content.
- When settings are changed, flush rewrite rules.
- Style admin fields in a table.
- Use PAGE_FOR_CPT_TEXTDOMAIN constant.

### Changed
- Do not require the `page_for_cpt_slug` filter. Automaticatically apply to registered post types.
- Change default admin field option to "Not Set".

## [0.2] - 2015-01-16

### Added
- Added `Page_For_CPT::get_post_type_for_page()` method.
- Added `Page_For_CPT::get_page_for_post_type()` method.

## [0.1] - 2014-09-30

### Added
- First public release.
- Add `page_for_cpt_use_page_body_classes` filter to use page body classes instead of archive body classes.

[Unreleased]: https://github.com/benhuson/page-for-cpt/compare/0.6...HEAD
[0.6]: https://github.com/benhuson/page-for-cpt/compare/0.5...0.6
[0.5]: https://github.com/benhuson/page-for-cpt/compare/0.4...0.5
[0.4]: https://github.com/benhuson/page-for-cpt/compare/0.3...0.4
[0.3]: https://github.com/benhuson/page-for-cpt/compare/0.2...0.3
[0.2]: https://github.com/benhuson/page-for-cpt/compare/0.1...0.2
[0.1]: https://github.com/benhuson/page-for-cpt/releases/tag/0.1
