<?php

class ModelExtensionDealbaoImage extends Model
{
    private $dealbao_prefix = 'dealbao_';


    /**
     * upload self image to website
     * @param $url
     * @return string
     */
    public function saveGoodsImage(string $url)
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


}