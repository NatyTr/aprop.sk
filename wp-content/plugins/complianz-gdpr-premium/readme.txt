=== Complianz Privacy Suite (GDPR/CCPA) premium ===
Contributors: RogierLankhorst
Tags: GDPR, CCPA, AVG, E-Privacy
Requires at least: 4.9
License: Complianz Terms of Use (Premium, see pro/license.txt), and where applicable GPL 2 (Free)
Requires PHP: 5.6
Tested up to: 6.0
Stable tag: 6.1.3.1

Complianz Privacy Suite with a Cookie Consentbanner and customized Cookie Policy based on the results of the built in Cookie Scan.

== Description ==
Complianz Privacy Suite with a Cookie Consentbanner and customized Cookie Policy based on the results of the built in Cookie Scan.

IMPORTANT! Complianz Privacy Suite can help you meet compliance requirements, but you as user must ensure that all requirements are met.

= Installation =
* Go to “plugins” in your Wordpress Dashboard, and click “add new”
* Click “upload”, and select the zip file you downloaded after the purchase.
* Activate
* Navigate to “Complianz”, and follow the instructions

== Frequently Asked Questions ==

== Change log ==
= 6.1.3.1 =
* Fix: drop Elementor Pro Cookie Banner support
* Fix: drop aria-checked=true for input type=checkbox, props @lofesa
* Improvement: improve caching mechanism to ensure that on even for persistent cached database queries, these get updated when necessary
* Fix: Uncaught TypeError: service is null when Elementor integration used in combination with Cookie Shredder/consent per service
* Improvement: new event for integrations 'cmplz_status_change_service'
* Improvement: when checking callback function, also check for prefix
* Improvement: user registration pro
* Fix: duplicate id in back-end radio field

= 6.1.3 =
* Fix: Consent Mode consenting to marketing when preferences was accepted
* Fix: Burst Statistics integration triggering twice

= 6.1.2 =
* New: Integration for Burst - Privacy Friendly Statistics
* Fix: localhost reference in default css, props @toxicum
* Fix: saving of category selection in integrations
* Fix: saving of border radius of 0 in cookie banner
* Fix: race condition in TCF
* Fix: Woocommerce Google Analytics - Enhanced Ecommerce integration
* Improvement: editable "I Agree" button for consent per service/Cookie Shredder
* Improvement: cache get and cache set on database queries on the cookie policy and cookie shredder, removing duplicate queries, props David
* Improvement: add duplicate banner option when A/B testing enabled
* Improvement: don't track records of consent for bots
* Improvement: automatically disable SSL verify if user server does not supports it
* Improvement: text in impressum regarding journalistic content.
* Improvement: TCF features incorrectly could be unchecked. Changed checkboxes to disabled, props Robert
* Improvement: transients replace with own transients

= 6.1.1.1 =
* Fix: WP Rocket lazyload integration should not load when lazy load not enabled in wp rocket

= 6.1.1 =
* New: Matomo Tag Manager / WP Piwik
* Improvement: TCF default legitimate interest inactive
* Improvement: export banner settings option
* Improvement: remove space after empty text, causing unnecessary paragraphs
* Improvement: force auto updates for TCF
* Improvement: use close button with enter, props @benqwerty
* Improvement: limit banner image size to banner editor only, props @bkwineper
* Improvement: lazy load integration for WP Rocket and LazyLoader plugin, props @Lofesa
* Improvement: change divs in banner to spans, props @kbelimpassakis
* Improvement: move inline css to head, props @kbelimpassakis
* Improvement: lock css file on css generation in banner
* Improvement: clean up blocked scripts after activation, props @puregraphx
* Improvement: check if detected ip in geo ip is localhost or empty, if so, fall back to other method
* Improvement: run load_plugin_textdomain for Loco Translate compatibility props @peterforensys
* Fix: save/enable on integrations slider
* Fix: dismiss Elementor pro activated notice
* Fix: fixed accidentally re-activating deactivated services on cookiedatabase.org sync

= 6.1.0.1 =
* Improvement: Themify Integration
* Improvement: WP Adverts integration, props @titusb, @gwin
* Improvement: Thrive integration, props @spike05de
* Improvement: editable text for consent per service placeholder
* Fix: due to dropped jquery, some features for TCF cookie policy not working
* Fix: Due to change from google-analytics.js to gtag.js, anonymizeIp has changed to anonymize_ip, props @ccalislar35
* Fix: On sync, include services without cookies, even if completed

= 6.1.0 =
* New: CookieShredder
* CSS: simplify scrollbar in dashboard css
* CSS: drop overflow: auto on header
* CSS: drop min-width 300px below 350px
* CSS: set bottom banner to bottom on mobile as well
* CSS: padding on links in legal pages
* CSS: override theme flex property on buttons in banner
* CSS: override theme line height property on manage consent button
* CSS: set bottom corners to radius 0 on manage consent button
* Improvement: matomo cookieless tracking
* Improvement: hide not required fields if no documents are selected, props @paaljoachim
* Improvement: allow for time zone offset when saving consent in records of consent
* Improvement: keep categories in sync https://github.com/Really-Simple-Plugins/complianz-gdpr/issues/324 props @antonellon
* Improvement: Link to permalink settings when notice is shown
* Improvement: DNT feedback string improved
* Improvement: drop fieldname class from fields in settings page, to prevent conflicts
* Improvement: CloudFlare CFCustom geo ip option
* Improvement: new options for GEO ip
* Improvement: allow saving of empty script center blocks
* Improvement: on switch to TCF banner, regenerate the banner css
* Improvement: obfuscate email address also with css
* Improvement: remove condition on categories settings in cookie banner setting, to allow for manage consent area configuration in cookie policy
* Improvement: catch Tatsu pagebuilder preview
* Improvement: auto enable GEO ip if records of consent enabled
* Improvement: disable hide cookiebanner option on legal pages
* Improvement: added font size option
* Improvement: responsiveness wizard
* Improvement: cookiebanner required feedback
* Improvement: new option to disable width auto correction
* Improvement: ratio option for placeholders on openstreetmap
* Improvement: added Flexible Maps integration
* Improvement: Less conditions on german paragraph in impressum
* Improvement: drop title element from svg in banner, props @alexbosch
* Improvement: p elements on all paragraphs in legal documents
* Improvement: add toggle to hide the legal document links on the banner
* Fix: Novo Maps integration
* Fix: domains with 'type' in the url could not load the css file props @nimdaweb
* Fix: Hubspot integration
* Fix: catch not set enable dependency key
* Fix: preview cookiebanner not always correctly updated.
* Fix: paging in integrations
* Fix: remove <title> tag from close button. props @doubleyourbrand
* Fix: only reload on a deny action if marketing was accepted props @dasisdormax
* Fix: allow mappress en google maps enabled at the same time
* Fix: allow quotes in custom css
* Fix: export filtered dataset from Records of Consent
* Fix: in cmplz_fire_categories, declare event as local variable, fixing theme conflicts with global declared variables props @jrt341.
* Fix: fix revert to defaults for text_checkbox booleans @puregraphx

= 6.0.14 =
* Fix: z-index of tour on integrations page
* Fix: amp integration not using the new array structure yet
* Improvement: toggles on script center custom scripts
* Improvement: move TCF vendorlist to cookiedatabase.org, and improve fallback mechanism
* Improvement: comment on manage consent button setting

= 6.0.13 =
* Improvement: auto enable migrate.js when upgrading from <6 to 6.0 and up

= 6.0.12 =
* Fix: allow for configuration which could cause empty buttons.
* Fix: anonymous statistics description toggle showing when field is disabled props @kaznim, @puregraphx.

= 6.0.11 =
* Fix: multiple regions in default css

= 6.0.10 =
* Improvement: when some updates were skipped, old varchar columns could cause row size too large issues, causing banner settings not to get saved.
* Improvement: if obsolete categories type 'hidden' still was in use, force override to 'view-preferences'
* Improvement: fallback to default css if custom generated css wasn't available due to file write permissions or skipped upgrade
* Fix: When text fields are empty, like the deny button, fallback to default value.

= 6.0.9 =
* Fix: detect duplicate upgrade in banner

