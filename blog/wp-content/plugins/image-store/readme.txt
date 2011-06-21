=== Image Store ===
Contributors: Hax
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8SJEQXK5NK4ES
Tags: e-commerce,shop,photo store,picture,image,galleries,imstore,image-store,secure,translate,translatable,watermark,
slideshow,gallery,sale,photographers,shop,online,google,shopping,cart,paypal,rss,shortcode,multi-languages,widget,prints
Requires at least: 3.0.0
Tested up to: 3.1.1
Stable tag: 2.0.9

Image Store (ImStore) is a photo gallery and store plugin for WordPress with Paypal and Google Checkout integration.

== Description ==
This plugin was created because there was a need in the WorPress community to have an images store that did not required the installation of multiple plugins. Enjoy!! and please support the plugin. :@) 

The plugin fully integrated with the WordPress database it only creates a post_expire column on the posts table.
so you will not find extra tables on your database(Cool!.. at least for me I hate extra tables).

* Example http://imstore.xparkmedia.com/photo-store
* Languages http://imstore.xparkmedia.com/languages
* Shortcode guide http://imstore.xparkmedia.com/usage

= Features =

* Payment notification.
* Paypal Cart integration.
* Google checkout integration
* WP edit image integration.
* Image RSS.
* Promotions.
* Gallery widget.
* Galley shortcode.
* Gallery expiration.
* Sort images feature.
* CSV sales download.
* CSV customer download.
* Customer Mailpress integration.
* Sales menu: To keep track of you sales.
* Image upload: Via a zip-file(no zip-mod required).
* Dynamic generation of sepia and black & white images.
* Taxomony(albums): Group Galleries using custom taxonomy.
* Price lists: Create only list and apply across galleries.
* Gallery Comments: allow user to add comments to galleries.
* Watermark function: You can add a watermark image or text. 
* Image download: Allow user to download image after purchase.
* Disable Store: Use it just like a gallery manager and not a store.
* Folder import: add galleries by just uploading image through FTP.
* Public Galleries: display your photos so that anybody can buy them.
* Secure Galleries: Secure clients photos so that only they can see them.
* User Permissions: Give access to users to specific sections of the plugin.
* Customer menu: Keep track of your galleries and customers.
* Pugin uninstall: Remove all entries added by the plugin.

== Installation ==

* Download the latest version of the plugin to your computer.
* With an FTP program,access your site's server.
* Upload the plugin folder to the /wp-content/plugins folder.
* In the WordPress administration panels,click on plugins from the menu on the left side.
* You should see the "Image Store" plugin listed.
* To turn the plugin on,click "activate" on the bottom of the plugin name.
* You should have now a new menu item called "Image Store".

= Tested on =

* MySQL 5.1.26 
* Apache 2.2.11
* Linux
* Explorer 8
* Safari 4.1
* Firefox 3.5
* Chrome 5.1
* Opera 9.6

= Recomendations =

* Change your upload folder "Gallery folder path" for security purpose Image Store > settings > gallery settings.
* Before installing the plugin set "Thumbnail size" setting to the decired size Wordpress admin > settings > media.
* DON'T provide download option for print size images use this option only for pixel sizes. 

== Frequently Asked Questions ==

* http://imstore.xparkmedia.com/blog
* http://checkout.google.com/support/sell/bin/answer.py?hl=en&answer=70647

== Changelog ==
= 2.0.9 =
* Important Security update.
* Changed: Moved select checkbox and label.

= 2.0.8 =
* Added: More currencies.
* Fixed: Searching issue.
* Fixed: Download PNG issue.
* Fixed: %category% in permalink issue.
* Fixed: WP subdirectory installation issue.
* Fixed: Download images in some paypal accounts.
* Added: Display image title.
* Added: Checkout user comment.
* Added: Shopping cart "total" in navigation.
* Added: taxonomy(album) template option.
* Added: Album pagination.
* Added: Gallery pagination.
* Added: WPTouch/Mobile css support.
* Changed: moved select checkbox, added label.


= 2.0.7 =
* Fixed: Date picker styles.
* Fixed: Permission settings.
* Fixed: Save settings issues.
* Fixed: Checkout email information.
* Added: Portuguese tranaslation.
* Added: Search image title and caption option.
* Improved: dynamic image sorting.
* Improved: html5 validation.

= 2.0.6 =
* Fixed: Trash image link broken.
* Fixed: Save imported image caption.
* Fixed: Improve image download quality.
* Fixed: Shortcode image display location.
* Fixed: Google checkout redirection to searchpage.
* Added: Preview image quality option.

= 2.0.5 =
* Fixed: Screen options labels.
* Fixed: Add to cart redirection.
* Fixed: Required checkout fields.
* Fixed: Dynamic image update/upload.
* Fixed: Email notification email and sale report.
* Fixed: Page navigation issue with hidden photo link.
* Added: Image page navigation.
* Added: Make galleries searchable.
* Added: Import metadata from image.

= 2.0.4 =
* Fixed: Shortcode issues.
* Added: Italian Translation.
* Fixed: Capabillity Issues with 3.1
* Fixed: Sales CSV download not working.
* Added: Single gallery template option.
* Changed: Removed "Protected" from gallery title.
* Changed: Image title and caption display (frontend).

= 2.0.3 =
* Important update: update cart error.

= 2.0.2 =
* Fixed: Download image error.
* Fixed: Wrong metadata information backend.
* Fixed: Not been able to change preview image size.

