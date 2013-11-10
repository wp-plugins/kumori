=== Plugin Name ===
Contributors: 
Donate link: 
Tags: plugin, cloud, transcode, amazon, aws, video, S3, elastic transcoder
Requires at least: 3.5.1
Tested up to: 3.7.1
Stable tag: 0.23
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

It's a plugin that lets the users upload video files and transcode them on-the-cloud!

== Description ==

It's a plugin that lets the users upload video files and transcode them on-the-fly using AWS Elastic Transcoder and S3 Services.
Using Kumori is pretty simple, just select the presets you want, upload the file, and then your video file becomes kumori-fied!
It also provides a simple management of AWS S3 and Elastic Transcoder services within the Wordpress Admin Menu.
It requires a subscription at AWS.

Next major version features:
* Allow the transcoding of existing video files in Media library
* Allow the transcoding of existing video files in S3
* Provide the new features of Elastic Transcoder
* Friendlier user messages
* More checks for file operations

== Installation ==

1. Upload the zip file via the Add new Plugin Page
2. Activate it via the Installed Plugins Page
3. Set the AWS credentials through Wordpress Admin->Settings->Media page

== Frequently Asked Questions ==

== Screenshots ==


== Changelog ==

= 0.23 =
* Fix (possibly and hopefully) the fatal errors from not setting the AWS credentials BEFORE using the plugin actions

= 0.22 =
* Fix (hopefully) the require_once path nightmares
* Change the max wait attempts from 5 to 15
* Point to the Settings->Media page for settings the AWS credentials

= 0.21 =
* Fix action in forms
* Fix Typo in S3 Actions

= 0.2 =
* Make use of WordPress CSS

== Upgrade Notice ==
