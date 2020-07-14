$(() => {
    /**
     * search button
     */
    $('#button-filter').on('click', function () {
        let url = '';
        if (sort) url += '&sort=' + sort;

        $.each($('.filter_content input'), function () {
            let filter = $(this).val();
            let filter_name = $(this).attr('name');
            if (filter) url += '&' + filter_name + '=' + encodeURIComponent(filter);
        })

        $.each($('.filter_content select'), function () {
            let filter = $(this).val();
            let filter_name = $(this).attr('name');
            if (filter) url += '&' + filter_name + '=' + encodeURIComponent(filter);
        })

        window.location = data_dealbao + '&user_token=' + user_token + '&group_id=' + group_id + '&level=' + level + url;
    });

    /**
     * download data to website
     */
    $("#download").on('click', () => {
        window.location = goods_download;
    });

    /**
     * delete collect
     */
    $('.delete-collect').on('click', function () {
        let spu = $(this).attr('data-spu');
        $.ajax({
            url: collect_delete,
            data: {spu: spu},
            dataType: 'json',
            type: 'post',
            success: (data) => {
                layer.msg(data.msg);
            },
            error: () => {
                layer.msg(ajax_error);
            },
        })
    })

    /**
     * zTree config
     */
    let setting = {
        callback: {
            onClick: (event, treeId, treeNode) => {
                let group_id = treeNode.group_id;
                let level = treeNode.level + 1;
                window.location = base_url + '&page=1&limit=' + limit + '&group_id=' + group_id + '&level=' + level;
            }
        },
        data: {
            key: {
                name: 'group_name',
                id: 'group_id',
            }
        }
    };

    /**
     * load zTree document
     */
    let zTreeObj = $.fn.zTree.init($("#cateTree"), setting, treeData);
    if (cate_id !== '') {
        let node = zTreeObj.getNodeByParam('group_id', cate_id);
        let expandNode = node;
        if (node.children === undefined || node.children === null)
            expandNode = node.getParentNode();
        zTreeObj.expandNode(expandNode);
        zTreeObj.selectNode(node);
    }


    $('#category_bind').on('click', () => {
        window.location = `${categoryBind}&spu=`;
    })
})