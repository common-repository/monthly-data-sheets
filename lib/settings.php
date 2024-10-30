<?php

/**
 * Created on 15 Oct, 2015
 *
 * @category Library
 * @package MonthlyDataSheet
 * @author RSK
 * @license GPL2
 */

namespace MonthlyDataSheet;

defined('ABSPATH') or die('Plugin file cannot be accessed directly.');

/**
 * Class includes all functions for adding monthly data sheet settings
 *
 * @category Library
 * @package MonthlyDataSheet
 * @author RSK
 * @license GPL2
 *
 */
class Settings {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Constructor
     * 
     * @author RSK
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'init_page'));
        add_filter('plugin_action_links_' . plugin_basename(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'monthly-data-sheets.php'), array('MonthlyDataSheet\Settings', 'add_action_link'));
    }

    /**
     * Display settings link
     * @param String $links
     * @return String
     * 
     * @author RSK
     */
    public static function add_action_link($links) {
        $link = array('<a href="' . admin_url('options-general.php?page=monthly_data_sheets_settings') . '">Settings</a>',);
        return array_merge($links, $link);
    }

    /**
     * Add options page
     * 
     * @author RSK
     */
    public function add_settings_page() {
        // This page will be under "Settings"
        add_options_page(
                'Monthly Data Sheet Settings', 'Data Sheets', 'manage_options', 'monthly_data_sheets_settings', array($this, 'admin_settings_page')
        );
    }

    /**
     * Options page callback
     * 
     * @author RSK
     */
    public function admin_settings_page() {
        // Set class property
        $this->options = get_option('monthly_data_sheets_settings');

        print ('<div class="wrap"><h2>Monthly Data Sheet Settings</h2><form method="post" action="options.php">');

        // This prints out all hidden setting fields
        settings_fields('monthly_data_sheets_option_group');
        do_settings_sections('monthly_data_sheets_settings');
        submit_button();

        print('</form></div>');
    }

    /**
     * Register and add settings
     * 
     * @author RSK
     */
    public function init_page() {
        register_setting(
                'monthly_data_sheets_option_group', 'monthly_data_sheets_settings', array($this, 'sanitize')
        );

        add_settings_section(
                'mds_setting_section', 'Data Sheet Settings', array($this, 'settings_section_info'), 'monthly_data_sheets_settings'
        );

        add_settings_field(
                'year_start', 'Start Year <small>(eg:1985)</small>', array($this, 'year_start_callback'), 'monthly_data_sheets_settings', 'mds_setting_section'
        );

        add_settings_field(
                'year_end', 'End Year <small>(eg: 2030)</small>', array($this, 'year_end_callback'), 'monthly_data_sheets_settings', 'mds_setting_section'
        );

        add_settings_field(
                'date_format', 'Date Format', array($this, 'date_format_callback'), 'monthly_data_sheets_settings', 'mds_setting_section'
        );

        add_settings_field(
                'time_start', 'Start Time <small>(24hr format)</small>', array($this, 'time_start_callback'), 'monthly_data_sheets_settings', 'mds_setting_section'
        );

        add_settings_field(
                'time_end', 'End Time <small>(24hr format)</small>', array($this, 'time_end_callback'), 'monthly_data_sheets_settings', 'mds_setting_section'
        );

        add_settings_field(
                'time_format', 'Time Format', array($this, 'time_format_callback'), 'monthly_data_sheets_settings', 'mds_setting_section'
        );

        /* MANAGERS */
        add_settings_section(
                'mds_setting_section_managers', 'Default Managers', array($this, 'managers_section_info'), 'monthly_data_sheets_settings'
        );

        add_settings_field(
                'managers', 'Managers', array($this, 'managers_callback'), 'monthly_data_sheets_settings', 'mds_setting_section_managers'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     * 
     * @author RSK
     */
    public function sanitize($input) {
        $new_input = array();

        $new_input['managers'] = $input['managers'];

        $new_input['year_start'] = $input['year_start'];
        $new_input['year_end'] = $input['year_end'];

        $new_input['time_start'] = $input['time_start'];
        $new_input['time_end'] = $input['time_end'];

        $new_input['date_format'] = $input['date_format'];
        $new_input['time_format'] = $input['time_format'];

        $new_input['rows'] = $input['rows'];
        if ($new_input['rows'] == 'custom') {
            $row_names = explode("\r\n", $input['row_names']);

            foreach ($row_names as $key => $value) {
                $value = sanitize_text_field($value);

                if ($value != '') {
                    $new_input['row_names'][$key] = substr($value, 0, 64);
                }
            }
        }

        $new_input['columns'] = $input['columns'];
        if ($new_input['columns'] == 'custom') {
            $column_names = explode("\r\n", $input['column_names']);

            foreach ($column_names as $key => $value) {
                $value = sanitize_text_field($value);

                if ($value != '') {
                    $new_input['column_names'][$key] = substr($value, 0, 64);
                }
            }
        }

        return $new_input;
    }

    /**
     * Print the Section text
     * 
     * @author RSK
     */
    public function settings_section_info() {
        print 'Select Data Sheet Display Settings';
    }

    /**
     * Print the Section text
     * 
     * @author RSK
     */
    public function managers_section_info() {
        print 'Select data sheet managers who can edit the data sheet data (You can override this in each sheet)';
    }

    /**
     * Get the settings option array and print one of its values
     * 
     * @author RSK
     */
    public function managers_callback() {
        $users = get_users();
        $selected = isset($this->options['managers']) ? $this->options['managers'] : array();

        printf('<select id="mds_managers" name="monthly_data_sheets_settings[managers][]" multiple="true">');

        // Loop through users and disply each
        foreach ($users as $user) {
            printf('<option value="%s" %s>%s</option>', $user->id, (in_array($user->id, $selected) ? 'selected="selected"' : ''), $user->display_name);
        }

        printf('</select>');
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function year_start_callback() {
        printf('<input type="text" id="mds_year_start" name="monthly_data_sheets_settings[year_start]" value="%s" />', $this->options['year_start']);
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function year_end_callback() {
        printf('<input type="text" id="mds_year_end" name="monthly_data_sheets_settings[year_end]" value="%s" />', $this->options['year_end']);
    }

    /**
     * Get the settings option array and print one of its values
     * 
     * @author RSK
     */
    public function date_format_callback() {
        $formats = array(
            'M jS, Y' => date('M jS, Y'),
            'j M, Y' => date('j M, Y'),
            'j M, y' => date('j M, y'),
            'F j' => date('F j'),
            'M j' => date('M j'),
            'j M' => date('j M'),
            'jS' => date('jS'),
            'Y-m-d' => date('Y-m-d'),
            'n-j-Y' => date('n-j-Y'),
            'j-n-Y' => date('j-n-Y'),
            'Y/m/d' => date('Y/m/d'),
            'n/j/Y' => date('n/j/Y'),
            'j/n/Y' => date('j/n/Y'),
        );
        $selected = isset($this->options['date_format']) ? $this->options['date_format'] : array();

        printf('<select id="mds_date_format" name="monthly_data_sheets_settings[date_format]">');

        // Loop through $formats and display each
        foreach ($formats as $format => $display) {
            printf('<option value="%s" %s>%s</option>', $format, ($format == $selected ? 'selected="selected"' : ''), $display);
        }

        printf('</select>');
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function time_start_callback() {
        printf('<input type="text" id="mds_time_start" name="monthly_data_sheets_settings[time_start]" value="%s" />', $this->options['time_start']);
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function time_end_callback() {
        printf('<input type="text" id="mds_time_end" name="monthly_data_sheets_settings[time_end]" value="%s" />', $this->options['time_end']);
    }

    /**
     * Get the settings option array and print one of its values
     * 
     * @author RSK
     */
    public function time_format_callback() {
        $formats = array(
            'ga' => date('ga'),
            'g:ia' => date('g:ia'),
            'G:i' => date('G:i'),
            'G' => date('G')
        );
        $selected = isset($this->options['time_format']) ? $this->options['time_format'] : array();

        printf('<select id="mds_time_format" name="monthly_data_sheets_settings[time_format]">');

        // Loop through $formats and display each
        foreach ($formats as $format => $display) {
            printf('<option value="%s" %s>%s</option>', $format, ($format == $selected ? 'selected="selected"' : ''), $display);
        }

        printf('</select>');
    }

}
