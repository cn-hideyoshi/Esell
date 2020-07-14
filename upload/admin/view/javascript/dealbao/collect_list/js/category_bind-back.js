$(() => {
    $("#addGroup").on('click', () => {
        let group = '<div class="form-group">\n' +
            '  <label for="" class="col-sm-2 control-label"><select class="form-control">' + category_option + '</select></label>\n' +
            '  <div class="col-sm-10">\n' +
            '  <div class="col-sm-10">' +
            '    <input type="text" name="category" value="" placeholder="商品分类"\n' +
            '       class="form-control" autocomplete="off">\n' +
            // '  <ul class="dropdown-menu"></ul>\n' +
            '     <div id="product-category" class="well well-sm"\n' +
            '        style="min-height: 150px; overflow: auto;"></div>' +
            '</div>' +
            '  <div class="col-sm-2"><button class="btn btn-primary"><i class="fa fa-minus-circle"></i></button></div>' +
            '</div>\n' +
            '</div>';
        $("#addGroup").parents('.form-group').before(group);
        let category_list = $('#form-feed').find('.form-group').find('input[name="category"]');
        category_list.last().autocomplete({
            'source': function (request, response) {
                $.ajax({
                    url: 'index.php?route=catalog/category/autocomplete&user_token=' + user_token + '&filter_name=' + encodeURIComponent(request),
                    dataType: 'json',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return {
                                label: item['name'],
                                value: item['category_id']
                            }
                        }));
                    }
                });
            },
            'select': function (item) {
                $('input[name=\'category\']').val('');

                $('#product-category' + item['value']).remove();

                $('#product-category').append('<div id="product-category' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="product_category[]" value="' + item['value'] + '" /></div>');
            }
        });
    })
})