= 6.0.8 =
* Fix: hidden category type enabled on wizard changes
* Fix: unescape texts in banner links

= 6.0.7 =
* CSS: to prevent theme css conflicts, reset values for: display block op label:after, summary, line-height buttons, margin on manage consent button, row reverse on buttons/bottom
* Fix: catch PHP 8.1 errors
* Improvement: catch not writable uploads folder
* Improvement: allow custom privacy statement and impressum documents on banner in free version
* Fix: auto adjust banner width code "walking"
* Fix: TCF for CCPA dismissed banner on pageload

= 6.0.6 =
* Improvement: fallback on small images in cookie banner
* Fix: enable blocked images after consent
* Fix: gap css improvements
* Improvement: auto install suppport
* Improvement: languages update
* Improvement: text change https://github.com/Really-Simple-Plugins/complianz-gdpr/issues/331

= 6.0.5 =
* Fix: link for white listing documentation, props @scheinercc
* Fix: update Pixel your site integration to 6.0 structure
* Fix: elementor placeholder css
* Fix: add cmplz- prefix to position as class from banner
* Improvement: css for opt out improved
* Fix: duplicate function name in store locator integration
* Fix: custom 'other purpose' translatable
* Fix: fire categories event on page load to allow Consent Mode to initialize correctly
* Fix: TCF language detection not working for norwegian because of bokmal prefix
* Fix: Image upload fallback for very small images, default preview when no image selected
* Improvement: hide link if corresponding legal page was not created yet
* Improvement: company country in impressum
* Improvement: cache queries for front-end to prevent duplicate queries

= 6.0.4 =
* Improvement: add more info to system status
* Improvement: Gtag does not need anonymize ip, as this is the default
* Fix: soft cookie wall in combination with opt-out consent type causing banner not to show
* Improvement: gap property not supported in safari, summary arrows on samsung mobile
* Improvement: TCF/IAB json files vendorlist improvements
* Fix: set service as bodyclass
* Fix: handle css for anonymous/default stats on cookie policy correctly
* Fix: race condition when only statistics was enabled, caused by reload
* Improvement: Oxygen builder support

= 6.0.3 =
* Fix: Open StreetMaps / OSM plugin integration
* Fix: updated all statistics integrations

= 6.0.2 =
* Improvement: allow both consent per category and consent per service on the custom consent area shortcode
* Improvement: show summary of detected cookies instead of full list on the scan page
* Fix: WP Google Maps integration
* Fix: records of consent could track a duplicate hit if two requests were triggered at the same time

= 6.0.1 =
* Fix: statistics consent when the questions "do you want to ask consent for statistics" has been answered with yes
* Fix: cmplz-document class on body when soft cookie wall was enabled
* Fix: {url} links detected by automatic tools
* Fix: css fix: limit category classes to banner only
* Fix: separate cookies with same name and different services in cookie list (Facebook/Instagram)
* Improvement: drop h1 and h2 from banner to prevent styling issues with styles from overriding themes

= 6.0.0 =
* Improvement: new Script Center, with option to add dependencies and placeholders
* Improvement: complete rewrite of the cookiebanner code, faster, modular, easily customizable.
* Improvement: removed jquery as dependency from the cookiebanner javascript
* Fix: new array structure for cookiebanner settings causing error with translations.
* Fix: load tcf stub als first action on page
* Fix: catastrophic backtracking with regex on iframes that do not contain a URL, props @ajoah https://github.com/Really-Simple-Plugins/complianz-gdpr/issues/320
* Improvement: auto consent for visits from bots

= 5.5.2.1 =
* Fix: regex pattern in placeholder code should allow for linebreaks props @ajoah
* Fix: license check for TCF
* Improvement: changed order in ip number checks in GEO ip feature, to allow for some not standard hosting companies
* Fix: on multisite, when the settings "set cookies on root" is used, the rt prefix should not be used.
* Improvement: limit DPO question to EU and UK only
* Fix: obsolete paragraph in Brazilian privacy policy
* Fix: duplicate word "telephone" in Impressum
* Fix: after enabling region redirect, i.c.w. a general document like impressum, the notice about not all pages having been linked did not go away
* Improvement: some improvements on imprint regarding strings required in German.

= 5.5.2 =
* Fix: load google analytics also when banner is not required
* Improvement: when license is not activated, don't re-check on each pageload.

= 5.5.1 =
* Fix: auto update disabled for other plugins

= 5.5.0.1 =
* Improvement: Animate task dismissal
* Fix: typo's in texts
* New translations
* Improvement: add "enable_tcf_support" variable to gtag.js

= 5.5.0 =
* New: Region Redirect option in settings
* New: Placeholder themes and customization options
* New: Google Consent Mode for Google Tag Manager and GTAG.js
* Improvement: Imprint labeling for legal form
* Improvement: Cookiedatabase optin to optout
* Improvement: prepare update notice for 6.0
* Improvement: new filter 'cmplz_cookiescan_post_types' for posttypes in cookiescan
* Improvement: sharing of data section from different regions merged
* Improvement: line break in legal documents after publish/checked date
* Fix: Terms & Conditions recommendation not showing in free version
* Fix: automatic redirect to English for native English docs
* TCF: enable TCF in Brazil, South Africa and Australia
* TCF: legal basis not checked when not used by any vendors.
* TCF: keep purposes with same id in sync when checked.
* TCF: Clear TC string if it was created over 365 days ago
* Fix: CSV download of records of consent not working in FireFox
* Fix: detection of WP Consent API support in wizard not showing correct result
* Fix: Imprint missing a paragraph due to condition

= 5.4.0.2 =
* New: WP Store locator maps integration
* Improvement: some string changes
* Improvement: save feedback
* New: WCAG option on the admin
* New: cmplz_choice cookie also available without a/b testing or records of consent
* Fix: restore youtube-nocookie url to be recognized as URL for YouTube placeholders
* Fix: allow marketing cookies on accept deny, props @klous-1

= 5.4.0.1 =
* Fix: anonymizeip double quote in gtag.js

= 5.4.0 =
* Improvement: separate object to legitimate interest in TCF
* New: new region: Brazil
* Improvement: merged EU and US Data Protection officer questions in wizard
* Improvement: some line breaks in privacy statements

= 5.3.1 =
* New: Meks Easy Maps
* Fix: keep cookie and service lists in sync across languages when new languages are added.
* Fix: auto update notice condition reversed
* Fix: on the root website of a multisite setup accept all did not enable statistics
* New: Avada integration

= 5.3.0.1 =
* Fix: French translation causing fatal error

= 5.3.0 =
* Improvement: textual changes in cookie policy
* Improvement: save selected setting in localstorage for dropdowns in dashboard
* Improvement: change retention into expiration
* Improvement: better privacy policy link description for Complianz
* Improvement: privacy friendly analytics feedback in DE
* Improvement: samesite and secure cookies for Google Analytics and gtag.js
* Improvement: daily check for free translation files, and admin notice if free is not deleted
* Improvement: notice about conflicting cookie banner plugins
* Improvement: append banner to end of body element, to improve LCP
* Improvement: linkedin placeholder
* Fix: no integration notice when WP Google Maps active
* Fix: GTM categories not included in A/B tests
* Fix: extend core WP privacy annex
* Improvement: correct Elementor css causing styling issues on Complianz pages.
* New: Imprint
* Fix: initialize the __tcfapi() and postmessage functionality
* Improvement: several changes to the way TCF is handled
* Improvement: do not autoload class with class_exists check in cmplz_uses_gutenberg() function props @knomepasi

= 5.2.6.2 =
* Improvement: new IAB TCF storage disclosure requirement regarding cookie refresh.
* Improvement: if vendor has only legitimate interest, legint checkbox on vendor level is disabled

= 5.2.6.1 =
* Improvement: increase vendorlist download frequency

= 5.2.6 =
* Fix: upgrade SQL for 5.2.0 statistics upgrade.
* Fix: Sync services in multilanguage environment
* Improvement: prevent naming conflicts with custom posttypes when region redirecting by adding a prefix
* Fix: A/B testing on multisite DB column mismatch because of new rt_ cookie prefix
* Fix: consent area required a cookiename change on multisite environments because of rt_ cookie prefix

