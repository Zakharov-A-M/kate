<?php

class ModelCatalogProductOption extends Model
{
    /**
     * Add option in product
     *
     * @param array $data
     */
	public function addProductOption($data)
    {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "product_option` SET
		    product_id = '" . (int)$data['product_id'] . "', 
		    option_id = '" . (int)$data['option_id'] . "', 
		    value = '" . $this->db->escape(isset($data['value']) ? $data['value'] : '') . "', 
		    required = '" . (int)$data['required'] . "'"
        );

		$productOptionId = $this->db->getLastId();

		if (isset($data['product_option_value'])) {
			foreach ($data['product_option_value'] as $productOptionValue) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_option_value` SET
                    product_option_id = '" . (int)$productOptionId . "', 
                    product_id = '" . (int)$data['product_id'] . "', 
                    option_id = '" . (int)$data['option_id'] . "', 
                    option_value_id = '" . (int)$productOptionValue['option_value_id'] . "', 
                    quantity = '" . (int)$productOptionValue['quantity'] . "', 
                    subtract = '" . (int)$productOptionValue['subtract'] . "', 
                    price = '" . (float)$productOptionValue['price'] . "', 
                    price_prefix = '" . $this->db->escape($productOptionValue['price_prefix']) . "', 
                    points = '" . (int)$productOptionValue['points'] . "', 
                    points_prefix = '" . $this->db->escape($productOptionValue['points_prefix']) . "', 
                    weight = '" . (float)$productOptionValue['weight'] . "', 
                    weight_prefix = '" . $this->db->escape($productOptionValue['weight_prefix']) . "'"
                );
			}
		}

		$this->cache->delete('product');
	}

    /**
     * Edit option for product
     *
     * @param int $productOptionId
     * @param array $data
     */
	public function editProductOption($productOptionId, $data)
    {
		$this->db->query("UPDATE `" . DB_PREFIX . "product_option` SET 
            product_id = '" . (int)$data['product_id'] . "', 
            option_id = '" . (int)$data['option_id'] . "', 
            value = '" . $this->db->escape(isset($data['value']) ? $data['value'] : '') . "', 
            required = '" . (int)$data['required'] . "' 
            WHERE product_option_id = '" . (int)$productOptionId . "'"
        );

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value 
		    WHERE product_option_id = '" . (int)$productOptionId . "'"
        );

		if (isset($data['product_option_value'])) {
			foreach ($data['product_option_value'] as $productOptionValue) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET 
				    product_option_value_id = '" . (int)$productOptionValue['product_option_value_id'] . "', 
				    product_option_id = '" . (int)$productOptionId . "', 
				    product_id = '" . (int)$data['product_id'] . "', 
				    option_id = '" . (int)$data['option_id'] . "', 
				    option_value_id = '" . (int)$productOptionValue['option_value_id'] . "', 
				    quantity = '" . (int)$productOptionValue['quantity'] . "', 
				    subtract = '" . (int)$productOptionValue['subtract'] . "', 
				    price = '" . (float)$productOptionValue['price'] . "', 
				    price_prefix = '" . $this->db->escape($productOptionValue['price_prefix']) . "', 
				    points = '" . (int)$productOptionValue['points'] . "', 
				    points_prefix = '" . $this->db->escape($productOptionValue['points_prefix']) . "', 
				    weight = '" . (float)$productOptionValue['weight'] . "', 
				    weight_prefix = '" . $this->db->escape($productOptionValue['weight_prefix']) . "'"
                );
			}
		}

		$this->cache->delete('product');
	}

    /**
     * Delete option for product
     *
     * @param int $productOptionId
     */
	public function deleteProductOption($productOptionId)
    {
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option 
		    WHERE product_option_id = '" . (int)$productOptionId . "'"
        );
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value 
		    WHERE product_option_id = '" . (int)$productOptionId . "'"
        );

		$this->cache->delete('product');
	}

    /**
     * Get option product
     *
     * @param int $product_option_id
     * @return mixed
     */
	public function getProductOption($product_option_id)
    {
		$query = $this->db->query("SELECT DISTINCT *, pd.name AS `product`, od.name AS `option` 
            FROM `" . DB_PREFIX . "product_option` po 
            LEFT JOIN `" . DB_PREFIX . "product` p ON (po.product_id = p.product_id) 
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id) 
            LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) 
            LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) 
            WHERE po.product_option_id = '" . (int)$product_option_id . "' 
            AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'"
        );

		return $query->row;
	}

    /**
     * Get options all product
     *
     * @param array $data
     * @return mixed
     */
	public function getProductOptions($data = [])
    {
		$sql = "SELECT *, pd.name AS `product`, od.name AS `option`, o.type, o.sort_order 
            FROM `" . DB_PREFIX . "product_option` po 
            LEFT JOIN `" . DB_PREFIX . "product` p ON (po.product_id = p.product_id) 
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id) 
            LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) 
            LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) 
            WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
            AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_product_id'])) {
			$sql .= " AND po.product_id = '" . $this->db->escape((string)$data['filter_product_id']) . "%'";
		}

		if (!empty($data['filter_product'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape((string)$data['filter_product']) . "%'";
		}

		if (!empty($data['filter_option'])) {
			$sql .= " AND od.name LIKE '" . $this->db->escape((string)$data['filter_option']) . "%'";
		}

		$sortData = [
			'pd.name',
			'od.name',
			'o.type',
			'o.sort_order'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sortData)) {
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

    /**
     * Get count option product
     *
     * @param array $data
     * @return int
     */
	public function getTotalProductOptions($data = [])
    {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "product_option` po 
		    LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (po.product_id = pd.product_id) 
		    LEFT JOIN `" . DB_PREFIX . "option_description` od ON (po.option_id = od.option_id) 
		    WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
		    AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_product_id'])) {
			$sql .= " AND po.product_id = '" . $this->db->escape((string)$data['filter_product_id']) . "%'";
		}

