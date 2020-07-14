<?php


class ControllerExtensionDealbaoCollectList extends Controller
{
    /**
     * goods list view
     */
    public function index()
    {
        $this->language->load('extension/dealbao/collect_list');
        $this->load->model('extension/dealbao/common');
        $this->setDocument();
        $data = $this->getClient();
        $this->document->addScript('view/javascript/dealbao/zTree_v3/js/jquery.ztree.all.min.js');
        $this->document->addStyle('view/javascript/dealbao/zTree_v3/css/zTreeStyle/zTreeStyle.css');
        $this->document->addScript('view/javascript/dealbao/collect_list/js/collect_list.js?' . time());
        //origin data
        $cate_list = $this->getCollectGroup();
        !isset($goods_list['error_warning']) && $data['cate_list'] = $cate_list['cate_list'];
        $goods_list = $this->getCollectGoodsList();
        !isset($goods_list['error_warning']) && $data['goods_list'] = $goods_list['goods_list'];
        //data error
        !isset($data['error_warning']) && isset($goods_list['error_warning']) && $data['error_warning'] = $goods_list['error_warning'];
        !isset($data['error_warning']) && isset($cate_list['error_warning']) && $data['error_warning'] = $cate_list['error_warning'];

        $data['sort'] = !empty($this->request->get['sort']) ? $this->request->get['sort'] : '';
        $break = ['route', 'user_token'];
        $url_break = ['sort', 'gcId', 'sort_order', 'level'];
        $page_arr = ['page'];
        $url = '';
        foreach ($this->request->get as $key => $value) {
            if (in_array($key, $break)) continue;
            $data[$key] = $value;
            if (!in_array($key, $url_break) && !in_array($key, $page_arr)) $url .= '&' . $key . '=' . $value;
        }
        if (!empty($this->request->get['sort_order']) && $this->request->get['sort_order'] != 'asc' && !empty($this->request->get['sort']))
            $url .= '&sort_order=desc';

        $breadcrumbs[] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/dealbao/collect_list', 'user_token=' . $this->session->data['user_token'])
        );

