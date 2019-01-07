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

        $menu = $this->config->get('config_menu');
        foreach ($menu[$this->config->get('config_current_country')]['array'] as $key => $item) {
            $data['menu'][$key] = [
                'title' => $item['title'],
                'link' => $item['link'],
                'sort_order' => $item['sort_order']
            ];
        }
        if (!empty( $data['menu'])) {
            usort($data['menu'], function ($item1, $item2) {
                return $item1['sort_order'] <=> $item2['sort_order'];
            });
        }

        return $this->load->view('common/menu', $data);
    }

    /**
     * View menu in header
     *
     * @return string
     */
    public function getMenuMobile()
    {
        $data = [];

        $menu = $this->config->get('config_menu');
        foreach ($menu[$this->config->get('config_current_country')]['array'] as $key => $item) {
            $data[$key] = [
                'title' => $item['title'],
                'link' => $item['link'],
                'sort_order' => $item['sort_order']
            ];
        }
        if (!empty($data)) {
            usort($data, function ($item1, $item2) {
                return $item1['sort_order'] <=> $item2['sort_order'];
            });
        }

        return $data;
    }

}
