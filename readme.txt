=== The SEO Framework ===
Contributors: Cybr
Donate link: https://theseoframework.com/donate/
Tags: open graph, description, automatic, generate, generator, title, breadcrumbs, ogtype, meta, metadata, search, engine, optimization, seo, framework, canonical, redirect, bbpress, twitter, facebook, google, bing, yahoo, jetpack, genesis, woocommerce, multisite, robots, icon, cpt, custom, post, types, pages, taxonomy, tag, sitemap, sitemaps, screenreader, rtl, feed
Requires at least: 3.8.0
Tested up to: 4.5.2
Stable tag: 2.6.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

The SEO Framework plugin provides an automated and advanced SEO solution for your WordPress website.

== Description ==

= The SEO Framework =

**Easy for beginners, awesome for experts. WordPress SEO for Everyone.**

An accessible, unbranded and extremely fast SEO solution for any WordPress website.

> <strong>This plugin strongly helps you create better SEO value for your content.</strong><br>
> But at the end of the day, it all depends on how entertaining or well-constructed your content or product is.
>
> No SEO plugin does the magic thing to be found instantly. But doing it right helps a lot.<br>
> The SEO Framework helps you doing it right. Give it a try!
>
> The Default Settings are recommended within the SEO Settings page. If you know what you're doing, go ahead and change them! Each option is also documented.

= What this plugin does, in a few lines =

* Automatically configures SEO for every page, post, taxonomy and term.
* Allows you to adjust the SEO globally.
* Allows you to adjust the SEO for every applicable page, post, taxonomy and term.
* Shows you how to improve your SEO with a beautiful SEO bar for each supported Post, Page and Taxonomy.
* Helps your pages get ranked distinctively through various Metatag and scripting techniques.
* Helps your pages get shared more beautiful through Facebook, Twitter and other social sites.
* Allows plugin authors to easily extend this plugin.
* Supports custom post types, like WooCommerce and bbPress.
* Automatically upgrades itself from Genesis SEO.
* Allows for easy SEO plugin switch using a tool.

*Read **Transferring SEO Content using SEO Data Transporter** below for SEO plugin transitioning instructions.*

= Unbranded, Free and for the Professional =
This plugin is unbranded! This even means that we don't even put the name "The SEO Framework" anywhere within the WordPress interface, aside from the plugin activation page.
This plugin makes great use of the default WordPress interface elements, like as if this plugin is part of WordPress. No ads, no nags.
The small and hidden HTML comment can easily be disabled with the use of a filter.

Nobody has to know about the tools you've used to create your or someone else's website. A clean interface, for everyone.

= Numbers don't lie, performance matters =
Optimizing SEO is a fundamental process for any website. So we try to be non-intrusive with The SEO Framework.
The SEO Framework is byte and process optimized on PHP level, with each update the optimization is improved when possible.
Page rendering time matters in SEO. This is where we lay focus on.

* This plugin is written with massive and busy (multi-)sites in mind.
* This plugin uses various caching methods which store heavy calculations in memory and the database.
* This plugin is on average 1.49x to 1.95x faster compared to other popular SEO plugins.
* This plugin consumes on average 1.42x more server resources than other popular SEO plugins in exchange for improved performance.
* This plugin has on average 1.30 to 1.60x more database interactions in exchange for improved performance.
* And last but not least, this plugin always has 100% fewer advertisements. Let's keep it that way.

*Numbers may vary per installation and version. Last checked: 14th May 2016.*
*The numbers are based on actual plugin code runtime.*

