=== Easy GDPR Consent Forms - MailChimp ===
Contributors: asadkn
Tags: mailchimp, gdpr, dsgvo, popup, newsletter, consent, forms, mc4wp
Requires at least: 4.6
Tested up to: 4.9.6
Requires PHP: 5.4
Stable tag: trunk
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Comply with GDPR Consent requirement for your MailChimp forms with an innovative popup and no site design changes.

== Description ==
Easy GDPR Consent Forms lets you make your MailChimp forms GDPR compliant with *no design changes* to your existing site. Comply with GDPR Consent requirement for your MailChimp forms with a unique popup method.

With GDPR going into effect, sites using newsletter are required to collect consent from EU visitors on each way the email will be used. 

However, adding multiple checkboxes clutters the widget designs and you may not have the means or time to redesign it. And MailChimp Official doesn't even support GDPR fields for Embedded forms. Keeping that in mind, here's a unique solution.

#### Setup Video (Example @ 1:13)
https://www.youtube.com/watch?v=IO-yP5Rnxsc

Note: This is similar to how ConvertKit handles it but they use a redirect before a consent form is shown. This plugin does it with better user experience using an in-site popup to decrease friction and bounces.

**Features**

* Show a consent form popup on submit for "MailChimp for WordPress" plugin or the official Embedded MailChimp forms.
* Limit the consent popup to **EU visitors** only - with MaxMind GeoLite2 database. This way, you don't have to bother all other users.
* Consent logs are saved, accessible in dashboard, and can be deleted by admin.
* Multiple forms of different types supported.
* Detailed instructions when adding a form.
* No design changes necessary.

**Disclaimer**

*It's not magic*: using this plugin does not make your MailChimp forms automatically compliant with GDPR laws. You have to write up messages and create consent choices based on your own legal requirements.

*This plugin can provide GeoLite2 data created by MaxMind, available from http://www.maxmind.com. (when GeoLocation feature enabled)*

== Installation ==

1. Upload/Install and activate the plugin.
2. Go to *GDPR Consent Forms* > Add New to add your first form.
3. Follow the instructions.
4. Clear any cache from a cache plugin.

== Changelog ==

= 1.0.1 = 
* Fixed Autoptimize and minification plugins compatibility.
* Added more hooks for devs.
* Removed unnecessary metaboxes.

= 1.0.0 =
* Initial release.