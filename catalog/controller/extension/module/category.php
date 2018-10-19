<?php

class ControllerExtensionModuleCategory extends Controller
{
    /**
     * @return string
     */
    public function index()
    {
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

        $data['categories'] = array();

        $data['categories'] = $this->model_catalog_category->sortCategory();
        if (!empty($this->session->data['customer_id'])) {
            $data['specials'] = $this->model_catalog_product->getTotalProductSpecials();
            $data['specials_link'] = $this->url->link('information/discount');
        }

        return $this->load->view('extension/module/category', $data);
    }

}
