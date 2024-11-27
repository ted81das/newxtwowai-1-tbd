=== ZionBuilder PRO Plugin ===
Contributors: zionbuilder
Tags: page builder, editor, visual editor, design, website builder, front-end builder
Requires at least: 6.0.0
Tested up to: 6.6.2
Stable tag: 3.6.12
Requires PHP: 7.0

ZionBuilder PRO is a truly innovative plugin that comes to complete the free version of Zion Builder with many awesome features.

== Description ==
Built and designed by [Hogash team](https://hogash.com), the creator of famous WordPress theme [Kallyas](https://kallyas.net/), this plugin brings in addition to its free version, tools which are meant to help users build their WordPress websites in no-time.

Below are listed the core competencies

Elements and templates at your fingertip
----------------------------------------
Add elements and templates right where you need them from the page builder "Add Elements Popup"

PRO elements
----------------
* Countdown
* Social Share
* Search
* Pro Tabs ( replaces normal tabs element and adds ability to use page builder elements as content )
* Pro Accordions ( replaces normal accordions element and adds ability to use page builder elements as content )

Key Features
-----------------------------------
* Zion Library Pro Blocks
* Header Builder ( coming soon )
* Footer Builder ( coming soon )
* Transitions
* Transform
* Upload Custom icons
* Upload Custom fonts
* Adobe Fonts
* Dynamic Content
* Global Colors
* Global Gradients
* Users Permissions


Advanced features
-----------------------------------
* Post revisions
* Role Manager
* Regenerate CSS
* Replace URL
* Custom CSS
* Custom javaScript
* Renaming elements
* Element’s visibility
* Custom HTML element
* Columns sizes and offsets
* Extendable options



Documentation and Support
==========================
For documentation and tutorials visit our [Knowledge Base](https://zionbuilder.io/help-center/).


== Frequently Asked Questions ==

= What is Zion Page Builder Pro version ? =

With Zion Builder Pro you’ll be able to pick from a much larger library of options, and can further customize your site by editing almost every part of it using the theme builder (including headers, footers, archive, and single post pages, and more).

== Screenshots ==

== Changelog ==
= 3.6.9 TBD =
Added: Ability to set the "inner content" element wrapper HTML tag
Fixed: Theme builder doesn't apply the correct template in certain conditions

= 3.6.8 2023-23-07 =
Added: Ability to set custom css to css classes
Added: Element display condition to show/hide the element if the active query has additional pages
Added: option for the repeater to query based on the post author
Improved: post terms repeater field. This now shows all the taxonomies instead of just the ones related to the current post.
Improved: Repeaters performance in both the editor and rendered page
Improved: Allow wrapping text with span elements for text and heading
Fixed: element display condition "Number of posts" not working properly
Fixed: URL parameter element condition not working in certain conditions
Fixed active item not highlighted properly inside tree view if the element is also a repeater provider/consumer
Fixed nested repeaters doesn't render the proper dynamic data
Fixed mobile menu flickers on page load
Fixed background image blend mode doesn't work with gradient background inside a repeater
Fixed php notice related to dynamic data
Fixed mega menu labels not working if they contain backslashes
Fixed mega menu labels don't work with Cyrillic alphabet
Fixed mini cart doesn't display contents with latest WooCommerce version

= 3.6.7 2023-12-04 =
Added: Options to select the desired slider builder slide effect ( slide, fade, cube, etc )
Added: Option to close the mobile menu when a menu item is clicked
Fixed: Theme builder archive conditions not showing all taxonomy terms
Fixed: Media modal appears when using the dynamic data background options
Fixed: Dynamic data options not showing the first time after clicking on the edit icon in certain conditions
Improved: Fixed typo in "zionbuilderpro/conditions/exluded_post_types" filter

= 3.6.6 2023-12-04 =
Added: Offset option for the query builder ( https://feedback.zionbuilder.io/roadmap/adding-offset-in-query-builder )
Added: Tax query options for repeater provider
Added: Meta query options for repeater provider
Added: Dynamic data fields for all elements "Advanced options"
Added: Option to ignore sticky posts in Repeater query
Fixed: Theme builder display conditions popup placed behind the conditions modal

= 3.6.5 2023-14-03 =
Added: Allow MetaBox dynamic data for Link options
Added: Ability to use Dynamic data for element custom attributes
Added: Dynamic data option for all supported options inside the builder ( https://feedback.zionbuilder.io/roadmap/please-add-dynamic-filed-to-countdown-timer-date )
Added: Column gap and row gap options to the container element ( https://feedback.zionbuilder.io/roadmap/add-flex-gap-to-sections-and-containers )
Added: Column gap option - Style options > Flexbox container options
Added: Row gap option - Style options > Flexbox container options
Fixed: Global color added twice inside the admin panel in certain conditions

= 3.6.4 2023-17-02 =
Fixed: Whitelabel admin page not opening
Fixed: Buy PRO message appears inside the admin panel for global colors

= 3.6.3 2023-13-02 =
Added: Ability to specify the expected result from the function return value display condition
Added: CSS aspect-ration property inside display options panel ( https://feedback.zionbuilder.io/b/5v8jzj0g/feature-requests/aspect-ratio-in-editor )
Improved: Custom font CSS generation ( https://feedback.zionbuilder.io/b/5v8jzj0g/feature-requests/custom-fonts-in-pro-version-generating-broken-css )
Added: Tooltips for function return value display conditions. These tooltips will show information about the edited field.
Fixed: Dynamic link doesn't show the full list of link sources
Improved: Added placeholder texts to function return value advanced display condition option fields
Improved: Made function return value display condition work with functions that don't return "true"/"false"
Fixed: Fatal error can appear when the function return value display condition is not used properly
Fixed: Dynamic data doesn't show in editor mode for the background image
Fixed: ACF dynamic field cannot be selected
Fixed: Cannot properly translate string using Loco Translate
Fixed: Icon element CSS not generating properly inside a repeater
Fixed: Global colors cannot be selected even if the PRO version is active

= 3.6.2 2023-30-01 =
Fixed: Icon list text doesn't work with dynamic data ( https://feedback.zionbuilder.io/b/5v8jzj0g/feature-requests/i-want-pencil-icon-on-every-icon-list-options-for-single-postdynamic-data )
Fixed: Cannot select specific pages inside theme builder rules

= 3.6.1 2023-28-01 =
Fixed: Typo in modal element options
Fixed: Dynamic data not working for image options
Fixed: text appearing when deleting a theme builder component

= 3.6.0 2023-27-01 =
Added: Display condition: The current user is post author
Fixed: PHP notice in error log appearing in certain conditions related to dynamic fields
Fixed: Menu element "hide on breakpoint" option not working in editor mode
Fixed: Accordion menu not working in editor mode in certain conditions
Improved: Updated all code to Typescript and Vue setup
Improved: Moved to native WP i18n translation methods
Improved: Prevent infinite loops when the same menu item is added inside a mega menu
Improved: Change the active tab when clicking on a tab item from the tree view panel

= 3.5.0 2022-15-11 =
Added: icons and specific colors for repeater providers and consumers in element toolbox and tree view panels
Added: WooCommerce thank you element
Added: WooCommerce store notices element
Added: WooCommerce mini cart element
Added: Ability to style the thank you page with theme builder
Added: Additional style options for WooCommerce Product reviews form element
Added: Additional style options for WooCommerce cart product element
Improved: Compatibility between custom code element and some server configuration
Added: Sale price style options for Product price element
Improvement: Disable slider builder auto play inside editor
Fixed: Mega menu display center not working
Fixed: Pagination previous and next links are reversed
Fixed: Mobile menu breakpoint trigger not working in editor mode
Fixed: Background image not set for single page templates if dynamic image is used

= 3.4.2 2022-21-10 =
FIXED: Custom CSS not applying to page
IMPROVEMENT: Updated links that pointed to Github issues to feedback.zionbuilder.io
FIXED: dynamic gradients not rendering in editor

= 3.4.1 2022-18-10 =
Fixed: theme builder not rendering templates in certain conditions

= 3.4.0 2022-18-10 =
Added: Ability to select image size for dynamic images
Added: Additional permissions for users and user roles
Added: Evergreen countdown type
Added: Ability to set an action after the countdown element reaches it's designated date
Added: "Sidebar has widgets" display condition
Added: Ability to edit theme builder templates from admin bar
Improvement: Removed jQuery from menu element
Improvement: Removed jQuery from countdown element
Improvement: Allow advanced conditions select field to filter the conditions
Improvement: Added friendly admin message for errors generated by the PHP code placed inside the code element
Fixed: Repeater data/Dynamic content is not rendered properly in the editor
Fixed: Menu position doesn't work in certain conditions
Fixed: Menu style accordion not working
Fixed: Dynamic data not working properly in editor mode
Fixed: Mega menu styles not loading properly in editor mode

= 3.3.0 2022-09-02 =
Added: Ability to change or disable the getting started video that appears for new users
Improvement: Allow text selection/editing inside slider builder slides
Improvement: You can now see tabs element content in tree view

= 3.2.1 2022-05-09 =
Improvement: Removed debug code

= 3.2.0 2022-05-09 =
Added: Responsive options for "slides to show" and "slides to scroll" options for slider builder element
Improvement: Removed jQuery dependency for slider builder
Improvement: Disabled infinite scroll for slider builder element in edit mode. It was causing several problems
Improvement: [Social Share element] Removed jQuery dependency
Improvement: [Modal Element] Removed jQuery dependency
Fixed: Custom CSS class not applying for menu element
Fixed: Typo in code
Fixed: Post excerpt dynamic data not showing custom excerpt text
Fixed: Template name is not preserved when reordering templates

= 3.1.0 2022-04-07 =
Added: MetaBox integration for dynamic fields
Added: Ability to specify the post excerpt length for dynamic field
Added: WooCommerce cart totals element
Added: WooCommerce cart products element
Added: WooCommerce cart cross sells element
Added: WordPress conditionals as element display conditions
Added: WooCommerce customer total orders as element display condition
Added: WooCommerce customer total spent as element display condition
Added: WooCommerce cart as element display condition
Added: WooCommerce products from cart as element display condition
Added: WooCommerce cart total value as element display condition
Added: WooCommerce product info as element display condition
Added: Browser info as element display condition
Added: Cookie as element display condition
Added: URL variable as element display condition
Added: Operating system as element display condition
Added: Referrer as element display condition
Added: Function return value as element display condition
Added: User login status as the element display condition
Improvement: Removed jQuery dependency for slider builder element
Improvement: Allow custom breakpoint values to work for slider builder > slides to show

= 3.0.0 2022-03-14 =
Added: Advanced Element conditions - activate them from element options > advanced tab > Element Visibility
Added: Post comments element
Added: Confirmation popup that appears when closing the theme builder without saving
Added: Ability to share zion builder library with different websites
Added: backface-visibility to display options
Added: Additional options to style the search form element
Added: Flex gap option to style options
Added: Flex gap to container options
Fixed: Element toolbox appears above modal content in editor mode
Fixed: Pagination not working on homepage

= 2.7.4 2021-08-18 =
* Added additional info for licensing activation error
* Fixed API key cannot be activated due to SSL error
* Improvement: Element custom css is now copied when copying element styles
* [Accesibility] Made Menu element accessible by keyboard
* [Accesibility] Added aria label to search form element
* Fixed global colors not working inside custom css classes
* Fixed page custom css not applying
* Added additional order by options for Query Builder ( name, type, date, post modified, parent, random and comment count )
* Fixed PHP notice appearing when using Dynamic data ACF integration in certain conditions
* Fixed WooCommerce product ratings tab not rendering properly in edit mode
* Fixed WooCommerce images element not rendering properly in edit mode
* Fixed several WooCommerce elements not working properly in editor mode


= 2.7.2 2021-08-18 =
* Allow automatic updates even if the requirements are not met

== Changelog ==
= 2.7.1 2021-07-22 =
* Set minimum Free plugin version required

= 2.7.0 2021-07-22 =
* Added ACF integration for dynamic content fields
* Added ACF reoeater as a repeater provider source
* Added dynamic data repeater field
* Improved Post custom field inside a repeater. It will now populate the options based on the query and post with the ability to manually add a post custom field as an option
* Improvement: Keep dynamic data buttons visible even after selecting a dynamic data tag
* Improvement: Allow the user to add an additional post custom field in case the desired field is not automatically populated
* Fixed cannot edit theme builder components
* Fixed 404 errors on the Theme builder components edit page.
* Fixed color picker not working on theme builder edit screen
* Fixed dynamic background image not working for repeated elements
* Fixed White label image too big in admin menu

== Changelog ==
= 2.6.1 2021-06-24 =
* Fixed Mega menu doesn't show the display tab properly
* Fixed cannot select post terms in repeater

= 2.6.0 2021-06-22 =
* Added option to select the page to use for "post/page" dynamic field
* Fixed featured image dynamic data not working as the background image in certain conditions
* Fixed input shape divider value cannot be deleted

= 2.5.0 2021-06-15 =
* Improved: The theme builder can automatically set the dynamic data source based on the template conditions
* Added WP filter so other plugins can exclude certain post types from the theme builder conditions
* Added option for the repeater provider to use the current page query
* Added responsive optio for slider builder slides to show option
* Added compatibility with SEO press plugin
* Added compatibility with Facet WP plugin
* Added order by and order options for the post terms dynamic data option
* Added "Function return value" option to all dynamic data types ( images, text, links )
* Improved Dynamic data source option by adding additional sources and new design
* Excluded Zion Builder templates from the theme builder post types
* Fixed typo in slider builder option title
* Fixed mega menu responsive button doesn't open the menu in certain conditions
* Fixed mega menu not opening submenu links in certain conditions
* Fixed theme builder edit component not working in certain conditions
* Fixed dynamic data options popup can exit the browser window

= 2.4.0 2021-05-14 =
* Added mega menu system
* Added menu element
* Fixed dynamic color not working properly in certain conditions

= 2.3.0 2021-04-27 =
* Fixed theme builder generates unused CSS
* Fixed Free plugin install link not working

= 2.2.0 2021-04-12 =
* Added style options for modal close button
* Added CSS Object Fit options to display options
* Added WooCommerce product tabs element
* Added ability to set a template for single post types that belong to a specific taxonomy
* Added inner content element
* Added error message that appears when a license key is not valid ( invaid or expired )
* Added: In white label options you can now change the plugin slug ( the slug that appears in various admin pages )
* Added additional style options for various WooCommerce elements
* Added style options for WooCommerce sale badge
* Improvement: Removed outline from slider builder navigation buttons
* Improvement: Elements generated from server are now Automatically refreshed when the current post id is changed from page options
* Improvement: Slider builder now has 100% width by default
* Improved: All elements that show data for a specific post now automatically refresh when chaning the post
* Improved theme builder modal UI
* Improved: The pagination element will not appear if there are no pages to show
* Removed PRO label for various pro elements
* Fixed Slider builder slides to show not chaning live in edit mode
* Fixed dynamic data not showing correct value in edit mode in certain conditions
* Fixed dynamic content not showing featured image in certain conditions
* Fixed dynßamic content image not working for background image in certain conditions
* Fixed autoplay option for Slider builder not working in certain conditions
* Fixed White label plugin name change breaks theme builder admin page
* Various code cleanup and fixes

= 2.1.0 2021-03-16 =
* Feature: Added slider builder element
* Feature: Added modal/popup builder
* Feature: Added WooCommerce loop add to cart element
* Fixed: Various small fixes and improvements

= 2.0.0 2021-02-26 =
* Feature: Theme builder ( header and footer builder )
* Feature: Added invert filter to style options
* Feature: Added ability to add custom attributes to elements
* Feature: Allow selecting dynamic data source
* Feature: Woocommerce elements
* Feature: White label
* Feature: Repeater elements
* Improvement: RTL support
* Improvement: Pro license display on mobile
* Improvement: Added translate strings for dynamicContent Option
* Improvement: Updated plugin structure to support integrations
* Fixed custom code not loading element file
* Fixed missing pro label to custom code
* Fixed templates sortable not working
* Fixed custom icons preview modal
* Fixed title on social share element

= 1.2.1 2020-12-08 =
* Fixed custom classes not applying on socialShare, search and accordion elements

= 1.2.0 2020-12-07 =
* Added Custom PHP
* Automatically refresh the page after inserting the API key

= 1.1.1 2020-11-27 =
* Hotfix for lifetime license

= 1.1.0- 2020-11-24 =
* Fixed various issues related to global colors/gradients
* Fixed icons list not adding to tabs
* Updated tabs to zb 1.1.0
* Add loader when delete license key
* Removed utils
* Register elements with the new system
* Re-organize code structure
* Updated dependencies
* Updated folder structure
* update to vue 3 and fix syntax compatibility
* Fixed text domain used for elements

= 1.0.1 =
* Hotfix for typeKit

= 1.0.0 =
* Initial Public Release
