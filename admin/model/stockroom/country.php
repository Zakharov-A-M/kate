<?php

class ModelStockroomCountry extends Model
{
    /**
     * Get countries for autocomplete
     *
     * @param array $data
     * @return mixed
     */
    public function getCountriesForAutocomplete($data = array())
    {
        if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "country oc WHERE oc.name LIKE '" . $data['filter_name'] . "%'";

            $sort_data = array(
                'name',
                'iso_code_2',
                'iso_code_3'
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
}
