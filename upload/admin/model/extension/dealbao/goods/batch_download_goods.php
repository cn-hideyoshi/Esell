<?php

class ModelExtensionDealbaoGoodsBatchDownloadGoods extends Model
{
    private $dealbao_prefix = 'dealbao_';

    /**
     * save goods to database
     * @param $list
     * @param $download_info
     * @return bool|array
     */
    public function saveToDataBase($list, $download_info)
    {

        $break_keys = ['areaid_1', 'areaid_2', 'brand_id', 'brand_name', 'cate_group_id', 'gc_id', 'gc_id_1', 'gc_id_2', 'gc_id_3', 'gc_name', 'gm_id', 'goods_id', 'is_show', 'is_default', 'last_sale_time', 'lower_type', 'refund_type', 'release_state', 'store_id'];
        $sql = 'insert into `' . $this->dealbao_prefix . 'download_goods` ';
        try {
            $insert_keys = array_keys($list[0]);
            $diff_keys = array_diff($insert_keys, $break_keys);
            $sql .= ' (`' . implode('`,`', $diff_keys) . '`,`download_id`)  values';
            foreach ($list as $goods) {
                $insert_value = '(';
                foreach ($goods as $key => $value) {
                    if (in_array($key, $break_keys)) continue;
                    is_array($value) && $value = serialize($value);
                    $value = str_replace("\\", "\\\\", $value);
                    $value = str_replace("'", "\'", $value);
                    $insert_value .= "'" . $value . "',";
                }
                $insert_value .= "'" . $download_info['download_id'] . "'";
                $sql .= $insert_value . '),';
            }
            $sql = trim($sql, ',');
        } catch (\Exception $e) {
            return ['code' => 500, 'message' => 'data error'];
        }
        try {
            $this->db->query($sql);
        } catch (\Exception $e) {
            return ['code' => 500, 'message' => 'sql error'];
        }
        return ['data' => true];
    }

    /**
     * save download history
     * @param $data
     * @return array
     */
    public function saveDownloadHistory($data)
    {
        try {
            $sql = "insert into `" . $this->dealbao_prefix . "download_history` (`bind_group_id`,`create_time`,`update_time`) 
        values('{$data['bind_group_id']}','{$data['create_time']}','{$data['update_time']}')";
            $this->db->query($sql);
        } catch (\Exception $e) {
            return ['code' => 500, 'message' => $this->language->get('init_error')];
        }
        return ['data' => $this->db->getLastId()];
    }

    /**
     * get download group info
     * @param $data
     * @return array
     */
    public function getDownloadGroup($data)
    {
        $sql = "select * from `" . $this->dealbao_prefix . "download_group` where `download_id`='{$data['download_id']}' and `group_id`='{$data['group_id']}'";
        $query = $this->db->query($sql);
        if (empty($query->row)) {
            $sql = "insert into `" . $this->dealbao_prefix . "download_group` (`download_id`,`group_id`,`category_id`) values('{$data['download_id']}','{$data['group_id']}','{$data['category_id']}')";
            $this->db->query($sql);
            $id = $this->db->getLastId();
            $query->row = ['id'          => $id,
                           'download_id' => $data['download_id'],
                           'group_id'    => $data['group_id'],
                           'category_id' => $data['category_id'],
                           'page'        => 0,
                           'inc'         => 0,
                           'limit'       => 0,
                           'status'      => 0];
        }
        return $query->row;

    }

    /**
     * get goods info
     * @param $data
     * @return array
     */
    public function getGoodsInfo($data)
    {
        $sql = 'select * from `' . $this->dealbao_prefix . 'download_goods` where `download_id` = \'' . $data['download_id'] . '\' limit ' . $data['limit'] . ',1';
        $query = $this->db->query($sql);
        return $query->row ? $query->row : ['code' => 500, 'message' => $this->language->get('no_data')];
    }

    /**
     * save goods to product
     * @param $data
     * @return array
     */
    public function saveGoodsToProduct($data)
    {
        $this->load->model('extension/dealbao/lang');
        $websiteLang = $this->model_extension_dealbao_lang->getWebsiteLang();

        //more image
        $image_list = [];
        if (!empty($data['images_more'])) foreach ($data['images_more'] as $image)
            $image_list[] = $this->saveGoodsImage($image['goods_image']);

        foreach ($data['sku_data'] as $sku) {
            $result = $this->setProduct($sku);
            $sku['product_id'] = $result;
            //category
            $this->setGoodsCate($sku, $data['cate_list']);
            //description
            $this->setProductDescription($sku, $websiteLang);
            //store
            $this->setProductStore($sku);
            //related
            $this->setProductRelated($sku);
            //layout
            $this->setProductLayout($sku);
            //product image
            $this->saveProductImage($sku, $image_list);
        }

        return ['data' => $data['spu']];
    }