        $data['breadcrumbs'] = $this->model_extension_dealbao_common->editBreadCrumbs($this, $breadcrumbs);
        $data = $this->model_extension_dealbao_common->getCommonData($this, $data);
        $data['data_dealbao'] = HTTP_SERVER . 'index.php?route=extension/dealbao/collect_list';
        $data['collect_delete'] = $this->url->link('extension/dealbao/collect_list/delete&user_token=' . $this->session->data['user_token']);
        $data['category_bind'] = $this->url->link('extension/dealbao/collect_list/category_bind&user_token=' . $this->session->data['user_token']);
        $data['now_url'] = $data['data_dealbao'] . '&user_token=' . $data['user_token'] . $url;
        $data['edit_link'] = $this->url->link('extension/dealbao/collect_list/edit', 'user_token=' . $this->session->data['user_token']);
        $data['goods_download'] = $this->url->link('extension/dealbao/collect_list/download&user_token=' . $this->session->data['user_token']);
        foreach ($this->request->get as $key => $value) in_array($key, $url_break) && $url .= '&' . $key . '=' . $value;
        $data['pagination'] = $this->model_extension_dealbao_common->pagination($this, $data['goods_list']['total'] ?? 0, $url, 'collect_list');
        $data['results'] = $this->model_extension_dealbao_common->limitOption($this, $data['goods_list']['total'] ?? 0);
        $this->response->setOutput($this->load->view('extension/dealbao/collect_list', $data));
    }

    /**
     * bind collect group to local category
     */
    public function category_bind()
    {
        $this->load->language('extension/dealbao/collect_list/category_bind');
        $this->load->model('extension/dealbao/common');
        $this->setDocument();
        $data = $this->getClient();
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->model('setting/setting');
            $post = $this->request->post;
            $config = $post['category_bind'];
            $this->model_setting_setting->editSetting('dealbao_category_bind', ['dealbao_category_bind' => $config]);
            $this->model_extension_dealbao_common->buildResponse(true, $this->language);
            return;
        }
        $data = array_merge($this->getCollectGroup(), $data);
        $data = array_merge($this->getLocalCategory(), $data);

        $this->document->addScript('view/javascript/dealbao/zTree_v3/js/jquery.ztree.all.min.js');
        $this->document->addStyle('view/javascript/dealbao/zTree_v3/css/zTreeStyle/zTreeStyle.css');
        $this->document->addScript('view/javascript/dealbao/collect_list/js/category_bind.js?' . time());
        if (isset($this->error['warning'])) $data['error_warning'] = $this->error['warning'];

        $breadcrumbs = [];
        $breadcrumbs[] = [
            'text' => $this->language->get('list_title'),
            'href' => $this->url->link('extension/dealbao/collect_list', 'user_token=' . $this->session->data['user_token'])
        ];
        $breadcrumbs[] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/dealbao/collect_list/category_bind', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'] = $this->model_extension_dealbao_common->editBreadCrumbs($this, $breadcrumbs);
        $data['user_token'] = $this->session->data['user_token'];
        $data['data_dealbao'] = HTTP_SERVER . 'index.php?route=extension/dealbao/collect_list/category_bind';
        $category_bind = $this->config->get('dealbao_category_bind');
        if ($category_bind) $data['category_bind'] = json_encode($category_bind);
        $data = $this->model_extension_dealbao_common->getCommonData($this, $data);
        $this->response->setOutput($this->load->view('extension/dealbao/collect_list/category_bind', $data));
    }

    /**
     * delete collect goods
     */
    public function delete()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->model('extension/dealbao/common');
            $this->getClient();
            $lang_map = $this->config->get('dealbao_lang_map');
            $post_data = $this->request->post;
            $param = ['language_id' => $lang_map['to']['default'], 'spu' => $post_data['spu']];
            $res = $this->model_extension_dealbao_sdk_client->getSdkCallback('Collect/deleteCollectGoodsBySpu', $param);
            echo json_encode($res);
        }
    }

    /**
     * download goods
     * @return mixed
     */
    public function download()
    {
        $this->language->load('extension/dealbao/collect_list/download');
        $this->load->model('extension/dealbao/common');
        $this->setDocument();
        $data = $this->getClient();
        $this->document->addScript('view/javascript/dealbao/zTree_v3/js/jquery.ztree.all.min.js');
        $this->document->addStyle('view/javascript/dealbao/zTree_v3/css/zTreeStyle/zTreeStyle.css');
        $this->document->addScript('view/javascript/dealbao/collect_list/js/download.js?' . time());
        $data = array_merge($this->getCollectGroup(), $data);
        $data['bind_relate'] = json_encode((object)$this->config->get('dealbao_category_bind'));
        $breadcrumbs[] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/dealbao/collect_list', 'user_token=' . $this->session->data['user_token'])
        );
        $data['download_step'] = $this->url->link('extension/dealbao/collect_list/download_step&user_token=' . $this->session->data['user_token']);
        $data['breadcrumbs'] = $this->model_extension_dealbao_common->editBreadCrumbs($this, $breadcrumbs);
        $data = $this->model_extension_dealbao_common->getCommonData($this, $data);
        return $this->response->setOutput($this->load->view('extension/dealbao/collect_list/download', $data));
    }

    /**
     * download start
     */
    public function download_step()
    {
        set_time_limit(0);
        $this->load->model('extension/dealbao/common');
        $this->language->load('extension/dealbao/common/ajax');
        $this->getClient();
        $post = $this->request->post;
        switch ($post['step']) {
            case 0:
                $this->step0($post);
                break;
            case 1:
                $this->step1($post);
                break;
            case 2:
                $this->step2($post);
                break;
            case 3:
                $this->step3($post);
                break;
        }
        return;
    }

    /**
     * @param $post
     * @return bool
     */
    private function step0($post)
    {
        $this->load->model('extension/dealbao/goods/batch_download_goods');
        $data = [
            'bind_group_id' => implode(',', $post['group_id']),
            'create_time'   => time(),
            'update_time'   => time(),
        ];
        $result = $this->model_extension_dealbao_goods_batch_download_goods->saveDownloadHistory($data);
        $this->model_extension_dealbao_common->buildResponse($result, $this->language);
        return true;
    }

    /**
     * download step1
     * @param $post
     * @return bool
     */
    private function step1($post)
    {
        $this->load->model('extension/dealbao/goods/batch_download_goods');
        $data = ['download_id' => $post['download_id'], 'group_id' => $post['id'], 'category_id' => is_array($post['category_id']) ? implode(',', $post['category_id']) : $post['category_id']];
        $connect_info = $this->model_extension_dealbao_goods_batch_download_goods->getDownloadGroup($data);
        $param = ['group_id' => $post['id']];
        $result = $this->model_extension_dealbao_sdk_client->getSdkCallback('Collect/getCollectCountByGroup', $param);
        !empty($result['data']) && $result['data'] = ['total' => $result['data'], 'inc' => $connect_info['inc']];
        $this->model_extension_dealbao_common->buildResponse($result, $this->language);
        return true;
    }

    /**
     * download step2
     * @param $post
     * @return bool
     */
    private function step2($post)
    {
        $this->load->model('extension/dealbao/goods/batch_download_goods');
        $param = [
            'group_id' => $post['id'],
            'page'     => $post['page'],
            'size'     => $post['size'],
        ];
        $result = $this->model_extension_dealbao_sdk_client->getSdkCallback('Collect/downloadGoodsList', $param);
        if (!empty($result['code']) && $result['code'] != 200) {
            $this->model_extension_dealbao_common->buildResponse($result, $this->language);
            return true;
        }
        $real = $this->mergeSpu($result['data']);
        $real = $this->mergeSku($real);
        $download_info = ['download_id' => $post['download_id']];
        $res = $this->model_extension_dealbao_goods_batch_download_goods->saveToDataBase($real, $download_info);
        $this->model_extension_dealbao_common->buildResponse($res, $this->language);
        return true;
    }

    /**
     * download step3
     * @param $post
     * @return bool
     */
    private function step3($post)
    {
        $this->load->model('extension/dealbao/goods/batch_download_goods');
        $param = ['limit' => ($post['page'] - 1) * $post['size'] + $post['inc'], 'download_id' => $post['download_id']];
        $goods_info = $this->model_extension_dealbao_goods_batch_download_goods->getGoodsInfo($param);
        $param = ['download_id' => $post['download_id'], 'group_id' => $post['group_id']];
        $download_info = $this->model_extension_dealbao_goods_batch_download_goods->getDownloadGroup($param);

        $decode_keys = ['sku_data', 'images_more', 'goods_body'];
        foreach ($decode_keys as $key) $goods_info[$key] = unserialize($goods_info[$key]);
        if (!empty($goods_info['code'])) {
            $this->model_extension_dealbao_common->buildResponse($goods_info, $this->language);
            return true;
        }
        $goods_info['cate_list'] = explode(',', $download_info['category_id']);
        $result = $this->model_extension_dealbao_goods_batch_download_goods->saveGoodsToProduct($goods_info);
        $this->model_extension_dealbao_common->buildResponse($result, $this->language);
        return true;
    }

    /**
     * merge spu language
     * @param $data
     * @return array
     */
    private function mergeSpu($data)
    {
        $goods_list = [];
        foreach ($data as $goods) $goods_list[$goods['spu']][$goods['language_id']] = $goods;
        $real = [];
        foreach ($goods_list as $key => $spu) {
            $goods_info = [];
            $goods_body = [];
            $real_sku = [];
            foreach ($spu as $id => $lang) {
                empty($goods_info) && $goods_info = $lang;
                $goods_body[$id] = $lang['goods_body'];
                $sku_data = [];
                foreach ($lang['sku_data'] as $goods) $sku_data[] = $goods['_source'];
                $real_sku = array_merge($real_sku, $sku_data);
            }
            $goods_info['goods_body'] = $goods_body;
            $goods_info['sku_data'] = $real_sku;
            $real[] = $goods_info;
            unset($goods_list[$key]);
        }
        return $real;
    }

    /**
     * merge sku language
     * @param $list
     * @return mixed
     */
    private function mergeSku($list)
    {
        foreach ($list as &$goods_info) {
            $lang_arr = [];
            $sku_data = [];
            foreach ($goods_info['sku_data'] as $goods) {
                $goods['goods_spec'] = unserialize($goods['goods_spec']);
                $goods['spec_name'] = unserialize($goods['spec_name']);
                empty($sku_data[$goods['goods_sku']]) && $sku_data[$goods['goods_sku']] = $goods;
                $lang_arr[$goods['goods_sku']]['goods_name'][$goods['language_id']] = $goods['goods_name'];
                $lang_arr[$goods['goods_sku']]['goods_spec'][$goods['language_id']] = $goods['goods_spec'];
                $lang_arr[$goods['goods_sku']]['spec_name'][$goods['language_id']] = $goods['spec_name'];
            }

            foreach ($sku_data as $key => &$value) {
                $value['goods_name'] = $lang_arr[$key]['goods_name'];
                $value['goods_spec'] = $lang_arr[$key]['goods_spec'];
                $value['spec_name'] = $lang_arr[$key]['spec_name'];
            }
            $goods_info['sku_data'] = array_values($sku_data);
        }
        return $list;
    }

    /**
     * get local category
     */
    private function getLocalCategory()
    {
        $results = $this->model_extension_dealbao_common->getCategories();
        $data = $this->model_extension_dealbao_common->getTreeData($results, 0, 'parent_id', 'category_id');
        return ['cate_list_local' => json_encode((array)$data)];
    }

    /**
     * get sdk client
     * @return array
     */
    private function getClient()
    {
        $this->load->model('extension/dealbao/token');
        $this->load->model('extension/dealbao/sdk_client');
        $data = [];
        $res = $this->model_extension_dealbao_token->getAccessToken();
        if ($res == false) $data['error_warning'] = $this->language->get('error_app_info');
        $this->model_extension_dealbao_sdk_client->getClient();
        return $data;
    }

    /**
     * set public document
     */
    private function setDocument()
    {
        $this->load->model('extension/dealbao/common');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addScript('view/javascript/dealbao/layer/layer.js');
        $this->document->addScript('view/javascript/dealbao/list_common.js');
    }

    /**
     * permission
     * @return bool
     */
    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/dealbao/goods_list'))
            $this->error['warning'] = $this->language->get('error_permission');

        return !$this->error;
    }

    /**
     * get collect group list
     * @return array
     */
    private function getCollectGroup()
    {
        $lang_map = $this->config->get('dealbao_lang_map');
        $param = ['language_id' => $lang_map['to']['default']];
        $cate = $this->model_extension_dealbao_sdk_client->getSdkCallback('Collect/getCollectGroup', $param);
        return $cate['code'] != 200 ? ['error_warning' => $cate['message']] : ['cate_list' => json_encode($cate['data'])];
    }

    /**
     * get collect goods list
     * @return array
     */
    private function getCollectGoodsList()
    {
        $lang_map = $this->config->get('dealbao_lang_map');
        $search_data = $this->request->get;
        $param = [
            'page'        => empty($search_data['page']) ? 1 : $search_data['page'],
            'size'        => empty($search_data['limit']) ? 10 : $search_data['limit'],
            'language_id' => $lang_map['to']['default']
        ];
        !empty($search_data['group_id']) && $param['group_id'] = $search_data['group_id'];
        $info = $this->model_extension_dealbao_sdk_client->getSdkCallback('Collect/getCollectGoodsByGroup', $param);
        return $info['code'] != 200 ? ['error_warning' => $info['msg']] : ['goods_list' => $info];
    }

    /**
     * get goods info
     * @return array
     */
    private function getGoodsInfo()
    {
        $search_data = $this->request->get;
        $info = $this->model_extension_dealbao_sdk_client->getSdkCallback('Goods/getGoodsBySpu', ['spu' => $search_data['spu']]);
        $data = $info['data'];
        $goods_list = [];
        foreach ($data as $goods) $goods_list[$goods['spu']][$goods['language_id']] = $goods;
        $real = [];
        foreach ($goods_list as $key => $spu) {
            $goods_info = [];
            $goods_body = [];
            $real_sku = [];
            foreach ($spu as $id => $lang) {
                empty($goods_info) && $goods_info = $lang;
                $goods_body[$id] = $lang['goods_body'];
                $sku_data = [];
                foreach ($lang['sku_data'] as $goods) {
                    $sku_data[] = $goods['_source'];
                }
                $real_sku = array_merge($real_sku, $sku_data);
            }
            $goods_info['goods_body'] = $goods_body;
            $goods_info['sku_data'] = $real_sku;
            $real[] = $goods_info;
            unset($goods_list[$key]);
        }
        return $info['code'] != 200 ? ['error_warning' => $info['msg']] : ['goods_list' => $real];
    }
}