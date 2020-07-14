<?php


class ControllerExtensionDealbaoOrderList extends Controller
{
    /**
     * 列表
     * @throws Exception
     */
    public function index()
    {
        $this->load->model('extension/dealbao/common');
        $this->language->load('extension/dealbao/order_list');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->getClient();
        $data      = $this->getOrderList();
        $break     = ['route', 'user_token'];
        $url_break = ['sort', 'page'];
        $url       = '';
        foreach ($this->request->get as $key => $value) {
            if (in_array($key, $break)) continue;
            $data[$key] = $value;
            if (!in_array($key, $url_break)) $url .= '&' . $key . '=' . $value;
        }
        $breadcrumbs[]       = array(
            'text' => $this->language->get('list_title'),
            'href' => $this->url->link('extension/dealbao/order_list', 'user_token=' . $this->session->data['user_token'])
        );
        $data['breadcrumbs'] = $this->model_extension_dealbao_common->editBreadCrumbs($this, $breadcrumbs);

        $data = $this->model_extension_dealbao_common->getCommonData($this, $data);

        $data['pagination']   = $this->model_extension_dealbao_common->pagination($this, $data['order_list']['total'] ?? 0, $url, 'order_list');
        $data['results']      = $this->model_extension_dealbao_common->limitOption($this, $data['order_list']['total'] ?? 0);
        $data['data_dealbao'] = HTTP_SERVER . 'index.php?route=extension/dealbao/order_list';
        $data['edit_link']    = $this->url->link('extension/dealbao/order_list/edit', 'user_token=' . $this->session->data['user_token']);
        $this->response->setOutput($this->load->view('extension/dealbao/order_list', $data));
    }

    /**
     * 订单详情
     */
    public function edit()
    {
        $this->language->load('extension/dealbao/order_list/edit');
        $this->load->model('extension/dealbao/common');
        $this->getClient();
        $data                = $this->getOrderInfo();
        $breadcrumbs[]       = array(
            'text' => $this->language->get('list_title'),
            'href' => $this->url->link('extension/dealbao/order_list', 'user_token=' . $this->session->data['user_token'])
        );
        $breadcrumbs[]       = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/dealbao/order_list/edit', 'user_token=' . $this->session->data['user_token'])
        );
        $data['breadcrumbs'] = $this->model_extension_dealbao_common->editBreadCrumbs($this, $breadcrumbs);
        $data                = $this->model_extension_dealbao_common->getCommonData($this, $data);

        $this->response->setOutput($this->load->view('extension/dealbao/order_list/edit', $data));
    }

    /**
     * 获取订单详情
     * @return array
     */
    private function getOrderInfo()
    {
        $get      = $this->request->get;
        $lang_map = $this->config->get('dealbao_lang_map');
        if (empty($get['orderSn'])) return ['error_warning' => $this->language->get('empty_order_sn')];
        $param      = [
            'language_id' => $lang_map['to']['default'],
            'page'        => 1,
            'size'        => 1,
            'order_sn'    => $get['orderSn'],
        ];
        $order_list = $this->model_extension_dealbao_sdk_client->getSdkCallback('Order/getOrderList', $param);
        !empty($order_list['data'][0]['reciver_info']) && $order_list['data'][0]['reciver_info'] = unserialize($order_list['data'][0]['reciver_info']);
        !empty($order_list['data'][0]['create_time']) && $order_list['data'][0]['create_time'] = date('Y-m-d H:i:s', $order_list['data'][0]['create_time']);
        !empty($order_list['data'][0]['update_time']) && $order_list['data'][0]['update_time'] = date('Y-m-d H:i:s', $order_list['data'][0]['update_time']);
        return $order_list['code'] != 200 ? ['error_warning' => $order_list['msg']] : ['order_info' => $order_list['data'][0]];
    }

    /**
     * 获取订单列表
     * @return array
     */
    private function getOrderList()
    {
        // test develop repository
        $get        = $this->request->get;
        $lang_map   = $this->config->get('dealbao_lang_map');
        $param      = [
            'language_id' => $lang_map['to']['default'],
            'page'        => !empty($get['page']) ? $get['page'] : 1,
            'size'        => !empty($get['limit']) ? $get['limit'] : 10,
            'order_sn'    => !empty($get['filter_orderSn']) ? $get['filter_orderSn'] : '',
            'goods_name'  => !empty($get['filter_goodsName']) ? $get['filter_goodsName'] : '',
            'start_time'  => !empty($get['filter_startTime']) ? $get['filter_startTime'] : '',
            'end_time'    => !empty($get['filter_endTime']) ? $get['filter_endTime'] : '',
        ];
        $order_list = $this->model_extension_dealbao_sdk_client->getSdkCallback('Order/getOrderList', $param);
        if (!empty($order_list['data'])) {
            foreach ($order_list['data'] as &$order) {
                $order['update_time'] = !empty($order['update_time']) ? date('Y-m-d H:i:s', $order['update_time']) : '无';
                $order['create_time'] = !empty($order['create_time']) ? date('Y-m-d H:i:s', $order['create_time']) : '无';
            }
        }
        return $order_list['code'] != 200 ? ['error_warning' => $order_list['msg']] : ['order_list' => $order_list];
    }

    /**
     * getClient
     * @return array
     */
    private function getClient()
    {
        $this->load->model('extension/dealbao/token');
        $this->load->model('extension/dealbao/sdk_client');
        $data = [];
        $res  = $this->model_extension_dealbao_token->getAccessToken();
        if ($res == false) $data['error_warning'] = $this->language->get('error_app_info');
        $this->model_extension_dealbao_sdk_client->getClient();
        return $data;
    }


    /**
     * 验证权限
     * @return bool
     */
    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/dealbao/order_list'))
            $this->error['warning'] = $this->language->get('error_permission');

        return !$this->error;
    }

}