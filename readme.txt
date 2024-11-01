=== Very Good Collection Add-On ===
Contributors: wafsite
Tags: gravity, gravityforms, addon, payment, verygoodcollection
Requires at least: 4.5
Tested up to: 5.7.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add Very Good Collection payment method for your Gravity Forms

== Description ==

Accept Credit card, Debit card or Very Good Collection account payment.

Send and receive money from anyone with Our Borderless Payment Collection Platform. Payout straight to your bank account.

Signup for an account [here](https://verygoodcollection.com/login)

= After installation, please follow the instructions bellow: =

= Setting Up the Very Good Collection Add-On =
1. Go to your WordPress admin Dashboard.
2. On the left side navigation menu, hover over "Forms" and click on "Settings".
3. From this page, click the "VGC" tab. You will be presented with the Very Good Collection Settings Screen.

= Very Good Collection Account Information =
1. "Prefix":  Enter a prefix for your invoice numbers. If you use your Very Good Collection account for multiple stores ensure this prefix is unique as Very Good Collection will not allow orders with the same invoice number.
2. "Merchant Key": Enter the Merchant Key from your Very Good Collection Account. This field is required.
3. "Public Key": Enter the Public Key from your Very Good Collection Account. This field is required.
4. "Secret Key": Enter the Secret Key from your Very Good Collection Account. This field is required.

= Creating a Feed for the Very Good Collection Add-On =
Before the Very Good Collection Add-On can be used with Gravity Forms, you will first need to create a feed. A feed allows form submission data to be sent to another source. In this instance, payment data being sent to Very Good Collection.
<strong>Required Fields</strong>
To create a feed for Very Good Collection, you must have the following fields on your form:
1. Product Field and/or Total Field
2. Name Field and Email Field

= Create a Feed =
To create a feed to Very Good Collection using the Very Good Collection Add-On for Gravity Forms, do the following from your WordPress Admin Dashboard:
1. Click on "Forms" in the left side menu.
2. Select the form that you want to use with Very Good Collection.
3. Once within your desired form, hover over "Settings" and click on "VGC".
4. Click "Add New" to create a new feed. You will be presented with the Very Good Collection Feed Settings screen.

= Feed Settings Field =
1. A feed "Name" is requried. It is only used for identification and will not be shown anywhere other than your feed listing.
2. Choose the "Transaction Type". Select "Product and Service" for payment.
3. Configure the "Billing Information". Map each of the various options to the disered form field that will contain that information.
4. "Conditional Logic": If unchecked, every successful form submission will be sent to Very Good Collection. If you wish to set specific conditions for sending form data to Very Good Collection, then check the "Enable Condition" box and fill out your required criteria.
6. Click the "Update Settings" button to save your options.

== Installation ==

= Installation using the Add-On Browser =
1. Log into your WordPress admin dashboard.
2. Hover over "Forms" and click on "Add-Ons".
3. Here you will see the Very Good Collection Add-On. To install, simply click the "Install" button. Once the "Install" button is clicked, Wordpress handle the download and installation of the Very Good Collection Add-On. Be sure to click "Activate Plugin" to active the Add-On.

= Manual Installation =
1. Download the plugin zip file.
2. Login to your WordPress Admin. Click on "Plugins > Add New" from the left menu.
3. Click on the "Upload" option, then click "Choose File" to select the zip file you downloaded. Click "OK" and "Install Now" to complete the installation.
4. Activate the plugin.
For FTP manual installation, [check here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).


== Changelog ==

= 1.0 =
* Initial version