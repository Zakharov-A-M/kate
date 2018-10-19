<?php
class ControllerExtensionModuleUniLiveSearch extends Controller {
	public function index() {
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		
		$this->load->language('extension/module/uni_othertext');

		$language_id = $this->config->get('config_language_id');

		$data['search_description'] = isset($uniset['search_description']) ? true : false;

		$data['products'] = [];
		
		$search = trim($this->request->get['filter_name']);
		$category_id = isset($this->request->get['category_id']) ? $this->request->get['category_id'] : '';
		
		$data['search_phrase'] = urlencode($search);
		
		$currency = $this->session->data['currency'];
		
		if ($search) {
			$filter_data = array(
				'filter_name'         => $search,
				'filter_tag'          => $search,
				//'filter_description'  => $search_description,
				'filter_category_id'  => $category_id,
				'filter_sub_category' => 1,
				//'sort'                => $search_sort,
				//'order'               => $search_order,
				'start'               => 0,
				'limit'               => 5
			);
				
			$results = $this->model_catalog_product->getProducts($filter_data);
			$results_total = $this->model_catalog_product->getTotalProducts($filter_data);

			foreach ($results as $result) {
                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                }

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->currency->convert($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $result['currency'], $this->session->data['currency']), $this->session->data['currency'], true);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
                    $special = $this->currency->format($this->currency->convert($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $result['currency'], $this->session->data['currency']), $this->session->data['currency'], true);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $currency);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}
					
				$data['products'][] = array(
					'product_id'  	=> $result['product_id'],
					'image'      	=> $image,
					'name' 			=> $result['name'],
					'description' 	=> strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')),
					'rating'		=>  -1,
					'price'      	=>  $price,
					'special'     	=> $special,
					'href'       	=> $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}
			
			$data['products_total'] = $results_total;
			
			$data['show_more'] = $results_total > 5 ? true : false;
		}
		
		$this->response->setOutput($this->load->view('extension/module/uni_live_search', $data));
	}
}
