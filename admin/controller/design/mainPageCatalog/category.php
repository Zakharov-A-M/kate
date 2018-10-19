<?php

class ControllerDesignMainPageCatalogCategory extends Controller
{
    private $error = [];
    private $countries = [];

    public function index()
    {
        $this->load->language('design/mainPageCatalog/category');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        $this->load->model('localisation/country');
        $this->countries = $this->model_localisation_country->getCountriesContact();

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            foreach ($this->countries as $country) {
                if (!empty($this->request->post['category'][$country['country_id']])) {
                    foreach ($this->request->post['category'][$country['country_id']] as $key => $value) {
                        $data[$country['country_id']]['array'] =  $this->request->post['category'][$country['country_id']];
                    }
                    if (!empty($this->request->post['name'][$country['country_id']])) {
                        $data[$country['country_id']]['name'] =  $this->request->post['name'][$country['country_id']];
                    }
                    if (!empty($this->request->post['allCategory'][$country['country_id']])) {
                        $data[$country['country_id']]['allCategory'] =  $this->request->post['allCategory'][$country['country_id']];
                    }
                }
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $this->model_setting_setting->editSettingValue('config_category_main_page', 'config_category_main_page', $data);

            $this->response->redirect($this->url->link('design/mainPageCatalog/category', 'user_token=' . $this->session->data['user_token']));
        }

        $this->getForm();
    }

    /**
     * View form for banner in the main page catalog
     */
    protected function getForm()
    {
        $data['text_form'] = $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        if (isset($this->error['title'])) {
            $data['error_title'] = $this->error['title'];
        } else {
            $data['error_title'] = array();
        }

        $url = '';

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_edit_main_page'),
            'href' => ''
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('design/mainPageCatalog/category', 'user_token=' . $this->session->data['user_token'] . $url)
        ];

        $data['action'] = $this->url->link('design/mainPageCatalog/category', 'user_token=' . $this->session->data['user_token']);

        $category_info = $this->config->get('config_category_main_page');

        $data['user_token'] = $this->session->data['user_token'];

        $data['countries'] = $this->countries;

        $this->load->model('tool/image');

        foreach ($data['countries'] as $country) {
            if (!empty($category_info[$country['country_id']]['array'])) {
                foreach ($category_info[$country['country_id']]['array'] as $key => $item) {
                    if (is_file(DIR_IMAGE . $item['image'])) {
                        $image = $item['image'];
                        $thumb = $item['image'];
                    } else {
                        $image = '';
                        $thumb = 'no_image.png';
                    }

                    $this->load->model('catalog/category');
                    $nameCategory = '';
                    if (!empty($item['link'])) {
                        $category = $this->model_catalog_category->getCategory($item['link']);
                        if (!empty($category['name'])) {
                            $nameCategory = $category['name'];
                        }
                    }

                    $data['category'][$country['country_id']][$key] = [
                        'title'      => $item['title'] ?? '',
                        'link'       => $item['link'] ?? '',
                        'category'   => $nameCategory,
                        'image'      => $image,
                        'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
                        'sort_order' => $item['sort_order']
                    ];
                }
                $data['name'][$country['country_id']]['name'] = $category_info[$country['country_id']]['name'] ?? '';
                $data['allCategory'][$country['country_id']] = [
                    'status' => $category_info[$country['country_id']]['allCategory']['status'] ?? 0,
                    'link'   => $category_info[$country['country_id']]['allCategory']['link'] ?? 0,
                    'text'   => $category_info[$country['country_id']]['allCategory']['text'] ?? 0,
                ];
            }
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('design/mainPageCatalog/category_form', $data));
    }

    /**
     * Auto complete category
     */
    public function autoComplete()
    {
        $json = [];

        if (isset($this->request->get['category']) && isset($this->request->get['countryId'])) {

            $filter_data = array(
                'filter_name' => $this->request->get['category'],
                'sort'        => 'name',
                'order'       => 'ASC',
                'start'       => 0,
                'limit'       => 100
            );

            $this->load->model('catalog/category');

            $results = $this->model_catalog_category->getCategoryMainPage($filter_data);
            foreach ($results as $result) {
                if (!empty($result['children'])) {
                    $count = $this->model_catalog_category->getCategoryProductCount(
                        $result['children']['category_id'],
                        $this->request->get['countryId']
                    );
                    if (!empty($count['total']) && $count['total'] > 0) {
                        $json[] = [
                            'category_id' =>  $result['children']['category_id'],
                            'name'        => strip_tags(
                                html_entity_decode(
                                    $result['parent']['name'] . ' > '. $result['children']['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                            )
                        ];
                    }
                } else {
                    $count = $this->model_catalog_category->getTotalProducts(
                        $result['parent']['category_id'],
                        $this->request->get['countryId']
                    );
                    if (!empty($count) && $count > 0) {
                        $json[] = [
                            'category_id' =>  $result['parent']['category_id'],
                            'name'        => strip_tags(
                                html_entity_decode($result['parent']['name'], ENT_QUOTES, 'UTF-8')
                            )
                        ];
                    }
                }
            }
            $json = array_slice($json,0, 5);
        }

        $sortOrder = [];

        foreach ($json as $key => $value) {
            $sortOrder[$key] = $value['name'];
        }

        array_multisort($sortOrder, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
