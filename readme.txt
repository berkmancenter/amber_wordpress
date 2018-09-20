=== Amber ===
Contributors: berkmancenter
Donate link: https://cyber.law.harvard.edu
Tags: links, archiving
Requires at least: 4.0.0
Tested up to: 4.9.8
Stable tag: 1.4.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Amber keeps links working on blogs and websites.

== Description ==

[youtube http://www.youtube.com/watch?v=TfslrakKHyo]

Whether links fail because of DDoS attacks, censorship, or just plain old link rot, reliably accessing linked content is a problem for Internet users everywhere. The more routes we provide to information, the more all people can freely share that information, even in the face of filtering or blockages. Amber adds to these routes.

Amber automatically preserves a snapshot of every page linked to on a website, giving visitors a fallback option if links become inaccessible. If one of the pages linked to on this website were to ever go down, Amber can provide visitors with access to an alternate version. This safeguards the promise of the URL: that information placed online can remain there, even amidst network or endpoint disruptions.

Lots of copies keeps stuff safe. By default, Amber stores snapshots directly on the host website. But users can choose to store snapshots using a combination of the following third party storage and archiving systems: the Internet Archive, Perma.cc, and Amazon Simple Storage Service (Amazon S3).

Amber is an open source project led by the [Berkman Klein Center for Internet & Society](https://cyber.law.harvard.edu). It builds on a proposal from Tim Berners-Lee and Jonathan Zittrain for a "mutual aid treaty for the Internet" that would enable operators of websites to enter easily into mutually beneficial agreements and bolster the robustness of the entire web. The project also aims to mitigate risks associated with increasing centralization of online content.

Learn more at [Amberlink.org](http://amberlink.org).


== Installation ==

[youtube http://www.youtube.com/watch?v=25Kz7PqapG4]

Full installation instructions, as well as a guide for configurations and settings, can be found on the [Amber wiki guide](https://github.com/berkmancenter/amber_wordpress/wiki).

1. Install the plugin through the WordPress plugins screen directly, or upload the plugin files to the `/wp-content/plugins/plugin-name` directory.
1. Activate the plugin through the 'Plugins' screen in WordPress.
1. Use the Settings -> Amber screen to configure Amber.
1. Start preserving links!


= Other Requirements =

PHP cURL extension enabled

== Frequently Asked Questions ==

Here are two of the most frequently asked questions. A full list of FAQs, as well as all Amber documentation, can be found on the [Amber wiki guide](https://github.com/berkmancenter/amber_wordpress/wiki).

= What are some legal issues I should be aware of when using Amber? =

Those who copy third-party content should always be aware of copyright issues. This is no different when it comes to using Amber. When you use Amber, you are preserving the copyrighted content of other publishers. Many uses of Amber -- linking to and preserving a webpage for purposes of criticism, comment, news reporting, teaching, scholarship, or research -- likely constitute fair use under United States law and thus do not infringe on copyrights. However, users of Amber should be aware that fair use rights vary significantly in different countries around the world. In addition, there are limits to fair use.

For a helpful guide on fair use, please visit the [Digital Media Law Project](http://www.dmlp.org/legal-guide/fair-use).

For additional information on intellectual property issues that concern bloggers, please visit the [Electronic Frontier Foundation](https://www.eff.org/issues/bloggers/legal/liability/IP).


= Can websites opt-out of having their webpages preserved through Amber? =

Amber respects the Robots Exclusion Standard and does not preserve any web page that opts out of web crawling via this protocol. It also uses the user agent "Amber" when preserving web pages, which you can choose to disallow on your website. You can read a full overview at [Amberlink.org](http://amberlink.org/fetcher/).

== Screenshots ==

1. If a link on your website were to go down, Amber provides your visitors with a snapshot of that link.
2. Mobile compatibility provides all visitors with the ability to view these snapshots.
3. You can configure the snapshot storage and delivery preferences to fit the needs of your website and visitors.
4. Ensure the content to which you link never vanishes down the memory hole.

== Changelog ==

= Version 1.4.5 =
*Release date: XXXX*

New in Amber v1.4.5 for WordPress:

* **WordPress 4.6 compatability** Amber has been tested through WordPress 4.6.1


= Version 1.4.4 =
*Release date: April 13, 2016*

New in Amber v1.4.4 for WordPress:

* **WordPress 4.5 compatability** Amber has been tested through WordPress 4.5.

* **Bug fixes.** This release fixes a problem with display of snapshot images and CSS.

* **Performance.** CSS and JavaScript is no longer included on pages where it is not required.

= Version 1.4.3 =
*Release date: March 28, 2016*

New in Amber v1.4.3 for WordPress:

* **PHP 5.2 compatability.** Amber is now compatible with PHP 5.2.

* **Security fixes.** Better protection against XSS attacks from malicious snapshots.

= Version 1.4.2 =
*Release date: March 15, 2016*

Thanks entirely to your downloads and feedback, this release adds new features and fixes reported bugs. New in Amber v1.4.2 for WordPress:

* **Increased default snapshot size.** The default limit for snapshot sizes has been increased to 5MB from 1MB. You can still configure the snapshot size limit as you see fit.

* **New fields added to the Dashboard.** If a link cannot be preserved, the Amber Dashboard now displays the reason why it cannot be preserved in the Notes column.

* **Improved URL identification.** Amber now properly preserves all links that contain additional HTML attributes.

* **Updated location-specific behavior.** Amber allows users to specify if location-specific features are enabled via the dropdown menu in Amber Settings. If no country is specified for Amber Delivery, Amber no longer calls a third-party service.

* **Updated dependency checking.** If the cURL library is not installed, Amber now displays a notice and warning upon plugin activation (credit: [webster](https://github.com/webster))

= Version 1.4.1 =
Fixed an issue with displaying saved pages to viewers. All users should upgrade Amber to v1.4.1.

= Version 1.4 =
*Release date: December 7, 2015*

We're in the WordPress plugin directory! New and improved in Amber v1.4 for WordPress:

* **Storage of snapshots in other locations.** You can now choose to store snapshots on one or more third party storage and archiving systems: the [Internet Archive](http://archive.org), [Perma.cc](http://perma.cc), and [Amazon Web Services (S3)]( http://https://aws.amazon.com/s3/). You can enable this feature to free up space, take advantage of donated host space, or simply to contribute to existing web archival efforts. Remember: lots of copies keeps stuff safe!

* **Display of snapshots from other archives.** Amber can now also display to your visitors alternative versions of a URL from other archives, including the Internet Archive, the Library of Congress Web Archive, Archive-It, archive.is, Webcite, and more. If enabled, you can display to your visitors the Amber snapshot in addition to other available versions of the same page--providing even more routes to linked content. This is made possible through expanded support of the [Memento protocol](http://mementoweb.org/about/) and the Memento project's  ["time travel" and "TimeGate" concepts](http://timetravel.mementoweb.org/about/).

* **Compatibility with academic efforts to retrieve more accurate data.** This release defined parameters for Amber users to benefit from academic research efforts on Internet activity and content controls. The Berkman Klein Center's [Internet Monitor](https://thenetmonitor.org/) project compiles and curates quantitative data to give policy makers, digital activists, and researchers. Primary data collected by the Berkman Klein Center and our partners, as well as relevant secondary data, will one day help answer the difficult question of "Which URLs are down for whom, where, when, and why?" While such a complete system is not in operation right now, eventually users of Amber can enable this feature to benefit from (by retrieving and contributing) such data.

Curious about previous releases before we were part of the WordPress plugin directory? Check out our [Github repository](https://github.com/berkmancenter/amber_wordpress).
