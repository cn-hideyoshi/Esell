<?php


class ModelExtensionDealbaoCommon extends Model
{

    private $result = [
        'code'    => 0,
        'message' => '',
        'data'    => '',
    ];

    public function getCommonData($that, $data)
    {
        $data['error_warning'] = isset($that->error['warning']) ? $that->error['warning'] : $data['error_warning'] ?? '';
        $data['action'] = $data['breadcrumbs'][count($data['breadcrumbs']) - 1]['href'];
        $data['cancel'] = $data['breadcrumbs'][count($data['breadcrumbs']) - 2]['href'];
        $data['user_token'] = $that->session->data['user_token'];
        $data['header'] = $that->load->controller('common/header');
        $data['column_left'] = $that->load->controller('common/column_left');
        $data['footer'] = $that->load->controller('common/footer');
        $data['success'] = isset($that->session->data['success']) ? $that->session->data['success'] : $data['success'] ?? '';
        $that->session->data['success'] = '';
        return $data;
    }

    public function getCategories()
    {
        $sql = "SELECT cp.category_id AS category_id, 
                GROUP_CONCAT( cd1.`name` ORDER BY cp.LEVEL SEPARATOR '  >  ' ) AS `name_group`,
                cd2.`name`,
                c1.parent_id, c1.sort_order FROM " . DB_PREFIX . "category_path cp 
                   LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) 
                   LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) 
                   LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) 
                   LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) 
                       WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "'
                       AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'
                       GROUP BY cp.category_id ORDER BY `name`,sort_order";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getTreeData($data, $parent_id, $parent_key, $key)
    {
        $tree = array();
        foreach ($data as $row) {
            $row['name'] = htmlspecialchars_decode($row['name']);
            if ($row[$parent_key] == $parent_id) {
                $arr = $this->getTreeData($data, $row[$key], $parent_key, $key);
                if ($arr) $row['children'] = $arr;
                $tree[] = $row;
            }
        }
        return $tree;
    }

    public function editBreadCrumbs($that, $append)
    {
        $breadcrumbs = array();
        $breadcrumbs[] = array(
            'text' => $that->language->get('text_home'),
            'href' => $that->url->link('common/dashboard', 'user_token=' . $that->session->data['user_token'])
        );
        $breadcrumbs[] = array(
            'text' => $that->language->get('text_extension'),
            'href' => $that->url->link('marketplace/extension', 'user_token=' . $that->session->data['user_token'] . '&type=dealbao')
        );

        return array_merge($breadcrumbs, $append);
    }

    public function pagination($that, $total, $url, $action = 'goods_list')
    {
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = empty($that->request->get['page']) ? 1 : $that->request->get['page'];
        $pagination->limit = empty($that->request->get['limit']) ? 10 : $that->request->get['limit'];
        $pagination->url = $that->url->link('extension/dealbao/' . $action, 'user_token=' . $that->session->data['user_token'] . $url . '&page={page}');
        return $pagination->render();
    }

    public function limitOption($that, $total, $limitArray = ['10', '30', '50', '70', '100'])
    {
        if ($total == 0) return '';
        $page = $that->request->get;
        empty($page['page']) && $page['page'] = 1;
        empty($page['limit']) && $page['limit'] = 10;

        $select = '<div style="width: 16%;float:right;text-align: center;">' . $page['page'] . '/' . ceil($total / $page['limit']) . '</div>
            <div style="width: 22%;float:right;"><select class="form-control page_select">';

        foreach ($limitArray as $value)
            $select .= '<option value="' . $value . '" ' . ($value == $page['limit'] ? 'selected' : '') . '>' . $value . '</option>';
        $select .= '</select></div>
            <div style="width: 16%;float:right;text-align: center;">' . $that->language->get('limit_text') . '</div>
            <div style="width: 22%;float:right;"><input type="text" class="form-control" value="' . $page['page'] . '"></div>
            <div style="width: 16%;float:right;text-align: center;">' . $that->language->get('page_text') . '</div>';
        return $select;
    }

    public function buildResponse($result, $language)
    {
        $this->result['code'] = $result['code'] ?? 200;
        $this->result['message'] = $result['message'] ?? $language->get('success_text');
        $this->result['data'] = $result['data'] ?? true;
        echo json_encode($this->result);
    }
}