<?php

class ModelCatalogExchange extends Model
{
    const NOMENCLATURE =  SERVER_1C_TEST . 'nomenclature';
    const CATEGORY = SERVER_1C_TEST . 'category';

    public function isConfigured()
    {
        $query = "SELECT * FROM " . DB_PREFIX . "exchange";
        $result = $this->db->query($query);

        return ($result->num_rows > 0) ? true : false;
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

    public function saveToken()
    {
        $token = $this->generateToken();
        $query = "UPDATE " . DB_PREFIX . "exchange SET token='" . $token . "'";
        $this->db->query($query);
    }

    public function saveCredentials($login, $password)
    {
        $query = "UPDATE " . DB_PREFIX . "exchange SET login='" . $login . "', password='" . $password . "'";
        $this->db->query($query);
    }

    public function getData()
    {
        $query = "SELECT * FROM " . DB_PREFIX . "exchange";
        $result = $this->db->query($query);

        return $result->row;
    }


    public function configureModule($login, $password)
    {
        $token = $this->generateToken();
        $this->db->query("INSERT INTO `" . DB_PREFIX . "exchange`  (token,login,password,route,error) values ('" . $token ."','" . $login ."','" . $password ."','" . self::MODULE_ROUTE ."',0)");
        return $this->getData();

    }

    public function moduleSettings($post)
    {
        $login = $post['ex_login'];
        $password = $post['ex_password'];
        if ($this->isConfigured()) {
            $this->saveCredentials($login, $password);
        } else {
            $this->configureModule($login, $password);
        }
    }

    public function getStatistic()
    {
        $sql = "SELECT error, updated_at from " . DB_PREFIX . "exchange";
        $result = $this->db->query($sql);

        return $result->row;
    }

    /**
     * Send request to 1C
     *
     * @param string $exchangeUrl
     * @param array $headers
     * @return mixed
     * @throws Exception
     */
    public function execRequest(string $exchangeUrl, $headers = ['all: 2'])
    {
        $query = "SELECT * FROM " . DB_PREFIX . "exchange";
        $result = $this->db->query($query);
        $login = $result->row['login'];
        $password = $result->row['password'];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $exchangeUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_USERPWD, "$login:$password");
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            throw new Exception('Error http request');
        }

        curl_close($curl);
        $responseInfo = json_decode($response, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new Exception('Invalid json format');
        }

