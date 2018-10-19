<?php

class ModelRestapiNomenclature extends Model
{
    /**
     * Update price for product
     *
     * @param $guid
     * @param $price
     * @return mixed
     */
	public function SetPrice($guid, $price)
    {
        return $this->db->query("UPDATE " . DB_PREFIX . "product SET price = " . (float)$price . " WHERE GUID = '" . (string)$guid . "'");
	}
}
