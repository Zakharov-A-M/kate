<?php

class ModelLocalisationStockroomCountry extends Model
{
    /**
     * @return array
     */
    public function getCountries()
    {
        $countries = array();

        $query = $this->db->query("SELECT sc.country_id AS country_id, sc.name AS name, sc.domain as domain, lang.code AS langCode, curr.code AS currCode FROM " . DB_PREFIX . "stockroom_country sc INNER JOIN " . DB_PREFIX . "language lang ON (sc.language_id = lang.language_id) INNER JOIN " . DB_PREFIX . "currency curr ON (curr.currency_id = sc.currency_id)");

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

    /**
     * Get currency
     *
     * @param string $url
     * @return mixed
     */
    public function getCurrency(string $url)
    {
        $query = $this->db->query("SELECT curr.code AS code FROM " . DB_PREFIX . "stockroom_country sc INNER JOIN " . DB_PREFIX . "currency curr ON (curr.currency_id = sc.currency_id) WHERE sc.domain  = '" . $url . "'");

        return  $query->row['code'];

    }

    /**
     * Get language for stockroom
     *
     * @param string $url
     * @return mixed
     */
    public function getLanguage(string $url)
    {
        $query = $this->db->query("SELECT lang.code AS code FROM " . DB_PREFIX . "language lang INNER JOIN " . DB_PREFIX . "stockroom_country sc ON (sc.language_id = lang.language_id) WHERE sc.domain = '" . $url . "'");

        return  $query->row['code'];

    }

    /**
     * Get country for stockroom
     *
     * @param string $url
     * @return mixed
     */
    public function getCountry(string $url)
    {
        $query = $this->db->query("SELECT sc.country_id AS country_id FROM " . DB_PREFIX . "stockroom_country sc WHERE sc.domain = '" . $url . "'");

        return  $query->row['country_id'];
    }
}
