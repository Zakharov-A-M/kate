<?php

class ModelCatalogCategory extends Model
{
    public function getCategory($category_id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");

        return $query->row;
    }

    public function getCategories($parent_id = 0)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");

        return $query->rows;
    }

    public function getLastCategory()
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1' AND c.top = 1 ORDER BY c.category_id, LCASE(cd.name) LIMIT 1 ");

        return $query->rows;
    }

    public function getCategoryFilters($category_id)
    {
        $implode = array();

        $query = $this->db->query("SELECT filter_id FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");

        foreach ($query->rows as $result) {
            $implode[] = (int)$result['filter_id'];
        }

        $filter_group_data = array();

        if ($implode) {
            $filter_group_query = $this->db->query("SELECT DISTINCT f.filter_group_id, fgd.name, fg.sort_order FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_group fg ON (f.filter_group_id = fg.filter_group_id) LEFT JOIN " . DB_PREFIX . "filter_group_description fgd ON (fg.filter_group_id = fgd.filter_group_id) WHERE f.filter_id IN (" . implode(',', $implode) . ") AND fgd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY f.filter_group_id ORDER BY fg.sort_order, LCASE(fgd.name)");

            foreach ($filter_group_query->rows as $filter_group) {
                $filter_data = array();

                $filter_query = $this->db->query("SELECT DISTINCT f.filter_id, fd.name FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) WHERE f.filter_id IN (" . implode(',', $implode) . ") AND f.filter_group_id = '" . (int)$filter_group['filter_group_id'] . "' AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY f.sort_order, LCASE(fd.name)");

                foreach ($filter_query->rows as $filter) {
                    $filter_data[] = array(
                        'filter_id' => $filter['filter_id'],
                        'name' => $filter['name']
                    );
                }

                if ($filter_data) {
                    $filter_group_data[] = array(
                        'filter_group_id' => $filter_group['filter_group_id'],
                        'name' => $filter_group['name'],
                        'filter' => $filter_data
                    );
                }
            }
        }

        return $filter_group_data;
    }

    public function getCategoryLayoutId($category_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

        if ($query->num_rows) {
            return (int)$query->row['layout_id'];
        } else {
            return 0;
        }
    }

    public function getTotalCategoriesByCategoryId($parent_id = 0)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");

        return $query->row['total'];
    }

    /**
     * Get all categories
     */
    public function getAllCategories()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");

        return $query->rows;
    }

    /**
     * Get all category with count product more 0
     *
     * @param int|null $countryId
     * @return mixed
     */
    public function getCountProductCategory($countryId = null)
    {
        $sql = "SELECT pc.category_id, COUNT(DISTINCT pc.product_id) AS total
                FROM " . DB_PREFIX . "product_to_category AS pc
                LEFT JOIN " . DB_PREFIX . "stockroom_nomenclature AS sn ON pc.product_id=sn.nomenclature_id
                LEFT JOIN " . DB_PREFIX . "stockroom_country AS sc ON sc.stockroom_id=sn.stockroom_id
                LEFT JOIN oc_stockroom_attached sa ON (sa.attach_stockroom_id = sc.stockroom_id)";
        if (!empty($countryId)) {
            $sql .= " WHERE (sc.stockroom_id = sa.attach_stockroom_id OR sc.country_id='" . (int)$countryId . "')
            AND sn.amount > 0";
        }
        $sql .= " GROUP BY pc.category_id";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * Get all active category with name in sort asc parent_id
     *
     * @return mixed
     */
    public function getCategoryActiveName()
    {
        $sql = "SELECT c.category_id, c.parent_id, cd.name FROM oc_category AS c
                LEFT JOIN oc_category_description AS cd ON cd.category_id=c.category_id
                WHERE cd.language_id=" . (int)$this->config->get('config_language_id') .
            " AND c.status=1 ORDER BY c.sort_order, LCASE(cd.name)";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * Get list category
     *
     * @return array
     */
    public function sortCategory()
    {
        $activeCategory = $tempArr = $this->getCategoryActiveName();
        $countProductsCategory = $this->getCountProductCategory($this->config->get('config_current_country'));
        $countCat = [];
        $allCat = [];
        foreach ($countProductsCategory as $item) {
            $countCat[$item['category_id']] = $item['total'];
        }
        foreach ($activeCategory as $item) {
            $allCat[$item['category_id']] = [
                'parent_id' => $item['parent_id'],
                'category_id' => $item['category_id'],
                'name' => $item['name'],
                'count' => (isset($countCat[$item['category_id']]) ? (int)$countCat[$item['category_id']] : 0)
            ];
        }
        $data['categories_menu'] = [];
        foreach ($allCat as $item3) {
            $thisCount = $this->addLavel($item3['category_id'], $activeCategory, (int)$item3['count'], $countCat);
            if ($thisCount > 0) {
                $data['categories_menu'][$item3['parent_id']][] = [
                    'category_id' => $item3['category_id'],
                    'name' => $item3['name'] . ($this->config->get('config_product_count') ? ' (' . $thisCount . ')' : ''),
                    'image' => '',//$image,
                    'href' => ($item3['parent_id']) ? $this->url->link('product/category', 'path=' . $item3['parent_id'] . '_' . $item3['category_id']) : $this->url->link('product/category', 'path=' . $item3['category_id'])
                ];
            }
        }
        return $data['categories_menu'];
    }

    /**
     * @param $id
     * @param $tempArr
     * @param $count
     * @param $countCat
     * @return int
     */
    private function addLavel($id, $tempArr, $count, $countCat)
    {
        $keys = array_keys(array_column($tempArr, 'parent_id'), $id);
        if (!empty($keys)) {
            foreach ($keys as $k) {
                $key = $tempArr[$k]['category_id'];
                $count += (int)(isset($countCat[$key]) ? $countCat[$key] : 0);
                $newC = (int)$this->addLavel($key, $tempArr, 0, $countCat);
                $count += $newC;
            }
        }
        return $count;
    }

    /**
     * Get main parent category from categoryId
     *
     * @param integer $categoryId
     * @return mixed
     */
    public function getParentCategoryId($categoryId)
    {
        $query = $this->db->query("
            SELECT c.category_id, c.parent_id, cd.name FROM oc_category AS c
            LEFT JOIN oc_category_description AS cd ON cd.category_id=c.category_id
            WHERE cd.language_id= '" . (int)$this->config->get('config_language_id') . "'
            AND c.category_id= '" . (int)$categoryId . "'"
        );
        if (!empty($query->row['parent_id'])) {
            return $this->getParentCategoryId($query->row['parent_id']);
        }

        return $query->row;
    }

    /**
     * Get count parent category from categoryId
     *
     * @param integer $categoryId
     * @return mixed
     */
    public function getCheckCategoryParent($categoryId)
    {
        $query = $this->db->query(" SELECT COUNT(category_id) as total FROM oc_category
            WHERE  parent_id = '" . (int)$categoryId . "'"
        );
        if (empty($query->row['total'])) {
            return true;
        }
        return false;
    }
}
