<?php

class ModelExtensionESellOrder extends Model
{
    private $e_sell_prefix = 'dealbao_';

    public function setOrderRelated($local, $origin)
    {
        if (empty($local) || empty($origin)) return;
        $sql = 'insert into `' . $this->e_sell_prefix . 'order_related` (`order_id`,`pay_sn`,`order_sn`) 
        values(\'' . $local['order_id'] . '\',\'' . $origin['paySn'] . '\',\'' . serialize($origin['orderSn']) . '\')';
        $this->db->query($sql);
    }
}