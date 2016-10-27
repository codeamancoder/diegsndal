=== WooCommerce Currency Switcher ===
Contributors: RealMag777
Donate link: http://codecanyon.net/item/woocommerce-currency-switcher/8085217?ref=realmag777
Tags: woocommerce currency switcher, currencies, currencies switcher, currency switcher, woocommerce switcher, currency converter, currency, shortcode, switcher, woocommerce
Requires at least: 3.8.0
Tested up to: 4.5.3
Stable tag: 1.1.7

WooCommerce Currency Switcher – is the plugin that allows you to switch to different currencies and get their rates converted in the real time!

== Description ==

WooCommerce Currency Switcher – is a plugin that allows you to switch to different currencies and get their rates converted in the real time – a must have plugin for your WooCommerce powered online store!
Currency Switcher is available as with shortcode [woocs] and with the widget.
Plus it is possible to show on the front: currency converter and currencies rates widget/shortcode as here (in sidebar) http://demo.currency-switcher.com/

Read more here: http://currency-switcher.com/



### The Plugin Features:

* Shortcode - just add in text widget [woocs width='100px'] OR [woocs width='50%']
* 3 Widgets: currencies switcher, currency converter, currencies rates
* 5 kinds of selectors with flags + flag mode
* 2 more additional AJAXED shortcodes: [woocs_rates exclude="" precision=2] & [woocs_converter exclude="" precision=2]
* Currency can be changed automatically according to visitor's IP using woo WC_Geolocation PHP class
* 4 currencies aggregators
* The customer allowed to pay in the selected currency
* WooCommerce native PayPal gate ready!
* Ability to set new order currency which were created through admin panel by hands
* Ability recount order from any currency to basic currency in multiple mode of the plugin
* Easy to use for administrators and shop customers
* Compatible with WooCommerce 2.4.x and higher
* Compatible with WooCommerce Extra Product Options => http://codecanyon.net/item/woocommerce-extra-product-options/7908619?ref=realmag777
* Compatible with – WooCommerce Stripe Gateway by woothemes
* Compatible with – PayPal for WooCommerce by angelleye
* Compatible with – WooCommerce PDF Invoices & Packing Slips
* Compatible with – WooCommerce Subscriptions by woothemes
* 90% compatibility with different payment gates in 'multiple allowed' mode, just try it!


Look more here please: http://codecanyon.net/item/woocommerce-currency-switcher/8085217?ref=realmag777

https://www.youtube.com/watch?v=wUoM9EHjnYs

DO YOU NEED SEARCHING PRODUCTS FILTER FOR WOOCOMMERCE? LOOK IT HERE PLEASE: https://wordpress.org/plugins/woocommerce-products-filter/

== Installation ==
* Download to your plugin directory or simply install via Wordpress admin interface.
* Activate.
* Use.


== Frequently Asked Questions ==

Q: Where can I see demo?
R: http://demo.currency-switcher.com/

Q: Documentation?
R: http://currency-switcher.com/codex/

Q: More FAQ?
R: http://currency-switcher.com/category/faq/


== Screenshots ==
1. Currencies back panel
2. Options back panel
3. GeoLocation rules in the back panel

== Changelog ==

= 1.1.7 =
* Heap of small bugs fixed
* https://wordpress.org/support/topic/multi-currency-on-invoices?replies=8 - resolved
* new option in the currencies settings: Decimals
* new hook: woocs_drop_down_view
* advanced API doc: http://currency-switcher.com/codex/	
	
= 1.1.6 =
* Heap of small bugs fixed
* New hook woocs_price_html_tail
* Approx. value on cart and chekout page in the currency of customer (in multiple mode only+geoip enabled)
* New hook woocs_get_approximate_amount_text
* Previous version of the plugin is here: http://currency-switcher.com/wp-content/uploads/2016/05/woocommerce-currency-switcher-1154.zip

= 1.1.5.4 =
* Improvements for security functionality
* GeoIp hot fix

= 1.1.5.3 =
* Closed XSS vulnerability. Thanks to Ben Khlifa Fahmi ; Founder & CEO of BenkhlifaExploit Founder & Pentester at Tunisian Whitehats Security

