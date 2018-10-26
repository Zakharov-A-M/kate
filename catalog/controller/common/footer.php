<?php

class ControllerCommonFooter extends Controller
{
	public function index()
    {
		$this->load->language('common/footer');
        $this->load->language('common/menu');

		$this->load->model('catalog/information');

		$data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}

        $data['catalog'] = $this->url->link('product/category');
        $data['about'] = $this->url->link('information/about');
        $data['how_order'] = $this->url->link('information/howorder');
        $data['how_pay'] = $this->url->link('information/howpay');
        $data['delivery'] = $this->url->link('information/delivery');
        $data['articles'] = $this->url->link('catalog/articles');
        $data['news'] = $this->url->link('information/news');
        $data['contact'] = $this->url->link('information/contact');
        $data['sales'] = $this->url->link('information/sales');

		$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name')[$this->config->get('config_country_id')], date('Y', time()));

		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}

        $this->load->model('stockroom/stockroom');
       // $data['stockroom'] = $this->model_stockroom_stockroom->getStockroomForCountryAll();
        $data['stockroom'] = $this->model_catalog_information->getAddressInfo($this->session->data['country']);

		$data['scripts'] = $this->document->getScripts('footer');
		
		return $this->load->view('common/footer', $data);
	}
}
