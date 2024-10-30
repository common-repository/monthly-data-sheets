<?php

/**
 * Meta box display and actions file
 * @since 1.0.0
 * @category Library
 * @package MonthlyDataSheet
 * @author RSK
 * @license GPL2
 */

namespace MonthlyDataSheet;

/**
 * Class includes all functions for setting up monthly data sheet meta box and fields
 *
 * @category Library
 * @package MonthlyDataSheet
 * @author RSK
 * @license GPL2
 *
 */
class Meta {

    public function __construct() {

        if (is_admin()) {
            add_action('load-post.php', array($this, 'init_metabox'));
            add_action('load-post-new.php', array($this, 'init_metabox'));
        }
    }

    public function init_metabox() {
        add_action('add_meta_boxes', array($this, 'add_metabox'));
        add_action('save_post', array($this, 'save_metabox'), 10, 2);
    }

    public function add_metabox() {
        add_meta_box(
                'monthly_data_sheets', __('Data Sheet Settings', 'datasheet'), array($this, 'render_metabox'), 'datasheet', 'advanced', 'high'
        );
    }

    public function render_metabox($post) {
        // Add nonce for security and authentication.
        wp_nonce_field('monthly_data_sheets_nonce_action', 'monthly_data_sheets_nonce');

        // Retrieve an existing value from the database.
        $row_names = get_post_meta($post->ID, 'mds_row_names', true);
        $column_names = get_post_meta($post->ID, 'mds_column_names', true);
        $managers = get_post_meta($post->ID, 'mds_managers', true);
        $mds_options = get_option('monthly_data_sheets_settings');

        /* If post is not saved, then prefill the managers from options */
        if (empty($post->post_name)) {
            $managers = isset($mds_options['managers']) && count($mds_options['managers']) ? $mds_options['managers'] : array();
        }

        /* Form fields. */
        echo '
            <table class="mds-meta-box">
                <tr>
                    <th>
                        <label for="mds_managers">' . __('Managers', 'monthly_data_sheets') . '</label>
                    </th>
                    <td>
                        <p>
                            <select id="mds_managers" name="mds_managers[]" multiple="true" class="widefat">';

        $users = get_users();

        // Loop through users and disply each
        foreach ($users as $user) {
            printf('
                                <option value="%s" %s>%s</option>', $user->id, (in_array($user->id, $managers) ? 'selected="selected"' : ''), $user->display_name);
        }

        echo '
                            </select>
                            <small>' . __('Choose sheet managers (who can edit the sheets from front end)', 'monthly_data_sheets') . '</small>
                        </p>                         
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="mds_row_names">' . __('Rows', 'monthly_data_sheets') . '</label>
                    </th>
                    <td>
                        ' . $this->title_block_container('row', $row_names) . '
                            <small>' . __('Add custom rows (Leave empty to use dates of month as default rows )', 'monthly_data_sheets') . '</small>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="mds_column_names">' . __('Columns', 'monthly_data_sheets') . '</label>
                    </th>
                    <td>
                        ' . $this->title_block_container('column', $column_names) . '
                            <small>' . __('Add custom columns (Leave empty to use hours of day as default columns )', 'monthly_data_sheets') . '</small>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><b>Note:</b> <i>Any change in the rows/columns will appear in sheets without data only. Old sheets will remain intact.</i></td>
                </tr>
            </table>';
    }

    /**
     * Display the titles(rows/columns) with add more options
     * 
     * @param String $type
     * @param Array $names
     * @return string
     */
    private function title_block_container($type, $names = array()) {
        $id = 0;
        $count = is_array($names) ? count($names) : 0;
        $display = '
                    <div class="mds-titles-block">
                        <table border="0">
                            <tbody>
                                <tr style="display: ' . ($count ? 'table-row' : 'none') . ';">
                                    <td>Order</td>
                                    <td>Name</td>
                                    <td></td>
                                </tr>';
        /* Display the title blocks if there is any */
        if ($count) {
            for ($id = 0; $id < $count; $id++) {
                $display .= $this->title_block($type, ($id + 1), $names[$id]);
            }
        }
        $display .= '
                            </tbody>
                        </table>
                        <p>
                            <input type="hidden" name="mds_' . $type . '_count" id="mds_' . $type . '_count" value="' . $count . '" />
                            <input type="button" name="mds_add_' . $type . '" parent_type="' . $type . '" count="' . $count . '" value="Add ' . $type . '" class="button mds_add_block"/>
                        </p>
                    </div>';
        return $display;
    }

    /**
     * Display each title row
     * Also used for AJAX creation of title rows
     * 
     * @param String $type
     * @param Integer $id
     * @return string
     */
    public static function title_block($type, $id = 0, $value = '') {
        $type = isset($_POST['mds_block_type']) ? $_POST['mds_block_type'] : $type;
        $id = isset($_POST['mds_block_id']) ? $_POST['mds_block_id'] : $id;

        $row = '
                            <tr id="mds_' . $type . '_' . $id . '" class="mds-more-' . $type . '">
                                <td>
                                    <input type="number" id="mds_' . $type . '_order_' . $id . '" name="mds_' . $type . '[order][]" value="' . $id . '" class="field small-text" />
                                </td>
                                <td>
                                    <input type="text" id="mds_' . $type . '_name_' . $id . '" name="mds_' . $type . '[name][]" value="' . $value . '" class="field" />
                                </td>
                                <td>
                                    <input type="button" name="mds_' . $type . '_delete[]" value="Remove" parent_type="' . $type . '" class="button mds_delete_block"/>
                                </td>
                            </tr>';

        /* Print the data for ajax calls */
        if (defined('DOING_AJAX') && DOING_AJAX) {
            echo $row;
            wp_die();
        }
        return $row;
    }

    /**
     * Save metabox data
     * 
     * @param Integer $post_id
     * @param Object $post
     * @return Boolean
     */
    public function save_metabox($post_id, $post) {

        /* Process data only if any data is posted */
        if (!isset($_POST['monthly_data_sheets_nonce'])) {
            return;
        }
        // Add nonce for security and authentication.
        $nonce_name = $_POST['monthly_data_sheets_nonce'];
        $nonce_action = 'monthly_data_sheets_nonce_action';

        // Check if post type is 'monthly_data_sheets'
        if ($post->post_type != 'datasheet')
            return;

        // Check if a nonce is set and valid.
        if (!isset($nonce_name) || !wp_verify_nonce($nonce_name, $nonce_action))
            return;

        // Check if the user has permissions to save data.
        if (!current_user_can('edit_post', $post_id))
            return;

        // Check if it's not an autosave or a revision
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id))
            return;

