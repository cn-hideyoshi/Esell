<?php

class ModelExtensionDealbaoGoods extends Model
{
    private $dealbao_prefix = 'dealbao_';
    private $goods          = [];
    private $sku_data       = [];
    private $cateList       = [];
    private $setWebSiteLang = [];

    /**
     * save goods to website
     * @param array $info
     * @param array $webSiteLang
     * @return string|array
     */
    public function saveGoods(array $info, array $webSiteLang)
    {
        //develop test
        $this->setWebSiteLang($webSiteLang);
        $this->setGoods($info['goods_info']);
        $this->setCateList($info['cate_list']);
        $result = false;
        try {

            //product
            foreach ($this->sku_data as $key => $goods) {
                $language_id = $this->getLanguageId($goods['language_id']);
                $result = $this->setProduct($goods);

                $this->sku_data[$key]['product_id'] = $result;
                $this->sku_data[$key]['website_lang'] = $language_id;
            }
            //category
            $this->setGoodsCate();
            //description
            $this->setProductDescription();
            //store
            $this->setProductStore();
            //related
            $this->setProductRelated();
            //layout
            $this->setProductLayout();
        } catch (\Exception $e) {
            $result = ['code' => 500, 'msg' => $e->getMessage()];
        }

        return $result;
    }

    private function setWebSiteLang($webSiteLang)
    {
        $this->setWebSiteLang = $webSiteLang;
    }

    /**
     * set private param $cateList
     * @param $cateList
     */
    private function setCateList($cateList)
    {
        $this->cateList = $cateList;
    }

    /**
     * set private param $goods
     * @param $info
     */
    private function setGoods($info)
    {
        $lang_arr = [];
        $lang_list = [];
        foreach (explode(',', $info['language_list']) as $lang) {
            $lang_list[] = $this->getLanguageId($lang);
        }
        $info['lang_id'] = $this->getLanguageId($info['language_id']);
        $info['lang_list'] = $lang_list;
        $this->goods = $info;
        unset($this->goods['sku_data']);
        foreach ($info['sku_data'] as $goods) {
            $goods['goods_spec'] = unserialize($goods['goods_spec']);
            $goods['spec_name'] = unserialize($goods['spec_name']);
            empty($this->sku_data[$goods['goods_sku']]) && $this->sku_data[$goods['goods_sku']] = $goods;
            $lang_arr[$goods['goods_sku']]['goods_name'][$goods['language_id']] = $goods['goods_name'];
            $lang_arr[$goods['goods_sku']]['goods_spec'][$goods['language_id']] = $goods['goods_spec'];
            $lang_arr[$goods['goods_sku']]['spec_name'][$goods['language_id']] = $goods['spec_name'];
        }
        foreach ($this->sku_data as $key => &$value) {
            $value['goods_name'] = $lang_arr[$key]['goods_name'];
            $value['goods_spec'] = $lang_arr[$key]['goods_spec'];
            $value['spec_name'] = $lang_arr[$key]['spec_name'];
        }

        $this->sku_data = array_values($this->sku_data);
    }

    /**
     * insert into product
     * @param $goods
     * @return mixed
     */
    private function setProduct($goods)
    {
        $sql = 'insert into ' . DB_PREFIX . 'product (model,sku,upc,ean,jan,isbn,mpn,location,quantity,stock_status_id,image,manufacturer_id,shipping,price,points,tax_class_id,date_available,weight,weight_class_id,length,width,height,length_class_id,subtract,minimum,sort_order,`status`,viewed,date_added,date_modified,dealbao_status) values';
        $sql .= '(\'' . $goods['spu'] . '\',\'' . $goods['goods_sku'] . '\',\'\',\'\',\'\',\'\',\'\',\'\',\'' . $goods['goods_storage'] . '\',\'5\',\'' . $this->saveGoodsImage($goods['goods_image']) . '\',\'0\',\'1\',\'' . $goods['goods_price'] . '\',\'0\',\'0\',\'' . date('Y-m-d') . '\',\'' . $goods['weight'] . '\',\'1\',\'' . $goods['goods_length'] . '\',\'' . $goods['goods_wide'] . '\',\'' . $goods['goods_wide'] . '\',\'2\',\'1\',\'1\',\'0\',\'0\',\'0\',\'' . date('Y-m-d') . '\',\'' . date('Y-m-d') . '\',\'1\')';
        $this->db->query($sql);
        return $this->db->getLastId();
    }

    /**
     * insert into product to category
     */
    private function setGoodsCate()
    {
        $sql = 'insert into ' . DB_PREFIX . 'product_to_category (product_id,category_id) values';
        foreach ($this->sku_data as $goods) {
            foreach ($this->cateList as $cate_id) {
                $sql .= '(\'' . $goods['product_id'] . '\',\'' . $cate_id . '\'),';
            }
        }

        $this->db->query(trim($sql, ','));
    }

    /**
     * insert into product description
     */
    private function setProductDescription()
    {
        $sql = 'insert into ' . DB_PREFIX . 'product_description (product_id,language_id,`name`,`description`,tag,meta_title,meta_description,meta_keyword) values';
        foreach ($this->sku_data as $key => $goods) {
            foreach ($this->setWebSiteLang as $language) {

                $language_id = $language['language_id'];
                $dealbao_lang_id = $this->getLanguageId($language_id, 'to');
                !isset($goods['goods_name'][$dealbao_lang_id]) && $dealbao_lang_id = $this->getLanguageId('default', 'to');

                $sql .= '(\'' . $goods['product_id'] . '\',
                \'' . $language_id . '\',
                \'' . $goods['goods_name'][$dealbao_lang_id] . '\',
                \'' . htmlspecialchars($this->goods['goods_body'][$dealbao_lang_id]) . '\',
                \'\',\'' . $goods['goods_name'][$dealbao_lang_id] . '\',\'\',\'\'),';
            }
        }
        $this->db->query(trim($sql, ','));
    }

    /**
     * insert into product store
     */
    private function setProductStore()
    {
        $sql = 'insert into ' . DB_PREFIX . 'product_to_store (product_id,store_id) values';
        foreach ($this->sku_data as $goods) $sql .= '(\'' . $goods['product_id'] . '\',\'0\'),';

        $this->db->query(trim($sql, ','));
    }

    /**
     * insert into product related
     */
    private function setProductRelated()
    {
        $sql = 'insert into ' . DB_PREFIX . 'product_related (product_id,related_id) values';
        foreach ($this->sku_data as $goods) $sql .= '(\'' . $goods['product_id'] . '\',\'' . $goods['product_id'] . '\'),';

        $this->db->query(trim($sql, ','));
    }

    /**
     * inert into product layout
     */
    private function setProductLayout()
    {
        $sql = 'insert into ' . DB_PREFIX . 'product_to_layout (product_id,store_id,layout_id) values';
        foreach ($this->sku_data as $goods) $sql .= '(\'' . $goods['product_id'] . '\',\'0\',\'0\'),';
        $this->db->query(trim($sql, ','));
    }

    /**
     * upload self image to website
     * @param $url
     * @return string
     */
    private function saveGoodsImage(string $url)
    {
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
        return str_replace('../image/', '', $save_dir . $filename);
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