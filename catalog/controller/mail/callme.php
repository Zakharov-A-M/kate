<?php
class ControllerMailCallme extends Controller
{
	public function call($array)
	{
		$data['text_greeting'] = sprintf($this->language->get('text_greeting'), html_entity_decode($this->config->get('config_name')[$this->config->get('config_current_country')], ENT_QUOTES, 'UTF-8'));
		$data['text_change'] = $this->language->get('text_change');
		$data['text_ip'] = $this->language->get('text_ip');
		$data['data'] = $array;
		$this->load->model('catalog/information');
		$data['domainLang'] = $this->model_catalog_information->getAddressInfo($this->session->data['country']);
		$data['telephone'] = (isset($data['domainLang']['phone'])?$data['domainLang']['phone']:$this->config->get('config_telephone'));


		$data['ip'] = $this->request->server['REMOTE_ADDR'];
		$data['store_url'] = HTTP_SERVER;
		$data['store'] = html_entity_decode($this->config->get('config_name')[$this->config->get('config_current_country')], ENT_QUOTES, 'UTF-8');

		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($this->config->get('config_email')[$this->config->get('config_current_country')]);
		$mail->setFrom($this->config->get('config_email')[$this->config->get('config_current_country')]);
		$mail->setSender(html_entity_decode($this->config->get('config_name')[$this->config->get('config_current_country')], ENT_QUOTES, 'UTF-8'));
		$mail->setSubject($data['store']. ' - Перезвонить');
		$mail->setHtml($this->load->view('mail/callme', $data));
		$mail->send();

	}
}
