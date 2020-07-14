$(() => {
    let is_start = false;
    let bach_length = 0;
    let now = -1;
    let total = 0;
    let inc = 0;
    let download_id = 0;
    let group_ids = [];
    let page = 0;
    let size = 5;
    /**
     * setting
     * @type {{data: {key: {name: string}}, callback: {onCheck: onCheck}, check: {enable: boolean}}}
     */
    let setting = {
        callback: {
            onCheck: (event, treeId, treeNode) => {
                // let is_check = treeNode.checked;
            }
        },
        check: {
            enable: true,
        },
        data: {key: {name: 'group_name'}}
    };
    let treeObj = $.fn.zTree.init($("#cate_list"), setting, cate_list);

    /**
     * click confirm button
     */
    $("#confirm").on('click', e => {
        if (is_start) return false;
        let all = treeObj.getCheckedNodes();
        all.forEach(v => {
            let html =
                '<div class="form-group clearfix batch_download" data-id="' + v.group_id + '">' +
                '<label class="col-sm-2 control-label">' + v.group_name + '</label>' +
                '<div class="col-sm-10">' +
                '<div class="progress">' +
                '<div class="progress-bar" style="width: 0;"></div>' +
                '</div>' +
                '<div class="progress-text">wait...</div>' +
                '</div>' +
                '</div>';
            $('form').prepend(html);
            group_ids.push(v.group_id);
        })


    })

    /**
     * click start button
     * @type {*|jQuery.fn.init|jQuery|HTMLElement}
     */
    let batch_document = $('.batch_download');
    $("#start_download").on('click', e => {
        if (is_start) return false;
        if (batch_document.length === 0) return false;
        bach_length = batch_document.length;
        is_start = true;
        step0();
    })


    function step0() {
        group_ids = [6, 7, 8];
        let data = {step: 0, group_id: group_ids};
        step_ajax(data,
            data => {
                if (data.code === 200) {
                    download_id = data.data;
                    step1();
                } else {
                    layer.msg(data.msg)
                    is_start = false
                }
            },
            () => {
                layer.msg(ajax_error)
                is_start = false;
            })
    }

    /**
     * step1
     * @returns {boolean}
     */
    function step1() {
        now++;
        page = 0;
        if (now >= bach_length) return false;
        let id = batch_document[now].getAttribute('data-id');
        let progress_bar = batch_document[now].getElementsByClassName('progress-bar')[0];
        let progress_text = batch_document[now].getElementsByClassName('progress-text')[0];
        progress_text.innerHTML = calculate_the_total_text;

        let data = {step: 1, id: id, download_id: download_id, category_id: bind_relate[id]};
        step_ajax(data,
            data => {
                if (data.code === 200) {
                    total = data.data.total;
                    inc = data.data.inc;
                    progress_text.innerHTML = `${inc} / ${total}`;
                    progress_bar.style.cssText = 'width:' + (inc / total) * 100 + '%';
                    step2();
                } else {
                    now--;
                }
            },
            () => {
                layer.msg(download_error_text);
                now--;
            });
    }

    /**
     * step2
     */
    function step2() {
        inc = 0;
        page++;
        let id = batch_document[now].getAttribute('data-id');
        let data = {download_id: download_id, step: 2, page: page, size: size, id: id};
        step_ajax(data,
            data => {
                if (data.code === 200) {
                    step3();
                } else {
                    now--;
                }
            },
            () => {
                layer.msg(download_error_text);
                now--;
            });
    }

    /**
     * step3
     */
    function step3() {
        if (((page - 1) * size + inc) / total >= 1) {
            step1()
            return;
        }

        let id = batch_document[now].getAttribute('data-id');
        let progress_bar = batch_document[now].getElementsByClassName('progress-bar')[0];
        let progress_text = batch_document[now].getElementsByClassName('progress-text')[0];
        progress_text.innerHTML = `${(page - 1) * size + inc + 1} / ${total}`;
        progress_bar.setAttribute('class', 'progress-bar progress-bar-warning');
        progress_bar.style.cssText = 'width:' + (((page - 1) * size + inc + 1) / total) * 100 + '%';

        let data = {
            download_id: download_id,
            group_id: id,
            step: 3,
            inc: inc,
            page: page,
            size: size,
        };
        step_ajax(data,
            data => {
                if (data.code === 200) {
                    inc++;
                    progress_bar.setAttribute('class', 'progress-bar progress-bar-success');
                    progress_bar.style.cssText = 'width:' + (((page - 1) * size + inc) / total) * 100 + '%';
                    if (inc + 1 > size) {
                        step2();
                        return;
                    }
                    setTimeout(step3, 1000);
                } else {
                    now--;
                    inc--;
                }
            },
            () => {
                layer.msg(download_error_text);
                now--;
            });
    }

    /**
     * @param data
     * @param success
     * @param error
     */
    function step_ajax(data, success, error) {
        $.ajax({
            url: download_step,
            timeout: 0,
            data: data,
            type: 'post',
            dataType: 'json',
            success: success,
            error: error,
        })
    }
})