<?php

use \Dealbao\Open\Client;

class ModelExtensionESellToken extends Model
{
    public static $client;

    public function getAccessToken()
    {
        $cache       = $this->cache;
        $accessToken = $cache->get('accessToken');
        if (!$accessToken || (!empty($accessToken['expire']) && $accessToken['expire'] <= time())) {
            $config_info  = $this->config->get('dealbao_config');
            $config       = ['appid'  => $config_info['app_id'],
                             'appkey' => $config_info['app_key'],
                             'secret' => $config_info['secret']];

            self::$client = new Client($config);
//            $param['refresh'] = true;
            $res = self::$client->AccessToken->getAccessToken();
            if (empty($res['code']) || $res['code'] != 200) return false;
            $res['data']['expire'] = $res['data']['expire_in'] + time() - 100;

            $cache->set('accessToken', $res['data']);
        }
        return $cache->get('accessToken');
    }
}