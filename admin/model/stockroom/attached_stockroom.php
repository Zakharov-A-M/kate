<?php

class ModelStockroomAttachedStockroom extends Model
{
    /**
     * Get attached stockrooms
     *
     * @param $stockroomId
     * @return mixed
     */
    public function getAttachedStockrooms($stockroomId)
    {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "stockroom_attached WHERE stockroom_id = '" . (int)$stockroomId . "'"
        );

        return $query->rows;
    }

    /**
     * Get attached stockrooms
     *
     * @param $id
     * @return mixed
     */
    public function getStockroom($id)
    {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "stockroom_attached WHERE id = '" . (int)$id . "'"
        );
        return $query->row;
    }

    /**
     * Get attached stockroom name
     *
     * @param $id
     * @return mixed
     */
    public function getAttachedStockroomName($id)
    {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "stockroom_description
            WHERE stockroom_id = '" . (int)$id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'"
        );

        return $query->row;
    }

    /**
     * Delete attached stockroom
     *
     * @param $id
     */
    public function deleteAttachedStockroom($id)
    {
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "stockroom_attached WHERE id = '" . (int)$id . "'"
        );
    }

    /**
     * Get nomenclature for autoComplete
     *
     * @param array $data
     * @param array $currentId
     * @return mixed
     */
    public function getNomenclatureForAutoComplete($data, $currentId)
    {
        if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "stockroom_description sd 
                WHERE sd.language_id = '" .  (int)$this->config->get('config_language_id') ."' 
                AND sd.name LIKE '%" . $data['filter_name'] . "%'
                AND stockroom_id NOT IN  (".  implode(",", $currentId) .")";
            $sortData = array(
                'name',
            );

            if (isset($data['sort']) && in_array($data['sort'], $sortData)) {
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
     * Edit attached stockroom from Id
     *
     * @param integer $id
     * @param array $data
     */
    public function editAttachedStockroom($id, $data)
    {
        $this->db->query(
            "UPDATE " . DB_PREFIX . "stockroom_attached SET 
            attach_stockroom_id = '" . (int)$data['attach_stockroom_id'] . "', 
            sort_order = '" . (int)$data['sort_order'] . "', 
            description_delivery = '" . $data['delivery'] . "' 
            WHERE id = '" . (int)$id . "'"
        );
    }

    /**
     * Added attached stockroom from Id
     *
     * @param integer $id
     * @param array $data
     */
    public function addAttachedStockroom($id, $data)
    {
        $this->db->query(
            "INSERT INTO " . DB_PREFIX . "stockroom_attached SET 
            attach_stockroom_id = '" . (int)$data['attach_stockroom_id'] . "',
            sort_order = '" . (int)$data['sort_order'] . "',
            description_delivery = '" . $data['delivery'] . "',
            stockroom_id = '" . (int)$id . "'"
        );
    }

    /**
     * Check dublicate stockroom
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function dublicateAttachedStockroom($id, $data)
    {
        $query = $this->db->query(
            "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stockroom_attached
            WHERE stockroom_id = '" . (int)$id . "' AND 
            attach_stockroom_id = '" . (int)$data['attach_stockroom_id'] . "'"
        );

        if ($query->row['total'] > 0) {
            return true;
        }

        return false;
    }
}
