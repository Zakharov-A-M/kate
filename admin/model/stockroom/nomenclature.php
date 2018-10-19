<?php

class ModelStockroomNomenclature extends Model
{
    /**
     * @param $id
     * @return mixed
     */
    public function getNomenclature($id)
    {
        $query = $this->db->query("SELECT sn.amount AS amount, sn.nomenclature_id AS nomenclature_id, pd.name AS nomenclature_name  FROM " . DB_PREFIX . "stockroom_nomenclature sn INNER JOIN " . DB_PREFIX . "product p ON (sn.nomenclature_id = p.product_id)  LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE sn.id = '" . (int)$id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
        return $query->row;
    }

    /**
     * Get all nomenclatures in this stockroom
     *
     * @param $stockroom_id
     * @param array $data
     * @return mixed
     */
    public function getNomenclatures($stockroom_id, $data = array())
    {
        $sql = "SELECT sn.ID AS id, p.image AS image, pd.name AS name, pd.description AS description, sn.amount AS amount FROM " . DB_PREFIX . "stockroom_nomenclature sn INNER JOIN " . DB_PREFIX . "stockroom s ON (sn.stockroom_id = s.stockroom_id) INNER JOIN " . DB_PREFIX . "product p ON (sn.nomenclature_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id = p.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND s.stockroom_id = '" . (int)$stockroom_id . "'";


        $sort_data = array(
            'name',
            'description',
            'amount'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY nomenclature_id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * Get count nomenclatures
     *
     * @param $stockroom_id
     * @return mixed
     */
    public function getTotalNomenclatures($stockroom_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stockroom_nomenclature sn WHERE sn.ID = '" . (int)$stockroom_id . "'");

        return $query->row['total'];
    }

    /**
     * Delete nomenclature
     *
     * @param $id
     */
    public function deleteNomenclature($id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "stockroom_nomenclature WHERE ID = '" . (int)$id . "'");

        $this->cache->delete('stockroom_nomenclature');
        $this->cache->deleteCategoryForCountry();
    }

    /**
     * Get nomenclature for autocomplete
     *
     * @param array $data
     * @return mixed
     */
    public function getNomenclatureForAutocomplete($data = [])
    {
        if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "product_description oc INNER JOIN " . DB_PREFIX . "product c1 ON (c1.product_id = oc.product_id) WHERE oc.language_id = '" .  (int)$this->config->get('config_language_id') ."' AND oc.name LIKE '" . $data['filter_name'] . "%'";

            $sort_data = array(
                'name',
            );

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $sql .= " ORDER BY " . $data['sort'];
            } else {
                $sql .= " ORDER BY name";
            }

            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                $sql .= " DESC";
            } else {
                $sql .= " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $query = $this->db->query($sql);

            return $query->rows;
        }
    }

    /**
     * Add new nomenclature
     *
     * @param $stockroom_id
     * @param $data
     * @return bool
     */
    public function addNomenclature($stockroom_id, $data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "stockroom_nomenclature SET stockroom_id = '" . (int)$stockroom_id . "', `amount` = '" . (int)$data['amount'] . "', `nomenclature_id` = '" . (int)$data['nomenclature_id'] . "'");
        $this->cache->deleteCategoryForCountry();

        return true;
    }

    /**
     * Edit ID nomenclature
     *
     * @param $id
     * @param $data
     */
    public function editNomenclature($id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "stockroom_nomenclature SET amount = '" . (int)$data['amount'] . "', nomenclature_id = '" . (int)$data['nomenclature_id'] . "'WHERE ID = '" . (int)$id . "'");
        $this->cache->deleteCategoryForCountry();
    }

    /**
     * Сheck to dublicate nomenclature in stockroom
     *
     * @param $stockroom_id
     * @param $data
     * @return int
     */
    public function dublicateNomenclature($stockroom_id, $data)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stockroom_nomenclature st WHERE st.stockroom_id = '" . (int)$stockroom_id . "' AND st.nomenclature_id = '" . (int)$data['nomenclature_id'] . "'");

        return (int)$query->row['total'];
    }

    /**
     * Get ID value to nomenclature and stockroom
     *
     * @param $stockroom_id
     * @param $nomeclatureId
     * @return int
     */
    public function getIDNomenclature($stockroom_id, $nomeclatureId)
    {
        $query = $this->db->query("SELECT ID AS id FROM " . DB_PREFIX . "stockroom_nomenclature WHERE stockroom_id = '" . (int)$stockroom_id . "' AND nomenclature_id = '" . (int)$nomeclatureId . "'");

        return (int)$query->row['id'];
    }
}
