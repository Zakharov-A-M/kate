<?php

class ControllerProductProduct extends Controller
{
    /**
     * View page current product
     */
    public function index()
    {
        $this->load->language('product/product');

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];
        if ($this->cart->hasProducts()) {
            $productsCart = $this->cart->getProducts();
            $data['linkCheckOut'] = $this->url->link('checkout/uni_checkout');
        }

        $this->load->model('catalog/category');
        $data['logged'] = $this->customer->isLogged();
        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
            $categoryId = (int)array_pop($parts);
            $categoryInfo = $this->model_catalog_category->getParentCategoryId($categoryId);
            if ($categoryInfo) {
                $data['breadcrumbs'][] = [
                    'text' => $categoryInfo['name'],
                    'href' => $this->url->link(
                        'product/category',
                        'path=' . $categoryInfo['category_id']
                    )
                ];
            }

            // Set the last category breadcrumb
            if (count($parts) > 0) {
                $categoryInfo = $this->model_catalog_category->getCategory($categoryId);
                if ($categoryInfo) {
                    $url = '';

                    if (isset($this->request->get['sort'])) {
                        $url .= '&sort=' . $this->request->get['sort'];
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

                    $data['breadcrumbs'][] = [
                        'text' => $categoryInfo['name'],
                        'href' => $this->url->link(
                            'product/category',
                            'path=' . $this->request->get['path'] . $url
                        )
                    ];
                }
            }
        }

        $this->load->model('catalog/manufacturer');
        if (isset($this->request->get['manufacturer_id'])) {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_brand'),
                'href' => $this->url->link('product/manufacturer')
            ];

            $url = '';
            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
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

            $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer(
                $this->request->get['manufacturer_id']
            );

