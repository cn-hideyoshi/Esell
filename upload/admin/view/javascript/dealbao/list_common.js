/**
 * page button
 */
$('.page_results select').on('change', function () {
    let value = $(this).children('option:selected').val();

    let location_url = base_url + '&page=1&limit=' + value;
    if (cate_id !== '') location_url += '&group_id=' + cate_id + '&level=' + level;
    window.location = location_url;
});
$('.page_results input').on('change', function () {
    let value = $(this).children('option:selected').val();
    window.location = base_url + '&page=1&limit=' + value;
})