/* 
 Created on : Dec 03, 2015
 Author     : RSK
 */


var $ = jQuery.noConflict();

var mds_row_count = 0;
var mds_column_count = 0;

$(document).ready(function () {

    /* initialize the counters */
    if ($('#mds_row_count').is('input')) {
        mds_row_count = parseInt($('#mds_row_count').val());
    }
    if ($('#mds_column_count').is('input')) {
        mds_column_count = parseInt($('#mds_column_count').val());
    }

    /* Delete row/column blocks */
    $(document).on("click", '.mds_delete_block', mds_delete_block);

    /* Add more rows/columns */
    $(document).on("click", '.mds_add_block', mds_add_block);

    /* Check order fields to ensure that only numbers are added */
    $(document).on("change", 'input[name="mds_row[order][]"]', number_check);
    $(document).on("change", 'input[name="mds_column[order][]"]', number_check);
});

/**
 * Check whether the field value is a number
 * 
 * @param {Object} event
 * @returns {undefined}
 */
function number_check(event) {
    var element = event.target;

    /* Set the field to zero if not a number */
    if (isNaN($(element).val())) {
        $(element).val(0);
    }
}

/**
 * Delete add more block 
 * 
 * @param {Object} event
 * @returns {undefined}
 */
function mds_delete_block(event) {
    var element = event.target;
    var parent_type = $(element).attr('parent_type');
    var parent = $(element).parents('.mds-more-' + parent_type);
    var parent_container = $(element).parents('.mds-titles-block');
    var headings_container = $(parent_container).find('tr:first');

    /* Fetch the order of item to be removed */
    var parent_id = $(parent).attr('id');
    var order = parseInt($('#' + parent_id + ' td input[name="mds_' + parent_type + '[order][]"]').val());

    /* Remove the block */
    $(parent).remove();

    /* Reduce the order of further elements */
    $('.mds-more-' + parent_type + ' td input[name="mds_' + parent_type + '[order][]"]').each(function (index, element) {
        var element_order = parseInt($(element).val());

        /* Reduce the order of succeeding items (make sure that the removed item order is > 0) */
        if (order > 0 && element_order > order) {

            $(element).val(element_order - 1);
        }
    });

    /* Reduce the counter value */
    var counter = $('#mds_' + parent_type + '_count');
    if (parent_type == 'row') {
        mds_row_count--;
        $(counter).val(mds_row_count);
    } else {
        mds_column_count--;
        $(counter).val(mds_column_count);
    }

    /* Hide headings if there is no block */
    if (!$(counter).val()) {
        $(headings_container).hide();
    }
}

/**
 * Add more row/column blocks
 * 
 * @param {Object} event
 * @returns {undefined}
 */
function mds_add_block(event) {
    var element = event.target;
    var parent_type = $(element).attr('parent_type');
    var parent_container = $(element).parents('.mds-titles-block').find('table:first');
    var headings_container = $(parent_container).find('tr:first');
    var counter = $('#mds_' + parent_type + '_count');
    var count = 0;

    if (parent_type == 'row') {
        mds_row_count++;
        count = mds_row_count;
    } else {
        mds_column_count++;
        count = mds_column_count;
    }
    $(counter).val(count);

    var post_data = {
        action: 'title_block',
        mds_block_type: parent_type,
        mds_block_id: count
    };

    $.ajax({
        method: "POST", url: MCJS.ajaxUrl,
        data: post_data
    }).done(function (response) {
        /* Add the new block */
        $(parent_container).append(response);

        /* Show headings if there is any block */
        if ($(counter).val()) {
            $(headings_container).show();
        }
    });
}