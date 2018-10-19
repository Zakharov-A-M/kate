<?php

class ControllerAccountAffiliate extends Controller
{
    private $error = [];

    public function add()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/affiliate');
            $this->response->redirect($this->url->link('affiliate/login'));
        }

        $this->load->language('account/affiliate');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('account/affiliate');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_account_affiliate->addAffiliate($this->customer->getId(), $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('account/account'));
        }
        $this->getForm();
    }

    public function edit()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/affiliate');
            $this->response->redirect($this->url->link('affiliate/login'));
        }

        $this->load->language('account/affiliate');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('account/affiliate');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_account_affiliate->editAffiliate($this->customer->getId(), $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('account/account'));
        }
        $this->getForm();
    }

    public function getForm()
    {
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account')
        ];

        if ($this->request->get['route'] == 'account/affiliate/add') {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_affiliate'),
                'href' => $this->url->link('account/affiliate/add')
            ];
        } else {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_affiliate'),
                'href' => $this->url->link('account/affiliate/edit')
            ];
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['cheque'])) {
            $data['error_cheque'] = $this->error['cheque'];
        } else {
            $data['error_cheque'] = '';
        }

        if (isset($this->error['paypal'])) {
            $data['error_paypal'] = $this->error['paypal'];
        } else {
            $data['error_paypal'] = '';
        }

        if (isset($this->error['bank_account_name'])) {
            $data['error_bank_account_name'] = $this->error['bank_account_name'];
        } else {
            $data['error_bank_account_name'] = '';
        }

        if (isset($this->error['bank_account_number'])) {
            $data['error_bank_account_number'] = $this->error['bank_account_number'];
        } else {
            $data['error_bank_account_number'] = '';
        }

        if (isset($this->error['custom_field'])) {
            $data['error_custom_field'] = $this->error['custom_field'];
        } else {
            $data['error_custom_field'] = array();
        }

        $data['action'] = $this->url->link($this->request->get['route']);

        if ($this->request->get['route'] == 'account/affiliate/edit' &&
            $this->request->server['REQUEST_METHOD'] != 'POST') {
            $affiliateInfo = $this->model_account_affiliate->getAffiliate($this->customer->getId());
        }

        if (isset($this->request->post['company'])) {
            $data['company'] = $this->request->post['company'];
        } elseif (!empty($affiliateInfo)) {
            $data['company'] = $affiliateInfo['company'];
        } else {
            $data['company'] = '';
        }

        if (isset($this->request->post['website'])) {
            $data['website'] = $this->request->post['website'];
        } elseif (!empty($affiliateInfo)) {
            $data['website'] = $affiliateInfo['website'];
        } else {
            $data['website'] = '';
        }

        if (isset($this->request->post['tax'])) {
            $data['tax'] = $this->request->post['tax'];
        } elseif (!empty($affiliateInfo)) {
            $data['tax'] = $affiliateInfo['tax'];
        } else {
            $data['tax'] = '';
        }

        if (isset($this->request->post['payment'])) {
            $data['payment'] = $this->request->post['payment'];
        } elseif (!empty($affiliateInfo)) {
            $data['payment'] = $affiliateInfo['payment'];
        } else {
            $data['payment'] = 'cheque';
        }

        if (isset($this->request->post['cheque'])) {
            $data['cheque'] = $this->request->post['cheque'];
        } elseif (!empty($affiliateInfo)) {
            $data['cheque'] = $affiliateInfo['cheque'];
        } else {
            $data['cheque'] = '';
        }

        if (isset($this->request->post['paypal'])) {
            $data['paypal'] = $this->request->post['paypal'];
        } elseif (!empty($affiliateInfo)) {
            $data['paypal'] = $affiliateInfo['paypal'];
        } else {
            $data['paypal'] = '';
        }

        if (isset($this->request->post['bank_name'])) {
            $data['bank_name'] = $this->request->post['bank_name'];
        } elseif (!empty($affiliateInfo)) {
            $data['bank_name'] = $affiliateInfo['bank_name'];
        } else {
            $data['bank_name'] = '';
        }

        if (isset($this->request->post['bank_branch_number'])) {
            $data['bank_branch_number'] = $this->request->post['bank_branch_number'];
        } elseif (!empty($affiliateInfo)) {
            $data['bank_branch_number'] = $affiliateInfo['bank_branch_number'];
        } else {
            $data['bank_branch_number'] = '';
        }

        if (isset($this->request->post['bank_swift_code'])) {
            $data['bank_swift_code'] = $this->request->post['bank_swift_code'];
        } elseif (!empty($affiliateInfo)) {
            $data['bank_swift_code'] = $affiliateInfo['bank_swift_code'];
        } else {
            $data['bank_swift_code'] = '';
        }

        if (isset($this->request->post['bank_account_name'])) {
            $data['bank_account_name'] = $this->request->post['bank_account_name'];
        } elseif (!empty($affiliateInfo)) {
            $data['bank_account_name'] = $affiliateInfo['bank_account_name'];
        } else {
            $data['bank_account_name'] = '';
        }

        if (isset($this->request->post['bank_account_number'])) {
            $data['bank_account_number'] = $this->request->post['bank_account_number'];
        } elseif (!empty($affiliateInfo)) {
            $data['bank_account_number'] = $affiliateInfo['bank_account_number'];
        } else {
            $data['bank_account_number'] = '';
        }

        // Custom Fields
        $this->load->model('account/custom_field');

        $data['custom_fields'] = $this->model_account_custom_field->getCustomFields(
            $this->config->get('config_customer_group_id')
        );

        if (isset($this->request->post['custom_field'])) {
            $data['affiliate_custom_field'] = $this->request->post['custom_field'];
        } elseif (isset($affiliateInfo)) {
            $data['affiliate_custom_field'] = json_decode($affiliateInfo['custom_field'], true);
        } else {
            $data['affiliate_custom_field'] = array();
        }

        $affiliateInfo = $this->model_account_affiliate->getAffiliate($this->customer->getId());

        if (!$affiliateInfo && $this->config->get('config_affiliate_id')) {
            $this->load->model('catalog/information');

            $informationInfo = $this->model_catalog_information->getInformation(
                $this->config->get('config_affiliate_id')
            );

            if ($informationInfo) {
                $data['text_agree'] = sprintf(
                    $this->language->get('text_agree'),
                    $this->url->link(
                        'information/information/agree',
                        'information_id=' . $this->config->get('config_affiliate_id')
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

        $data['back'] = $this->url->link('account/account');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $this->response->setOutput($this->load->view('account/affiliate', $data));
    }

    protected function validate()
    {
        if ($this->request->post['payment'] == 'cheque' && !$this->request->post['cheque']) {
            $this->error['cheque'] = $this->language->get('error_cheque');
        } elseif (($this->request->post['payment'] == 'paypal') &&
                (
                    (utf8_strlen($this->request->post['paypal']) > 96) ||
                    !filter_var($this->request->post['paypal'], FILTER_VALIDATE_EMAIL)
                )
            ) {
            $this->error['paypal'] = $this->language->get('error_paypal');
        } elseif ($this->request->post['payment'] == 'bank') {
            if ($this->request->post['bank_account_name'] == '') {
                $this->error['bank_account_name'] = $this->language->get('error_bank_account_name');
            }

            if ($this->request->post['bank_account_number'] == '') {
                $this->error['bank_account_number'] = $this->language->get('error_bank_account_number');
            }
        }

        // Custom field validation
        $this->load->model('account/custom_field');

        $customFields = $this->model_account_custom_field->getCustomFields(
            $this->config->get('config_customer_group_id')
        );

        foreach ($customFields as $customField) {
            if ($customField['location'] == 'affiliate') {
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
                            $this->request->
                            post['custom_field'][$customField['location']][$customField['custom_field_id']],
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

        // Validate agree only if customer not already an affiliate
        $affiliateInfo = $this->model_account_affiliate->getAffiliate($this->customer->getId());
        if (!$affiliateInfo && $this->config->get('config_affiliate_id')) {
            $this->load->model('catalog/information');
            $informationInfo =
                $this->model_catalog_information->getInformation($this->config->get('config_affiliate_id'));

            if ($informationInfo && !isset($this->request->post['agree'])) {
                $this->error['warning'] = sprintf($this->language->get('error_agree'), $informationInfo['title']);
            }
        }

        return !$this->error;
    }
}
