<?php

class ModelRestapiOrder extends Model
{
    /**
     * Update status for order
     *
     * @param $order_GUID
     * @param $status
     * @return mixed
     */
    public function changeStatusOrder($order_GUID, $status)
    {
        return $this->db->query("UPDATE " . DB_PREFIX . "order SET order_status_id = " . (int)$status . " WHERE GUID = '" . (string)$order_GUID . "'");
    }
}
