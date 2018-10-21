<?php

class ControllerDesignLiquidWallpaperPhoto extends Controller
{
	private $error = [];
	private $countries = [];

	public function index()
    {
		$this->load->language('design/liquidWallpaper/photo');

		$this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        $this->load->model('localisation/country');
        $this->countries = $this->model_localisation_country->getCountriesContact();

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            foreach ($this->countries as $country) {
                if (!empty($this->request->post['banner'][$country['country_id']])) {
                    foreach ($this->request->post['banner'][$country['country_id']] as $key => $value) {
                       $data[$country['country_id']] = $this->request->post['status'][$country['country_id']];
                       $data[$country['country_id']]['array'] =  $this->request->post['banner'][$country['country_id']];
                    }
                }
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $this->model_setting_setting->editSettingValue(
                'config_photo_liquid_wallpaper',
                'config_photo_liquid_wallpaper',
                $data
            );

			$this->response->redirect($this->url->link(
			    'design/liquidWallpaper/photo',
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

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_liquidWallpaper_page'),
        ];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link(
			    'design/liquidWallpaper/photo',
                'user_token=' . $this->session->data['user_token']
            )
		];

		$data['action'] = $this->url->link(
		    'design/liquidWallpaper/photo',
            'user_token=' . $this->session->data['user_token']
        );

		$banner_info = $this->config->get('config_photo_liquid_wallpaper');

		$data['user_token'] = $this->session->data['user_token'];
		$data['countries'] = $this->countries;

		$this->load->model('tool/image');

        foreach ($data['countries'] as $country) {
            if (!empty($banner_info[$country['country_id']]['array'])) {
                foreach ($banner_info[$country['country_id']]['array'] as $key => $item) {
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
                $data['status'][$country['country_id']]['status'] = $banner_info[$country['country_id']]['status'];
            }
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/liquidWallpaper/photo_form', $data));
	}

}
