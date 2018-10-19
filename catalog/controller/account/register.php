<?php

class ControllerAccountRegister extends Controller
{
    private $error = [];

    /**
     * View page register account
     */
    public function index()
    {
        if ($this->customer->isLogged()) {
            $this->response->redirect($this->url->link('account/edit'));
        }

        $this->load->language('account/register');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
        $this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

        $this->load->model('account/customer');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            unset($this->session->data['guest']);
            $customerId = $this->model_account_customer->addCustomer($this->request->post);

            // Clear any previous login attempts for unregistered accounts.
            $this->model_account_customer->deleteLoginAttempts($this->request->post['email']);
            $this->customer->login($this->request->post['email'], $this->request->post['password']);

            // Log the IP info
            $this->model_account_customer->addLogin($this->customer->getId(), $this->request->server['REMOTE_ADDR']);

            $this->response->redirect($this->url->link('account/edit'));
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
            'text' => $this->language->get('text_register'),
            'href' => $this->url->link('account/register')
        ];
        $data['text_account_already'] = sprintf(
            $this->language->get('text_account_already'),
            $this->url->link('account/login')
        );

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['firstname'])) {
            $data['error_firstname'] = $this->error['firstname'];
        } else {
            $data['error_firstname'] = '';
        }

        if (isset($this->error['lastname'])) {
            $data['error_lastname'] = $this->error['lastname'];
        } else {
            $data['error_lastname'] = '';
        }

        if (isset($this->error['email'])) {
            $data['error_email'] = $this->error['email'];
        } else {
            $data['error_email'] = '';
        }

        if (isset($this->error['custom_field'])) {
            $data['error_custom_field'] = $this->error['custom_field'];
        } else {
            $data['error_custom_field'] = [];
        }

        if (isset($this->error['password'])) {
            $data['error_password'] = $this->error['password'];
        } else {
            $data['error_password'] = '';
        }

        if (isset($this->error['confirm'])) {
            $data['error_confirm'] = $this->error['confirm'];
        } else {
            $data['error_confirm'] = '';
        }

        $data['action'] = $this->url->link('account/register');

        $data['customer_groups'] = [];

        if (is_array($this->config->get('config_customer_group_display'))) {
            $this->load->model('account/customer_group');
            $customerGroups = $this->model_account_customer_group->getCustomerGroups();
            foreach ($customerGroups as $customerGroup) {
                if (in_array(
                    $customerGroup['customer_group_id'],
                    $this->config->get('config_customer_group_display'))
                ) {
                    $data['customer_groups'][] = $customerGroup;
                }
            }
        }

        if (isset($this->request->post['customer_group_id'])) {
            $data['customer_group_id'] = $this->request->post['customer_group_id'];
        } else {
            $data['customer_group_id'] = 2;
        }

        if (isset($this->request->post['firstname'])) {
            $data['firstname'] = $this->request->post['firstname'];
        } else {
            $data['firstname'] = '';
        }

        if (isset($this->request->post['lastname'])) {
            $data['lastname'] = $this->request->post['lastname'];
        } else {
            $data['lastname'] = '';
        }

        if (isset($this->request->post['patronymic'])) {
            $data['patronymic'] = $this->request->post['patronymic'];
        } else {
            $data['patronymic'] = '';
        }

        if (isset($this->request->post['email'])) {
            $data['email'] = $this->request->post['email'];
        } else {
            $data['email'] = '';
        }

        if (isset($this->request->post['telephone'])) {
            $data['telephone'] = $this->request->post['telephone'];
        } else {
            $data['telephone'] = '';
        }

        // Custom Fields
        $data['custom_fields'] = [];

        $this->load->model('account/custom_field');
        $customFields = $this->model_account_custom_field->getCustomFields();

        foreach ($customFields as $customField) {
            if ($customField['location'] == 'account') {
                $data['custom_fields'][] = $customField;
            }
        }

        if (isset($this->request->post['custom_field']['account'])) {
            $data['register_custom_field'] = $this->request->post['custom_field']['account'];
        } else {
            $data['register_custom_field'] = [];
        }

        if (isset($this->request->post['password'])) {
            $data['password'] = $this->request->post['password'];
        } else {
            $data['password'] = '';
        }

        if (isset($this->request->post['confirm'])) {
            $data['confirm'] = $this->request->post['confirm'];
        } else {
            $data['confirm'] = '';
        }

        if (isset($this->request->post['newsletter'])) {
            $data['newsletter'] = $this->request->post['newsletter'];
        } else {
            $data['newsletter'] = '';
        }

        // Captcha
        if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') &&
            in_array('register', (array)$this->config->get('config_captcha_page'))
        ) {
            $data['captcha'] = $this->load->controller(
                'extension/captcha/' . $this->config->get('config_captcha'),
                $this->error
            );
        } else {
            $data['captcha'] = '';
        }

        if ($this->config->get('config_account_id')) {
            $this->load->model('catalog/information');

            $informationInfo = $this->model_catalog_information->getInformation(
                $this->config->get('config_account_id')
            );
            if ($informationInfo) {
                $data['text_agree'] = sprintf(
                    $this->language->get('text_agree'),
                    $this->url->link(
                        'information/information/agree',
                        'information_id=' . $this->config->get('config_account_id')
                    ),
                    $informationInfo['title'],
                    $informationInfo['title']
                );
            } else {
                $data['text_agree'] = '';
            }
        } else {
            $data['text_agree'] = '';
        }

        if (isset($this->request->post['agree'])) {
            $data['agree'] = $this->request->post['agree'];
        } else {
            $data['agree'] = false;
        }

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $this->response->setOutput($this->load->view('account/register', $data));
    }

    /**
     * Validate form registration
     *
     * @return bool
     */
    private function validate()
    {
        if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) ||
            (utf8_strlen(trim($this->request->post['firstname'])) > 32)
        ) {
            $this->error['firstname'] = $this->language->get('error_firstname');
        }

        if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) ||
            (utf8_strlen(trim($this->request->post['lastname'])) > 32)
        ) {
            $this->error['lastname'] = $this->language->get('error_lastname');
        }

        if ((utf8_strlen($this->request->post['email']) > 96) ||
            !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)
        ) {
            $this->error['email'] = $this->language->get('error_email');
        }

        if ($this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
            $this->error['warning'] = $this->language->get('error_exists');
        }

        // Customer Group
        if (isset($this->request->post['customer_group_id']) &&
            is_array($this->config->get('config_customer_group_display')) &&
            in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))
        ) {
            $customerGroupId = $this->request->post['customer_group_id'];
        } else {
            $customerGroupId = $this->config->get('config_customer_group_id');
        }

        // Custom field validation
        $this->load->model('account/custom_field');
        $customFields = $this->model_account_custom_field->getCustomFields($customerGroupId);
        foreach ($customFields as $customField) {
            if ($customField['location'] == 'account') {
                if ($customField['required'] &&
                    empty($this->request->post['custom_field'][$customField['location']][$customField['custom_field_id']])
                ) {
                    $this->error['custom_field'][$customField['custom_field_id']] = sprintf(
                        $this->language->get('error_custom_field'),
                        $customField['name']
                    );
                } elseif (($customField['type'] == 'text') &&
                    !empty($customField['validation']) &&
                    !filter_var(
                        $this->request->post['custom_field'][$customField['location']][$customField['custom_field_id']],
                        FILTER_VALIDATE_REGEXP,
                        ['options' => ['regexp' => $customField['validation']]]
                    )
                ) {
                    $this->error['custom_field'][$customField['custom_field_id']] = sprintf(
                        $this->language->get('error_custom_field'),
                        $customField['name']
                    );
                }
            }
        }

        if ((utf8_strlen(
                html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) < 4) ||
            (utf8_strlen(
                html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) > 40)
        ) {
            $this->error['password'] = $this->language->get('error_password');
        }

        if ($this->request->post['confirm'] !== $this->request->post['password']) {
            $this->error['confirm'] = $this->language->get('error_confirm');
        }

        // Captcha
        if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') &&
            in_array('register', (array)$this->config->get('config_captcha_page'))
        ) {
            $captcha = $this->load->controller(
                'extension/captcha/' . $this->config->get('config_captcha') . '/validate'
            );
            if ($captcha) {
                $this->error['captcha'] = $captcha;
            }
        }

        // Agree to terms
        if ($this->config->get('config_account_id')) {
            $this->load->model('catalog/information');

            $informationInfo = $this->model_catalog_information->getInformation(
                $this->config->get('config_account_id')
            );
            if ($informationInfo && !isset($this->request->post['agree'])) {
                $this->error['warning'] = sprintf($this->language->get('error_agree'), $informationInfo['title']);
            }
        }

        return !$this->error;
    }

    /**
     * Get all custom field
     */
    public function customfield()
    {
        $json = array();

        $this->load->model('account/custom_field');

        // Customer Group
        if (isset($this->request->get['customer_group_id']) &&
            is_array($this->config->get('config_customer_group_display')) &&
            in_array($this->request->get['customer_group_id'], $this->config->get('config_customer_group_display'))
        ) {
            $customerGroupId = $this->request->get['customer_group_id'];
        } else {
            $customerGroupId = $this->config->get('config_customer_group_id');
        }

        $customFields = $this->model_account_custom_field->getCustomFields($customerGroupId);

        foreach ($customFields as $customField) {
            $json[] = array(
                'custom_field_id' => $customField['custom_field_id'],
                'required' => $customField['required']
            );
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