= 5.2.5.1 =
* Fix: legitimate interests enabled by default in TC String

= 5.2.5 =
* Fix: placeholder detection used wrong key, resulting in default placeholder

= 5.2.4.1 =
* data breach report dropdown

= 5.2.4 =
* minor bug fix

= 5.2.3 =
* Fix: text domain correction
* Fix: min-width upload button
* Fix: in subdirectory installations, the find_wordpress_basepath function could not find the wp installation, props @ianpegg
* Improvement: re-structured services detection
* Improvement: drop TGM integration
* Improvement: flags for multiple regions
* TCF/IAB: some changes to keep the banner and cookie policy compliant with the IAB requirements
* Improvement: enable auto updates

= 5.2.2.3 =
* Fix: TCF prefix not retrieved

= 5.2.2.2 =
* Translations update
* Fix: TCF in combination with opt out banner causes opt out banner to stay hidden due to css change improving CLS
* Fix: Cookie name change needed to get updated in the TCF integration

= 5.2.2.1 =
* Translations update

= 5.2.2 =
* Fix: When choosing anonymous statistics from a not supported statistics type, the wizard forced configuration by Complianz.

= 5.2.1 =
* Fix: arguments error in notice

= 5.2.0 =
* New: TikTok integration
* New: South Africa POPIA support
* Fix: On multisite, root/main site cookies get different prefix, to prevent browser from getting confused about cookies on root and subpaths.
* Fix: Monsterinsights integration causing two pageviews
* Fix: Restore ajax loaded content blocker option, which was not following the setting's input
* Improvement: load TCF strings for the wizard also from the IAB/TCF files shipped with the plugin
* Improvement: set background color of TCF banner to same color as same dropdowns in cookie policy
* Improvement: reset padding bottom for iframes during blocked status where responsiveness is handled with a padding-bottom 56%.
* Improvement: CLS improvement for TCF.
* Fix: several small fixes in the Databreach wizard
* Improvement: Detect Google Maps integration, and show notice about possible solution
* Improvement: Disable ACF integration when any of the other Google Maps integrations is detected
* Improvement: make TCF banner buttons color editable
* Improvement: databreach wizard disclaimer
* Improvement: added fallback URL for TCF data

= 5.1.3 =
* Fix: CLS score fix caused TCF banner to re-appear after dismissing

= 5.1.2 =
* Fix: TCF CLS score
* Fix: dropshadow on TCF banner

= 5.1.1 =
* Fix: copy shortcode for multiple regions always selecting first shortcode
* Fix: translations for TCF not pulled in correctly in certain race conditions
* Fix: allow for German Ringel S in Google Maps addresses

= 5.1.0 =
* New: Privacy Act 1988 Australia
* Update: Privacy Statements US/Canada.
* Fix: miscelaneous cookies without information in another language defaulted to the wrong translation
* New: Pixel your site pro
* Improvement: changed "Analytical" to "statistical".
* Fix: terminology in US/CA documents "to citizens and legal permanent residents of" instead of "to citizens of"
* Fix: explicitly ask consent in some EU regions question restored

= 5.0.3 =
* Improvement: new purposes for EU/UK
* Fix: shepherd loop
* Improvement: switch consent area in cookie policies
* Fix: Elementor integration after reload
* New: Variation swatches for Woocommerce integration
* Fix: link to processing agreement in wizard
* Improvement: show "hide cookie banner" metabox only on public post types

= 5.0.2.1 =
* Fix: css for summary/details on privacy policy updated

= 5.0.2 =
* Fix: Exclude RSS feed from cookie blocker.
* CSS: save button to primary on license
* Improvement: tips & tricks not translatable
* Fix: CSS styles for cookies shortcode
* Fix: since 5.0 UK could not set the categories type separately
* Fix: preview of categories in automatically hidden after timeout
* Improvement: option to set a default region for not selected regions.
* Fix: upgrade integrations notices to 5.0 style
* Improvement: when cookie banner isn't necessary, show "open" notice instead of "completed"
* Fix: on duplicate cookies cleanup, do not delete cookies when from different services
* Fix: Processing field for 'other' option, linked textfield wasn't shown when selected
* Fix: feedback on settings saved responsive and multilanguage proof
* Improvement: change varchar fields in cookiebanner table to text, to preven max rowsize warnings on some installations.
* Fix: warning about CMP files after deactivating TCF

= 5.0.1.2 =
* Improvement: US Disclosed list not required
* Improvement: tooltips to flow "up"
* Fix: not possible to switch off box-shadow due to default setting and false comparison.

= 5.0.1.1 =
* Fix: some banner fields not editable for TCF banner
* Fix: hyperlink color on accept button in opt-out banner not passed to front-end.
* Improvement: integrations enabled notice dismissed for upgraded users.
* Improvement: body status classes consistency improved by always using categories. Use of 'allow' and 'deny' is dropped
* Improvement: catch error on cookie retrieval in sandboxes iframe

= 5.0.1 =
* TCF CSS changes
* Translations update
* Changed enqueue scripts to enqueue inline scripts
* Elementor Ultimate Add on integration
* Improvement: load TCF files from plugin's server.

= 5.0.0 =
* New: Animations and more customizations on cookie banner
* New: Integrations for 10+ plugins and services
* Improvement: UX
* Improvement: WCAG on policies
* Improvement: Adjustments for CNIL
* Improvement: A/B Testing adjustments
* Improvement: Elementor integration improved
* New: WeGlot support
* Improvement: moved AMP blocker to AMP integration

= 4.9.13 =
* New: WP Video Lightbox integration
* New: Extended pixelyoursite to the Pro integration
* New: Easy Fancy Box integration
* Fix: Records of consent dynamic PDF search
* Fix: custom Tag Manager events translatable

= 4.9.12 =
* Legal: ending delimiter in clicky recognition regex

= 4.9.11 =
* Legal: Do Not Sell My Personal information form moved up to top of policy in accordance with recent changes
* Fix: Elementor integration firing init, causing issues with hamburger menu
* New: Clicky integration
* New: feedback for AMP/no javascript
* Fix: lanuage when requesting over rest-api with polylang
* Fix: When using Geo IP, and visiting the website from one of the supported regions, but that region is not selected, it incorrectly showed a banner.

= 4.9.10 =
* New: Novo Maps integration
* Improvement: pinterest blocklist extended
* Fix: not 100% in wizard because of CF7 notification, even when not applicable.
* Improvement: don't show update notification for Terms & Conditions using TGMPA


= 4.9.9 =
* Improvement: only show CF7 notice when recaptcha is active

= 4.9.8 =
* Improvement: Events Calendar integration
* Drop Contact Form 7 integration as of CF7 5.4, due to continuous breaking changes in CF7. Contact Form 7 should integrate with the WP Consent API instead.

= 4.9.7 =
* Improvement: cookie policy overview css for mobile
* Fix: Youtube in Elementor widget after Elementor update not blocking anymore

= 4.9.6 =
* Fix: Revoke on cookie policy with accept/deny banner
* Improvement: do not re-run marketing after consent
* Improvement: clear cookies after cookie policy id change. Resolves issues for WPML users with cookiepath issues
* Improvement: activate blocked images on consent
* Fix: prevent infinite loop in Canada region with certain configurations
* Improvement: limit number of requests by jquery error detection
* Improvement: compliance with new TCF requirements
* Improvement: Advanced Captcha reCaptcha updated to integrate with latest version
* Improvement: improved URL pattern in URL input field
* Improvement: changed Advanced Custom Fields detection from ACF to ACF_VERSION, as it seems another plugin or theme is using this same function/constant/class
* Improvement: improved method of keeping track of blocked content containers that were already set up, or activated.
* Improvement: TranslatePress compatibility
* Improvement: allow for space in Google Maps iframe embed URL
* Improvement: stricter matching for WooCommerce Google Analytics pro and WooCommerce Google Analytics Integration, preventing matching on other scripts

= 4.9.5.1 =
* Fix: merge integration fix

= 4.9.5 =
* JetPack integration
* Fix: disabling of integrations not working properly after theme support was added.
* Fix: short code for custom consent area not activating due to changes in the jquery events.

