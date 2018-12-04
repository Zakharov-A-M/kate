<?php

class ControllerInformationContact extends Controller
{
	private $error = [];

    /**
     * View page contact
     *
     * @throws Exception
     */
	public function index()
    {
		$this->load->language('information/contact');
		$this->load->model('catalog/information');
		$this->load->model('tool/image');
		$this->document->setTitle($this->language->get('heading_title'));

		if ($this->config->get('config_image')) {
			$data['image'] = $this->model_tool_image->resize($this->config->get('config_image'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_height'));
		} else {
			$data['image'] = false;
		}

		$data['store'] = $this->config->get('config_name')[$this->config->get('config_current_country')];
		$data['address'] = html_entity_decode($this->config->get('config_address')[$this->config->get('config_current_country')], ENT_QUOTES, 'UTF-8');
		$data['geocode'] = $this->config->get('config_geocode')[$this->config->get('config_current_country')];
		$data['geocode_hl'] = $this->config->get('config_language');
		$data['telephone'] = html_entity_decode($this->config->get('config_telephone')[$this->config->get('config_current_country')], ENT_QUOTES, 'UTF-8');
        $data['config_email'] = html_entity_decode($this->config->get('config_email')[$this->config->get('config_current_country')], ENT_QUOTES, 'UTF-8');
		$data['fax'] = $this->config->get('config_fax')[$this->config->get('config_current_country')];
		$data['open'] = html_entity_decode($this->config->get('config_open')[$this->config->get('config_current_country')], ENT_QUOTES, 'UTF-8');
		$data['comment'] = html_entity_decode($this->config->get('config_comment')[$this->config->get('config_current_country')], ENT_QUOTES, 'UTF-8');
		if (empty(trim(strip_tags($data['comment'])))) {
            $data['comment'] = false;
        }

		$data['locations'] = array();

		$this->load->model('localisation/location');

		foreach((array)$this->config->get('config_location') as $location_id) {
			$location_info = $this->model_localisation_location->getLocation($location_id);

			if ($location_info) {
				if ($location_info['image']) {
					$image = $this->model_tool_image->resize($location_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_height'));
				} else {
					$image = false;
				}

				$data['locations'][] = array(
					'location_id' => $location_info['location_id'],
					'name'        => $location_info['name'],
					'address'     => nl2br($location_info['address']),
					'geocode'     => $location_info['geocode'],
					'telephone'   => $location_info['telephone'],
					'fax'         => $location_info['fax'],
					'image'       => $image,
					'open'        => nl2br($location_info['open']),
					'comment'     => $location_info['comment']
				);
			}
		}

		$data['domainLang'] = $this->model_catalog_information->getAddressInfo($this->session->data['country']);

		// Captcha
		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
		} else {
			$data['captcha'] = '';
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['subscribe'] = $this->load->controller('extension/module/subscribe');

		$this->response->setOutput($this->load->view('information/contact', $data));
	}
}
