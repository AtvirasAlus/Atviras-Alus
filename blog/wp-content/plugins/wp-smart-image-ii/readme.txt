=== WP Smart Image II ===
Contributors: Dario Ferrer (@metacortex)
Tags: images, image, thumbnail, thumbnails, attachment, attachments, post, posts, photo, photos, picture, pictures, layout, design, development, webdesign, picture, img
Donate link: http://www.darioferrer.com
Requires at least: 2.8
Tested up to: 3.0
Stable tag: 0.2

Powerful, reliable and lightweight plugin which helps you to easily show post images and handle them. Essential tool for web designers and developers.

== Description ==

WP Smart Image II is a Wordpress plugin which gives you a whole control over the thumbnails that you wish to show with posts and pages (e.g. the index of a website).

This plugin combines the best image management functionalities in WordPress and turn their handling in a very easy job.

= How works WP Smart Image II =

WP Smart Image II takes all Wordpress image functions and put on your hands in a simple and right way.

= "Right way"?... Are there wrong ways to show post images? =

Yes! Custom fields (e.g.) is a very wrong and annoying way to do this job. If you are used custom fields to assign images to posts, with WP Smart Image II will forget all the extra work and will enjoy the real process automation that has been in WordPress all this time and probably you never knew existed.

= What's the problem with Wordpress native image engine? =

