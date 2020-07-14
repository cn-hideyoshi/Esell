<?php

use  \Dealbao\Open\Client;

class ModelExtensionDealbaoSdkClient extends Model
{
    private static $client;

    public function getClient()
    {
        $cache        = $this->cache;
        $accessToken  = $cache->get('accessToken');
        $config_info  = $this->config->get('dealbao_config');
        $config       = ['appid'        => $config_info['app_id'],
                         'appkey'       => $config_info['app_key'],
                         'secret'       => $config_info['secret'],
                         "access_token" => $accessToken['access_token']];

        self::$client = new Client($config);
        return self::$client;
    }

    public function getSdkCallback($url, $param = [])
    {
        list($model, $function) = explode('/', $url);
        $res = self::$client->$model->$function($param);
        if (isset($res['code']) && $res['code'] == 1000012)
            $this->cache->set('accessToken', null);
        return $res;
    }
}