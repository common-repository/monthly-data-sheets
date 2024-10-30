<?php

/**
 * Main actions file
 * @since 1.0.0
 * @category Library
 * @package MonthlyDataSheet
 * @author RSK
 * @license GPL2
 */

namespace MonthlyDataSheet;

/**
 * Class includes all functions for setting up monthly data sheets plugin
 *
 * @category Library
 * @package MonthlyDataSheet
 * @author RSK
 * @license GPL2
 *
 */
class Main {

    public function __construct() {
        $pluginFile = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'monthly-data-sheets.php';

        /*
         * Register the activation hook function
         */
        register_activation_hook($pluginFile, array($this, 'activate'));

        /**
         * Bind the action to add scripts and styles
         */
        add_action('wp_enqueue_scripts', array($this, 'scripts'));

        /**
         * Create the post type 'datasheet'
         */
        add_action('init', array($this, 'datasheet_post'), 0);

        /**
         * Create the taxonomy 'sheet_category'
         */
        add_action('init', array($this, 'datasheet_category'), 0);
    }

    /**
     * Add settings on plugin activation
     */
    public static function activate() {
        global $wpdb;
        global $mds_db_version;
        $mds_installed_db_version = get_option('mds_db_version');

        /* Create table only the table does not already exist */
        if ($mds_db_version != $mds_installed_db_version) {
            $charset_collate = $wpdb->get_charset_collate();
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            $sheet_table = $wpdb->prefix . 'mds_sheet';
            $sheet_sql = 'CREATE TABLE `' . $sheet_table . '` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `post_id` int(11) NOT NULL,
                    `year` int(11) NOT NULL,
                    `month` int(11) NOT NULL,
                    `rows` text NOT NULL,
                    `columns` text NOT NULL,
                    `created_time` datetime NOT NULL,
                    `updated_time` datetime NOT NULL,
                    PRIMARY KEY (`id`)
                   ) ENGINE=InnoDB ' . $charset_collate;
            dbDelta($sheet_sql);

            $data_table = $wpdb->prefix . 'mds_sheet_data';
            $data_sql = 'CREATE TABLE `' . $data_table . '` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `sheet_id` int(11) NOT NULL,
                    `row` varchar(64) NOT NULL,
                    `column` varchar(64) NOT NULL,
                    `data` text NOT NULL,
                    `type` varchar(64) NOT NULL,
                    `updated_time` datetime NOT NULL,
                    PRIMARY KEY (`id`)
                   ) ENGINE=InnoDB ' . $charset_collate;

            dbDelta($data_sql);

