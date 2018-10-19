<?php

class ControllerAccountOrder extends Controller
{
    /**
     * View list orders user
     */
    public function index()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/order');

            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->language('account/order');
        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/edit')
        ];

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['orders'] = [];
        $this->load->model('account/order');
        $orderTotal = $this->model_account_order->getTotalOrders();
        $results = $this->model_account_order->getOrders(($page - 1) * 10, 10);
        foreach ($results as $result) {
            $productTotal = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
            $voucherTotal = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);
            $data['orders'][] = [
                'order_id' => $result['order_id'],
                'name' => $result['firstname'] . ' ' . $result['lastname'],
                'status' => $result['status'],
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'products' => ($productTotal + $voucherTotal),
                'total' => $this->currency->format(
                    $this->currency->convert(
                        $result['total'],
                        $result['currency_code'],
                        $this->session->data['currency']
                    ),
                    $this->session->data['currency'],
                    true
                ),
                'view' => $this->url->link('account/order/info', 'order_id=' . $result['order_id']),
            ];
        }


        $pagination = new Pagination();
        $pagination->total = $orderTotal;
        $pagination->page = $page;
        $pagination->limit = 10;
        $pagination->url = $this->url->link('account/order', 'page={page}');
        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf(
            $this->language->get('text_pagination'),
            ($orderTotal) ? (($page - 1) * 10) + 1 : 0,
            ((($page - 1) * 10) > ($orderTotal - 10)) ? $orderTotal : ((($page - 1) * 10) + 10),
            $orderTotal,
            ceil($orderTotal / 10)
        );
        $data['continue'] = $this->url->link('account/edit');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left_profile'] = $this->load->controller('account/column_left_profile');

        $this->response->setOutput($this->load->view('account/order_list', $data));
    }

    /**
     * Viewed order ID
     *
     * @return Action
     */
    public function info()
    {
        $this->load->language('account/order');

        if (isset($this->request->get['order_id'])) {
            $orderId = $this->request->get['order_id'];
        } else {
            $orderId = 0;
        }

        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $orderId);
            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->model('account/order');
        $orderInfo = $this->model_account_order->getOrder($orderId);
        if ($orderInfo) {
            $this->document->setTitle($this->language->get('text_order'));
            $url = '';
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $data['breadcrumbs'] = [];
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_account'),
                'href' => $this->url->link('account/edit')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('account/order', $url)
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_order'),
                'href' => $this->url->link(
                    'account/order/info',
                    'order_id=' . $this->request->get['order_id'] . $url
                )
            ];

            if (isset($this->session->data['error'])) {
                $data['error_warning'] = $this->session->data['error'];

                unset($this->session->data['error']);
            } else {
                $data['error_warning'] = '';
            }

            if (isset($this->session->data['success'])) {
                $data['success'] = $this->session->data['success'];

                unset($this->session->data['success']);
            } else {
                $data['success'] = '';
            }

            if ($orderInfo['invoice_no']) {
                $data['invoice_no'] = $orderInfo['invoice_prefix'] . $orderInfo['invoice_no'];
            } else {
                $data['invoice_no'] = '';
            }

            $data['order_id'] = (int)$this->request->get['order_id'];
            $data['date_added'] = date($this->language->get('date_format_short'), strtotime($orderInfo['date_added']));

            $data['payment_method'] = $orderInfo['payment_method'];
            if (!empty($orderInfo['score']) && is_file(DIR_IMAGE . 'catalog/' . $orderInfo['score'])) {
                $data['score'] = $orderInfo['score'];
            }

            if ($orderInfo['shipping_address_1']) {
                $format = ' {firstname} {lastname} ' . "\n" . '{address_1}';

                $find = [
                    '{firstname}',
                    '{lastname}',
                    '{address_1}'
                ];

                $replace = [
                    'firstname' => $orderInfo['shipping_firstname'],
                    'lastname' => $orderInfo['shipping_lastname'],
                    'address_1' => $orderInfo['shipping_address_1']
                ];

                $data['shipping_address'] = str_replace(
                    ["\r\n", "\r", "\n"],
                    '<br />',
                    preg_replace(
                        ["/\s\s+/", "/\r\r+/", "/\n\n+/"],
                        '<br />',
                        trim(str_replace($find, $replace, $format))
                    )
                );
            }
            $data['shipping_method'] = $orderInfo['shipping_method'];
            $this->load->model('catalog/product');
            $this->load->model('tool/upload');

            // Products
            $data['products'] = [];
            $products = $this->model_account_order->getOrderProducts($this->request->get['order_id']);
            foreach ($products as $product) {
                $optionData = [];
                $options = $this->model_account_order->getOrderOptions(
                    $this->request->get['order_id'],
                    $product['order_product_id']
                );

                foreach ($options as $option) {
                    if ($option['type'] != 'file') {
                        $value = $option['value'];
                    } else {
                        $uploadInfo = $this->model_tool_upload->getUploadByCode($option['value']);
                        if ($uploadInfo) {
                            $value = $uploadInfo['name'];
                        } else {
                            $value = '';
                        }
                    }
                    $optionData[] = [
                        'name' => $option['name'],
                        'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                    ];
                }

                $productInfo = $this->model_catalog_product->getProduct($product['product_id']);
                if ($productInfo) {
                    $reorder = $this->url->link(
                        'account/order/reorder',
                        'order_id=' . $orderId . '&order_product_id=' . $product['order_product_id']
                    );
                } else {
                    $reorder = '';
                }

                $data['products'][] = [
                    'name' => $product['name'],
                    'model' => $product['model'],
                    'option' => $optionData,
                    'quantity' => $product['quantity'],
                    'price' => $this->currency->format(
                        $this->currency->convert(
                            $product['price'],
                            $orderInfo['currency_code'],
                            $this->session->data['currency']
                        ),
                        $this->session->data['currency'],
                        true
                    ),
                    'total' => $this->currency->format(
                        $this->currency->convert(
                            $product['price'] * $product['quantity'],
                            $orderInfo['currency_code'],
                            $this->session->data['currency']
                        ),
                        $this->session->data['currency'],
                        true
                    ),
                    'reorder' => $reorder,
                    'return' => $this->url->link(
                        'account/return/add',
                        'order_id=' . $orderInfo['order_id'] . '&product_id=' . $product['product_id']
                    )
                ];
            }

            // Voucher
            $data['vouchers'] = [];
            $vouchers = $this->model_account_order->getOrderVouchers($this->request->get['order_id']);
            foreach ($vouchers as $voucher) {
                $data['vouchers'][] = array(
                    'description' => $voucher['description'],
                    'amount' => $this->currency->format(
                        $voucher['amount'],
                        $orderInfo['currency_code'],
                        $orderInfo['currency_value']
                    )
                );
            }

            // Totals
            $data['totals'] = [];
            $totals = $this->model_account_order->getOrderTotals($this->request->get['order_id']);
            foreach ($totals as $total) {
                $data['totals'][] = array(
                    'title' => $total['title'],
                    'text' => $this->currency->format(
                        $this->currency->convert(
                            $total['value'],
                            $orderInfo['currency_code'],
                            $this->session->data['currency']
                        ),
                        $this->session->data['currency'],
                        true
                    ),
                );
            }

            $data['comment'] = nl2br($orderInfo['comment']);
            $data['histories'] = [];
            $results = $this->model_account_order->getOrderHistories($this->request->get['order_id']);
            foreach ($results as $result) {
                $data['histories'][] = [
                    'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                    'status' => $result['status'],
                    'comment' => $result['notify'] ? nl2br($result['comment']) : ''
                ];
            }

            $data['continue'] = $this->url->link('account/order');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');
            $data['column_left_profile'] = $this->load->controller('account/column_left_profile');
            $this->response->setOutput($this->load->view('account/order_info', $data));
        } else {
            return new Action('error/not_found');
        }
    }

    /**
     * Change product in order
     */
    public function reorder()
    {
        $this->load->language('account/order');

        if (isset($this->request->get['order_id'])) {
            $orderId = $this->request->get['order_id'];
        } else {
            $orderId = 0;
        }

        $this->load->model('account/order');

        $orderInfo = $this->model_account_order->getOrder($orderId);
        if ($orderInfo) {
            if (isset($this->request->get['order_product_id'])) {
                $orderProductId = $this->request->get['order_product_id'];
            } else {
                $orderProductId = 0;
            }

            $orderProductInfo = $this->model_account_order->getOrderProduct($orderId, $orderProductId);
            if ($orderProductInfo) {
                $this->load->model('catalog/product');
                $productInfo = $this->model_catalog_product->getProduct($orderProductInfo['product_id']);
                if ($productInfo) {
                    $optionData = [];
                    $orderOptions = $this->model_account_order->getOrderOptions(
                        $orderProductInfo['order_id'],
                        $orderProductId
                    );
                    foreach ($orderOptions as $orderOption) {
                        if ($orderOption['type'] == 'select' ||
                            $orderOption['type'] == 'radio' ||
                            $orderOption['type'] == 'image'
                        ) {
                            $optionData[$orderOption['product_option_id']] = $orderOption['product_option_value_id'];
                        } elseif ($orderOption['type'] == 'checkbox') {
                            $optionData[$orderOption['product_option_id']][] = $orderOption['product_option_value_id'];
                        } elseif ($orderOption['type'] == 'text' ||
                            $orderOption['type'] == 'textarea' ||
                            $orderOption['type'] == 'date' ||
                            $orderOption['type'] == 'datetime' ||
                            $orderOption['type'] == 'time'
                        ) {
                            $optionData[$orderOption['product_option_id']] = $orderOption['value'];
                        } elseif ($orderOption['type'] == 'file') {
                            $optionData[$orderOption['product_option_id']] = $this->encryption->encrypt(
                                $this->config->get('config_encryption'),
                                $orderOption['value']
                            );
                        }
                    }

                    $this->cart->add($orderProductInfo['product_id'], $orderProductInfo['quantity'], $optionData);
                    $this->session->data['success'] = sprintf(
                        $this->language->get('text_success'),
                        $this->url->link('product/product', 'product_id=' . $productInfo['product_id']),
                        $productInfo['name'],
                        $this->url->link('checkout/cart')
                    );
                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['payment_method']);
                    unset($this->session->data['payment_methods']);
                } else {
                    $this->session->data['error'] = sprintf(
                        $this->language->get('error_reorder'),
                        $orderProductInfo['name']
                    );
                }
            }
        }

        $this->response->redirect($this->url->link('account/order/info', 'order_id=' . $orderId));
    }
}
