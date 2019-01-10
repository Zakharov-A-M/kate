<?php

class ControllerDesignLiquidWallpaperText extends Controller
{
	private $error = [];
	private $countries = [];

	public function index()
    {
		$this->load->language('design/mainPageCatalog/text');

		$this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        $this->load->model('localisation/country');
        $this->countries = $this->model_localisation_country->getCountriesContact();

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            foreach ($this->countries as $country) {
                if (!empty($this->request->post['text'][$country['country_id']])) {
                    foreach ($this->request->post['text'][$country['country_id']] as $key => $value) {
                       $data[$country['country_id']]['text'] = $this->request->post['text'][$country['country_id']]['text'];
                    }
                }
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $this->model_setting_setting->editSettingValue(
                'config_liquid_wallpaper_text',
                'config_liquid_wallpaper_text',
                $data
            );

			$this->response->redirect($this->url->link(
			    'design/liquidWallpaper/text',
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
        $this->document->addStyle('view/javascript/codemirror/lib/codemirror.css');
        $this->document->addStyle('view/javascript/codemirror/theme/monokai.css');
        $this->document->addStyle('view/javascript/summernote/summernote.css');

        $this->document->addScript('view/javascript/codemirror/lib/codemirror.js');
        $this->document->addScript('view/javascript/codemirror/lib/xml.js');
        $this->document->addScript('view/javascript/codemirror/lib/formatting.js');
        $this->document->addScript('view/javascript/summernote/summernote.js');
        $this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');
        $this->document->addScript('view/javascript/summernote/opencart.js');

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
			    'design/stretchCeiling/text',
                'user_token=' . $this->session->data['user_token']
            )
		];

		$data['action'] = $this->url->link(
		    'design/liquidWallpaper/text',
            'user_token=' . $this->session->data['user_token']
        );

		$text = $this->config->get('config_liquid_wallpaper_text');
		$data['user_token'] = $this->session->data['user_token'];
		$data['countries'] = $this->countries;

		$this->load->model('tool/image');

        foreach ($data['countries'] as $country) {
            if (!empty($text[$country['country_id']]['text'])) {
                $data['text'][$country['country_id']]['text'] = $text[$country['country_id']]['text'];
            }
		}
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/mainPageCatalog/text_form', $data));
	}
}