= 4.9.4 =
* Fix: when configured with accept/deny, accept on blocked content container not working

= 4.9.3.1 =
* Fix: prevent REST API requests from being cached

= 4.9.3 =
* Fix: not storing consent

= 4.9.2 =
* Improvement: Fn.resize shorthand is deprecated
* Improvement: pass language to ajax calls with two character language code
* New: records of consent
* New: Woocommerce Analytics Pro integration
* New: Citadello Directory integration
* Improvement: when new languages are added to a site (multilanguage setup) ensure each cookie is added in every new language
* Fix: on multisite environments cookies were set on language subdomains instead of root. This fix sets cookiepath to root for WPML and polylang

= 4.9.1 =
* Fix cookie path on sites where WordPress is installed in a subfolder

= 4.9.0.2 =
* Translation updates

= 4.9.0.1 =
* Fix: use get_rest_url() instead of site_url for rest api calls

= 4.9.0 =
* WCAG: Do Not Sell My Personal information form WCAG improvements
* WCAG: fix button accessibility and div for Contact Form 7. props @juliemoynat-tanaguru
* Improvement: set default checkbox style to slider
* Improvement: Recaptcha v2 for CF7 CSS for better placeholder look
* WCAG: fix category checkbox square accessibility. props @juliemoynat-tanaguru
* New: support for plugin "Invisible recaptcha for WordPress"
* New: option to disable monthly automatic cookie scan
* New: Volocation integration
* New: Set cookie path based on site url. This allows for sites in subfolders to place cookies on the subfolder URL only
* Fix: typo in css class
* New: Gravity Forms recaptcha integration
* New: Advanced noCaptcha & invisible captcha integration
* New: Added status change event to be able to hook into consent actions from the user. E.g. a reload on consent action for plugins with server side consent management.
* New: MonsterInsights Enhanced ECommerce integration
* New: Generate Press theme integration
* Fix: Resource interpreted as Document but transferred with MIME type. Due to browsers expecting a html document as source, using mp4 as src placeholder caused unexpected behaviour.
* Fix: duplicate statistics tracking on anonymous statistics accept action fix not merged correctly in previous update
* Improvement: new method of ip detection added
* Fix: some strings for Canadian policies not translatable
* Fix: revoke marketing cookies if statistics still enabled, on configurations with non anonymous statistics
* Fix: incorrectly track a/b testing even when a/b testing not enabled
* Fix: PDF generation for data breaches
* Improvement: more context for email on updating legal documents each 12 months in US configurations props @gfields108
* Improvement: resolve jQuery Migrate notice "Global events are undocumented and deprecated" props @m266
* Improvement: change front-end admin-ajax.php calls into rest-api calls
* Improvement: Visual Composer front end editing exclude from the Cookie Blocker
* Improvement: PHP 8 compatibility
* Improvement: IE compatibility
* New: Woocommerce analytics integration (free)

= 4.8.2 =
* Improvement: possibility to add both a marketing and an advertising cookies section
* Fix: prevent double firing of analytics icw native class on accept #926
* Improvement: impressum legal update. https://www.versandhandelsrecht.de/2020/11/fernabsatzrecht/impressum/rstv-mstv-impressum/
* Improvement: fixed two edge cases: Impressum after region change to non EU, analytics script in script center when running analytics from Complianz

= 4.8.1 =
* Fix: Do Not Track & Global Privacy Control feedback in Cookie Policy
* Fix: Shares data test returning false positive because of inverted script center script condition, props Michael
* Fix: Tag Manager script was incorrectly added when Google Tag Manager for WordPress plugin was used, props @imkane
* Fix: PHP warning when classes to insert contain two spaces. props @jadorwin
* Improvement: Revoke cookie consent string changed into Manage Consent
* Improvement: add Post Status for legal documents
* Improvement: remove quotes in WP Google Maps string to prevent German quotes issues
* Improvement: jquery error detection, skip error on line 0 as false positive, prevent overwriting error
* New: Nudgify integration
* New: you can now disable the cookieblocker by adding ?cmplz_safe_mode=1 to the URL

= 4.8.0 =
* Improvement: include stats when in safe mode
* Improvement: CSS style for invisible checkboxes to make them readable by screen readers. props @juliemoynat-tanaguru
* Improvement: load TCF strings directly to prevent editing of required texts
* Improvement: Trust Pulse integration
* Fix: boolean comparison on 1 value for geo ip detection in javascript, causing the plugin to do an unnecessary call to the server.
* Fix: custom statistics script blocking
* Fix: exclude TCF 2.0 / IAB banner css from non GDPR regions
* Fix: allow for matching on relative URL's for script sources, props @onwk
* Fix: Canadian TCF integration was missing vendors overview on Cookie Policy
* Improvement: publisherCountryCode set for TCF
* Improvement: skip translation of cookie properties when Polylang is enabled, as Polylang can't handle different fieldname contexts
* Improvement: banner bottom edgeless theme responsiveness, props @tim
* Improvement: Podcast Player integration, props @vedathemes, @uiuiui7
* Improvement: consent mode Google
* Fix: Disable cookieblocker on AMP when AMP integration not enabled, props @jensminor
* Fix: ony one ID for the cookie policy overview div, props @frown
* Improvement: maximize cookie name length, to prevent display issues
* Improvement: don't show conversion percentage when A/B testing not enabled
* Fix: typo in text domain
* Fix: "Usage" translated with _x function with wrong arguments

= 4.7.7 =
* Improvement: Map Multi Marker integration
* Fix: proof of consent showing cookie descriptions in white because of new cookie policy css props @ollieuk
* Fix: cookie descriptions css causing white space at the bottom on some themes props @umutusu

= 4.7.6 =
* Fix: pass new cookies overview css also when only the cookies shortcode is used.
* Fix: saving of plugin integration settings
* Improvement: some themes overriding the white-space:normal for the blocked content button
* Improvement: don't show "hide cookie banner option when no slug is available
* Improvement: compatibility with native browser lazyload option loading="lazy"

= 4.7.5 =
* Improvement: force display none on video placeholder for themes that are overriding the display none
* Fix: wrong text-domain for a TCF string
* Improvement: cookie list responsive and better compact design
* Improvement: translation of TCF strings in back-end
* improvement: responsiveness for banner bottom with square category checkboxes
* Fix: TCF features not saved correctly
* Fix: statistics paragraph EU/UK cookie policy not taking into account consent differences for statistical cookies
* Fix: advertising section incorrectly in cookie policy showing when no ads are showing
* Improvement: WCAG for blocked content notice: changed clickable div into button

= 4.7.4 =
* Improvement: don't enqueue document css in Gutenberg editor when disabled
* Improvement: no cookie wall on cookie policy
* Improvement: responsiveness of TCF banner
* Improvement: upgrade translation files on activation rather then upgrade from free
* Improvement: stricter instagram detection, preventing false positives on hyperlinks
* Improvement: show feedback on missing jquery and on jquery errors on front-end
* Fix: TCF only loading correctly when GEO ip enabled
* Fix: safe data before loading fields, which could cause conditions not to be updated yet on next pageload
* Fix: set default for personalized ads, to prevent unfocusable document control
* Fix: pass language as parameter with ajax calls, to ensure WPML and polylang translation

= 4.7.3.1 =
* Fix: roll back text/plain on statistics scripts

= 4.7.3 =
* CF 7 reCaptcha v2 fix
* Increase conditional jquery priority to limit chances of deregistering afterwards.
* Extend safe mode with script center features
* TCF implementation
* set custom statistics implementation default to plain so complianz can manage activation

= 4.7.2 =
* Improvement: prevent running of upgrade from free to premium more than once.
* Improvement: add option to flag first party marketing cookies with an integration
* Improvement: set tabindex to 0 for banner controls
* Improvement: limit access to hide banner option to users with manage_privacy capability
* Fix: Do Not Track signal not passed correctly when caching enabled
* Fix: legacy revoke button in US
* Fix: on configurations without marketing cookies "save preferences" was not functioning properly
* Fix: max banner width was implemented with min-width.

= 4.7.1.1 =
* Fix: banner width adjustment not FireFox compatible

