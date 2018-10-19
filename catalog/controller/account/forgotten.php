<?php

class ControllerAccountForgotten extends Controller
{
    private $error = [];

    public function index()
    {
        if ($this->customer->isLogged()) {
            $this->response->redirect($this->url->link('account/edit'));
        }

        $this->load->language('account/forgotten');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('account/customer');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_account_customer->editCode($this->request->post['email'], token(40));
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('account/login'));
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
            'text' => $this->language->get('text_forgotten'),
            'href' => $this->url->link('account/forgotten')
        ];

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['email'])) {
            $data['error_email'] = $this->error['email'];
        } else {
            $data['error_email'] = '';
        }

        if (isset($this->request->post['email'])) {
            $data['email'] = $this->request->post['email'];
        } else {
            $data['email'] = '';
        }

        $data['action'] = $this->url->link('account/forgotten');
        $data['back'] = $this->url->link('account/login');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/forgotten', $data));
    }

    protected function validate()
    {
        if (!isset($this->request->post['email'])) {
            $this->error['warning'] = $this->language->get('error_email');
        } elseif (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = $this->language->get('error_email_validate');
        } elseif (!$this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
            $this->error['warning'] = $this->language->get('error_email');
        }

        // Check if customer has been approved.
        $customerInfo = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);
        if ($customerInfo && !$customerInfo['status']) {
            $this->error['warning'] = $this->language->get('error_approved');
        }

        return !$this->error;
    }
}
