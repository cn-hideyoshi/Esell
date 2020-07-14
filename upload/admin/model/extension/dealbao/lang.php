<?php

class ModelExtensionDealbaoLang extends Model
{
    public function getLangList($client)
    {
        return $client->Lang->getLangList();
    }

    public function getWebsiteLang()
    {
        $sql   = 'select * from ' . DB_PREFIX . 'language where `status` = 1';
        $query = $this->db->query($sql);
        return $query->rows;
    }
}