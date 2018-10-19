<?php
class ModelCatalogProduct extends Model {

    /**
     * Safe product from file xml
     *
     * @param $product
     * @return bool
     */
    public function addProductFromFile($product)
    {
        $data = [];

        if ($this->getTotalProductsByGUID($product->UUID) > 0) {
            return true;
        }

        $this->load->model('catalog/manufacturer');

        $manufacturer = $this->model_catalog_manufacturer->getManufacturerByName($product->Proizvoditel);

        $data['GUID'] = $product->UUID;
        $data['manufacturer_id'] = $manufacturer[0]['manufacturer_id'] ?? '';
        $data['price'] = $product->Price ?? '';
        $data['product_discount'][0] = $product->Skidka ?? '';
        $data['quantity'] = $product->Ostatok ?? '';
        $data['weight'] =  (float)$product->Ves ?? '';
        $data['length']  = (float)$product->Dlina ?? '';
        $data['width']  = (float)$product->Shirina ?? '';
        $data['height'] = (float)$product->Visota ?? '';
        $data['currency'] = !empty($product->ValPrice) ? $product->ValPrice : 'USD';
        for ($i = 1; $i < 11; $i++) {
            $varname = '';
            $$varname = 'pic_' . $i;
            if (!empty($product->$$varname) && isset($product->$$varname)) {
                $data['product_image'][] = [
                'image' => $product->$$varname,
                'sort_order' => 0
               ];
            }
        }

        $data['product_description'] = [
            2 => [
                'name' => $product->Name,
                'description' => $product->Opisanie,
                'meta_title' => $product->PechatnayaForma,
                'meta_description' => '',
                'meta_keyword' => '',
                'tag' => ''
            ],
        ];

        if ($product->Skidka) {
            $data['product_discount'] = [
                [
                    'customer_group_id' => 0,
                    'quantity' => $product->Ostatok ?? 0,
                    'priority' => 0,
                    'price' => abs($product->Skidka) ?? 0,
                    'date_start' => date("d.m.y"),
                    'date_end' => date("d.m.y")
                ]
            ];
        }

        if (!empty($product->Analog)) {
            $data['product_analog'] = [];
            $analogs = explode(",", $product->Analog);
            foreach ($analogs as $analog) {
                $product_analog = $this->getProductsByUUID($analog);
                if (isset($product_analog[0]['GUID'])) {
                    array_push($data['product_analog'], $product_analog[0]['product_id']);
                }
            }
        }

        if (!empty($product->SopTovari)) {
            $data['product_related'] = [];
            $sopProducts = explode(",", $product->SopTovari);
            foreach ($sopProducts as $sopProduct) {
                $product_soput = $this->getProductsByUUID($sopProduct);
                if (isset($product_soput[0]['GUID'])) {
                    array_push($data['product_related'], $product_soput[0]['product_id']);
                }
            }
        }

        if (!empty($product->IDCategory)) {
            $this->load->model('catalog/category');
            $categoryID = $this->model_catalog_category->getCategoryByGUID($product->IDCategory);
            $data['product_category'] = [];
            if (isset($categoryID[0]['category_id'])) {
                array_push($data['product_category'], $categoryID[0]['category_id']);
            }
        }

        $data['model'] = '';
        $data['sku'] = '';
        $data['unit_measure'] = $product->Edinica ?? '';
        $data['upc'] = '';
        $data['ean'] = '';
        $data['jan'] = '';
        $data['isbn'] = '';
        $data['mpn'] = '';
        $data['location'] = '';
        $data['tax_class_id'] = '0';
        $data['minimum'] = '1';
        $data['subtract'] = '1';
        $data['stock_status_id'] = '6';
        $data['shipping'] = '1';
        $data['date_available'] = date('Y-m-d');
        $data['length_class_id'] = '1';
        $data['weight_class_id'] = '1';
        $data['status'] = '1';
        $data['sort_order'] = '1';



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

        $this->addProduct($data);
        return true;
    }

