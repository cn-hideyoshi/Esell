<?php

class ControllerExtensionDealbaoConfig extends Controller
{
    private $error = array();

    /**
     * 信息填写修改页
     * @throws Exception
     */
    public function index()
    {
        $this->load->language('extension/dealbao/config');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $configInfo = ['dealbao_config' => $this->request->post];

            $this->model_setting_setting->editSetting('dealbao', $configInfo);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dealbao'));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dealbao')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/dealbao/config', 'user_token=' . $this->session->data['user_token'])
        );

        $data['action']       = $this->url->link('extension/dealbao/config', 'user_token=' . $this->session->data['user_token']);
        $data['cancel']       = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dealbao');
        $data['user_token']   = $this->session->data['user_token'];
        $data['data_dealbao'] = HTTP_CATALOG . 'index.php?route=extension/dealbao/config';

        $info = $this->config->get('dealbao_config');
        if ($info) $data['info'] = $info;

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/dealbao/config', $data));
    }


    /**
     * 验证
     * @return bool
     */
    protected function validate()
    {
        !$this->user->hasPermission('modify', 'extension/dealbao/config') &&
        $this->error['warning'] = $this->language->get('error_permission');

        empty($this->request->post['app_key']) && $this->error['warning'] = $this->language->get('empty_app_key');
        empty($this->request->post['app_id']) && $this->error['warning'] = $this->language->get('empty_app_id');
        empty($this->request->post['secret']) && $this->error['warning'] = $this->language->get('empty_secret');
        return !$this->error;
    }
}
