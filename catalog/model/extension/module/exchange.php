<?php

class ModelExtensionModuleExchange extends Model
{
    const NOMENCLATURE = "http://86.57.128.226:8085/KA_TEST/hs/DataExchangeSite/643/testSite/nomenclature";
    const PRICE = "http://86.57.128.226:8085/KA_TEST/hs/DataExchangeSite/643/testSite/presence";

    public function isConfigured()
    {
        $query = "SELECT * FROM " . DB_PREFIX . "exchange";
        $result = $this->db->query($query);

        return $result ? true : false;
    }

    public function generateToken()
    {
        $chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
        $max=32;
        $size=StrLen($chars)-1;
        $password=null;
        while($max--) {
            $password .= $chars[rand(0,$size)];
        }
        return $password;
    }

    public function getData()
    {
        $query = "SELECT * FROM " . DB_PREFIX . "exchange";
        $result = $this->db->query($query);

        return $result->row;
    }

    /**
     * Send request to 1C
     *
     * @param $exchangeUrl
     * @return mixed|string
     */
    public function execRequest(string $exchangeUrl)
    {
        $query = "SELECT * FROM " . DB_PREFIX . "exchange";
        $result = $this->db->query($query);
        $login = $result->row['login'];
        $password = $result->row['password'];

        $result = 'CURL ERROR';
        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $exchangeUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_USERPWD, "$login:$password");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            $result = curl_exec($curl);
            $result = json_decode($result, true);
        }

        return $result;
    }

    /**
     * Get nomenclature
     *
     * @return string
     */
    public function getNomenclature()
    {
        return $this->execRequest(self::NOMENCLATURE);
    }

    public function auth(string $token)
    {
        $query = "SELECT * FROM " . DB_PREFIX . "exchange";
        $result = $this->db->query($query);
        $exchangeToken = $result->row['token'];

        return ($token === $exchangeToken);
    }

    public function updateStatus(bool $error)
    {
        $query = "UPDATE " . DB_PREFIX . "exchange SET error='" . $error ."', updated_at=now()";
        $this->db->query($query);
    }

    public function getProducts()
    {
        $result = $this->getNomenclature();
        return $result['data'];
    }

    public function parseExchangeResponse(array $responseItem)
    {
        $data = [];
        $data['GUID'] = $responseItem['GUID_Nomenclature'];
        $data['product_description'] = [
            2 => [
                'name' => $responseItem['nomenclatureName'],
                'description' => $responseItem['description'],
                'meta_title' => $responseItem['nomenclatureName'],
                'meta_description' => '',
                'meta_keyword' => '',
                'tag' => ''
            ],
        ];
        $data['model'] = $responseItem['nomenclatureName'];
        $data['sku'] = '';
        $data['upc'] = '';
        $data['ean'] = '';
        $data['jan'] = '';
        $data['isbn'] = '';
        $data['mpn'] = '';
        $data['location'] = '';
        $data['price'] = '';
        $data['tax_class_id'] = '0';
        $data['quantity'] = '1';
        $data['minimum'] = '1';
        $data['subtract'] = '1';
        $data['stock_status_id'] = '6';
        $data['shipping'] = '1';
        $data['date_available'] = date('Y-m-d');
        $data['length'] = '';
        $data['width'] = '';
        $data['height'] = '';
        $data['length_class_id'] = '1';
        $data['weight'] = '';
        $data['weight_class_id'] = '1';
        $data['status'] = '1';
        $data['sort_order'] = '1';
        $data['manufacturer'] = '';
        $data['manufacturer_id'] = '0';
        $data['category'] = '';
        $data['product_category'] = [
            0 => '60'
        ];
        $data['filter'] = '';
        $data['product_store'] = [
            0 => '0'
        ];
        $data['download'] = '';
        $data['related'] = '';
        $data['image'] = '';
        $data['points'] = '';
        $data['product_reward'] = [
            1 => [
                'points' => ''
            ],
        ];
        $data['product_seo_url'] = [
            0 => [
                2 => '',
                1 => '',
            ],
        ];
        $data['product_layout'] = [
            0 => ''
        ];

        return $data;
    }

}