= 4.7.1 =
* Fix: legacy revoke button in opt out regions, revoking in manage consent tab in opt out regions
* Improvement: add option to exclude cookie banner from a page
* Improvement: keyboard accessibility of square and slider checkboxes on the banner for WCAG2
* Improvement: when no marketing categories are present, don't show the marketing category on the banner
* Improvement: consent area shortcode caching proof
* Improvement: only accept marketing on placeholder accept
* Improvement: drop obsolete setting cookie_warning_enabled
* Improvement: rename cookieconfig.js to complianz.js to prevent unnecessary blocking by all in one wp security
* Fix: edited some typos
* Improvement: better adjusting to long button texts in the banner
* Improvement: add data-nosnippet to banner div to discourage indexing by search engines
* Improvement: separate question to explicitly let users choose to block recaptcha
* Improvement: google site kit notification
* Fix: accept deny banner variation did not revoke anymore after accepting, then revoking.
* Fix: CAOS integration not working anymore
* Improvements on the consent area shortcode implementation

= 4.7.0.3 =
* Fix: When "no document" was selected for a particular privacy document, a hash could be printed in the banner

= 4.7.0.2 =
* Fix: Vimeo sunset of simple API v2, requiring update of placeholder/thumbnail download
* Fix: Canada getting incorrect privacy statement URL on banner

= 4.7.0.1 =
* updated NL and BE languages were missing a %s placeholder, necessary for correct cookie policy

= 4.7.0 =
* Improvement: WCAG 2 compatibility
* Improvement: WP 5.5 permissions callback default true for public rest api calls
* New: Calendly integration
* New: Consent Shortcode to wrap your content manually [cmplz-consent-area][/cmplz-consent-area]
* Improvement: string update
* Improvement: proof of consent remove unnecessary info
* Improvement: improved activation notice
* Improvement: added Gutenberg preview image
* Improvement: improved Google Maps placeholders
* Fix: placeholders update
* Fix: static Google Maps images integration not working correctly due to regex pattern
* Fix: AMP matching on facebook and facebook-like tag
* Fix: Processors missing agreement notice when option to use processors was disabled
* Fix: Google Ads integration could not be disabled
* Fix: cookiedomain feature could cause indexOf undefined error in specific congfigurations
* Fix: in some configurations not all generated proof of of consent document were displayed, because of duplicate indexes
* NPM package updates

= 4.6.10.1 =
* Google Maps easy integration added
* Split last cookie sync into separate ajax call for better performance

= 4.6.10 =
* Fix: prevent page reload when user chooses functional only in category banner, when no other category has been selected.

= 4.6.9 =
* Fix: option to set cookies on optional domain on multisite was not checked correctly, causing cookies not to get set on some configurations

= 4.6.8 =
* Fix: run upgrade for banner width to prevent saving issues because bannerwidth still has odd number of pixels
* Fix: correctly load defaults so the banner has a fallback if the impressum title is not yet entered.
* Improvement: Syncing or adding used services won't enable integrations

= 4.6.7 =
* Improvement: read more link on double analytics implementation
* Fix: string "obsolete page" not translatable
* improvement: impressum title in cookie banner editable
* Improvement: remove "we do not use ... " statements from cookie policy
* Improvement: facebook for woocommerce integration
* Improvement: support for cross domain cookie consent on multisite
* Improvement: add noreferrer, noopener attributes to links on cookie policy
* Improvement: fix issue where Chrome bug causes blurred banner on uneven width sizes
* Improvement: fix broken Contact Form 7 integrations because of continuous changes to CF7

= 4.6.6 =
* New: Async script center option
* Fix: prevent CSS theme override in some themes on square checkbox
* Fix: prevent warning about rest-api by removing slash
* Fix: marketing level not firing correctly with Tag Manager
* Fix: when Do Not Sell My Personal Information is added, wrong link was highlighted as "upgrade" link
* Improvement: default banner width larger

= 4.6.5 =
* Fix: Tag Manager event not firing in new style checkboxes
* Improvement: catch error when uploads dir is not writable, for pdf creation
* Fix: Correctly replace banner labels in policy
* Fix: after 4.6.0 update script-center custom scripts not firing after consent
* Improvement: Simple Business Directory
* Improvement: dismiss soft cookie wall for categories below marketing
* Fix: manage consent paragraph not in correct paragraph for UK policy
* Improvement: additional css to prevent theme override of classic checkbox css
* Fix: don't show processing agreements notice if no privacy policy is generated by Complianz
* Fix: don't force banner width on top, bottom and fixed banners

= 4.6.4 =
* Improvement: Dismiss review notice with GET to prevent issues with dismissing
* Improvement: Rename updater class to prevent conflicts
* Improvement: Facebook / Twitter Smash Balloon integration added
* Fix: drop blocking of PayPal as third party

= 4.6.3 =
* Fix: load ACF Maps integration only when Google Maps is enqueued

= 4.6.2 =
* Fix: fallback for banner settings in case upgrade to new category banners didn't run successfully
* Fix: cmplz_set_cookie function called without expiration, causing a session expiration

= 4.6.1 =
* Fix: for the hidden categories banner, event was set too early, causing incorrect banner behaviour if UK used a different categories type.
* Fix: on opt-out only, preview didn't work because the gethovercolour function couldn't process an empty hex
* Fix: Contact form 7 recaptcha update patch
* Improvement: regex for double statistics implementation was not specific enough

= 4.6.0 =
* New: New Cookie banner variations with new checkbox options, accept all button, etc.
* Fix: revoke on legacy revoke button not revoking correctly
* Fix: in case of categories, paragraph text in cookie policy didn't match
* Improvement: updated EDD licensing code
* Improvement: vimeo with DNT=1 in the URL will not get blocked, as it's privacy friendly, non tracking.
* New: added Rate My Post integration
* New: added ACF (Advanced Custom Fields) integration for Google Maps

= 4.5.2 =
* Improvement: set cookie slug to default empty string to prevent conflict with de_DE plugin
* Fix: setCookie function at one point in code called without expiry, causing it to get set with session expiry

= 4.5.1 =
* Fix: User registration dependency
* Fix: consent management on UK policy not possible due to an incorrect condition
* Improvement: improve conditions and descriptions for selling data elements in legal documents and questions
* Improvement: jquery >3.x compatibility
* Improvement: Divi notice for Recaptcha
* Fix: correctly translate Complianz cookie retention
* Fix: some themes showing double checkboxes

= 4.5.0.1 =
* Fix: in opt out the banner was dismissed after first pageload

= 4.5.0 =
* Improvement: convert region array when enabling or disabling geo ip setting
* Improvement: support for retrieval of second party cookies
* Improvement: dropped deprecated wp.editor in favor of wp.blockEditor in Gutenberg block, added panelrows.
* Improvement: manage consent by category on Cookie Policy
* SSL verify enabled for license verification
* javascript sanitization https://github.com/rlankhorst/complianz-gdpr/issues/260 props pierrotevrard-idp
* Do not automatically enable a service integration after a cookiedatabase sync
* Get screencapture for youtube videoseries
* Catch not existing src in iframe in cmplzGetURLParam()
* Improvement: for the geo ip document redirect, redirect the "other" region to website's base region
* Fix: possible issue when custom editing the cookie policy content because of empty table cells.
* Fix: AMP plugin changed hooks, causing the integration not to work 100%
* Improvement: added AddToAny in the integrations list
* Fix: missing filter in integrations list props @orjhor
* Fix: language selecter services was called 'select {language} cookies';

= 4.4.2 =
* Improvement: pass type to Cookiedatabase: localstorage or cookie
* Fix: custom URL not saving
* Fix: {link} not replaced in new cookie template
* Fix: one function in TGMPA library not prefixed yet, causing conflicts with other libraries
* Fix: cookieFunction and collectedPersonalData sync across different languages https://github.com/rlankhorst/complianz-gdpr/issues/259. props pierrotevrard-idp

= 4.4.1 =
* Fix: improve integration with WP Google Maps plugin
* Fix: template override feature from theme not working https://github.com/rlankhorst/complianz-gdpr/issues/242 props xantek
* Fix: with line breaks in iframe element, https://github.com/rlankhorst/complianz-gdpr/issues/244 props pierrotevrard
* Fix: iframe replacement issue with linebreaks https://github.com/rlankhorst/complianz-gdpr/issues/246 props pierrotevrard
* Fix: purpose section not shown when privacy generation is enabled with UK only
* Improvement: Translatepress support
* Improvement: word "impressum" translatable

