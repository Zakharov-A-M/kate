<?php

class ModelCatalogProduct extends Model
{

    const EXCHANGE_DEFAULT_STORE_ID = 0;

    public function updateViewed($productId)
    {
        $this->db->query(
            "UPDATE " . DB_PREFIX . "product SET viewed = (viewed + 1) WHERE product_id = '" . (int)$productId . "'"
        );
    }

    public function getProduct($productId)
    {
        if (!empty($this->session->data['customer_id'])) {
            $query0 = $this->db->query(
                "SELECT partner_guid FROM oc_customer WHERE customer_id='" .
                (int)$this->session->data['customer_id'] . "'"
            );
            $cust = $query0->row['partner_guid'];
        } else {
            $cust = 0;
        }

        $query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, p.GUID, p.GUID_Characteristic,
        m.name AS manufacturer,
        (SELECT price FROM " . DB_PREFIX . "product_discount pd2
        WHERE pd2.product_id = p.product_id
        AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
        AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW())
        AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW()))
        ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount,
        (SELECT SUM(amount) FROM " . DB_PREFIX . "stockroom_nomenclature sn
        LEFT JOIN " . DB_PREFIX . "stockroom s ON (sn.stockroom_id = s.stockroom_id)
        LEFT JOIN " . DB_PREFIX . "stockroom_country sc ON (sc.stockroom_id = s.stockroom_id)
        LEFT JOIN " . DB_PREFIX . "stockroom_attached sa ON (sa.attach_stockroom_id = s.stockroom_id)
        WHERE sn.nomenclature_id = p.product_id
        AND (s.stockroom_id = sa.attach_stockroom_id OR sc.country_id = '" .
            (int)$this->config->get('config_current_country') . "')) AS amount,
        (SELECT price FROM " . DB_PREFIX . "product_special ps
        WHERE ps.product_id = p.product_id
        AND  ps.customer_guid = '" . trim($cust) . "' AND ps.customer_guid != ''
        ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special,
        (SELECT points FROM " . DB_PREFIX . "product_reward pr
        WHERE pr.product_id = p.product_id
        AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') AS reward,
        (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss
        WHERE ss.stock_status_id = p.stock_status_id
        AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status,
        (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd
        WHERE p.weight_class_id = wcd.weight_class_id
        AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class,
        (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd
        WHERE p.length_class_id = lcd.length_class_id
        AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class,
        (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1
        WHERE r1.product_id = p.product_id AND r1.status = '1'
        GROUP BY r1.product_id) AS rating,
        (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2
        WHERE r2.product_id = p.product_id AND r2.status = '1'
        GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p
        LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
        LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
        LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id)
        WHERE p.product_id = '" . (int)$productId . "'
        AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
        AND p.status = '1' AND p.date_available <= NOW()
        AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

        if ($query->num_rows) {
            return array(
                'product_id' => $query->row['product_id'],
                'GUID' => $query->row['GUID'],
                'GUID_Characteristic' => $query->row['GUID_Characteristic'],
                'name' => $query->row['name'],
                'description' => $query->row['description'],
                'meta_title' => $query->row['meta_title'],
                'meta_description' => $query->row['meta_description'],
                'meta_keyword' => $query->row['meta_keyword'],
                'tag' => $query->row['tag'],
                'model' => $query->row['model'],
                'sku' => $query->row['sku'],
                'upc' => $query->row['upc'],
                'ean' => $query->row['ean'],
                'jan' => $query->row['jan'],
                'isbn' => $query->row['isbn'],
                'mpn' => $query->row['mpn'],
                'location' => $query->row['location'],
                'amount' => $query->row['amount'],
                'quantity' => $query->row['quantity'],
                'stock_status' => $query->row['stock_status'],
                'image' => $query->row['image'],
                'manufacturer_id' => $query->row['manufacturer_id'],
                'manufacturer' => $query->row['manufacturer'],
                'discount' => $query->row['discount'],
                'currency' => $query->row['currency'],
                'price' => $query->row['price'],
                'special' => $query->row['special'],
                'reward' => $query->row['reward'],
                'points' => $query->row['points'],
                'tax_class_id' => $query->row['tax_class_id'],
                'date_available' => $query->row['date_available'],
                'weight' => $query->row['weight'],
                'weight_class_id' => $query->row['weight_class_id'],
                'length' => $query->row['length'],
                'width' => $query->row['width'],
                'height' => $query->row['height'],
                'analog_group' => $query->row['analog_group'],
                'length_class_id' => $query->row['length_class_id'],
                'subtract' => $query->row['subtract'],
                'rating' => round($query->row['rating']),
                'reviews' => $query->row['reviews'] ? $query->row['reviews'] : 0,
                'minimum' => $query->row['minimum'],
                'sort_order' => $query->row['sort_order'],
                'status' => $query->row['status'],
                'date_added' => $query->row['date_added'],
                'date_modified' => $query->row['date_modified'],
                'vendorCode' => $query->row['vendorCode'],
                'viewed' => $query->row['viewed'],
                'scope' => $query->row['scope'],
                'lengthString' => $query->row['lengthString']
            );
        } else {
            return false;
        }
    }

    /**
     *  Get all products specials
     *
     * @param array $data
     * @return array
     */
    public function getProductAllSpecials($data)
    {
        $queryPartner = $this->db->query(
            "SELECT partner_guid FROM oc_customer
            WHERE customer_id='" . (int)$this->session->data['customer_id'] . "'"
        );
        $partnerGuid = $queryPartner->row['partner_guid'];

        $sql = "SELECT p.product_id, (SELECT AVG(rating) AS total
                FROM " . DB_PREFIX . "review r1
                WHERE r1.product_id = p.product_id
                AND r1.status = '1'
                GROUP BY r1.product_id) AS rating,
                (SELECT price FROM " . DB_PREFIX . "product_discount pd2
                WHERE pd2.product_id = p.product_id
                AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
                AND pd2.quantity = '1'
                AND ((pd2.date_start = '0000-00-00'
                OR pd2.date_start < NOW())
                AND (pd2.date_end = '0000-00-00'
                OR pd2.date_end > NOW()))
                ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount,
                (SELECT price FROM " . DB_PREFIX . "product_special ps
                WHERE ps.product_id = p.product_id
                AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
                AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW())
                AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))
                ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special ";

        $sql .= " FROM " . DB_PREFIX . "product_special ps
            LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id)
            LEFT JOIN " . DB_PREFIX . "product_to_category pc ON (pc.product_id = ps.product_id)
            LEFT JOIN " . DB_PREFIX . "category c ON (pc.category_id = c.category_id)";

        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
            LEFT JOIN " . DB_PREFIX . "stockroom_nomenclature sn ON (p.product_id = sn.nomenclature_id)
            LEFT JOIN " . DB_PREFIX . "stockroom st ON (sn.stockroom_id = st.stockroom_id)
            LEFT JOIN " . DB_PREFIX . "stockroom_country sc ON (sc.stockroom_id = st.stockroom_id)
            LEFT JOIN " . DB_PREFIX . "stockroom_attached sa ON
                (sa.stockroom_id = st.stockroom_id OR sa.attach_stockroom_id = st.stockroom_id)
            WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            AND p.status = '1'
            AND p.date_available <= NOW()
            AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND
                 (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))
            AND ((sn.amount > 0 AND sc.country_id = '" . (int)$this->config->get('config_current_country') . "') OR
                (st.stockroom_id = sa.attach_stockroom_id AND sn.amount > 0))
                AND c.status = 1
            AND ps.customer_guid = '" . trim($partnerGuid) . "'
            AND ps.customer_guid != ''";

        if (!empty($data['filter_manufacturer_id'])) {
            $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
        }

        $sql .= " GROUP BY p.product_id";
        $sortData = array(
            'pd.name',
            'p.model',
            'p.quantity',
            'p.price',
            'rating',
            'p.sort_order',
            'p.date_added',
            'p.product_id'
        );

        if (!empty($data['sort'])) {
            if (is_array($data['sort'])) {
                $sql .= " ORDER BY ";
                $sql .= $this->addSortProduct($data, $sortData);
            } else {
                $sql .= " ORDER BY p.sort_order";
            }
        } else {
            $sql .= " ORDER BY p.sort_order";
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

        $productData = [];
        $query = $this->db->query($sql);
        foreach ($query->rows as $result) {
            $productData[$result['product_id']] = $this->getProduct($result['product_id']);
        }
        return $productData;
    }

    public function getProducts($data = array())
    {
        $sql = "SELECT p.product_id, (SELECT AVG(rating) AS total
                FROM " . DB_PREFIX . "review r1
                WHERE r1.product_id = p.product_id
                AND r1.status = '1'
                GROUP BY r1.product_id) AS rating,
                (SELECT price FROM " . DB_PREFIX . "product_discount pd2
                WHERE pd2.product_id = p.product_id
                AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
                AND pd2.quantity = '1'
                AND ((pd2.date_start = '0000-00-00'
                OR pd2.date_start < NOW())
                AND (pd2.date_end = '0000-00-00'
                OR pd2.date_end > NOW()))
                ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount,
                (SELECT price FROM " . DB_PREFIX . "product_special ps
                WHERE ps.product_id = p.product_id
                AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
                AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW())
                AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))
                ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX .
                    "product_to_category p2c ON (cp.category_id = p2c.category_id) LEFT JOIN oc_category c ON
                     (c.category_id = cp.category_id)";
            } else {
                $sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
            }

            if (!empty($data['filter_filter'])) {
                $sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN "
                    . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
            } else {
                $sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
            }
        } else {
            $sql .= " FROM " . DB_PREFIX .
                "product p LEFT JOIN oc_product_to_category pc ON
                 (pc.product_id = p.product_id) LEFT JOIN oc_category c ON (pc.category_id = c.category_id)";
        }

        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " .
            DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX .
            "stockroom_nomenclature sn ON (p.product_id = sn.nomenclature_id) LEFT JOIN " . DB_PREFIX .
            "stockroom st ON (sn.stockroom_id = st.stockroom_id) LEFT JOIN " . DB_PREFIX .
            "stockroom_country sc ON (sc.stockroom_id = st.stockroom_id) LEFT JOIN " . DB_PREFIX .
            "stockroom_attached sa ON (sa.stockroom_id = st.stockroom_id OR sa.attach_stockroom_id = st.stockroom_id)
             WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND
              p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" .
            (int)$this->config->get('config_store_id') . "' AND ((sn.amount > 0 AND sc.country_id = '" .
            (int)$this->config->get('config_current_country') . "') OR (st.stockroom_id = sa.attach_stockroom_id AND
             sn.amount > 0)) AND c.status = 1";

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
            } else {
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
            }

            if (!empty($data['filter_filter'])) {
                $implode = array();

                $filters = explode(',', $data['filter_filter']);

                foreach ($filters as $filter_id) {
                    $implode[] = (int)$filter_id;
                }

                $sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
            }
        }

        if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
            $sql .= " AND (";

            if (!empty($data['filter_name'])) {
                $implode = array();

                $words = explode(
                    ' ',
                    trim(preg_replace('/\s+/', ' ', $data['filter_name']))
                );

                foreach ($words as $word) {
                    $implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }

                if (!empty($data['filter_description'])) {
                    $sql .= " OR pd.description LIKE '%" . $this->db->escape((string)$data['filter_name']) . "%'";
                }
            }

            if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
                $sql .= " OR ";
            }

            if (!empty($data['filter_tag'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

                foreach ($words as $word) {
                    $implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }
            }

            if (!empty($data['filter_name'])) {
                $sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
            }

            $sql .= ")";
        }

        if (!empty($data['filter_manufacturer_id'])) {
            $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
        }

        $sql .= " GROUP BY p.product_id";
        $sortData = array(
            'pd.name',
            'p.model',
            'p.quantity',
            'p.price',
            'rating',
            'p.sort_order',
            'p.date_added',
            'p.product_id'
        );

        if (!empty($data['sort'])) {
            if (is_array($data['sort'])) {
                $sql .= " ORDER BY ";
                foreach ($data['sort'] as $key => $params) {
                    $param = explode('-', $params);
                    if (in_array($param[0], $sortData)) {
                        if ($param[0] == 'pd.name' || $param[0] == 'p.model') {
                            if ($key > 0) {
                                $sql .= ", LCASE(" . $param[0] . ") " . $param[1] . "";
                            } else {
                                $sql .= " LCASE(" . $param[0] . ") " . $param[1] . "";
                            }

                        } elseif ($param[0] == 'p.price') {
                            if ($key > 0) {
                                $sql .= ", (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL
                                 THEN discount ELSE p.price END) $param[1] ";
                            } else {
                                $sql .= " (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL
                                 THEN discount ELSE p.price END) $param[1] ";
                            }
                        }
                    }
                }
            } else {
                $sql .= " ORDER BY p.sort_order";
            }
        } else {
            $sql .= " ORDER BY p.sort_order";
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

        $productData = [];

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $productData[$result['product_id']] = $this->getProduct($result['product_id']);
        }

        return $productData;
    }

    public function getProductSpecials($data = array())
    {
        $sql = "SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM " . DB_PREFIX .
            "review r1 WHERE r1.product_id = ps.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating
             FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON
              (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON
               (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON
                (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND
                 p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND
                  ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND
                   ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR
                    ps.date_end > NOW())) GROUP BY ps.product_id";

        $sortData = array(
            'pd.name',
            'p.model',
            'ps.price',
            'rating',
            'p.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sortData)) {
            if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
                $sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
            } else {
                $sql .= " ORDER BY " . $data['sort'];
            }
        } else {
            $sql .= " ORDER BY p.sort_order";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC, LCASE(pd.name) DESC";
        } else {
            $sql .= " ASC, LCASE(pd.name) ASC";
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

        $productData = array();

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $productData[$result['product_id']] = $this->getProduct($result['product_id']);
        }

        return $productData;
    }

    public function getProductAttributes($productId)
    {
        $productAttributeGroupData = array();

        $productAttributeGroupQuery = $this->db->query(
            "SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " .
            DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " .
            DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " .
            DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE
             pa.product_id = '" . (int)$productId . "' AND agd.language_id = '" .
            (int)$this->config->get('config_language_id') . "' GROUP BY ag.attribute_group_id ORDER BY
             ag.sort_order, agd.name"
        );

        foreach ($productAttributeGroupQuery->rows as $product_attribute_group) {
            $productAttributeData = array();

            $productAttributeQuery = $this->db->query(
                "SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX .
                "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON
                 (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX .
                "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE
                 pa.product_id = '" . (int)$productId . "' AND a.attribute_group_id = '" .
                (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" .
                (int)$this->config->get('config_language_id') . "' AND pa.language_id = '" .
                (int)$this->config->get('config_language_id') . "' ORDER BY a.sort_order, ad.name"
            );

            foreach ($productAttributeQuery->rows as $product_attribute) {
                $productAttributeData[] = array(
                    'attribute_id' => $product_attribute['attribute_id'],
                    'name' => $product_attribute['name'],
                    'text' => $product_attribute['text']
                );
            }

            $productAttributeGroupData[] = array(
                'attribute_group_id' => $product_attribute_group['attribute_group_id'],
                'name' => $product_attribute_group['name'],
                'attribute' => $productAttributeData
            );
        }

        return $productAttributeGroupData;
    }

    public function getProductOptions($productId)
    {
        $productOptionData = array();

        $productOptionQuery = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX .
            "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON
             (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$productId . "' AND
              od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order"
        );

        foreach ($productOptionQuery->rows as $product_option) {
            $productOptionValueData = array();

            $productOptionValueQuery = $this->db->query(
                "SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " .
                DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " .
                DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE
                 pov.product_id = '" . (int)$productId . "' AND pov.product_option_id = '" .
                (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" .
                (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order"
            );

            foreach ($productOptionValueQuery->rows as $product_option_value) {
                $productOptionValueData[] = array(
                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                    'option_value_id' => $product_option_value['option_value_id'],
                    'name' => $product_option_value['name'],
                    'image' => $product_option_value['image'],
                    'quantity' => $product_option_value['quantity'],
                    'subtract' => $product_option_value['subtract'],
                    'price' => $product_option_value['price'],
                    'price_prefix' => $product_option_value['price_prefix'],
                    'weight' => $product_option_value['weight'],
                    'weight_prefix' => $product_option_value['weight_prefix']
                );
            }

            $productOptionData[] = array(
                'product_option_id' => $product_option['product_option_id'],
                'product_option_value' => $productOptionValueData,
                'option_id' => $product_option['option_id'],
                'name' => $product_option['name'],
                'type' => $product_option['type'],
                'value' => $product_option['value'],
                'required' => $product_option['required']
            );
        }

        return $productOptionData;
    }

    public function getProductDiscounts($productId)
    {
        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$productId .
            "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') .
            "' AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < NOW()) AND
             (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity ASC, priority ASC, price ASC"
        );

        return $query->rows;
    }

    public function getProductImages($productId)
    {
        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$productId .
            "' ORDER BY sort_order ASC"
        );

        return $query->rows;
    }

    /**
     * Get products related
     *
     * @param $productId
     * @return array
     */
    public function getProductRelated($productId)
    {
        $productData = array();

        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "product_related pr LEFT JOIN " . DB_PREFIX .
            "stockroom_nomenclature sn ON (pr.related_id = sn.nomenclature_id) LEFT JOIN " . DB_PREFIX .
            "stockroom_country sc ON (sc.stockroom_id = sn.stockroom_id) LEFT JOIN " . DB_PREFIX .
            "stockroom_attached sa ON (sa.stockroom_id = sn.stockroom_id OR
             sa.attach_stockroom_id = sn.stockroom_id) LEFT JOIN " . DB_PREFIX . "product p ON
              (pr.related_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON
               (p.product_id = p2s.product_id) WHERE pr.product_id = '" . (int)$productId . "' AND
                p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" .
            (int)$this->config->get('config_store_id') . "' AND ((sc.country_id = '" .
            (int)$this->config->get('config_current_country') . "' AND sn.amount > 0) OR
             (sn.stockroom_id = sa.attach_stockroom_id AND sn.amount > 0)) "
        );

        foreach ($query->rows as $result) {
            $productData[$result['related_id']] = $this->getProduct($result['related_id']);
        }

        return $productData;
    }

    /**
     * Get list analog products
     *
     * @param $productId
     * @param $analogGroup
     * @return array
     */
    public function getProductAnalog($analogGroup, $productId)
    {
        $productData = [];

        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "stockroom_nomenclature sn ON
             (p.product_id = sn.nomenclature_id) LEFT JOIN " . DB_PREFIX . "stockroom_country sc ON
              (sc.stockroom_id = sn.stockroom_id) LEFT JOIN " . DB_PREFIX . "stockroom_attached sa ON
               (sa.stockroom_id = sc.stockroom_id OR sa.attach_stockroom_id = sc.stockroom_id) WHERE
                p.analog_group = '" . $analogGroup . "' AND p.product_id != '" . $productId . "' AND
                 p.analog_group != '0'  AND p.status = '1' AND p.date_available <= NOW() AND
                  ((sc.country_id = '" . (int)$this->config->get('config_current_country') . "' AND
                   sn.amount > 0) OR (sc.stockroom_id = sa.attach_stockroom_id AND sn.amount > 0))"
        );

        foreach ($query->rows as $result) {
            $productData[$result['product_id']] = $this->getProduct($result['product_id']);
        }

        return $productData;
    }

    public function getProductLayoutId($productId)
    {
        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" .
            (int)$productId . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'"
        );

        if ($query->num_rows) {
            return (int)$query->row['layout_id'];
        } else {
            return 0;
        }
    }

    public function getCategories($productId)
    {
        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$productId . "'"
        );

        return $query->rows;
    }

    public function getTotalProducts($data = array())
    {
        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total";

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX .
                    "product_to_category p2c ON (cp.category_id = p2c.category_id) LEFT JOIN
                     oc_category c ON (c.category_id = cp.category_id)";
            } else {
                $sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
            }

            if (!empty($data['filter_filter'])) {
                $sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id)
                 LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
            } else {
                $sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
            }
        } else {
            $sql .= " FROM " . DB_PREFIX . "product p LEFT JOIN oc_product_to_category pc ON
             (pc.product_id = p.product_id) LEFT JOIN oc_category c ON
              (pc.category_id = c.category_id)";
        }

        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " .
            DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)  LEFT JOIN " .
            DB_PREFIX . "stockroom_nomenclature sn ON (p.product_id = sn.nomenclature_id)  LEFT JOIN " .
            DB_PREFIX . "stockroom st ON (sn.stockroom_id = st.stockroom_id) LEFT JOIN " .
            DB_PREFIX . "stockroom_country sc ON (sc.stockroom_id = st.stockroom_id) LEFT JOIN " .
            DB_PREFIX . "stockroom_attached sa ON (sa.stockroom_id = st.stockroom_id OR
             sa.attach_stockroom_id = st.stockroom_id)  WHERE pd.language_id = '" .
            (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND
             p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') .
            "' AND ((sn.amount > 0 AND sc.country_id = '" . (int)$this->config->get('config_current_country') .
            "') OR (st.stockroom_id = sa.attach_stockroom_id AND sn.amount > 0)) AND c.status = 1";

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
            } else {
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
            }

            if (!empty($data['filter_filter'])) {
                $implode = array();

                $filters = explode(',', $data['filter_filter']);

                foreach ($filters as $filter_id) {
                    $implode[] = (int)$filter_id;
                }

                $sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
            }
        }

        if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
            $sql .= " AND (";

            if (!empty($data['filter_name'])) {
                $implode = array();

                $words = explode(
                    ' ',
                    trim(preg_replace('/\s+/', ' ', $data['filter_name']))
                );

                foreach ($words as $word) {
                    $implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }

                if (!empty($data['filter_description'])) {
                    $sql .= " OR pd.description LIKE '%" . $this->db->escape((string)$data['filter_name']) . "%'";
                }
            }

            if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
                $sql .= " OR ";
            }

            if (!empty($data['filter_tag'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

                foreach ($words as $word) {
                    $implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }
            }

            if (!empty($data['filter_name'])) {
                $sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
            }

            $sql .= ")";
        }

        if (!empty($data['filter_manufacturer_id'])) {
            $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getProfile($productId, $recurringId)
    {
        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX .
            "recurring r JOIN " . DB_PREFIX .
            "product_recurring pr ON (pr.recurring_id = r.recurring_id AND
             pr.product_id = '" . (int)$productId . "')
              WHERE pr.recurring_id = '" . (int)$recurringId .
            "' AND status = '1' AND pr.customer_group_id = '" .
            (int)$this->config->get('config_customer_group_id') . "'");

        return $query->row;
    }

    public function getProfiles($productId)
    {
        $query = $this->db->query(
            "SELECT rd.* FROM " . DB_PREFIX . "product_recurring pr JOIN " . DB_PREFIX .
            "recurring_description rd ON (rd.language_id = " . (int)$this->config->get('config_language_id') .
            " AND rd.recurring_id = pr.recurring_id) JOIN " . DB_PREFIX . "recurring r ON
             r.recurring_id = rd.recurring_id WHERE pr.product_id = " . (int)$productId . " AND
              status = '1' AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') .
            "' ORDER BY sort_order ASC"
        );

        return $query->rows;
    }

    public function getTotalProductSpecials()
    {
        $queryPartner = $this->db->query(
            "SELECT partner_guid FROM oc_customer
          WHERE customer_id='" . (int)$this->session->data['customer_id'] . "'"
        );
        $partnerGuid = $queryPartner->row['partner_guid'];

        $query = $this->db->query("
          SELECT COUNT(DISTINCT ps.product_id) AS total
          FROM " . DB_PREFIX . "product_special ps
          LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id)
          LEFT JOIN oc_product_to_category pc ON (pc.product_id = p.product_id)
          LEFT JOIN oc_category c ON (pc.category_id = c.category_id)
          LEFT JOIN " . DB_PREFIX . "stockroom_nomenclature AS sn ON ps.product_id = sn.nomenclature_id
          LEFT JOIN " . DB_PREFIX . "stockroom_country AS sc ON sc.stockroom_id = sn.stockroom_id
          LEFT JOIN oc_stockroom_attached sa ON (sa.attach_stockroom_id = sc.stockroom_id)
          WHERE
            (
                sc.stockroom_id = sa.attach_stockroom_id OR
                sc.country_id='" . (int)$this->config->get('config_current_country') . "'
            ) AND
            sn.amount > 0 AND
            c.status = 1  AND
            p.status = '1' AND
            p.date_available <= NOW() AND
            (
                (ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND
                 (ps.date_end = '0000-00-00' OR ps.date_end > NOW())
            ) AND
            ps.customer_guid = '" . trim($partnerGuid) . "' AND
            ps.customer_guid != ''
        ");
        return $query->row['total'];
    }

    /**
     * Get count product ID in current country
     *
     * @param $id
     * @return mixed
     */
    public function getCountProductInCurrentCountry($id)
    {
        $query = $this->db->query("SELECT SUM(amount) AS amount
            FROM " . DB_PREFIX . "stockroom_nomenclature sn
            LEFT JOIN oc_stockroom s ON (sn.stockroom_id = s.stockroom_id)
            LEFT JOIN oc_stockroom_country sc ON (sc.stockroom_id = s.stockroom_id)
            WHERE sn.nomenclature_id = '" . (int)$id . "'
            AND sc.country_id = '" . (int)$this->config->get('config_current_country') . "'"
        );

        return $query->row['amount'];
    }

    /**
     * Add sorting from product
     *
     * @param array $data
     * @param array $sortData
     * @return string
     */
    public function addSortProduct(array $data, array $sortData)
    {
        $sql = '';
        foreach ($data['sort'] as $key => $params) {
            $param = explode('-', $params);
            if (in_array($param[0], $sortData)) {
                if ($param[0] == 'pd.name' || $param[0] == 'p.model') {
                    if ($key > 0) {
                        $sql .= ", LCASE(" . $param[0] . ") " . $param[1] . "";
                    } else {
                        $sql .= " LCASE(" . $param[0] . ") " . $param[1] . "";
                    }

                } elseif ($param[0] == 'p.price') {
                    if ($key > 0) {
                        $sql .= ", (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL
                                 THEN discount ELSE p.price END) $param[1] ";
                    } else {
                        $sql .= " (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL
                                 THEN discount ELSE p.price END) $param[1] ";
                    }
                }
            }
        }
        return $sql;
    }
}
