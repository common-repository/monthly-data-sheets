<?php

/**
 * Plugin Name: Monthly Data Sheets
 * Description: This plugin provides an admin interface to create monthly data sheets. The front end interface provides option to edit and view data sheets in each month.
 * Version: 1.0.1
 * Author: Reenu Shomy K
 * License: GPL2
 */
defined('ABSPATH') or die('Plugin file cannot be accessed directly.');

global $mds_db_version;
$mds_db_version = '1.0.0';

/* Default settigs data */
define('_mds_date_format', 'M jS, Y');
define('_mds_time_format', 'Ga');
define('_mds_year_start', '2000');
define('_mds_year_end', '2050');
define('_mds_time_start', '9');
define('_mds_time_end', '17');

// Include the library files
include 'lib/main.php';
include 'lib/meta.php';
include 'lib/settings.php';
include 'lib/data.php';
include 'lib/functions.php';

new MonthlyDataSheet\Main;

/* Required only in admin panel */
if (is_admin()) {
    new MonthlyDataSheet\Settings;
    new MonthlyDataSheet\Meta;

    /**
     * Bind the action to add admin scripts and styles
     */
    add_action('admin_enqueue_scripts', array('MonthlyDataSheet\Main', 'admin_scripts'));

    add_action('wp_ajax_title_block', array('MonthlyDataSheet\Meta', 'title_block'));
} else {

    /**
     * Bind the action to front end scripts and styles
     */
    add_action('enqueue_scripts', array('MonthlyDataSheet\Main', 'scripts'));
}
/**
 * Add filter to append data sheet to the data sheet post content
 */
add_filter('the_content', array('MonthlyDataSheet\Main', 'display'), 3);

if (!function_exists('__p')) {

    /**
     * Function to print formatted data recursively
     * 
     * @param Mixed $data data to be printed
     */
    function __p($data) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

}