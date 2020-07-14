<?php


class ControllerExtensionDealbaoGoodsList extends Controller
{
    /**
     * goods list view
     */
    public function index()
    {
        $this->language->load('extension/dealbao/goods_list');
        $this->loadPublicData();
        $this->getClient();
        //origin data
        $cate_list = $this->getCateList();
        !isset($cate_list['error_warning']) ? $data['cate_list'] = $cate_list['cate_list'] : $data['error_warning'] = $cate_list['error_warning'];
        $goods_list = $this->getGoodsList();
        !isset($goods_list['error_warning']) ? $data['goods_list'] = $goods_list['goods_list'] : $data['error_warning'] = $goods_list['error_warning'];
        //data error
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
            'href' => $this->url->link('extension/dealbao/goods_list', 'user_token=' . $this->session->data['user_token'])
        );

        $data['breadcrumbs'] = $this->model_extension_dealbao_common->editBreadCrumbs($this, $breadcrumbs);
        $data = $this->model_extension_dealbao_common->getCommonData($this, $data);
        $data['data_dealbao'] = HTTP_SERVER . 'index.php?route=extension/dealbao/goods_list';
        $data['goods_download'] = HTTP_SERVER . 'index.php?route=extension/dealbao/goods_list/download&user_token=' . $data['user_token'];
        $data['now_url'] = $data['data_dealbao'] . '&user_token=' . $data['user_token'] . $url;
        $data['edit_link'] = $this->url->link('extension/dealbao/goods_list/edit', 'user_token=' . $this->session->data['user_token']);
        foreach ($this->request->get as $key => $value) in_array($key, $url_break) && $url .= '&' . $key . '=' . $value;
        $data['pagination'] = $this->model_extension_dealbao_common->pagination($this, $data['goods_list']['total'] ?? 0, $url);
        $data['results'] = $this->model_extension_dealbao_common->limitOption($this, $data['goods_list']['total'] ?? 0);
        $this->response->setOutput($this->load->view('extension/dealbao/goods_list', $data));
    }

    /**
     * goods detail view
     */
    public function edit()
    {
        $this->language->load('extension/dealbao/goods_list/edit');
        $this->loadPublicData();
        $this->getClient();
        $data = $this->getGoodsInfo();
        $breadcrumbs[] = array(
            'text' => $this->language->get('list_title'),
            'href' => $this->url->link('extension/dealbao/goods_list', 'user_token=' . $this->session->data['user_token'])
        );
        $breadcrumbs[] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/dealbao/goods_list/edit', 'user_token=' . $this->session->data['user_token'])
        );
        $data['breadcrumbs'] = $this->model_extension_dealbao_common->editBreadCrumbs($this, $breadcrumbs);
        $data = $this->model_extension_dealbao_common->getCommonData($this, $data);

        $this->response->setOutput($this->load->view('extension/dealbao/goods_list/edit', $data));
    }

    /**
     * goods download view
     * @throws Exception
     */
    public function download()
    {
        $data = [];
        $this->load->language('extension/dealbao/goods_list/download');
        $this->load->model('extension/dealbao/goods');
        $this->loadPublicData();
        $this->getClient();
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->saveGoodsInfo();
            return;
        }
        $data = array_merge($this->getGoodsInfo(), $data);
        $data['goods_list_json']
            = json_encode($data['goods_list']);
        $breadcrumbs[] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/dealbao/goods_list', 'user_token=' . $this->session->data['user_token'])
        );
        $breadcrumbs[] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/dealbao/goods_list/download', 'user_token=' . $this->session->data['user_token'])
        );
        $data['breadcrumbs'] = $this->model_extension_dealbao_common->editBreadCrumbs($this, $breadcrumbs);
        $data = $this->model_extension_dealbao_common->getCommonData($this, $data);
        $this->response->setOutput($this->load->view('extension/dealbao/goods_list/download', $data));
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
     * save goods
     * @throws Exception
     */
    private function saveGoodsInfo()
    {
        $this->load->model('extension/dealbao/lang');
        $request = $this->request->post;
        $request['goods_info'] = json_decode(htmlspecialchars_decode($request['goods_info']), true);
        $websiteLang = $this->model_extension_dealbao_lang->getWebsiteLang();
        $result = $this->model_extension_dealbao_goods->saveGoods($request, $websiteLang);
        $this->model_extension_dealbao_common->buildResponse($result, $this->language);
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
     * loadPublicData
     */
    private function loadPublicData()
    {
        $this->load->model('extension/dealbao/common');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addScript('view/javascript/dealbao/layer/layer.js');
        $this->document->addScript('view/javascript/dealbao/list_common.js');
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

    /**
     * get goods category
     * @return array
     */
    private function getCateList()
    {
        $lang_map = $this->config->get('dealbao_lang_map');
        $param = ['language_id' => $lang_map['to']['default']];
        $cate = $this->model_extension_dealbao_sdk_client->getSdkCallback('Cate/getGoodsCategory', $param);
        return $cate['code'] != 200 ? ['error_warning' => $cate['msg']] : ['cate_list' => json_encode($cate['data'])];
    }

    /**
     * get goods list
     * @param $cate_list
     * @return array
     */
    private function getGoodsList()
    {

        $lang_map = $this->config->get('dealbao_lang_map');
        $search_data = $this->request->get;
        $data = [
            'max_price'   => empty($search_data['filter_max_price']) ? '' : $search_data['filter_max_price'],
            'min_price'   => empty($search_data['filter_min_price']) ? '' : $search_data['filter_min_price'],
            'goods_name'  => empty($search_data['filter_keyword']) ? '' : $search_data['filter_keyword'],
            'order'       => empty($search_data['sort_order']) ? '' : $search_data['sort_order'],
            'level'       => empty($search_data['level']) ? 1 : $search_data['level'] - 1,
            'gc_id'       => empty($search_data['gcId']) ? '' : $search_data['gcId'],
            'page'        => empty($search_data['page']) ? 1 : $search_data['page'],
            'size'        => empty($search_data['limit']) ? 10 : $search_data['limit'],
            'sort'        => empty($search_data['sort']) ? '' : $search_data['sort'],
            'language_id' => $lang_map['to']['default'],
        ];
        $list = $this->model_extension_dealbao_sdk_client->getSdkCallback('Goods/getGoodsListByCateGroup', $data);
        return $list['code'] != 200 ? ['error_warning' => $list['msg']] : ['goods_list' => $list];
    }
}