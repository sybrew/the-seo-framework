=== The SEO Framework ===
Contributors: Cybr
Donate link: https://theseoframework.com/donate/
Tags: open graph, description, automatic, generate, generator, title, breadcrumbs, ogtype, meta, metadata, search, engine, optimization, seo, framework, canonical, redirect, bbpress, twitter, facebook, google, bing, yahoo, jetpack, genesis, woocommerce, multisite, robots, icon, cpt, custom, post, types, pages, taxonomy, tag, sitemap, sitemaps, screenreader, rtl, feed
Requires at least: 3.6.0
Tested up to: 4.5.1
Stable tag: 2.5.2.4
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
* Automatically configures SEO for every page, post, taxonomy and term.
* Allows you to adjust the SEO globally.
* Allows you to adjust the SEO for every applicable page.
* Shows you how to improve your SEO with a beautiful SEO bar for each supported Post, Page and Taxonomy.
* Helps your pages get ranked distinctively through various Metatag and scripting techniques.
* Helps your pages get shared more beautiful through Facebook, Twitter and other social sites.
* Allows plugin authors to easily extend this plugin.
* Supports custom post types, like WooCommerce and bbPress.
* Automatically upgrades itself from Genesis SEO.
* Allows for easy SEO plugin switch using a tool.

*Read **Transferring SEO Content using SEO Data Transporter** below for SEO plugin transitioning instructions.*

= Numbers don't lie =
Optimizing SEO is a fundamental process for any website. So we try to be non-intrusive with The SEO Framework.
The SEO Framework is byte and process optimized on PHP level, with each update the optimization is improved when possible.

* This plugin is written with massive and busy (multi-)sites in mind.
* This plugin is 197% to 867% faster compared to other popular SEO plugins.
* This plugin consumes 177% to 260% fewer server resources than other popular SEO plugins.
* 15% fewer database interactions (numbers may vary on this one depending on plugin compatibility).
* 100% fewer advertisements. Let's keep it that way.

