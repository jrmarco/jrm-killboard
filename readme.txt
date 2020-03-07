=== JRM Killboard ===

Contributors: jrmarco
Donate link: https://bigm.it
Tags: eveonline, eve online, eve, killboard, game
Requires at least: 4.8.12
Tested up to: 5.3.2
Requires PHP: 5.6
Stable tag: 1.3.1
License: GPLv2 or later

Display corporation kills using Killmails: sync it manually or automatically. Customizable: display your killboard the way you like it

== Description ==

JRM Killboard is a plugin developed for Eve Online gamers and lovers. It creates your local Wordpress Killboard for your corporation to be shown on your frontend fast and easily. You can make fun of your competitors and enemy corporations in a click! It is simple and quick to set up, ready to be used. Can be updated manually or if you have access to the ESI API application, you can link your account and delegate everything to the plugin that will pull and process your corporation killmails for you. Offers a set of settings to adapt the "look & feel" of the killboard to your taste and your Wordpress template. Plugin comes with a specific page template to be activated, with the choices you made on the admin settings page and offers two endpoint to perform automatic updates of killmails and item prices. In case you don't have access to an ESI API application account you can always manually loads your corporation killmails from the admin panel with a simple copy&paste. For further details [click here](https://github.com/jrmarco/jrm-killboard/wiki/JRM-Killboard).

Main features:

* create a personal Killboard on your website
* select the "look & feel" of your killboard
* plugin ready to sync with ESI API application
* allows to manually load your killmails
* one endpoint to automatically sync your corporation killmails
* one endpoint to automatically sync the items price
* show proudly your kills and make fun of the enemies!

PLEASE NOTICE: To be able to use the ESI API application synchronization, you need an account on the official Eve Developer website. You can create one [here](https://developers.eveonline.com/)

== Installation ==

* Download the plugin from the Wordpress official plugin store via your Wordpress installation or using the ZIP from the same page
* Enable the JRM Killboard plugin from the plugins panel
* JRM Killboard menu will be shown on your Wordpress admin menu ( only for user with level Super Users, Administrators and Editors )
* JRM Killboard -> Killboard menu item : shows kills and infos
* JRM Killboard -> Configurations menu item : set ESI infos
* JRM Killboard -> Graphics menu item : customize look & feel of the public page
* JRM Killboard -> Items menu item : items list, prices
* Create a new page, set template page attribute: JRM Killboard
* You are ready to fly!

Full guide [here](https://github.com/jrmarco/jrm-killboard/wiki/Guide)

== Screenshots ==

1. Admin: Killboard
2. Admin: Settings page
3. Admin: Graphics page
4. Admin: Graphics page, colors
5. Public: Killboard page
6. Public: Inspect items list
7. Stats
8. Logs
9. Manual price set

== Changelog ==

= 1.3.1 =
* Fixed issue with uploads folder permission

= 1.3 =
* Fixed item inspection page positioning
* Add admin items page
* New admin pages UX
* Bulk actions

= 1.2 =
* Separated  main configurations from graphics settings
* Improved admin pages UX
* Add fields for custom classes
* Add items inspection

= 1.1.1 =
* Fixed wrong API endpoint
* Remove limit on price processing
* Include image fixed size

= 1.1 =
* Add custom color to Header, table header and footer
* Add css and styles to frontend images table
* Add option to sync ESI API with OAuth v1 and v2

= 1.0 =
* Release JRM Killboard 1.0

== Upgrade Notice ==

= 1.1.1 =
* Fixed wrong API endpoint

= 1.1 =
* Fixed Killmail validation link
* Fixed issue with css custom themes
* Fixed log file permission
* Recude header & body POST data
* Add OAuth v1
* Fixed request validation when performing SSO

== CCP Copyright Notice ==

EVE Online and the EVE logo are registered trademarks of CCP hf. EVE Online and all associated logos and designs are the intellectual property of CCP hf. All the images, game data coming from the ESI API or other recognizable features of the intellectual property relating to these trademarks are likewise the intellectual property of CCP hf. JRMKillboard uses EVE Online and all associated logos and designs for information purposes only on this website but does not endorse, and is not in any way affiliated with it. CCP is in no way responsible for the content nor functioning of this Wordpress plugin, nor can it be liable for any damage arising from the use of this Wordpress plugin. All Eve Related Materials are Property Of [CCP Games](http://www.ccpgames.com/). This Wordpress plugin ( JRMKillboard ) makes use of ESI Api and Eve Online Developer applications. All information can be found on official [Eve Developers Website](https://developers.eveonline.com/) - [License Agreement](https://developers.eveonline.com/resource/license-agreement). - © 2014 CCP hf. All rights reserved. "EVE", "EVE Online", "CCP", and all related logos and images are trademarks or registered trademarks of CCP hf.

== Disclaimer == 

JRMKillboard and his creator ( jrmarco ) is not responsible for any damage arising the use of this Wordpress plugin nor any limitation/block/ban/interruption of service of your Eve Developer Application caused by the use of it 