No problem! Otherwise, the [WordPress](http://www.wordpress.org) image engine is one of the most advanced and flexible ones so far. WordPress offers a wide range of functionalities and tags, which allows for and easy handling of images and files, associating them with posts, categories, pages and other elements with total freedom.

However, the problem with all these funcionalities is that finding the propper way to associate data and get a specific result can get really hard. In an attempt to do that, many people have tried to do it by setting through custom fields, ignoring that WordPress has a large platform with many resources at our disposal to do that.

= What isn't Wp Smart Image II? =

It is not a filter that works inside the content. WP Smart Image II only works in the template areas and it's use is focused to the template's file system.

It is not a plugin that changes other functionalities behaviour. It's a resource that lets you take advantage of the already existing functionalities provided by WordPress.

= What you can do with WP Smart Image II =

**General**

* A wide range of functionalities using a single tag.
* A plugin that uses the Wordpress resources in a very productive way and does not overload your system.
* Stable, reliable and always working.
* A tool with many, many options to give you the control over your design, blogging or development.

**Design & layout**

* Add `"width"` and `"height"` attributes through CSS or HTML methods.
* Personalize the `"alt"` and `"title"` attributes if they haven't been configured yet.
* Choose between the four presets WordPress sizes: **Thumbnail**, **Medium**, **Large** and **Full**. Additionally, you can create new plugin preset sizes with [Max Image Size Control](http://wordpress.org/extend/plugins/max-image-size-control/), and easily handle them through WP Smart Image II.
* Customize the generic images and its paths for all the sizes.
* Link the image to the article or leave it without a link.
* Add a link to the full version of the image from the thumbnail or any size.
* Add a link to the image's attachment page from the thumbnail or any size.
* Show random images per post (instead only one).
* Show preset images at the posts list if images are not setting to appear in the content. Also you can choose to leave it without images.
* Adapt the tag type to website DTD for a right W3C valitation.

**Web Development**

* Choose between "echo" or "return" the tag.
* Add custom CSS classes and ID to properly handling the images through CSS, javascript, PHP and others web resources.
* Get the image url instead of the whole tag.
* Easily add and define attributes to image's links, as `"rel"`, `"class"`, `"id"` and `"target"`.
* Literally slice the image data in pieces and post them separately (width, height, mimetype, ID, etc.)
* Choose to run the plugin under **PHP mode**, without need to db queries for common tasks.
* Add custom attributes as you wish (as javascript events) and apply dynamic data tho them.
* Handle multiple images per post directly or in `array` mode as well.
* Switch easily between double or single quotes for tag attributes.

**Blogging**

* Choose the image to show throught a friendly widget in the editor.
* Choose any image from your Media Library and assign it to the post.
* Show/hide thumbnails in RSS feeds.
* Customize sizes in RSS feeds.
* Assing titles of the images directly from your editor box
* Choose any image from your Media Library and assign it to the post, even if the image isn't attached to the post.
* Use The Post Thumbnail editor box to manage images processed under the WPSI engine.

Enjoy designing!

= Localization =

* Dutch (nl_NL) - Rene from [WP Webshop](http://wpwebshop.com/)
* Russian (ru_RU) - Vladimir from [ShinePHP](http://www.shinephp.com/)
* Spanish (es_ES) - Dario Ferrer

If you have been translated this plugin into your language, please let me know.

== Installation ==

1. Upload `wp-smart-image-ii` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php wpsi(); ?>` in your template
1. You can add further customization throught `Settings > WP Smart Image II Menu`

= If you are upgrading from WP Smart Image =

1. Uninstall WP Smart Image. Delete the plugin's folder.
1. Install WP Smart Image II.
1. Change **only** the parameter's syntax placed into your tags (See [documentation](http://www.darioferrer.com/wpsi/wpsi-en/wpsi-ii-parameters) for more details).
1. That's it. Enjoy the plugin.

= Uninstall =

1. If you wish to remove the plugin completely, press the buttons called "Remove data" and "Remove postmeta" to clean the Wordpress Smart Image II DB table.
1. Deactivate the plugin through the 'Plugins' menu in WordPress.

== Upgrade Notice ==

Please read the **Installation** section

== Frequently Asked Questions ==

= Where can I find Documentation/Support about this plugin? =

The main documentation site is the [Lab](http://www.lab.darioferrer.com). Here you can find all detailed information about WP Smart Image II.

Also you can find direct support at WPSI II forums: [English users](http://www.darioferrer.com/que/viewforum.php?f=4) | [Spanish users](http://www.darioferrer.com/que/viewforum.php?f=2)

= WP Smart Image II uses custom fields for store basic data? =

No. Using custom fields to show attached images is a wrong method. Yes, it is relatively famous, but is wrong. [WordPress](http://www.wordpress.org) has a very advanced ways to control post's images. However, saying is not the same than do it, because many times it is some difficult to achieve tha we want in this area. WP Smart Image II simply makes this work a quite more easy to you.

However, WP Smart Image II works with postmeta fields to more complex jobs (as show external images) storing a minimal needed data to make the job.

= WP Smart Image II adds many data in my DB? =

Noup. This plugins uses all existing post data you have been entered through Wordpress Editor. Only creates a little field in options table to save your settings. If you choose to use the editor's widget, one field per post will be created in the _postmeta table (only if you make any configuration throught the widget; otherwise no fields will be created).

= How can I settings my images to be shown? =

* Upload the images through your edition panel (required for database file association).
* In the Gallery section **drag the image you want to show to the first position**, then it will be shown, even if you don't use it in the content.
* That's it.

= Hey, I can't see the widget in my editor! =

Just activate the checkbox for option "Activate editor box" through Settings page. Save your settings.

= This new widget disables my previous settings? =

Absolutely not. Your old settings remain unbroken.

= Where I must to place the tag? =

You should place the tag into [the loop](http://codex.wordpress.org/The_Loop) (see [screenshot #3](http://wordpress.org/extend/plugins/wp-smart-image/screenshot-3.png) for a graphic example)

= I can place the parameters in an unordered way? =

Yes, you can combine parameters without an specific order.

= All parameters are right to all situations? =

Some parameters may not appear depending of your settings. E.g. a "rel" attribute will not shown in an unlinked image, because "rel" is a property of links. In cases like this, if you set wrong parameters the plugin simply ignore them and works anyway. Another example: if you have a Stric DTD site and if you activate a "target _blank", of course you'll ruin your standard. If you add a fixed ID's to several images or links, you are proceeding in a wrong way. 

Otherwise, if you are trying to implement some javascript/ajax toy defining "rel" or "id" parameters , you are in a good way to achieve what you want, depending of your intentions.

For a better handling of WPSI II parameters, start from the [parameters table](http://www.lab.darioferrer.com/doc/index.php?title=WPSI_II_-_Parameters_Table), so you can taking a look to available options.

= "Dario, you forgot to add certain function..." =

Please let me know you're thinking through any of ways above. Thank you!.

= "I'm a programming guru and I think you can modify this string in this way..." =

All your suggestions are welcome. Thank you!.

= "I translated your plugin man!" =

Nice... let me know! :D

== Screenshots ==

1. A small sample of what you can do with WPSI II
2. WP Smart Image II Logo
3. The editor box.
4. Settings page.

== Changelog ==

= 0.2 =
* Maximum compatibility with Wordpress 2.8.X, 2.9.X and 3.0.
* Mew admin interface more fresh and usable.
* PHP Mode now works totally automated, without need of file editing.
* New languages: Russian and Dutch.
* New parameter `number` to obtain multiple images per post.
* New parameter `array` for a better handling over multiple images per post.
* New parameters `custom` and `acustom` to add custom attributes to images and links, respectively.
* New parameter `quote` to switch between singles and double quotes on links and image tags.
* Added option for manage images through `the_post_thumbnail` editor interface.
* Automatic template preparing for `post-thumbnails` features.
* Added autodetecting custom sizes from Max Image Size Control plugin.
* Defined new language directory.
* Admin header's files and codes only appear when the WPSI settings page is loaded.
* General code optimization.
* Improved form security.

= 0.1.5 =
* Deleted uneeded strings for compatibility

= 0.1.4 =
* Fixed more issues on `alt` and `title` on images and links.
* Added "p" parameter to get specific images out of the loop.
* Added the function `wpsi()` to replace the actual `wp_smart_image()` and `get_wpsi()` for the return mode.
* Introducing `el_title` parameter replacing to `the_title` to avoid conflicts on some attribute strings.

= 0.1.3 =

* Fixed `alt` and `title` issue on images and links.
* Added option to backup the PHP config file.
* Code optimization.

= 0.1.2 =
* Added option to edit/backup the PHP config file on PHP Mode.
* The plugin has been prepared for accept extensions.
* Added `showtitle` parameter replacing `title` behavior (see documentation)
* Added new parameters: `alt`, `title` and `atitle` for better handling of `ALT` and `TITLE` elements (see documentation)

= 0.1.1 =
* Minor fixes on some wrong html code which broke W3C validation

= 0.1 =
* First public release