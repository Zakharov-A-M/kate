<?php

class ControllerDesignMainPageCatalogImageLink extends Controller
{
	private $error = [];
	private $countries = [];

	public function index()
    {
		$this->load->language('design/mainPageCatalog/imageLink');

		$this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        $this->load->model('localisation/country');
        $this->countries = $this->model_localisation_country->getCountriesContact();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            foreach ($this->countries as $country) {
                if (!empty($this->request->post['links'][$country['country_id']])) {
                    foreach ($this->request->post['links'][$country['country_id']] as $key => $value) {
                       $data[$country['country_id']]['image'] = $this->request->post['image'][$country['country_id']]['image'];
                       $data[$country['country_id']]['array'] =  $this->request->post['links'][$country['country_id']];
                    }
                }
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $this->model_setting_setting->editSettingValue(
                'config_main_page_image_link',
                'config_main_page_image_link',
                $data
            );

			$this->response->redirect($this->url->link(
			    'design/mainPageCatalog/imageLink',
                'user_token=' . $this->session->data['user_token']
            ));
		}

		$this->getForm();
	}

    /**
     * View form for banner in the main page catalog
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

		if (isset($this->error['image'])) {
			$data['error_image'] = $this->error['image'];
		} else {
			$data['error_image'] = '';
		}

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link(
			    'design/mainPageCatalog/imageLink',
                'user_token=' . $this->session->data['user_token']
            )
		];

		$data['action'] = $this->url->link(
		    'design/mainPageCatalog/imageLink',
            'user_token=' . $this->session->data['user_token']
        );

		$imageLinkInfo = $this->config->get('config_main_page_image_link');
		$data['user_token'] = $this->session->data['user_token'];
		$data['countries'] = $this->countries;

		$this->load->model('tool/image');

        foreach ($data['countries'] as $country) {
            if (!empty($imageLinkInfo[$country['country_id']]['array'])) {
                foreach ($imageLinkInfo[$country['country_id']]['array'] as $key => $item) {
                    if (is_file(DIR_IMAGE . $item['image'])) {
                        $image = $item['image'];
                        $thumb = $item['image'];
                    } else {
                        $image = '';
                        $thumb = 'no_image.png';
                    }

                    $data['links'][$country['country_id']][$key] = [
                        'title'      => $item['title'],
                        'link'       => $item['link'],
                        'image'      => $image,
                        'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
                        'sort_order' => $item['sort_order']
                    ];
                }

                if (is_file(DIR_IMAGE .$imageLinkInfo[$country['country_id']]['image'])) {
                    $image = $this->model_tool_image->resize($imageLinkInfo[$country['country_id']]['image'], 100, 100);
                    $src = $imageLinkInfo[$country['country_id']]['image'];
                } else {
                    $image = '';
                    $src = '';
                }
                $data['image'][$country['country_id']]['image'] = $image;
                $data['image'][$country['country_id']]['src'] = $src;
            }
		}
        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/mainPageCatalog/image_form', $data));
	}

    /**
     * Validate form banner
     *
     * @return bool
     */
	protected function validateForm()
    {
        foreach ($this->countries as $country) {
		    if (!empty($this->request->post['image'][$country['country_id']])) {
                foreach ($this->request->post['banner'][$country['country_id']] as $key => $value) {
                    if ((utf8_strlen($value['image']) < 2) || (utf8_strlen($value['image']) > 255)) {
                        $this->error['image'][$country['country_id']][$key] = $this->language->get('error_image');
                    }
                }
            }
        }

		return !$this->error;
	}
}