        $managers = $_POST['mds_managers'];

        /* If custom rows are added */
        $row_names = $mds_row_names = array();
        if ($_POST['mds_row_count'] > 0) {
            $rows = $_POST['mds_row'];

            /* Fetch data, sanitize and group in order */
            for ($count = 0; $count < $_POST['mds_row_count']; $count++) {
                $name = substr(sanitize_text_field($rows['name'][$count]), 0, 64);

                if (!empty($name)) {
                    $row_names[sanitize_text_field($rows['order'][$count])][] = $name;
                }
            }

            /* Sort in order */
            ksort($row_names);

            /* Merge the grouped names */
            foreach ($row_names as $each) {
                $mds_row_names = array_merge($mds_row_names, $each);
            }
        }

        /* If custom columns are provided */
        $column_names = $mds_column_names = array();
        if ($_POST['mds_column_count'] > 0) {
            $columns = $_POST['mds_column'];

            /* Fetch data, sanitize and group in order */
            for ($count = 0; $count < $_POST['mds_column_count']; $count++) {
                $name = substr(sanitize_text_field($columns['name'][$count]), 0, 64);

                if (!empty($name)) {
                    $column_names[sanitize_text_field($columns['order'][$count])][] = $name;
                }
            }

            /* Sort in order */
            ksort($column_names);

            /* Merge the grouped names */
            foreach ($column_names as $each) {
                $mds_column_names = array_merge($mds_column_names, $each);
            }
        }

        /* Change empty array to empty string */
        if (!count($mds_row_names))
            $mds_row_names = '';

        if (!count($mds_column_names))
            $mds_column_names = '';

        // Update the meta field in the database.
        update_post_meta($post_id, 'mds_row_names', $mds_row_names);
        update_post_meta($post_id, 'mds_column_names', $mds_column_names);
        update_post_meta($post_id, 'mds_managers', $managers);
    }

}
