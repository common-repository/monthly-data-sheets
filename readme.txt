=== Monthly Data Sheets ===
Contributors: Reenu
Tags: sheet, monthly, diary, appointments, book, logbook
Requires at least: 3.9.3
Tested up to: 4.5.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Monthly Data Sheets plugin allows you to create data sheets for each month with custom rows and columns

== Description ==

Monthly data sheets plugin allows you to create multiple data sheets. Each sheet could save and display data in separate sheets, one per month. Only manager users can edit the data. This could be used as a ledger book, diary, daily data logger, appointment book etc.

Major features in Monthly Data Sheet include:

* Ability to create multiple sheets with custom row names and column names
* Use single sheet post to save data in any month
* Provides edit option to selected manager users only for each sheet
* Edit monthly data via front end interface

== Installation ==

1. Upload the plugin files to the '/wp-content/plugins/monthly-data-sheets' directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Data Sheets screen to edit the plugin settings

== Frequently Asked Questions ==

= How could I edit the sheet contents? =

Login as a user who is assigned as sheet manager. Click on the 'View Data Sheet' link. In the front end page, you can see the year, month dropdowns and the edit sheet button. Select the preferred month and then click on the 'Edit Data Sheet' Link.

= How could I add custom rows to a sheet? =

You could do this as admin user. In the edit sheet page in admin panel, and click on the 'Add row' button to add new rows.

= How could I create a diary? =

Just create a new sheet via admin panel menu, Data Sheets => Add New. The default rows of a sheet will be days in month. So should only add a column 'Notes' and you will get a diary page with one line for each day. 

= How could I create an appointment book? =

Just create a new sheet via admin panel menu, Data Sheets => Add New. The default rows will be days and default columns will be hours in a day.

= The default columns seems to be 9am to 5pm. How could I change this? =

You can change this via the admin panel menu, Settings => Monthly Data Sheet. The fields are 'Start Time' and 'End Time'. You could change the date and time formats also.

= Why is my new custom row not appearing in last month's sheet? =

New custom rows or columns will not be added if a particular month's data sheet is already filled. You should delete the particular month's sheet to get the latest rows and columns.

= I am getting 404 when I view my first sheet. How could I load the sheet page? =

Just take Settings->Permalinks and click save. Your sheet pages will load successfully.

== Screenshots ==

1. General Settings
2. Row and column customizations
3. Front end view

== Changelog ==

= 1.0.0 =
* Initial Release

== 1.0.1
* Edit page display bug fix