		if (!empty($data['filter_product'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape((string)$data['filter_product']) . "%'";
		}

		if (!empty($data['filter_option'])) {
			$sql .= " AND od.name LIKE '" . $this->db->escape((string)$data['filter_option']) . "%'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

    /**
     * Get options by product ID
     *
     * @param int $productId
     * @return array
     */
	public function getProductOptionsByProductId($productId)
    {
		$productOptionData = [];

		$productOptionQuery = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po 
		    LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) 
		    LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) 
		    WHERE po.product_id = '" . (int)$productId . "' 
		    AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'"
        );

		foreach ($productOptionQuery->rows as $productOption) {
			$productOptionValueData = [];

			$productOptionValueQuery = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov 
			    LEFT JOIN " . DB_PREFIX . "option_value ov ON(pov.option_value_id = ov.option_value_id) 
			    WHERE pov.product_option_id = '" . (int)$productOption['product_option_id'] . "' 
			    ORDER BY ov.sort_order ASC"
            );

			foreach ($productOptionValueQuery->rows as $productOptionValue) {
				$productOptionValueData[] = [
					'product_option_value_id' => $productOptionValue['product_option_value_id'],
					'option_value_id'         => $productOptionValue['option_value_id'],
					'quantity'                => $productOptionValue['quantity'],
					'subtract'                => $productOptionValue['subtract'],
					'price'                   => $productOptionValue['price'],
					'price_prefix'            => $productOptionValue['price_prefix'],
					'points'                  => $productOptionValue['points'],
					'points_prefix'           => $productOptionValue['points_prefix'],
					'weight'                  => $productOptionValue['weight'],
					'weight_prefix'           => $productOptionValue['weight_prefix']
				];
			}

			$productOptionData[] = array(
				'product_option_id'    => $productOption['product_option_id'],
				'product_option_value' => $productOptionValueData,
				'option_id'            => $productOption['option_id'],
				'name'                 => $productOption['name'],
				'type'                 => $productOption['type'],
				'value'                => $productOption['value'],
				'required'             => $productOption['required']
			);
		}

		return $productOptionData;
	}

    /**
     * Get value option from product
     *
     * @param int $productId
     * @param int $productOptionValueId
     * @return mixed
     */
	public function getProductOptionValue($productId, $productOptionValueId)
    {
		$query = $this->db->query("SELECT 
          pov.option_value_id, 
          ovd.name, 
          pov.quantity, 
          pov.subtract, 
          pov.price, 
          pov.price_prefix, 
          pov.points, 
          pov.points_prefix, 
          pov.weight, 
          pov.weight_prefix 
          FROM " . DB_PREFIX . "product_option_value pov 
          LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) 
          LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) 
          WHERE pov.product_id = '" . (int)$productId . "' 
          AND pov.product_option_value_id = '" . (int)$productOptionValueId . "' 
          AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'"
        );

		return $query->row;
	}

    /**
     * Get option from ID
     *
     * @param int $productOptionId
     * @return mixed
     */
	public function getProductOptionValues($productOptionId)
    {
		$query = $this->db->query("SELECT 
          product_option_value_id, 
          option_value_id,
          quantity,
          subtract, 
          price, 
          price_prefix, 
          points, 
          points_prefix, 
          weight, 
          weight_prefix
           FROM " . DB_PREFIX . "product_option_value 
           WHERE product_option_id = '" . (int)$productOptionId . "'"
        );

		return $query->rows;
	}

    /**
     * Get count option from product
     *
     * @param int $optionId
     * @return mixed
     */
	public function getTotalProductsByOptionId($optionId)
    {
		$query = $this->db->query("SELECT COUNT(*) AS total 
            FROM " . DB_PREFIX . "product_option
		    WHERE option_id = '" . (int)$optionId . "'"
        );

		return $query->row['total'];
	}
}
