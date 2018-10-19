<?php

class ControllerProductCategory extends Controller
{
    /**
     * View categoty  in page product
     */
    public function index()
    {
        $this->load->language('product/category');

        $this->load->model('catalog/category');

        $this->load->model('catalog/product');

        $this->load->model('tool/image');

        if ($this->cart->hasProducts()) {
            $productsCart = $this->cart->getProducts();
        }

        if (isset($this->request->get['filter'])) {
            $filter = $this->request->get['filter'];
        } else {
            $filter = '';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'p.sort_order';
        }

        if (isset($this->request->get['manufacturer'])) {
            $manufacturer = $this->request->get['manufacturer'];
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

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];

        if (isset($this->request->get['path'])) {
            $url = '';
            $parts = explode('_', (string)$this->request->get['path']);
            $categoryId = (int)array_pop($parts);

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . http_build_query($this->request->get['sort']);
            }
            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            if (count($parts) > 0 || $this->model_catalog_category->getCheckCategoryParent($categoryId)) {
                $categoryInfo = $this->model_catalog_category->getParentCategoryId($categoryId);
                if ($categoryInfo) {
                    $data['breadcrumbs'][] = array(
                        'text' => $categoryInfo['name'],
                        'href' => $this->url->link(
                            'product/category',
                            'path=' . $categoryInfo['category_id'] . $url
                        )
                    );
                }
            }
        } else {
            $categoryId = 0;
        }

        $data['linkCheckOut'] = $this->url->link('checkout/uni_checkout');
        $categoryInfo = $this->model_catalog_category->getCategory($categoryId);

