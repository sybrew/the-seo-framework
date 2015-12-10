=== The SEO Framework ===
Contributors: Cybr
Donate link: http://theseoframework.com/
Tags: open graph, description, automatic, generate, generator, title, breadcrumbs, ogtype, meta, metadata, search, engine, optimization, seo, framework, canonical, redirect, bbpress, twitter, facebook, google, bing, yahoo, jetpack, genesis, woocommerce, multisite, robots, icon, cpt, custom, post, types, pages, taxonomy, tag, sitemap, sitemaps, screenreader, rtl
Requires at least: 3.6.0
Tested up to: 4.4.0
Stable tag: 2.4.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

The SEO Framework makes sure your Website's SEO is always up-to-date without any configuration needed. It also has support for extending.

== Description ==

= The SEO Framework =

**The lightning fast all in one automated SEO optimization plugin for WordPress**

> <strong>This plugin strongly helps you create better SEO value for your content.</strong><br />
> But at the end of the day, it all depends on how entertaining or well-constructed your content or product is.
>
> No SEO plugin does the magic thing to be found instantly. But doing it right helps a lot.<br />
> The SEO Framework helps you doing it right. Give it a try!
>
> The Default Settings are recommended in the SEO Settings page. If you know what you're doing, go ahead and change them! Each option is also documented.

= What this plugin does, in a few lines =
* Automatically configures your SEO including the title, description, etc...
* Allows you to adjust the SEO.
* Shows you how to improve your SEO with a beautiful SEO bar for each post/page/tag/category.
* Helps your site get ranked better.
* Helps your pages get shared more beautiful through e.g. Facebook and Twitter.
* Allows plugin authors to easily extend this plugin.
* Supports custom post types, like WooCommerce and bbPress.
* Automatically upgrades from Genesis SEO.
* Allows upgrade from other SEO plugins using a tool.

*Read **Transferring SEO Content using SEO Data Transporter** below for transferring instructions.*

= Numbers don't lie =
Optimizing SEO is a fundamental process for any website. So we try to be non-intrusive with The SEO Framework.
The SEO Framework is byte and process optimized on PHP level, with each update the optimization is improved when possible.

* This plugin is written with massive and busy (multi-)sites in mind.
* This plugin is 197% to 867% faster compared to other popular SEO plugins.
* This plugin consumes 177% to 260% fewer server resources than other popular SEO plugins.
* 15% fewer database interactions (numbers may vary on this one depending on plugin compatibility).
* 100% fewer advertisements. Let's keep it that way.

