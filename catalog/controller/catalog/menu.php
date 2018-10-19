<?php

class ControllerCatalogMenu extends Controller
{
    /**
     * View menu in category for product
     *
     * @return string
     */
    public function index()
    {
        $data = [];
        $this->load->language('common/menu');
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');

        $data['categories'] = [];
        $categories = $this->model_catalog_category->getCategories(0);
        foreach ($categories as $category) {
            if ($category['top']) {
                // Level 2
                $childrenData = [];
                $children = $this->model_catalog_category->getCategories($category['category_id']);
                foreach ($children as $child) {
                    $filterData = [
                        'filter_category_id'  => $child['category_id'],
                        'filter_sub_category' => true
                    ];
                    $childrenData[] = [
                        'id'    => $child['category_id'],
                        'name'  => $child['name'] . ($this->config->get('config_product_count') ?
                                ' (' . $this->model_catalog_product->getTotalProducts($filterData) . ')' :
                                ''
                            ),
                        'href'  => $this->url->link(
                            'product/category',
                            'path=' . $category['category_id'] . '_' . $child['category_id']
                        )
                    ];
                }

                $this->load->model('tool/image');
                if (!empty($category['image']) && is_file(DIR_IMAGE . $category['image'])) {
                    $image = $this->model_tool_image->resize($category['image'], 20, 20);
                } else {
                    $image = $this->model_tool_image->resize('no_image.png', 20, 20);
                }

                $data['categories'][] = [
                    'id'       => $category['category_id'],
                    'name'     => $category['name'],
                    'image'    => $image,
                    'children' => $childrenData,
                    'column'   => $category['column'] ? $category['column'] : 1,
                    'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
                ];
            }
        }

        if (!empty($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
        } else {
            $parts = [];
        }

        if (isset($parts[0])) {
            $data['category_id'] = $parts[0];
        } else {
            $data['category_id'] = 0;
        }

        if (isset($parts[1])) {
            $data['child_id'] = $parts[1];
        } else {
            $data['child_id'] = 0;
        }

        return $this->load->view('catalog/menu', $data);
    }
}