= 2.0.1 =
* Fixed: gallery feed 404.
* Added: Extra image security.
* Fixed: Price list sort missing.
* Fixed: Auto password generation on all post.
* Fixed: Translation redirect problem.
* Fixed: Translation permalink problem.
* Fixed: Mini thumbnail not been genarated after image edition.
* Changed: Custom post type (ims_gallery) capabilities.

= 2.0.0 =
* Code Cleanup
* Improved performace.
* Added: Google Checkout.
* Added: Gallery Comments.
* Added: Gallery name to sales reports.
* Added: Option to remove color options.
* Added: Option to remove "photos" or "slideshow" link.
* Added: Integration with custom post types and toxomony.
* Changed: Gallery logout.
* Changed: Gallery preview.
* Changed: Gallery management.
* Changed: Permalink structure.
* Changed: Paypal IPN Listener url.
* Changed: Add/edit gallery interface.
* Changed: preset all class/styles with "ims_" in the frontend.

= 1.2.5 =
* Fixed: Paypal issues.
* Fixed: Propotional discount not been sent to paypal.
* Front-end: CSS modifications.

= 1.2.4 =
* Fixed: Typos.
* Fixed: Flash upload not working.
* Changed: Gallery status display.

= 1.2.3 =
* Fixed: shopping cart showing wrong color selection.

= 1.2.2 =
* Fixed: new installation and capabilities problem.
* Fixed: settings reset after plugin update.

= 1.2.1 =
* Unstable

= 1.2.0 =
* Added: User gallery screen.
* Added: Option for admin to edit gallery id.
* Added: Status label on gallery list and edit screen.
* Added: Allow to move core files to sub directory.
* Added: feature to allow user keep track of their galleries.
* Fixed: Image creation date.
* Fixed: Add images to favorite not working.
* Fixed: Price list not seving image size unit.
* Fixed: Issue of not been able to uplaod small images.
* Changed: Flash image upload notification.
* Changed: Gallery permissions.

= 1.1.1 =
* Fixed: Sales CSV download permissions.
* Fixed: Total price format when using email notification only.
* Fixed: Serialize data showing on the image-size-dropdown menu when adding images to car.
* Admin: CSS modifications.
* Added: Image title on sale reports.

= 1.1.0 =
* Front-end: CSS modifications.
* Updates: Spanish translation.
* Admin: CSS modifications.
* Admin: HTML clean up.
* Added: Option not to expire galleries.
* Added: Feature to use gallery on the home page.
* Added: Feature use color box on wp galleries.
* Added: Image size units(in. cm. px.)
* Added: Settings for the required fields on the checkout page.
* Added: Feature recreate images after image settings have been changed.
* Fixed: Image cache after browser's cache is cleared.
* Changed: create new galleries with pending status instead of publish.

= 1.0.2 =
* Fixed: Paypal IPN issues.
* Fixed: Disable image rss.
* Fixed: Incorrect paypal cart currency type.
* Fixed: "mini" image size showing instead of preview.
* Added: Orders by email notification only(disable paypal).

= 1.0.1 =
* Fixed: Translation issues.

= 1.0.0 =
* Improved dynamic image cache.
* Fixed: misspells.
* Fixed: save gallery settings.
* Fixed: double slash on permalinks.
* Removed "add to favorites" link from unsecure galleries.
* Added: Spanish translation
* Fixed: WP thumbnail preview conflict.
* Fixed: file not being deleted from server when image was deleted.

= 0.5.5 =
* Added: drag and drop image sort(admin).
* Security fix: image url.
* Fixed: Image edit didn't create new image when thumb only was selected.
* Fixed: php error on dynamic css file for IE colorbox support.

= 0.5.4 =
* Fixed: Flash image upload
* Fixed: Preview size settings not saving when updated.
* Fixed: Add new menu "Save into" not displaying galleries for selection.

= 0.5.3 =
* Added: widget.
* Added: image rss.
* Added: gallery shortcode.
* Fixed: permalink confict.
* Fixed: js error with new slideshow options.
* Fixed: admind displaying wrong expiration date.
* Removed: columns setting,not needed controled by css.

= 0.5.2 =
* CSS compression.
* CSS modifications.
* Added: Slideshow options
* Added: colorbox gallery feature.
* Fixed: js errors on IE.
* Fixed: watermark text location.
* Fixed: expire gallery query/cron
* Fixed: CSS AlphaImageLoader image url for(color box)IE.
* Text change: Inside USA to Local.
* Relocated colorbox styles and images.

= 0.5.1 =
* HTML clean up
* CSS modifications.
* Add image cache(htaccess).
* Fixed: permalinks admin/frontend.
* Fixed: images displaying on the frontend with trash status.
* Remove: login link from unsecure galleries.
* Increase RAM memory for swfupload to process big images.

= 0.5.0 =
* Beta release

== CREDITS ==

= Galleriffic =
Trent Foley(http://www.twospy.com/galleriffic/)

= Colorbox =
Jack Moore,Alex Gregory(http://colorpowered.com/colorbox/)

= Uploadfy =
Ronnie Garcia,Benj Arriola,RonnieSan(http://www.uploadify.com/)


== Upgrade Notice ==
* Upgrade from 2.0.0 will change your permalinks. 
* Upgrade from 1.0.2 and previous price lists need to bee updated to use the image unit. 
* Upgrade from 0.5.2 and previous slideshow options will be added or reset setting to update options. 
* Upgrade from 0.5.0 to 0.5.0 may change your permalinks. 

== Screenshots ==

1. Screenshot Menu
2. Screenshot New Gallery
3. Screenshot Pricing
4. Screenshot Sales / Screen Options
5. Screenshot Settings
6. Screenshot Galley Options
7. Screenshot Slideshow
8. Screenshot Pricelist
9. Screenshot Shopping Cart