            add_option('mds_db_version', $mds_db_version);
        }
        $manager = 1;
        $users = get_users();

        if (isset($users[0]->ID) && $users[0]->ID) {
            $manager = $users[0]->ID;
        }

        /* Set Default data */
        $options = array(
            'managers' => array($manager),
            'columns' => 'default',
            'column_names' => array(),
            'rows' => 'default',
            'row_names' => array(),
            'date_format' => _mds_date_format,
            'time_format' => _mds_time_format,
            'year_start' => _mds_year_start,
            'year_end' => _mds_year_end,
            'time_start' => _mds_time_start,
            'time_end' => _mds_time_end
        );
        add_option('monthly_data_sheets_settings', $options);
    }

    /**
     * Function to add scripts and styles
     */
    public static function scripts() {
        wp_enqueue_style("mds-styles", plugin_dir_url(dirname(__FILE__)) . "css/styles.css", array(), '1.0.0');

        /* Register scripts */
        wp_register_script('mds-scripts', plugin_dir_url(dirname(__FILE__)) . "js/scripts.js", array("jquery"), "1.0.2", true);

        /* Localize the script with data */
        $js_variables = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'homeUrl' => home_url()
        );
        wp_localize_script('mds-scripts', 'mcJS', $js_variables);

        /* Enqueue the script with localized data. */
        wp_enqueue_script("mds-scripts");
    }

    /**
     * Function to add scripts and styles
     */
    public static function admin_scripts() {
        wp_enqueue_style("mds-admin-styles", plugin_dir_url(dirname(__FILE__)) . "css/admin-styles.css", array(), '1.0.0');

        /* Register scripts */
        wp_register_script('mds-admin-scripts', plugin_dir_url(dirname(__FILE__)) . "js/admin-scripts.js", array('jquery'), '1.0.0', true);

        /* Localize the script with data */
        $jsDomainVariables = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'homeUrl' => home_url()
        );
        wp_localize_script('mds-admin-scripts', 'MCJS', $jsDomainVariables);

        /* Enqueue the script with localized data. */
        wp_enqueue_script("mds-admin-scripts");
    }

    /**
     * Register Custom Post Type
     */
    public static function datasheet_post() {

        $labels = array(
            'name' => _x('Data Sheets', 'Post Type General Name', 'monthly-data-sheet'),
            'singular_name' => _x('Data Sheet', 'Post Type Singular Name', 'monthly-data-sheet'),
            'menu_name' => __('Data Sheets', 'monthly-data-sheet'),
            'name_admin_bar' => __('Data Sheet', 'monthly-data-sheet'),
            'parent_item_colon' => __('Parent Data Sheet:', 'monthly-data-sheet'),
            'all_items' => __('All Data Sheets', 'monthly-data-sheet'),
            'add_new_item' => __('Add New Data Sheet', 'monthly-data-sheet'),
            'add_new' => __('Add New', 'monthly-data-sheet'),
            'new_item' => __('New Data Sheet', 'monthly-data-sheet'),
            'edit_item' => __('Edit Data Sheet', 'monthly-data-sheet'),
            'update_item' => __('Update Data Sheet', 'monthly-data-sheet'),
            'view_item' => __('View Data Sheet', 'monthly-data-sheet'),
            'search_items' => __('Search Data Sheet', 'monthly-data-sheet'),
            'not_found' => __('Not found', 'monthly-data-sheet'),
            'not_found_in_trash' => __('Not found in Trash', 'monthly-data-sheet'),
            'items_list' => __('Data Sheets list', 'monthly-data-sheet'),
            'items_list_navigation' => __('Data Sheets list navigation', 'monthly-data-sheet'),
            'filter_items_list' => __('Filter sheets list', 'monthly-data-sheet'),
        );
        $args = array(
            'label' => __('Data Sheet', 'monthly-data-sheet'),
            'description' => __('Data Sheet post type.', 'monthly-data-sheet'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'thumbnail',),
            'taxonomies' => array('sheet_category'),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 20,
            'menu_icon' => 'dashicons-admin-page',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'capability_type' => 'page',
        );
        register_post_type('datasheet', $args);
    }

    /**
     *  Register Custom Taxonomy
     */
    public static function datasheet_category() {

        $labels = array(
            'name' => _x('Categories', 'Taxonomy General Name', 'monthly-data-sheet'),
            'singular_name' => _x('Category', 'Taxonomy Singular Name', 'monthly-data-sheet'),
            'menu_name' => __('Categories', 'monthly-data-sheet'),
            'all_items' => __('All Categories', 'monthly-data-sheet'),
            'parent_item' => __('Parent Category', 'monthly-data-sheet'),
            'parent_item_colon' => __('Parent Category:', 'monthly-data-sheet'),
            'new_item_name' => __('New Category Name', 'monthly-data-sheet'),
            'add_new_item' => __('Add New Category', 'monthly-data-sheet'),
            'edit_item' => __('Edit Category', 'monthly-data-sheet'),
            'update_item' => __('Update Category', 'monthly-data-sheet'),
            'view_item' => __('View Category', 'monthly-data-sheet'),
            'separate_items_with_commas' => __('Separate items with commas', 'monthly-data-sheet'),
            'add_or_remove_items' => __('Add or remove items', 'monthly-data-sheet'),
            'choose_from_most_used' => __('Choose from the most used', 'monthly-data-sheet'),
            'popular_items' => __('Popular Categories', 'monthly-data-sheet'),
            'search_items' => __('Search Categories', 'monthly-data-sheet'),
            'not_found' => __('Not Found', 'monthly-data-sheet'),
            'items_list' => __('Categories list', 'monthly-data-sheet'),
            'items_list_navigation' => __('Categories list navigation', 'monthly-data-sheet'),
        );
        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
        );
        register_taxonomy('datasheet_category', array('datasheet'), $args);
    }

    /**
     * Filter function to append sheet to the sheet post content
     * 
     * @param String $content
     * @param type $post
     */
    public static function display($content) {
        global $post;
        $user_id = get_current_user_id();
        $sheet_id = get_the_ID();
        $action_save = 'Save Sheet';
        $action_edit = 'Edit Sheet';
        $action_delete = 'Delete Sheet';
        $action_cancel = 'Cancel';

        /* Return post content for all other posts */
        if ($post->post_type != 'datasheet') {
            return $post->post_content;
        }

        $options = get_option('monthly_data_sheets_settings');
        $managers = get_post_meta($sheet_id, 'mds_managers', TRUE);
        $post_rows = get_post_meta($sheet_id, 'mds_row_names', TRUE);
        $post_columns = get_post_meta($sheet_id, 'mds_column_names', TRUE);
        $is_sheet_manager = in_array($user_id, $managers) ? TRUE : FALSE;

        $action = isset($_REQUEST['mds_action']) ? $_REQUEST['mds_action'] : $action_cancel;
        $selected_month = isset($_REQUEST['mds_month']) ? $_REQUEST['mds_month'] : date('m');
        $selected_year = isset($_REQUEST['mds_year']) ? $_REQUEST['mds_year'] : date('Y');

        /* Save submitted data if save button is clicked */
        if (isset($_POST['mds_action']) && $_POST['mds_action'] == $action_save) {
            self::save($sheet_id);
        } else if (isset($_POST['mds_delete']) && $_POST['mds_delete'] == $action_delete) {

            /* Delete button in edit page is clicked */
            echo Functions::sheet_data_delete_page($action_delete, $selected_year, $selected_month);
            return;
        } else if (isset($_POST['mds_action']) && $_POST['mds_action'] == $action_delete) {

            /* Delete button in delete confirmation page is clicked */
            self::delete($sheet_id, $selected_year, $selected_month);
        }

        /* Take default row names. */
        if (empty($post_rows)) {
            $post_rows = Functions::get_default_rows($options, $selected_year, $selected_month);
        }

        /* Take default row names. */
        if (empty($post_columns)) {
            $post_columns = Functions::get_default_columns($options);
        }

        $year_start = $options['year_start'];
        $year_end = $options['year_end'];

        $months = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        );

        /* Years drop down values */
        $year_options = '';
        for ($year = $year_start; $year <= $year_end; $year++) {
            $selected = ($year == $selected_year) ? 'selected="selected"' : '';
            $year_options .= '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
        }

        /* Months drop down values */
        $month_options = '';
        foreach ($months as $month => $month_name) {
            $selected = (($month + 1) == $selected_month) ? 'selected="selected"' : '';
            $month_options .= '<option value="' . ($month + 1) . '" ' . $selected . '>' . $month_name . '</option>';
        }

        $data = FALSE;
        $sheet_rows = $post_rows;
        $sheet_columns = $post_columns;
        $sheet = Data::get_sheet($sheet_id, $selected_year, $selected_month);
        if ($sheet) {
            $data = Data::get_sheet_data($sheet->id);
            $sheet_rows = !empty($sheet->rows) ? unserialize($sheet->rows) : Functions::get_default_rows($options, $selected_year, $selected_month);
            $sheet_columns = !empty($sheet->columns) ? unserialize($sheet->columns) : Functions::get_default_columns($options);
        }

        /* Enable edit option if requested user is a sheet manager */
        $sheet_buttons = $sheet_links = '';
        if ($is_sheet_manager) {
            $sheet_buttons = '<input class="mds-button" type="submit" name="mds_action" value="' . (($action == $action_edit) ? $action_save : $action_edit) . '" />';

            /* View sheet button for edit page */
            if ($action == $action_edit) {
                $sheet_buttons .= '<input class="mds-button" type="submit" name="mds_cancel" value="' . $action_cancel . '" />';
            } else if ($data) {

                /* Show delete button if there is any data */
                $sheet_buttons .= '<input class="mds-button" type="submit" name="mds_delete" value="' . $action_delete . '" />';
            }
        }

        /* Show links to view next and previous months in view page */
        if ($action != $action_edit) {
            $next_month = $selected_month + 1;
            $prev_month = $selected_month - 1;
            $next_year = $selected_year;
            $prev_year = $selected_year;

            if ($selected_month == 12) {
                $next_month = 1;
                $next_year = $selected_year + 1;
            } else if ($selected_month == 1) {
                $prev_month = 12;
                $prev_year = $selected_year - 1;
            }

            $sheet_links = '
                <div class="mds-left mds-md-4">
                    <a href="' . get_the_permalink() . '?mds_month=' . $prev_month . '&mds_year=' . $prev_year . '">&lt;&lt; Previous month</a>
                </div>
                <div class="mds-right mds-md-4">
                    <a href="' . get_the_permalink() . '?mds_month=' . $next_month . '&mds_year=' . $next_year . '">Next month &gt;&gt;</a>
                </div>';
        }

        $sheet_links = '
        <div class="mds-md-12">' . $sheet_links . '</div>';

        $sheet_selects = '
                <select name="mds_year" id="mds_year">' . $year_options . '</select><select name="mds_month" id="mds_month">' . $month_options . '</select>';

        $display = ((!empty($sheet_rows) && (count($sheet_rows) > 5) && $data) ? $sheet_links : '') . '
        <form method="post" action="' . get_the_permalink() . '">
            <div class="mds-container mds-md-12">
                <div class="mds-left mds-md-6">' . $sheet_selects . '</div>
                <div class="mds-right mds-md-6">' . $sheet_buttons . '</div>
            </div>
            <div class="mds-container mds-md-12">';

        /* Show edit form on request */
        if ($action == $action_edit) {
            $display .= Functions::sheet_data_edit_page($post_rows, $post_columns, $sheet_rows, $sheet_columns, $data);
        } else {
            $display .= Functions::sheet_data_view_page($post_rows, $post_columns, $sheet_rows, $sheet_columns, $data);
        }
        $display .= '
            </div>';

        /* Show message if rows/colmns are deleted or added in the sheet post */
        if ($is_sheet_manager && $data && $post_rows != $sheet_rows || $post_columns != $sheet_columns) {
            $display .= '<b>Note: </b><small>The new rows/columns will not appear in monthly sheets with data. Delete the current sheet data to load new sheet with latest rows and columns.</small>';
        }

        /* Show another save button beneath the sheet in edit page */
        if (empty($sheet_rows) || (count($sheet_rows)) > 5 && ($data || $action == 'Edit Sheet')) {
            $display .= '<div class="mds-right mds-md-12">' . $sheet_buttons . '</div>';
        }

        $display .= '<input type="hidden" name="mds_url" id="mds_url" value="' . get_the_permalink() . '" /><input type="hidden" name="mds_id" value="' . get_the_ID() . '" /></form>';

        return $post->post_content . $display . $sheet_links;
    }

    /**
     * Function to save sheet data
     * 
     * @return Boolean
     */
    public static function save($post_id) {
        global $wpdb;
        $sheet_id = 0;
        $old_data = array();

        /* Return if no data is submitted */
        if (!isset($_POST['mds_data'])) {
            return TRUE;
        }
        /* Return if ID is manipulated */
        if ($post_id != $_POST['mds_id']) {
            return TRUE;
        }

        $row_names = $column_names = array();
        $new_data = $_POST['mds_data'];
        $year = $_POST['mds_year'];
        $month = $_POST['mds_month'];

        $sheet = Data::get_sheet($post_id, $year, $month);
        if ($sheet) {
            $sheet_id = $sheet->id;
            $old_data = Data::get_sheet_data($sheet->id, $year, $month);
        } else {
            $sheet_id = Data::add_sheet($post_id, $year, $month);
        }

        /* Process the new data */
        foreach ($new_data as $row => $columns) {
            $row_sep = strpos($row, '::');
            $row_id = substr($row, 0, $row_sep);
            $row_name = substr($row, ($row_sep + 2));

            foreach ($columns as $column => $data) {
                $col_sep = strpos($column, '::');
                $column_id = substr($column, 0, $col_sep);
                $column_name = substr($column, ($col_sep + 2));

                $data = sanitize_text_field($data);

                /* Replace the old data in DB with new data in the sheet */
                if ($data != '' && isset($old_data[$row_id][$column_id])) {
                    Data::add_sheet_cell($sheet_id, $row_id, $column_id, $data, TRUE);
                } else if ($data != '' && !isset($old_data[$row_id][$column_id])) {

                    /* Add new data in sheet to DB */
                    Data::add_sheet_cell($sheet_id, $row_id, $column_id, $data, FALSE);
                } else if (isset($old_data[$row_id][$column_id])) {

                    /* Remove the old data in table, which is removed from sheet */
                    Data::delete_sheet_cell($sheet_id, $row_id, $column_id);
                }
                $row_names[$row_id] = $row_name;
                $column_names[$column_id] = $column_name;
            }
        }

        /* Clear sheet rows if parent post rows are empty (default) */
        if (get_post_meta($post_id, 'mds_row_names', TRUE) == '') {
            $row_names = '';
        }

        /* Clear sheet columns if parent post columns are empty (default) */
        if (get_post_meta($post_id, 'mds_column_names', TRUE) == '') {
            $column_names = '';
        }
        Data::update_sheet($sheet_id, $row_names, $column_names);
    }

    /**
     * Delete the sheet and data of a given month
     * 
     * @param type $post_id
     * @param type $year
     * @param type $month
     */
    public static function delete($post_id, $year, $month) {
        $sheet = Data::get_sheet($post_id, $year, $month);
        if ($sheet) {
            Data::delete_sheet($sheet->id);
        }
    }

}
