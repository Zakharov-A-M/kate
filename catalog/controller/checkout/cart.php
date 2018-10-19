<?php

class ControllerCheckoutCart extends Controller
{
    /**
     * View page for cart product
     */
    public function index()
    {
        $this->load->language('checkout/cart');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'href' => $this->url->link('common/home'),
            'text' => $this->language->get('text_home')
        ];

        $data['breadcrumbs'][] = [
            'href' => $this->url->link('checkout/cart'),
            'text' => $this->language->get('heading_title')
        ];

        if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
            if (!$this->cart->hasStock() && (
                    !$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')
                )
            ) {
                $data['error_warning'] = $this->language->get('error_stock');
            } elseif (isset($this->session->data['error'])) {
                $data['error_warning'] = $this->session->data['error'];

                unset($this->session->data['error']);
            } else {
                $data['error_warning'] = '';
            }

            if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
                $data['attention'] = sprintf(
                    $this->language->get('text_login'),
                    $this->url->link('account/login'),
                    $this->url->link('account/register')
                );
            } else {
                $data['attention'] = '';
            }

            if (isset($this->session->data['success'])) {
                $data['success'] = $this->session->data['success'];

                unset($this->session->data['success']);
            } else {
                $data['success'] = '';
            }

            $data['action'] = $this->url->link('checkout/cart/edit');

            if ($this->config->get('config_cart_weight')) {
                $data['weight'] = $this->weight->format(
                    $this->cart->getWeight(),
                    $this->config->get('config_weight_class_id'),
                    $this->language->get('decimal_point'),
                    $this->language->get('thousand_point')
                );
            } else {
                $data['weight'] = '';
            }

            $this->load->model('tool/image');
            $this->load->model('tool/upload');
            $this->load->model('catalog/product');

            $data['products'] = [];
            $products = $this->cart->getProducts();
            foreach ($products as $product) {
                $productTotal = 0;

                foreach ($products as $product_2) {
                    if ($product_2['product_id'] == $product['product_id']) {
                        $productTotal += $product_2['quantity'];
                    }
                }

                if ($product['minimum'] > $productTotal) {
                    $data['error_warning'] = sprintf(
                        $this->language->get('error_minimum'),
                        $product['name'],
                        $product['minimum']
                    );
                }

                if ($product['image']) {
                    $image = $this->model_tool_image->resize($product['image'], 150, 150);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 150, 150);
                }

                $optionData = [];

                foreach ($product['option'] as $option) {
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

                // Display prices
                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $unitPrice = $product['price'];
                    $price = $this->currency->format($unitPrice, $this->session->data['currency'], true);
                    $total = $this->currency->format(
                        $unitPrice * $product['quantity'],
                        $this->session->data['currency'],
                        true
                    );
                } else {
                    $price = false;
                    $total = false;
                }

                $recurring = '';

                if ($product['recurring']) {
                    $frequencies = [
                        'day' => $this->language->get('text_day'),
                        'week' => $this->language->get('text_week'),
                        'semi_month' => $this->language->get('text_semi_month'),
                        'month' => $this->language->get('text_month'),
                        'year' => $this->language->get('text_year')
                    ];

                    if ($product['recurring']['trial']) {
                        $recurring = sprintf(
                            $this->language->get('text_trial_description'),
                                $this->currency->format(
                                    $this->tax->calculate(
                                        $product['recurring']['trial_price'] * $product['quantity'],
                                        $product['tax_class_id'],
                                        $this->config->get('config_tax')
                                    ),
                                    $this->session->data['currency']
                                ),
                                $product['recurring']['trial_cycle'],
                                $frequencies[$product['recurring']['trial_frequency']],
                                $product['recurring']['trial_duration']
                        ) . ' ';
                    }

                    if ($product['recurring']['duration']) {
                        $recurring .= sprintf(
                            $this->language->get('text_payment_description'),
                            $this->currency->format(
                                $this->tax->calculate(
                                    $product['recurring']['price'] *
                                    $product['quantity'],
                                    $product['tax_class_id'],
                                    $this->config->get('config_tax')
                                ),
                                $this->session->data['currency']
                            ),
                            $product['recurring']['cycle'],
                            $frequencies[$product['recurring']['frequency']],
                            $product['recurring']['duration']
                        );
                    } else {
                        $recurring .= sprintf(
                            $this->language->get('text_payment_cancel'),
                            $this->currency->format(
                                $this->tax->calculate(
                                    $product['recurring']['price'] *
                                    $product['quantity'],
                                    $product['tax_class_id'],
                                    $this->config->get('config_tax')
                                ),
                                $this->session->data['currency']
                            ),
                            $product['recurring']['cycle'],
                            $frequencies[$product['recurring']['frequency']],
                            $product['recurring']['duration']
                        );
                    }
                }
                $productInfo = $this->model_catalog_product->getProduct($product['product_id']);
                //получение кол-во в данной стране
                $countCurrentCountry = $this->model_catalog_product->getCountProductInCurrentCountry(
                    $product['product_id']
                );
                if (empty($countCurrentCountry) || $countCurrentCountry < $product['quantity']) {
                    $this->load->model('stockroom/stockroom');
                    $stockroomsAttached = $this->model_stockroom_stockroom->getAttachedStockroomsForCountry();
                    if (!empty($stockroomsAttached)) {
                        $delivery = $this->getDeliveryStockroomAttached(
                            $countCurrentCountry,
                            $stockroomsAttached,
                            $product
                        );
                    }
                }

                $data['products'][] = [
                    'cart_id' => $product['cart_id'],
                    'delivery' => !empty($delivery) ? $delivery : "",
                    'thumb' => $image,
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'amount' => $productInfo['amount'],
                    'minimum' => $productInfo['minimum'],
                    'model' => $product['model'],
                    'option' => $optionData,
                    'recurring' => $recurring,
                    'quantity' => $product['quantity'],
                    'stock' => $product['stock'] ? $product['stock'] : !(
                        !$this->config->get('config_stock_checkout') ||
                        $this->config->get('config_stock_warning')
                    ),
                    'reward' => (
                        $product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) :''
                    ),
                    'price' => $price,
                    'total' => $total,
                    'href' => $this->url->link(
                        'product/product',
                        'product_id=' . $product['product_id']
                    )
                ];
            }

            // Gift Voucher
            $data['vouchers'] = [];

            if (!empty($this->session->data['vouchers'])) {
                foreach ($this->session->data['vouchers'] as $key => $voucher) {
                    $data['vouchers'][] = [
                        'key' => $key,
                        'description' => $voucher['description'],
                        'amount' => $this->currency->format($voucher['amount'], $this->session->data['currency']),
                        'remove' => $this->url->link('checkout/cart', 'remove=' . $key)
                    ];
                }
            }

            // Totals
            $this->load->model('setting/extension');

            $totals = [];
            $taxes = $this->cart->getTaxes();
            $total = 0;

            $totalData = [
                'totals' => &$totals,
                'taxes' => &$taxes,
                'total' => &$total
            ];

            // Display prices
            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $sortOrder = [];
                $results = $this->model_setting_extension->getExtensions('total');
                foreach ($results as $key => $value) {
                    $sortOrder[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
                }
                array_multisort($sortOrder, SORT_ASC, $results);
                foreach ($results as $result) {
                    if ($this->config->get('total_' . $result['code'] . '_status')) {
                        $this->load->model('extension/total/' . $result['code']);

                        // We have to put the totals in an array so that they pass by reference.
                        $this->{'model_extension_total_' . $result['code']}->getTotal($totalData);
                    }
                }

                $sortOrder = [];
                foreach ($totals as $key => $value) {
                    $sortOrder[$key] = $value['sort_order'];
                }

                array_multisort($sortOrder, SORT_ASC, $totals);
            }

            $data['totals'] = [];
            foreach ($totals as $total) {
                $data['totals'][] = [
                    'title' => $total['title'],
                    'text' => $this->currency->format($total['value'], $this->session->data['currency'], true)
                ];
            }

            $data['continue'] = $this->url->link('common/home');
            $data['checkout'] = $this->url->link('checkout/uni_checkout');
            $this->load->model('setting/extension');
            $data['modules'] = [];
            $files = glob(DIR_APPLICATION . '/controller/extension/total/*.php');
            if ($files) {
                foreach ($files as $file) {
                    $result = $this->load->controller('extension/total/' . basename($file, '.php'));

                    if ($result) {
                        $data['modules'][] = $result;
                    }
                }
            }

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('checkout/cart', $data));
        } else {
            $data['text_error'] = $this->language->get('text_empty');
            $data['continue'] = $this->url->link('common/home');
            unset($this->session->data['success']);
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
     * Added new product in cart
     */
    public function add()
    {
        $this->load->language('checkout/cart');
        $json = [];
        if (isset($this->request->post['product_id'])) {
            $productId = (int)$this->request->post['product_id'];
        } else {
            $productId = 0;
        }

        $this->load->model('catalog/product');
        $productInfo = $this->model_catalog_product->getProduct($productId);
        if ($productInfo) {
            if (isset($this->request->post['quantity'])) {
                $quantity = (int)$this->request->post['quantity'];
            } else {
                $quantity = 1;
            }

            if (isset($this->request->post['option'])) {
                $option = array_filter($this->request->post['option']);
            } else {
                $option = [];
            }

            $productOptions = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

            foreach ($productOptions as $productOption) {
                if ($productOption['required'] && empty($option[$productOption['product_option_id']])) {
                    $json['error']['option'][$productOption['product_option_id']] = sprintf(
                        $this->language->get('error_required'),
                        $productOption['name']
                    );
                }
            }

            if (isset($this->request->post['recurring_id'])) {
                $recurringId = $this->request->post['recurring_id'];
            } else {
                $recurringId = 0;
            }

            $recurrings = $this->model_catalog_product->getProfiles($productInfo['product_id']);

            if ($recurrings) {
                $recurringIds = [];

                foreach ($recurrings as $recurring) {
                    $recurringIds[] = $recurring['recurring_id'];
                }

                if (!in_array($recurringId, $recurringIds)) {
                    $json['error']['recurring'] = $this->language->get('error_recurring_required');
                }
            }

            if (!$json) {
                $productInfo = $this->model_catalog_product->getProduct($this->request->post['product_id']);
                $count = 0;
                foreach ($this->cart->getProducts() as $item) {
                    if ($item['product_id'] == $this->request->post['product_id']) {
                        $count += $item['quantity'];
                    }
                }

                if ($productInfo['amount'] >= $count + $quantity) {
                    $this->cart->add($this->request->post['product_id'], $quantity, $option, $recurringId);
                    $count += $quantity;
                }
                $this->load->model('tool/image');

                if ($productInfo['image']) {
                    $imageProduct = $this->model_tool_image->resize($productInfo['image'], 100, 100);
                } else {
                    $imageProduct = $this->model_tool_image->resize('placeholder.png', 100, 100);
                }

                $json['success'] = sprintf(
                    $this->language->get('text_success'),
                    $this->url->link('checkout/uni_checkout')
                );

                // Unset all shipping and payment methods
                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);

                // Totals
                $this->load->model('setting/extension');

                $totals = array();
                $taxes = $this->cart->getTaxes();
                $total = 0;

                // Because __call can not keep var references so we put them into an array.
                $totalData = array(
                    'totals' => &$totals,
                    'taxes' => &$taxes,
                    'total' => &$total
                );

                // Display prices
                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $sortOrder = [];

                    $results = $this->model_setting_extension->getExtensions('total');
                    foreach ($results as $key => $value) {
                        $sortOrder[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
                    }

                    array_multisort($sortOrder, SORT_ASC, $results);
                    foreach ($results as $result) {
                        if ($this->config->get('total_' . $result['code'] . '_status')) {
                            $this->load->model('extension/total/' . $result['code']);
                            $this->{'model_extension_total_' . $result['code']}->getTotal($totalData);
                        }
                    }

                    $sortOrder = [];

                    foreach ($totals as $key => $value) {
                        $sortOrder[$key] = $value['sort_order'];
                    }

                    array_multisort($sortOrder, SORT_ASC, $totals);
                }

                $json['total'] = sprintf(
                    $this->language->get('text_items'),
                    $this->cart->countProducts() + (
                    isset($this->session->data['vouchers']) ?
                        count($this->session->data['vouchers']) :
                        0
                    ),
                    $this->currency->format($total, $this->session->data['currency'])
                );
            } else {
                $json['redirect'] = str_replace(
                    '&amp;',
                    '&',
                    $this->url->link('product/product', 'product_id=' . $this->request->post['product_id'])
                );
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Edit product in cart
     */
    public function edit()
    {
        $this->load->language('checkout/cart');

        $json = [];

        // Update
        if (!empty($this->request->post['quantity'])) {
            foreach ($this->request->post['quantity'] as $key => $value) {
                $this->cart->update($key, $value);
            }

            $this->session->data['success'] = $this->language->get('text_remove');
            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['reward']);

            $this->response->redirect($this->url->link('checkout/cart'));
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Remove product in cart
     */
    public function remove()
    {
        $this->load->language('checkout/cart');

        $json = [];

        // Remove
        if (isset($this->request->post['key'])) {
            $this->cart->remove($this->request->post['key']);

            unset($this->session->data['vouchers'][$this->request->post['key']]);

            $json['success'] = $this->language->get('text_remove');

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['reward']);

            // Totals
            $this->load->model('setting/extension');

            $totals = [];
            $taxes = $this->cart->getTaxes();
            $total = 0;

            // Because __call can not keep var references so we put them into an array.
            $totalData = array(
                'totals' => &$totals,
                'taxes' => &$taxes,
                'total' => &$total
            );

            // Display prices
            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $sortOrder = array();

                $results = $this->model_setting_extension->getExtensions('total');

                foreach ($results as $key => $value) {
                    $sortOrder[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
                }

                array_multisort($sortOrder, SORT_ASC, $results);

                foreach ($results as $result) {
                    if ($this->config->get('total_' . $result['code'] . '_status')) {
                        $this->load->model('extension/total/' . $result['code']);

                        // We have to put the totals in an array so that they pass by reference.
                        $this->{'model_extension_total_' . $result['code']}->getTotal($totalData);
                    }
                }

                $sortOrder = [];

                foreach ($totals as $key => $value) {
                    $sortOrder[$key] = $value['sort_order'];
                }

                array_multisort($sortOrder, SORT_ASC, $totals);
            }

            $json['total'] = sprintf(
                $this->language->get('text_items'),
                $this->cart->countProducts() + (isset($this->session->data['vouchers']) ?
                    count($this->session->data['vouchers']) :
                    0
                ),
                $this->currency->format($total, $this->session->data['currency']));
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /***
     * Get delivery stockroom attached
     *
     * @param int $countCurrentCountry
     * @param array $stockroomsAttached
     * @param array $product
     * @return array
     */
    public function getDeliveryStockroomAttached(int $countCurrentCountry, array $stockroomsAttached, array $product)
    {
        $count = $countCurrentCountry;
        $flag = true;
        foreach ($stockroomsAttached as $stockroom) {
            $countProduct = $this->model_stockroom_stockroom->getCountProductStockroom(
                $stockroom['stockroom_id'],
                $product['product_id']
            );
            if (!empty($countProduct['amount']) &&
                $countProduct['amount'] + $count >= $product['quantity']) {
                $delivery[] = [
                    'delivery' => $stockroom['delivery'],
                    'count' => $product['quantity'] - $count
                ];
                break;
            } else {
                if (!empty($countProduct['amount'])) {
                    if ($flag) {
                        $delivery[] = [
                            'delivery' => $stockroom['delivery'],
                            'count' => $countProduct['amount']
                        ];
                        $count += $countProduct['amount'];
                        $flag = false;
                    } else {
                        $delivery[] = [
                            'delivery' => $stockroom['delivery'],
                            'count' => $countProduct['amount'] - $count
                        ];
                    }
                }
            }
        }
        return $delivery ?? null;
    }
}
