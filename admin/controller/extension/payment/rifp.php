<?php
class ControllerExtensionPaymentRifp extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/rifp');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_rifp', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment'));
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
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/rifp', 'user_token=' . $this->session->data['user_token'])
		);

		$data['action'] = $this->url->link('extension/payment/rifp', 'user_token=' . $this->session->data['user_token']);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

		if (isset($this->request->post['payment_rifp_order_status_id'])) {
			$data['payment_rifp_order_status_id'] = $this->request->post['payment_rifp_order_status_id'];
		} else {
			$data['payment_rifp_order_status_id'] = $this->config->get('payment_rifp_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_rifp_status'])) {
			$data['payment_rifp_status'] = $this->request->post['payment_rifp_status'];
		} else {
			$data['payment_rifp_status'] = $this->config->get('payment_rifp_status');
		}

		if (isset($this->request->post['payment_rifp_sort_order'])) {
			$data['payment_rifp_sort_order'] = $this->request->post['payment_rifp_sort_order'];
		} else {
			$data['payment_rifp_sort_order'] = $this->config->get('payment_rifp_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/rifp', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/rifp')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