        if ($categoryInfo) {
            $this->document->setTitle($categoryInfo['meta_title']);
            $this->document->setDescription($categoryInfo['meta_description']);
            $this->document->setKeywords($categoryInfo['meta_keyword']);

            $data['heading_title'] = $categoryInfo['name'];

            $data['text_compare'] = sprintf(
                $this->language->get('text_compare'),
                (
                    isset($this->session->data['compare']) ?
                    count($this->session->data['compare']) :
                    0
                )
            );

            // Set the last category breadcrumb
            $data['breadcrumbs'][] = [
                'text' => $categoryInfo['name'],
                'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'])
            ];

            if ($categoryInfo['image']) {
                $data['thumb'] = $this->model_tool_image->resize(
                    $categoryInfo['image'],
                    $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'),
                    $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height')
                );
            } else {
                $data['thumb'] = '';
            }

            $data['description'] = html_entity_decode(
                $categoryInfo['description'],
                ENT_QUOTES,
                'UTF-8'
            );

            $url = '';

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . http_build_query($this->request->get['sort']);
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $data['categories'] = [];
            $data['products'] = [];

            $filterData = [
                'filter_category_id'    => $categoryId,
                'filter_sub_category'   => true,
                'filter_filter'         => $filter,
                'sort'                  => $sort,
                'order'                 => $order,
                'start'                 => ($page - 1) * $limit,
                'limit'                 => $limit,
                'filter_manufacturer_id'=> $manufacturer ?? ''
            ];

            $productTotal = $this->model_catalog_product->getTotalProducts($filterData);

            $results = $this->model_catalog_product->getProducts($filterData);

            foreach ($results as $result) {
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
                            $this->tax->calculate(
                                $result['price'],
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

                if ($this->config->get('config_tax')) {
                    $tax = $this->currency->format(
                        (float)$result['special'] ? $result['special'] : $result['price'],
                        $this->session->data['currency']
                    );
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
                    'product_id'  => $result['product_id'],
                    'thumb'       => $image,
                    'name'        => $result['name'],
                    'amount'      => $result['amount'],
                    'model'       => $result['model'],
                    'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                    'price'       => $price,
                    'special'     => $special,
                    'inCart'      => $inCart,
                    'tax'         => $tax,
                    'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
                    'rating'      => $result['rating'],
                    'href'        => $this->url->link(
                        'product/product',
                        'path=' . $this->request->get['path'] . '&product_id=' . $result['product_id'] . $url
                    )
                ];
            }

            $url = '';

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $data['sorts'] = $this->generateUrl();
            $url = '';

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . http_build_query($this->request->get['sort']);
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            $data['limits'] = [];

            $limits = array_unique([
                $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'),
                25,
                50,
                75,
                100
            ]);

            sort($limits);
            $params = '';
            $url = '';

            if (!empty($this->request->get['sort'])) {
               foreach ($this->request->get['sort'] as $item) {
                   $params .= '&sort[]=' . $item;
               }
               $url .= $params;
            }

            foreach($limits as $value) {
                $data['limits'][] = [
                    'text'  => $value,
                    'value' => $value,
                    'href'  => $this->url->link(
                        'product/category',
                        'path=' . $this->request->get['path'] . $params . '&limit=' . $value
                    )
                ];
            }



            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
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
            $pagination->url = $this->url->link(
                'product/category',
                'path=' . $this->request->get['path'] . $url . '&page={page}'
            );

            $data['pagination'] = $pagination->render();

            $data['results'] = sprintf(
                $this->language->get('text_pagination'),
                ($productTotal) ? (($page - 1) * $limit) + 1 : 0,
                (
                    (($page - 1) * $limit) > ($productTotal - $limit)
                ) ? $productTotal : ((($page - 1) * $limit) + $limit),
                $productTotal,
                ceil($productTotal / $limit)
            );

            // http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
            if ($page == 1) {
                $this->document->addLink(
                    $this->url->link('product/category', 'path=' . $categoryInfo['category_id']),
                    'canonical'
                );
            } else {
                $this->document->addLink(
                    $this->url->link(
                        'product/category',
                        'path=' . $categoryInfo['category_id'] . '&page='. $page
                    ), 'canonical'
                );
            }

            if ($page > 1) {
                $this->document->addLink(
                    $this->url->link(
                        'product/category',
                        'path=' . $categoryInfo['category_id'] . (
                            ($page - 2) ? '&page='. ($page - 1) : '')
                    ),
                    'prev'
                );
            }

            if ($limit && ceil($productTotal / $limit) > $page) {
                $this->document->addLink(
                    $this->url->link(
                        'product/category',
                        'path=' . $categoryInfo['category_id'] . '&page='. ($page + 1)
                    ), 'next'
                );
            }

            $data['sort'] = $sort;
            $data['order'] = $order;
            $data['limit'] = $limit;

            $data['continue'] = $this->url->link('common/home');

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('product/category', $data));
        } else {
            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_category'),
                'href' => $this->url->link('common/home')
            ];

            $this->document->setTitle($this->language->get('text_category'));
            $this->document->setDescription($this->language->get('text_category'));
            $this->document->setKeywords($this->language->get('text_category'));

            $data['products'] = [];
            $data['compare'] = $this->url->link('product/compare');
            $data['text_compare'] = sprintf(
                $this->language->get('text_compare'),
                (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0)
            );

            $url = '';

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            if (isset($this->request->get['manufacturer'])) {
                $manufacturer = $this->request->get['manufacturer'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . http_build_query($this->request->get['sort']);
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $filterData = array(
                'filter_sub_category'    => true,
                'filter_filter'          => $filter,
                'sort'                   => $sort,
                'order'                  => $order,
                'start'                  => ($page - 1) * $limit,
                'limit'                  => $limit,
                'filter_manufacturer_id' => $manufacturer ?? ''
            );

            $productTotal = $this->model_catalog_product->getTotalProducts($filterData);

            $results = $this->model_catalog_product->getProducts($filterData);
            $userAutorize = false;

            if(!empty($this->user)&&$this->user->isLogged()){
                $userAutorize = true;
            }

            foreach ($results as $result) {
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
                            $this->tax->calculate(
                                $result['price'],
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
                    $price = false;
                }

                if ((float)$result['special']) {
                    $special = $this->currency->format(
                        $this->currency->convert(
                            $this->tax->calculate(
                                $result['special'],
                                $result['tax_class_id'],
                                $this->config->get('config_tax')
                            ), $result['currency'],
                            $this->session->data['currency']
                        ),
                        $this->session->data['currency'],
                        true
                    );
                } else {
                    $special = false;
                }

                if ($this->config->get('config_tax')) {
                    $tax = $this->currency->format(
                        (float)$result['special'] ? $result['special'] : $result['price'],
                        $this->session->data['currency']
                    );
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
                    'product_id'  => $result['product_id'],
                    'thumb'       => $image,
                    'name'        => $result['name'],
                    'amount'      => $result['amount'],
                    'model'       => $result['model'],
                    'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                    'price'       => $price,
                    'special'     => ($userAutorize?$special:false),
                    'inCart'      => $inCart,
                    'tax'         => $tax,
                    'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
                    'rating'      => $result['rating'],
                    'href'        => $this->url->link(
                        'product/product',
                        'product_id=' . $result['product_id'] . $url
                    )
                ];
            }

            $url = '';

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $data['sorts'] = $this->generateUrl();

            $url = '';

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            $params = '';

            if (!empty($this->request->get['sort'])) {
                foreach ($this->request->get['sort'] as $item) {
                    $params .= '&sort[]=' . $item;
                }
                $url .= $params;
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            $data['limits'] = [];

            $limits = array_unique([
                $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'),
                25,
                50,
                75,
                100
            ]);

            sort($limits);

            foreach($limits as $value) {
                $data['limits'][] = [
                    'text'  => $value,
                    'value' => $value,
                    'href'  => $this->url->link('product/category',  $params . '&limit=' . $value)
                ];
            }

            $url = '';

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
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
            $pagination->total = $productTotal;
            $pagination->page = $page;
            $pagination->limit = $limit;
            $pagination->url = $this->url->link('product/category',  $url . '&page={page}');

            $data['pagination'] = $pagination->render();

            $data['results'] = sprintf(
                $this->language->get('text_pagination'),
                ($productTotal) ? (($page - 1) * $limit) + 1 : 0,
                (
                    (($page - 1) * $limit) > ($productTotal - $limit)
                ) ?
                    $productTotal : ((($page - 1) * $limit) + $limit
                ),
                $productTotal,
                ceil($productTotal / $limit)
            );

            if ($page == 1) {
                $this->document->addLink($this->url->link('product/category'), 'canonical');
            } else {
                $this->document->addLink(
                    $this->url->link('product/category', 'page='. $page), 'canonical'
                );
            }

            if ($page > 1) {
                $this->document->addLink(
                    $this->url->link('product/category', (($page - 2) ? 'page='. ($page - 1) : '')),
                    'prev'
                );
            }

            if ($limit && ceil($productTotal / $limit) > $page) {
                $this->document->addLink(
                    $this->url->link('product/category', 'page='. ($page + 1)),
                    'next'
                );
            }

            $data['sort'] = $sort;
            $data['order'] = $order;
            $data['limit'] = $limit;

            $data['continue'] = $this->url->link('common/home');

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('product/category', $data));
        }
    }

    /**
     * Generate url from selected sort
     *
     * @return mixed
     */
    public function generateUrl()
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
            'path'  => $this->url->link(
                'product/category',
                (!empty($this->request->get['path'])) ?
                    '&path=' . $this->request->get['path'] . $params :
                    " " . $params
            )
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
            'path'  => $this->url->link(
                'product/category',
                (!empty($this->request->get['path'])) ?
                    '&path=' . $this->request->get['path'] . $params :
                    " " . $params
            )
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
            'path' => $this->url->link(
                'product/category',
                (!empty($this->request->get['path'])) ?
                    '&path=' . $this->request->get['path'] . $params :
                    " " . $params
            )
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
            'path' => $this->url->link(
                'product/category',
                (!empty($this->request->get['path'])) ?
                    '&path=' . $this->request->get['path'] . $params :
                    " " . $params
            )
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
            'path' => $this->url->link(
                'product/category',
                (!empty($this->request->get['path'])) ?
                    '&path=' . $this->request->get['path'] . $params :
                    " " . $params
            )
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
            'path' => $this->url->link(
                'product/category',
                (!empty($this->request->get['path'])) ?
                    '&path=' . $this->request->get['path'] . $params :
                    " " . $params
            )
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
            'path' => $this->url->link(
                'product/category',
                (!empty($this->request->get['path'])) ?
                    '&path=' . $this->request->get['path'] . $params :
                    " " . $params
            )
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
            'path' => $this->url->link(
                'product/category',
                (!empty($this->request->get['path'])) ?
                    '&path=' . $this->request->get['path'] . $params :
                    " " . $params
            )
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
            'path' => $this->url->link(
                'product/category',
                (!empty($this->request->get['path'])) ?
                    '&path=' . $this->request->get['path'] . $params :
                    " " . $params
            )
        ];
        return $data;
    }
}
