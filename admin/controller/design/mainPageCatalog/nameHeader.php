<?php

class ControllerDesignMainPageCatalogNameHeader extends Controller
{
	private $countries = [];

	public function index()
    {
		$this->load->language('design/mainPageCatalog/name_header');

		$this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        $this->load->model('localisation/country');
        $this->countries = $this->model_localisation_country->getCountriesContact();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $data = [];
            foreach ($this->countries as $country) {
               $data[$country['country_id']]['status'] = $this->request->post['status'][$country['country_id']];
               $data[$country['country_id']]['name'] =  $this->request->post['name'][$country['country_id']];
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $this->model_setting_setting->editSettingValue(
                'config_header_name_catalog',
                'config_header_name_catalog',
                $data
            );
			$this->response->redirect(
			    $this->url->link(
			        'design/mainPageCatalog/nameHeader',
                    'user_token=' . $this->session->data['user_token']
                )
            );
		}

		$this->getForm();
	}

    /**
     * View form for banner in the main page catalog
     */
	protected function getForm()
    {
		$data['text_form'] = $this->language->get('text_edit');

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
            'text' => $this->language->get('text_edit_main_page'),
            'href' => ''
        ];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link(
			    'design/mainPageCatalog/nameHeader',
                'user_token=' . $this->session->data['user_token'] . $url
            )
		];

		$data['action'] = $this->url->link(
		    'design/mainPageCatalog/nameHeader',
            'user_token=' . $this->session->data['user_token']
        );

		$nameInfo = $this->config->get('config_header_name_catalog');

		$data['user_token'] = $this->session->data['user_token'];

		$data['countries'] = $this->countries;

		$this->load->model('tool/image');

        foreach ($data['countries'] as $country) {
            if (!empty($nameInfo[$country['country_id']])) {
                foreach ($nameInfo[$country['country_id']] as $key => $item) {
                    $data['name'][$country['country_id']] = $nameInfo[$country['country_id']]['name'] ?? '';
                    $data['status'][$country['country_id']] = $nameInfo[$country['country_id']]['status'] ?? 0;
                }
            }
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/mainPageCatalog/name_header', $data));
	}
}
