<?php

class ControllerCheckoutUniCheckout extends Controller
{
    /**
     * View page ordering
     */
    public function index()
    {
        if (isset($this->session->data['shipping_address_id'])) {
            unset($this->session->data['shipping_address_id']);
        }

        $this->document->addStyle('catalog/view/theme/unishop2/stylesheet/checkout.css');

        $this->load->language('checkout/cart');
        $this->load->language('checkout/checkout');
        $this->load->language('checkout/uni_checkout');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('checkout/uni_checkout', '', true)
        ];

        if (!isset($this->session->data['guest']['customer_group_id'])) {
            $this->session->data['guest']['customer_group_id'] = (int)$this->config->get('config_customer_group_id');
        }

        if (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) {
            $this->response->redirect($this->url->link('checkout/cart'));
        }

        if (!$this->cart->hasStock() &&
            (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
            $data['error_warning'] = $this->language->get('error_stock');
        }

        if ($this->customer->isLogged()) {
            $data['customer_id'] = $this->session->data['customer_id'];

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['shipping_address']);
            unset($this->session->data['shipping_address_id']);
            unset($this->session->data['payment_address']);
            unset($this->session->data['payment_address_id']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);

            unset($this->session->data['guest']);
            unset($this->session->data['account']);
            unset($this->session->data['shipping_country_id']);
            unset($this->session->data['shipping_zone_id']);
            unset($this->session->data['payment_country_id']);
            unset($this->session->data['payment_zone_id']);
        }

        if ($this->customer->isLogged()) {
            $this->load->model('account/address');
            $data['firstname'] = $this->customer->getFirstName();
            $data['lastname'] = $this->customer->getLastName();
            $data['patronymic'] = $this->customer->getPatronymic();
            $data['email'] = $this->customer->getEmail();
            $data['telephone'] = $this->customer->getTelephone();
        }

        $data['comment'] = isset($this->session->data['comment']) ? $this->session->data['comment'] : '';

        $this->load->model('account/customer_group');

        $data['customer_groups'] = [];

        if (is_array($this->config->get('config_customer_group_display'))) {
            $customerGroups = $this->model_account_customer_group->getCustomerGroups();
            $groupDisplay = $this->config->get('config_customer_group_display');
            foreach ($customerGroups as $customerGroup) {
                if (in_array($customerGroup['customer_group_id'], $groupDisplay)) {
                    $data['customer_groups'][] = $customerGroup;
                }
            }
        }

        $data['customer_group_id'] = isset($this->session->data['guest']['customer_group_id']) ?
            $this->session->data['guest']['customer_group_id'] :
            $this->config->get('config_customer_group_id');

        $this->load->model('account/custom_field');

        $data['custom_fields'] = $this->custom_field('account');

        $data['is_logged'] = $this->customer->isLogged() ? true : false;
        $data['is_shipping'] = $this->cart->hasShipping() ? true : false;
        $data['confirm'] = isset($this->session->data['confirm']) ? $this->session->data['confirm'] : '';
        $data['text_confirm'] = '';

        if ($this->config->get('config_checkout_id')) {
            $this->load->model('catalog/information');

            $informationInfo = $this->model_catalog_information->getInformation(
                $this->config->get('config_checkout_id')
            );

            if ($informationInfo) {
                $data['text_confirm'] = sprintf(
                    $this->language->get('text_agree'),
                    $this->url->link(
                        'information/information/agree',
                        'information_id=' . $this->config->get('config_checkout_id'),
                        'SSL'
                    ),
                    $informationInfo['title'],
                    $informationInfo['title']
                );
            }
        }

