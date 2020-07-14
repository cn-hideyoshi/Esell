<?php

class ModelExtensionESellGoods extends Model
{
    public function batchChangeStock($param)
    {
        if (empty($param)) return;
        $sql = 'update `' . DB_PREFIX . 'product` set quantity = case `dealbao_sku`';
        foreach ($param as $mark => $stock) {
            $sql .= "when '{$mark}' then '{$stock}'";
        }
        $sql .= ' end where `dealbao_sku` in (\'' . implode('\',\'', array_keys($param)) . '\')';
        $this->db->query($sql);
    }
}