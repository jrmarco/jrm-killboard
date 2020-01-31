# JRM Killboard

![version](https://img.shields.io/badge/stable-1.1.1-blue) ![license][https://img.shields.io/badge/license-GPLv2-brightgreen]

=== JRM Killboard ===

Contributors: jrmarco
Donate link: https://bigm.it
Tags: eveonline, eve online, eve, killboard, game
Requires at least: 4.8.12
Tested up to: 5.3.2
Requires PHP: 5.6
Stable tag: 1.1.1
License: GPLv2 or later

Display corporation kills using Killmails: sync it manually or automatically. Customizable: display your killboard the way you like it

== Description ==

JRM Killboard is a plugin developed for Eve Online gamers and lovers. It creates your local Wordpress Killboard for your corporation to be shown on your frontend fast and easily. You can make fun of your competitors and enemy corporations in a click! It is simple and quick to set up, ready to be used. Can be updated manually or if you have access to the ESI API application, you can link your account and delegate everything to the plugin that will pull and process your corporation killmails for you. Offers a set of settings to adapt the "look & feel" of the killboard to your taste and your Wordpress template. Plugin comes with a specific page template to be activated, with the choices you made on the admin settings page and offers two endpoint to perform automatic updates of killmails and item prices. In case you don't have access to an ESI API application account you can always manually loads your corporation killmails from the admin panel with a simple copy&paste. For further details and instructions please read the guide.

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
* From the JRM Killboard -> Main page you will see all your kills and their infos
* From the JRM Killboard -> Settings page you can customize your killboard with required data and graphic settings
* Once the settings are done go to the Wordpress Pages menu -> Add new
* Create a new page, on the page attribute choose the template: JRM Killboard
* You are ready to fly!

== Screenshots ==

1. Admin Main page
2. Admin Settings page
3. Public Killboard page
4. General settings detail
5. Graphics settings detail
6. ESI Sync detail
7. Stats
8. Logs
9. Price calculation and manual price set

== Changelog ==

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

== GUIDE : JRM Killboard Main ==

JRM Killboard Admin Main page: from this page a list of the synchronized killmails is displayed, sorted by the time when the kill was performed. You can hide/show, delete each kill, you can also reach the killmail report if you need it. Each kill comes with: victim, date, list of the attackers, value of the kill.  From time to time, might happen that kill(s) you sync do not have a price/quotation. This may happen because plugin fails ( price might also be missing at that time ) to retrieve an official price for an item during the price sync. You can identify the killmails that requires an evaluation by the word "Pending" in the "Value" column. The interface will help you showing some tools to complete the kill estimation:
* Calculate costs : this button will trigger the process to calculate the missing estimation. Process is automated, once complete will update the kills that require an estimation
* Missing price: this selector will show ( if required ) a list of items that doesn't have an official price or cannot be evaluated at that time. Simply search the price from the market ( or wherever you prefer ) and insert it into the input field and then save. This price will be considered the updated one for that item, until a new price will come up from the next sync

== GUIDE : JRM Killboard Settings ==

JRM Killboard Admin Settings page: from this page you can setup and customize the Killboard (backend and frontend). There are several settings in this page, we are going to split them in two main category: General Settings and Graphic Settings, as their header says. The first one control the core process of the plugin, the second one drive the "look & feel" of the killboard public page. Let dive into each settings:
* General Settings:
	* Corporation Id : your corporation ID. PLEASE BE CAREFUL to insert the correct one or the killmail import ( both manual or auto ) will fail if that doesn't match with the correct one.
	* ONLY WITH ESI APPLICATION ENABLED AND SYNC : 
		* Client Id : ESI Application ID, is specific for your application. Retrieve it from the ESI Application admin panel. DO NOT SHARE THIS DATA
		* Client Secret : ESI Application Secret, is specific for your application. Retrieve it from the ESI Application admin panel. DO NOT SHARE THIS DATA
		* OAuth version : You can choose which version of OAuth standard use. I recommend Version2. If you don't know what this mean, just leave version 2
		* Synchronization : this field set up the period of run of the cronjob offered by Wordpress ( WP-Cron ). This process can be run every hour, twice a day or once per day. We advise against the use of it, especially for heavy load platform. If you have direct access to system cron or external service go for it ( there are a lot on the web, some free )
		* Endpoints Name: URL name parameter, required in the call to reach the cronjob endpoints
		* Endpoints Secred: URL secret parameter, required in the call to reach the cronjob endpoints
* Graphic Settings:
	* Killboard title : frontend page title of the Killboard
	* Margin : container HTML margin value of the frontend killboard. You can specify any allowed value. Sample: 20px OR 0 10px 0 10px 
	* Padding : container HTML Padding value of the frontend killboard. You can specify any allowed value. Sample: 20px OR 0 10px 0 10px
	* Elements per page : number of displayed element per page in the Admin AND frontend table
	* Show kills: choose type of kills shown on the frontend killboard. You can choose between: All, Done ( kills done by corporates ), Suffered ( kills suffered by corporates )
	* Font size : font size on the elements ( all ) in the HTML container frontend killboard page
	* Image size : image size on the table rows for Capsuler, Alliance and Corporation images
	* Kills done -> background color : table rows background color of the kills your corporation perform. Field accept HTML color value ( textual or hexadecimal ). Sample : red OR #008000 OR cyan. You can have a preview moving out your focus from the field
	* Kills done -> text color : same as previous settings, but related to text color
	* Kills suffered -> background color : table rows background color of the kills your corporation suffered. Field accept HTML color value ( textual or hexadecimal ). Sample : red OR #008000 OR cyan. You can have a preview moving out your focus from the field
	* Kills suffered -> text color : same as previous settings, but related to text color
	* Header,table header and footer settings -> background color : background color of main frontend page header,table header and footer. Field accept HTML color value ( textual or hexadecimal ). Sample : red OR #008000 OR cyan. You can have a preview moving out your focus from the field
	* Header,table header and footer settings -> text color : same as previous settings, but related to text color
	* Table columns : allows to choose which columns you want to display on the frontend killboard page. At least one column must be active
	* Display Developer Sign on Frontend : enable/disable the developer sign on the frontend killboard page ( Made with ♥ by jrmarco ), if you want to support me

On the very right side of the page you can see two sections
* Statistics : here will be listed the general stats of your Killboard. Numeric infos about number of capsulers, killmails, corporations and items stored by your platform
* Processing Logs: here will be shown process log ( notice, info or error ) during auto sycn of killmails and prices

== GUIDE : Killmails ==

First of all, if you don't know what Killmails are, [read this](https://wiki.eveuniversity.org/Killmail). Killmails can be synced manually or automatically via an ESI Application. We will now dive into the "How-To" on both scenario:

* Manually load killmail : killmail can be manually imported ( this can be done even if you enabled the ESI Application auto sync ). First of all a Corporation ID must be stored in the Admin Settings page. If you skip this step manual sync won't be possible. From the JRM Killboard :: Main page, use the top field called Killmail URL : paste the killmail link ( links have this format: https://esi.evetech.net/ (LATEST OR V1) /killmails/ KILL-ID / KILL-HASH / ) and click on the Load Kill button. Your kill will be loaded ( if not present ) and all the information fetched. If the kill items are missing or price is missing at that time, process will ask you to provide a price for it

* Auto Sync Killmail - ESI Application : killmails and price sync can be done automatically using the combination of an ESI Application and a Cronjob. The application and the cronjob are not direct part of the plugin, you need to provide them TO the plugin.
	* ESI Application: an ESI application must be requested and created from the [Eve Developer](https://developers.eveonline.com/) website. To obtain one:
		* LogIn with your Eve Online account, go to Manage Application, Create New Application. Here you have to provide some info about your application : 
			1. Name and Description : this are not relevant for the plugin. Put what you like
			2. Connection Type : AUTHENTICATION & API ACCESS . This settings allows the plugin to call the ESI endpoint whenever needs to fetch the killmails for you. 
			3. Permissions : plugin requires only one -> esi-killmails.read_corporation_killmails.v1 . As description says "Allows reading of a corporation's kills and losses". With this permission plugin will be able to perform ONLY THIS ACTION. At the moment plugin doesn't need any other permission rather then esi-killmails.read_corporation_killmails.v1 . 
			4. Callback url : this is the url used by the Developer LogIn API to forward you once the authentication is completed. Pay close attention to this field, callback url MUST BE the JRM Killboard plugin settings page. Sample: http OR https://YOUR-DOMAIN/wp-admin/admin.php?page=jrmevekillboard_settings . You can copy and paste it directly from your Wordpress installation admin menu link: right click and copy the link from the JRM Killboard -> Settings menu link.
			5. Create Application : your ESI Application is now ready to be used
	* Once you have an ESI Application, go to the JRM Killboard Admin Settings page and copy and paste the Client Id and Client Secret into the input fields and Save the configuration
	* Interface will now display the EVE SSO Authenticate button. This will forward you to the Eve Online Account LogIn, after login you will be asked to choose which capsuler you want to sync with this application and will show you the scope ( ESI Application permission ) required by the application you created before. Confirm and you will be redirected back to the Wordpress JRM Killboard Settings page, with the ESI Application details updated. Congratulations you are now ready to sync your killboard!
	* At any moment you can suspent the ESI Application authentication/link using the Remove button, placed next to the ESI Sync status. PLEASE NOTICE: this action won't revoke the ESI Application token, you will have to do it directly from your Eve Online account settings
	* You can now set up a Cronjob ( System Cronjob, External Cronjob ) that will trigger all the sync process. Point it to the endpoints displayed right after the ESI Application sync status

== Images ==

All the images shown on the Admin pages and in the frontend page of the Killboard are loaded directly from the ESI Image CDN. Images have public access, no authentication is required. I choose to call images from the image service directly because: it is offered via a global CDN, Eve has tons of images ( more than 20000 ) and download them all might be an issue for someone. Documentation can be reached [here](https://images.evetech.net)

== CCP Copyright Notice ==

EVE Online and the EVE logo are registered trademarks of CCP hf. EVE Online and all associated logos and designs are the intellectual property of CCP hf. All the images, game data coming from the ESI API or other recognizable features of the intellectual property relating to these trademarks are likewise the intellectual property of CCP hf. JRMKillboard uses EVE Online and all associated logos and designs for information purposes only on this website but does not endorse, and is not in any way affiliated with it. CCP is in no way responsible for the content nor functioning of this Wordpress plugin, nor can it be liable for any damage arising from the use of this Wordpress plugin. All Eve Related Materials are Property Of [CCP Games](http://www.ccpgames.com/). This Wordpress plugin ( JRMKillboard ) makes use of ESI Api and Eve Online Developer applications. All information can be found on official [Eve Developers Website](https://developers.eveonline.com/) - [License Agreement](https://developers.eveonline.com/resource/license-agreement). - © 2014 CCP hf. All rights reserved. "EVE", "EVE Online", "CCP", and all related logos and images are trademarks or registered trademarks of CCP hf.

== Disclaimer == 

JRMKillboard and his creator ( jrmarco ) is not responsible for any damage arising the use of this Wordpress plugin nor any limitation/block/ban/interruption of service of your Eve Developer Application caused by the use of it 
