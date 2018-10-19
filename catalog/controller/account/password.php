<?php

class ControllerAccountPassword extends Controller
{
    private $error = [];

    /**
     * Viewed page change password
     */
    public function index()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/password');
            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->language('account/password');
        $this->document->setTitle($this->language->get('heading_title'));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->model('account/customer');
            $this->model_account_customer->editPassword($this->customer->getEmail(), $this->request->post['password']);
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

        if (isset($this->error['current_password'])) {
            $data['error_current_password'] = $this->error['current_password'];
        } else {
            $data['error_current_password'] = '';
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

        $data['action'] = $this->url->link('account/password');

        if (isset($this->request->post['current-password'])) {
            $data['current_password'] = $this->request->post['current-password'];
        } else {
            $data['current_password'] = '';
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

        $data['back'] = $this->url->link('account/edit');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left_profile'] = $this->load->controller('account/column_left_profile');

        $this->response->setOutput($this->load->view('account/password', $data));
    }

    /**
     * Validate change password
     *
     * @return bool
     */
    protected function validate()
    {
        $this->load->model('account/customer');
        if ((utf8_strlen(
                    html_entity_decode(
                        $this->request->post['current-password'],
                        ENT_QUOTES,
                        'UTF-8'
                    )
                ) < 4) ||
            (utf8_strlen(
                    html_entity_decode(
                        $this->request->post['current-password'],
                        ENT_QUOTES,
                        'UTF-8'
                    )
                ) > 40)
        ) {
            $this->error['current_password'] = $this->language->get('error_current_password_empty');
        } elseif (!$this->model_account_customer->checkCurrentPassword($this->request->post['current-password'])) {
            $this->error['current_password'] = $this->language->get('error_current_password_not_same');
        }

        if ((utf8_strlen(
                    html_entity_decode(
                        $this->request->post['password'],
                        ENT_QUOTES,
                        'UTF-8'
                    )) < 4) ||
            (utf8_strlen(
                    html_entity_decode(
                        $this->request->post['password'],
                        ENT_QUOTES,
                        'UTF-8'
                    )) > 40)
        ) {
            $this->error['password'] = $this->language->get('error_password');
        }

        if ((utf8_strlen(
                    html_entity_decode(
                        $this->request->post['confirm'],
                        ENT_QUOTES,
                        'UTF-8'
                    )) < 4) ||
            (utf8_strlen(
                    html_entity_decode(
                        $this->request->post['confirm'],
                        ENT_QUOTES,
                        'UTF-8'
                    )) > 40)
        ) {
            $this->error['confirm'] = $this->language->get('error_confirm_length');
        } elseif ($this->request->post['confirm'] != $this->request->post['password']) {
            $this->error['confirm'] = $this->language->get('error_confirm');
        }

        return !$this->error;
    }
}
