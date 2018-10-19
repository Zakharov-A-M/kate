<?php

class ControllerProductSearch extends Controller
{
    /**
     * View page search product
     */
	public function index()
    {
		$this->load->language('product/search');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

        if ($this->cart->hasProducts()) {
            $productsCart = $this->cart->getProducts();
        }

		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		if (isset($this->request->get['tag'])) {
			$tag = $this->request->get['tag'];
		} elseif (isset($this->request->get['search'])) {
			$tag = $this->request->get['search'];
		} else {
			$tag = '';
		}

		if (isset($this->request->get['description'])) {
			$description = $this->request->get['description'];
		} else {
			$description = '';
		}

		if (isset($this->request->get['category_id'])) {
			$category_id = $this->request->get['category_id'];
		} else {
			$category_id = 0;
		}

		if (isset($this->request->get['sub_category'])) {
			$sub_category = $this->request->get['sub_category'];
		} else {
			$sub_category = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		if (isset($this->request->get['search'])) {
			$this->document->setTitle($this->language->get('heading_title') .  ' - ' . $this->request->get['search']);
		} elseif (isset($this->request->get['tag'])) {
			$this->document->setTitle($this->language->get('heading_title') .  ' - ' . $this->language->get('heading_tag') . $this->request->get['tag']);
		} else {
			$this->document->setTitle($this->language->get('heading_title'));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$url = '';

		if (isset($this->request->get['search'])) {
			$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['tag'])) {
			$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['description'])) {
			$url .= '&description=' . $this->request->get['description'];
		}

		if (isset($this->request->get['category_id'])) {
			$url .= '&category_id=' . $this->request->get['category_id'];
		}

		if (isset($this->request->get['sub_category'])) {
			$url .= '&sub_category=' . $this->request->get['sub_category'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('product/search', $url)
		);

		if (isset($this->request->get['search'])) {
			$data['heading_title'] = $this->language->get('heading_title') .  ' - ' . $this->request->get['search'];
		} else {
			$data['heading_title'] = $this->language->get('heading_title');
		}

		$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

		$data['compare'] = $this->url->link('product/compare');

		$this->load->model('catalog/category');
        $data['linkCheckOut'] = $this->url->link('checkout/uni_checkout');

		// 3 Level Category Search
        $data['categories'] = [];
        $this->config->set('config_product_count', 0);
        $data['categories'] = $this->model_catalog_category->sortCategory();
        $this->config->set('config_product_count', 1);

		$data['products'] = [];

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$filter_data = array(
				'filter_name'         => $search,
				'filter_tag'          => $tag,
				'filter_description'  => $description,
				'filter_category_id'  => $category_id,
				'filter_sub_category' => $sub_category,
				'sort'                => $sort,
				'order'               => $order,
				'start'               => ($page - 1) * $limit,
				'limit'               => $limit
			);

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->currency->convert($result['price'], $result['currency'], $this->session->data['currency']), $this->session->data['currency'], true);
                } else {
					$price = false;
				}

				if ((float)$result['special'] && $this->customer->isLogged()) {
                    $special = $this->currency->format($this->currency->convert($result['special'], $result['currency'], $this->session->data['currency']), $this->session->data['currency'], true);
                } else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

                if (!empty($productsCart)) {
                    foreach ($productsCart as $product) {
                        if ($product['product_id'] == $result['product_id']) {
                            $inCart = true;
                            break;
                        } else {
                            $inCart = false;
                        }
                    }
                } else {
                    $inCart = false;
                }

