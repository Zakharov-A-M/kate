<?php

class ControllerDesignMainPageCatalogProduct extends Controller
{
    private $countries = [];

    /**
     * View page popular product
     */
	public function index()
    {
        $this->load->language('design/mainPageCatalog/product');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('catalog/product');
        $this->load->model('setting/setting');
        $this->load->model('localisation/country');
        $this->load->model('tool/image');

        $this->countries = $this->model_localisation_country->getCountriesContact();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/mainPageCatalog/product', 'user_token=' . $this->session->data['user_token']));
		}

		$this->getForm();
	}

    /**
     * Get page form from popular product
     */
	protected function getForm()
    {
		$data['text_form'] = $this->language->get('text_edit');

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
			'href' => $this->url->link('design/banner', 'user_token=' . $this->session->data['user_token'])
		];

		$data['action'] = $this->url->link('design/mainPageCatalog/product', 'user_token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($banner_info)) {
			$data['name'] = $banner_info['name'];
		} else {
			$data['name'] = '';
		}
        $data['countries'] = $this->countries;
        $product_info = $this->config->get('config_product_main_page');
        $data['name'] = [];

        foreach ($data['countries'] as $country) {
            if (!empty($product_info[$country['country_id']]['array'])) {
                foreach ($product_info[$country['country_id']]['array'] as $key => $item) {
                    $product = $this->model_catalog_product->getProduct($item);
                    if (is_file(DIR_IMAGE . $product['image'])) {
                        $image = $this->model_tool_image->resize($product['image'], 120, 120);
                    } else {
                        $image = $this->model_tool_image->resize('no_image.png', 120, 120);
                    }

                    $data['product'][$country['country_id']][$key] = [
                        'product_id' => $product['product_id'],
                        'name'       => $product['name'],
                        'image'      => $image,
                        'model'      => $product['model'],
                        'price'      => $this->currency->format(
                            $product['price'],
                            $product['currency'] ?? $this->config->get('config_currency'),
                            true,
                            true
                        ),
                    ];
                }
                $data['name'][$country['country_id']] = $product_info[$country['country_id']]['name'] ?? '';
            }
        }

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/mainPageCatalog/product_form', $data));
	}

	/**
     * Auto complete for search product
     */
	public function autoComplete()
    {
        $json = [];

        if (isset($this->request->get['product'])) {
            $this->load->model('catalog/product');

            $filter_data = array(
                'filter_name' => $this->request->get['product'],
                'sort'        => 'name',
                'order'       => 'ASC',
                'start'       => 0,
                'limit'       => 5,
                'countryId'   => $this->request->get['countryId'],
            );

            $results = $this->model_catalog_product->getNomenclatureForAutoComplete($filter_data);

            foreach ($results as $result) {
                $json[] = [
                    'product_id' => $result['product_id'],
                    'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
                ];
            }
        }

        $sortOrder = [];

        foreach ($json as $key => $value) {
            $sortOrder[$key] = $value['name'];
        }

        array_multisort($sortOrder, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Added new product in
     */
    public function addProduct()
    {
        $this->load->model('catalog/product');
        $this->load->model('setting/setting');
        $this->load->model('localisation/country');
        $this->load->model('tool/image');
        $this->load->language('design/mainPageCatalog/product');

        $json = [];
        if (isset($this->request->get['productId']) && isset($this->request->get['countryId'])) {
            $productInfo = $this->config->get('config_product_main_page');
            if (!empty($productInfo[$this->request->get['countryId']]['array'])) {
                if (!in_array($this->request->get['productId'], $productInfo[$this->request->get['countryId']]['array'])) {
                    array_push($productInfo[$this->request->get['countryId']]['array'], $this->request->get['productId']);
                    $this->model_setting_setting->editSettingValue('config_product_main_page', 'config_product_main_page', $productInfo);

                    $product = $this->model_catalog_product->getProduct($this->request->get['productId']);
                    if (is_file(DIR_IMAGE . $product['image'])) {
                        $image = $this->model_tool_image->resize($product['image'], 120, 120);
                    } else {
                        $image = $this->model_tool_image->resize('no_image.png', 120, 120);
                    }

                    $json['product'] = [
                        'product_id' => $product['product_id'],
                        'name'       => $product['name'],
                        'image'      => $image,
                        'model'      => $product['model'],
                        'countryId'  => $this->request->get['countryId'],
                        'price'      => $this->currency->format(
                            $product['price'],
                            $product['currency'] ?? $this->config->get('config_currency')
                        ),
                    ];

                    $json['status'] = true;
                    $json['text'] = $this->language->get('success_add_product');
                } else {
                    $json['status'] = false;
                    $json['text'] = $this->language->get('error_dublicated_product');
                }
            } else {
                $productInfo[$this->request->get['countryId']] = [
                    'array' => [
                        $this->request->get['productId']
                    ]
                ];
                $this->model_setting_setting->editSettingValue('config_product_main_page', 'config_product_main_page', $productInfo);

                $product = $this->model_catalog_product->getProduct($this->request->get['productId']);
                if (is_file(DIR_IMAGE . $product['image'])) {
                    $image = $this->model_tool_image->resize($product['image'], 120, 120);
                } else {
                    $image = $this->model_tool_image->resize('no_image.png', 120, 120);
                }

                $json['product'] = [
                    'product_id' => $product['product_id'],
                    'name'       => $product['name'],
                    'image'      => $image,
                    'model'      => $product['model'],
                    'countryId'  => $this->request->get['countryId'],
                    'price'      => $this->currency->format(
                        $product['price'],
                        $product['currency'] ?? $this->config->get('config_currency')
                    ),
                ];

                $json['status'] = true;
                $json['text'] = $this->language->get('success_add_product');
            }
        } else {
            $json['status'] = false;
            $json['text'] = $this->language->get('error_product');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Delete product from country
     */
    public function deleteProduct()
    {
        $this->load->model('catalog/product');
        $this->load->model('setting/setting');
        $this->load->model('localisation/country');
        $this->load->model('tool/image');
        $this->load->language('design/mainPageCatalog/product');

        $json = [];
        if (isset($this->request->get['productId']) && isset($this->request->get['countryId'])) {
            $productInfo = $this->config->get('config_product_main_page');
            if (!empty($productInfo[$this->request->get['countryId']]['array'])) {
                if (!in_array($this->request->get['productId'], $productInfo[$this->request->get['countryId']]['array'])) {
                    $json['status'] = false;
                    $json['text'] = $this->language->get('error_not_product');
                } else {
                    foreach ($productInfo[$this->request->get['countryId']]['array'] as $key => $item) {
                        if ($item == $this->request->get['productId']) {
                            unset($productInfo[$this->request->get['countryId']]['array'][$key]);
                            $json['status'] = true;
                            $json['text'] = $this->language->get('success_delete_product');
                        }
                    }
                    $this->model_setting_setting->editSettingValue('config_product_main_page', 'config_product_main_page', $productInfo);
                }
            } else {
                $json['status'] = false;
                $json['text'] = $this->language->get('error_not_product');
            }
        } else {
            $json['status'] = false;
            $json['text'] = $this->language->get('error_product');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Change name popular product
     */
    public function changeName()
    {
        $this->load->model('setting/setting');
        $this->load->language('design/mainPageCatalog/product');

        if (isset($this->request->get['name']) && isset($this->request->get['countryId'])) {
            $productInfo = $this->config->get('config_product_main_page');
            $productInfo[$this->request->get['countryId']]['name'] = $this->request->get['name'];
            $this->model_setting_setting->editSettingValue('config_product_main_page', 'config_product_main_page', $productInfo);
            $json['status'] = true;
            $json['text'] = $this->language->get('success_change_name');
        } else {
            $json['status'] = false;
            $json['text'] = $this->language->get('error_change_name');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
