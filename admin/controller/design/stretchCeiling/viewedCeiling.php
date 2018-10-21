<?php

class ControllerDesignStretchCeilingViewedCeiling extends Controller
{
	private $error = [];
	private $countries = [];

	public function index()
    {
		$this->load->language('design/stretchCeiling/viewedCeiling');

		$this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        $this->load->model('localisation/country');
        $this->countries = $this->model_localisation_country->getCountriesContact();

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            foreach ($this->countries as $country) {
                if (!empty($this->request->post['banner'][$country['country_id']])) {
                    foreach ($this->request->post['banner'][$country['country_id']] as $key => $value) {
                       $data[$country['country_id']]['status'] = $this->request->post['status'][$country['country_id']]['status'];
                       $data[$country['country_id']]['name'] = $this->request->post['name'][$country['country_id']]['name'];
                       $data[$country['country_id']]['array'] =  $this->request->post['banner'][$country['country_id']];
                    }
                }
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $this->model_setting_setting->editSettingValue(
                'config_viewed_stretch_ceiling',
                'config_viewed_stretch_ceiling',
                $data
            );

			$this->response->redirect($this->url->link(
			    'design/stretchCeiling/viewedCeiling',
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

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_stretchCeiling_page'),
        ];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link(
			    'design/stretchCeiling/viewedCeiling',
                'user_token=' . $this->session->data['user_token']
            )
		];

		$data['action'] = $this->url->link(
		    'design/stretchCeiling/viewedCeiling',
            'user_token=' . $this->session->data['user_token']
        );

		$viewedCeiling = $this->config->get('config_viewed_stretch_ceiling');

		$data['user_token'] = $this->session->data['user_token'];
		$data['countries'] = $this->countries;

		$this->load->model('tool/image');

        foreach ($data['countries'] as $country) {
            if (!empty($viewedCeiling[$country['country_id']]['array'])) {
                foreach ($viewedCeiling[$country['country_id']]['array'] as $key => $item) {
                    if (is_file(DIR_IMAGE . $item['image'])) {
                        $image = $item['image'];
                        $thumb = $item['image'];
                    } else {
                        $image = '';
                        $thumb = 'no_image.png';
                    }

                    $data['banner'][$country['country_id']][$key] = [
                        'title'      => $item['title'],
                        'image'      => $image,
                        'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
                        'sort_order' => $item['sort_order']
                    ];
                }
                $data['status'][$country['country_id']]['status'] = $viewedCeiling[$country['country_id']]['status'];
                $data['name'][$country['country_id']]['name'] = $viewedCeiling[$country['country_id']]['name'];
            }
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/stretchCeiling/viewed_ceiling_form', $data));
	}

}