            if ($manufacturer_info) {
                $data['breadcrumbs'][] = [
                    'text' => $manufacturer_info['name'],
                    'href' => $this->url->link(
                        'product/manufacturer/info',
                        'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url
                    )
                ];
            }
        }

        if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
            $url = '';

            if (isset($this->request->get['search'])) {
                $url .= '&search=' . $this->request->get['search'];
            }

            if (isset($this->request->get['tag'])) {
                $url .= '&tag=' . $this->request->get['tag'];
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
                $url .= '&sort=' . $this->request->get['sort'];
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

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_search'),
                'href' => $this->url->link('product/search', $url)
            ];
        }

        if (isset($this->request->get['product_id'])) {
            $productId = (int)$this->request->get['product_id'];
        } else {
            $productId = 0;
        }

        $this->load->model('catalog/product');
        $productInfo = $this->model_catalog_product->getProduct($productId);
        if ($productInfo) {
            if (isset($this->request->cookie['products'])) {
                $result = json_decode($this->request->cookie['products'], true);
                foreach ($result as $key => $item) {
                    if ($item == $productId) {
                        unset($result[$key]);
                    }
                }
                array_unshift($result, $productId);
                setcookie(
                    'products',
                    json_encode(array_values($result)),
                    time() + 60 * 60 * 24 * 30,
                    '/', $this->request->server['HTTP_HOST']
                );
            } else {
                setcookie(
                    'products',
                    json_encode([$productId]),
                    time() + 60 * 60 * 24 * 30,
                    '/',
                    $this->request->server['HTTP_HOST']
                );
            }

            $url = '';
            if (isset($this->request->get['path'])) {
                $data['breadcrumbs'][] = $this->addBreadcrumbs($productId);
            } else {
                $category = $this->model_catalog_product->getCategories($productId);
                if (!empty($category[0]['category_id'])) {
                    $this->request->get['path'] = $category[0]['category_id'];
                    $url .= '&path=' . $this->request->get['path'];
                    $parentCategoryInfo =
                        $this->model_catalog_category->getParentCategoryId($this->request->get['path']);
                    if ($parentCategoryInfo) {
                        $data['breadcrumbs'][] = [
                            'text' => $parentCategoryInfo['name'],
                            'href' => $this->url->link(
                                'product/category',
                                'path=' . $parentCategoryInfo['category_id']
                            )
                        ];
                    }
                    $categoryInfo = $this->model_catalog_category->getCategory($this->request->get['path']);
                    if ($categoryInfo) {
                        $data['breadcrumbs'][] = [
                            'text' => $categoryInfo['name'],
                            'href' => $this->url->link(
                                'product/category',
                                'path=' . $this->request->get['path']
                            )
                        ];
                    }
                }
            }

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            if (isset($this->request->get['manufacturer_id'])) {
                $url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
            }

            if (isset($this->request->get['search'])) {
                $url .= '&search=' . $this->request->get['search'];
            }

            if (isset($this->request->get['tag'])) {
                $url .= '&tag=' . $this->request->get['tag'];
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
                $url .= '&sort=' . $this->request->get['sort'];
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

            $data['breadcrumbs'][] = [
                'text' => $productInfo['name'],
                'href' => $this->url->link(
                    'product/product',
                    $url . '&product_id=' . $this->request->get['product_id']
                )
            ];

            $this->document->setTitle($productInfo['meta_title']);
            $this->document->setDescription($productInfo['meta_description']);
            $this->document->setKeywords($productInfo['meta_keyword']);
            $this->document->addLink(
                $this->url->link('product/product', 'product_id=' . $this->request->get['product_id']),
                'canonical'
            );
            $this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
            $this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
            $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
            $this->document->addScript(
                'catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js'
            );
            $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
            $this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

            $data['heading_title'] = $productInfo['name'];

            $data['text_minimum'] = sprintf($this->language->get('text_minimum'), $productInfo['minimum']);
            $data['text_login'] = sprintf(
                $this->language->get('text_login'),
                $this->url->link('account/login'),
                $this->url->link('account/register')
            );

            $this->load->model('catalog/review');

            $data['tab_review'] = sprintf($this->language->get('tab_review'), $productInfo['reviews']);

            if (!empty($productsCart)) {
                foreach ($productsCart as $product) {
                    if ($product['product_id'] == (int)$this->request->get['product_id']) {
                        $inCart = [
                            'quantity' => sprintf(
                                $this->language->get('text_in_cart_quantity_product'),
                                $product['quantity']
                            ),
                            'link' => $this->url->link('checkout/cart')
                        ];
                        if ($product['quantity'] >= $productInfo['amount']) {
                            $inCart['notBuy'] = 1;
                        }
                        break;
                    } else {
                        $inCart = false;
                    }
                }
            } else {
                $inCart = false;
            }

            $data['product_id'] = (int)$this->request->get['product_id'];
            $data['manufacturer'] = $productInfo['manufacturer'];
            $data['manufacturers'] = $this->url->link(
                'product/manufacturer/info',
                'manufacturer_id=' . $productInfo['manufacturer_id']
            );
            $data['model'] = $productInfo['model'];
            $data['vendorCode'] = $productInfo['vendorCode'];
            $data['reward'] = $productInfo['reward'];
            $data['length'] = round($productInfo['length'], 2);
            $data['width'] = round($productInfo['width'], 2);
            $data['height'] = round($productInfo['height'], 2);
            $data['weight'] = $productInfo['weight'];
            $data['scope'] = $productInfo['scope'];
            $data['lengthString'] = $productInfo['lengthString'];
            $data['amount'] = $productInfo['amount'];
            $data['inCart'] = $inCart;
            $data['points'] = $productInfo['points'];
            $data['description'] = html_entity_decode(
                $productInfo['description'],
                ENT_QUOTES,
                'UTF-8'
            );

            if ($productInfo['quantity'] <= 0) {
                $data['stock'] = $productInfo['stock_status'];
            } elseif ($this->config->get('config_stock_display')) {
                $data['stock'] = $productInfo['quantity'];
            } else {
                $data['stock'] = $this->language->get('text_instock');
            }

            $this->load->model('tool/image');
            if ($productInfo['image']) {
                $data['popup'] = $this->model_tool_image->resize($productInfo['image']);
            } else {
                $data['popup'] = '';
            }
            $data['statusMainPhoto'] = true;
            if (!is_file(DIR_IMAGE . $productInfo['image'])) {
                $data['statusMainPhoto'] = false;
            }

            if ($productInfo['image']) {
                $data['thumb'] = $this->model_tool_image->resize(
                    $productInfo['image'],
                    $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'),
                    $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height')
                );
            } else {
                $data['thumb'] = $this->model_tool_image->resize(
                    'placeholder.png',
                    $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
                    $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')
                );
            }

            $data['images'] = [];

            $results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
            foreach ($results as $result) {
                if ($result['image'] != $productInfo['image']) {
                    $data['images'][] = [
                        'popup' => $this->model_tool_image->resize($result['image']),
                        'thumb' => $this->model_tool_image->resize(
                            $result['image'],
                            $this->config->get(
                                'theme_' . $this->config->get('config_theme') . '_image_additional_width'
                            ),
                            $this->config->get(
                                'theme_' . $this->config->get('config_theme') . '_image_additional_height')
                        )
                    ];
                }
            }

            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $data['price'] = $this->currency->format(
                    $this->currency->convert(
                        $productInfo['price'],
                        $productInfo['currency'],
                        $this->session->data['currency']
                    ),
                    $this->session->data['currency'],
                    true
                );
            } else {
                $data['price'] = false;
            }

            if ((float)$productInfo['special']) {
                $data['special'] = $this->currency->format(
                    $this->currency->convert(
                        $productInfo['special'],
                        $productInfo['currency'],
                        $this->session->data['currency']
                    ),
                    $this->session->data['currency'],
                    true
                );
            } else {
                $data['special'] = false;
            }

            if (!$this->customer->isLogged()) {
                $data['special'] = false;
            }

            if ($this->config->get('config_tax')) {
                $data['tax'] = $this->currency->format(
                    (float)$productInfo['special'] ? $productInfo['special'] : $productInfo['price'],
                    $this->session->data['currency']
                );
            } else {
                $data['tax'] = false;
            }

            $discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);

            $data['discounts'] = [];

            foreach ($discounts as $discount) {
                $data['discounts'][] = [
                    'quantity' => $discount['quantity'],
                    'price' => $this->currency->format(
                        $this->tax->calculate(
                            $discount['price'],
                            $productInfo['tax_class_id'],
                            $this->config->get('config_tax')
                        ),
                        $this->session->data['currency']
                    )
                ];
            }

            if ($productInfo['minimum']) {
                $data['minimum'] = $productInfo['minimum'];
            } else {
                $data['minimum'] = 1;
            }

            $data['review_status'] = $this->config->get('config_review_status');

            if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
                $data['review_guest'] = true;
            } else {
                $data['review_guest'] = false;
            }

            if ($this->customer->isLogged()) {
                $data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
            } else {
                $data['customer_name'] = '';
            }

            $data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$productInfo['reviews']);
            $data['rating'] = (int)$productInfo['rating'];

            // Captcha
            if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') &&
                in_array('review', (array)$this->config->get('config_captcha_page'))
            ) {
                $data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
            } else {
                $data['captcha'] = '';
            }

            $data['share'] = $this->url->link(
                'product/product',
                'product_id=' . (int)$this->request->get['product_id']
            );

            $data['attribute_groups'] = $this->model_catalog_product->getProductAttributes(
                $this->request->get['product_id']
            );

            $data['products'] = [];

            $results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

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
                            $result['special'],
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
                    'product_id' => $result['product_id'],
                    'thumb' => $image,
                    'amount' => $result['amount'],
                    'name' => $result['name'],
                    'inCart' => $inCart,
                    'description' => utf8_substr(
                        trim(
                            strip_tags(
                                html_entity_decode(
                                    $result['description'],
                                    ENT_QUOTES, 'UTF-8'
                                )
                            )
                        ),
                        0,
                        $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')
                    ) . '..',
                    'price' => $price,
                    'special' => $special,
                    'tax' => $tax,
                    'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
                    'rating' => $rating,
                    'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'])
                ];
            }

            //Gel analog products
            $results = $this->model_catalog_product->getProductAnalog(
                $productInfo['analog_group'],
                $productInfo['product_id']
            );

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
                            $result['special'],
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

                $data['products_analog'][] = [
                    'product_id' => $result['product_id'],
                    'thumb' => $image,
                    'amount' => $result['amount'],
                    'name' => $result['name'],
                    'description' => utf8_substr(
                        trim(
                            strip_tags(
                                html_entity_decode(
                                        $result['description'],
                                        ENT_QUOTES,
                                        'UTF-8')
                                )
                        ),
                        0,
                        $this->config->get(
                            'theme_' . $this->config->get('config_theme') . '_product_description_length'
                        )
                        ) . '..',
                    'price' => $price,
                    'inCart' => $inCart,
                    'special' => $special,
                    'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
                    'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'])
                ];
            }

            $data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);
            $this->model_catalog_product->updateViewed($this->request->get['product_id']);
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('product/product', $data));
        } else {
            $url = '';

            if (isset($this->request->get['path'])) {
                $url .= '&path=' . $this->request->get['path'];
            }

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            if (isset($this->request->get['manufacturer_id'])) {
                $url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
            }

            if (isset($this->request->get['search'])) {
                $url .= '&search=' . $this->request->get['search'];
            }

            if (isset($this->request->get['tag'])) {
                $url .= '&tag=' . $this->request->get['tag'];
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
                $url .= '&sort=' . $this->request->get['sort'];
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

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_error'),
                'href' => $this->url->link('product/product', $url . '&product_id=' . $productId)
            ];

            $this->document->setTitle($this->language->get('text_error'));

            $data['continue'] = $this->url->link('common/home');

            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }

    /**
     * Review connect product
     */
    public function review()
    {
        $this->load->language('product/product');

        $this->load->model('catalog/review');

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['reviews'] = [];

        $reviewTotal = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

        $results = $this->model_catalog_review->getReviewsByProductId(
            $this->request->get['product_id'],
            ($page - 1) * 5, 5
        );

        foreach ($results as $result) {
            $data['reviews'][] = [
                'author' => $result['author'],
                'text' => nl2br($result['text']),
                'rating' => (int)$result['rating'],
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
            ];
        }

        $pagination = new Pagination();
        $pagination->total = $reviewTotal;
        $pagination->page = $page;
        $pagination->limit = 5;
        $pagination->url = $this->url->link(
            'product/product/review',
            'product_id=' . $this->request->get['product_id'] . '&page={page}'
        );

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf(
            $this->language->get('text_pagination'),
            ($reviewTotal) ? (($page - 1) * 5) + 1 : 0,
            (
                (($page - 1) * 5) > ($reviewTotal - 5)
            ) ?
                $reviewTotal : ((($page - 1) * 5) + 5),
            $reviewTotal,
            ceil($reviewTotal / 5)
        );

        $this->response->setOutput($this->load->view('product/review', $data));
    }

    /**
     * Write comment for product
     */
    public function write()
    {
        $this->load->language('product/product');

        $json = array();

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
                $json['error'] = $this->language->get('error_name');
            }

            if ((utf8_strlen($this->request->post['text']) < 25) ||
                (utf8_strlen($this->request->post['text']) > 1000)) {
                $json['error'] = $this->language->get('error_text');
            }

            if (empty($this->request->post['rating']) ||
                $this->request->post['rating'] < 0 ||
                $this->request->post['rating'] > 5) {
                $json['error'] = $this->language->get('error_rating');
            }

            // Captcha
            if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') &&
                in_array('review', (array)$this->config->get('config_captcha_page'))) {
                $captcha = $this->load->controller(
                    'extension/captcha/' . $this->config->get('config_captcha') . '/validate'
                );

                if ($captcha) {
                    $json['error'] = $captcha;
                }
            }

            if (!isset($json['error'])) {
                $this->load->model('catalog/review');

                $this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

                $json['success'] = $this->language->get('text_success');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Get description recurring product
     */
    public function getRecurringDescription()
    {
        $this->load->language('product/product');
        $this->load->model('catalog/product');

        if (isset($this->request->post['product_id'])) {
            $product_id = $this->request->post['product_id'];
        } else {
            $product_id = 0;
        }

        if (isset($this->request->post['recurring_id'])) {
            $recurring_id = $this->request->post['recurring_id'];
        } else {
            $recurring_id = 0;
        }

        if (isset($this->request->post['quantity'])) {
            $quantity = $this->request->post['quantity'];
        } else {
            $quantity = 1;
        }

        $product_info = $this->model_catalog_product->getProduct($product_id);
        $recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);
        $json = [];
        if ($product_info && $recurring_info) {
            if (!$json) {
                $frequencies = array(
                    'day' => $this->language->get('text_day'),
                    'week' => $this->language->get('text_week'),
                    'semi_month' => $this->language->get('text_semi_month'),
                    'month' => $this->language->get('text_month'),
                    'year' => $this->language->get('text_year'),
                );

                if ($recurring_info['trial_status'] == 1) {
                    $price = $this->currency->format(
                        $this->tax->calculate(
                            $recurring_info['trial_price'] * $quantity,
                            $product_info['tax_class_id'],
                            $this->config->get('config_tax')),
                        $this->session->data['currency']
                    );
                    $trial_text = sprintf(
                            $this->language->get('text_trial_description'),
                            $price,
                            $recurring_info['trial_cycle'],
                            $frequencies[$recurring_info['trial_frequency']],
                            $recurring_info['trial_duration']) . ' ';
                } else {
                    $trial_text = '';
                }

                $price = $this->currency->format(
                    $this->tax->calculate(
                        $recurring_info['price'] * $quantity,
                        $product_info['tax_class_id'],
                        $this->config->get('config_tax')),
                    $this->session->data['currency']);

                if ($recurring_info['duration']) {
                    $text = $trial_text . sprintf(
                            $this->language->get('text_payment_description'),
                            $price,
                            $recurring_info['cycle'],
                            $frequencies[$recurring_info['frequency']],
                            $recurring_info['duration']
                        );
                } else {
                    $text = $trial_text . sprintf(
                            $this->language->get('text_payment_cancel'),
                            $price,
                            $recurring_info['cycle'],
                            $frequencies[$recurring_info['frequency']],
                            $recurring_info['duration']
                        );
                }

                $json['success'] = $text;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Add breadcrumbs link category
     *
     * @param int $productId
     * @return array
     */
    public function addBreadcrumbs(int $productId)
    {
        if (count(explode('_', (string)$this->request->get['path'])) == 1) {
            $category = $this->model_catalog_product->getCategories($productId);
            if (!empty($category[0]['category_id'])) {
                $this->request->get['path'] = $category[0]['category_id'];
                $categoryInfo = $this->model_catalog_category->getCategory($this->request->get['path']);
                if ($categoryInfo) {
                    return [
                        'text' => $categoryInfo['name'],
                        'href' => $this->url->link(
                            'product/category',
                            'path=' . $this->request->get['path']
                        )
                    ];
                }
            }
        }
    }
}
