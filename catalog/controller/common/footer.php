<?php

class ControllerCommonFooter extends Controller
{
	public function index()
    {
		$this->load->language('common/footer');
        $this->load->language('common/menu');
		$this->load->model('catalog/information');
		$data['name'] = $this->config->get('config_name')[$this->config->get('config_current_country')];
		$data['domainLang'] = $this->model_catalog_information->getAddressInfo($this->session->data['country']);
		$data['telephone'] = (isset($data['domainLang']['phone'])?$data['domainLang']['phone']:$this->config->get('config_telephone'));
		$data['open'] = html_entity_decode($this->config->get('config_open')[$this->config->get('config_current_country')], ENT_QUOTES, 'UTF-8');
		$data['address2'] = html_entity_decode($data['domainLang']['address2'], ENT_QUOTES, 'UTF-8');
		$data['scripts'] = $this->document->getScripts('footer');
        $data['subscribe'] = $this->load->controller('extension/module/subscribe');
		return $this->load->view('common/footer', $data);
	}
}