        return $responseInfo;
    }

    /**
     * Get nomenclature
     *
     * @return mixed
     * @throws Exception
     */
    public function getNomenclature()
    {
        $result = $this->execRequest(self::NOMENCLATURE);
        return $result['data'];
    }

    /**
     * Get nomenclature from GUID
     */
    public function getNomenclatureGuid($guid)
    {
        $result = $this->execRequest(self::NOMENCLATURE, ['all: 1', 'GUID_nomenclature:'.$guid]);
        return $result['data'];
    }

    /**
     * Get category
     *
     * @return mixed
     * @throws Exception
     */
    public function getCategory()
    {
        $result = $this->execRequest(self::CATEGORY);
        return $result['data'];
    }
    /**
     * Disable category
     *
     * @return mixed
     * @throws Exception
     */
    public function disableCategory()
    {
        $this->db->query(
            "UPDATE " . DB_PREFIX . "category SET  status = '0', date_modified = NOW() WHERE 1"
        );
        return true;
    }

    /**
     * Parse nomenclature from 1C
     *
     * @param array $responseItem
     * @return array
     */
    public function parseNomenclatureResponse(array $responseItem)
    {
        $this->load->model('catalog/product');
        $data = [];
        $data['GUID'] = $responseItem['GUID_Nomenclature'];
        $data['GUID_Characteristic'] = $responseItem['GUID_Characteristic'];

        $this->load->model('localisation/language');
        $this->load->model('catalog/product');
        $languages = $this->model_localisation_language->getLanguages();
        $flag = true;
        foreach ($responseItem['nameS'] as $nameS) {
            if (empty($nameS['country'])) {
                foreach ($languages as $language) {
                    $data['product_description'][$language['language_id']] = [
                        'name' => $nameS['name'],
                        'description' => $nameS['description'],
                        'meta_title' => $responseItem['nameFull'],
                        'meta_description' => '',
                        'meta_keyword' => $responseItem['nameFull'],
                        'tag' => ''
                    ];
                }
            }
            if ($nameS['country'] == 112) {
                $flag = false;
            }
        }
        foreach ($languages as $language) {
            foreach ($responseItem['nameS'] as $nameS) {
                if ($nameS['country'] == $language['iso']) {
                    $data['product_description'][$language['language_id']] = [
                        'name' => $nameS['name'],
                        'description' => $nameS['description'],
                        'meta_title' => $responseItem['nameFull'],
                        'meta_description' => '',
                        'meta_keyword' => $responseItem['nameFull'],
                        'tag' => ''
                    ];
                }
                if ($nameS['country'] == 643 && $flag) {
                    $data['product_description'][3] = [
                        'name' => $nameS['name'],
                        'description' => $nameS['description'],
                        'meta_title' => $responseItem['nameFull'],
                        'meta_description' => '',
                        'meta_keyword' => $responseItem['nameFull'],
                        'tag' => ''
                    ];
                }
            }
        }

        $data['product_seo_url'][0] = [];
        foreach ($languages as $language) {
            if (!empty($data['product_description'][$language['language_id']]['name'])) {
                $stringUrl = $this->model_catalog_product->cyrillicToLatinaFromRus(
                    $data['product_description'][$language['language_id']]['name']
                );
                $data['product_seo_url'][0][$language['language_id']] = $this->model_catalog_product->clearWasteChars(
                    $stringUrl
                ) . '.html';
            }
        }

        $data['price'] = str_replace(',', '.', $responseItem['prices'][0]['price']);
        $this->load->model('localisation/currency');
        $currencies = $this->model_localisation_currency->getCurrencies();
        $data['product_special'] = [];
        $this->load->model('customer/customer_group');
        $this->load->model('customer/customer');
        $data['currency'] = 'USD';
        foreach ($responseItem['prices'] as $price) {
            foreach ($currencies as $currency) {
                if ($currency['iso'] == $price['country']) {
                    $dataPrice = round((float)str_replace(',', '.', $price['price']), $currency['decimal_place']);
                    if (!empty($price['discount'])) {
                        $priceSpecial = round((float)$dataPrice - ((float)$dataPrice / 100 * abs($price['discount'])), $currency['decimal_place']);
                        if ($price['ContractGUID'] == '00000000-0000-0000-0000-000000000000') {
                            if ($this->model_customer_customer_group->getTotalCustomerGroups() > 0) {
                                foreach ($this->model_customer_customer_group->getAllCustomerGroup() as $group) {
                                    $data['product_special'][] = [
                                        'customer_group_id' => $group['customer_group_id'],
                                        'priority' => 1,
                                        'customer_id' => NULL,
                                        'price' => $priceSpecial,
                                        'date_start' => date("Y-m-d"),
                                        'date_end' => date("Y-m-d", strtotime("+3 month"))
                                    ];
                                }
                            }
                        } else {
                                $data['product_special'][] = [
                                    'customer_group_id' => 2,
                                    'priority' => 1,
                                    'customer_guid' => $price['ContractGUID'],
                                    'customer_id' => 0,
                                    'price' => $priceSpecial,
                                    'date_start' => date("Y-m-d"),
                                    'date_end' => date("Y-m-d", strtotime("+3 month"))
                                ];
                        }
                    }else{
                        $data['currency'] = $currency['code'];
                        $data['price'] = round((float)str_replace(',', '.', $price['price']), $currency['decimal_place']);
                    }
                }
            }
        }

        if (!empty($responseItem['analogue']) && !empty($responseItem['analogue'][0]['GUIDAnalog'])) {
            $data['product_analog'] = $responseItem['analogue'][0]['GUIDAnalog'];
        }

        if (!empty($responseItem['joinProducts'])) {
            $data['product_related'] = [];
            foreach ($responseItem['joinProducts'] as $joinProduct) {
                if (!empty($joinProduct['GUID_Nomenclature'])) {
                    $product = $this->model_catalog_product->getProductsByGUID($joinProduct['GUID_Nomenclature']);
                    if (!empty($product['product_id'])) {
                        array_push($data['product_related'], $product['product_id']);
                    }
                }
            }
        }

        $this->load->model('catalog/manufacturer');

        if ($this->model_catalog_manufacturer->getTotalManufacturerByGUID($responseItem['manufacturerGUID']) > 0) {
            $data['manufacturer_id'] = $this->model_catalog_manufacturer->getManufacturerByGUID($responseItem['manufacturerGUID']);
        } else {
            $data['manufacturer_id'] =  $this->model_catalog_manufacturer->addManufacturer([
                'name' => $responseItem['manufacturer'],
                'sort_order' => 0,
                'manufacturer' => $responseItem['manufacturer'],
                'GUID' => $responseItem['manufacturerGUID']
            ]);
        }

        $data['model'] = $responseItem['Marke'];
        if (!empty($responseItem['vendorCode'])) {
            $data['vendorCode'] = $responseItem['vendorCode'];
        }

        $this->load->model('catalog/category');

        if ($this->model_catalog_category->getTotalCategoryByGUID($responseItem['categoryGUID']) > 0) {
            $result = $this->model_catalog_category->getCategoryByGUID($responseItem['categoryGUID']);
            $data['product_category'] = [
                $result
            ];
        }

        $data['pr'] = [
            'pr1' => $responseItem['pr1'],
            'pr2' => $responseItem['pr2'],
            'pr3' => $responseItem['pr3'],
            'sf1' => $responseItem['sf1'],
            'sf2' => $responseItem['sf2'],
            'sf3' => $responseItem['sf3'],
            'sf4' => $responseItem['sf4'],
            'sf5' => $responseItem['sf5'],
            'sf6' => $responseItem['sf6']
        ];

        $data['image'] = !empty($responseItem['basicimage'])&&$responseItem['basicimage']!='1c_Images/' ?
            'catalog/'.  str_replace('\\', '/', $responseItem['basicimage']):
            '';

        if (!empty($responseItem['images']) && isset($responseItem['images'])) {
            $data['product_image'] = [];
            foreach ($responseItem['images'] as $item) {
                if (!empty($item['ПутьКФайлу'])) {
                    $image = str_replace('\\', '/', $item['ПутьКФайлу']);
                    array_push($data['product_image'], [
                        'image' => 'catalog/'. $image,
                        'sort_order' =>  0
                    ]);
                }
            }
        }

        $data['sku'] = '';
        $data['upc'] = '';
        $data['ean'] = '';
        $data['jan'] = '';
        $data['isbn'] = '';
        $data['mpn'] = '';
        $data['location'] = '';
        $data['unit_measure'] = '';
        $data['tax_class_id'] = '0';
        $data['quantity'] = '1';
        $data['minimum'] = '1';
        $data['subtract'] = '1';
        $data['stock_status_id'] = '6';
        $data['shipping'] = '1';
        $data['date_available'] = date('Y-m-d');
        $data['length'] = (!empty((float)$responseItem['length']) ? (float)str_replace(',', '.', $responseItem['length']) : '');
        $data['width'] = (!empty((float)$responseItem['width']) ? (float)str_replace(',', '.', $responseItem['width']) : '');
        $data['height'] = (!empty((float)$responseItem['height']) ? (float)str_replace(',', '.', $responseItem['height']) : '');
        $data['length_class_id'] = '1';
        $data['weight'] = (!empty($responseItem['weight']) ? $responseItem['weight'] : '');
        $data['scope'] = (!empty($responseItem['Scope']) ? $responseItem['Scope'] : '');
        $data['lengthString'] = (!empty($responseItem['lengthString']) ? $responseItem['lengthString'] : '');
        $data['weight_class_id'] = '1';
        $data['status'] = '1';
        $data['category'] = '';
        $data['filter'] = '';
        $data['product_store'] = [
            0 => '0'
        ];
        $data['download'] = '';
        $data['related'] = '';
        $data['points'] = '';
        $data['product_reward'] = [
            1 => [
                'points' => ''
            ],
        ];

        $data['product_layout'] = [
            0 => ''
        ];

        if ($this->model_catalog_product->getTotalProductsByGUIDCharacteristic($data['GUID'], $data['GUID_Characteristic']) > 0) {
            $this->model_catalog_product->editProduct(
                $this->model_catalog_product->getProductByGUID(
                    $data['GUID'],
                    $data['GUID_Characteristic']
                ),
                $data
            );
        } else {
            $data['sort_order'] = '1';
            $this->model_catalog_product->addProduct($data);
        }

        return $data;
    }

    /**
     * Parse json category
     *
     * @param array $responseItem
     * @return array|bool
     */
    public function parseCategoryResponse(array $responseItem)
    {
        $this->load->model('catalog/category');
        $data = [];

        $data['GUID'] = $responseItem['categoryGUID'];

        if (!empty($responseItem['parentGUID']) && isset($responseItem['parentGUID']) && $responseItem['parentGUID'] != '00000000-0000-0000-0000-000000000000') {
            $data['parent_id'] = 0;
            if ($this->model_catalog_category->getTotalCategoryByGUID($responseItem['parentGUID']) > 0) {
                $data['parent_id'] = $this->model_catalog_category->getCategoryByGUID($responseItem['parentGUID']);
            }
            $data['top'] = 0;
        } else {
            $data['top'] = 1;
            $data['parent_id'] = 0;
        }

        $data['column'] = 0;
        $data['status'] = ($responseItem['show']=="true" ? 1 : 0);
        $data['image'] = '';
        $data['category_description'] = [];

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        $flag = true;
        foreach ($languages as $language) {
            $data['category_description'][$language['language_id']] = [
                'name' => $responseItem['categoryName'],
                'description' => '',
                'meta_title' => $responseItem['categoryName'],
                'meta_description' => '',
                'meta_keyword' => ''
            ];
        }

        if (!empty($responseItem['names'])) {
            foreach ($responseItem['names'] as $item) {
                if ($item['country'] == 112) {
                    $flag = false;
                }
            }
            foreach ($languages as $language) {
                foreach ($responseItem['names'] as $name) {
                    if ($name['country'] == $language['iso']) {
                        $data['category_description'][$language['language_id']] = [
                            'name' => $name['name'],
                            'description' => '',
                            'meta_title' => $name['name'],
                            'meta_description' => '',
                            'meta_keyword' => ''
                        ];
                    }
                    if ($name['country'] == 643 && $flag) {
                        $data['category_description'][20] = [
                            'name' => $name['name'],
                            'description' => '',
                            'meta_title' => $name['name'],
                            'meta_description' => '',
                            'meta_keyword' => ''
                        ];
                    }
                }
            }
        }

        $data['category_store'] = [
            0
        ];

        if ($this->model_catalog_category->getTotalCategoryByGUID($data['GUID']) > 0) {
            $this->model_catalog_category->editCategory($this->model_catalog_category->getCategoryByGUID($data['GUID']), $data);
        } else {
            $this->model_catalog_category->addCategory($data);
        }

        return $data;
    }
}
