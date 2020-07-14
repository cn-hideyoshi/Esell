<?php


class ControllerExtensionExtensionDealbao extends Controller
{
    private $error = array();

    public function index()
    {
        $this->language->load('extension/extension/dealbao');

        $this->load->model('setting/extension');
        $this->getList();

    }

    public function install()
    {

        $this->load->language('extension/extension/dealbao');

        $this->load->model('setting/extension');

        if ($this->validate()) {
            $this->model_setting_extension->install('dealbao', $this->request->get['extension']);

            $this->load->model('user/user_group');

            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/dealbao/' . $this->request->get['extension']);
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/dealbao/' . $this->request->get['extension']);

            // Call install method if it exsits
            $this->load->controller('extension/dealbao/' . $this->request->get['extension'] . '/install');

            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->getList();
    }

    public function uninstall()
    {
        $this->load->language('extension/extension/dealbao');

        $this->load->model('setting/extension');

        if ($this->validate()) {
            $this->model_setting_extension->uninstall('dealbao', $this->request->get['extension']);

            // Call uninstall method if it exsits
            $this->load->controller('extension/dealbao/' . $this->request->get['extension'] . '/uninstall');

            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->getList();
    }

    protected function getList()
    {
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $extensions = $this->model_setting_extension->getInstalled('dealbao');

        foreach ($extensions as $key => $value) {
            if (!is_file(DIR_APPLICATION . 'controller/extension/dealbao/' . $value . '.php')
                && !is_file(DIR_APPLICATION . 'controller/dealbao/' . $value . '.php')) {
                $this->model_setting_extension->uninstall('dealbao', $value);
                unset($extensions[$key]);
            }
        }

        $data['extensions'] = array();

        // Compatibility code for old extension folders
        $files = glob(DIR_APPLICATION . 'controller/extension/dealbao/*.php');
        if ($files) {
            foreach ($files as $file) {

                $extension = basename($file, '.php');

                $this->load->language('extension/dealbao/' . $extension, 'extension');
                $single               = array(
                    'name'      => $this->language->get('extension')->get('heading_title'),
                    'install'   => $this->url->link('extension/extension/dealbao/install',
                        'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
                    'uninstall' => $this->url->link('extension/extension/dealbao/uninstall',
                        'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
                    'installed' => in_array($extension, $extensions),
                    'edit'      => $this->url->link('extension/dealbao/' . $extension,
                        'user_token=' . $this->session->data['user_token']));
                $single['status']     = $extension == 'config' ?
                    empty($this->config->get('dealbao_' . $extension)['status']) ? $this->language->get('text_disabled') : $this->language->get('text_enabled') :
                    in_array($extension, $extensions) ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
                $data['extensions'][] = $single;
            }
        }

        $this->response->setOutput($this->load->view('extension/extension/dealbao', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/extension/dealbao')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}