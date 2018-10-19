<?php
class ControllerExtensionCurrencyFixer extends Controller
{
    CONST API_KEY = '25b398203551c0e0f8fe6d21d29097d3';
	private $error = array();

	public function index() {
		$this->load->language('extension/currency/fixer');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('currency_fixer', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=currency'));
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
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=captcha')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/currency/fixer', 'user_token=' . $this->session->data['user_token'])
		);

		$data['action'] = $this->url->link('extension/currency/fixer', 'user_token=' . $this->session->data['user_token']);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=currency');

		if (isset($this->request->post['currency_fixer_status'])) {
			$data['currency_fixer_status'] = $this->request->post['currency_fixer_status'];
		} else {
			$data['currency_fixer_status'] = $this->config->get('currency_fixer_status');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/currency/fixer', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/currency/fixer')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function currency($default = '') {
		if ($this->config->get('currency_fixer_status')) {
			$currencies = array();

			$params = [
			    'get'   => 'rates',
                'pairs' => '',
                'key'   => self::API_KEY
            ];

			$this->load->model('localisation/currency');

			$results = $this->model_localisation_currency->getCurrencies();

			foreach ($results as $result) {
				if (($result['code'] != $default)) {
					$currencies[] = $result;
				}
			}

			if ($currencies) {
                foreach ($currencies as $currency) {
                    $params['pairs'] = $default.$currency['code'];

                    $arrContextOptions=array(
                        "ssl"=>array(
                            "verify_peer"=>false,
                            "verify_peer_name"=>false,
                        ),
                    );

                    $url = 'https://currate.ru/api/?' . http_build_query($params);

                    $response = file_get_contents($url, false, stream_context_create($arrContextOptions));
                    $response_info = json_decode($response, true);

                    if (isset($response_info['data']) && $response_info['status'] == 200) {
                        if (isset($response_info['data'][$default.$currency['code']])) {
                            $this->model_localisation_currency->editValueByCode($currency['code'], $response_info['data'][$default.$currency['code']]);
                        }
                    }
                }

				$this->cache->delete('currency');
			}

			$this->model_localisation_currency->editValueByCode($default, '1.00000');
		}
	}
}