= Completely pluggable =
The SEO Framework also features pluggable functions. All functions are active and can be called within the WordPress Loop.
This allows other developers to extend the plugin wherever needed.
We have also provided an API documentation located at [The SEO Framework API Docs](http://theseoframework.com/docs/api/).

= Still not convinced? Let's dive deeper =

**This plugin automatically generates:**

* Description, with anti-spam techniques.
* Title, with super-fast 'wrong themes' support (so no buffer rewriting!).
* Various Open Graph tags.
* Special Open Graph description, which organically integrates with the Facebook and Twitter snippets
* Extended Open Graph Images support, including image manipulation.
* Canonical, with full WPMUdev Domain Mapping, subdomain and HTTPS support to prevent duplicated content.
* LD+Json script that adds extended search support for Google Search and Chrome.
* LD+Json script for Knowledge Graph (Personal/Business site relations, name and logo).
* LD+Json script for Breadcrumbs (just like the visual one) which extends page relation support in Google Search.
* Publishing and editing dates, accurate to the day.
* Link relationships, with full WPMUdev Domain Mapping and HTTPS support.
* Various Facebook and Twitter Meta tags.
* Simple Sitemap with Pages, Posts and Custom Post Types (CPT), which listens to the in-post settings.
* Feed excerpts and backlinks to prevent content scraping.

**This plugin goes further, behind the screens it:**

* Prevents canonical errors with categories, pages, subdomains and multisite domain mapping.
* Disables 404 pages and empty categories from being indexed, even if they don't send a 404 response.
* Automatically notifies Google, Bing and Yahoo on Post or Page update when sitemaps are enabled.

**This plugin allows you to manually set these values for each post, page, supported CPT and term:**

* Title
* Description
* Canonical URL
* Robots (nofollow, noindex, noarchive)
* Redirect, with MultiSite spam filter (Post/Page/CPT only)
* Local on-site search settings (Post/Page/CPT only)

**This plugin allows you to adjust various site settings:**

* Title and Description Separators
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
* Google/Bing/Pinterest Webmaster verification
* Google Knowledge Graph
* Sitemap intergration
* Robots.txt
* Feed content output
* And much more

**This plugin helps you to create better content, at a glance. By showing you:**

* If the title is too long, too short and/or automatically generated.
* If the description is too long, too short and/or automatically generated.
* If the description uses some words too often.
* If the page is indexed, redirected, followed and/or archived.
* If your website is publicly accessible.

**We call this The SEO Bar. Check out the [Screenshots](https://wordpress.org/plugins/autodescription/screenshots/#plugin-info) to see how it helps you!**

> This plugin is fully compatible with the [Domain Mapping plugin by WPMUdev](https://premium.wpmudev.org/project/domain-mapping/) and the [Domain Mapping plugin by Donncha](https://wordpress.org/plugins/wordpress-mu-domain-mapping/).<br />
> This plugin is now also compatible with all kinds of custom post types.<br />
> This will **prevent canonical errors**. This way your site will always be correctly indexed, no matter what you use!<br />
>
> This plugin is also completely ad-free and has a WordPress integrated clean layout. As per WordPress.org plugin guidelines and standards.

= Caching =

This plugin's code is highly optimized on PHP-level and uses variable, object and transient caching. This means that there's little extra page load time from this plugin, even with more Meta tags used.
A caching plugin isn't even needed for this plugin as you won't notice a difference, however it's supported wherever best suited.

**If you use object caching:**
The output will be stored for each page, if you've edited a page the page output Meta will stay the same until the object cache expires. So be sure to clear your object cache or wait until it expires.

**Supported Caches:**

* Server-level Opcode (optimized)
* Staticvar functions (prevents running code twice or more)
* Staticvar class (instead of globals, prevents constructors running multiple times)
* Objects for database calls
* Transients for process intensive operations or persisting communication with front-and back end.
* CDN for Open Graph and Twitter images
* HTML and script Minification caching as well as Database caching are also supported.

= Compatibility =

**Basics:**

* Full internationalization support through WordPress.org.
* Extended Multibyte support (CJK).
* Full Right to Left (RTL) support.
* Color vision deficiency accessibility.
* Screen-reader accessibility.
* Admin screen: Posts, Pages, Taxonomies, Terms, Custom Post Types.
* Front-end: Every page, post, taxonomy, term, custom post type, search request, 404, etc.
* MultiSite, this plugin is in fact built upon one.
* Detection of robots.txt and sitemap.xml files.
* Detection of theme Title "doing it right" (or wrong).

**Plugins:**

* W3 Total Cache, WP Super Cache, Batcache, etc.
* WooCommerce: Shop Page, Products, Product Galleries, Product Categories and Product Tags.
* Custom Post Types, (all kinds of plugins) with automatic integration.
* WPMUdev and Donncha's Domain Mapping with full HTTPS support.
* WPMUdev Avatars for og:image and twitter:image if no other image is found.
* bbPress: Forums, Topics, Replies.
* BuddyPress profiles.
* Ultimate Member profiles.
* AnsPress Questions, Profiles and Pages, also Canonical errors have been fixed.
* StudioPress SEO Data Transporter for Posts and Pages.
* WPML, URL's, sitemap and per-page/post SEO settings. (The full and automated compatibility is being discussed with WPML.)
* qTranslate X, URL's, limited sitemap and per-page/post SEO settings (through shortcodes by set by qTranslate X).
* Jetpack modules: Custom Content Types (Testimonials, Portfolio), Infinite Scroll, Photon.
* Most popular SEO plugins, let's not get in each other's way.
* Many, many other plugins, yet to confirm.

**Themes:**

* All themes.
* Special extended support for Genesis & Genesis SEO. This plugin takes all Post, Page, Category and Tag SEO values from Genesis and uses them within The SEO Framework Options. The easiest upgrade!

If you have other popular SEO plugins activated, this plugin will automatically prevent SEO mistakes by deactivating itself on almost every part.
It will however output robots metadata, LD+Json and og:image, among various other meta data which are bound to social media.

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

= About the sitemap =

The sitemap generated with The SEO Framework is sufficient for Search Engines to find Posts, Pages and supported Custom Post Types throughout your website.
It also listens to the noindex settings on each of the items.
If you however require a more expanded sitemap, feel free to activate a dedicated sitemap plugin. The SEO Framework will automatically deactivate its sitemap functionality when another (known) sitemap plugin is found.
If it is not automatically detected and no notification has been provided on the Sitemap Settings, feel free to open a support ticket and it will be addressed carefully.

The Breadcrumb script generated by this plugin on posts will also make sure Google easily finds related categories which aren't included within the sitemap of this plugin.

= Other notes =

*Genesis SEO will be disabled upon activating this plugin. This plugin takes over and extends Genesis SEO.*

***The Automatic Description Generation will work with any installation, but it will exclude shortcodes. This means that if you use shortcodes or a page builder, be sure to enter your custom description or the description will fall short.***

***The home page tagline settings won't have any effect on the title output if your theme's title output is not written according to the WordPress standards, which luckily are enforced strongly on new WordPress.org themes since recently.***

> <strong>Check out the "[Other Notes](https://wordpress.org/plugins/autodescription/other_notes/#Other-Notes)" tab for the API documentation.</strong>

*I'm aware that radio buttons lose their input when you drag the metaboxes around. This issue is fixed since WordPress 4.5.0 (alpha and later).*
*But not to worry: Your previous setting will be returned on save. So it's like nothing happened.*

== Installation ==

1. Install The SEO Framework either via the WordPress.org plugin directory, or by uploading the files to your server.
1. Either Network Activate this plugin or activate it on a single site.
1. That's it!

1. Let the plugin automatically work or fine-tune each page with the metaboxes beneath the content or on the taxonomy pages.
1. Adjust the SEO settings through the SEO settings page if desired. Red checkboxes are rather left unchecked. Green checkboxes are default enabled.

== Screenshots ==

1. This plugin shows you what you can improve, at a glance. With full color vision deficiency support.
2. Hover over any of the SEO Bar's items to see how you can improve the page's SEO. Red is bad, orange is okay, green is good. Blue is situational.
3. The dynamic Post/Page SEO settings Metabox. This box is also neatly implemented in Categories and Tags.
4. The SEO Settings Page. With over 70 settings, you are in full control. Using the Default Settings and filling in the Knowledge Graph Settings is recommended to do.

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
Why? Because I hate them, probably more than you do.
I also don't want to taint your website from the inside, like many popular plugins do.
Read more about this on the [Plugin Guidelines, Section 7](https://wordpress.org/plugins/about/guidelines/).

***But how do you make a living?***

Currently, The SEO Framework is non-profit.
This plugin was first released to the public in March 15th, 2015. From there it has grown, from 179 lines of code, to more than 17100 lines.
With over 600,000 characters of code written, this plugin is absolutely a piece of art in my mind.
And that's what it should stay, (functional) art.
I trust that people are good at heart and will tell their friends and family about the things they enjoy the most, what they're excited about, what they find beneficial or even beautiful.

With The SEO Framework I try to achieve exactly that. It's made with <3.

= Does this plugin collect my data? =

Absolutely not! Read more about this on the [Plugin Guidelines, Section 7](https://wordpress.org/plugins/about/guidelines/).

= Premium version? =

Nope! Only premium extensions. These are being developed.

= If a premium extensions is released, what will happen to this plugin? =

This plugin is built to be an all-in-one SEO solution, so:

1. No advertisements about the premium extensions will be placed within this plugin.
1. No features will be removed or replaced for premium-only features.
1. The premium extensions will most likely only be used for big-business SEO. Which are very difficult to develop and which will confuse most users anyway.

= I've heard about an extension manager, what's that? =

Currently it's not available. When it is, it will allow you to download and activate extensions for The SEO Framework. It will support both multisite and single-site and the registration will be based on the Akismet plugin.

= The sitemap doesn't contain categories, images, news, etc. is this OK? =

This is not a problem. Search Engines love crawling WordPress because its structure is consistent and well known.
If a visitor can't find a page, why would a Search Engine? Don't rely on your sitemap, but on your content and website's useability.

= What's does the application/ld+json script do? =

The LD+Json scripts are Search Engine helpers which tell Search Engines how to connect and index the site. They tell the Search Engine if your site contains an internal search engine, what sites you're socially connected to and what page structure you're using.

= The (home page) title is different from the og:title, or doesn't do what I want or told it to. =

The theme you're using is using outdated standards and is therefore doing it wrong. Inform your theme author about this issue.

Give the theme author these two links: https://codex.wordpress.org/Title_Tag https://make.wordpress.org/themes/2015/08/25/title-tag-support-now-required/

If you know your way around PHP, you can speed up this process by replacing the `<title>some code here</title>` code with `<title><?php wp_title('') ?></title>` within the `header.php` file of the theme you're using.

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

Please refer to this small guide: [SEO Data Migration](http://theseoframework.com/docs/seo-data-migration/).
Transporting Terms and Taxonomies SEO data isn't supported.

== Changelog ==

= 2.5.2.4 - Secure Globals =

**Summarized**

* This minor security update ensures the Runway Framework theme compatibility as well as resolving some minor security concerns.

**For everyone:**

* Fixed: Global Page/Paged vars could (undesirably) be overwritten by external themes, causing the website to crash on archives.
* Improved: Various admin-only sanitation features.

= 2.5.2.3 - Unfamiliar Homefront =

**Summarized:**

* This minor update resolves an issue where an uncommon type of Home Page as blog could be seen as an empty Archive. This results in the Home Page being removed from the Search Index.
* 2.6.0 and all its promised features are still being developed and is due in April.

**For everyone:**

* Fixed: When the Home Page is a blog, it could be seen as an empty Archive when no Posts are set to published.
* Note: This detection is actually a feature to prevent bad Archives and 404 pages from being indexed. Alas, the Home Page has enough settings and can be uncommonly adjusted by Themes and (builder) Plugins.

**For whoever was affected:**

* First off, I'm terribly sorry for this inconvenience and this negative hit on your Home Page.
* Luckily, its effects aren't lasting and your Home Page will be reindexed automatically.
* Your website was only affected by this bug if you have 0 posts published, and - at the same time - if your Home Page isn't assigned as a Static Page, but a Blog.

**For whoever was affected and wants to speed things up:**

* You can manually reindex your Home Page with Google and the Bing Search Network (Yahoo!, Bing, DuckDuckGo, and others whom use the Bing Search Network).
* To do this, perform the following steps. Be sure to clear all your caches beforehand:

***Google:***

1. Go to [Google Webmasters](https://www.google.com/webmasters/) and sign up if you haven't already.
1. Add your property if you haven't already.
1. Verify your property if you haven't already. You can add the verification code they give within the Webmasters Settings metabox within the SEO Settings page. Be sure to clear your cache.
1. Go to your property.
1. On the left, go to `Crawl => Fetch as Google`.
1. Without changing any details, hit "Fetch and Render".
1. It takes up to 3 days for Google to re-index your Home Page.

***Bing Search Network:***

1. Go to [Bing Webmaster Tools](http://www.bing.com/toolbox/webmaster) and sign up if you haven't already.
1. Add your property if you haven't already.
1. Verify your property if you haven't already. You can add the verification code they give within the Webmasters Settings metabox within the SEO Settings page. Be sure to clear your cache.
1. Go to your property.
1. On the left, go to `Diagnostics & Tools => Fetch as Bingbot`.
1. Enter your Home Page URL and hit "Fetch".
1. Wait a little and Bing will output the source of your page on success.
1. It takes up to 3 days for Bing to re-index your Home Page. It takes up to 14 days for the network to catch up.

= 2.5.2.2 - Description Conditions =

**Summarized:**

* 2.6.0 is already underway, but I found the following fix mandatory. So here is 2.5.2.2!
* This minor update makes sure the social description is as intended when you choose to remove the blogname from the regular description.

**For everyone:**

* Fixed: When description additions are disabled, the social description is no longer "Title on Blogname".

= 2.5.2.1 - Unforeseen Improvements =

**Summarized:**

* This minor update brings you a special scenario fix that caused an unnecessary browser alert.
* Also, object caching now works as intended and you can see a minor performance improvement when used.

**For everyone:**

* Improved: The HTML plugin indicators now ignore object cache. Making the filter more responsive.
* Fixed: The HTML plugin indicator timer translation is now put to use (oops).
* Fixed: JavaScript bug caused by undefined error where the user was prompted for settings change while there weren't any on the Post or Page edit screens when the **"Remove blogname from title?"** option is checked or when editing the home page and the **"Add site description (tagline) to the Title on the Home Page?"** is unchecked.
* Fixed: Incompatible object cache group names.
* Removed: Twitter card and Twitter title output on 404 and search pages. The Twitter tags rely on other tags which can't be rendered in their full extend by default on these pages. Twitter uses OG tags to fall back on.
* Note: The per-page output cache key has changed for the output, this forces the object cache to be invalidated.

**For developers:**

* Added: `AutoDescription_Detect::tell_title_doing_it_wrong()`, based on the following deprecated function.
* Deprecated: `AutoDescription_Detect::title_doing_it_wrong()`, this function was reworked to a point of uselessness in 2.5.2.
* Changed: If the theme you're using is doing the wp_title() function wrong, the title parameters are now changed within the hidden HTML comment, i.e. e.g. `<!-- Title diw: "title" : "sep" : "seplocation" -->`).
* Improved: Transient for title doing it right is now also set if the theme is doing it right to reduce extra database calls.
* Cleaned up code.

= 2.5.2 - Data Feed Influx =

**Summarized:**

* This is the biggest update so far, with over a hundred noticeable changes and with many functions added to remove repeated calls and replace them with memory stored functions.
* This update of The SEO Framework brings you Feed excerpt generation as well as options to add backlinks in the feed. You can now also add your Pinterest verification code.
* Also, a lot of the WordPress behavior detection and plugin detection has been reworked, improved and has been put to great use within the option pages to eliminate confusion by removing options if they have no effect.
* And last but not least, due to popular demand an extra option has been added to change the Title generation, although it decouples all your pages within your website.
* Oh, and many minor improvements and bug fixes have also been put into effect, including a few more important WooCommerce shop page fixes and a proud-to-be-of 20% overall performance improvement.

**SEO Tip of the Update:**

* If you are writing blog posts, take a look at the rising trends at Google Trends before it's all over the internet.
* Write about any of the trends once in a while, and if the trend kicks off your website might just be exposed to many people.

**For everyone:**

* Added: Pinterest Analytics verification field.
* Added: Feed option in the new Feed Settings metabox tab to convert the feed content into to an excerpt (400 characters) to counter scrapers and to prevent duplicated content issues. Default enabled on new installations.
* Added: Feed option to add a non-followed backlink to the feed content to counter scrapers and bad links. Default enabled on new installations.
* Added: The custom excerpt field (if available) is now taken into account before generating one from the content.
* Added: Title additions options, which allows you to remove the blogname from all pages (except the home page, which has its own option already). This option is only available to themes which are doing it right. Default disabled and recommended not to be used, not even if the SEO data transfer went otherwise than expected.
* Added: The custom in-post titles now also listen to the new Title addition options.
* Added: The dynamic title additions placeholder has also been put into effect when the Title Additions Location has been set in reverse order. This effectively also eliminates confusion.
* Added: Dynamic title additions left/right options based on being enabled or disabled.
* Added: PHP 5.2 compatibility, which is actually very much outdated. PHP 5.2 will henceforth be (syntax-wise) supported until at least March 2016. Please [stay](http://php.net/eol.php) [updated](http://php.net/supported-versions.php)!
* Added: Subtle sitemap plugin detection notification on SEO Settings Page. If a sitemap plugin is detected, the option will be removed from view and will be replaced with the notification.
* Added: The sitemap detection will also remove the Sitemap ping and timestamp submenus and their options.
* Added: Robots.txt file detection. If a robots.txt file is detected in the website's root folder the options to change it will be removed.
* Added: sitemap.xml file detection. If a sitemap.xml file is detected in the website's root folder the options to output it will be removed.
* Added: Subtle OG plugin detection notification on SEO Settings Page.
* Added: The generation comments within the Sitemap output are now translatable.
* Added: The plugin usage comments within the HTML output are now translatable.
* Added: Translation comments aimed at front-end translations, translators can now easily find high-priority translations through the "Front-end" filter keyword.
* Added: Removal of WordPress Core adjacent post rel links, this is now managed by The SEO Framework.
* Added: Implemented, but left deactivated, the SEO bar in the Post Edit screens. You can enable this through a filter, found at [The SEO Framework filters](https://theseoframework.com/docs/api/filters/).
* Added: Ultimate Member profile titles compatibility.
* Added: A lot more sitemap plugins can be detected.
* Improved: All archives get the archive title correctly.
* Improved: When no excerpt can be created, the social description will be the same as the regular description again, instead of being empty.
* Improved: Sped up breadcrumb generation time when no Post parents are found.
* Improved: All single sentence html hover titles have no more dots at the end to maintain consistency.
* Improved: Navigation tabs in the SEO Setting page now don't overflow anymore in the Social Meta Settings metabox.
* Improved: Greatly reduced non-minified CSS file size by converting spaces into tabs.
* Improved: Plugin Home link in the Plugin Activation page is now updated to HTTPS.
* Improved: Various comparison checks are sped up.
* Improved: Added trimming of spaces around the blogname and title in the Title and Description.
* Improved: The character counter and settings changes is much more responsive and now listens to more browser interactions.
* Improved: Greatly reduced sitemap generation time by caching more variables.
* Changed: Small textual change to make things more clear in the Description Settings.
* Fixed: Custom Canonical URL now works again when using WPMUdev Domain Mapping.
* Fixed: Firefox checkbox colors are now also visible through shadows, when FireFox officially supports checkbox styling (which has been in question for [almost 8 years](https://bugzilla.mozilla.org/show_bug.cgi?id=418833)!) it will be removed.
* Fixed: Firefox separator radio button margins were doubled.
* Fixed: Firefox unnecessary white space above separator buttons.
* Fixed: Sitemap `lastmod` dates could be in an incorrect syntax due to WordPress translations.
* Fixed: Some other plugins pop up annoying notices over the controls within the SEO Framework (outside of their plugin domain!). They've been pushed down. Because of this fix the title is also pushed in-line to go beneath the Screen Options on tablets, a welcome side effect.
* Fixed: og:locale content when a translation plugin has incorrectly changed the locale code of WordPress.
* Fixed: Saving Draft after SEO Settings change gave an unnecessary and incorrect unsaved data popup notification.
* Fixed: Sitemap URL Location missed a slash with special permalink settings.
* Fixed: When using WordPress version below 4.5, when the Home Title location lost its input as a result from metabox dragging, the option is now properly falling back to its previous value rather than the global Title Settings value.
* Fixed: Robots.txt URL Location missed a slash with special permalink settings.
* Fixed: WPML Negotiation Type miscalculation, Different Domain Language URL format was treated as the subdirectory URL format.
* Fixed: Invalid HTML around the Local Search Settings question mark in the Inpost Metabox.
* Fixed: Twitter card type wasn't properly sanitized. This didn't cause a security issue as the option value is properly escaped before it's sent to the database and on output. Hurray for Framework.
* Fixed: Settings radio boxes now contain a second fall back to default if empty value is and was given.
* Fixed: Missing characters at the end of the title when the theme is using an html entity that has characters like the end of the title name.
* Fixed: Day date archives cache keys could've been too long on old WordPress installations.
* Fixed: WooCommerce main shop page now listens to all the assigned page SEO settings.
* Fixed: WooCommerce main shop page description title is now correct.
* Fixed: When a static front page has been assigned, the Posts Page can now listen to the redirect option.
* Fixed: Temporarily added lines back (which were removed in WordPress 4.4) beneath the option tabs in the metaboxes on mobile screens.
* Fixed: Dynamic Title Placeholder input text visual glitch when the input text was overflowing.
* Fixed: Miscalculation in multibyte string positioning when using non UTF-8 characters and when the server didn't support mbstring. This could've caused a miscalculation for the title when the theme was doing it wrong.
* Updated: POT file.

**For developers:**

* Added: New filters.
* Added: New Class: `AutoDescription_Feed` within `feed.class.php`.
* Added: More filterable image arguments.
* Added: Two Image fallback filters.
* Added: Extra title argument to disable sanitation. Requires santition afterwards.
* Added: If the theme you're using is doing the `wp_title()` function wrong, the title parameters are now added in a hidden HTML comment (i.e. e.g. `<!-- Title diw: | true right -->`) in the page footer for developer debugging purposes.
* Added: Sanitation for effectively removing spaces and html tags, `s_no_html_space`.
* Added: With up to 140 calls in a single load in the admin area, a new cached function has arrived: `AutoDescription_PostData::get_blogname()`. Effectively reduced RAM usage by 800kB with 100 posts displayed.
* Added: New cached function: `AutoDescription_PostData::get_blogdescription()`.
* Added: New function: `AutoDescription_Generate::fetch_locale()`, fetches correct Open Graph locale based on WordPress settings or through parameter.
* Added: New cached function: `AutoDescription_Detect::can_i_use()`. Dump your multidimensional array of functions, classes and constants in this function and it will calculate if they can all be used, which also caches the matches for these checks are quite intensive.
* Added: New cached function: `AutoDescription_Detect::is_singular()`. Special is_singular check which works on both front-end as backend. This also tells that the WooCommerce Shop page is singular instead of an archive.
* Added: Many more functions, which aren't important enough to be highlighted.
* Added: `post_id` filterable argument on `AutoDescription_Generate::get_image()`.
* Added: When The SEO Framework debugger is activated, the screen names are shown at the bottom-right in the admin screens.
* Optimized: Many comparisons have been converted into type sensitive statements, this is less forgiving to erroneous option array filters.
* Changed: Reduced sitemap ping throttle transient name length.
* Changed: When the first parameter of `AutoDescription_PostData::get_excerpt_by_id()` is filled in, it will now not only escape the attributes, but strip the whole content from its tags and shortcodes.
* Changed: `AudoDescription_Generate::description_from_custom_field()` can no longer return an array with page for posts data.
* Improved: `AutoDescription_PostData::get_excerpt_by_id()` now listens more carefully to the first parameter. If it's not empty, it doesn't try to fetch the post content.
* Improved: Removed the Generator tag through action instead of filter.
* Improved: Disabled plugin output completely on Preview mode, including the title tag manipulation.
* Improved: `AutoDescription_Admin_Init::add_post_state` no longer uses `global $post`.
* Improved: Cleaned up debug messages.
* Updated: Doing it Wrong notice to the current standards.
* Fixed: Unlikely conflict in the default site options and warned site options. This should only have caused a problem if the (protected) warned settings were fetched before admin_init.
* Fixed: Check for error in breadcrumbs is now also applicable for the term fetch, instead of doubling the cat check.
* Fixed: Empty values can now be correctly debugged for value type through the debugger.
* Fixed: Debugger no longer generates an error when parsing boolean values.
* Fixed: Debugger no longer generates an error when fetching a non-existing excerpt on the front page.
* Fixed: PHP Warning on transient generation when displaying multiple CPT on a page.
* Fixed: PHP Fatal error when debugging a taxonomy.
* Fixed: PHP Fatal errors when debugging the sitemap (unrelated fix).
* Removed: Generic debug messages in the sitemap, these caused the sitemap syntax to fail on generation.
* Removed: Doing it Wrong notice in the footer when the theme is outputting the title wrong on WordPress 4.4.0 and lower. This has been exchanged for a small html comment.
* Removed: Genesis check for `wp_title` as it now supports the much required title tag.
* Removed: IS_HMPL constant check, now only listens to the filter.
* Removed: Override and front-page image arguments as it has been replaced with a more extensive and easier to understand filter.
* Deprecated: `AudoDescription_Generate::generate_description_from_id()` 2nd argument. Exchanged for escape. 3rd parameter will still work until 2.6.0.
* Deprecated: `AudoDescription_Generate::generate_excerpt()` 3rd argument. Exchanged for char length. 4th and 5th parameters are also deprecated and will work as max_char_length until 2.6.0.
* Deprecated: `AutoDescription_Admin_Init::supported_screens` as it isn't used anymore. This also deprecates the `the_seo_framework_supported_screens` filter.
* Note: On many places, `empty()` has been exchanged for type sensitive checks. If you do not wish to use a parameter, always input the default to maintain expected behavior.
* Note: The description excerpt cache version has been bumped to refresh all excerpts.
* Cleaned up code.

**Notes:**

* I am aware that the GeoDirectory plugin title settings are ignored by this plugin. A compatibility update is planned.

= 2.5.1 - Undocumented Properties =

**Summarized:**

* This update addresses issues with the Facebook protocol caused by overlooked (and undocumented) terms. This makes sure the Facebook meta tags work correctly and as per standards.
* Also, all Posts Page data has been fixed and is now being fetched correctly.

**SEO Tip of the Update:**

* Having a faster website will improve your Search Engine Results Page (SERP) ranking.
* It will also prevent potential visitors from hitting the back button if they can't reach your website in a timely manner.
* See SEO Tip of The Update (2.3.9) for related information on how page speed affects SEO. You can find all the previous SEO tips within the plugin folder.

**For everyone:**

* Added: The 'blog' og:type type for the blog page or homepage when it's a Post Page.
* Changed: Page type has been set to `website` rather than `article` when no og:image has been provided on a post to adhere to the standard, this will still output an error in the validator although it's correct.
* Improved: Standardized the default permalink structure URL and reduced memory usage.
* Improved: Shortlink generation time.
* Fixed: The Blog Page now listens to the robots settings.
* Fixed: The Blog Page now listens the custom Title set.
* Fixed: The Blog Page auto-description InPost Metabox placeholder is now what it outputs on the front-end.
* Fixed: Usage of "name" instead of "property" in OG/Facebook meta tags. Sometimes Facebook fixed this automatically, see "List of property fixes" below for more information.

**For developers:**

* Added: New filter.
* Added: Bumped year of copyright.
* Changed: Space after each exclamation mark to maintain flow in PHP from being mixed.
* Cleaned up code.

**List of property fixes:**

* article:author
* article:publisher
* fb:app_id
* article:published_time
* article:modified_time

= Full changelog =

**The full changelog can be found [here](http://theseoframework.com/?cat=3).**

== Other Notes ==

= Filters =

= Add any of the filters to your theme's functions.php or a plugin to change this plugin's output. =

Learn about them here: [The SEO Framework filters](http://theseoframework.com/docs/api/filters/)

= Constants =

= Overwrite any of these constants in your theme's functions.php or a plugin to change this plugin's output by simply defining the constants. =

View them here: [The SEO Framework constants](http://theseoframework.com/docs/api/constants/)

= Actions =

= Use any of these actions to add your own output. =

They are found here: [The SEO Framework actions](http://theseoframework.com/docs/api/actions/)

= Settings API =

= Add settings to and interact with The SEO Framework. =

Read how to here: [The SEO Framework Settings API](http://theseoframework.com/docs/api/settings/)

= Beta Version =

= Stay updated with the latest version before it's released? =

If there's a beta, it will be available [on Github](https://github.com/sybrew/the-seo-framework). Please note that changes there might not reflect the final outcome of the full version release.
