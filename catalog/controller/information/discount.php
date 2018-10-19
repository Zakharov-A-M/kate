<?php

class ControllerInformationDiscount extends Controller
{
    /**
     * View page discount
     */
    public function index()
    {
        $this->load->language('information/discount');
        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/delivery')
        ];

        if ($this->cart->hasProducts()) {
            $productsCart = $this->cart->getProducts();
        }
        $data['linkCheckOut'] = $this->url->link('checkout/uni_checkout');
        $url = '';

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'p.sort_order';
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

        $this->load->model('catalog/product');
        $filterData = [
            'sort' => $sort,
            'start' => ($page - 1) * $limit,
            'limit' => $limit,
        ];

        $productTotal = $this->model_catalog_product->getTotalProductSpecials();
        $products = $this->model_catalog_product->getProductAllSpecials($filterData);

        if ($products) {
            $data['text_compare'] = sprintf(
                $this->language->get('text_compare'),
                (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0)
            );
            $data['compare'] = $this->url->link('product/compare');

            foreach ($products as $result) {
                $this->load->model('tool/image');
                if ($result['image']) {
                    $image = $this->model_tool_image->resize(
                        $result['image'],
                        $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
                        $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')
                    );
                } else {
                    $image = $this->model_tool_image->resize(
                        'placeholder.png',
                        $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
                        $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')
                    );
                }

                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format(
                        $this->currency->convert(
                            $result['price'],
                            $result['currency'],
                            $this->session->data['currency']
                        ),
                        $this->session->data['currency'],
                        true
                    );
                } else {
                    $price = false;
                }

                if ((float)$result['special']) {
                    $special = $this->currency->format(
                        $this->currency->convert(
                            $this->tax->calculate(
                                $result['special'],
                                $result['tax_class_id'],
                                $this->config->get('config_tax')
                            ),
                            $result['currency'],
                            $this->session->data['currency']
                        ),
                        $this->session->data['currency'],
                        true
                    );
                } else {
                    $special = false;
                }

                if (!empty($productsCart)) {
                    $inCart = $this->checkProductInCart($productsCart, $result);
                } else {
                    $inCart = false;
                }

                $data['products'][] = [
                    'product_id' => $result['product_id'],
                    'thumb' => $image,
                    'name' => $result['name'],
                    'amount' => $result['amount'],
                    'model' => $result['model'],
                    'price' => $price,
                    'special' => $special,
                    'inCart' => $inCart,
                    'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
                    'rating' => $result['rating'],
                    'href' => $this->url->link(
                        'product/product',
                        'product_id=' . $result['product_id'] . $url
                    )
                ];
            }
            $url = '';

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $data['sorts'] = $this->generateUrl($url);

            $url = '';
            $params = '';

            if (!empty($this->request->get['sort'])) {
                foreach ($this->request->get['sort'] as $item) {
                    $params .= '&sort[]=' . $item;
                }
                $url .= $params;
            }

            $data['limits'] = [];

            $limits = array_unique(array(
                $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'),
                25,
                50,
                75,
                100
            ));
            sort($limits);
            foreach ($limits as $value) {
                $data['limits'][] = array(
                    'text'  => $value,
                    'value' => $value,
                    'href'  => $this->url->link('information/discount', $url . '&limit=' . $value)
                );
            }

            $url = '';

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
            $pagination->total = $productTotal;
            $pagination->page = $page;
            $pagination->limit = $limit;
            $pagination->url = $this->url->link('information/discount', $url . '&page={page}');

            $data['pagination'] = $pagination->render();

            $data['results'] = sprintf(
                $this->language->get('text_pagination'),
                ($productTotal) ? (($page - 1) * $limit) + 1 : 0,
                ((($page - 1) * $limit) > ($productTotal - $limit)) ? $productTotal : ((($page - 1) * $limit) + $limit),
                $productTotal,
                ceil($productTotal / $limit)
            );
        }

        $data['limit'] = $limit;
        $data['sort'] = $sort;
        $data['continue'] = $this->url->link('common/home');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('information/discount', $data));
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
            'path' => $this->url->link('information/discount', $url . $params)
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
                        unset($getUrl[$key]);
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
            'path' => $this->url->link('information/discount', $url . $params)
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
            'path' => $this->url->link('information/discount', $url . $params)
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
            'path' => $this->url->link('information/discount', $url . $params)
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
            'path' => $this->url->link('information/discount', $url . $params)
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
            'path' => $this->url->link('information/discount', $url . $params)
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
            'path' => $this->url->link('information/discount', $url . $params)
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
            'path' => $this->url->link('information/discount', $url . $params)
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
            'path' => $this->url->link('information/discount', $url . $params)
        ];
        return $data;
    }

    /***
     * Check product in cart
     *
     * @param array $productsCart
     * @param array $result
     * @return bool
     */
    public function checkProductInCart(array $productsCart, array $result)
    {
        $inCart = false;
        foreach ($productsCart as $product) {
            if ($product['product_id'] == $result['product_id']) {
                return true;
            } else {
                $inCart = false;
            }
        }
        return $inCart;
    }
}
