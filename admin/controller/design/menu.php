<?php

class ControllerDesignMenu extends Controller
{
	private $error = [];
	private $countries = [];

    /**
     * View points menu
     */
	public function index()
    {
		$this->load->language('design/menu');

		$this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        $this->load->model('localisation/country');
        $this->countries = $this->model_localisation_country->getCountriesContact();

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            foreach ($this->countries as $country) {
                if (!empty($this->request->post['menu'][$country['country_id']])) {
                    foreach ($this->request->post['menu'][$country['country_id']] as $key => $value) {
                       $data[$country['country_id']]['array'] =  $this->request->post['menu'][$country['country_id']];
                    }
                }
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $this->model_setting_setting->editSettingValue('config_menu', 'config_menu', $data);

			$this->response->redirect(
			    $this->url->link(
			        'design/menu',
                'user_token=' . $this->session->data['user_token']
                )
            );
		}

		$this->getForm();
	}

    /**
     * View form for menu in the header
     */
	protected function getForm()
    {
		$data['text_form'] = $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

		$url = '';
		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('design/menu', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['action'] = $this->url->link('design/menu', 'user_token=' . $this->session->data['user_token']);

		$menus = $this->config->get('config_menu');

		$data['user_token'] = $this->session->data['user_token'];
		$data['countries'] = $this->countries;
        foreach ($data['countries'] as $country) {
            if (!empty($menus[$country['country_id']]['array'])) {
                foreach ($menus[$country['country_id']]['array'] as $key => $item) {
                    $data['menus'][$country['country_id']][$key] = [
                        'title'      => $item['title'],
                        'link'       => $item['link'],
                        'sort_order' => $item['sort_order']
                    ];
                }
            }
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/menu_form', $data));
	}
}
