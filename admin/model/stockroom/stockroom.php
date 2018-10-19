<?php

class ModelStockroomStockroom extends Model
{
    /**
     * Add stockroom
     *
     * @param $data
     * @return mixed
     */
	public function addStockroom($data)
    {
        $queryCountry = $this->db->query("SELECT c.country_id AS country_id FROM " . DB_PREFIX . "country c WHERE c.iso  = '" . $data['country_id'] . "'");
        $countryId =  $queryCountry->row['country_id'];

        $this->db->query("INSERT INTO " . DB_PREFIX . "stockroom SET phone = '" . $data['phone'] . "', `email` = '" . $data['email'] . "'");
        $stockroom_id = $this->db->getLastId();

        $this->db->query("INSERT INTO " . DB_PREFIX . "stockroom_country SET country_id = '" . $countryId . "', stockroom_id = '" . $stockroom_id . "'");


        $query = $this->db->query("SELECT lang.language_id FROM " . DB_PREFIX . "language lang");

        foreach ($query->rows as $language) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "stockroom_description SET language_id = '" . $language['language_id'] . "', stockroom_id = '" . $stockroom_id . "', name = '" . $data['name'] . "', address = '" . $data['address'] . "'");
        }

		if (isset($data['GUID']) && !empty($data['GUID'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "stockroom SET GUID = '" . $data['GUID'] . "' WHERE stockroom_id = '" . (int)$stockroom_id . "'");
        }
		return true;
	}

    /**
     * Add stockroom for form
     *
     * @param $data
     * @return mixed
     */
    public function addStockroomForm($data)
    {
        $queryCountry = $this->db->query("SELECT c.country_id AS country_id FROM " . DB_PREFIX . "country c WHERE c.iso  = '" . $data['country_id'] . "'");
        $countryId =  $queryCountry->row['country_id'];
        $sortname = 0;
        if (!empty($data['sortname'])){
            $sortname = $data['sortname'];
        }
        $this->db->query("INSERT INTO " . DB_PREFIX . "stockroom SET phone = '" . $data['phone'] . "', email = '" . $data['email'] . "', sortname='".$sortname."'");
        $stockroom_id = $this->db->getLastId();

        $this->db->query("INSERT INTO " . DB_PREFIX . "stockroom_country SET country_id = '" . $data['country_id'] . "', stockroom_id = '" . $stockroom_id . "'");

        foreach ($data['stockroom_description'] as $key => $description) {
             $this->db->query("INSERT INTO " . DB_PREFIX . "stockroom_description SET language_id = '" . $key . "', stockroom_id = '" . $stockroom_id . "', name = '" . $description['name'] . "', address = '" . $description['address'] . "'");
        }

        if (isset($data['GUID']) && !empty($data['GUID'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "stockroom SET GUID = '" . $data['GUID'] . "' WHERE stockroom_id = '" . (int)$stockroom_id . "'");
        }
        return true;
    }

    /**
     * Edit stockroom
     *
     * @param $stockroom_id
     * @param $data
     */
    public function editStockroomForm($stockroom_id, $data)
    {
        $sortname = 0;
        if (!empty($data['sortname'])){
            $sortname = $data['sortname'];
        }
        $this->db->query("UPDATE " . DB_PREFIX . "stockroom SET email = '" . $data['email'] . "', sortname = '" . $sortname . "', phone = '" . $data['phone'] . "' WHERE stockroom_id = '" . (int)$stockroom_id . "'");
        $id = $this->db->getLastId();

        foreach ($data['stockroom_description'] as $key => $description) {
            $this->db->query("UPDATE " . DB_PREFIX . "stockroom_description SET language_id = '" . $key . "', stockroom_id = '" . $stockroom_id . "', name = '" . $description['name'] . "', address = '" . $description['address'] . "' WHERE stockroom_id = '" . $stockroom_id . "' AND language_id = '" . $key . "'");
        }

        $this->db->query("UPDATE " . DB_PREFIX . "stockroom_country SET country_id = '" . $data['country_id'] . "' WHERE stockroom_id = '" . (int)$stockroom_id . "'");


        if (isset($data['GUID']) && !empty($data['GUID'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "stockroom SET GUID = '" . $data['GUID'] . "' WHERE stockroom_id = '" . (int)$stockroom_id . "'");
        }
    }

    /**
     * Edit stockroom
     *
     * @param $stockroom_id
     * @param $data
     */
	public function editStockroom($stockroom_id, $data)
    {
		$this->db->query("UPDATE " . DB_PREFIX . "stockroom SET email = '" . $data['email'] . "', phone = '" . $data['phone'] . "' WHERE stockroom_id = '" . (int)$stockroom_id . "'");

		$query = $this->db->query("SELECT lang.language_id FROM " . DB_PREFIX . "language lang");

        foreach ($query->rows as $language) {
            $this->db->query("UPDATE " . DB_PREFIX . "stockroom_description SET stockroom_id = '" . $stockroom_id . "', name = '" . $data['name'] . "', address = '" . $data['address'] . "' WHERE stockroom_id = '" . (int)$stockroom_id ."' AND language_id = '" . $language['language_id'] . "'");
        }

        $queryCountry = $this->db->query("SELECT c.country_id AS country_id FROM " . DB_PREFIX . "country c WHERE c.iso  = '" . $data['country_id'] . "'");
        $countryId =  $queryCountry->row['country_id'];

        $this->db->query("UPDATE " . DB_PREFIX . "stockroom_country SET country_id = '" . $countryId . "' WHERE stockroom_id = '" . $stockroom_id . "'");


        if (isset($data['GUID']) && !empty($data['GUID'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "stockroom SET GUID = '" . $data['GUID'] . "' WHERE stockroom_id = '" . (int)$stockroom_id . "'");
        }
    }

    /**
     * Delete stockroom
     *
     * @param $stockroom_id
     */
	public function deleteStockroom($stockroom_id)
    {
		$this->db->query("DELETE FROM " . DB_PREFIX . "stockroom WHERE stockroom_id = '" . (int)$stockroom_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "stockroom_country WHERE stockroom_id = '" . (int)$stockroom_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "stockroom_description WHERE stockroom_id = '" . (int)$stockroom_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "stockroom_nomenclature WHERE stockroom_id = '" . (int)$stockroom_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "stockroom_attached WHERE stockroom_id = '" . (int)$stockroom_id . "'");
        $this->cache->deleteCategoryForCountry();
    }

    /**
     * Get this stockroom
     *
     * @param $stockroom_id
     * @return mixed
     */
    public function getStockroom($stockroom_id)
    {
        $query = $this->db->query("SELECT s.stockroom_id AS stockroom_id, s.email AS email, s.phone AS phone, c.name AS country_name, sc.country_id AS country_id, s.sortname AS sortname FROM " . DB_PREFIX . "stockroom s LEFT JOIN " . DB_PREFIX . "stockroom_country sc ON (s.stockroom_id = sc.stockroom_id) LEFT JOIN " . DB_PREFIX . "country c ON (c.country_id = sc.country_id)  WHERE s.stockroom_id = '" . (int)$stockroom_id . "'");

        return $query->row;
    }

    /**
     * @param $stockroom_id
     * @return mixed
     */
    public function getStockroomDescription($stockroom_id)
    {
        $query = $this->db->query("SELECT sd.language_id AS language_id, sd.name AS name, sd.address AS address  FROM " . DB_PREFIX . "stockroom_description sd WHERE sd.stockroom_id = '" . (int)$stockroom_id . "'");

        return $query->rows;
    }


    /**
     * Get name stockroom
     *
     * @param $stockroom_id
     * @return mixed
     */
    public function getStockroomName($stockroom_id)
    {
        $query = $this->db->query("SELECT sd.name AS name FROM " . DB_PREFIX . "stockroom_description sd WHERE sd.stockroom_id = '" . (int)$stockroom_id . "' AND sd.language_id = '" . (int)$this->config->get('config_language_id')  . "'");

        return $query->row['name'];
    }

    /**
     * Get address stockroom
     *
     * @param $stockroom_id
     * @return mixed
     */
    public function getStockroomAddress($stockroom_id)
    {
        $query = $this->db->query("SELECT  st.address AS address FROM " . DB_PREFIX . "stockroom st WHERE st.stockroom_id = '" . (int)$stockroom_id . "'");

        return $query->row['address'];
    }

    /**
     * @param array $data
     * @return mixed
     */
	public function getStockrooms($data = array())
    {
        $sql = "SELECT st.stockroom_id AS stockroom_id, sd.name AS name, st.GUID AS GUID, sc.country_id AS country_id, c.name AS country_name, sd.address AS address, st.sortname AS sortname  FROM " . DB_PREFIX . "stockroom st LEFT JOIN " . DB_PREFIX . "stockroom_country sc ON (st.stockroom_id = sc.stockroom_id) LEFT JOIN " . DB_PREFIX . "country c ON (c.country_id = sc.country_id)  LEFT JOIN " . DB_PREFIX . "stockroom_description sd ON (sd.stockroom_id = st.stockroom_id)";


		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '%" . $this->db->escape((string)$data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY st.stockroom_id";

		$sort_data = array(
			'name',
			'country_name',
			'address'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			//$sql .= " ORDER BY sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			//$sql .= " ASC";
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
     * Get count stockroom
     *
     * @return mixed
     */
	public function getTotalStockrooms()
    {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stockroom");

		return $query->row['total'];
	}

    /**
     * Get count stockroom from GUID
     *
     * @param $guid
     * @return mixed
     */
    public function getTotalStockroomsGUID($guid)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stockroom WHERE GUID = '" .$guid . "'");

        return (int)$query->row['total'];
    }

    /**
     * Get this stockroom from GUID
     *
     * @param $guid
     * @return mixed
     */
    public function getStockroomGUID($guid)
    {
        $query = $this->db->query("SELECT s.stockroom_id AS stockroom_id FROM " . DB_PREFIX . "stockroom s WHERE s.GUID = '" . $guid . "'");

        return (int)$query->row['stockroom_id'];
    }
}
