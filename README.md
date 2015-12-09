# The SEO Framework

# Description

**The lightning fast all in one automated SEO optimization plugin for WordPress**

> <strong>This plugin strongly helps you create better SEO value for your content.</strong><br />
> But at the end of the day, it all depends on how entertaining or well-constructed your content or product is.
>
> No SEO plugin does the magic thing to be found instantly. But doing it right helps a lot.<br />
> The SEO Framework helps you doing it right. Give it a try!
>
> The Default Settings are recommended in the SEO Settings page. If you know what you're doing, go ahead and change them! Each option is also documented.

## What this plugin does, in a few lines
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

## Numbers don't lie
Optimizing SEO is a fundamental process for any website. So we try to be non-intrusive with The SEO Framework.
The SEO Framework is byte and process optimized on PHP level, with each update the optimization is improved when possible.

* This plugin is written with massive and busy (multi-)sites in mind.
* This plugin is 197% to 867% faster compared to other popular SEO plugins.
* This plugin consumes 177% to 260% fewer server resources than other popular SEO plugins.
* 15% fewer database interactions (numbers may vary on this one depending on plugin compatibility).
* 100% fewer advertisements. Let's keep it that way.

## Completely pluggable
The SEO Framework also features pluggable functions. All functions are working and can be called within the WordPress Loop.
We have also provided an API documentation located at [The SEO Framework API Docs](http://theseoframework.com/docs/api/).

## Still not convinced? Let's dive deeper

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

## Caching

This plugin's code is highly optimized on PHP-level and uses variable, object and transient caching. This means that there's little extra page load time from this plugin, even with more Meta tags used.
A caching plugin isn't even needed for this plugin as you won't notice a difference, however it's supported wherever best suited.

**If you use object caching:**
The output will be stored for each page, if you've edited a page the page output Meta will stay the same until the object cache expires. So be sure to clear your object cache or wait until it expires.

## Compatibility

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
* BuddyPress: Front-end profiles and pages.
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

## Transferring SEO data using SEO Data Transporter

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

## Other notes

**This plugin copies data from the Genesis SEO Meta, this means that when you use Genesis, you can easily upgrade to this plugin without editing each page!**

*Genesis SEO will be disabled upon activating this plugin. The new SEO Settings page adds extended SEO support.*

***The Automatic Description Generation will work with any installation. But it will exclude shortcodes. This means that if you use shortcodes or a page builder, be sure to enter your custom description or the description will fall short in length.***

***The home page tagline settings don't have any effect on the title output if your theme's title output is not written according to the WordPress standards, which luckily are enforced strongly on new WordPress.org themes since recently.***

> <strong>Check out the "[Other Notes](https://wordpress.org/plugins/autodescription/other_notes/#Other-Notes)" tab for the API documentation.</strong>

*I'm aware that radio buttons lose their input when you drag the metaboxes around. This issue will be fixed in WordPress 4.5.0 according to the milestones.*
*But not to worry: Your previous value will be returned on save. So it's like nothing happened.*

# Installation

1. Install The SEO Framework either via the WordPress.org plugin directory, or by uploading the files to your server.
1. Either Network Activate this plugin or activate it on a single site.
1. That's it!
1. Let the plugin automatically work or fine-tune each page with the metaboxes beneath the content or on the taxonomy pages.
1. Adjust the SEO settings through the SEO settings page if desired. Red checkboxes are rather left unchecked. Green checkboxes are default enabled.