    /**
     * insert into product
     * @param $goods
     * @return mixed
     */
    private function setProduct($goods)
    {
        $sql = 'insert into ' . DB_PREFIX . 'product (model,sku,upc,ean,jan,isbn,mpn,location,quantity,stock_status_id,image,manufacturer_id,shipping,price,points,tax_class_id,date_available,weight,weight_class_id,length,width,height,length_class_id,subtract,minimum,sort_order,`status`,viewed,date_added,date_modified,dealbao_status) values';
        $sql .= '(\'' . $goods['spu'] . '\',\'' . $goods['goods_sku'] . '\',\'\',\'\',\'\',\'\',\'\',\'\',\'' . $goods['goods_storage'] . '\',\'5\',\'' . $this->saveGoodsImage($goods['goods_image']) . '\',\'0\',\'1\',\'' . $goods['goods_price'] . '\',\'0\',\'0\',\'' . date('Y-m-d') . '\',\'' . $goods['weight'] . '\',\'1\',\'' . $goods['goods_length'] . '\',\'' . $goods['goods_wide'] . '\',\'' . $goods['goods_wide'] . '\',\'2\',\'1\',\'1\',\'0\',\'1\',\'0\',\'' . date('Y-m-d') . '\',\'' . date('Y-m-d') . '\',\'1\')';
        $this->db->query($sql);
        return $this->db->getLastId();
    }

    /**
     * insert into product to category
     * @param $goods
     * @param $cate_list
     */
    private function setGoodsCate($goods, $cate_list)
    {
        $sql = 'insert into ' . DB_PREFIX . 'product_to_category (product_id,category_id) values';
        foreach ($cate_list as $cate_id) {
            $sql .= '(\'' . $goods['product_id'] . '\',\'' . $cate_id . '\'),';
        }
        $this->db->query(trim($sql, ','));
    }

    /**
     * insert into product description
     * @param $goods
     * @param $websiteLang
     */
    private function setProductDescription($goods, $websiteLang)
    {
        $sql = 'insert into ' . DB_PREFIX . 'product_description (product_id,language_id,`name`,`description`,tag,meta_title,meta_description,meta_keyword) values';

        foreach ($websiteLang as $language) {
            $language_id = $language['language_id'];
            $dealbao_lang_id = $this->getLanguageId($language_id, 'to');
            !isset($goods['goods_name'][$dealbao_lang_id]) && $dealbao_lang_id = $this->getLanguageId('default', 'to');

            $sql .= '(\'' . $goods['product_id'] . '\',
                \'' . $language_id . '\',
                \'' . $goods['goods_name'][$dealbao_lang_id] . '\',
                \'' . htmlspecialchars($this->goods['goods_body'][$dealbao_lang_id]) . '\',
                \'\',\'' . $goods['goods_name'][$dealbao_lang_id] . '\',\'\',\'\'),';

        }
        $this->db->query(trim($sql, ','));
    }

    /**
     * insert into product store
     * @param $goods
     */
    private function setProductStore($goods)
    {
        $sql = 'insert into ' . DB_PREFIX . 'product_to_store (product_id,store_id) values';
        $sql .= '(\'' . $goods['product_id'] . '\',\'0\'),';
        $this->db->query(trim($sql, ','));
    }

    /**
     * insert into product related
     */
    private function setProductRelated($goods)
    {
        $sql = 'insert into ' . DB_PREFIX . 'product_related (product_id,related_id) values';
        $sql .= '(\'' . $goods['product_id'] . '\',\'' . $goods['product_id'] . '\'),';

        $this->db->query(trim($sql, ','));
    }

    /**
     * inert into product layout
     */
    private function setProductLayout($goods)
    {
        $sql = 'insert into ' . DB_PREFIX . 'product_to_layout (product_id,store_id,layout_id) values';
        $sql .= '(\'' . $goods['product_id'] . '\',\'0\',\'0\'),';
        $this->db->query(trim($sql, ','));
    }

    /**
     * insert into product image
     * @param $goods
     * @param $image_list
     */
    private function saveProductImage($goods, $image_list)
    {
        if (empty($image_list)) return;
        $sql = 'insert into ' . DB_PREFIX . 'product_image (product_id,image,sort_order) values';
        foreach ($image_list as $image) $sql .= '(\'' . $goods['product_id'] . '\',\'' . $image . '\',\'0\'),';
        $this->db->query(trim($sql, ','));
    }

    /**
     * upload self image to website
     * @param $url
     * @return string
     */
    private function saveGoodsImage(string $url)
    {
        $sql = 'select * from ' . $this->dealbao_prefix . 'download_images  where `origin_url` = \'' . $url . '\'';
        $query = $this->db->query($sql);
        if (!empty($query->row)) return $query->row['local_url'];

        $save_dir = '../image/catalog/dalbao/' . date('Ymd') . '/';
        //创建保存目录
        if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) return 'no_image.png';

        $ext = strrchr($url, '.');
        if ($ext != '.gif' && $ext != '.jpg') return 'no_image.png';
        $filename = time() . $ext;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        $img = curl_exec($ch);
        curl_close($ch);
        //$image_name就是要保存到什么路径,默认只写文件名的话保存到根目录
        $fp = fopen($save_dir . $filename, 'w');//保存的文件名称用的是链接里面的名称
        fwrite($fp, $img);
        fclose($fp);
        $image = str_replace('../image/', '', $save_dir . $filename);
        $sql = 'insert into ' . $this->dealbao_prefix . 'download_images (origin_url,local_url) values (\'' . $url . '\',\'' . $image . '\')';
        $this->db->query($sql);
        return $image;
    }

    /**
     * get connected languageId
     * @param int $language_id
     * @param string $type
     * @return int
     */
    private function getLanguageId($language_id, $type = 'back')
    {
        $lang_map = $this->config->get('dealbao_lang_map');
        return isset($lang_map[$type][$language_id]) ? $lang_map[$type][$language_id] : $lang_map[$type]['default'];
    }

}