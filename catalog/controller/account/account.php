<?php

class ControllerAccountAccount extends Controller
{
    public function index()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account');
            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->language('account/account');
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

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        $data['edit'] = $this->url->link('account/edit');
        $data['password'] = $this->url->link('account/password');
        $data['address'] = $this->url->link('account/address');
        $data['credit_cards'] = [];
        $files = glob(DIR_APPLICATION . 'controller/extension/credit_card/*.php');
        foreach ($files as $file) {
            $code = basename($file, '.php');
            if ($this->config->get('payment_' . $code . '_status') && $this->config->get('payment_' . $code . '_card')) {
                $this->load->language('extension/credit_card/' . $code, 'extension');
                $data['credit_cards'][] = [
                    'name' => $this->language->get('extension')->get('heading_title'),
                    'href' => $this->url->link('extension/credit_card/' . $code)
                ];
            }
        }

        $data['wishlist'] = $this->url->link('account/wishlist');
        $data['order'] = $this->url->link('account/order');
        $data['download'] = $this->url->link('account/download');

        if ($this->config->get('total_reward_status')) {
            $data['reward'] = $this->url->link('account/reward');
        } else {
            $data['reward'] = '';
        }

        $data['return'] = $this->url->link('account/return');
        $data['transaction'] = $this->url->link('account/transaction');
        $data['newsletter'] = $this->url->link('account/newsletter');
        $data['recurring'] = $this->url->link('account/recurring');

        $this->load->model('account/affiliate');
        $affiliateInfo = $this->model_account_affiliate->getAffiliate($this->customer->getId());
        if (!$affiliateInfo) {
            $data['affiliate'] = $this->url->link('account/affiliate/add');
            $data['tracking'] = '';
        } else {
            $data['affiliate'] = $this->url->link('account/affiliate/edit');
            $data['tracking'] = $this->url->link('account/tracking');
        }

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_left-profile'] = $this->load->controller('common/column_left-profile');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/account', $data));
    }

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
                'zone' => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
                'status' => $countryInfo['status']
            ];
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