				$data['products'][] = [
                    'amount'      => $result['amount'],
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
                    'model'       => $result['model'],
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
                    'inCart'      => $inCart,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $result['rating'],
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'] . $url)
				];
			}

			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = $this->generateUrl($url);

			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

            $params = '';

            if (!empty($this->request->get['sort'])) {
                foreach ($this->request->get['sort'] as $item) {
                    $params .= '&sort[]=' . $item;
                }
                $url .= $params;
            }

			$data['limits'] = array();

			$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

			sort($limits);

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/search', $url . '&limit=' . $value)
				);
			}

			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
                $url .= $params;
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/search', $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			if (isset($this->request->get['search']) && $this->config->get('config_customer_search')) {
				$this->load->model('account/search');

				if ($this->customer->isLogged()) {
					$customer_id = $this->customer->getId();
				} else {
					$customer_id = 0;
				}

				if (isset($this->request->server['REMOTE_ADDR'])) {
					$ip = $this->request->server['REMOTE_ADDR'];
				} else {
					$ip = '';
				}

				$search_data = array(
					'keyword'       => $search,
					'category_id'   => $category_id,
					'sub_category'  => $sub_category,
					'description'   => $description,
					'products'      => $product_total,
					'customer_id'   => $customer_id,
					'ip'            => $ip
				);

				$this->model_account_search->addSearch($search_data);
			}
		}

		$data['search'] = $search;
		$data['description'] = $description;
		$data['category_id'] = $data['current'] = $category_id;
		$data['sub_category'] = $sub_category;

		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('product/search', $data));
	}

    /**
     * Generate url from selected sort
     * @param $url
     * @return mixed
     */
    public function generateUrl($url)
    {
        $data['sort_name'] = [];
        $params = '';
        $flag = true;
        if (!empty($this->request->get['sort'])) {
            $getUrl = $this->request->get['sort'];
            if (in_array('pd.name-DESC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'pd.name-DESC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = false;
            }
            if (in_array('pd.name-ASC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'pd.name-ASC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = false;
            }
            foreach ($getUrl as $item) {
                $params .= '&sort[]='.$item ;
            }
        }
        $data['sort_name'][0] = [
            'text'  => $this->language->get('text_default'),
            'flag'  =>  $flag,
            'path' => $this->url->link('product/search', $url . $params)
        ];

        $params = '';
        $flag = false;
        if (!empty($this->request->get['sort'])) {
            $getUrl = $this->request->get['sort'];
            if (in_array('pd.name-DESC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'pd.name-DESC') {
                        unset($getUrl[$key]);
                    }
                    $flag = false;
                }
            }
            if (in_array('pd.name-ASC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'pd.name-ASC') {
                        unset($getUrl[$key]);;
                    }
                }
                $flag = true;
            }
            foreach ($getUrl as $item) {
                $params .= '&sort[]=' . $item;
            }
        }
        $params .= '&sort[]=pd.name-ASC';
        $data['sort_name'][1] = [
            'text'  => $this->language->get('text_name_asc'),
            'flag'  =>  $flag,
            'path' => $this->url->link('product/search', $url . $params)
        ];

        $params = '';
        $flag = false;
        if (!empty($this->request->get['sort'])) {
            $getUrl = $this->request->get['sort'];
            if (in_array('pd.name-DESC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'pd.name-DESC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = true;
            }
            if (in_array('pd.name-ASC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'pd.name-ASC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = false;
            }
            foreach ($getUrl as $item) {
                $params .= '&sort[]=' . $item;
            }
        }
        $params .= '&sort[]=pd.name-DESC';
        $data['sort_name'][2] = [
            'text'  => $this->language->get('text_name_desc'),
            'flag'  =>  $flag,
            'path' => $this->url->link('product/search', $url . $params)
        ];


        //model
        $data['sort_model'] = [];
        $params = '';
        $flag = true;
        if (!empty($this->request->get['sort'])) {
            $getUrl = $this->request->get['sort'];
            if (in_array('p.model-ASC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'p.model-ASC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = false;
            }
            if (in_array('p.model-DESC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'p.model-DESC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = false;
            }
            foreach ($getUrl as $item) {
                $params .= '&sort[]='.$item ;
            }
        }
        $data['sort_model'][0] = [
            'text'  => $this->language->get('text_default'),
            'flag'  =>  $flag,
            'path' => $this->url->link('product/search', $url . $params)
        ];

        $params = '';
        $flag = false;
        if (!empty($this->request->get['sort'])) {
            $getUrl = $this->request->get['sort'];
            if (in_array('p.model-ASC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'p.model-ASC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = true;
            }
            if (in_array('p.model-DESC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'p.model-DESC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = false;
            }
            foreach ($getUrl as $item) {
                $params .= '&sort[]=' . $item;
            }
        }
        $params .= '&sort[]=p.model-ASC';
        $data['sort_model'][1] = [
            'text'  => $this->language->get('text_model_asc'),
            'flag'  =>  $flag,
            'path' => $this->url->link('product/search', $url . $params)
        ];

        $params = '';
        $flag = false;
        if (!empty($this->request->get['sort'])) {
            $getUrl = $this->request->get['sort'];
            if (in_array('p.model-ASC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'p.model-ASC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = false;
            }
            if (in_array('p.model-DESC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'p.model-DESC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = true;
            }
            foreach ($getUrl as $item) {
                $params .= '&sort[]=' . $item;
            }
        }
        $params .= '&sort[]=p.model-DESC';
        $data['sort_model'][2] = [
            'text'  => $this->language->get('text_model_desc'),
            'flag'  =>  $flag,
            'path' => $this->url->link('product/search', $url . $params)
        ];

        //price
        $data['sort_price'] = [];
        $params = '';
        $flag = true;
        if (!empty($this->request->get['sort'])) {
            $getUrl = $this->request->get['sort'];
            if (in_array('p.price-ASC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'p.price-ASC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = false;
            }
            if (in_array('p.price-DESC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'p.price-DESC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = false;
            }
            foreach ($getUrl as $item) {
                $params .= '&sort[]=' . $item;
            }
        }
        $data['sort_price'][0] = [
            'text'  => $this->language->get('text_default'),
            'flag'  =>  $flag,
            'path' => $this->url->link('product/search', $url . $params)
        ];

        $params = '';
        $flag = false;
        if (!empty($this->request->get['sort'])) {
            $getUrl = $this->request->get['sort'];
            if (in_array('p.price-ASC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'p.price-ASC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = true;
            }
            if (in_array('p.price-DESC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'p.price-DESC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = false;
            }
            foreach ($getUrl as $item) {
                $params .= '&sort[]=' . $item;
            }
        }
        $params .= '&sort[]=p.price-ASC';
        $data['sort_price'][1] = [
            'text'  => $this->language->get('text_price_asc'),
            'flag'  =>  $flag,
            'path' => $this->url->link('product/search', $url . $params)
        ];

        $params = '';
        $flag = false;
        if (!empty($this->request->get['sort'])) {
            $getUrl = $this->request->get['sort'];
            if (in_array('p.price-ASC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'p.price-ASC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = false;
            }
            if (in_array('p.price-DESC', $getUrl)) {
                foreach ($getUrl as $key => $item) {
                    if ($item == 'p.price-DESC') {
                        unset($getUrl[$key]);
                    }
                }
                $flag = true;
            }
            foreach ($getUrl as $item) {
                $params .= '&sort[]=' . $item;
            }
        }
        $params .= '&sort[]=p.price-DESC';
        $data['sort_price'][2] = [
            'text'  => $this->language->get('text_price_desc'),
            'flag'  =>  $flag,
            'path' => $this->url->link('product/search', $url . $params)
        ];
        return $data;
    }
}
