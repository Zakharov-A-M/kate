<?php

class ModelCatalogProductFactor extends Model
{
    /**
     * Added factor for product
     *
     * @param $productId
     * @param $data
     * @return mixed
     */
    public function addProductFactor($productId, $data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_factor SET product_id = '" . (int)$productId . "', pr1 = '" . $this->db->escape((string)$data['pr1']) . "', pr2 = '" . $this->db->escape((string)$data['pr2']) . "', pr3 = '" . $this->db->escape((string)$data['pr3']) . "', sf1 = '" . $this->db->escape((string)$data['sf1']) . "', sf2 = '" . $this->db->escape((string)$data['sf2']) . "', sf3 = '" . $this->db->escape((string)$data['sf3']) . "', sf4 = '" . $this->db->escape((string)$data['sf4']) . "', sf5 = '" . $this->db->escape((string)$data['sf5']) . "', sf6 = '" . $this->db->escape((string)$data['sf6']) . "'");
        return  $productId;
    }

    /**
     * Edited factor product
     *
     * @param $productId
     * @param $data
     * @return mixed
     */
    public function editProductFactor($productId, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "product_factor SET pr1 = '" . $this->db->escape((string)$data['pr1']) . "', pr2 = '" . $this->db->escape((string)$data['pr2']) . "', pr3 = '" . $this->db->escape((string)$data['pr3']) . "', sf1 = '" . $this->db->escape((string)$data['sf1']) . "', sf2 = '" . $this->db->escape((string)$data['sf2']) . "', sf3 = '" . $this->db->escape((string)$data['sf3']) . "', sf4 = '" . $this->db->escape((string)$data['sf4']) . "', sf5 = '" . $this->db->escape((string)$data['sf5']) . "', sf5 = '" . $this->db->escape((string)$data['sf6']) . "' WHERE product_id = '" . (int)$productId . "'");
        return  $productId;
    }
}
