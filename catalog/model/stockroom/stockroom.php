<?php

class ModelStockroomStockroom extends Model
{
    /**
     * Description stockroom for footer
     * @return array
     */
    public function getStockroomForCountry()
    {
        $query = $this->db->query("SELECT s.stockroom_id AS stockroom_id, s.email AS email, s.phone AS phone, sd.name AS name, sd.address AS address FROM " . DB_PREFIX . "stockroom_country sc LEFT JOIN " . DB_PREFIX . "stockroom s ON (s.stockroom_id = sc.stockroom_id) LEFT JOIN " . DB_PREFIX . "stockroom_description sd ON (sd.stockroom_id = sc.stockroom_id) WHERE sc.country_id = '". $this->config->get('config_current_country')."' AND sd.language_id = '". $this->config->get('config_language_id')."' LIMIT 1");
        $stockroom = [
            'email' => $query->row['email'],
            'stockroom_id' => $query->row['stockroom_id'],
            'phone' => $query->row['phone'],
            'name' => $query->row['name'],
            'address' => $query->row['address'],
        ];

        return $stockroom;
    }
    /**
     * Description stockroom for footer
     * @return array
     */
    public function getStockroomForCountryAll()
    {
        $query = $this->db->query("SELECT s.stockroom_id AS stockroom_id, s.email AS email, s.phone AS phone, sd.name AS name, sd.address AS address FROM " . DB_PREFIX . "stockroom_country sc LEFT JOIN " . DB_PREFIX . "stockroom s ON (s.stockroom_id = sc.stockroom_id) LEFT JOIN " . DB_PREFIX . "stockroom_description sd ON (sd.stockroom_id = sc.stockroom_id) WHERE sc.country_id = '". $this->config->get('config_current_country')."' AND sd.language_id = '". $this->config->get('config_language_id')."' ORDER BY sortname DESC  LIMIT 2");
        $sto = $query->rows;
        $stockroom = array();
        foreach ($sto as $e){
            $stockroom[] = array(
                'email' => $e['email'],
                'stockroom_id' => $e['stockroom_id'],
                'phone' => $e['phone'],
                'name' => $e['name'],
                'address' => $e['address'],
            );
        }
        return $stockroom;
    }

    /**
     * Get all stockrooms attached for country
     *
     * @return mixed
     */
    public function getAttachedStockroomsForCountry()
    {
        $query = $this->db->query("
            SELECT sa.attach_stockroom_id as stockroom_id, sa.description_delivery as delivery
            FROM " . DB_PREFIX . "stockroom_country sc
            LEFT JOIN " . DB_PREFIX . "stockroom s ON (s.stockroom_id = sc.stockroom_id)
            LEFT JOIN " . DB_PREFIX . "stockroom_description sd ON (sd.stockroom_id = sc.stockroom_id)
            INNER JOIN " . DB_PREFIX . "stockroom_attached sa ON (sc.stockroom_id = sa.stockroom_id)
            WHERE sc.country_id = '". $this->config->get('config_current_country')."'
            AND sd.language_id = '". $this->config->get('config_language_id')."'
            ORDER BY sa.sort_order"
        );
        return $query->rows;
    }

    /**
     * Get count product in stockroom ID
     *
     * @param int $stockroomId
     * @param int $productId
     * @return mixed
     */
    public function getCountProductStockroom($stockroomId, $productId)
    {
        $query = $this->db->query("
            SELECT SUM(sn.amount) as amount, s.GUID
            FROM " . DB_PREFIX . "stockroom_nomenclature sn
            LEFT JOIN " . DB_PREFIX . "stockroom s ON (s.stockroom_id = sn.stockroom_id)
            WHERE sn.nomenclature_id = '". (int)$productId ."'
            AND sn.stockroom_id = '". (int)$stockroomId . "'"
        );
        return $query->row;
    }

}