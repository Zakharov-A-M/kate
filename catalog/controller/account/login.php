<?php

class ControllerAccountLogin extends Controller
{
    const TRY_REGISTERED = 3;
    const SHOW_CAPTCHA = 2;
    private $error = [];
    private $captcha = false;

    public function index()
    {
        if ($this->customer->isLogged()) {
            $this->response->redirect($this->url->link('account/edit'));
        }

        $this->load->language('account/login');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('account/customer');
        if (!empty($this->request->get['token'])) {
            $this->model_account_customer->approveUser($this->request->get['token']);
        }
        $this->load->model('catalog/product');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            // Unset guest
            unset($this->session->data['guest']);

            // Default Shipping Address
            $this->load->model('account/address');
            if ($this->config->get('config_tax_customer') == 'payment') {
                $this->session->data['payment_address'] = $this->model_account_address->getAddress(
                    $this->customer->getAddressId()
                );
            }

            if ($this->config->get('config_tax_customer') == 'shipping') {
                $this->session->data['shipping_address'] = $this->model_account_address->getAddress(
                    $this->customer->getAddressId()
                );
            }

            // Wishlist
            if (isset($this->session->data['wishlist']) && is_array($this->session->data['wishlist'])) {
                $this->load->model('account/wishlist');
                foreach ($this->session->data['wishlist'] as $key => $product_id) {
                    $this->model_account_wishlist->addWishlist($product_id);

                    unset($this->session->data['wishlist'][$key]);
                }
            }

            // Log the IP info
            $this->model_account_customer->addLogin($this->customer->getId(), $this->request->server['REMOTE_ADDR']);
            if (isset($this->request->post['redirect']) &&
                $this->request->post['redirect'] != $this->url->link('account/logout') &&
                (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false)
            ) {
                $this->response->redirect(str_replace('&amp;', '&', $this->request->post['redirect']));
            } elseif ($this->model_catalog_product->getTotalProductSpecials()) {
                $this->response->redirect($this->url->link('information/discount'));
            } else {
                $this->response->redirect($this->url->link('product/category'));
            }
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
            'text' => $this->language->get('text_login'),
            'href' => $this->url->link('account/login')
        ];

        if (isset($this->session->data['error'])) {
            $data['error_warning'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } elseif (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['action'] = $this->url->link('account/login');
        $data['register'] = $this->url->link('account/register');
        $data['forgotten'] = $this->url->link('account/forgotten');

        if (isset($this->request->post['redirect']) &&
            (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false)
        ) {
            $data['redirect'] = $this->request->post['redirect'];
        } elseif (isset($this->session->data['redirect'])) {
            $data['redirect'] = $this->session->data['redirect'];
            unset($this->session->data['redirect']);
        } else {
            $data['redirect'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['email'])) {
            $data['email'] = $this->request->post['email'];
        } else {
            $data['email'] = '';
        }

        if (isset($this->request->post['password'])) {
            $data['password'] = $this->request->post['password'];
        } else {
            $data['password'] = '';
        }

        // Captcha
        if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') &&
            in_array('authorization', (array)$this->config->get('config_captcha_page')) && $this->captcha
        ) {
            $data['captcha'] = $this->load->controller(
                'extension/captcha/' . $this->config->get('config_captcha'),
                $this->error
            );
        } else {
            $data['captcha'] = '';
        }

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/login', $data));
    }

    protected function validate()
    {
        // Check how many login attempts have been made.
        $loginInfo = $this->model_account_customer->getLoginAttempts($this->request->post['email']);

        if ($loginInfo &&
            ($loginInfo['total'] >= $this->config->get('config_login_attempts')) &&
            strtotime('-1 hour') < strtotime($loginInfo['date_modified'])
        ) {
            $this->error['warning'] = $this->language->get('error_attempts');
        }

        // Captcha
        if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') &&
            in_array('authorization', (array) $this->config->get('config_captcha_page')) &&
            !empty($loginInfo['total'])
        ) {
            if ($loginInfo['total'] >= self::TRY_REGISTERED) {
                $captcha = $this->load->controller(
                    'extension/captcha/' . $this->config->get('config_captcha') . '/validate'
                );
                if ($captcha) {
                    $this->error['captcha'] = $captcha;
                    $this->captcha = true;
                }
            }
        } elseif (!empty($loginInfo['total']) && $loginInfo['total'] >= self::SHOW_CAPTCHA) {
            $this->captcha = true;
        }

        // Check if customer has been approved.
        $customerInfo = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);
        if ($customerInfo && !$customerInfo['status']) {
            $this->error['warning'] = $this->language->get('error_approved');
        }

        if (!$this->error) {
            if (!$this->customer->login($this->request->post['email'], $this->request->post['password'])) {
                $this->error['warning'] = $this->language->get('error_login');
                $this->model_account_customer->addLoginAttempt($this->request->post['email']);
                if (!empty($loginInfo['total']) && $loginInfo['total'] >= self::SHOW_CAPTCHA) {
                    $this->captcha = true;
                }
            } else {
                $this->model_account_customer->deleteLoginAttempts($this->request->post['email']);
            }
        } elseif (!empty($loginInfo['total']) && $loginInfo['total'] >= self::SHOW_CAPTCHA) {
            $this->captcha = true;
        }

        return !$this->error;
    }

    public function token()
    {
        $this->load->language('account/login');

        if (isset($this->request->get['email'])) {
            $email = $this->request->get['email'];
        } else {
            $email = '';
        }

        if (isset($this->request->get['login_token'])) {
            $token = $this->request->get['login_token'];
        } else {
            $token = '';
        }

        // Login override for admin users
        $this->customer->logout();
        $this->cart->clear();

        unset($this->session->data['order_id']);
        unset($this->session->data['payment_address']);
        unset($this->session->data['payment_method']);
        unset($this->session->data['payment_methods']);
        unset($this->session->data['shipping_address']);
        unset($this->session->data['shipping_method']);
        unset($this->session->data['shipping_methods']);
        unset($this->session->data['comment']);
        unset($this->session->data['coupon']);
        unset($this->session->data['reward']);
        unset($this->session->data['voucher']);
        unset($this->session->data['vouchers']);

        $this->load->model('account/customer');

        $customerInfo = $this->model_account_customer->getCustomerByEmail($email);

        if ($customerInfo && $customerInfo['token'] &&
            $customerInfo['token'] == $token &&
            $this->customer->login($customerInfo['email'], '', true)
        ) {
            // Default Addresses
            $this->load->model('account/address');

            if ($this->config->get('config_tax_customer') == 'payment') {
                $this->session->data['payment_address'] = $this->model_account_address->getAddress(
                    $this->customer->getAddressId()
                );
            }

            if ($this->config->get('config_tax_customer') == 'shipping') {
                $this->session->data['shipping_address'] = $this->model_account_address->getAddress(
                    $this->customer->getAddressId()
                );
            }

            $this->model_account_customer->editToken($email, '');
            $this->response->redirect($this->url->link('account/edit'));
        } else {
            $this->session->data['error'] = $this->language->get('error_login');
            $this->model_account_customer->editToken($email, '');
            $this->response->redirect($this->url->link('account/login'));
        }
    }
}
