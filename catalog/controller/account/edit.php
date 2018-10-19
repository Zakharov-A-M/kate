<?php

class ControllerAccountEdit extends Controller
{
    private $error = [];

    /**
     * View page contact information user
     */
    public function index()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/edit');

            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->language('account/edit');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
        $this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

        $this->load->model('account/customer');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if ($this->config->get('config_customer_group_id') == 2) {
                $this->model_account_customer->editCustomerFX($this->customer->getId(), $this->request->post);
            } else {
                $this->model_account_customer->editCustomerUR($this->customer->getId(), $this->request->post);
            }

            $this->model_account_customer->sendAccount1C($this->customer->getId(), $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

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

        $data['action'] = $this->url->link('account/edit');

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $customerInfo = $this->model_account_customer->getCustomer($this->customer->getId());
            if (isset($this->session->data['success'])) {
                $data['success'] = $this->session->data['success'];
                unset($this->session->data['success']);
            } else {
                $data['success'] = '';
            }
        }

        if (isset($this->request->post['firstname'])) {
            $data['firstname'] = $this->request->post['firstname'];
        } elseif (!empty($customerInfo)) {
            $data['firstname'] = $customerInfo['firstname'];
        } else {
            $data['firstname'] = '';
        }

        if (isset($this->request->post['lastname'])) {
            $data['lastname'] = $this->request->post['lastname'];
        } elseif (!empty($customerInfo)) {
            $data['lastname'] = $customerInfo['lastname'];
        } else {
            $data['lastname'] = '';
        }

        if (isset($this->request->post['patronymic'])) {
            $data['patronymic'] = $this->request->post['patronymic'];
        } elseif (!empty($customerInfo)) {
            $data['patronymic'] = $customerInfo['patronymic'];
        } else {
            $data['patronymic'] = '';
        }

        if (isset($this->request->post['email'])) {
            $data['email'] = $this->request->post['email'];
        } elseif (!empty($customerInfo)) {
            $data['email'] = $customerInfo['email'];
        } else {
            $data['email'] = '';
        }

        if (isset($this->request->post['telephone'])) {
            $data['telephone'] = $this->request->post['telephone'];
        } elseif (!empty($customerInfo)) {
            $data['telephone'] = $customerInfo['telephone'];
        } else {
            $data['telephone'] = '';
        }

        // Custom Fields
        $data['custom_fields'] = [];
        $this->load->model('account/custom_field');
        $customFields =
            $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

        foreach ($customFields as $customField) {
            if ($customField['location'] == 'account') {
                $data['custom_fields'][] = $customField;
            }
            if ($customField['location'] == 'account_edit') {
                $data['custom_fields'][] = $customField;
            }
        }

        if (isset($this->request->post['custom_field']['account'])) {
            $data['account_custom_field'] = $this->request->post['custom_field']['account'];
        } elseif (isset($customerInfo)) {
            $data['account_custom_field'] = json_decode($customerInfo['custom_field'], true);
        } else {
            $data['account_custom_field'] = [];
        }

        if (isset($this->request->post['custom_field']['account_edit'])) {
            $data['account_custom_field'] = $this->request->post['custom_field']['account_edit'];
        } elseif (isset($customerInfo)) {
            $data['account_custom_field'] = json_decode($customerInfo['custom_field'], true);
        } else {
            $data['account_custom_field'] = [];
        }

        $data['back'] = $this->url->link('account/edit');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left_profile'] = $this->load->controller('account/column_left_profile');

        $this->response->setOutput($this->load->view('account/edit', $data));
    }

    /**
     * Validate form information account user
     *
     * @return bool
     */
    protected function validate()
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

        if (($this->customer->getEmail() != $this->request->post['email']) &&
            $this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])
        ) {
            $this->error['warning'] = $this->language->get('error_exists');
        }

        // Custom field validation
        $this->load->model('account/custom_field');

        $customFields =
            $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

        foreach ($customFields as $customField) {
            if ($customField['location'] == 'account') {
                if ($customField['required'] &&
                    empty(
                    $this->request->post['custom_field'][$customField['location']][$customField['custom_field_id']]
                    )
                ) {
                    $this->error['custom_field'][$customField['custom_field_id']] = sprintf(
                        $this->language->get('error_custom_field'),
                        $customField['name']
                    );
                } elseif (($customField['type'] == 'text') && !empty($customField['validation']) &&
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
            if ($customField['location'] == 'account_edit') {
                if ($customField['required'] &&
                    empty(
                    $this->request->post['custom_field'][$customField['location']][$customField['custom_field_id']]
                    )
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
                if ($customField['custom_field_id'] == 15) {
                    $elemArr =
                        $this->request->post['custom_field'][$customField['location']][$customField['custom_field_id']];
                    foreach ($elemArr as $key => $item) {
                        if (!trim($item)) {
                            unset($this->request->post['custom_field'][$customField['location']][$customField['custom_field_id']][$key]);
                        }
                    }
                }
                if (($customField['custom_field_id'] == 12) &&
                    !empty(
                        $this->request->post['custom_field'][$customField['location']][$customField['custom_field_id']]
                    )&& (!$this->validateDate(
                        $this->request->post['custom_field'][$customField['location']][$customField['custom_field_id']])
                    )
                ) {
                     $this->error['custom_field'][$customField['custom_field_id']] = sprintf(
                         $this->language->get('error_validate_date'),
                         $customField['name']
                     );
                }
            }
        }
        return !$this->error;
    }

    /**
     * Validate form date
     *
     * @param string $date
     * @return bool
     */
    public function validateDate(string $date)
    {
        $today = new \DateTime();
        $date = \DateTime::createFromFormat('d:m:Y', $date);
        return $date && $date < $today;
    }
}
