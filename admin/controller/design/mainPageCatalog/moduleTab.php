<?php

class ControllerDesignMainPageCatalogModuleTab extends Controller
{
	private $error = [];

    /**
     * View page tab in main page catalog
     */
	public function index()
    {
        $this->load->language('design/mainPageCatalog/tab');
		$this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        $this->load->model('localisation/country');
		$this->getList();
	}

    /**
     * Added new tab for country
     */
	public function add()
    {
        $this->load->language('design/mainPageCatalog/tab');

		$this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $tabInfo = $this->config->get('config_tab_main_page');
            if (isset($tabInfo[$this->request->get['countryId']]['array'])) {
                $maxId = 0;
                foreach ($tabInfo[$this->request->get['countryId']]['array'] as $item) {
                    if ($item['id'] > $maxId) {
                        $maxId = $item['id'];
                    }
                }
                array_push(
                    $tabInfo[$this->request->get['countryId']]['array'],
                    [
                        'id' => $maxId + 1,
                        'name' => $this->request->post['name'],
                        'text' => $this->request->post['text']
                    ]
                );
            } else {
                $tabInfo[$this->request->get['countryId']]['array'][0] = [
                    'id' => 1,
                    'name' => $this->request->post['name'],
                    'text' => $this->request->post['text']
                ];
            }

            $this->model_setting_setting->editSettingValue(
                'config_tab_main_page',
                'config_tab_main_page',
                $tabInfo
            );

			$this->session->data['success'] = $this->language->get('text_add_success');

			$this->response->redirect(
			    $this->url->link(
			        'design/mainPageCatalog/moduleTab',
                    'user_token=' . $this->session->data['user_token']
                )
            );
		}