        $data['checkout_guest'] = $this->config->get('config_checkout_guest');
        $data['address'] = $this->address();
        $data['shipping_method'] = $this->shipping_method();
        $data['payment_method'] = $this->payment_method();
        $data['cart'] = $this->cart();
        $data['login_user'] = $this->url->link('account/login');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('checkout/uni_checkout', $data));
    }

    /**
     * validate form for order
     * if ajax we send validate errors
     * if ajax and not errors send confirmation of an order
     */
    public function validate()
    {
        $this->load->language('checkout/cart');
        $this->load->language('checkout/checkout');
        $this->load->model('account/custom_field');
        $this->load->model('account/customer');
        $this->load->model('account/customer_group');

        $json = [];

        if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') ||
                $this->config->get('config_stock_warning'))) {
            $json['error']['error_warning'] = $this->language->get('error_stock');
        }

        if (!$this->cart->hasProducts() && !empty($this->session->data['vouchers'])) {
            unset($json['error']);
        }

        //customer data
        $this->session->data['telephone'] = isset($this->request->post['telephone']) ?
            $this->request->post['telephone'] : '';
        $this->session->data['patronymic'] = isset($this->request->post['patronymic']) ?
            $this->request->post['patronymic'] : '';
        $firstname = isset($this->request->post['firstname']) ? trim($this->request->post['firstname']) : '';
        $lastname = isset($this->request->post['lastname']) ? trim($this->request->post['lastname']) : '';
        $address = isset($this->request->post['address']) ? trim($this->request->post['address']) : '';
        $addressOld = isset($this->request->post['address_old']) ? trim($this->request->post['address_old']) : '';
        $newAddress = isset($this->request->post['new-address']) ? trim($this->request->post['new-address']) : '';
        $email = isset($this->request->post['email']) &&
        $this->request->post['email'] != '' ? $this->request->post['email'] : '';


        //validate

        if (utf8_strlen($firstname) < 3 || utf8_strlen($firstname) > 32) {
            $json['error']['firstname'] = $this->language->get('error_firstname');
        } else {
            $this->session->data['firstname'] = htmlspecialchars(strip_tags($firstname));
        }

        if ($this->request->post['shipping_method'] != 'pickup.pickup') {
            if (utf8_strlen($address) < 3 || utf8_strlen($address) > 250) {
                if (utf8_strlen($addressOld) < 3 || utf8_strlen($address) > 250) {
                    $json['error']['address'] = $this->language->get('error_address');
                    $json['error']['address_old'] = $this->language->get('error_address');
                } else {
                    $this->session->data['address'] = htmlspecialchars(strip_tags($addressOld));
                    $this->session->data['newAddress'] = false;
                }
            } else {
                $this->session->data['address'] = htmlspecialchars(strip_tags($address));
                $this->session->data['newAddress'] = htmlspecialchars(strip_tags($newAddress));
            }
        }

        if (utf8_strlen($lastname) < 3 || utf8_strlen($lastname) > 32) {
            $json['error']['lastname'] = $this->language->get('error_lastname');
        } else {
            $this->session->data['lastname'] = htmlspecialchars(strip_tags($lastname));
        }

        if (utf8_strlen($email) > 96 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $json['error']['email'] = $this->language->get('error_email');
        } else {
            $this->session->data['email'] = htmlspecialchars(strip_tags($email));
        }

        if (!$this->customer->isLogged()) {
            if ($this->model_account_customer->getTotalCustomersByEmail($email)) {
                $json['error']['email'] = $this->language->get('error_exists');
            }
        }

        //shipping method
        if ($this->cart->hasProducts() && $this->cart->hasShipping()) {
            if (!isset($this->request->post['shipping_method'])) {
                $json['error']['warning'] = $this->language->get('error_shipping');
            } else {
                $shipping = explode('.', $this->request->post['shipping_method']);
                if (!isset($shipping[0]) || !isset($shipping[1])) {
                    $json['error']['warning'] = $this->language->get('error_shipping');
                }
            }
        }

        //payment method
        if ($this->cart->hasProducts() &&
            (!isset($this->request->post['payment_method']) ||
            !isset($this->session->data['payment_methods'][$this->request->post['payment_method']]))
        ) {
            $json['error']['warning'] = $this->language->get('error_payment');
        }

        //agree
        if ($this->config->get('config_checkout_id')) {
            $this->load->model('catalog/information');
            $informationInfo =
                $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

            if ($informationInfo && !isset($this->request->post['confirm'])) {
                $json['error']['confirm'] = sprintf($this->language->get('error_agree_rules'));
            }
        }

        if (isset($this->request->post['custom_field']['account'])) {
            $this->session->data['custom_field'] = $this->request->post['custom_field']['account'];
        } else {
            $this->session->data['custom_field'] = [];
        }

        $this->session->data['comment'] = isset($this->request->post['comment']) ?
            strip_tags($this->request->post['comment']) : '';

        if (!$this->cart->hasProducts()) {
            $json['error']['warning'] = $this->language->get('error_stock');
        }

        if (!$json) {
            //guest
            if (!$json && !$this->customer->isLogged()) {
                if (isset($this->request->post['customer_group_id']) &&
                    is_array($this->config->get('config_customer_group_display')) &&
                    in_array($this->request->post['customer_group_id'],
                        $this->config->get('config_customer_group_display'))) {
                    $customerGroupId = $this->request->post['customer_group_id'];
                } else {
                    $customerGroupId = $this->config->get('config_customer_group_id');
                }

                $this->session->data['account'] = 'guest';
                $this->session->data['guest']['firstname'] = $this->session->data['firstname'];
                $this->session->data['guest']['lastname'] = $this->session->data['lastname'];
                $this->session->data['guest']['patronymic'] = $this->session->data['patronymic'] ?? '';
                $this->session->data['guest']['email'] = $this->session->data['email'];
                $this->session->data['guest']['telephone'] = $this->session->data['telephone'] ?? '';
                $this->session->data['guest']['customer_group_id'] = $customerGroupId;
                $this->session->data['guest']['fax'] = isset($this->request->post['fax']) ?
                    $this->request->post['fax'] :
                    '';
            }

            //confirm checkout
            $json['success'] = $this->addOrder();
        } else {
            $json['success']['result'] = false;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Registered new user
     *
     * @param array $data
     * @return mixed
     */
    private function addNewCustomer(array $data)
    {
        $data['password'] = $data['new_password'] = $this->generateStrongPassword(8);
        $customerId = $this->model_account_customer->addCustomer($data);
        $this->model_account_customer->addNewAddress($customerId, $data['shipping_address_2']);

        $this->customer->login($data['email'], $data['password']);

        $this->load->model('account/customer');
        $this->session->data['customer_id'] = $customerId;

        $customerInfo = $this->model_account_customer->getCustomer($customerId);
        $customerGroupInfo = $this->model_account_customer_group->getCustomerGroup(
            $customerInfo['customer_group_id']
        );

        if ($customerGroupInfo['approval']) {
            $this->session->data['approve_customer'] = true;
        }

        unset($this->session->data['guest']);
        return [
            'customer_id' => $customerId,
            'email' => $data['email'],
            'new_password' => $data['new_password']
        ];
    }

    /**
     * Generate custom password
     *
     * @param $length
     * @return string
     */
    public function generateStrongPassword($length): string
    {
        $alphabet = [
            'abcdefghijklmnopqrstuvwxyz',
            '1234567890',
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
        ];
        $pass = [];

        for ($i = 0; $i < $length - 3; $i++) {
            $n = rand(0, 2);
            $str = $alphabet[$n];
            $pass[] = $str[rand(0, strlen($str) - 1)];
        }

        for ($i = 0; $i < 3; $i++) {
            $str = $alphabet[$i];
            $pass[] = $str[rand(0, strlen($str) - 1)];
        }

        return implode($pass);
    }

    /**
     * View data address for order
     *
     * @return string
     */
    public function address()
    {
        $this->load->language('checkout/cart');
        $this->load->language('checkout/checkout');
        $this->load->language('checkout/uni_checkout');
        if (isset($this->request->get['render'])) {
            $data['render'] = 1;
        }
        $data['address_show'] = true;
        $shippingMethod = isset($this->session->data['shipping_method']) ?
            $this->session->data['shipping_method'] :
            '';

        $languageId = $this->config->get('config_language_id');

        $data['blocked'] = isset($uniset['checkout_address_blocked']) ? true : false;
        $data['new_address'] = $newAddress = isset($this->request->post['new-address']) ? true : false;
        $data['old_address'] = $oldAddress = isset($this->request->post['old_address']) ? true : false;
        if (!empty($shippingMethod['code'])) {
            if ($shippingMethod['code'] === 'pickup.pickup') {
                $data['address_show'] = false;
            }
        } else {
            $shippingMethod = isset($this->request->post['shipping_method']) ?
                $this->request->post['shipping_method'] :
                '';
            if ($shippingMethod === 'pickup.pickup') {
                $data['address_show'] = false;
            }
        }

        $data['customer_id'] = $this->customer->isLogged() ? $this->customer->getId() : '';
        $data['is_shipping'] = $this->cart->hasShipping() ? true : false;
        $this->load->model('account/address');
        $data['addresses'] = $this->customer->getId() ? $this->model_account_address->getAddresses() : [];
        $this->load->model('localisation/country');
        $data['countries'] = $this->model_localisation_country->getCountries();

        if ($this->customer->isLogged()) {
            $data['address_list'] = $this->customer->getAddress();
            if (!$data['address_list']) {
                $data['new_address'] = true;
                $newAddress = true;
            }
        } else {
            $data['new_address'] = true;
            $newAddress = true;
        }

        if ($newAddress) {
            $data['address'] = isset($this->request->post['address']) ? $this->request->post['address'] : false;
        }
        if ($oldAddress) {
            $data['address_old'] = isset($this->request->post['address_old']) ?
                $this->request->post['address_old'] : false;
        }

        $result = $this->load->view('checkout/uni_address', $data);

        if (isset($this->request->get['render'])) {
            $this->response->setOutput($result);
        } else {
            return $result;
        }
    }

    /**
     * Get view for shipping method
     *
     * @return string
     */
    public function shipping_method()
    {
        $this->load->language('extension/module/uni_othertext');
        $this->load->language('checkout/checkout');
        $this->load->language('checkout/uni_checkout');

        $shippingAddress = isset($this->session->data['shipping_address']) ?
            $this->session->data['shipping_address'] :
            [
                'country_id' => $this->config->get('config_country_id'),
                'zone_id' => $this->config->get('config_zone_id'),
                'firstname' => '',
                'lastname' => '',
                'company' => '',
                'address_1' => '',
                'city' => '',
                'iso_code2' => '',
                'iso_code3' => ''
            ];

        $methodData = [];

        if ($shippingAddress) {
            $this->tax->setShippingAddress($shippingAddress['country_id'], $shippingAddress['zone_id']);
            $this->load->model('setting/extension');
            $results = $this->model_setting_extension->getExtensions('shipping');

            foreach ($results as $result) {
                if ($this->config->get('shipping_' . $result['code'] . '_status')) {
                    $this->load->model('extension/shipping/' . $result['code']);
                    $quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($shippingAddress);
                    if ($quote) {
                        $methodData[$result['code']] = array(
                            'title' => $quote['title'],
                            'quote' => $quote['quote'],
                            'sort_order' => $quote['sort_order'],
                            'error' => $quote['error']
                        );
                    }
                }
            }

            $sortOrder = [];

            foreach ($methodData as $key => $value) {
                $sortOrder[$key] = $value['sort_order'];
            }

            array_multisort($sortOrder, SORT_ASC, $methodData);
        }

        $data['shipping_methods'] = $this->session->data['shipping_methods'] = $methodData;

        if (isset($this->request->post['shipping_method'])) {
            $shippingMethod = $this->request->post['shipping_method'];
            $shipping = explode('.', $shippingMethod);

            if (isset($shipping[0]) &&
                isset($shipping[1]) && isset($methodData[$shipping[0]]['quote'][$shipping[1]])) {
                $this->session->data['shipping_method'] = $methodData[$shipping[0]]['quote'][$shipping[1]];
            } else {
                unset($this->session->data['shipping_method']);
            }
        }

        if (!isset($this->session->data['shipping_method']) && $methodData) {
            $firstMethod = [];
            foreach ($methodData as $key => $method) {
                if ($method['quote'] != '') {
                    $keys = array_keys($method['quote']);
                    $key = $keys[0];
                }
                $firstMethod = $method['quote'][$key];
                break;
            }

            $this->session->data['shipping_method'] = $firstMethod;
        }

        $data['code'] = isset($this->session->data['shipping_method']['code']) ?
            $this->session->data['shipping_method']['code'] : '';
        $data['error_warning'] = (empty($this->session->data['shipping_methods'])) ?
            sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact')) : '';
        $result = $this->load->view('checkout/uni_shipping', $data);
        if ($this->cart->hasShipping()) {
            if (isset($this->request->get['render'])) {
                $this->response->setOutput($result);
            } else {
                return $result;
            }
        } else {
            return '';
        }
    }

    /**
     * Get method payment for order
     *
     * @return string
     */
    public function payment_method()
    {
        $this->load->language('extension/module/uni_othertext');
        $this->load->language('checkout/checkout');
        $this->load->language('checkout/uni_checkout');

        $paymentAddress = isset($this->session->data['payment_address']) ?
            $this->session->data['payment_address'] :
            array(
                'country_id' => $this->config->get('config_country_id'),
                'zone_id' => $this->config->get('config_zone_id'),
                'firstname' => '',
                'lastname' => '',
                'company' => '',
                'address_1' => '',
                'city' => '',
                'iso_code2' => '',
                'iso_code3' => ''
            );

        if (!isset($this->session->data['payment_zone_id'])) {
            $this->session->data['payment_zone_id '] = $paymentAddress['zone_id'];
        }

        $this->tax->setPaymentAddress($paymentAddress['country_id'], $paymentAddress['zone_id']);

        $methodData = [];

        if ($paymentAddress) {
            $totalData = [];
            $total = 0;
            $taxes = $this->cart->getTaxes();
            $totals = [];
            $totalData = [
                'totals' => &$totals,
                'taxes' => &$taxes,
                'total' => &$total
            ];

            $this->load->model('setting/extension');
            $results = $this->model_setting_extension->getExtensions('total');
            $sortOrder = [];
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

            $results = $this->model_setting_extension->getExtensions('payment');
            $recurring = $this->cart->hasRecurringProducts();

            foreach ($results as $result) {
                if ($this->config->get('payment_' . $result['code'] . '_status')) {
                    $this->load->model('extension/payment/' . $result['code']);
                    $method = $this->{'model_extension_payment_' . $result['code']}->getMethod(
                        $paymentAddress,
                        $total
                    );

                    if ($method) {
                        if ($this->config->get('config_current_country') != 220 &&
                            $this->config->get('config_current_country') != 20) {
                            if ($recurring) {
                                if (property_exists(
                                        $this->{'model_extension_payment_' . $result['code']},
                                        'recurringPayments') &&
                                    $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
                                    $methodData[$result['code']] = $method;
                                }
                            } else {
                                $methodData[$result['code']] = $method;
                            }
                        } else {
                            if ($method['code'] != 'yandex_money') {
                                if ($recurring) {
                                    if (property_exists(
                                            $this->{'model_extension_payment_' . $result['code']},
                                            'recurringPayments') &&
                                        $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
                                        $methodData[$result['code']] = $method;
                                    }
                                } else {
                                    $methodData[$result['code']] = $method;
                                }
                            }
                        }
                    }
                }
            }

            $sortOrder = [];

            foreach ($methodData as $key => $value) {
                $sortOrder[$key] = $value['sort_order'];
            }
            array_multisort($sortOrder, SORT_ASC, $methodData);
        }

        $data['payment_methods'] = $this->session->data['payment_methods'] = $methodData;
        if (isset($this->request->post['payment_method'])) {
            $paymentMethod = $this->request->post['payment_method'];

            if (isset($methodData[$paymentMethod])) {
                $this->session->data['payment_method'] = $methodData[$paymentMethod];
            } else {
                unset($this->session->data['payment_method']);
            }
        }

        if (!isset($this->session->data['payment_method']) && $methodData) {
            $firstMethod = [];
            foreach ($methodData as $key => $method) {
                $firstMethod = $method;
                break;
            }
            $this->session->data['payment_method'] = $firstMethod;
        }

        $data['code'] = isset($this->session->data['payment_method']['code']) ?
            $this->session->data['payment_method']['code'] : '';
        $data['error_warning'] = empty($this->session->data['payment_methods']) ?
            sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact')) : '';

        $result = $this->load->view('checkout/uni_payment', $data);

        if (isset($this->request->get['render'])) {
            $this->response->setOutput($result);
        } else {
            return $result;
        }
    }

    /**
     * View customer input for order
     *
     * @param string $location
     * @return string
     */
    public function custom_field($location = '')
    {
        $data['custom_fields'] = [];

        $this->load->model('account/custom_field');

        $customFields = $this->model_account_custom_field->getCustomFields(
            $this->config->get('config_customer_group_id')
        );

        foreach ($customFields as $customField) {
            if ($customField['location'] == $location) {
                $data['custom_fields'][] = $customField;
            }
        }

        $data['checked'] = [];

        if (isset($this->session->data['custom_field'])) {
            $data['checked'] = $this->session->data['custom_field'];
        }

        if (isset($this->session->data['payment_address']['custom_field'])) {
            $data['checked'] = $this->session->data['payment_address']['custom_field'];
        }

        return $this->load->view('checkout/uni_customfield', $data);
    }

    /**
     * View products in page order
     *
     * @return string
     */
    public function cart()
    {
        $data = [];
        $this->load->language('product/product');
        $this->load->language('checkout/cart');
        $this->load->language('extension/module/uni_othertext');
        $this->load->language('checkout/uni_checkout');
        $this->load->language('checkout/checkout');

        $uniset = $this->config->get('config_unishop2');
        $languageId = $this->config->get('config_language_id');

        if (!isset($this->session->data['vouchers'])) {
            $this->session->data['vouchers'] = [];
        }

        $points = $this->customer->getRewardPoints();
        $pointsTotal = 0;

        foreach ($this->cart->getProducts() as $product) {
            if ($product['points']) {
                $pointsTotal += $product['points'];
            }
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } elseif (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') ||
                $this->config->get('config_stock_warning'))) {
            $data['error_warning'] = $this->language->get('error_stock');
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

        $data['weight'] = $this->config->get('config_cart_weight') && $this->cart->getWeight() ?
            $this->weight->format(
                $this->cart->getWeight(),
                $this->config->get('config_weight_class_id'),
                $this->language->get('decimal_point'),
                $this->language->get('thousand_point')
            ) : '';

        $this->load->model('tool/image');

        $data['products'] = [];
        $this->load->model('catalog/product');
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
                    if (isset($option['option_value'])) {
                        $value = $option['option_value'];
                    } elseif (isset($option['value'])) {
                        $value = $option['value'];
                    } else {
                        $value = '';
                    }
                } else {
                    $filename = $this->encryption->decrypt(
                        isset($option['option_value']) ?
                            $option['option_value'] :
                            isset($option['value']) ? $option['value'] : '');
                    $value = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
                }

                $optionData[] = [
                    'name' => $option['name'],
                    'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                ];
            }

            if (($this->config->get('config_customer_price') &&
                    $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format($product['price'], $this->session->data['currency'], true);
            } else {
                $price = false;
            }

            if (($this->config->get('config_customer_price') && $this->customer->isLogged()) ||
                !$this->config->get('config_customer_price')) {
                $total = $this->currency->format(
                    $product['price'] * $product['quantity'],
                    $this->session->data['currency'],
                    true
                );
            } else {
                $total = false;
            }

            $recurring = '';

            if ($product['recurring']) {
                $frequencies = array(
                    'day' => $this->language->get('text_day'),
                    'week' => $this->language->get('text_week'),
                    'semi_month' => $this->language->get('text_semi_month'),
                    'month' => $this->language->get('text_month'),
                    'year' => $this->language->get('text_year')
                );

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
                                $product['recurring']['price'] * $product['quantity'],
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
                                $product['recurring']['price'] * $product['quantity'],
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
            $countCurrentCountry =
                $this->model_catalog_product->getCountProductInCurrentCountry($product['product_id']);
            if (empty($countCurrentCountry) || $countCurrentCountry < $product['quantity']) {
                $this->load->model('stockroom/stockroom');
                $stockroomsAttached = $this->model_stockroom_stockroom->getAttachedStockroomsForCountry();
                if (!empty($stockroomsAttached)) {
                    $delivery = $this->load->controller(
                        'checkout/cart/getDeliveryStockroomAttached',
                        $countCurrentCountry,
                        $stockroomsAttached,
                        $product
                    );
                }
            }

            $data['products'][] = [
                'cart_id' => $product['cart_id'],
                'product_id' => $product['product_id'],
                'delivery' => !empty($delivery) ? $delivery : "",
                'amount' => $productInfo['amount'],
                'minimum' => $productInfo['minimum'],
                'thumb' => $image,
                'name' => $product['name'],
                'stock_count' => $product['stock_count'],
                'model' => $product['model'],
                'option' => $optionData,
                'quantity' => $product['quantity'],
                'stock' => $product['stock'] ?
                    true :
                    !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
                'reward' => ($product['reward'] ?
                    sprintf($this->language->get('text_points'), $product['reward']) : ''),
                'price' => $price,
                'total' => $total,
                'totalCount' => $product,
                'href' => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                'remove' => $this->url->link('checkout/cart', 'remove=' . $product['product_id']),
                'recurring' => $recurring,
            ];
        }

        $data['related'] = isset($uniset['checkout_related_product']) ? $uniset['checkout_related_product'] : '';
        $data['checkout_related_text'] =
            isset($uniset[$languageId]['checkout_related_text']) ? $uniset[$languageId]['checkout_related_text'] : '';
        $data['products_related'] = $this->products_related();
        $data['products_related_after'] =
            isset($uniset['checkout_related_product_after']) ? $uniset['checkout_related_product_after'] : '';

        $data['products_recurring'] = [];
        $data['vouchers'] = [];

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $key => $voucher) {
                $data['vouchers'][] = array(
                    'key' => $key,
                    'description' => $voucher['description'],
                    'amount' => $this->currency->format($voucher['amount'], $this->session->data['currency']),
                    'remove' => $this->url->link('checkout/cart', 'remove=' . $key)
                );
            }
        }

        $data['coupon_status'] = $this->config->get('total_coupon_status');
        if (isset($this->request->post['coupon'])) {
            $data['coupon'] = $this->request->post['coupon'];
        } elseif (isset($this->session->data['coupon'])) {
            $data['coupon'] = $this->session->data['coupon'];
        } else {
            $data['coupon'] = '';
        }

        $data['voucher_status'] = $this->config->get('total_voucher_status');
        if (isset($this->request->post['voucher'])) {
            $data['voucher'] = $this->request->post['voucher'];
        } elseif (isset($this->session->data['voucher'])) {
            $data['voucher'] = $this->session->data['voucher'];
        } else {
            $data['voucher'] = '';
        }

        $data['reward_status'] = ($points && $pointsTotal && $this->config->get('total_reward_status'));

        if (isset($this->request->post['reward'])) {
            $data['reward'] = $this->request->post['reward'];
        } elseif (isset($this->session->data['reward'])) {
            $data['reward'] = $this->session->data['reward'];
        } else {
            $data['reward'] = '';
        }

        $this->load->model('setting/extension');

        $totalData = [];
        $total = 0;
        $taxes = $this->cart->getTaxes();

        $totals = [];
        $totalData = [
            'totals' => &$totals,
            'taxes' => &$taxes,
            'total' => &$total
        ];

        $results = $this->model_setting_extension->getExtensions('total');
        $sortOrder = [];
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
        $sum = 0;

        foreach ($data['products'] as $e) {
            $sum += (float)$e['total'];
        }

        $sortOrder = [];
        foreach ($totals as $key => $value) {
            if ($value['code'] == 'sub_total' || $value['code'] == 'total') {
                $totals[$key]['value'] = $sum;
            }
            $sortOrder[$key] = $value['sort_order'];
        }

        array_multisort($sortOrder, SORT_ASC, $totals);
        $data['totals'] = [];
        foreach ($totals as $total) {
            $data['totals'][] = array(
                'title' => $total['title'],
                'text' => $this->currency->format($total['value'], $this->session->data['currency'], true),
            );
        }

        $result = $this->load->view('checkout/uni_cart', $data);

        if (isset($this->request->get['render'])) {
            $this->response->setOutput($result);
        } else {
            return $result;
        }
    }

    /**
     * Save order
     *
     * @return mixed
     */
    private function addOrder()
    {
        $this->load->language('checkout/checkout');

        $this->load->model('account/customer');
        $this->load->model('setting/extension');

        if (!$this->cart->hasShipping()) {
            unset($this->session->data['shipping_address']);
            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
        }

        $currency = $this->session->data['currency'];

        $orderData = [];

        $totalData = [];
        $total = 0;
        $taxes = $this->cart->getTaxes();
        $totals = [];
        $totalData = [
            'totals' => &$totals,
            'taxes' => &$taxes,
            'total' => &$total
        ];

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

        $orderData['totals'] = $totals;
        $orderData['invoice_prefix'] = $this->config->get('config_invoice_prefix');
        $orderData['store_id'] = $this->config->get('config_store_id');
        $orderData['store_name'] = $this->config->get('config_name');
        $orderData['store_url'] = $orderData['store_id'] ?
            $this->config->get('config_url') : $this->config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER;

        $orderData['firstname'] = $this->session->data['firstname'];
        $orderData['lastname'] = $this->session->data['lastname'];
        $orderData['patronymic'] = $this->session->data['patronymic'] ?? '';
        $orderData['email'] = $this->session->data['email'];
        $orderData['telephone'] = $this->session->data['telephone'] ?? '';

        if ($this->customer->isLogged()) {
            $customerId = $this->customer->getId();
            $customerInfo = $this->model_account_customer->getCustomer($customerId);
            $orderData['customer_id'] = $customerId;
            $orderData['customer_group_id'] = $customerInfo['customer_group_id'];
            $orderData['fax'] = $customerInfo['fax'];
        } elseif (isset($this->session->data['approve_customer'])) {
            $customerId = $this->session->data['customer_id'];
            $customerInfo = $this->model_account_customer->getCustomer($customerId);
            $orderData['customer_id'] = $customerId;
            $orderData['customer_group_id'] = $customerInfo['customer_group_id'];
            $orderData['fax'] = $customerInfo['fax'];
            unset($this->session->data['approve_customer']);
        } else {
            $orderData['customer_id'] = 0;
            $orderData['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
            $orderData['fax'] = $this->session->data['guest']['fax'];
        }

        $orderData['custom_field'] = isset($this->session->data['custom_field']) ?
            $this->session->data['custom_field'] : [];
        $orderData['payment_firstname'] = $orderData['firstname'];
        $orderData['payment_lastname'] = $orderData['lastname'];
        $orderData['payment_patronymic'] = $orderData['patronymic'];
        $orderData['payment_company'] = '';
        $orderData['payment_city'] = '';
        $orderData['payment_postcode'] = '';
        $orderData['payment_zone'] = '';
        $orderData['payment_zone_id'] = '';
        $orderData['payment_country'] = '';
        $orderData['payment_country_id'] = '';
        $orderData['payment_address_format'] = '';
        $orderData['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ?
            $this->session->data['payment_address']['custom_field'] : array());
        $orderData['payment_address_1'] = $this->session->data['address'] ?? '';
        $orderData['payment_address_2'] = $this->session->data['address'] ?? '';
        $orderData['order_status_id'] = 0;

        $orderData['payment_method'] = isset($this->session->data['payment_method']['title']) ?
            $this->session->data['payment_method']['title'] : '';
        $orderData['payment_code'] = isset($this->session->data['payment_method']['code']) ?
            $this->session->data['payment_method']['code'] : '';

        if ($this->cart->hasShipping()) {
            $orderData['shipping_firstname'] = $orderData['firstname'];
            $orderData['shipping_lastname'] = $orderData['lastname'];
            $orderData['shipping_patronymic'] = $orderData['patronymic'];
            $orderData['shipping_company'] = '';
            $orderData['shipping_city'] = '';
            $orderData['shipping_postcode'] = '';
            $orderData['shipping_zone'] = '';
            $orderData['shipping_zone_id'] = '';
            $orderData['shipping_country'] = '';
            $orderData['shipping_country_id'] = '';
            $orderData['shipping_address_format'] = '';
            $orderData['shipping_custom_field'] = (isset($this->session->data['shipping_address']['custom_field']) ?
                $this->session->data['shipping_address']['custom_field'] : array());

            $orderData['shipping_method'] = isset($this->session->data['shipping_method']['title']) ?
                $this->session->data['shipping_method']['title'] : '';
            $orderData['shipping_code'] = isset($this->session->data['shipping_method']['code']) ?
                $this->session->data['shipping_method']['code'] : '';
            $orderData['shipping_address_1'] = $orderData['shipping_address_2'] =
                ($orderData['shipping_code'] != 'pickup.pickup') ?
                    $this->session->data['address'] ??
                    '' :
                    '';
        } else {
            $orderData['shipping_firstname'] = '';
            $orderData['shipping_lastname'] = '';
            $orderData['shipping_patronymic'] = '';
            $orderData['shipping_company'] = '';
            $orderData['shipping_address_1'] = '';
            $orderData['shipping_address_2'] = '';
            $orderData['shipping_city'] = '';
            $orderData['shipping_postcode'] = '';
            $orderData['shipping_zone'] = '';
            $orderData['shipping_zone_id'] = '';
            $orderData['shipping_country'] = '';
            $orderData['shipping_country_id'] = '';
            $orderData['shipping_address_format'] = '';
            $orderData['shipping_custom_field'] = [];
            $orderData['shipping_method'] = '';
            $orderData['shipping_code'] = '';
        }

        $orderData['products'] = [];
        $orderData['total'] = 0;

        foreach ($this->cart->getProducts() as $product) {
            $optionData = [];

            foreach ($product['option'] as $option) {
                $optionData[] = [
                    'product_option_id' => $option['product_option_id'],
                    'product_option_value_id' => $option['product_option_value_id'],
                    'option_id' => $option['option_id'],
                    'option_value_id' => $option['option_value_id'],
                    'name' => $option['name'],
                    'value' => $option['value'],
                    'type' => $option['type']
                ];
            }
            $price = $this->currency->format($product['price'], $currency, true);
            $total = $this->currency->format($product['price'] * $product['quantity'], $currency, true);

            $orderData['products'][] = [
                'product_id' => $product['product_id'],
                'name' => $product['name'],
                'model' => $product['model'],
                'option' => $optionData,
                'download' => $product['download'],
                'quantity' => $product['quantity'],
                'subtract' => $product['subtract'],
                'price' => $price,
                'total' => $total,
                'tax' => $this->tax->getTax($product['price'], $product['tax_class_id']),
                'reward' => $product['reward']
            ];

            (float)$orderData['total'] += (float)$total;
        }

        $orderData['vouchers'] = [];

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $voucher) {
                $orderData['vouchers'][] = [
                    'description' => $voucher['description'],
                    'code' => substr(md5(mt_rand()), 0, 10),
                    'to_name' => $voucher['to_name'],
                    'to_email' => $voucher['to_email'],
                    'from_name' => $voucher['from_name'],
                    'from_email' => $voucher['from_email'],
                    'voucher_theme_id' => $voucher['voucher_theme_id'],
                    'message' => $voucher['message'],
                    'amount' => $voucher['amount']
                ];
            }
        }

        $orderData['comment'] = $this->session->data['comment'];

        if (isset($this->request->cookie['tracking'])) {
            $orderData['tracking'] = $this->request->cookie['tracking'];

            $subtotal = $this->cart->getSubTotal();

            // Affiliate
            $affiliateInfo = $this->model_account_customer->getAffiliateByTracking($this->request->cookie['tracking']);

            if ($affiliateInfo) {
                $orderData['affiliate_id'] = $affiliateInfo['customer_id'];
                $orderData['commission'] = ($subtotal / 100) * $affiliateInfo['commission'];
            } else {
                $orderData['affiliate_id'] = 0;
                $orderData['commission'] = 0;
            }

            // Marketing
            $this->load->model('checkout/marketing');

            $marketingInfo = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

            if ($marketingInfo) {
                $orderData['marketing_id'] = $marketingInfo['marketing_id'];
            } else {
                $orderData['marketing_id'] = 0;
            }
        } else {
            $orderData['affiliate_id'] = 0;
            $orderData['commission'] = 0;
            $orderData['marketing_id'] = 0;
            $orderData['tracking'] = '';
        }

        $orderData['language_id'] = $this->config->get('config_language_id');
        $orderData['currency_id'] = $this->currency->getId($this->session->data['currency']);
        $orderData['currency_code'] = $this->session->data['currency'];
        $orderData['currency_value'] = $this->currency->getValue($this->session->data['currency']);
        $orderData['ip'] = $this->request->server['REMOTE_ADDR'];

        if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
            $orderData['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
            $orderData['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
        } else {
            $orderData['forwarded_ip'] = '';
        }

        if (isset($this->request->server['HTTP_USER_AGENT'])) {
            $orderData['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
        } else {
            $orderData['user_agent'] = '';
        }

        if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
            $orderData['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
        } else {
            $orderData['accept_language'] = '';
        }

        $this->load->model('checkout/order');
        if (!$this->customer->isLogged()) {
            $dateUser = $this->addNewCustomer($orderData);
            $orderData['customer_id'] = $dateUser['customer_id'];
        }
        if (!empty($this->session->data['newAddress'])) {
            $this->model_account_customer->addNewAddressUser(
                $orderData['customer_id'],
                $this->session->data['address']
            );
        }

        $this->session->data['order_id'] = $this->model_checkout_order->addOrder($orderData);
        $this->model_checkout_order->sendOrder1C($this->session->data['order_id'], $orderData);
        $payContent = $this->load->controller('extension/payment/' . $this->session->data['payment_method']['code']);

        return [
            'metod' => $this->session->data['payment_method']['code'],
            'result' => $payContent
        ];
    }

    /**
     * Get load payed
     *
     * @return mixed
     */
    public function loadpay()
    {
        header('Content-Type:text/html; charset=UTF-8');
        $data['success'] = $this->load->controller(
            'extension/payment/' . $this->session->data['payment_method']['code']
        );
        return $data;
    }

    /**
     * Get country order
     *
     */
    public function country()
    {
        $json = [];
        $this->load->model('localisation/country');
        $countryInfo = $this->model_localisation_country->getCountry($this->request->get['country_id']);
        if ($countryInfo) {
            $this->load->model('localisation/zone');
            $json = [
                'country_id' => $countryInfo['country_id'],
                'name' => $countryInfo['name'],
                'iso_code_2' => $countryInfo['iso_code_2'],
                'iso_code_3' => $countryInfo['iso_code_3'],
                'address_format' => $countryInfo['address_format'],
                'postcode_required' => $countryInfo['postcode_required'],
                'zone' => $this->model_localisation_zone->getZonesByCountryId(
                    $this->request->get['country_id']
                ),
                'status' => $countryInfo['status']
            ];
        }

        $this->response->setOutput(json_encode($json));
    }

    /**
     * Get related products
     *
     * @return array
     */
    private function products_related()
    {
        $uniset = $this->config->get('config_unishop2');
        $languageId = $this->config->get('config_language_id');

        $this->load->model('tool/image');
        $this->load->model('extension/module/uni_related');
        $this->load->model('extension/module/uni_new_data');

        $currency = $this->session->data['currency'];

        $this->load->model('catalog/product');
        $this->load->model('tool/image');
        $data['checkout_related_text'] = $uniset[$languageId]['checkout_related_text'];

        $products = [];
        if ($this->cart->getProducts()) {
            $results = $this->model_extension_module_uni_related->getRelated();
            foreach ($results as $result) {
                $image = $result['image'] ? $this->model_tool_image->resize($result['image'], 110, 110) : '';
                if (($this->config->get('config_customer_price') && $this->customer->isLogged()) ||
                    !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format(
                        $this->tax->calculate(
                            $result['price'],
                            $result['tax_class_id'],
                            $this->config->get('config_tax')
                        ),
                        $currency
                    );
                } else {
                    $price = false;
                }

                if ((float)$result['special']) {
                    $special = $this->currency->format(
                        $this->tax->calculate(
                            $result['special'],
                            $result['tax_class_id'],
                            $this->config->get('config_tax')
                        ),
                        $currency
                    );
                } else {
                    $special = false;
                }

                if ($this->config->get('config_tax')) {
                    $tax = $this->currency->format(
                        (float)$result['special'] ? $result['special'] : $result['price'],
                        $currency
                    );
                } else {
                    $tax = false;
                }

                if ($this->config->get('config_review_status')) {
                    $rating = (int)$result['rating'];
                } else {
                    $rating = false;
                }

                $newData = $this->model_extension_module_uni_new_data->getNewData($result);

                if ($newData['special_date_end']) {
                    $data['show_timer'] = true;
                }

                if ($result['quantity'] > 0) {
                    $products[] = array(
                        'product_id' => $result['product_id'],
                        'thumb' => $image,
                        'name' => $result['name'],
                        'description' => utf8_substr(
                            strip_tags(
                                html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')
                            ),
                            0,
                            $this->config->get('config_product_description_length')
                        ) . '..',
                        'price' => $price,
                        'special' => $special,
                        'price_value' => $this->tax->calculate(
                            $result['price'],
                            $result['tax_class_id'],
                            $this->config->get('config_tax')
                        ) * $this->currency->getValue($currency),
                        'special_value' => $this->tax->calculate(
                            $result['special'],
                            $result['tax_class_id'],
                            $this->config->get('config_tax')
                        ) * $this->currency->getValue($currency),
                        'tax' => $tax,
                        'rating' => $rating,
                        'additional_image' => '',
                        'num_reviews' => $result['reviews'],
                        'quantity' => $result['quantity'],
                        'minimum' => $result['minimum'],
                        'stickers' => $newData['stickers'],
                        'options' => $newData['options'],
                        'reviews' => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                        'href' => $this->url->link('product/product', 'product_id=' . $result['product_id']),
                        'cart_btn_disabled' => $result['quantity'] <= 0 &&
                        isset($uniset['cart_btn_disabled']) ? $uniset['cart_btn_disabled'] : '',
                        'cart_btn_icon_mobile' => $result['quantity'] <= 0 &&
                        isset($uniset['cart_btn_icon_disabled_mobile']) ? $uniset['cart_btn_icon_disabled_mobile'] : '',
                        'cart_btn_icon' => $result['quantity'] > 0 ?
                            $uniset[$languageId]['cart_btn_icon'] : $uniset[$languageId]['cart_btn_icon_disabled'],
                        'cart_btn_text' => $result['quantity'] > 0 ?
                            $uniset[$languageId]['cart_btn_text'] : $uniset[$languageId]['cart_btn_text_disabled'],
                    );
                }
            }
        }

        return $products;
    }
}
