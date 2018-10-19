<?php

	class ControllerExtensionModuleExchange extends Controller
	{
		public function index()
		{
		    $this->load->model('extension/module/exchange');
			$this->load->language('extension/module/exchange');
	        $this->document->setTitle($this->language->get('heading_title'));

            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
                $this->model_extension_module_exchange->moduleSettings($this->request->post);
                $this->session->data['success'] = $this->language->get('text_success');

                $this->response->redirect($this->url->link('extension/module/exchange', 'user_token=' . $this->session->data['user_token']));
            }

	        if (isset($this->error['warning'])) {
	            $data['error_warning'] = $this->error['warning'];
	        } else {
	            $data['error_warning'] = '';
	        }
	        $data['breadcrumbs'] = array();

	        $data['breadcrumbs'][] = array(
	            'text' => $this->language->get('text_home'),
	            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
	        );

	        $data['breadcrumbs'][] = array(
	            'text' => $this->language->get('text_extension'),
	            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
	        );

	        $data['breadcrumbs'][] = array(
	            'text' => $this->language->get('heading_title'),
	            'href' => $this->url->link('extension/module/account', 'user_token=' . $this->session->data['user_token'])
	        );

	        $data['action'] = $this->url->link('extension/module/exchange', 'user_token=' . $this->session->data['user_token']);
            $data['refresh_token'] = $this->url->link('extension/module/exchange/refresh', 'user_token=' . $this->session->data['user_token']);
	        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

	        if (isset($this->request->post['module_account_status'])) {
	            $data['module_account_status'] = $this->request->post['module_account_status'];
	        } else {
	            $data['module_account_status'] = $this->config->get('module_account_status');
	        }

	        $data['header'] = $this->load->controller('common/header');
	        $data['column_left'] = $this->load->controller('common/column_left');
	        $data['footer'] = $this->load->controller('common/footer');
	        $data['exchange_status_title'] = $this->language->get('exchange_status_title');
	        $data['exchange_config_title'] = $this->language->get('exchange_config_title');
	        $data['enabled_status'] = $this->language->get('enabled_status');
	        $data['disabled_status'] = $this->language->get('disabled_status');
            $data['disabled_status_description'] = $this->language->get('disabled_status_description');
            $data['token_unavailable'] = $this->language->get('token_unavailable');
            $data['table_executed_title'] = $this->language->get('table_executed_title');
            $data['table_error_title'] = $this->language->get('table_error_title');
            $data['no_error'] = $this->language->get('no_error');
            $data['with_error'] = $this->language->get('with_error');
            $moduleData =  $this->model_extension_module_exchange->getData();
            $data['login'] = $moduleData['login'] ?? '';
            $data['password'] = $moduleData['password'] ?? '';
            $data['ex_token'] = $moduleData['token'] ?? '';
            $data['module_status'] = $this->model_extension_module_exchange->isConfigured();
            if ($data['module_status']) {
                $data['module_stat'] = $this->model_extension_module_exchange->getStatistic();
            }
            $data['module_route'] = $moduleData['route'] ?? '';
            $data['server_url'] = $_SERVER['SERVER_NAME'];
            $data['protocol'] = $_SERVER['HTTPS'] ? 'https://' : 'http://';

	        $this->response->setOutput($this->load->view('extension/module/exchange', $data));
		}

        protected function validate() {

		    return true;
        }

        public function refresh()
        {
            $this->load->model('extension/module/exchange');
            $this->model_extension_module_exchange->saveToken();

            $this->response->redirect($this->url->link('extension/module/exchange', 'user_token=' . $this->session->data['user_token']));
        }
	}