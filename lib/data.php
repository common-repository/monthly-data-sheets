<?php

/**
 * Main database actions file
 * @since 1.0.0
 * @category Library
 * @package MonthlyDataSheet
 * @author RSK
 * @license GPL2
 */

namespace MonthlyDataSheet;

/**
 * Class includes all functions for managing data fetches and updates of monthly data sheets plugin
 *
 * @category Library
 * @package MonthlyDataSheet
 * @author RSK
 * @license GPL2
 *
 */
class Data {

    const sheet_table = 'mds_sheet';
    const sheet_data_table = 'mds_sheet_data';

    /**
     * Create new sheet for the given post
     * 
     * @global \MonthlyDataSheet\Object $wpdb
     * @param type $post_id
     * @param type $year
     * @param type $month
     * @param type $rows
     * @param type $columns
     */
    public static function add_sheet($post_id, $year, $month, $rows = '', $columns = '') {
        global $wpdb;
        $table = $wpdb->prefix . self::sheet_table;

        $table_data = array(
            'post_id' => $post_id,
            'year' => $year,
            'month' => $month,
            'rows' => serialize($rows),
            'columns' => serialize($columns),
            'created_time' => date('Y-m-d h:i:s')
        );

        /* Return the sheet id */
        if ($wpdb->insert($table, $table_data)) {
            return $wpdb->insert_id;
        }
        return FALSE;
    }

    /**
     * Update the sheet rows and columns (may differ if post fields are edited)
     * 
     * @global \MonthlyDataSheet\Object $wpdb
     * @param type $sheet_id
     * @param type $rows
     * @param type $columns
     * @return boolean
     */
    public static function update_sheet($sheet_id, $rows, $columns) {

        global $wpdb;
        $table = $wpdb->prefix . self::sheet_table;

        $table_data = array(
            'id' => $sheet_id,
            'rows' => (!empty($rows) ? serialize($rows) : ''),
            'columns' => (!empty($columns) ? serialize($columns) : ''),
            'updated_time' => date('Y-m-d h:i:s')
        );

        $where = array(
            'id' => $sheet_id,
        );

        /* Return the sheet id */
        if ($wpdb->update($table, $table_data, $where)) {
            return $wpdb->insert_id;
        }
        return FALSE;
    }

    /**
     * Retrieves the sheet info for the given month in given year
     * 
     * @global Object $wpdb
     * @param Integer $post_id
     * @param Integer $year
     * @param Integer $month
     * @return Boolean
     */
    public static function get_sheet($post_id, $year, $month) {
        global $wpdb;
        $table = $wpdb->prefix . self::sheet_table;

        $query = "
                SELECT * 
                FROM " . $table . " 
                WHERE 
                    post_id = %d 
                    AND `year` = %d 
                    AND `month` = %d";
        return $wpdb->get_row($wpdb->prepare($query, $post_id, $year, $month));
    }

    /**
     * Retrieves the sheet info for the given month and year
     * 
     * @global Object $wpdb
     * @param Integer $post_id
     * @param Integer $year
     * @param Integer $month
     */
    public static function get_post_data($post_id, $year, $month) {
        global $wpdb;
        $post_data = array();
        $sheet_table = $wpdb->prefix . 'mds_sheet';
        $data_table = $wpdb->prefix . 'mds_sheet_data';

        $query = "
            SELECT sheet.id AS sheet_id, `row`, `column`, `data` 
            FROM " . $sheet_table . " sheet 
            JOIN " . $data_table . " sheet_data ON (sheet.id = sheet_data.sheet_id)
            WHERE 
                    post_id = %d 
                    AND `year` = %d
                    AND `month` = %d";
        $data = $wpdb->get_results($wpdb->prepare($query, $post_id, $year, $month));

        if (!$data)
            return FALSE;

        /* Format the data into a multidimensional array */
        foreach ($data as $row) {
            $post_data[$row->row][$row->column] = $row->data;
        }

        return $post_data;
    }

    /**
     * Retrieves the data of the given sheet
     * 
     * @global Object $wpdb
     * @param Integer $sheet_id
     */
    public static function get_sheet_data($sheet_id) {
        global $wpdb;
        $sheet_data = array();
        $table = $wpdb->prefix . self::sheet_data_table;

        $query = "
            SELECT `row`, `column`, `data` 
            FROM " . $table . " mds_sheet_data
            WHERE sheet_id = %d";
        $data = $wpdb->get_results($wpdb->prepare($query, $sheet_id));

        if (!$data)
            return FALSE;

        /* Format the data into a multidimensional array */
        foreach ($data as $row) {
            $sheet_data[$row->row][$row->column] = $row->data;
        }

        return $sheet_data;
    }

    /**
     * Add a new cell data
     * 
     * @global \MonthlyDataSheet\Object $wpdb
     * @param type $sheet_id
     * @param type $row
     * @param type $column
     * @param type $data
     * @param type $dataExists
     */
    public static function add_sheet_cell($sheet_id, $row, $column, $data, $dataExists = FALSE) {
        global $wpdb;
        $updated = FALSE;
        $table = $wpdb->prefix . self::sheet_data_table;

        /* Update existing data */
        if ($dataExists) {
            $table_data = array(
                'data' => $data,
                'type' => 'text',
                'updated_time' => date('Y-m-d h:i:s')
            );

            $where = array(
                'sheet_id' => $sheet_id,
                'row' => $row,
                'column' => $column
            );
            $updated = $wpdb->update($table, $table_data, $where);
        } else {
            $table_data = array(
                'sheet_id' => $sheet_id,
                'row' => $row,
                'column' => $column,
                'data' => $data,
                'type' => 'text',
                'updated_time' => date('Y-m-d h:i:s')
            );

            $updated = $wpdb->insert($table, $table_data);
        }
        return $updated;
    }

    /**
     * Delete an existing cell data
     * 
     * @global \MonthlyDataSheet\Object $wpdb
     * @param type $sheet_id
     * @param type $row
     * @param type $column
     */
    public static function delete_sheet_cell($sheet_id, $row, $column) {
        global $wpdb;
        $table = $wpdb->prefix . self::sheet_data_table;

        $where = array(
            'sheet_id' => $sheet_id,
            'row' => $row,
            'column' => $column,
        );
        $wpdb->delete($table, $where);
    }

    /**
     * Delete the sheet and data from DB
     * 
     * @global \MonthlyDataSheet\Object $wpdb
     * @param type $sheet_id
     */
    public static function delete_sheet($sheet_id) {
        global $wpdb;
        $sheet_table = $wpdb->prefix . self::sheet_table;
        $sheet_data_table = $wpdb->prefix . self::sheet_data_table;
        $wpdb->delete($sheet_table, array('id' => $sheet_id));
        $wpdb->delete($sheet_data_table, array('sheet_id' => $sheet_id));
    }

}
