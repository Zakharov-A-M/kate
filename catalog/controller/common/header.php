<?php

class ControllerCommonHeader extends Controller
{

    /**
     * View header
     *
     * @return string
     */
	public function index()
    {
		// Analytics
		$this->load->model('setting/extension');

		$data['analytics'] = array();

		$analytics = $this->model_setting_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
			}
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($this->config->get('config_url') . 'image/' . $this->config->get('config_icon'), 'icon');
		}

        $this->load->model('catalog/information');

		$data['title'] = $this->document->getTitle();

		$data['base'] = $this->config->get('config_url');
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts('header');
		$data['lang'] = $this->language->get('code');
		$data[ 'direction'] = $this->language->get('direction');

		$data['name'] = $this->config->get('config_name')[$this->config->get('config_current_country')];
		$data['open'] = html_entity_decode($this->config->get('config_open')[$this->config->get('config_current_country')], ENT_QUOTES, 'UTF-8');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $this->config->get('config_url') . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

        $nameInfo = $this->config->get('config_header_name_catalog');
        if (!empty(trim($nameInfo[$this->config->get('config_current_country')]['name'])) &&
            ($nameInfo[$this->config->get('config_current_country')]['status'])
        ) {
            $data['nameBelowLogo'] = $nameInfo[$this->config->get('config_current_country')]['name'];
        }


		$this->load->language('common/header');

		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account'), $this->customer->getFirstName(), $this->url->link('account/logout'));
		
		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/wishlist');
		$data['logged'] = $this->customer->isLogged();
		$data['account'] = $this->url->link('account/edit');
		$data['register'] = $this->url->link('account/register');
		$data['login'] = $this->url->link('account/login');
		$data['order'] = $this->url->link('account/order');
		$data['transaction'] = $this->url->link('account/transaction');
		$data['download'] = $this->url->link('account/download');
		$data['logout'] = $this->url->link('account/logout');
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/uni_checkout');
		$data['contact'] = $this->url->link('information/contact');


        $data['domainLang'] = $this->model_catalog_information->getAddressInfo($this->session->data['country']);
		$data['telephone'] = (isset($data['domainLang']['phone'])?$data['domainLang']['phone']:$this->config->get('config_telephone'));
		$data['address2'] = html_entity_decode($data['domainLang']['address2'], ENT_QUOTES, 'UTF-8');

		$data['language'] = $this->load->controller('common/country');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');
		$data['cart'] = $this->load->controller('common/cart');
		$data['menu'] = $this->load->controller('common/menu');
		$data['menu_mobile'] = $this->load->controller('common/menu/getMenuMobile');
		$data['time'] = microtime();

        if ($this->customer->getId() && !$this->customer->getRegistred() && !isset($this->request->cookie['modal'])) {
            if (isset($this->request->get['route']) && ($this->request->get['route'] != 'account/edit')) {
                $data['partner'] = true;
                $data['personal_area'] = $this->url->link('account/edit');
            }
        }
        if((isset($this->request->get['route']))&&($this->request->get['route'] == 'checkout/cart')&&(!$this->customer->getId())){
            $data['partner'] = true;
            $data['personal_area'] = $this->url->link('account/edit');
        }

		return $this->load->view('common/header', $data);
	}

    /**
     * Setcookie from modal
     */
	public function setcookie()
    {
        setcookie('modal', 1, time() + 60 * 60, '/', $this->request->server['HTTP_HOST']);
    }
}