= 4.4.0 =
* Fix: OpenStreetMaps (OSM plugin) compatibility fix
* Fix: cookie blocker for ajax loaded content made conditional with a setting in the general settings
* Tweak: allow colon in URL field
* Updated libraries
* Added Impressum for Germany and Austria
* Tweak: optimization of cookiebanner database queries

= 4.3.5 =
* Fix: TGM compatibility fix
* Fix: when dismiss on scroll is used in the US in combination with the hide settings button, the settings button wasn't hidden immediately
* Fix: Improved blocked content activation on ajax loaded content
* Fix: Add try/catch for the strange situation that file_exists returns true while it's not there on wp engine.
* Fix: Mappres integration needed CSS update

= 4.3.4.1 =
* Fix: when user country is not in regions list, an incorrect region was returned, resulting in a PHP warning

= 4.3.4 =
* Fix: Mailchimp for CF7 compatibility
* Tweak: hide license key
* Tweak: added filter to allow to manipulate script output
* Fix: embedded analytics script triggered before consent because regex didn't match the script after PHPcs changes
* Fix: condition on Cookie Policy text field caused field not to be shown when both CCPA and PIPEDA apply
* Tweak: renamed TGM library classes to prevent conflicts with incorrectly implemented TGM libary in some themes

= 4.3.3 =
* Fix: the "all" region was skipped in a region check

= 4.3.2 =
* Fix: autoredirect not working correctly after Canada update
* Improvement: make pages creation an explicit user action
* MPDF package update
* removed divs in legal documents, in favour of p tags.
* Improved jquery activation script for iframes for smoother loading of iframe
* Improved cookie delete option: it will now archive, so won't get added again on new scan
* Improved handling of ajax loaded content in Cookie Blocker (Ultimate Member)
* Fix translation of banner items for multiple language configurations
* Fix: WPML changed index in supported languages from language_code to code

= 4.3.1 =
* Tested for PHP 7.4 issues
* Fix: in add menu step, when no disclaimer was selected, menus were shown double

