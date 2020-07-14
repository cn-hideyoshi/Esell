$(() => {
    /**
     * refresh tree
     */
    $('#refreshTreeData').on('click', () => {
        let treeNode = originTree.getSelectedNodes();
        if (treeNode.length === 0) return;

        let list = getCateBindInfo(treeNode[0].group_id + '');
        let nodes = localTree.getSelectedNodes();
        if (nodes.length > 0) for (let node in nodes) localTree.cancelSelectedNode(nodes[node]);

        for (let key in list) {
            if (!key) continue;
            let node = localTree.getNodeByParam('category_id', list[key]);
            localTree.selectNode(node, true);
        }
    })

    /**
     * save tree
     */
    $("#saveTreeData").on('click', () => {
        let ids = [];
        let treeNode = originTree.getSelectedNodes();
        if (treeNode.length === 0) return;

        let nodes = localTree.getSelectedNodes();
        if (nodes.length > 0) {
            for (let node in nodes) {
                let cate_id = nodes[node]['category_id'] - 0;
                ids.push(cate_id)
            }
        }

        category_bind[treeNode[0].group_id] = ids;
    })

    /**
     * submit
     */
    $("#submit").on('click', () => {
        $.ajax({
            url: '',
            data: {category_bind: category_bind},
            type: 'post',
            dataType: 'json',
            success: data => {
                layer.msg(data.message);
            },
            error: () => {
                layer.msg(ajax_error);
            }
        })
    })

    /**
     * origin setting
     */
    let originSetting = {
        callback: {
            onClick: (event, treeId, treeNode) => {
                let list = getCateBindInfo(treeNode.group_id + '');
                let nodes = localTree.getSelectedNodes();
                localTree.expandAll(false);
                if (nodes.length > 0) for (let node in nodes) localTree.cancelSelectedNode(nodes[node]);

                for (let key in list) {
                    if (!key) continue;
                    let node = localTree.getNodeByParam('category_id', list[key]);
                    localTree.selectNode(node, true);
                }
            }
        },
        data: {key: {name: 'group_name'}}
    };

    /**
     * local setting
     */
    let localSetting = {
        view: {
            selectedMulti: true,
        },
        callback: {
            beforeClick: (treeId, treeNode, clickFlag) => {
                if (!$('#' + treeNode.tId + '_a').hasClass('curSelectedNode')) {
                    localTree.selectNode(treeNode, true);
                } else {
                    localTree.cancelSelectedNode(treeNode);
                }
                return false;
            },
        },
    }

    /**
     * load zTree document
     */
    let originTree = $.fn.zTree.init($("#originTree"), originSetting, origin);
    let localTree = $.fn.zTree.init($("#localTree"), localSetting, local);
})

function getCateBindInfo(group_id) {
    try {
        if (category_bind[group_id] === undefined)
            return false;
        return category_bind[group_id];
    } catch (e) {
        return false;
    }
}
