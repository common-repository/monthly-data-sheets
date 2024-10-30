/* 
 Created on : Dec 03, 2015
 Author     : RSK
 */


var $ = jQuery.noConflict();

$(document).ready(function () {

    /* Load sheet on changing month */
    $(document).on("change", '#mds_month', load_sheet);

    /* Load sheet on changing year */
    $(document).on("change", '#mds_year', load_sheet);

});

/**
 * Loading corresponding sheet on changing year or month
 * @returns {Boolean}
 */
function load_sheet() {
    var month = $('#mds_month').val();
    var year = $('#mds_year').val();
    var url = $('#mds_url').val();
    window.location.href = url + '?mds_month=' + month + '&mds_year=' + year;
}


