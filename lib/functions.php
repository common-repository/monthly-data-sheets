<?php

/**
 * Functions file
 * @since 1.0.0
 * @category Library
 * @package MonthlyDataSheet
 * @author RSK
 * @license GPL2
 */

namespace MonthlyDataSheet;

/**
 * Class includes all functions for managing monthly data sheet plugin
 *
 * @category Library
 * @package MonthlyDataSheet
 * @author RSK
 * @license GPL2
 *
 */
class Functions {

    /**
     * Data sheet edit form
     * 
     * @param Array $post_rows
     * @param Array $post_columns
     * @param Array $sheet_rows
     * @param Array $sheet_columns
     * @param Array $data
     * @return String
     */
    public static function sheet_data_edit_page($post_rows, $post_columns, $sheet_rows = FALSE, $sheet_columns = FALSE, $data = FALSE) {
        $output = '
                    <table>';

        /* Use post rows and columns initially */
        if (!$data) {
            $rows = $post_rows;
            $columns = $post_columns;
        } else {
            $rows = empty($sheet_rows) ? $post_rows : $sheet_rows;
            $columns = empty($sheet_columns) ? $post_columns : $sheet_columns;
        }

        /* Add an empty row initially, to display the column names */
        $rows[-1] = '';
        ksort($rows);

        /* Rows */
        foreach ($rows as $row_id => $row) {
            $row_label = is_array($row) ? $row['label'] : $row;
            $row_name = is_array($row) ? $row['name'] : $row;
            $output .= '
                        <tr>
                            <th>' . (($row_id < 0) ? '' : $row_label) . '</th>';

            /* Columns */
            foreach ($columns as $column_id => $column) {
                $column_label = is_array($column) ? $column['label'] : $column;
                $column_name = is_array($column) ? $column['name'] : $column;

                /* First entry will be the column name */
                if ($row_id < 0) {
                    $output .= '
                            <th>' . $column_label . '</th>';
                } else {
                    $output .= '
                            <td>
                                <textarea name="mds_data[' . $row_id . '::' . $row_name . '][' . $column_id . '::' . $column_name . ']" rows="1" cols="10">' . (isset($data[$row_id][$column_id]) ? $data[$row_id][$column_id] : '') . '</textarea>
                            </td>';
                }
            }
            $output .= '
                        </tr>';
        }

        $output .= '
                    </table>';
        return $output;
    }

    /**
     * Data sheet display
     * 
     * @param Array $post_rows
     * @param Array $post_columns
     * @param Array $sheet_rows
     * @param Array $sheet_columns
     * @param Array $data
     * @return String
     */
    public static function sheet_data_view_page($post_rows, $post_columns, $sheet_rows, $sheet_columns, $data) {

        if (!$data)
            return '';

        $rows = empty($sheet_rows) ? $post_rows : $sheet_rows;
        $columns = empty($sheet_columns) ? $post_columns : $sheet_columns;

        /* Add an empty row initially, to display the column names */
        $rows[-1] = '';
        ksort($rows);

        $output = '
                    <table>';
        /* Rows */
        foreach ($rows as $row_id => $row) {
            $row_label = is_array($row) ? $row['label'] : $row;
            $output .= '
                        <tr>
                            <th>' . (($row_id < 0) ? '' : $row_label) . '</th>';

            /* Columns */
            foreach ($columns as $column_id => $column) {
                $column_label = is_array($column) ? $column['label'] : $column;

                /* First entry will be the column name */
                if ($row_id < 0) {
                    $output .= '
                            <th>' . $column_label . '</th>';
                } else {
                    $output .= '
                            <td>
                                ' . (isset($data[$row_id][$column_id]) ? $data[$row_id][$column_id] : '') . '
                            </td>';
                }
            }
            $output .= '
                        </tr>';
        }

        $output .= '
                    </table>';

        return $output;
    }

    /**
     * Delete confirmation form
     * 
     * @param type $delete_action
     * @param type $year
     * @param type $month
     * @return string
     */
    public static function sheet_data_delete_page($delete_action, $year, $month) {
        $output = '<p>Do you really want to delete the sheet data?</p>
                <div class="mds-left mds-md-4">
                    <form method="post" action="' . get_the_permalink() . '">
                        <input type="submit" name="mds_action" value="Cancel" />
                        <input type="hidden" name="mds_month" value="' . $month . '">
                        <input type="hidden" name="mds_year" value="' . $year . '">
                    </form>
                </div>
                <div class="mds-right mds-md-4">
                    <form method="post" action="' . get_the_permalink() . '">
                        <input type="submit" name="mds_action" value="' . $delete_action . '" />
                        <input type="hidden" name="mds_month" value="' . $month . '">
                        <input type="hidden" name="mds_year" value="' . $year . '">
                    </form>
                </div>
            ';
        return $output;
    }

    /**
     * Get the default rows ie, dates in current month
     * 
     * @param Array $options
     * @param String $selected_year
     * @param String $selected_month
     * @return type
     */
    public static function get_default_rows($options, $selected_year, $selected_month) {
        $post_rows = array();
        $date_format = $options['date_format'];
        $days = date('t', strtotime($selected_year . '-' . $selected_month . '01'));
        for ($day = 1; $day <= $days; $day++) {
            $date = $selected_year . '-' . $selected_month . '-' . $day;
            $post_rows[] = array('name' => $date, 'label' => date($date_format, strtotime($date)));
        }
        return $post_rows;
    }

    /**
     * Get the default row names. ie, dates in current month
     * 
     * @param Array $options
     * @return Array
     */
    public static function get_default_columns($options) {
        $post_columns = array();
        $start_hour = $options['time_start'];
        $end_hour = $options['time_end'];
        $time_format = $options['time_format'];
        for ($hour = $start_hour; $hour <= $end_hour; $hour++) {
            $post_columns[] = array('name' => $hour, 'label' => date($time_format, mktime($hour, 0, 0)));
        }
        return $post_columns;
    }

}