= 1.1.5.1 =
* 1 fix for woocommerce 2.5.1 variable products price

= 1.1.5 =
* New option: I am using cache plugin for my site - alloes using the plugin with cached sites
* New button in order to convert oder data to basic currency amounts
* New shortcode: [woocs_show_current_currency text="" currency="" flag=1 code=1]
* New shortcode: [woocs_show_custom_price value=20] -> price in selected currency for txt-adv-banners
* New option: Prices without cents
* New option: Hide switcher on checkout page
* Hint: wp-admin/admin.php?page=wc-settings&tab=woocs&woocs_reset=1 - reset currency options - be care
* Improved: cron periods added - weekly, monthly
* New filter: add_filter('woocs_price_format', 'my_woocs_price_format', 999, 2); - Any manipulation with price format, look it in the docs http://currency-switcher.com/documentation/#!/section_8
* previous v.1.1.4 is here: http://currency-switcher.com/wp-content/uploads/2016/01/woocommerce-currency-switcher-114.zip

= 1.1.4 =
* WordPress 4.3 small adaptation - using __construct in the widget, prev widget-API was deprecated
* GEO IP functionality is free from now
* price popup on the front near each price optionally
* in body implemented currency css class. Example: currency-eur
* added currency agregator for Russian Centrobank - asked by customers from Russia

= 1.1.3 =
* compatibility for woocommerce 2.4
* added storage optionally, transient for sites which can work with session normally because of server options
* added new drop-down wselect -> https://github.com/websanova/wSelect#wselectjs
* in shortcode [woocs] and currency switcher widget added new option txt_type which allows show currency description in drop-down instead of its code
* previous v.1.1.2 is here: http://currency-switcher.com/wp-content/uploads/2015/08/woocommerce-currency-switcher-112.zip

= 1.1.2 =
* some small bugs fixed
* dark chosen implemented

= 1.1.1 =
* some small bugs fixed
* done a lot to make compatibility higher

= 1.1.0 =
* 1 bad logic bug fixed, which broke recount prices in multiple mode
* AJAX refresh of mini cart fixed, now its ok

= 1.0.9 =
* Compatibility for 90% payment gates without any customizations
* 2 new widgets+shortcodes: currency converter, currency rates
* Attention for codecanyon customers - do not update to this version - it is the free one and have less functionality!!! Download your copy of the plugin you bought from codecanyon site only!
* If you are allows to your customers pay in their selected currency be attentive  to update to 1.0.9 and higher, you will get
little inconvenience in wp-admin/edit.php?post_type=shop_order with displayed orders amount + order amounts inside!
You can close orders using old version of the plugin and then update to the v.1.0.9 or greater. If you are happy with 1.0.4 version of the plugin - continue use it.
If you uses basic currency for payments - update the plugin with no doubt.
WOOCS 1.0.4 is here - http://currency-switcher.com/wp-content/uploads/2015/07/woocommerce-currency-switcher-104.zip

= 1.0.4 =
* Validation error: PayPal amounts do not match fixed
* WooCommerce native range slider - js price format improvement

= 1.0.3 =
* Currency can be changed automatically according to visitor’s IP using woo WC_Geolocation class
* Possibility set currency on front by flags images mode

= 1.0.2 =
Some features and bug fixes

= 1.0.1 =
Some features and bug fixes

= 1.0.0 =
Plugin release. Operate all the basic functions.



== License ==

This plugin is copyright pluginus.net &copy; 2012-2016 with [GNU General Public License][] by realmag777.

This program is free software; you can redistribute it and/or modify it under
the terms of the [GNU General Public License][] as published by the Free
Software Foundation; either version 2 of the License, or (at your option) any
later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY. See the GNU General Public License for more details.

  [GNU General Public License]: http://www.gnu.org/copyleft/gpl.html


== Upgrade Notice ==
Look here for ADVANCED version of the plugin: http://codecanyon.net/item/woocommerce-currency-switcher/8085217?ref=realmag777