= Completely pluggable =
The SEO Framework also features pluggable functions. All functions are working and can be called within the WordPress Loop.
We have also provided an API documentation located at [The SEO Framework API Docs](http://theseoframework.com/docs/api/).

= Still not convinced? Let's dive deeper =

**This plugin automatically generates:**

* Description
* Title, with super-fast 'wrong themes' support (so no buffer rewriting!)
* og:image
* og:locale
* og:type
* og:title
* og:description
* og:url
* og:site_name
* Canonical, with full WPMUdev Domain Mapping and HTTPS support
* LD+Json, extended search support for Google Search and Chrome
* LD+Json, Knowledge Graph (partially)
* LD+Json, Breadcrumbs
* Publishing and editing dates
* Link relationships, with full WPMUdev Domain Mapping and HTTPS support
* Various Facebook and Twitter Meta tags
* And a Sitemap

**This plugin allows you to manually set these values for each post, page and taxonomy:**

* Title
* Description
* Canonical
* Robots (nofollow, noindex, noarchive)
* Redirect, with Multisite spam filter
* Local on-site search settings

**This plugin allows you to adjust various site settings:**

* Title Separator
* Title Additions Location
* Auto Description Output
* Robots for Archives
* Robots for the whole site
* Home Page Description, Title, Tagline and various other options
* Facebook Social integration
* Twitter Social integration
* Open Graph Meta output
* Shortlink tag output
* Post publishing time output
* Link relationships
* Google/Bing Webmaster verification
* Google Knowledge Graph
* Sitemap
* Robots.txt
* Etc.

**This plugin helps you to create better content, at a glance. By showing you:**

* If the title is too long, too short and/or automatically generated.
* If the description is too long, too short and/or automatically generated.
* If the description uses some words too often.
* If the pages are indexed.
* If the page is indexed, redirected, followed and/or archived.
* If your blog is public.

**We call this The SEO Bar. Check out the [Screenshots](https://wordpress.org/plugins/autodescription/screenshots/#plugin-info) to see how it helps you!**
**Screenshots are a little outdated and will be updated soon.**

> This plugin is fully compatible with the [Domain Mapping plugin by WPMUdev](https://premium.wpmudev.org/project/domain-mapping/) and [Domain Mapping plugin by Donncha](https://wordpress.org/plugins/wordpress-mu-domain-mapping/).
> This plugin is now also compatible with all kinds of custom post types.
> This will **prevent canonical errors**. This way your site will always be correctly indexed, no matter what you use!
>
> This plugin is also completely ad-free and has a WordPress integrated clean layout. As per WordPress.org plugin guidelines and standards.
>
> No initial configuration is needed. Either Network Activate this or use it on a single site.

= Caching =

This plugin's code is highly optimized on PHP-level and uses variable, object and transient caching. This means that there's little extra page load time from this plugin, even with more Meta tags used.
A caching plugin isn't even needed for this plugin as you won't notice a difference, however it's supported wherever best suited.

**If you use object caching:**
The output will be stored for each page, if you've edited a page the page output Meta will stay the same until the object cache expires. So be sure to clear your object cache or wait until it expires.

= Compatibility =

**Basics:**

* Full internationalization support through WordPress.org. Dutch is maintained by the plugin author.
* Extended Multibyte support (CJK).
* Right to Left support.
* Complete screen-reader support for accessibility.
* Admin: Posts, Pages, Taxonomies, Terms, Custom Post Types.
* Front-end: Every page, post, taxonomy, term, custom post type, search request, 404, etc.

**Plugins:**

* W3 Total Cache, WP Super Cache, Batcache, etc.
* WooCommerce: Products, Product Categories and Product Tags.
* Custom Post Types, (all kinds of plugins) with automatic integration.
* WPMUdev and Donncha's Domain Mapping with full HTTPS support.
* WPMUdev Avatars for og:image and twitter:image if no other image is found.
* bbPress: Forums, Topics, Replies.
* BuddyPress.
* AnsPress Questions and Pages, also Canonical errors have been fixed.
* StudioPress SEO Data Transporter for Posts, Pages, Taxonomies and Terms.
* WPML, URL's, sitemap and per-page/post SEO settings. (The full and automated compatibility is being discussed with WPML.)
* qTranslate X, URL's, sitemap and per-page/post SEO settings (through shortcodes by set by qTranslate X).
* Most popular SEO plugins, let's not get in each other's way.
* Jetpack modules: Custom Content Types (Testimonials, Portfolio), Infinite Scroll, Photon.
* Many, many others, yet to confirm.

**Themes:**

* All themes.
* Genesis & Genesis SEO. This plugin takes all Post, Page, Category and Tag SEO values from Genesis and uses them within The SEO Framework Options. The easiest upgrade!

**Caches:**

* Opcode
* Page
* Object
* Transient
* CDN

If you have other popular SEO plugins activated, this plugin will automatically prevent SEO mistakes by deactivating itself on almost every part.
It will however output robots metadata and og:image, among various other things which are bound to social media.

= Transferring SEO data using SEO Data Transporter =

Because this plugin was initially written to extend the Genesis SEO, it uses the same option name values. This makes transferring from Genesis SEO to The SEO Framework work automatically.

> If you didn't use Genesis SEO previously, Nathan Rice (StudioPress) has created an awesome plugin for your needs to transfer your SEO data.
>
> Get the [SEO Data Transporter from WordPress.org](https://wordpress.org/plugins/seo-data-transporter/).
>
> Usage:<br />
> 1. Install and activate SEO Data Transporter.<br />
> 2. Go to the <strong>SEO Data Transporter menu within Tools</strong>.<br />
> 3. Select your <strong>previous SEO plugin</strong> within the first dropdown menu.<br />
> 4. Select <strong>Genesis</strong> within the second dropdown menu.<br />
> 5. Click <strong>Analyze</strong> for extra information about the data transport.<br />
> 6. Click <strong>Convert</strong> to convert the data.
>
> The SEO Framework now uses the same data from the new Genesis SEO settings on Posts, Pages and Taxonomies.

= Other notes =

**This plugin copies data from the Genesis SEO Meta, this means that when you use Genesis, you can easily upgrade to this plugin without editing each page!**

*Genesis SEO will be disabled upon activating this plugin. The new SEO Settings page adds extended SEO support.*

***The Automatic Description Generation will work with any installation. But it will exclude shortcodes. This means that if you use shortcodes or a page builder, be sure to enter your custom description or the description will fall short in length.***

***The home page tagline settings don't have any effect on the title output if your theme's title output is not written according to the WordPress standards, which luckily are enforced strongly on new WordPress.org themes since recently.***

> <strong>Check out the "[Other Notes](https://wordpress.org/plugins/autodescription/other_notes/#Other-Notes)" tab for the API documentation.</strong>

*I'm aware that radio buttons lose their input when you drag the metaboxes around. This issue will be fixed in WordPress 4.5.0 according to the milestones.*
*But not to worry: Your previous value will be returned on save. So it's like nothing happened.*

== Installation ==

1. Install The SEO Framework either via the WordPress.org plugin directory, or by uploading the files to your server.
1. Either Network Activate this plugin or activate it on a single site.
1. That's it!
1. Let the plugin automatically work or fine-tune each page with the metaboxes beneath the content or on the taxonomy pages.
1. Adjust the SEO settings through the SEO settings page if desired. Red checkboxes are rather left unchecked. Green checkboxes are default enabled.

== Screenshots ==
1. This plugin shows you what you can improve, at a glance. Try to aim for all green!
2. Hover over any of the SEO bar's color to see how you can improve the page's SEO.
3. The Post/Page SEO settings box. This box is also neatly implemented in Categories and Tags. (Aim for 145 to 155 characters, screenshot is outdated.)
4. The SEO settings page Title Settings. Also shows you a glance of the Robots Meta Settings.
5. The SEO settings page Home Page Settings.

== Frequently Asked Questions ==

= Is The SEO Framework Free? =

Absolutely! It will stay free as well, without ads or nags!

= I have a feature request, I've found a bug, a plugin is incompatible... =

Please visit [the support forums](https://wordpress.org/support/plugin/autodescription) and kindly tell me about it. I try to get back to you within 48 hours. :)

= I am a developer, how can I help? =

The SEO Framework is currently a one-man project. However, any input is greatly appreciated and everything will be considered.
Please leave feature requests in the Support Forums and I will talk you through the process of implementing it if necessary.

= I'm not a developer, how can I help? =

A way of donating is available through the donation link on the plugin website.
However, you can also greatly help by telling your friends about this plugin :).

= I want to remove or change stuff, but I can't find an option! =

The SEO Framework is very pluggable on many fields. Please refer to the [Other Notes](https://wordpress.org/plugins/autodescription/other_notes/).
Please note that a free plugin is underway which will allow you to change all filters from the dashboard. No ETA yet.

= No ads! Why? =

Nope, no ads! No nags! No links! Never!
Why? Because I hate them, probably more than you do. :(

I also don't want to taint your website from the inside, like many popular plugins do.
Read more about this on the [Plugin Guidelines, Section 7](https://wordpress.org/plugins/about/guidelines/).

***This plugin does leave one link to theseoframework.com in the plugin activation page. But that's OK, right? <3***

Advertisements are made to control your behavior, slowly and certainly you'll be persuaded by them, even if you don't like the product behind it. Ew! D:

***But how do you make a living?***

Currently, The SEO Framework is non-profit.
This plugin was first released to the public in March 15th, 2015. From there it has grown from 179 lines of code, to more than 14300 lines.
With over 540,000 characters of code written, this plugin is absolutely a piece of art in my mind.
And that's what it should stay, (functional) art.
I trust that people are good at heart and will tell their friends and family about the things they enjoy the most, what they're excited about, what they find beneficial or even beautiful.

With The SEO Framework I try to achieve exactly that. It's made with <3.

= Does this plugin collect my data? =

Absolutely not! Read more about this on the [Plugin Guidelines, Section 7](https://wordpress.org/plugins/about/guidelines/).

= Premium version? =

Nope! Only premium extensions. These are being developed.

= If a premium extensions is released, what will happen to this plugin? =

This plugin is built to be an all-in-one SEO solution, so:

* No advertisements about the premium extensions will be placed within this plugin.
* No features will be removed or replaced for premium-only features.
* The premium extensions will most likely only be used for big-business SEO. Which are very difficult to develop and which will confuse most users anyway.

= I've heard about an extension manager, what's that? =

Currently it's not available. When it is, it will allow you to download and activate extensions for The SEO Framework. It will support both multisite and single-site and the registration will be based on the Akismet plugin.

= I've heard that X SEO plugin does Y, why doesn't The SEO Framework do Y? =

I have no idea what you're talking about! What's X and what's Y? Silly :3.
Please be more elaborate in [the support forums](https://wordpress.org/support/plugin/autodescription), let's get Y working in The SEO Framework (if reasonable)!

= The sitemap doesn't contain categories, is this OK? =

This is not a problem and is currently done so by design to safe processing power. Search Engines love crawling WordPress because its structure is consistent and well known.
The lack of categories in the sitemap is currently extensively covered through a breadcrumb script output on every post which has a category. This way Search Engines will know of the existence of the category and will index it.

= What's does the application/ld+json script do? =

The LD+Json scripts are Search Engine helpers which tell Search Engines how to connect and index the site. They tell the Search Engine if your site contains an internal search engine, what sites you're socially connected to and what page structure you're using.

= My home page title is different from the og:title, or doesn't do what I told it to. =

The theme you're using is using outdated standards and is therefore doing it wrong. Inform your theme author about this issue.

**This is very, very bad for your website's SEO value.**

Give the theme author this link: https://codex.wordpress.org/Title_Tag
And this link: https://make.wordpress.org/themes/2015/08/25/title-tag-support-now-required/

And give the theme author these pieces of code:
`//* functions.php
add_theme_support( 'title-tag' );`

`//* header.php
if ( ! function_exists( '_wp_render_title_tag' ) ) {
	//* Below WordPress 4.1 compatibility
	function theme_slug_render_title() {
		/**
		 * Please, don't add any parameters to the wp_title function, except the empty one provided. Don't 'beautify' the title: It's bad for SEO.
		 * Don't add anything else between the title tag. No blogname, nothing.
		 */
		?><title><?php wp_title(''); ?></title><?php
	}
	add_action( 'wp_head', 'theme_slug_render_title' );
}`
If you know your way around PHP, you can speed up this process by replacing the `wp_title()` function with `wp_title('')` within `header.php`.

= The meta data is not being updated, and I'm using a caching plugin. =

All The SEO Framework's metadata is put into Object cache when a caching plugin is available. The descriptions are put into Transients. Please be sure to clear your cache.
If you're using W3 Total Cache you might be interested in [this free plugin](https://wordpress.org/plugins/w3tc-auto-pilot/) to do it for you.

= Ugh, I don't want anyone to know I'm using The SEO Framework! =

Aww :(
Oh well, here's the filter you need to remove the HTML tags saying where the Meta tags are coming from:
`add_filter( 'the_seo_framework_indicator', '__return_false' );`

= I'm fine with The SEO Framework, but not with you! =

Well then! D: We got off on the wrong foot, I guess..
If you wish to remove only my name from your HTML code, here's the filter:
`add_filter( 'sybre_waaijer_<3', '__return_false' );`

= I want to transport SEO data from other plugins to The SEO Framework, how do I do this? =

Please refer to [SEO Data Migration](http://theseoframework.com/docs/seo-data-migration/).

Transporting from The SEO Framework to other plugins currently isn't supported for Terms and Taxonomies.
If you wish to export data from The SEO Framework, please poke StudioPress with the request and tell them to look for 'admeta'.

== Changelog ==

= 2.4.3 - ?? =

**For Everyone:**
/
* Added: Title pagination indication, so you won't have duplicated titles anymore on Categories and paginated pages. This only works on themes that are doing it right, and is shown within the Open Graph and Twitter titles on any theme.
* TODO Fixed: Duplicate entires within WPML sitemap's are now correct again.
* Fixed: The Knowledge Graph now outputs your organisation or personal name correctly again.

**For Developers:**
/
* Added: New filters.
* Improved: `the_seo_framework_sitemap_custom_posts_count` now doesn't fetch any posts when set to `0` or `false`-ish.
* TODO Changed: Invalidated LD+Json transient cache key because of the fix. The expired cache will be flushed automatically.

= 2.4.2 - Canon in C =

**Summarized:**

This update is harmonizing canonical URL's in special situations. This includes complete support with WPML and qTranslate X, and on complex mutiple certificate MultiSite installations the canonical URL is now always correct when-and where ever you go.

Moreover, I present to you with pride, canonical URL's have been put into a lovely breadcrumb script. Where the sitemap lacks, the breadcrumb script makes up for it, and Google will better structure your website in the search results; this also informs the visitor of the structure of your pages before entering your website.

Among these changes, a few functions have been added to the options API to cover very special cases for every developer to use.

To top it off, this plugin is now also 150% to 500% times faster on the front page! Thanks to added transient caching methods on the LD+Json scripts.

**SEO Tip of the Update:**

* Canonical URLs are the URLs that lead to exactly where a page is located. It's very important for Search Engines to find the right page, to prevent duplicated content mistakes.
* The Search Engine will therefore always follow the canonical URL and will most likely ignore the current page if the canonical URL points to a page other than the current page.
* For this reason, the canonical URL within The SEO Framework will look for many variables before making one, with this update even more so.
* This concludes that it's advised not to touch the Custom Canonical URL unless you're absolutely certain that Search Engines should look for another webpage.

**For everyone:**

* Added: Breadcrumbs LD+Json script on categorized posts and child pages. This makes sure Google knows what category a post belongs to, or what page a page is a child of, even more structured than regular breadcrumbs. This can be removed through a filter.
* Added: Added more than one index-able category for a post? Now Google will know.
* Added: Complete Canonical URL support for qTranslate X.
* Added: Basic Sitemap Support for qTranslate X, it will create a sitemap for each of the languages if `Hide URL Language information` is set to off. It will generate one for the current language it finds otherwise and will cache it for a week or until post update.
* Added: Taxonomies and Terms Automated Descriptions now also try to fetch the custom Title.
* Added: The LD+Json scripts are now cached inside a transient for each page.
* Fixed: The Canonical URL within Posts and Pages are now correct again when using WPMUdev Domain Mapping and when you are within the original's domain dashboard.
* Fixed: The Canonical URL on the front end now doesn't output the incorrect scheme when using HTTPS anymore when the subdomain is HTTPS but the Mapped Domain is HTTP when using Domain Mapping by WPMUdev.
* Fixed: When using Donncha's Domain Mapping, the scheme will now always be correct.
* Fixed: The sitemap URLs now shows language domain URLs correctly again when using WPML.
* Fixed: Shortlink URL's no longer includes the language base when using WPML or qTranslate X.
* Fixed: The manual Canonical URL set for posts or pages are no longer reflected on the contained archives if the set post or page is the latest post or page.
* Fixed: The Featured Image set for posts or pages are no longer reflected on the contained archives for the social image Meta tags if the set post or page is the latest post or page.
* Improved: URL sanitation.
* Improved: No more home page cache keys are being generated on 404 pages.
* Improved: LD+Json search script, it now outputs your website's name in custom search if newly registered.
* Removed: Canonical URL on 404 pages. Not only was it unnecessary, but it was also wrong.
* Note: All sitemap transients will be invalidated upon this update to remain a consistent experience, they will be cleaned up automatically.

**For developers:**

* Added: New filters.
* Added: New functions for the options API.
* Added: New actions action listeners in page/taxonomy post boxes.
* Added: New useful function `AutoDescription_Generate::set_url_scheme()`. It's WordPress' core function without filter and a negligible nanosecond optimization.
* Added: New useful function `AutoDescription_Generate::get_relative_term_url()` for fetching archive urls.
* Added: URL input debugging when using debug 'more'.
* Added: Output debug in source only with a new constant. This still outputs debug information in the title tag, so be careful.
* Revised: Shortlink URL. It now uses a more static way of making one, instead of relying on `home_url()`.
* **Reworked:** `AutoDescription_Generate::the_url()` was starting to get smelly because of compatibility with so many plugins and types of pages. All parameters except the first two have been put into an argument array. Please note that the `parameters` will output a `_doing_it_wrong` notice if your input call is invalid. The code will continue to run normally but will ignore your parameters set.
* Revised: All title functions can now fetch the title from external posts.
* Cleaned up code.

**Note:**

* I've seen websites using CloudFlare combined with a relative scheme URL layer of some sort. This won't hurt your site, unless you mainly prefer using HTTPS.
* To prevent this from happening, I've created a filter which will force the https scheme. `the_seo_framework_canonical_force_scheme'.
* Please note that the CloudFlare layer, or any other early scheme overriding plugin will prevent the filter from working.
* The filter is quite simple, and can be found [here, heading "Since 2.4.2"](https://theseoframework.com/docs/api/filters/).
* Don't know what Canonical does? Then let The SEO Framework figure it all out for you :).
* What's better than this filter? A global 301 redirect from https to http, or vice versa. Consult with your hosting provider.

**About the breadcrumb script:**

* Read [all about the breadcrumb script](https://developers.google.com/structured-data/breadcrumbs?hl=en).
* It's a heavy process, it even tripled the output time of the meta on some pages. This is because each individual LD+Json input item has to be sanitized.
* Therefor it has been cached, simultaneously reducing this plugin's load time on the front-page by almost 100% caused by the search and Knowledge Graph script.
* Because WordPress doesn't have a one-way does it structure; this is truly something unique :). It has been tested on many sites with different parameters.
* Posts with multiple categories will have multiple scripts output.
* Posts with multiple categories of the same tree will have one script output per tree.
* Pages will always have at most one script output.
* Test categorized posts and pages with a tree [here](https://developers.google.com/structured-data/testing-tool/). Let me know if you find an error under `BreadcrumbList`, thank you very much in advance!
* This feature will not do anything yet with CPT, since they're not predictable. I will take a look at them in the future, because I really believe Portfolio's and Forums should support this.

= 2.4.1 - Update This Is =

**Summarized:**

With a great influx of users, a great amount of use case scenarios have been revealed. This update makes sure all known scenarios are covered.

First and foremost, I want to thank everyone for their support and trust in this plugin! I also want to thank everyone who has helped me discover hard to find bugs and their ideas for improvements.

This update brings many fixes of previously undiscovered ground. Most importantly, when your theme is doing it wrong and places the blog name on the left of the title, the title will now be correctly displayed.

Terms and Taxonomies' SEO bar is now output correctly. And many more Custom Post Types are now also automatically supported, including Jetpack Custom Content Types.

Other fixes include that trashed or private Custom Post Types are now no longer added to the Sitemap and the transient cache on enormous MultiSite networks and very large blogs built upon very old WordPress installations are now working on various post types.

Enjoy using AnsPress, that's great! The SEO Framework completely supports AnsPress too!

A few other minor changes have taken place, one of them is that the SEO Bar now highlights the words too many for the description.

**SEO Tip of the Update:**

* Navigation is very important for your website. Depending on your content, create an easy menu on what visitors need the most.
* Do not depend on your sitemap too much, if a user can't find a page, then why should a Search Engine?
* Internal links with aptly named titles are therefore considered best practice. Naming a link "read more here" is for this reason not advised for links that lead to landing pages.
* Rather use: Read all about "my product name here". Do not forget to make everything natural to read.

**For everyone:**

* Added: Highlighted the "words too many" within the SEO Bar description-description. Thanks Ovidiu for the suggestion!
* Added: The "words too many" within the SEO bar now represents capitalized abbreviations correctly, based on first encounter. E.g. "SEO" now doesn't become "Seo", but "SeO" still does change into "Seo" and "THis" is reflected as "This", this change makes sure that "SEO" stays "SEO".
* Added: The titles now add Protected and Private prefixes where applicable, based on Post or Page settings.
* Added: Support for all Custom Post Types (CPT) which support a title and editor. This is now possible because of the memory leak fix. It's reversible through a new filter.
* Added: Sitemap support for all CPT which support the title and editor.
* Changed: The AutoDescription title now is in par with the title on very special occasions.
* Changed: The Title is now fetched through the post object, instead of a function. This fixes a memory leak.
* Improved: The title is now generated a little bit faster in some scenarios.
* Fixed: Because of the way the title is now fetched, special plugins like AnsPress now correctly fetch the title on many occasions.
* Fixed: AnsPress Metadata conflicts have been resolved. Thank you Rahul (AnsPress author) for the functions.
* Fixed: AnsPress Custom SEO Settings can now be saved.
* Fixed: Anspress Custom SEO Settings for questions are now fetched correctly on the front-end.
* Fixed: Title separator was on the wrong side on themes that are doing it wrong and decided to switch things up.
* Fixed: Custom Post Types which are not published no longer are visible within the Sitemap (caused by typo).
* Fixed: Minor security improvement in the admin area. Disclosed and shouldn't have caused any security issues.
* Fixed: CPT Terms and Taxonomies could have too long transients names, causing them not to work on older WordPress installations.
* Fixed: Ping throttling for sitemaps updates could have too long transients names on older WordPress installations.
* Fixed: The homepage transient could be too long on older WordPress installations on very special occasions.
* Fixed: The 'Archives' title for untitled archives is now translatable.
* Fixed: Dutch typos within the SEO Settings Page.
* Fixed: Typo within Home Page Settings metabox, visible when robots settings were interchanged.
* Fixed: The Custom Home Page description placeholder within the Home Page Settings metabox now reflects its outcome when the front page is static.
* Fixed: The SEO Bar on Taxonomies and Terms now display the correct information again.
* Updated: POT language file.
* Updated: Dutch Translations.

**For developers:**

* Added: New filters.
* Added: New arguments for the `AutoDescription_Generate::title()` function. Boolean `description_title` and boolean `is_front_page`.
* Added: New function, `AutoDescription_Generate::description_from_custom_field()`.
* Updated: CSS files.
* Changed: WordPress' core `the_title` filter no longer influences this plugin's titles output anymore.
* Removed: Metabox action listeners that actually should've been removed in 2.4.0, because they have been replaced with new ones to prevent plugin conflict.
* Fixed: The Search query alternation now uses an action instead of a filter.
* Cleaned up code.

= 2.4.0 - Frequently Asked Queries =

**Summarized:**

The SEO Framework is now updated to a new second dot version. This means I've pushed a huge update and that the API has been optimized.

This also means that there has been an overhaul on many functions. A big rework. While doing so, there were many little bugs found, all of them have been fixed.

Meanwhile, the whole plugin has been improved once more to be faster. I believe the quest for speed has been very successful and come to an end.

The Sitemaps now also adds posts and pages created by Custom Post Types, like bbPress and WooCommerce.

Moreover, Domain Mapping by Donncha is now also supported, to prevent canonical errors and easily transfer SEO value when you map domains and keep them both accessible.

Because The SEO Framework is easily manipulated through the API, feel free to write your own (free or premium) extension plugin to add options.

**SEO Tip of the Update:**

* Invalid HTML doesn't affect your site's SEO value. However, valid HTML is considered best practice, and with a good reason.
* The good reason for valid HTML is that you can be certain that your website (almost) looks the same on every browser, so you're certain that the visitor will see your website as you intended.
* However, it doesn't affect your site's SEO value because Search Engines want to put out the best content for your search, not the most 'valid' content.
* With so many 'invalid' websites out there, Search Engines try to read the HTML as many web browsers would: by fixing common mistakes.
* Still using HTML4 or xHTML? Try to find a way to upgrade to HTML5, its semantic elements are very SEO friendly.

**For everyone:**

* Added: FAQ on WordPress.org's The SEO Framework plugin page.
* Added: Sitemap WPML compatibility.
* Added: The default permalink structure and shortlinks now also generate links based on registered query args, e.g. Custom Post Types.
* Added: Domain Mapping by Donncha support. This plugin is now suitable for every MultiSite :).
* Added: Removal of the WordPress core shortlink tag for when themes choose to include it, it's replaced with the SEO framework version if active (faster and more reliable).
* Added: The SEO Framework now shows you how much page load The SEO Framework has added to your page in the indicator after The SEO Framework Meta. This can be removed through a filter.
* Added: More sitemap content is available, now it supports posts and pages from Custom Post Types (bbPress, WooCommerce, AnsPress, etc.). Please note that it will combine all post types to prevent exceeding the memory limit. It will limit the maximum CPT posts/pages to 600 by default and are ordered by date, descending.
* Optimized: The Sitemap generation has once again been optimized. This is especially noticeable when there are many pages which are set to noindex.
* Changed: The Sitemap now allows for 100 more posts per post type. Making a total of 2100.
* Changed: The URLs now take your slashes into account.
* Rewritten: The Title generation has been completely rewritten, requires fewer server resources, is easier to maintain, etc.
* Rewritten: Massively improved the Title Cache by preemptively notifying the plugin if the theme is doing it wrong or right. It's now two to three times as fast on the front end which could save up to 0.2s of load time on old servers.
* Updated: Translation POT File.
* Updated: Dutch Translations.
* Removed: Home Page Title on Doing it Wrong themes is back to default.
* Removed: Legacy AnsPress title support for AnsPress versions lower than 2.4.
* Fixed: The Canonical URL is now correct for some plugins which don't abide to the WordPress default URL structure.
* Fixed: The title length calculation now doesn't add 3 characters too many to the counter when emptied.
* Fixed: The post modified time is now correct for posts in the sitemap.
* Fixed: Filter `the_seo_framework_sitemap_posts_count` now works again as intended.
* Fixed: The Homepage Metabox now notifies if the description is fetched from the front page, if applicable, again.
* Fixed: The SEO Bar on Terms and Taxonomies were incorrect at many points because of caching.
* Fixed: Bug where pages are output in the sitemap when the Blog Page was the last page added.
* Fixed: Title Placeholder in the Inpost SEO box was shown as the home page title when using WPML on a translated page using sub directories.
* Fixed: The SEO Bar title length calculation for every page when WPML is activated, it thought every page was the front page.
* Fixed: Home Page title could have unexpected results with the separator and separator location.
* Fixed: WPMUdev Domain Mapping external mapped URL now checks for scheme again.
* Fixed Typo: Therefor is now Therefore on various places.

**For developers:**

* Added: New filters!
* Added: User defined `$query->set( 'post__not_in' )` compatibility. This plugin now merges all values set `below 999 priority` for search queries on the `pre_get_posts` filter.
* Added: Better version compare: Use `the_seo_framework_dot_version( '2.4.x' )`, this returns true if the version is at least 2.4.0 and at most 2.4.9.
* Added: The debugger now also processes two dimensional arrays.
* Added: The SEO Framework Post Type support can now also be determined on `init`.
* Added: Sitemap generation time when debugger is active.
* **Reworked:** With over 734 lines of combined code, before it's too late: `AutoDescription_Generate:title` was very smelly because of compatibility with so many themes, plugins, etc. So it's now separated over different functions for different hooks, which all fall back to cached title. Please note that the `parameters` will output a `_doing_it_wrong` notice if your input call is invalid. The code will continue to run normally but will ignore your parameters set.
* Removed: AutoDescription_Load class and file. It has been replaced by The_SEO_Framework_Load since 2.2.5.
* Changed: moved `setup_sitemap_transient()` from transients.class.php to sitemaps.class.php, as it generates output it belongs there more nicely.
* Cleaned up code.

**Note for developers:**

* As with every second dot (2.4.x, 2.5.x, etc.) update, new API functions will be added or changed. Use `function the_seo_framework_dot_version()` to easily compare WordPress version. Read all about it [here](http://theseoframework.com/docs/api/settings/).

**About the performance change in 2.4.0:**

* This plugin now adds a total of about 1.5 milliseconds of load time on pages and 15 milliseconds in the admin area on PHP7. Enjoy!
* The sitemap is now generated within 0.15 seconds when parsing 2100 posts on PHP7.
* Please note that increasing this limit to 10,000 posts will use up to 80MB of RAM, the current limit has been carefully chosen and missing URL's within the sitemap doesn't affect SEO in almost all cases, especially with WordPress.

**Coming soon:**

* An Extension Manager, a separated opt-in plugin.
* The SEO Framework will stay ad-free and will never contain the Extension Manager by default.
* The Extension Manager allows you to install free/premium extensions.
* The extensions will be made available through subscriptions, ad-free, all-in-one.
* Free subscriptions for free extensions will be available, like with Akismet.
* More on this in December.

= 2.3.9 - The Littlest Things =

**Summarized:**

Version 2.2.8 of The SEO Framework was optimized on one place too many, this caused the Knowledge Graph LD+Json data not to be output on the front page. This update fixes that.

Like to change your settings in Customizer often? This update takes one more variable into account so the Title and Description is what it should be.

The SEO Bar initialization is now also reworked. Your admin area is much faster now and the SEO Bar requires fewer filters to implement.

**SEO Tip of the Update:**

* Search engines follow visitor interaction throughout the pages. If a visitor doesn't stay on a page long enough and hits the back button, your content probably isn't what the visitor was looking for and the Search Engine gets notified and adjusts for future queries.
* So be sure to create great titles and build a related and constructive first paragraph. If the visitor is hooked, the visitor will stay, read on or click through. Search Engines take note of this and your SERP value goes up.

**For everyone:**

* Added: The SEO Bar is now auto-loaded on all supported pages and posts.
* Improved: Inpost SEO Post Type support check is now cached.
* Improved: WPMUdev Avatars active plugin check, it's now more lightweight.
* Improved: Description and Title length are now trimmed of whitespaces in the length calculation on The SEO Bar.
* Improved: SEO Bar initialization. It's much faster now throughout the whole dashboard.
* Fixed: The page on the previously assigned static home page was handled as the front page for the title, description and the SEO bar when no longer a static home page is assigned.
* Fixed: Knowledge Graph output now works again (didn't work since 2.3.8).

**For developers:**

* Added: New filters!
* Added: The debugger now also shows if the page is a static front page.
* Changed: `AutoDescription_DoingItRight::init_columns` is now hooked at `current_screen` instead of `admin_init`. This way we can manipulate the screen and post type support.
* Changed: The Custom Post Types no longer has to support an author. See `AutoDescription_Inpost::post_type_supports_inpost` for the check.
* Updated: The debugger is more readable, consistent and flashy in the admin area.
* Changed: `AutoDescription_Detect::post_type_supports_inpost` no longer returns true by default if no parameter is set.
* Pre-emptive fix: Prevented minor unexpected results in various places.
* Removed: `the_seo_framework_column_support` filter, it's now completely automated. Refer to the `the_seo_framework_supported_post_types` filter if your post type is not added automatically.
* Cleaned up code.

**Note:**

* I've re-tested the database interactions (this value was wrong because of object caching). If you wish to conduct your own tests, feel free to let me know your results (and all variables taken into account). I'll update the numbers if necessary.

= Full changelog =

**The full changelog can be found [here](http://theseoframework.com/?cat=3).**

== Other Notes ==

== Filters ==

= Add any of the filters to your theme's functions.php or a plugin to change this plugin's output. =

Learn about them here: [The SEO Framework filters](http://theseoframework.com/docs/api/filters/)

== Constants ==

= Overwrite any of these constants in your theme's functions.php or a plugin to change this plugin's output by simply defining the constants. =

View them here: [The SEO Framework constants](http://theseoframework.com/docs/api/constants/)

== Actions ==

= Use any of these actions to add your own output. =

They are found here: [The SEO Framework actions](http://theseoframework.com/docs/api/actions/)

== Settings API ==

= Add settings to and interact with The SEO Framework. =

Read how to here: [The SEO Framework Settings API](http://theseoframework.com/docs/api/settings/)
