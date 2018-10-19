<?php

class ModelDomainDomain extends Model
{
    /**
     * Edit domain
     *
     * @param $country_id
     * @param $data
     */
	public function editDomain($country_id, $data)
    {
		$this->db->query("UPDATE " . DB_PREFIX . "country 
		SET domain = '" . $data['domain'] . "', 
		currency_id = '" . (int)$data['currency_id'] . "' 
		WHERE country_id = '" . (int)$country_id . "'");

		if (!empty($data['address'])||!empty($data['phone'])){
            $this->db->query("INSERT INTO " . DB_PREFIX . "address_country  
		(country_id, address, phone, email, address2, phone2, email2) 
		VALUES('".(int)$country_id."', '".
                $this->db->escape($data['address'])."', '".
                    $this->db->escape($data['phone'])."', '".
                        $this->db->escape($data['email'])."', '".
                            $this->db->escape($data['address2'])."', '".
                                $this->db->escape($data['phone2'])."', '".
                                    $this->db->escape($data['email2'])."') 
		ON DUPLICATE KEY UPDATE 
		address= '" . $this->db->escape($data['address']) . "',
		phone= '" . $this->db->escape($data['phone']) . "',
		email= '" . $this->db->escape($data['email']) . "',
		address2= '" . $this->db->escape($data['address2']) . "',
		phone2= '" . $this->db->escape($data['phone2']) . "',
		email2= '" . $this->db->escape($data['email2']) . "'");
        }


	}

    /**
     * Get this country
     *
     * @param $country_id
     * @return mixed
     */
	public function getDomain($country_id)
    {
		$query = $this->db->query("SELECT 
                  c.country_id AS country_id, 
                  c.currency_id AS currency_id, 
                  ac.address AS address, 
                  ac.phone AS phone, 
                  ac.email AS email,
                  ac.address2 AS address2, 
                  ac.phone2 AS phone2, 
                  ac.email2 AS email2, 
                  curr.title AS currency_title, 
                  c.domain AS domain 
                  FROM " . DB_PREFIX . "country c 
                  LEFT JOIN " . DB_PREFIX . "currency curr ON (c.currency_id = curr.currency_id) 
                  LEFT JOIN " . DB_PREFIX . "address_country ac ON (c.country_id = ac.country_id) 
                  WHERE c.country_id = '" . (int)$country_id . "'");
		
		return $query->row;
	}

    /**
     * @param array $data
     * @return mixed
     */
	public function getDomains($data = array())
    {
        $sql = "SELECT  c.country_id AS country_id, cd.title AS country_name, c.domain AS domain, lang.name AS language_name, curr.title AS currency_title FROM " . DB_PREFIX . "country c LEFT JOIN " . DB_PREFIX . "language lang ON (c.language_id = lang.language_id) LEFT JOIN " . DB_PREFIX . "currency curr ON (c.currency_id = curr.currency_id) LEFT JOIN " . DB_PREFIX . "country_description cd ON (c.country_id = cd.country_id) WHERE cd.language_id ='" . (int)$this->config->get('config_language_id') . "'";


		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '%" . $this->db->escape((string)$data['filter_name']) . "%'";
		}

		//$sql .= " GROUP BY cp.category_id";

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
     * Get count domains
     *
     * @return mixed
     */
	public function getTotalDomains()
    {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "country c WHERE c.domain IS NOT NULL");

		return $query->row['total'];
	}

    /**
     * Get nomenclature for currency
     *
     * @param array $data
     * @return mixed
     */
    public function getCurrencyForAutocomplete($data = array())
    {
        if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "currency curr  WHERE  curr.title LIKE '%" . $data['filter_name'] . "%'";

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