= Completely pluggable =
The SEO Framework also features pluggable functions. All functions are active and can be called within the WordPress Loop.
This allows other developers to extend the plugin wherever needed.
We have also provided an API documentation located at [The SEO Framework API Docs](http://theseoframework.com/docs/api/).

= Still not convinced? Let's dive deeper =

**By default, this plugin automatically generates:**

* Title, with super-fast 'wrong themes' support.
* Description, with anti-spam techniques.
* A canonical URL.
* Various Open Graph, Facebook and Twitter tags.
* Special Open Graph description, which organically integrates with the Facebook and Twitter snippets.
* Extended Open Graph Images support, including image manipulation.
* Canonical, with full WPMUdev Domain Mapping, subdomain and HTTPS support to prevent duplicated content.
* Schema.org LD+Json script that adds extended search support for Google Search and Chrome.
* Schema.org LD+Json script for Knowledge Graph (Personal/Business site relations, name and logo).
* Advanced Schema.org LD+Json script for Breadcrumbs (just like the visual one) which extends page relation support in Google Search.
* Schema.org LD+Json script to show the correct site name in Google Breadcrumbs.
* Publishing and editing dates, accurate to the day.
* Link relationships, with full WPMUdev Domain Mapping and HTTPS support.
* Simple Sitemap with Pages, Posts and Custom Post Types (CPT), which listens to the in-post settings.
* Feed excerpts and backlinks to prevent content scraping.

**This plugin goes further, behind the screens it:**

* Prevents canonical errors with categories, pages, subdomains and Multisite Domain Mapping.
* Disables 404 pages and empty categories from being indexed, even if they don't send a 404 response.
* Automatically notifies Google, Bing and Yandex on Post or Page update and deletion when sitemaps are enabled.

**This plugin allows you to manually set these values for each post, page, supported CPT and term:**

* Title
* Description
* Canonical URL
* Robots (nofollow, noindex, noarchive)
* Redirect, with optional Multisite spam filter (Post/Page/CPT only)
* Local on-site search settings (Post/Page/CPT only)

**This plugin allows you to adjust over 90 site settings, including:**

* Title and Description Separators and additions.
* Automated description output.
* Schema.org output, including Knowledge Graph options.
* Various robots options.
* Many home page specific options.
* Facebook, Twitter and Pinterest social integration
* Shortlink tag output.
* Link relationships
* Google, Bing, Pinterest and Yandex Webmaster verification
* Sitemap integration.
* Robots.txt sitemap integration.
* Feed anti-scraper options.
* And much, much more.

**This plugin helps you to create better content, at a glance. By showing you:**

* If the title is too long, too short, duplicated, and/or automatically generated.
* If the description is too long, too short, duplicated, has too many repeated words and/or automatically generated.
* If the page is indexed, redirected, followed and/or archived, while looking at other WordPress settings.

**We call this The SEO Bar. Check out the [Screenshots](https://wordpress.org/plugins/autodescription/screenshots/#plugin-info) to see how it helps you!**

> This plugin is fully compatible with the [Domain Mapping plugin by WPMUdev](https://premium.wpmudev.org/project/domain-mapping/) and the [Domain Mapping plugin by Donncha](https://wordpress.org/plugins/wordpress-mu-domain-mapping/).<br>
> This compatibility ensures **prevention of canonical errors**. This way your site will always be correctly indexed, no matter what you use!<br>

= Caching =

This plugin's code is highly optimized on PHP-level and uses variable, object and transient caching. This means that there's little extra page load time from this plugin, even with more Meta tags used.
A caching plugin isn't even needed for this plugin as you won't notice a difference, however it's supported wherever best suited.

**If you use object caching:**
The output will be stored for each page, if you've edited a page the page output Meta will stay the same until the object cache expires. So be sure to clear your object cache or wait until it expires.

**Used Caches:**

* Server-level Opcode (optimized).
* Staticvar functions (prevents running code twice or more).
* Staticvar class (instead of discouraged globals, prevents constructors running multiple times).
* Object caching for unique database calls and full front-end output.
* Transients for process intensive operations and persistent communication with front-and back end.

**All caching plugins are supported. If you use one, be sure to clear your cache when you want to robots to notice your changes.**

= Compatibility =

**Basics:**

* Full internationalization support through WordPress.org.
* Extended Multibyte support (CJK).
* Full Right to Left (RTL) language support.
* Extended Color vision deficiency accessibility.
* Screen reader accessibility.
* MultiSite, this plugin is in fact built upon one.
* Detection of robots.txt and sitemap.xml files.
* Detection of theme Title output "doing it right" (or wrong).

**Plugins:**

* W3 Total Cache, WP Super Cache, Batcache, etc.
* WooCommerce: Shop Page, Products, Product Breadcrumbs, Product Galleries, Product Categories and Product Tags.
* Custom Post Types, (all kinds of plugins) with automatic integration.
* WPMUdev and Donncha's Domain Mapping with full HTTPS support.
* WPMUdev Avatars for og:image and twitter:image if no other image is found.
* bbPress: Forums, Topics, Replies.
* BuddyPress profiles.
* Ultimate Member profiles.
* AnsPress Questions, Profiles and Pages, also Canonical errors have been fixed.
* StudioPress SEO Data Transporter for Posts and Pages.
* WPML, URLs, full sitemap and per-page/post SEO settings (Documentation is coming soon).
* qTranslate X, URLs, per-page/post SEO settings, the main language's sitemap (Documentation is coming soon).
* Polylang, URLs, per-page/post SEO settings, the main language's sitemap.
* Confirmed Jetpack modules: Custom Content Types (Testimonials, Portfolio), Infinite Scroll, Photon, Sitemaps, Publicize.
* Most popular SEO plugins, let's not get in each other's way.
* Many, many more plugins, yet to be confirmed.
* Divi Builder by Elegant Themes
* Visual Composer by WPBakery
* Page Builder by SiteOrigin
* Beaver Builder by Fastline Media

**Themes:**

* All themes.
* Special extended support for Genesis & Genesis SEO. This plugin takes all Post, Page, Category and Tag SEO values from Genesis and uses them within The SEO Framework Options. The easiest upgrade!

If you have other popular SEO plugins activated, this plugin will most likely automatically prevent SEO mistakes by deactivating itself on almost every part.

= Transferring SEO data using SEO Data Transporter =

Because this plugin was initially written to extend the Genesis SEO, it uses the same option name values. This makes transferring from Genesis SEO to The SEO Framework work automatically.

> If you didn't use Genesis SEO previously, Nathan Rice (StudioPress) has created an awesome plugin for your needs to transfer your SEO data.
>
> Get the [SEO Data Transporter from WordPress.org](https://wordpress.org/plugins/seo-data-transporter/).
>
> Usage:<br>
> 1. Install and activate SEO Data Transporter.<br>
> 2. Go to the <strong>SEO Data Transporter menu within Tools</strong>.<br>
> 3. Select your <strong>previous SEO plugin</strong> within the first dropdown menu.<br>
> 4. Select <strong>Genesis</strong> within the second dropdown menu.<br>
> 5. Click <strong>Analyze</strong> for extra information about the data transport.<br>
> 6. Click <strong>Convert</strong> to convert the data.
>
> The SEO Framework now uses the same data from the new Genesis SEO settings on Posts, Pages and Taxonomies.

= About the Sitemap =

The Sitemap generated with The SEO Framework is sufficient for Search Engines to find Posts, Pages and supported Custom Post Types throughout your website.
It also listens to the noindex settings on each of the items.
If you however require a more expanded Sitemap, feel free to activate a dedicated Sitemap plugin. The SEO Framework will automatically deactivate its Sitemap functionality when another (known) Sitemap plugin is found.
If it is not automatically detected and no notification has been provided on the Sitemap Settings, feel free to open a support ticket and it will be addressed carefully.

The Breadcrumb script generated by this plugin on Posts will also make sure Google easily finds related categories which aren't included within the Sitemap of this plugin.

= Other notes =

*Genesis SEO will be disabled upon activating this plugin. This plugin takes over and extends Genesis SEO.*

***The Automatic Description Generation will work with any installation, but it will exclude shortcodes. This means that if you use shortcodes or a page builder, be sure to enter your custom description or the description will fall short.***

***The home page tagline settings won't have any effect on the title output if your theme's title output is not written according to the WordPress standards, which luckily are enforced strongly on new WordPress.org themes since recently.***

> <strong>Check out the "[Other Notes](https://wordpress.org/plugins/autodescription/other_notes/#Other-Notes)" tab for the API documentation.</strong>

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
4. The SEO Settings Page. With over 90 settings, you are in full control. Using the Default Settings and filling in the Knowledge Graph Settings and Social Meta Settings is recommended to do.

== Frequently Asked Questions ==

= Is The SEO Framework Free? =

Absolutely! It will stay free as well, without ads or nags!

= I have a feature request, I've found a bug, a plugin is incompatible... =

Please visit [the support forums](https://wordpress.org/support/plugin/autodescription) and kindly tell me about it. I try to get back to you within 48 hours. :)

= Is this really a Framework? =

This plugin is not in particular a framework in a technical sense, but it is built with a framework's mindset. It is however a framework for your website's SEO, a building block that keeps everything together.
This means that this plugin will do all the great Search Engine Optimization, and also allows for extensions and real-time alterations. For when you really want or need to change something.
Extensions built for this plugin might just as well work as a standalone. The SEO Framework provides an easier and cached way of doing so, however.

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

Nope! Only premium extensions. These are planned and being developed.

= If a premium extensions is released, what will happen to this plugin? =

This plugin is built to be an all-in-one SEO solution for professional environments, so:

1. No advertisements about the premium extensions will be placed within this plugin.
1. No features will be removed or replaced for premium-only features.
1. The premium extensions will most likely only be used for big-business SEO. Which are very difficult to develop and which will confuse most users anyway.

= I've heard about an extension manager, what's that? =

Currently it's not available. When it is, it will allow you to download and activate extensions for The SEO Framework. It will support both multisite and single-site and the registration will be based on the Akismet plugin.

= The sitemap doesn't contain categories, images, news, etc. is this OK? =

This is not a problem. Search Engines love crawling WordPress because its structure is consistent and well known.
If a visitor can't find a page, why would a Search Engine? Don't rely on your sitemap, but on your content and website's usability.

= What's does the application/ld+json script do? =

The LD+Json scripts are Search Engine helpers which tell Search Engines how to connect and index the site. They tell the Search Engine if your site contains an internal search engine, what sites you're socially connected to and what page structure you're using.

= The (home page) title is different from the og:title, or doesn't do what I want or told it to. =

The theme you're using is using outdated standards and is therefore doing it wrong. Inform your theme author about this issue.

Give the theme author these two links: https://codex.wordpress.org/Title_Tag https://make.wordpress.org/themes/2015/08/25/title-tag-support-now-required/

If you know your way around PHP, you can speed up this process by replacing the `<title>some code here</title>` code with `<title><?php wp_title('') ?></title>` within the `header.php` file of the theme you're using.

= The meta data is not being updated, and I'm using a caching plugin. =

All The SEO Framework's metadata is put into Object cache when a caching plugin is available. The descriptions and schema.org scripts are put into Transients. Please be sure to clear your cache.
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

= 2.6.6 - Semantic Structures =

**Release date:**

* June 14th 2016

**Summarized**

* Page builders are great for if you want to style your website, and when you want to do it fast.
* So from this update the Divi Builder, Visual Composer, Beaver Builder, and the Page Builder by SiteOrigin are now fully supported.
* Various bugs have also been fixed, to improve your experience. One particular bugfix is related to Polylang; this bugfix should improve performance and reduce other bugs from happening as well.
* The Automated and Manual description output has been improved in several ways as well.
* For developers, a class has been removed. All functions within have been moved to more suitable classes. This change will reduce server resource usage and increase overall performance.

**SEO Tip of the Update - Know your Keywords:**

* To know how your website is found, sign up for Google Search Console. Over a couple of days (or weeks), elegant data has been accumulated about your website.
* When you go to the Search Analytics within the Search Console, you'll see a list of queries people have used to find your website.
* The queries can be used as keywords, they are excellent starting points for new post titles and subjects. Go ahead, use them!

**For developers - About the class removal:**

* Because the plugin makes use of a "[Facade pattern](https://en.wikipedia.org/wiki/Facade_pattern)". And through class extending, all functions within the plugin are available at all times.
* In trade for increased resource usage this does make the plugin very compatible, faster, and easier to work with.
* This plugin serves one single responsibility: Outputting SEO data, and determining the reason why. Therefore, although it looks like a "[God object](https://en.wikipedia.org/wiki/God_object)", it's not.
* Please keep in mind that all functions from any class are available through the "Facade object". Because of this, the class removal shouldn't cause issues for developers.
* You can call this "Facade object" through a single cached function (rather than a global variable), this cached function is `the_seo_framework()`.
* If you wish to extend this plugin, feel free to ask me for any details or suggestions at the [Support forums](https://wordpress.org/support/plugin/autodescription).

**Detailed Log:**

***There's something to be said about [all these details](https://theseoframework.com/?p=1365#detailed).***

= 2.6.5.1 - Schematic Hotfix =

**Release date:**

* June 9th 2016

**Summarized:**

* A typo in the Knowledge Graph settings check caused the Knowledge Graph not to be output, even if the option was enabled.
* Because of this, the LD+Json revisional cache key has been bumped up as well.
* For developers, as this update is so (relatively) minor, it's not documented on the plugin changelog page, nor a tag will be made.

= 2.6.5 - Systematic Support =

**Release date:**

* June 8th 2016

**Summarized:**

* Another maintenance release for The SEO Framework 2.6 is coming right at you!
* While Polylang creates Taxonomies from Pages without acknowledging it being an Archive, I have found a workaround for the Title not to show up correctly.
* Also, bbPress' Original Post within a topic is now shown correctly, although the bbPress 2.6.0 update will resolve this as well.
* On special pages like the Forum page of bbPress, no more cache key conflicts will be present, this makes it a recommended update.
* WPML Multilingual domains are now also fully supported. So instead of just adding a subdomain, this plugin now also takes whole new domains into consideration.
* The URL generation has also been improved once more. It now contains Canonical Pagination support, and expanded Plain Permalink structure support.
* And just when you thought new options were available, now you won't be fooled again.
* For developers it's now much easier to debug, and there are also three (actually four) brand new filters.
* And last but not least, 404 pages and Search Pages now have a Canonical URL, right towards your Homepage.

**SEO Tip of the Update - Skyscraper Technique:**

* Have you found something that greatly interests you, and do you think you can write a better article than you've found?
* Go write it, in your own words and style. Don't forget to make it much longer, better and more detailed. Don't forget to add images.
* Finally, share the content, reach out to the public, and of course your public. This will increase your rankings significantly.
* This method is known as the Skyscraper Technique; it's used for and as this SEO tip, minus the images, details or length.

**Detailed Log:**

***Sometimes, you just have to look [a little harder](https://theseoframework.com/?p=1339#detailed).***

= 2.6.4 - Biographic Consistency =

**Release date:**

* May 31st 2016

**Summarized:**

* This update is another maintenance release. It's highly recommended to install this update to prevent incorrect meta values.
* The fix that has been applied now set at the root cause for the issues that have persisted since the release of 2.6.0, rather than later on.
* I also bring you Author Descriptions. This description is pulled from the user's profile biography input and is handled the same way as other automated descriptions.

***Sorry, no SEO Tip of the Update this time.***

**Detailed log:**

***The smaller things in life can be [found here](https://theseoframework.com/?p=1284#detailed).***

= 2.6.3 - Plain Loops =

**Release date:**

* May 29th 2016

**Summarized:**

* This update is another maintenance release. This essentially means that bugs have been fixed.
* Most of the bugs that have been fixed are regarding URL generation for the shortlink and relationships.
* Next to that, the default "plain" permalink structure has now obtained a Canonical URL output.
* A much more reliable hook has been found to be used prior to caching the query. This prevents not only an infinite redirect loop in conjunction with some plugins, but will also prevent incorrect metadata being used on some themes.

**SEO Tip of the Updated - Keep it Alive:**

* Have you written about something already, and is the subject coming up again? Link back to the older publication.
* About 2 to 5 internal links within the content to older pages will help your visitors understand your hierarchy and find more related content (of which they're already interested in).
* It will also inform Search Engines on other important pages within your site; this is also known as link juice and will increase your Search Rankings overall.

**Detailed log:**

***Want to know which specific bugs have been fixed? Go ahead, you can [read them here](https://theseoframework.com/?p=1269#detailed).***

= 2.6.2 - Condensed Associations =

**Release date:**

* May 26th 2016

**Summarized:**

* Simply put, this update fixes a few bugs. One of these bugs caused all recognized Post Types to be judged wrongfully for supporting SEO.
* A very small bug with a major impact has also been fixed. This bug caused WooCommerce Shop Pages to canonicalized to the latest product.
* This update also makes sure all translations of 2.6.0 and later are put into effect.
* UTF-8 required languages are now correctly rendered on PHP5.3 and lower within the SEO bar, like Russian.
* And for developers, the URL generation has been slightly refactored. With more reliable and lighter variables being used throughout the generation.

**SEO Tip of the Updated - Geo Targeting:**

* Is your website about a local business? Then be sure to sign up for Google Businesses. This will massively increase your search presence, by an artificial result.
* It also greatly helps to have your server located near your target audience's location. The website will not only respond faster, but Search Engines can pick up descriptive signals from it.
* If your business resides and only serves in Belgium, for example, it's also better to have a ".be" domain name extension rather than a ".com" domain name extension.

**Detailed log:**

***Say hello to [my little friend](https://theseoframework.com/?p=1254#detailed).***

= 2.6.1 - Pacified Handling =

**Release date:**

* May 19th 2016

**Summarized:**

* The new plugin detection features from 2.6.0 "Pragmatic Foundation" was done in a manner that any arbitrary plugin could be detected with conflicting namespaces.
* For this reason, I've taken JetPack's philosophical standpoint on this and implemented it within The SEO Framework.
* Also, the new special Greek/Latin duplicated word counter now also works great on PHP versions 5.2 and 5.3.
* And in some configurations, the Home Page Title could have been rendered empty, so I got that fixed as well!
* For developers, many new filters have been added for plugin detection, be sure to check them out in the detailed log.

**Feature highlights:**

* **New:**
	* Open Graph Product types are now supported on WooCommerce products.
* **Improved:**
	* The Twitter Image tag has been updated to the latest standards.
	* Revised plugin detection.
	* A probable memory leak on archives has been fixed.

**SEO Tip of the Update - Image Descriptions:**

* Do you use Images in your Posts and Pages? Be sure to describe them! This way people are able to find your website through the Image Results.
* This can be easily done when editing the Image through WordPress' Media Library or when inserting an Image in the content. When you click on an Image, you can define various details.
* The Alt Text is to be used. If that's not found, the Image Caption is used. And if that's also not found, then the Image Title will be used.
* Make sure it clearly describes the Image, and be aware that it will fall back to it when the browser can't render the Image. It's also a great additions for people who are vision impaired.

**Detailed log:**

***Love details? Then head onto [the detailed changelog](https://theseoframework.com/?p=1231#detailed).***

= 2.6.0.2 - Tough Understructure =

**Release date:**

* May 18th 2016

**Summarized:**

* The SEO Framework 2.6.0 "Pragmatic Foundation" (released May 17th) brought a whole new way for determining the Page Type, synchronous with both the admin side and the front-end.
* However, in some installations, a few WordPress variables may have not yet been assigned when called within this plugin.
* This caused a wrong cache initialization, and therefore SEO values and settings were incorrectly rendered.
* This minor update adds determination for if the WordPress Query or Admin Page Type are accessible before caching anything to make sure no wrong status values are being used throughout the plugin.

**Detailed log:**

***Want to read the verbose "detailed" log? Please refer to [the detailed changelog](https://theseoframework.com/?p=1222#detailed).***

= 2.6.0.1 - The real Pragmatic Foundation =

* This minor update fixes a fatal error after updating to 2.6.0. Sorry about that!

= 2.6.0 - Pragmatic Foundation =

**Release date:**

* May 17th 2016

**Preamble:**

* This is a dot version bump update, which is done so as the core code has been changed drastically. Nine new classes have been added to maintain structured code, including many more function to fetch data easily and consistently.
* With hundreds of changes, I had to find a new way to present this update in an understandable manner. So here goes!

**Summarized:**

* With over 200 notable changes, I bring you a new Pragmatic Foundation.
* Most importantly, this update allows you to be better informed about your website's index status, through the much improved SEO bar.
* As the issue of the incorrect title length has finally been found, this update glorifies and updates its plugin's title counter once more.
* Many new options have been included within the SEO Settings page. Including much desired Title, Description and Schema.org options.
* WPML compatibility has received a rework, now all canonical URLs in the sitemap and front-end are always correct. The qTranslate X settings are now also being taken into account when outputting canonical URLs.
* A new script has been added on the front-page. This will make sure the Breadcrumb homepage name will be correct in the Search Engine Results Page.
* The breadcrumb script has been expanded to work on posts with multiple and nested categories. These scripts now also work on WooCommerce products. Don't be surprised if you suddenly have all kinds of scripts in the header! These scripts help Google and other Search Engines better understand your website.
* And for developers, with the code expanding rapidly, this update brings new light to the code by reorganizing the code into dedicated classes and is including many major refactorizations.

**Feature highlights:**

* **New:**
	* WooCommerce Schema.org breadcrumbs.
	* Intelligently nested Schema.org breadcrumbs.
	* Schema.org website name.
	* Vibrant character counters for when you need extra visual assistance.
	* The SEO Bar's tooltip is now able to speak to you through accessiblity tools.
	* More Automated Description options.
	* New Archive Title, Sitemap and Schema.org options.
	* Yandex Sitemap pinging support.
	* Automatic option merging on update.
	* AJAX integration when adding tags.
	* Personalized error handling for developers.
	* Over 150 new public functions for developers.
	* More than 20 brand new filters for developers.
	* WP Query Admin synchronization for developers.
	* Automated setting navigation tabs for developers.
* **Improved:**
	* Better Automated Description sentence punctuations.
	* Modernized and Smarter SEO bar, with many more conditional checks.
	* Many linguistic improvements, with more flow in the SEO Bar.
	* Extended Title Fix extension support.
	* More efficient cache key generation.
	* Adaptive WPML & qTranslate X URL generation.
	* Better editorial translations.
	* JetPack compatibility.

**SEO Tip of the Update: Redirects & Canonical**

* A change in the Canonical URL or the use of a 301 Redirect URL indicate that your page has moved.
* These can be seen by Search Engines as the same. However, the 301 Redirect enforces the relocation of the page to everyone, whereas the Canonical URL softly indicates.
* When changing the Canonical URL of a Page, you're telling robots to look and elsewhere, be sure to include a link to the new Page on the canonicalized Page to indicate where everything has moved to.
* However, it's even better to enforce a 301 redirect. This makes sure both your visitors as Search Engines know where to be instantaneously.
* If you've changed the permalink of a popular post, you should create an empty post that follows one of the said examples.

**Announcements:**

* Back when I started developing this update, I announced a new extension plugin for The SEO Framework! [Title Fix - The SEO Framework](https://wordpress.org/plugins/the-seo-framework-title-fix/).
* This update ensures extra compatibility with the Title Fix plugin, this will add back removed title features for if the theme is doing it wrong and when the Title Fix plugin is active.

**About: Plugin progression and help wanted:**

* This dear project has taken me over 2500 hours to perfect and maintain. This update alone has cost me over 300 hours to complete.
* I really want to keep this project free. In fact, the upcoming Author SEO is actually planned to be a premium extension, but it will be free.
* I also want to turn this project into my full-time job, even if it's free from monetization and/or premium content.
* And I will keep my promises, always: This plugin will stay free of ads, (self-)reference and this plugin will continue to be maintained.
* All with all, this means I can't do it without your help!
* Please consider making a donation. The link can be found on the WordPress.org plugin page, or [right here](https://theseoframework.com/?p=572).
* Thank you so much for your support!

**About: Changes in Changelog**

* I love to push many changes at once every update, as I'm only happy when everything works. If I find a bug, I'll be sure to fix it!
* So to clean up the massive all-inclusive changelogs, detailed information on updates are put aside and are visible on the plugin's website.
* With each update, I try to find better ways for presenting information and I try to minimize confusion.

**No Stone Left Unturned:**

* This massive update has touched almost every aspect of this plugin.
* With this, many fixes are put into effect for all known and possibly many unknown bugs. Better standards have been put into place, both for WordPress and the coding world.
* All changes have been tested thoroughly. However, it's always possible something has been overlooked. If you find anything out of place, let me know in the [plugin support forums](https://wordpress.org/support/plugin/autodescription)!

**For everyone - Squared SEO Bar:**

* From now on, the SEO bar is flat and squared. This gives a more modern look and feel.
* Screen Readers are now able to enunciate the tooltip when shown.

**For everyone - New Options Merging:**

* From this plugin update, new default options are automatically merged with the previous options on update.
* This way, you don't have to update each site you own with the new recommended features.
* No options will be overwritten in this process, and it only happens once in the admin area when the plugin is active and the one who can edit the site's SEO options is loading the site.
* This new feature is multisite compatible and can be disabled through a filter.
* When this happens, a dismissible update notification will show whenever this happens.
* The notification is unbranded, it will say "New SEO options have been updated." on the SEO Settings Page and will add "View the new options here." on all other Admin Pages.

**For everyone - Schema Markup:**

* New schema markup has been added, this helps Search Engines better understand your website.
* Breadcrumbs have been expanded, to support nested categories and multiple categories. Now you can see multiple breadcrumb scripts to help Search Engines better understand your website's structure.
* Breadcrumbs scripts now also work on WooCommerce products, enjoy!

**For translators - High priority translations:**

* Please look for **Front-end output** comments within the translation page to find high-priority translations.

**For translators - Linguistic improvements and other changes:**

* Objective translations for grammatically gendered noun types like "this post" (male in Dutch) and "this page" (genderless in Dutch) within sentences which are fetched dynamically (like "Product" and "Product Tag" for WooCommerce) couldn't be translated correctly.
* Therefore, I've changed these types of sentences without any loss of understanding.
* Small changes within translations happen over time, although I try to keep these to the necessary minimum. Nevertheless, as this is an ongoing project you can expect continuous improvements wherever possible. Translating WordPress and its plugins are a team effort :).
* Other small changes include conversion of WordPress slang to real English. Like "Paged" to "Paginated".
* Over time, inconsistencies have appeared within the language used within this plugin. These have been fixed. If you still find any, please notify me through the support forums and I'll address them.
* Thanks @pidengmor for many (over 40) linguistic improvements, they're really appreciated! Thank you so much for your time!
* I also want to make a big shout out to all the other translators who have contributed to this plugin! <3
* And if you wish to become a language editor, just let me know!

**For developers - Performance:**

* Because this plugin has grown massively in size with this update, the memory use has been increased marginally (to 4MB from 2.7MB). This shouldn't cause issues. And to improve this, singleton methods are going to be considered.
* Because many more items are being rendered, the plugin is a tad slower than before, even though overall performance has been improved. This won't affect your website's performance noticeably.
* This plugin has been benchmarked with PHP Xdebug. With it I found that some functions affected the performance marginally, and have thus been fixed accordingly.

**For developers - Refactoring classes:**

* The classes `AutoDescription_DoingItRight` and `AutoDescription_Generate` among others have been greatly refactored to improve performance and maintainability.
* Almost all public functions before this overhaul have maintained their initial behavior, the generation of the output has just been split over multiple functions. If this is not the case, and unexpected variables are input, a deprecation notice is output.

**For developers - WordPress Query Sync:**

* The current WordPress query only works on the front-end.
* `/inc/classes/query.class.php` contains alternative functions based on the WordPress query. However, although these functions work just like WordPress Core query functions, they also look for the screens within the admin area.
* These functions do not work on custom post types, yet. But they do work on WooCommerce products and product categories.
* This resulted in easing the whole code base as it doesn't have to check for admin/front-end per-function anymore. Further improvements are planned, but this update already contains most of the overhaul.

**Looking forward - Upcoming features:**

Not all planned features made it into 2.6.0. The following features are planned to be released in a future version.

* Author SEO (Title, Description, Twitter, Facebook, Google+, etc.).
* Google+ output options.
* The SEO Bar options.
* Per page title additions options.
* Canonical SEO (URL scheme options).
* Article modified time output options, just like the Sitemap time output options.
* Image Description as an excerpt for the attachment's page meta descriptions.
* Twitter card plugin detection and subtle notification of such.
* WP.me shortlink integration.

**Detailed log:**

***There are a lot more changes in this update which did not make it to this page. Please refer to [the detailed changelog](https://theseoframework.com/?p=1196#detailed).***

*What do you think of this change? Let me know at [Slack](https://wordpress.slack.com/messages/@cybr/) (no support questions there, please)!*

= Full changelog =

**The full changelog can be found [here](http://theseoframework.com/?cat=3).**

== Upgrade Notice ==

= 2.6.4 =
Highly recommended update that fixes various query checks and caches.

= 2.6.3 =
This update resolves an issue with the Home Page (blog) Title and Description output.

= 2.6.2 =
This update resolves an issue with the WooCommerce Shop Page Canonical URL. Installing this update is therefore highly recommended.

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

= Want to test the latest version before it's released? =

If there's a beta, it will be available [on Github](https://github.com/sybrew/the-seo-framework). Please note that changes there might not reflect the final outcome of the full version release. Use at own risk.
