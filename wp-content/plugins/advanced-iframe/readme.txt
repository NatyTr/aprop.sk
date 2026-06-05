=== Advanced iFrame ===
Contributors: mdempfle
Tags: iframe, embed, resize, shortcode, modify css
Requires at least: 3.3
Tested up to: 6.8.2
Stable tag: 2025.6
Requires PHP: 5.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0

Include content the way YOU like in an iframe that can hide and modify elements, does auto-height, forward parameters and does many, many more...

== Description ==

> **[New website: advanced-iframe.com](https://www.advanced-iframe.com/)**
> **[Demo](https://www.advanced-iframe.com/advanced-iframe/demo-advanced-iframe-2-0)**

Include content the way YOU like in an iframe that can hide and modify elements, does auto height, forward parameters and does many, many more...

= Main features of advanced iframe =
By entering the shortcode '[advanced_iframe]' you can include any webpage to any page or article.

Advanced iFrame now has out of the box support for embedded 3D models using the p3d 3D viewer. Go to https://p3d.in/b/24 and download a pre-configured plugin where the model does scale already nicely on all devices. Get started for free! If you need more storage or access to the Premium features of p3d.in, you can get a 50% discount on your first payment with the coupon AIFRAME on checkout.

The following cool features compared to a normal iframe are implemented:

- Hide areas of the layout to give the iframe more space (see screenshot)
- Show only specific areas of the iframe when the iframe is on a same domain (The Pro version supports this on different domains) or include parts directly by jQuery
- Modify css styles in the parent and the iframe to e.g. change the width of the content area (see screen-shot)
- Forward parameters to the iframe
- Resize the iframe to the content height or width on loading, AJAX or click
- Responsive videos (moved from the pro to the the free version in v2022)
- Scroll the parent to the top when the iframe is loaded
- Hide the content until it is fully loaded
- Add a css and js file to the parent page
- Security code: You can only insert the shortcode with a valid security code from the administration.
- Many additional cool features are available the pro version - see https://www.advanced-iframe.com/advanced-iframe/advanced-iframe-comparison-chart

In the free version you can update to the pro version directly or test all features in the 30 days trial!

Please note: Modification inside the iframe are only possible if you are on the same domain or use a workaround like described in the settings.

So please check first if the iframe page and the parent page are one the same domain. www.example.com and text.example.com are different domains! Please check in the documentation if you can use the feature you like

A free iframe checker is available at
https://www.advanced-iframe.com/advanced-iframe/free-iframe-checker.
This tool does check if a page is allowed to be included!

All settings can be set with shortcode attributes as well. If you only use one iframe please use the settings in the administration because there each parameter is explained in detail and also the defaults are set there.

= Limitations of the free version =
The free version has no functional restrictions and is for personal and small non-commercial sites. After 10.000 views/month you have to opt-in to get unlimited views. If you do not opt-in the iframe is still working 100% and at the bottom of the iframe a small notice to opt-in is shown.

= Upgrading to Advanced IFrame Pro =
It's quick and painless to get Advanced iFrame Pro. Simply sign up for the 30 days trail or buy directly in the plugin. You can than use the plugin on commercial, business, and professional sites and blogs. You furthermore get:

* Show only specific areas of the iframe even when the iframe is on different domain
* Graphical content selector: https://www.mdempfle.de/demos/configurator/advanced-iframe-area-selector.html
* External workaround supports iframe modifications
* Widget support
* No view limit
* Hide areas of an iframe
* Browser detection
* Change link targets
* URL forward parameter mapping.
* Zoom iframe content
* Accordion menu
* jQuery help
* Advanced lazy load
* Standalone version - can be used in ANY php page!
* And much more...

You can find the comparison chart here: https://www.advanced-iframe.com/advanced-iframe/advanced-iframe-comparison-chart
See the pro demo here:
https://www.advanced-iframe.com/advanced-iframe/advanced-iframe-pro-demo

= Administration =
* Go to Settings -> Advanced iFrame

=	Quick start guide =
The quickstart guide is also available as video: https://www.advanced-iframe.com/advanced-iframe/advanced-iframe-video-tutorials

To include a webpage to your page please check the following things first:

* Check if your page page is allowed to be included https://www.advanced-iframe.com/advanced-iframe/free-iframe-checker!
* Check if the iframe page and the parent page are one the same domain. www.example.com and text.example.com are different domains!
* Can you modify the page that should be included?

Most likely you have one of the following setups:

1.	iframe cannot be included:  You cannot include the content because the owner does not allow this.
1.	iframe can be included and you are on a different domain: See the feature comparison chart: https://www.advanced-iframe.com/advanced-iframe/advanced-iframe-comparison-chart and the feature overview https://www.advanced-iframe.com/advanced-iframe/advanced-iframe-features-availability-overview. To resize the content to the height/width or modify css you need to modify the remote iframe page by adding one line of Javascript to enable the provided workaround.
1.  iframe can be included and you are on the same domain: All features of the plugin can be used.

If you mix http and https read https://www.advanced-iframe.com/iframe-do-not-mix-http-and-https. Parent https and iframe http does not work on all mayor browsers!

== Installation ==
There are 2 ways to install the Advanced iFrame

*Using the Wordpress Admin screen*

1. Click Plugins, Add New
1. Search for advanced iframe
1. Install and Activate it
1. Place '[advanced_iframe]' in the editor directly or click on the "Add advanced iframe" button above the editor
1. Configure your iframe at your dashboard side menu -> "Advanced iFrame pro". For adding several iframes please see the examples and the FAQ.

*Using FTP*

1. Upload the 'advanced-iframe' folder of the download zip to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place '[advanced_iframe]' in the editor directly or click on the "Add advanced iframe" button above the editor
1. Configure your iframe at your dashboard side menu -> "Advanced iFrame pro". For adding several iframes please see the examples and the FAQ.

== Other Notes ==
= Advanced iframe attributes =

Below you find all possible shortcode attributes. If you only use one iframe please use the settings in the administration because there each parameter is explained in detail and also the defaults are set there.

Setting an attribute does overwrite the setting in the administration.

[advanced_iframe securitykey=""   src=""
  id=""   name=""
  width=""   height=""
  marginwidth=""   marginheight=""
  scrolling=""   frameborder=""
  class=""   style=""
  content_id=""   content_styles=""
  hide_elements=""   url_forward_parameter=""
  onload=""   onload_resize=""
  onload_scroll_top=""   onload_show_element_only=""
  store_height_in_cookie=""   additional_height=""
  additional_js=""   additional_css=""
  iframe_content_id=""   iframe_content_styles=""
  iframe_hide_elements=""  hide_page_until_loaded=""
  include_hide_page_until_loaded=""
  include_url="" include_content=""
  include_height=""  include_fade=""
  onload_resize_width=""   resize_on_ajax=""
  resize_on_ajax_jquery=""   resize_on_click=""
  resize_on_click_elements=""   use_shortcode_attributes_only=""
  onload_resize_delay=""
  ]


== Screenshots ==
1. Comparison between normal iframe and advanced iframe wrapper. The red areas are modified by the advanced iframe to display the content better.
2. This image shows the difference with an url forward parameter. In the advanced iframe a sub album is shown while the normal iframe still shows the entry screen.
3. The basic admin screen to enable standard settings
4. The advanced admin screen to enable advanced settings like HTML and css changes
5. The advanced admin screen to enable Javascript scroll to top and autoresize resize

== Frequently Asked Questions ==
Find the latest FAQ here:
https://www.advanced-iframe.com/advanced-iframe/advanced-iframe-faq/

= Demo =
See the pro demo here:
https://www.advanced-iframe.com/advanced-iframe/advanced-iframe-pro-demo

See the free demo here:
https://www.advanced-iframe.com/advanced-iframe/demo-advanced-iframe-2-0

== Upgrade Notice ==
Use the Wordpress installer to update or simply overwrite all files from your previous installation.
If you have some radio elements empty after the update simply select the one you like and save again.

== Changelog ==
= 2025.6 =
- Security fix: Vulnerability Title: Advanced iFrame <= 2025.5 - Authenticated (Contributor+) Stored Cross-Site Scripting CVE ID: CVE-2025-6987 was fixed.
- New: Tested with WordPress 6.8.2
- New: advanced iframe has a new domain: https://www.advanced-iframe.com. All links in the plugin where updated and checked. 
- New: https://www.advanced-iframe.com is live now. Everything from www.tinywegballery.com/blog was moved. Also a new menu structure was introduced.
- New: Edge was added as setting in the browser detection.
- New: Standalone version is now also available in the freemius version.
- New: Standalone examples where reworked and old links removed.
- New: Standalone version is now even easier to setup because the site_url handling was rewritten and the default should work now even better.
- New: Standalone version now also uses jQuery 3.7.1 like WordPress does.
- New: The freemius section documentation was improved based on user feedback.
- New: No 10.000 hit limit anymore. The powered by text is now removed automatically when you OPT-IN or if you disable it.
- New: Updated Freemius to 2.12.1
- Fix: Add iframe url as param: Same domain with hash" was broken because one of the last security fixes was too tight. Now it works fine again:  https://www.advanced-iframe.com/advanced-iframe/advanced-iframe-pro-demo/add-iframe-url-as-param-same-domain-hash
- Fix: add_iframe_url_as_param_direct was not working anymore because of a wrong security check. Now https://www.advanced-iframe.com/advanced-iframe/advanced-iframe-pro-demo/add-iframe-params-to-parent works fine for the remove and same domain again.
- Fix: documentation of the external workaround was improved.
- Fix: When switching between free and pro a notice about unwanted characters was shown. This was a notice because both plugin where active for a small amount of time. This is solved now.
- Fix: Users often use false in hide_part_of_iframe and a message was shown. Users contacted the advanced iframe team to solve this. Now this setting is simply ignored. 
- Removed: iframe_zoom_ie8 was removed and all the code that comes with it as ie8 browser is not used anymore.
- Removed: "Special case sub domain" section was removed as it was only containing the removal info text for one year.

= 2025.5 =
- Fix: == at the end of src caused the whole parameter to be removed. Now this is supported
- Fix: filteredContent variable was not defined properly. Now it is.

= 2025.4 =
- New: Tested with WordPress 6.8.1
- New: Updated Freemius SDK to 2.12.0 which improves compatibility with php 8.3 and 8.4
- Fix: advanced-iframe-admin-advanced.php was saved with a wrong line ending. Now Unix (LF) is used like for all other files. On some systems the administration was not loaded properly.

= 2025.3 =
- New: OPT-IN users now get additional benefits: Additional sections on the help tab, exclusive coupons, monthly chance to win a free license.
- New: Additional help is now also available for OPT-IN users.
- New: All OPT-IN users have the chance to win a free license once a month. As long as you allow to receive marketing emails, you can win. You can only win once.
- New: OPT-IN users will get exclusive coupons. No worries: Advanced iFrame will not spam you.
- Security Fix: CVE-2025-1437 - Authenticated (Contributor+) Stored Cross-Site Scripting) - using ononloadload was still executed. Now the filter method is checking recursively.
- Fix: Removed old text about the Flash Uploader.
- Fix: Updated the link to the forum

= 2025.2 =
- Fix: The close icon of show iframe as layer was not shown because of an old path. Now the correct path in the pro version is used.  https://www.advanced-iframe.com/advanced-iframe/advanced-iframe-pro-demo/show-the-iframe-as-layer
- Fix: Some links in the new pro version where still pointing to the old path. They are now working properly.

= 2025.1 =
- Fix: Documentation at the external workaround tab for the ai_external.js fixed and a link how to migrate from free to pro was added.
- Fix: hide_fullscreen.html was linking to the old plugins folder in the pro version. Now a placeholder is filled automatically. Please delete the hide_fullscreen.html in the advanced-iframe-custom folder once and enter the administration to get a new version generated. See https://www.advanced-iframe.com/advanced-iframe/advanced-iframe-pro-demo/full-screen-demo

= 2025.0 =
- New: Tested with WordPress 6.7.2
- New: Tested with php 8.2, 8.3, 8.4
- New: Support of freemius. This enables a 30 days trial of the pro version. Multi-site licenses, monthly, yearly and live time licenses are available now as well.
- New: All files in the js folder where re-factored, minor bugs fixed and optimized.
- New: The view counter is only counting front-end requests now. All view inside the administration are not counted anymore.
- New: The view counter message is now shown above the iframe in one line only. So your layout does not change if you hit the limit.
- New: The documentation folder in the zip was removed as it was outdated and all documentation is available in the administration in an even better format.
- New: map_parameter_to_url does support now :sameDomain. This disables external links as parameters. So only internal links which have the same 2nd level domain can be opened inside the iframe
- New: The replace function for empty parameters was rewritten. ?show will not be removed anymore. show= without any parameter will be still removed.
- New: The whole code was reformatted with Intellij and most of the code recommendations where done.
- Security Fix: map param to URL: When using hashes, the URLs are now checked if they are valid. Additionally there is now a limit of 1000 entries to avoid that the db is getting too big.
- Security Fix: "Stored Cross-Site Scripting via Host Header". When debug_js="bottom" is used the user agent and all headers are now escaped.
- Security Fix: the unfiltered_html check was made more strict. All parameters that allowed js are now simply removed.
- Fix: The parameters of parameter_url_mapping are now trimmed to handle slightly invalid input as well.
- Fix: hide_page_until_loaded fas only working in the pro version. Now it works in the free version again like it should.
- Fix: style of height and width of a custom
- Fix: printMediaQuery iframe width in the generator in the administration was not set properly. In the shortcode itself it was working fine.
- Removed: document.domain support is removed in the administration. Chrome removed this in Mai 2023, and it does not make sense to support this for other browsers anymore here as well. We announced this change 2022 already, and now it is also removed in the plugin.

= 2024.5 =
- New: Tested with Wordpress 6.5.4
- New: Description of ai_external.min.js was optimized.
- Fix: filterBasicXSS could cause a fatal php error with some configurations.

= 2024.4 =
- New: Tested with WordPress 6.5.3
- Fix: When using arrays in the parameters was causing an error. If this is now the case no optimization of placeholders are done anymore.
- Security fix: Added additional filters to some Javascript parameters to increase security.

= 2024.3 =
- Security fix: The filter attribute method now filters shortcode attributes which are parsed wrong by WordPress if the user does not have the unfiltered_html permission.
- Security fix: #x28 and #x29 are  filtered if the user does not have the unfiltered_html permission.

= 2024.2 =
- Security fix: The additional_js and additional_js_file_iframe attribute are now only allowed to be used if you have the permission "unfiltered_html", that you need in WordPress to use iframes. If you do not have this permission, during save the attributes are removed and an error message is shown.
- Fix: Show iframe as layer was not working properly in Firefox. The link was opening in a new tab. Using a different way to hide the iframe solves the problem.
- Fix: Filtering all short code attributes failed because "The Plus Blocks for Block Editor" was adding their settings to the ai attributes. I will contact them, why they do such stupid things!
- Fix: Switching a theme could cause an error message when no content pages where existing. Now this is only executed if content pages do exist.

= 2024.1 =
- Fix: add_iframe_url_as_param thrown an error because of the additional security filter. Now it works fine again.

= 2024.0 =
- Security fix: The include_html attribute is now only allowed to be used if you have the permission "unfiltered_html", that you need in WordPress to use iframes. If you do not have this permission, during save the attribute is removed and an error message is shown.
- Security fix: All shortcode attributes have now input sanitation to avoid Stored Cross-Site Scripting at save if you do not have the permission "unfiltered_html"! This happens in the normal editor and also in the Gutenberg block! Please get the unfiltered_html permission if you get an error message while you want to use '();= or a space in attributes. This sanitation is very general and does not allow all possible things you can do with advanced iframe. As 99.9% of the users who add an iframe are editors or above this should affect almost no one directly and it makes the plugin more secure.
- Security fix: " inside advanced iframe shortcode attributes is not allowed anymore to avoid XSS attacks.
- Security fix: Additional output filtering of short code attributes directly used in HTML or Javascript to avoid XSS attacks.  This is done for ALL roles!
- New: The scroll to top in the external workaround is now also supporting the "touched" event next to the "click" event.
- New: The documentation was improved for scroll to top as the external workaround is also supporting "iframe" if "Scrolls the parent window/iframe to the top" is set to iframe.

For older changes please see: https://www.advanced-iframe.com/advanced-iframe/advanced-iframe-history