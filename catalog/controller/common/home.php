<?php

class ControllerCommonHome extends Controller
{
    /**
     * View start page
     */
	public function index()
    {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));
        $this->load->language('common/home');

        $data['linkCheckOut'] = $this->url->link('checkout/uni_checkout');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$data['text'] = html_entity_decode($this->config->get('config_main_page_text')[$this->config->get('config_current_country')]['text'], ENT_QUOTES, 'UTF-8');


		$data['subscribe'] = $this->load->controller('extension/module/subscribe');
		$this->response->setOutput($this->load->view('common/home', $data));
	}
}
