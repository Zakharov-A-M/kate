<?php

class ControllerCommonMenu extends Controller
{
    /**
     * View menu in header
     *
     * @return string
     */
    public function index()
    {
        $this->load->language('common/menu');

        // Menu
        $this->load->model('catalog/category');

        $this->load->model('catalog/product');

        $data['categories'] = [];

        $data['categories'][] = [
            'text'     => $this->language->get('category'),
            'href'     => $this->url->link('product/'. 'category')
        ];

        $this->load->model('catalog/information');

        $infoDelivery = $this->model_catalog_information->getInformation(6);
        if ($infoDelivery) {
            $data['categories'][] = [
                'text' => $infoDelivery['title'],
                'href' => $this->url->link('information/delivery')
            ];
        }

        $data['categories'][] = [
            'text'     => $this->language->get('articles'),
            'href'     => $this->url->link('information/articles')
        ];

        $infoAbount = $this->model_catalog_information->getInformation(4);
        if ($infoAbount) {
            $data['categories'][] = [
                'text' => $infoAbount['title'],
                'href' => $this->url->link('information/about')
            ];
        }

        $data['categories'][] = [
            'text'     => $this->language->get('contact'),
            'href'     => $this->url->link('information/contact')
        ];


        $this->load->language('extension/module/category');

        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
        } else {
            $parts = array();
        }

        if (isset($parts[0])) {
            $data['category_id'] = $parts[0];
            if (!empty($parts[1])) {
                $data['category_id_current'] = $parts[1];
            } else {
                $data['category_id_current'] = $data['category_id'];
            }
        } else {
            $data['category_id_current'] = 0;
            $data['category_id'] = 0;
        }

        if (isset($parts[1])) {
            $data['child_id'] = $parts[1];
        } else {
            $data['child_id'] = 0;
        }

        $this->load->model('catalog/category');

        $this->load->model('catalog/product');

        $data['categories_menu'] = [];

        $data['categories_menu'] = $this->model_catalog_category->sortCategory();

        return $this->load->view('common/menu', $data);
    }

}
