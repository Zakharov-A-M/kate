<?php
class ModelLocalisationCountry extends Model {
	public function getCountry($country_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE country_id = '" . (int)$country_id . "' AND status = '1'");

		return $query->row;
	}

	public function getCountries() {
		$country_data = $this->cache->get('country.catalog');

		if (!$country_data) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE status = '1' ORDER BY name ASC");

			$country_data = $query->rows;

			$this->cache->set('country.catalog', $country_data);
		}

		return $country_data;
	}

    /**
     * Get language for stockroom
     *
     * @param string $url
     * @return mixed
     */
    public function getLanguage(string $url)
    {
        $query = $this->db->query("SELECT lang.code AS code FROM " . DB_PREFIX . "country c INNER JOIN " . DB_PREFIX . "language lang ON (c.language_id = lang.language_id) WHERE c.domain = '" . $url . "'");

        return  $query->row['code'];

    }

    /**
     * Get currency
     *
     * @param string $url
     * @return mixed
     */
    public function getCurrency(string $url)
    {
        $query = $this->db->query("SELECT curr.code AS code FROM " . DB_PREFIX . "country c INNER JOIN " . DB_PREFIX . "currency curr ON (curr.currency_id = c.currency_id) WHERE c.domain  = '" . $url . "'");

        return  $query->row['code'];

    }

    /**
     * Get country ISO
     *
     * @param string $url
     * @return mixed
     */
    public function getCountryIso(string $url)
    {
        $query = $this->db->query("SELECT c.country_id AS country_id FROM " . DB_PREFIX . "country c WHERE c.domain  = '" . $url . "'");

        return  $query->row['country_id'];
    }

    /**
     * Get country from iso
     *
     * @param int $id
     * @return mixed
     */
    public function getCountryFromIso(int $id)
    {
        $query = $this->db->query("SELECT c.iso AS iso FROM " . DB_PREFIX . "country c WHERE c.country_id  = '" . $id . "'");

        return  $query->row['iso'];
    }

    /**
     * @return array
     */
    public function getCountriesForMenu()
    {
        $countries = array();

        $query = $this->db->query("SELECT c.country_id as country_id, cd.title AS name, c.domain as domain, lang.code AS langCode, curr.code AS currCode FROM " . DB_PREFIX . "country c INNER JOIN " . DB_PREFIX . "language lang ON (c.language_id = lang.language_id) INNER JOIN " . DB_PREFIX . "currency curr ON (curr.currency_id = c.currency_id) INNER JOIN " . DB_PREFIX . "country_description cd ON (cd.country_id = c.country_id)  WHERE c.language_id = cd.language_id");

        foreach ($query->rows as $result) {
            $countries[] = [
                'country_id'  => $result['country_id'],
                'name'        => $result['name'],
                'langCode'    => $result['langCode'],
                'currCode'    => $result['currCode'],
                'domain'      => $result['domain']
            ];
        }

        return $countries;
    }

}
