=== The SEO Framework ===
Contributors: Cybr
Donate link: http://theseoframework.com/
Tags: open graph, description, automatic, generate, generator, title, breadcrumbs, ogtype, meta, metadata, search, engine, optimization, seo, framework, canonical, redirect, bbpress, twitter, facebook, google, bing, yahoo, jetpack, genesis, woocommerce, multisite, robots, icon, cpt, custom, post, types, pages, taxonomy, tag, sitemap, sitemaps, screenreader, rtl
Requires at least: 3.6.0
Tested up to: 4.4.0
Stable tag: 2.4.3
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
* Shows you how to improve your SEO with a beautiful SEO bar for each supported Post, Page and Taxonomy.
* Helps your pages get ranked distinctively through various Metatag techniques.
* Helps your pages get shared more beautiful through e.g. Facebook and Twitter.
* Allows plugin authors to easily extend this plugin.
* Supports custom post types, like WooCommerce and bbPress.
* Automatically upgrades itself from Genesis SEO.
* Allows easy switch from other SEO plugins using a tool.

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

> This plugin is fully compatible with the [Domain Mapping plugin by WPMUdev](https://premium.wpmudev.org/project/domain-mapping/) and the [Domain Mapping plugin by Donncha](https://wordpress.org/plugins/wordpress-mu-domain-mapping/).<br />
> This plugin is now also compatible with all kinds of custom post types.<br />
> This will **prevent canonical errors**. This way your site will always be correctly indexed, no matter what you use!<br />
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

= 2.4.3.1 - Naming Things =

**Summarized:**

* Small update which fixes two bugs reported by my dear users, Thanks! :)
* Search Pages are now also generating a new cache key so they can't conflict with the home page cache anymore in special scenarios.

**For everyone:**

* Improved: Massively improved plugin speed on 404 and search pages by eliminating redundant calls.
* Improved: Cache generation key is now using static cache correctly.
* Fixed: The object cache key is now generating a different cache key for search queries before checking if it's the home page. This will prevent Search Queries to take over the home page cache when using a premium theme to design a one-pager front-page through widgets.
* Fixed: Sitemap and robots example links weren't correct when the permalink structure doesn't end with a slash.
* Fixed: Fatal error caused by typo on search pages.

**For developers:**

* Added: Extra parameter to `AutoDescription_Generate::the_url()` for forcing trailing slashes.

= 2.4.3 - The Littlest Things | Page 2 =

**Summarized:**

* Districting titles together with different content is a very important SEO factor. And for this reason, the Title has now obtained pagination.
* Besides this change, many little fixes have been put in place, and a few filters have been added for special cases.

**SEO Tip of the Update:**

* Maintaining a blog will output not only fresh content, and notify Search Engines that your website is active, but it will also simultaneously improve your chances to be found.
* There are so many (big) questions to be answered, a blog post on each of those questions will surely attract different visitors.
* Keep the blog related to the main subject of your site, for it will categorize your website as a whole. Visitors will more likely engage and then keep coming back for more information.
* After all, you're most likely the expert on the subject of your website. Share your knowledge!

**For everyone:**

* Added: Title pagination indication, so you won't have duplicated titles anymore on Categories and paginated pages. This only works on themes that are doing it right, and is shown within the Open Graph and Twitter titles on any theme.
* Added: Bundled all "SEO Tip of the Update" together in one .txt file shipped with each update :)
* Updated: The way object caching is implemented. It's now much more consistent. This also invalidates many object cache keys within this plugin which will automatically be reinstated at the earliest request.
* Changed: Titles can now be a tad shorter (8 characters) before firing a "too short" notice. Although this might not be "perfect", "50 to 55" was however too intrusive. A good descriptive title is always better than a long title.
* Improved: When using object cache, the SEO Settings Page is a tad faster now.
* Updated: POT File.
* Updated: Dutch Translations.
* Fixed: The title could double its output when the theme is doing it wrong in special scenarios.
* Fixed: Many setting combinations with WPML have been covered now for the URL in various places.
* Fixed: The Knowledge Graph now outputs your organisation or personal name correctly again.
* Fixed: Robots.txt's output is now correct again when you're using a subdirectory for your WordPress installation.

**For developers:**

* Added: New filters.
* Added: Enhanced functionality with object caching. Mainly allowing it to be enabled or disabled.
* Changed: Grouped all object caching keys together in `theseoframework` group, instead of several based on my other plugins.
* Improved: `the_seo_framework_sitemap_custom_posts_count` now doesn't fetch any posts when set to `0` or `false`-ish.
* Fixed: PHP notice in robots.txt
* Fixed: API functions will return null now when The SEO Framework is not active through a filter instead of giving a fatal error.
* Removed: Redundant code left for testing purposes.
* Changed: Invalidated LD+Json transient cache key because of the fix. The expired cache will be flushed automatically.
* Cleaned up Code.

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

== Beta Version ==

= Stay updated with the latest version before it's released? =

The beta is available [on Github](https://github.com/sybrew/the-seo-framework). Please note that changes might not reflect the final outcome of the full version release.
