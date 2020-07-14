<?php

class ControllerExtensionDealbaoMapLanguage extends Controller
{
    public function index()
    {
        $this->language->load('extension/dealbao/map_language');
        $this->load->model('setting/setting');
        $this->load->model('extension/dealbao/common');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $post   = $this->request->post;
            $config = ['to' => [], 'back' => []];
            foreach ($post as $key => $value) {
                switch ($key) {
                    case 'default_to':
                        $config['to']['default'] = $value;
                        break;
                    case 'default_back':
                        $config['back']['default'] = $value;
                        break;
                    default:
                        $config['to'][$key]     = $value;
                        $config['back'][$value] = $key;
                }
            }
            $this->model_setting_setting->editSetting('dealbao_lang_map', ['dealbao_lang_map' => $config]);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/dealbao/map_language', 'user_token=' . $this->session->data['user_token'] . '&type=dealbao'));
        }
        $breadcrumbs = [];
        $this->load->model('extension/dealbao/token');
        $this->load->model('extension/dealbao/sdk_client');
        $this->load->model('extension/dealbao/lang');
        $res = $this->model_extension_dealbao_token->getAccessToken();
        if ($res == false) $data['error_warning'] = $this->language->get('error_app_info');
        $this->model_extension_dealbao_sdk_client->getClient();
        $data                 = $this->getLangList();
        $data['lang_map']     = $this->config->get('dealbao_lang_map');
        $data['website_lang'] = $this->model_extension_dealbao_lang->getWebsiteLang();
        $breadcrumbs[]        = array(
            'text' => $this->language->get('list_title'),
            'href' => $this->url->link('extension/dealbao/map_language', 'user_token=' . $this->session->data['user_token'])
        );

        $data['breadcrumbs'] = $this->model_extension_dealbao_common->editBreadCrumbs($this, $breadcrumbs);
        $data                = $this->model_extension_dealbao_common->getCommonData($this, $data);

        $this->response->setOutput($this->load->view('extension/dealbao/map_language', $data));
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/dealbao/map_language'))
            $this->error['warning'] = $this->language->get('error_permission');
        return !$this->error;
    }

    private function getLangList()
    {
        $info = $this->model_extension_dealbao_sdk_client->getSdkCallback('Lang/getLangList');
        return !empty($info) && $info['code'] == 200 ? ['lang_list' => $info['data']] : ['error_warning' => $info['msg']];
    }
}