		$this->getForm();
	}

    /**
     * Edit tab for country
     */
	public function edit()
    {
        $this->load->language('design/mainPageCatalog/tab');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $tabInfo = $this->config->get('config_tab_main_page');
            if (isset($tabInfo[$this->request->get['countryId']]['array'])) {
                foreach ($tabInfo[$this->request->get['countryId']]['array'] as $key => $item) {
                    if ($item['id'] == $this->request->get['tabId']) {
                        $tabInfo[$this->request->get['countryId']]['array'][$key]['name'] = $this->request->post['name'];
                        $tabInfo[$this->request->get['countryId']]['array'][$key]['text'] = $this->request->post['text'];
                    }
                }
            }
            $this->model_setting_setting->editSettingValue(
                'config_tab_main_page',
                'config_tab_main_page',
                $tabInfo
            );

            $this->session->data['success'] = $this->language->get('text_add_success');

            $this->response->redirect(
                $this->url->link(
                    'design/mainPageCatalog/moduleTab',
                    'user_token=' . $this->session->data['user_token']
                )
            );
        }

        $this->getForm();
	}

    /**
     * Delete tab in countryId
     */
	public function delete()
    {
        $this->load->model('setting/setting');
        $this->load->language('design/mainPageCatalog/tab');
        $json = [];

        if (isset($this->request->get['tabId']) && isset($this->request->get['countryId'])) {
            $tabInfo = $this->config->get('config_tab_main_page');
            if (!empty($tabInfo[$this->request->get['countryId']]['array'])) {
                foreach ($tabInfo[$this->request->get['countryId']]['array'] as $key => $item) {
                    if ($item['id'] == $this->request->get['tabId']) {
                        unset($tabInfo[$this->request->get['countryId']]['array'][$key]);
                        $json['status'] = true;
                        $json['text'] = $this->language->get('success_delete_tab');
                    }
                }
                if (count($tabInfo[$this->request->get['countryId']]['array']) < 5) {
                    $json['link'] = $this->url->link(
                        'design/mainPageCatalog/moduleTab/add',
                        'user_token=' . $this->session->data['user_token'] .
                        '&countryId=' . $this->request->get['countryId']
                    );
                    $json['add'] = true;
                }
                $this->model_setting_setting->editSettingValue(
                    'config_tab_main_page',
                    'config_tab_main_page',
                    $tabInfo
                );
            } else {
                $json['status'] = false;
                $json['text'] = $this->language->get('error_delete_tab');
            }
        } else {
            $json['status'] = false;
            $json['text'] = $this->language->get('error_delete_tab');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
	}

    /**
     * get lint tab panel for main page catalog
     */
	protected function getList()
    {
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
            'href' => $this->url->link('design/mainPageCatalog/moduleTab', 'user_token=' . $this->session->data['user_token'])
        ];

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

		$data['accessAdd'] = [];

		$data['user_token'] = $this->session->data['user_token'];

        $data['countries'] = $this->model_localisation_country->getCountriesContact();
        $tabInfo = $this->config->get('config_tab_main_page');
        foreach ($data['countries'] as $country) {
            $data['accessAdd'][$country['country_id']] = true;
            if (!empty($tabInfo[$country['country_id']]['array'])) {
                if (count($tabInfo[$country['country_id']]['array']) >= 5) {
                    $data['accessAdd'][$country['country_id']] = false;
                }
                foreach ($tabInfo[$country['country_id']]['array'] as $key => $item) {
                    $data['tabs'][$country['country_id']][$key] = [
                        'id' => $item['id'],
                        'name'       => $item['name'],
                        'text'       => $item['text'],
                        'edit'        => $this->url->link(
                            'design/mainPageCatalog/moduleTab/edit',
                            'user_token=' . $this->session->data['user_token'] .
                            '&tabId=' . $item['id'] .
                            '&countryId=' . $country['country_id']
                        ),
                    ];
                }
            }
            $data['add'][$country['country_id']] = $this->url->link(
                'design/mainPageCatalog/moduleTab/add',
                'user_token=' . $this->session->data['user_token'] . '&countryId=' . $country['country_id']
            );
        }

        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/mainPageCatalog/tab_list', $data));
	}

    /**
     * Get form for edited and added tab
     */
	protected function getForm()
    {
		$data['text_form'] = !isset($this->request->get['tabId']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        $this->document->addStyle('view/javascript/codemirror/lib/codemirror.css');
        $this->document->addStyle('view/javascript/codemirror/theme/monokai.css');
        $this->document->addStyle('view/javascript/summernote/summernote.css');

        $this->document->addScript('view/javascript/codemirror/lib/codemirror.js');
        $this->document->addScript('view/javascript/codemirror/lib/xml.js');
        $this->document->addScript('view/javascript/codemirror/lib/formatting.js');
        $this->document->addScript('view/javascript/summernote/summernote.js');
        $this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');
        $this->document->addScript('view/javascript/summernote/opencart.js');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

        if (isset($this->error['text'])) {
            $data['error_text'] = $this->error['text'];
        } else {
            $data['error_text'] = '';
        }

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link(
			    'design/mainPageCatalog/moduleTab',
                'user_token=' . $this->session->data['user_token']
            )
		];

		if (!isset($this->request->get['tabId'])) {
			$data['action'] = $this->url->link(
			    'design/mainPageCatalog/moduleTab/add',
                'user_token=' . $this->session->data['user_token'] .
                '&countryId=' . $this->request->get['countryId']
            );
		} else {
			$data['action'] = $this->url->link(
			    'design/mainPageCatalog/moduleTab/edit',
                'user_token=' . $this->session->data['user_token'] .
                '&countryId=' . $this->request->get['countryId'] .
                '&tabId=' . $this->request->get['tabId']
            );
		}

		$data['cancel'] = $this->url->link(
		    'design/mainPageCatalog/moduleTab',
            'user_token=' . $this->session->data['user_token']
        );

		if (isset($this->request->get['countryId']) && isset($this->request->get['tabId'])) {
            $tabInfo = $this->config->get('config_tab_main_page');
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($tabInfo)) {
		    foreach ($tabInfo[$this->request->get['countryId']]['array'] as $key => $item) {
		        if ($item['id'] == $this->request->get['tabId']) {
                    $data['name'] = $item['name'];
                }
            }
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['text'])) {
			$data['text'] = $this->request->post['text'];
		} elseif (!empty($tabInfo)) {
            foreach ($tabInfo[$this->request->get['countryId']]['array'] as $key => $item) {
                if ($item['id'] == $this->request->get['tabId']) {
                    $data['text'] = $item['text'];
                }
            }
		} else {
			$data['text'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/mainPageCatalog/tab_form', $data));
	}

    /**
     * Validate form tab
     *
     * @return boolean
     */
	protected function validateForm()
    {
		if ((utf8_strlen(trim($this->request->post['name'])) < 3) || (utf8_strlen(trim($this->request->post['name'])) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

        if ((utf8_strlen(trim($this->request->post['text'])) < 3)) {
            $this->error['text'] = $this->language->get('error_text');
        }

		return !$this->error;
	}
}