= 4.3.0 =
* New: Supports the [WP Consent API] (https://wpconsentapi.org/).
* Fix: Type on legal document
* Tweak: filter to change the region dynamically

= 4.2.2 =
* Fix: don't fire cookie blocker when not needed.

= 4.2.1 =
* Fix: use filename sanitize function instead of sanitize_title() for proof of consent files
* Fix: Twitter placeholder string added incorrectly

= 4.2.0 =
* New: PIPEDA support (Canada)
* New: added option to disable all notices
* Tweak: CAOS integration improved
* Fix: remove limit on list of processing agreements
* Fix: bug when searching in Do Not Sell My Personal Information requests
* Fix: allow for private Vimeo video's, props @volkmar-kantor
* Tweak: added option to disable placeholders per plugin/service
* Tweak: Updated WCAG to v2.1
* Tweak: moved custom recaptcha css to integrations modules
* Tweak: added introduction tour
* Fix: user region and consenttype for not enabled regions should return "other", even when the user is from a supported region
* Tweak: made aria-label in cookie notice translatable
* Fix: check if array key exists in GTM4WP integration
* Fix: check existence of table before retrieving services
* Fix: IE11 support for blocked content notice, props @volkmar-kantor
* Tweak: Improved placeholder support for Twitter embed
* Fix: removed unintentional dot before not numbered paragraphs
* Fix: custom policy URL incorrectly caused a not 100% completeness

= 4.1.5 =
* Tweak: improved signature style on processing agreements
* Fix: proof of consent link not working when website title contained an ampersand
* Fix: duplicate function name in contact form 7/G1 Maps integration, and in GADWP and GTM4WP integration

= 4.1.4 =
* Fix: Google Analytics was not blocked correctly

= 4.1.3 =
* Fix: Google Tag Manager integration
* New: Google Tag Manager 4 WP integration
* Tweak: updated default banner colors
* Tweak: improved menu order and tab order on integrations page

= 4.1.2 =
* Tweak: When consent on anonymous statistics is enabled for Germany,  Hotjar anonymous version should also require consent
* Fix: add space in "web beacon"
* Tweak: No lazy loading for WP Rocket iframes
* Tweak: offer option to show link to cookiedatabase.org for cookies and services
* Fix: database error on new install because cookies were checked before table was initialized on first activation.
* Tweak: Wp Forms recaptcha integration
* Tweak: Mappress integration
* Fix: placeholder activation for non iframes not working correctly
* Fix: typo in privacy policies
* Tweak: removed some obsolete statements in privacy policies
* Tweak: changed blocked content notice in "accept marketing cookies"
* Tweak: added OSM plugin open streetmaps support
* Fix: is_amp_endpoint function check
* Tweak: moved css to separate plugin integrations
* Tweak: JetPack twitter integration
* Tweak: improved notice when uploads folder not writable
* Tweak: improved non functional and functional cookies check
* Fix: cron was wrapped in logged in check, preventing the cron from running

= 4.1.1 =
* Fix: multiple regions not processed correctly by cmplz_has_region() function

= 4.1.0 =
* New: AMP support
* Tweak: offer option to ask opt-in for statistics in Germany
* Fix: dismiss on timeout not working
* Fix: revoke on cookie policy when GEO ip enabled, US only
* Fix: when no region was selected, a string with empty region could appear in the dashboard.
* Tweak: dismiss the upgrade notice even when no changes are detected.
* Tweak: improved review notice
* Tweak: remove "unknown privacy link"
* Tweak: no need to opt in to cookiedatabase.org when no cookies
* Tweak: dedicated shortcode [cmplz-cookies] to enable users to inlude the cookies list only
* Tweak: adjusted accept all cookies notice in blocked content notice
* Tweak: improve activation of video scripts for smoother experience
* Tweak: drop notification on plugin updates. This function is already handled by the "new cookies" feature
* Tweak: removed double occurence of disqus
* Tweak: added dot behind every paragraph
* Tweak: improve region explanation to avoid confusion
* Tweak: added remove data on uninstall option
* Tweak: extend regex for iframe URL's to support brackets in URL's

= 4.0.5 =
* Add second party service
* Fix: privacy friendly settings got inverted for check if banner was needed for statistics only

= 4.0.4.1 =
* roll back change of moment when placeholder classes are removed.

= 4.0.4 =
* opt in to cookiedatabase.org

= 4.0.3 =
Fix: cookiedatabase sync not synchronizing third party services in multilingual environments
Improvement: improved error messages for sync

= 4.0.2 =
Fix: UK and US policies still used old cookie descriptions

= 4.0.1 =
Fix: banner saving when UK and EU both use categories

= 4.0.0 =
* Improvement: separate consenttype for UK
* Improvement: cookie information retrieved from cookiedatabase.org

= 3.2.4 =
* Tweak: Matomo stats script updates
* Improved javascript array merging method

= 3.2.3 =
* Fix: missing retain data statement in privacy policy
* Fix: missing translation strings
* Fix: not registering strings from cookie translation for multilanguage environments

* Fix: changed placeholder.html in blocked iframe source to "about:blank"

= 3.2.2 =
* Improvement: added option to unlink and customize the legal documents
* Improvement: structure improvements to integrations code
* Fix: UK Cookie Policy URL not added to cookie notice
* Fix: Some questions not showing in wizard for UK region
* Improvement: added integration for GEO My WP members list
* Improvement: added Forminator integration
* Improvement: added Beehive integration

= 3.1.2 =
* Fix: incorrectly forcing en_US language

= 3.2.0 =
* Improvement: added shortcodes to document list on dashboard page
* Improvement: do not activate cookie banner before wizard has completed
* Improvement: added banner loaded jquery hook
* Improvement: The United Kingdom is now a separate region with specific cookie consent management
* Improvement: Script center is now embedded under 'Integrations'. A more flexible approach to blocking and enabling scripts, plugins and services
* Tweak: Tag Manager does not require a categorical approach of cookies
* Tweak: Feedback in dashboard has been improved when changing regions
* Tweak: Stylesheet updates

= 3.1.1 =
* Fix: document wrapped in double div
* Fix: PHP warning caused by empty list of proof of consent documents
* Fix: if upload directory does not have writing permissions, generating the PDF files could cause an error

= 3.1.0 =
* Improvement: proof of consent page, which works as consent registration on settings change
* Improvement: added script dependency array, to enable scripts to fire in a certain order
* Improvement: extended placeholder support for non-iframes
* Improvement: added soft Cookie Wall

= 3.0.11 =
* Improvement: pixel caffeine support
* Fix: version stripping second digit for upgrade check

= 3.0.10 =
* Fix: on saving of settings, when Contact Form 7 is integrated using consent box, mail settings are reset
* Fix: Avia front end pagebuilder getting blocked by cookie blocker
* Fix: Lawfull => lawful
* Improvement: added custom jquery event to hook into cookie consent events
* Fix: set Google Analytics as not functional
* Fix: duplicate advertising cookies settings in US cookie policy
* Improvement: added PayPal cookies
* Improvement: added cc-revoke example
* Improvement: added helptext to explain email addresses are ofuscated
* Improvement: sanitizing of hex color in custom css
* Improvement: WP Google Maps integration
* Improvement: moved do not track me integration to filterable array
* Improvement: comma separated ip's supported in GEO ip
* Improvement: prevent policies from being generated when not activated in settings
* Improvement: pixelyoursite plugin support
* Improvement: notifications when cookie blocker is enabled, to make sure users understand the implications
* Improvement: dropped youronlinechoices as suggested service

= 3.0.9 =
* Improvement: change revoke button in cookie policy to button element
* Improvement: for Tag Manager, a suggestion to set up personalized ads

= 3.0.8 =
* Improvement: add option to configure your own cookie policy URL
* Fix: creating legal document page when none is available after region switch
* Improvement: W3C validator compatibility for documents
* Fix: javascript pattern not matching correctly, causing both text/plain and text/javascript scripts.
* Improvement: recommended action on Google Fonts
* Fix: Pass font color to cc-category class
* Fix: allow for content in iframes tags in regex pattern
* Fix: A/B tracking still in progress notice when only one banner left
* Fix: hide comment checkbox when WP personal data storage for comments is disabled
* Fix: hide security measures question when privacy policy not selected
* Improvement: more info on personalized ads configuration with Tag Manager

= 3.0.7 =
* Tweak: remove blocking of custom Google implementations, as it is not yet possible to reactivate them

= 3.0.6 =
* Fix: new regex did not exclude cmplz-native scripts from cookie blocker

= 3.0.5 =
* Fix: saving when saved data is not an array
* Fix: prevent force category for Tag Manager after switching back to GA
* Improvement: allow for Youtube video series URL
* Fix: several improvements for US documents
* Fix: table remove on plugin deletion
* Improvement: when marketing level category is selected, statistics category should not get consent
* Improvement: not scrolling to top when accepting

= 3.0.4 =
* Fix: expiry days not passed to cookie banner

= 3.0.3 =
* Fix: add href to accept button on cookie banner
* Improvement: [cmplz-accept-link text="accept cookies"] shortcode
* Fix: Cookie policy advertising and statistics cookies settings fix
* Fix: Not saving unchecked checkbox custom document css
* Improvement: Hide filter selects when a/b testing not enabled
* Fix: Privacy statement for eu notice when using US only
* Fix: If page is deleted, stored cookiepage url could be empty
* Fix: When switching settings for advertising cookies, output might show both advertising and non-advertising paragraph
* Fix: No cookie banner mention in cookie policy when no banner is needed
* Fix: Cookie blocker was not activated when only statistics required a cookie warning
* Fix: selecting no thirdparty services or cookies could lead to double activation of statistics
* Fix: Selecting US as target region in some cases did not fire the default consent which is allowed for US privacy regulations

= 3.0.2 =
* Fix: Gravity forms checkbox not generated correctly
* Fix: Brand color not updating in cookiebanner
* Fix: US Cookie policy not showing correct purposes
* Fix: Incomplete cookie causing not reaching 100% without notice
* Fix: Enabling TM categories
* Fix: Elementor forcing lineheight of 0 in embeds

= 3.0.1 =
* Fix: hook for DB upgrade moved to an earlier one.

= 3.0.0 =
* Fix: removed google plus integration, as it's discontinued
* Fix: prevent saving from document URL's on autosave and revisions
* Fix: moved linkedin from script blocked list to async loaded list
* Fix: default region is now one of the selected regions in the wizard.
* Fix: when localstorage is empty, empty array could cause PHP error during cookie scan
* Fix: excluded elementor_font post_type from scan
* Fix: As elementor uses the classic shortcodes in Gutenberg, an exception should be made for Elementor when inserting default pages
* Improvement: completely rewritten video blocking and placeholder code, which should reduce possible issues
* Improvement: added HappyForms integration to enable recaptcha initialization.
* Improvement: hide nag notices from other plugins on Complianz pages.
* Improvement: dropped hook on save_post and insert_post which triggered new scan.
* Improvement: added option to stop scanning every week with define('CMPLZ_DO_NOT_SCAN');
* Improvement: added warning when a cookie is not completely filled out
* Improvement: extended support for different types of IP detection on servers
* Improvement: moved string translation support for polylang and WPML to core
* Improvement: Added option to disable adding placeholder HTML to video's
* Improvement: Added plural for Social Media statement in Cookie Policy
* Improvement: Added exception for Non Personalized Ads in advertisement section in Wizard and in Cookie Policy
* Improvement: Added escaping to outputted javascript, all scripts moved to templates
* Improvement: Moved cookie banner settings to separate table and object
* Improvement: limit ajax requests to a/b testing and multiple regions

= 2.1.8 =
* Tweak: improve escaping of css in document html output
* Tweak: improved Elementor and Gutenberg compatibility for youtube video activation after consent is given

= 2.1.6 =
* Fix: responsive video adjustments
* Fix: z index for blocked content text too high, causing it to float over the banner

= 2.1.5 =
* Feature: WP Forms integration
* Feature: Dailymotion placeholder support
* Improvement: prevent activation for PHP <5.6 and WP < 4.6
* Improvement: clean up placeholder folder every month
* Improvement: regex did not recognize google maps URL because of exclamation mark usage
* Improvement: higher quality placeholder image
* Improvement: when Tag Manager is selected, categories is enabled. To make this more explicit help text is added and the button disabled.
* Improvement: when saving settings in the cookie warning settings, we now maintain the region selection state
* Improvement: download video placeholders to own site to make sure Youtube and vimeo cannot track the users
* Improvement: placeholder img aspect ratio is used to resize the placeholder container div
* Fix: several css styling issues for the center theme with categories: color inheritance of label, display
* Fix: css styling for border with edgeless theme
* Fix: when user states no cookies are used, even if the scan detects them, no cookie banner will be shown, as per the user's wishes.
* Fix: FitVids compatibility for fluid video display
* Fix: empty locales array for cookie cache could cause PHP warning

= 2.1.4 =
* Fix: incorrect PHP opening causing input fields malform on some setups

= 2.1.3 =
* Tweak: option to save A/B testing reports
* Tweak: Purpose description for EU not needed in all situations
* Tweak: Split US and EU cookie banner text, to be configured separately
* Tweak: created a setting to configure blocked content text
* Tweak: Changed check on WP_DEBUG to SCRIPT_DEBUG for scripts
* Tweak: added placeholder to blocked iframes to prevent reloading to homepage
* Fix: In US cookie policy, "we ask consent for statistics" is removed
* Fix: In EU cookie policy, "we ask consent for statistics" is shown conditionally, based on anonymization settings of the statistics tool
* Tweak: added upgrade links
* Fix: empty localstorage and cookie array causing an warning
* Tweak: close button on modal help windows
* Fix: when choosing to configure your statistics yourself instead of matomo/analytics/tagmanager, a warning kept showing in the dashboard.
* Tweak: accept button not fitting in banner when using a very long decline text
* Fix: for paragraphs with both a field condition and a callback condition, the code did not enforce both conditions
* Fix: clang redirect to en locale not adjusted for Gutenberg block recognition
* Fix: if region is not selected, cookiebanner won't show for that region anymore

2.1.2
* Fix: extra line break in readme causing readme not to get parsed correctly

= 2.1.1 =
* Fix: allow reinitialisation of recaptcha v3 in Contact Form 7
* Tweak: prevent dns prefetch for blocked URL's
* Tweak: option to dismiss on scroll or dismiss on timeout
* Tweak: option to share more details on the automated processing
* Tweak: added secure flag to cookie set function when url is https

= 2.1.0 =
* Tweak: Gutenberg blocks from Complianz Privacy Suite
* Tweak: integration with https://wordpress.org/plugins/wp-donottrack/

= 2.0.9 =
* Tweak: allow users to keep their own, custom statistics tracking
* Fix: Revoke button text not defined with US only setup
* Tweak: Do Not Track now optional
* Tweak: Do No Track is not used in the percentage calculation anymore
* Tweak: new modal tooltips

= 2.0.8 =
* Fix: remaining time calculation bug
* Fix: privacy statement url to own site in cookie policy not generated correctly
* Tweak: support youtube and vimeo placeholders
* Tweak: uninstall not removing all data. This can be done explicitly in the settings as of now.
* Fix: custom strings not translated properly

= 2.0.7 =
* Translation update

= 2.0.6 =
* Tweak: added some new cookies to the database
* Tweak: changed site_url into home_url in the documents output
* Tweak: add support for blocking of instagram cookies
* Tweak: dropped pattern restriction on phonenumbers, as there are too many local differences.
* Fix: third party privacy statements not inserted in cookie policy
* Tweak: less strict policies for websites who do not target California
* Fix: privacy policy URL's not showing in cookie policy

= 2.0.5 =
* Tweak: changed site_url into home_url in the documents output
* Tweak: added support for partial matches on cookies with the dynamic part in the middle of the string
* Tweak: added some new cookies to the database
* Tweak: ajax call for user data only on first visit

= 2.0.4 =
* Fix: Cookie blocker inserting class within escaped strings.

= 2.0.3 =
* Moved geoip database file outside plugin, as custom extension .mmdb from MaxMind was causing update issues by servers not deleting this file.

= 2.0.2 =
* Fix: Tag manager events not firing outside selected regions
* Tweak: set default region after upgrade from pre-2.0 version
* Fix: showing empty privacy link in US cookie banner
* Fix: count nr of forms, when forms option empty throwing an error.
* Tweak: split checked docs date from edited docs date

= 2.0.1 =
* Tweak: first reported cookies added to the cookie database
* Tweak: Set a default target region if not existing yet.

= 2.0.0 =
* Tested up to WP 5.0
* Tweak: updated Geo IP database to latest
* Tweak: Document styling als used in PDF's and post view.
* Tweak: Dropped Youtube "nocookie" support, Youtube places cookies after first interaction, without consent
* Tweak: feedback on active adblockers or anonymous window during scan
* Tweak: user locking of the wizard, preventing multiple users from editing the wizard at the same time
* Tweak: improvements in visual feedback on validation
* Tweak: user interface design
* Fix: bug in dataleak email sending
* Feature: reporting of unrecognized cookies
* Feature: option to export and import options
* Feature: reset to factory defaults: clear all settings
* Feature: Select both US and EU as target region
* Feature: CaCPA support
* Feature: US privacy statement
* Feature: Do Not Sell My Personal Information page
* Feature: Do Not Sell My Personal Information opt out form & dashboard
* Feature: US Processor agreement wizard
* Feature: US Security Breach notification wizard
* Feature: US dedicated cookie warning
* Feature: COPPA childrent's privacy statement

= 1.2.6 =
* Fix: missing space in privacy statement, incorrect reference to cookie statement

= 1.2.5 =
* Tweak: added monsterinsights integration
* Fix: Privacy Policy did not show the correct paragraph on sharing with other parties

= 1.2.4 =
* Tweak: added a hide revoke button option in the settings
* Tweak: moved statistics script to overridable templates, and included them using action hooks, to make overriding more easy.
* Fix: cookie policy text was not 100% matched when the categories option was selected for the banner.
* Fix: tracking of statistics added new user when the status was not changed.
* Fix: center revoke button not in same style as other revoke buttons

= 1.2.3 =
* Fix: centered banner introduction caused the revoke button to show very large for top position banners.

= 1.2.2 =
* Fix: when no social media was found, this could result in an error on showing the scan results

= 1.2.1 =
* Tweak: show social media and third party services from actual detected list, not from wizard.
* Tweak: calculation of best performer without no_warning status
* Fix: no_choice status not tracked

= 1.2.0 =
* Fix: deleted cookies were added again on the next scan
* Tweak: script center added below menu for fast editing
* Tweak: AB testing
* Tweak: Added new banner position: centered
* Tweak: Added categories in cookies
* Tweak: Added new template: minimal

= 1.1.11 =
* Fix: cookie warning with geo ip and caching cached a user requirement, while the site requirement needs to be cached.
* Tweak: email obfuscation in legal documents
* Tweak: cookie warning a/b testing

= 1.1.10 =
* Fix: statistics should also be loaded when do not track is enabled
* Fix: moved cookie policy change date to separate variable
* Tweak: improved security of cookie enabling script

= 1.1.9 =
* Fix: empty contact key in saving data
* Tweak: overlay over dashboard when wizard is not completed yet, to force using wizard
* Fix: compile_statistics_more_info usage in privacy policy
* Tweak: brand color not required anymore
* Tweak: full integration of Matomo in Complianz GDPR

= 1.1.8 =
* Fix WPML/polylang translation bug

= 1.1.7 =
* Fix: count of warnings bug in wizard completed percentage

= 1.1.6 =
* Added statistics for cookie warnings
* Added Google Fonts and ReCaptcha to third party list
* Tweaked Cookie Policy
* Improved geoip for cached websites
* Added custom CSS option and advanced editing options to cookie banner

= 1.1.5 =
* Tweak: cookie changes not adding +one nags, only in the dashboard
* Tweak: custom plugin texts moved to addendum

= 1.1.4 =s
* Fix: complete rework of third party cookie blocker, dropped domDocument in favor of regex

= 1.1.3 =
* Tweak: use accept text in cookie policy

= 1.1.2 =
* Tweak: added css styles for legal documents
* Tweak: added option to add consent box to CF 7 and Gravity forms
* Fix: several bugfixes
* Tweak: improved feedback on dataleak report
* Tweak: added emailing capability for dataleak reports
* Tweak: added push down style to cookie warning
* Tweak: added Sumo to third party blocked scripts

= 1.1.1 =
* Tweak: updates wizard complete texts
* Tweak: updated known cookies list

= 1.1.0 =
* new dashboard
* added check if consent checkbox is needed on forms
* integrated wp erase personal data and wp export data
* phone numbers not required anymore
* added a < PHP 5.6 warning
* improved dataleaks and dataprocessing

= 1.0.9 =
* Fix: change of textdomain
* Fix: output escaping of html strings
* Fix: scan freezing when http URL's loaded over https.

= 1.0.8 =
* Fix: cookieblocker removed script in incorrect way, causing a php error
* Tweak: set page as processed before the request is made during scan
* Fix: pre 4.9.6 version of wp could not show admin pages due to privacy capability not existing

= 1.0.7 =
* Tweak: complete block of third party scripts until user as accepted.
* Tweak: respect Do Not Track setting in browsers

= 1.0.6 =
* Tweak: added menu selection as option in the wizard

= 1.0.5 =
* Tweak: Improved plugins privacy policy additions: making it editable
* Tweak: hide settings popup for cookie warning on mobile, with revoke link in cookie policy
* Tweak: improved dismiss and revoke functionality
* Fix: some bugs in dataleak decision tree

= 1.0.4 =
* Added scan for social media widgets and buttons

= 1.0.3 =
* Fix: retention period not correctly shown in privacy statement

= 1.0.2 =
* optimized cookie scan

= 1.0.1 =
* Translation fixes

= 1.0.0 =

== Upgrade notice ==

== Screenshots ==


== Frequently asked questions ==