    /**
     * Add new product
     *
     * @param $data
     * @return mixed
     */
	public function addProduct($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape((string)$data['model']) . "', sku = '" . $this->db->escape((string)$data['sku']) . "', GUID = '" . $this->db->escape((string)$data['GUID']) . "', upc = '" . $this->db->escape((string)$data['upc']) . "', ean = '" . $this->db->escape((string)$data['ean']) . "', jan = '" . $this->db->escape((string)$data['jan']) . "', isbn = '" . $this->db->escape((string)$data['isbn']) . "', mpn = '" . $this->db->escape((string)$data['mpn']) . "', location = '" . $this->db->escape((string)$data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape((string)$data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . $data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', currency = '" . $data['currency'] . "', unit_measure = '" . $data['unit_measure'] . "', scope = '" . $data['scope'] . "', lengthString = '" . $data['lengthString'] . "',  date_added = NOW(), date_modified = NOW()");

        $product_id = $this->db->getLastId();

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape((string)$data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }

        if (isset($data['vendorCode'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET vendorCode = '" . $this->db->escape((string)$data['vendorCode']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }

        if (isset($data['GUID_Characteristic'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET GUID_Characteristic = '" . $this->db->escape((string)$data['GUID_Characteristic']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }

        foreach ($data['product_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
        }

        if (isset($data['product_store'])) {
            foreach ($data['product_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        if (isset($data['product_attribute'])) {
            foreach ($data['product_attribute'] as $product_attribute) {
                if ($product_attribute['attribute_id']) {
                    // Removes duplicates
                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");
                    foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
                        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
                    }
                }
            }
        }

        if (isset($data['product_recurring'])) {
            foreach ($data['product_recurring'] as $product_recurring) {
                if (isset($product_recurring['recurring_id'])) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "product_recurring` SET `product_id` = " . (int)$product_id . ", customer_group_id = " . (int)$product_recurring['customer_group_id'] . ", `recurring_id` = " . (int)$product_recurring['recurring_id']);
                }
            }
        }

        if (isset($data['product_discount'])) {
            foreach ($data['product_discount'] as $product_discount) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "', discount = '" . $this->db->escape($product_discount['discount']) . "'");
            }
        }

        if (isset($data['product_special'])) {
            foreach ($data['product_special'] as $product_special) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_special 
                SET product_id = '" . (int)$product_id . "', 
                customer_group_id = '" . (int)$product_special['customer_group_id'] . "', 
                priority = '" . (int)$product_special['priority'] . "', 
                price = '" . (float)$product_special['price'] . "', 
                date_start = '" . $this->db->escape($product_special['date_start']) . "', 
                date_end = '" . $this->db->escape($product_special['date_end']) . "', 
                customer_guid = '" . $this->db->escape($product_special['customer_guid']) . "', 
                customer_id = '" . $this->db->escape($product_special['customer_id']) . "'");
            }
        }

        if (isset($data['product_image'])) {
            foreach ($data['product_image'] as $product_image) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
            }
        }

        if (isset($data['product_download'])) {
            foreach ($data['product_download'] as $download_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
            }
        }

        if (isset($data['product_category'])) {
            foreach ($data['product_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
            }
        }

        if (isset($data['product_filter'])) {
            foreach ($data['product_filter'] as $filter_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
            }
        }

        if (isset($data['product_related'])) {
            foreach ($data['product_related'] as $related_id) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
            }
        }

        if (isset($data['product_analog'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET analog_group = '" . $data['product_analog'] . "' WHERE product_id = '" . (int)$product_id . "'");
        }

        if (isset($data['product_reward'])) {
            foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
                if ((int)$product_reward['points'] > 0) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward['points'] . "'");
                }
            }
        }

        // SEO URL
        if (isset($data['product_seo_url'])) {
            foreach ($data['product_seo_url'] as $store_id => $language) {
                foreach ($language as $language_id => $keyword) {
                    if (!empty($keyword)) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($keyword) . "'");
                    }
                }
            }
        }

        if (isset($data['product_layout'])) {
            foreach ($data['product_layout'] as $store_id => $layout_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
            }
        }

        if (isset($data['pr'])) {
            $this->load->model('catalog/product_factor');
            $this->model_catalog_product_factor->addProductFactor($product_id, $data['pr']);
        }

        $this->cache->delete('product');
        $this->cache->deleteCategoryForCountry();
        return $product_id;
	}

    /**
     * Edit $product_id product
     *
     * @param $product_id
     * @param $data
     */
	public function editProduct($product_id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->db->escape((string)$data['model']) . "', sku = '" . $this->db->escape((string)$data['sku']) . "', upc = '" . $this->db->escape((string)$data['upc']) . "', ean = '" . $this->db->escape((string)$data['ean']) . "', jan = '" . $this->db->escape((string)$data['jan']) . "', isbn = '" . $this->db->escape((string)$data['isbn']) . "', mpn = '" . $this->db->escape((string)$data['mpn']) . "', location = '" . $this->db->escape((string)$data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape((string)$data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', currency = '" . $data['currency'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . $data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', scope = '" . $data['scope'] . "', lengthString = '" . $data['lengthString'] . "', date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape((string)$data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }

        if (isset($data['sort_order'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET sort_order = '" . (int)$data['sort_order'] . "' WHERE product_id = '" . (int)$product_id . "'");
        }

        if (isset($data['vendorCode'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET vendorCode = '" . $this->db->escape((string)$data['vendorCode']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }

        if (isset($data['GUID_Characteristic'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET GUID_Characteristic = '" . $this->db->escape((string)$data['GUID_Characteristic']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

        foreach ($data['product_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_store'])) {
            foreach ($data['product_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");

        if (!empty($data['product_attribute'])) {
            foreach ($data['product_attribute'] as $product_attribute) {
                if ($product_attribute['attribute_id']) {
                    // Removes duplicates
                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");
                    foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
                    }
                }
            }
        }

        $this->db->query("DELETE FROM `" . DB_PREFIX . "product_recurring` WHERE product_id = " . (int)$product_id);

        if (isset($data['product_recurring'])) {
            foreach ($data['product_recurring'] as $product_recurring) {
                if (isset($product_recurring['recurring_id'])) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "product_recurring` SET `product_id` = " . (int)$product_id . ", customer_group_id = " . (int)$product_recurring['customer_group_id'] . ", `recurring_id` = " . (int)$product_recurring['recurring_id']);
                }
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_discount'])) {
            foreach ($data['product_discount'] as $product_discount) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "', discount = '" . $this->db->escape($product_discount['discount']) . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_special'])) {
            foreach ($data['product_special'] as $product_special) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_special 
                SET product_id = '" . (int)$product_id . "', 
                customer_group_id = '" . (int)$product_special['customer_group_id'] . "', 
                priority = '" . (int)$product_special['priority'] . "', 
                price = '" . (float)$product_special['price'] . "', 
                date_start = '" . $this->db->escape($product_special['date_start']) . "', 
                date_end = '" . $this->db->escape($product_special['date_end']) . "', 
                customer_guid = '" . $this->db->escape($product_special['customer_guid']) . "', 
                customer_id = '" . $this->db->escape($product_special['customer_id']) . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_image'])) {
            foreach ($data['product_image'] as $product_image) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_download'])) {
            foreach ($data['product_download'] as $download_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_category'])) {
            foreach ($data['product_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
            }
        }

        if (isset($data['product_analog'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET analog_group = '" . $data['product_analog'] . "' WHERE product_id = '" . (int)$product_id . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_filter'])) {
            foreach ($data['product_filter'] as $filter_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");

        if (isset($data['product_related'])) {
            foreach ($data['product_related'] as $related_id) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_reward'])) {
            foreach ($data['product_reward'] as $customer_group_id => $value) {
                if ((int)$value['points'] > 0) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$value['points'] . "'");
                }
            }
        }

        // SEO URL
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");

        if (isset($data['product_seo_url'])) {
            foreach ($data['product_seo_url']as $store_id => $language) {
                foreach ($language as $language_id => $keyword) {
                    if (!empty($keyword)) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($keyword) . "'");
                    }
                }
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_layout'])) {
            foreach ($data['product_layout'] as $store_id => $layout_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_factor WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['pr'])) {
            $this->load->model('catalog/product_factor');
            $this->model_catalog_product_factor->editProductFactor($product_id, $data['pr']);
        }
        $this->cache->delete('product');
        $this->cache->deleteCategoryForCountry();
	}


	public function copyProduct($product_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p WHERE p.product_id = '" . (int)$product_id . "'");

		if ($query->num_rows) {
			$data = $query->row;

			$data['sku'] = '';
			$data['upc'] = '';
			$data['viewed'] = '0';
			$data['keyword'] = '';
			$data['status'] = '0';

			$data['product_attribute'] = $this->getProductAttributes($product_id);
			$data['product_description'] = $this->getProductDescriptions($product_id);
			$data['product_discount'] = $this->getProductDiscounts($product_id);
			$data['product_filter'] = $this->getProductFilters($product_id);
			$data['product_image'] = $this->getProductImages($product_id);
			$data['product_related'] = $this->getProductRelated($product_id);
			$data['product_reward'] = $this->getProductRewards($product_id);
			$data['product_special'] = $this->getProductSpecials($product_id);
			$data['product_category'] = $this->getProductCategories($product_id);
			$data['product_download'] = $this->getProductDownloads($product_id);
			$data['product_layout'] = $this->getProductLayouts($product_id);
			$data['product_store'] = $this->getProductStores($product_id);
			$data['product_recurrings'] = $this->getRecurrings($product_id);

			$copy_id = $this->addProduct($data);

			// Product Option Copy
			$this->load->model('catalog/product_option');

			$product_options = $this->model_catalog_product_option->getProductOptionsByProductId($product_id);

			foreach ($product_options as $product_option) {
				$product_option['product_id'] = $copy_id;

				$this->model_catalog_product_option->addProductOption($product_option);
			}
		}
	}

	public function deleteProduct($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_recurring WHERE product_id = " . (int)$product_id);
		$this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_factor WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "stockroom_nomenclature WHERE nomenclature_id = '" . (int)$product_id . "'");

		$this->cache->delete('product');
        $this->cache->deleteCategoryForCountry();
	}

	public function getProduct($product_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getProducts($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape((string)$data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape((string)$data['filter_model']) . "%'";
		}

		if (!empty($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape((string)$data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && $data['filter_quantity'] !== '') {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
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

	public function getProductsByCategoryId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");

		return $query->rows;
	}

	public function getProductDescriptions($product_id) {
		$product_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'tag'              => $result['tag']
			);
		}

		return $product_description_data;
	}

	public function getProductCategories($product_id) {
		$product_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_category_data[] = $result['category_id'];
		}

		return $product_category_data;
	}

	public function getProductFilters($product_id) {
		$product_filter_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_filter_data[] = $result['filter_id'];
		}

		return $product_filter_data;
	}

	public function getProductAttributes($product_id) {
		$product_attribute_data = array();

		$product_attribute_query = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' GROUP BY attribute_id");

		foreach ($product_attribute_query->rows as $product_attribute) {
			$product_attribute_description_data = array();

			$product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

			foreach ($product_attribute_description_query->rows as $product_attribute_description) {
				$product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
			}

			$product_attribute_data[] = array(
				'attribute_id'                  => $product_attribute['attribute_id'],
				'product_attribute_description' => $product_attribute_description_data
			);
		}

		return $product_attribute_data;
	}

	public function getProductImages($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getProductDiscounts($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' ORDER BY quantity, priority, price");

		return $query->rows;
	}

	public function getProductSpecials($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");

		return $query->rows;
	}

	public function getProductRewards($product_id) {
		$product_reward_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_reward_data[$result['customer_group_id']] = array('points' => $result['points']);
		}

		return $product_reward_data;
	}

	public function getProductDownloads($product_id) {
		$product_download_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_download_data[] = $result['download_id'];
		}

		return $product_download_data;
	}

	public function getProductStores($product_id) {
		$product_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_store_data[] = $result['store_id'];
		}

		return $product_store_data;
	}
	
	public function getProductSeoUrls($product_id) {
		$product_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $product_seo_url_data;
	}
	
	public function getProductLayouts($product_id) {
		$product_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $product_layout_data;
	}

	public function getProductRelated($product_id) {
		$product_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_related_data[] = $result['related_id'];
		}

		return $product_related_data;
	}

	public function getRecurrings($product_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_recurring` WHERE product_id = '" . (int)$product_id . "'");

		return $query->rows;
	}

	public function getTotalProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";

		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape((string)$data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape((string)$data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape((string)$data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && $data['filter_quantity'] !== '') {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalProductsByTaxClassId($tax_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE tax_class_id = '" . (int)$tax_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByStockStatusId($stock_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE stock_status_id = '" . (int)$stock_status_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByWeightClassId($weight_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE weight_class_id = '" . (int)$weight_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByLengthClassId($length_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE length_class_id = '" . (int)$length_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByDownloadId($download_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_download WHERE download_id = '" . (int)$download_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByManufacturerId($manufacturer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByAttributeId($attribute_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_attribute WHERE attribute_id = '" . (int)$attribute_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByProfileId($recurring_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_recurring WHERE recurring_id = '" . (int)$recurring_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}

    /**
     * Get count product to GUID from xml file
     *
     * @param $guid
     * @return mixed
     */
    public function getTotalProductsByGUID($guid)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE GUID = '" . $guid . "'");

        return (int)$query->row['total'];
    }

    /**
     * Get count product to GUID and GUID_Characteristic from 1C
     *
     * @param $guid
     * @param $guidCharacteristic
     * @return mixed
     */
    public function getTotalProductsByGUIDCharacteristic($guid, $guidCharacteristic)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE GUID = '" . $guid . "' AND GUID_Characteristic = '" . $guidCharacteristic . "'");

        return (int)$query->row['total'];
    }

    /**
     * Get product to uuid
     *
     * @param $uuid
     * @return mixed
     */
    public function getProductsByGUID($uuid)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE GUID = '" . $uuid ."'");

        return $query->row;
    }

    /**
     * Get product to GUID and GUID_Characteristic from 1C
     * @param $guid
     * @param $guidCharacteristic
     * @return mixed
     */
    public function getProductByGUID($guid, $guidCharacteristic)
    {
        $query = $this->db->query("SELECT pr.product_id AS product_id FROM " . DB_PREFIX . "product pr WHERE pr.GUID = '" . $guid . "' AND pr.GUID_Characteristic = '" . $guidCharacteristic . "'");

        return (int)$query->row['product_id'];
    }

    public function getNomenclatureForAutoComplete($data = [])
    {
        if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "product_description pd INNER JOIN " . DB_PREFIX . "product p ON (p.product_id = pd.product_id) INNER JOIN " . DB_PREFIX . "stockroom_nomenclature sn ON (sn.nomenclature_id = pd.product_id) INNER JOIN " . DB_PREFIX . "stockroom_country sc ON (sc.stockroom_id = sn.stockroom_id) WHERE pd.language_id = '" .  (int)$this->config->get('config_language_id') ."' AND sn.amount > 0 AND sc.country_id = '" . (int)$data['countryId'] . "' AND pd.name LIKE '" . $data['filter_name'] . "%'";

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
     * Change name product to latina from russia
     *
     * @param string $string
     * @return string
     */
    public function cyrillicToLatinaFromRus($string)
    {
        $arStrES = array("цх","сх","ех","хх");
        $arStrRS = array("ц$","с$","е$","х$");

        $replace = array(
            "А"=>"A","а"=>"a","Б"=>"B","б"=>"b","В"=>"V","в"=>"v","Г"=>"G","г"=>"g","Д"=>"D","д"=>"d",
            "Е"=>"E","е"=>"e","Ё"=>"Yo","ё"=>"yo","Ж"=>"Zh","ж"=>"zh","З"=>"Z","з"=>"z",
            "И"=>"I","и"=>"i",
            "Й"=>"J","й"=>"j","К"=>"K","к"=>"k","Л"=>"L","л"=>"l","М"=>"M","м"=>"m","Н"=>"N","н"=>"n",
            "О"=>"O","о"=>"o", "П"=>"P","п"=>"p","Р"=>"R","р"=>"r","С"=>"S","с"=>"s","Т"=>"T","т"=>"t",
            "У"=>"U","у"=>"u","Ф"=>"F","ф"=>"f","Х"=>"H","х"=>"h","Ц"=>"C","ц"=>"c","Ч"=>"Ch","ч"=>"ch",
            "Ш"=>"Sh","ш"=>"sh","Щ"=>"Shch","щ"=>"shch","Ы"=>"Y","ы"=>"y",
            "Э"=>"Eh","э"=>"eh","Ю"=>"Уu","ю"=>"yu","Я"=>"Ya","я"=>"ya","ъ"=>"","ь"=>"","$"=>"kh",
            "«"=>"", "»"=>"", "„"=>"", "“"=>"", "“"=>"", "”"=>"", "\•"=>"",
        );

        $string = str_replace($arStrES, $arStrRS, $string);

        return iconv("UTF-8","UTF-8//IGNORE",strtr($string,$replace));
    }

    /**
     * Delete space and punctuation symbol
     *
     * @param string $str
     * @return string
     */
    public function clearWasteChars($str)
    {
        $str = trim($str);
        $str = preg_replace('|_|','-',$str);
        $str = preg_replace('![^\w\d\s\-]*!u','',$str);
        $str = preg_replace('/\s+/', '-', $str);
        $str = preg_replace('| |','-',$str);
        $str = preg_replace('|-+|','-',$str);
        $str = preg_replace( array('!^-!', '!-$!'),array('', ''), $str);
        return $str;
    }

    /**
     * Update status product, if this category have status not active
     */
    public function updateStatusProduct()
    {
       $categories = $this->getNotActiveCategory();
       foreach ($categories as $category) {
           $products = $this->getProductsThisCategory($category);
           if (!empty($products)) {
               $this->setProductStatusNotActive($products);
           }
       }
    }

    /**
     * Get category not active
     *
     * @return mixed
     */
    public function getNotActiveCategory()
    {
        $query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category  WHERE  status = 0");
        return $query->rows;
    }

    /**
     * Get all product that have this category
     *
     * @param array $category
     * @return mixed
     */
    public function getProductsThisCategory(array $category)
    {
        if (!empty( $category['category_id'])) {
            $products = $this->db->query("
               SELECT product_id FROM " . DB_PREFIX . "product_to_category  
                    WHERE  category_id = '" . $category['category_id'] . "'
            ");
            return $products->rows;
        }
    }

    /**
     * Set not active product
     *
     * @param array $products
     */
    public function setProductStatusNotActive(array $products)
    {
        foreach ($products as $product) {
            $this->db->query("
            UPDATE " . DB_PREFIX . "product SET status = 0 
                WHERE product_id = " . (int)$product['product_id'] . ""
            );
        }
    }
}
