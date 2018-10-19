<?php

class ControllerMailCustomer extends Controller
{
    /**
     * Send message to email if approve user
     *
     * @param $route
     * @param $args
     * @param $output
     * @throws Exception
     */
	public function approve(&$route, &$args, &$output)
    {
		$this->load->model('customer/customer');

		$customer_info = $this->model_customer_customer->getCustomer($args[0]);

		if ($customer_info) {
			$this->load->model('setting/store');

			$store_info = $this->model_setting_store->getStore($customer_info['store_id']);

			if ($store_info) {
				$store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
				$store_url = $store_info['url'];
			} else {
				$store_name = html_entity_decode($this->config->get('config_name')[$this->config->get('config_country_id')], ENT_QUOTES, 'UTF-8');
				$store_url = HTTP_CATALOG;
			}

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($customer_info['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			$language = new Language($language_code);
			$language->load($language_code);
			$language->load('mail/customer_approve');

			$subject = sprintf($language->get('text_subject'), $store_name);

			$data['text_welcome'] = sprintf($language->get('text_welcome'), $store_name);
			$data['text_login'] = $language->get('text_login');
			$data['text_service'] = $language->get('text_service');
			$data['text_thanks'] = $language->get('text_thanks');

			$data['button_login'] = $language->get('button_login');

			$data['login'] = $store_url . 'index.php?route=account/login';
			$data['store'] = $store_name;
			$data['store_url'] = $store_url;

			$this->load->model('tool/image');

			if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
				$data['logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), $this->config->get('theme_default_image_location_width'), $this->config->get('theme_default_image_cart_height'));
			} else {
				$data['logo'] = '';
			}

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($customer_info['email']);
			$mail->setFrom($this->config->get('config_email')[$this->config->get('config_country_id')]);
			$mail->setSender($store_name);
			$mail->setSubject($subject);
			$mail->setHtml($this->load->view('mail/customer_approve', $data));
			$mail->send();
		}
	}

    /**
     * Send message to email if deny user
     *
     * @param $route
     * @param $args
     * @param $output
     * @throws Exception
     */
	public function deny(&$route, &$args, &$output)
    {
		$this->load->model('customer/customer');

		$customer_info = $this->model_customer_customer->getCustomer($args[0]);

		if ($customer_info) {
			$this->load->model('setting/store');

			$store_info = $this->model_setting_store->getStore($customer_info['store_id']);

			if ($store_info) {
				$store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
				$store_url = $store_info['url'];
			} else {
				$store_name = html_entity_decode($this->config->get('config_name')[$this->config->get('config_country_id')], ENT_QUOTES, 'UTF-8');
				$store_url = HTTP_CATALOG;
			}

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($customer_info['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			$language = new Language($language_code);
			$language->load($language_code);
			$language->load('mail/customer_deny');

			$subject = sprintf($language->get('text_subject'), $store_name);

			$data['text_welcome'] = sprintf($language->get('text_welcome'), $store_name);
			$data['text_denied'] = $language->get('text_denied');
			$data['text_thanks'] = $language->get('text_thanks');

			$data['button_contact'] = $language->get('button_contact');

			$data['contact'] = $store_url . 'index.php?route=information/contact';
			$data['store'] = $store_name;
			$data['store_url'] = $store_url;

			$this->load->model('tool/image');

			if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
				$data['logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), $this->config->get('theme_default_image_location_width'), $this->config->get('theme_default_image_cart_height'));
			} else {
				$data['logo'] = '';
			}

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($customer_info['email']);
			$mail->setFrom($this->config->get('config_email')[$this->config->get('config_country_id')]);
			$mail->setSender($store_name);
			$mail->setSubject($subject);
			$mail->setHtml($this->load->view('mail/customer_deny', $data));
			$mail->send();
		}
	}

    /**
     * Send message to email if confirmation user from 1C
     *
     * @param array $array
     * @throws Exception
     */
    public function confirmation1C(array $array)
    {
        $this->load->model('customer/customer');

        $customerInfo = $this->model_customer_customer->getCustomer($array['customer_id']);
        if ($customerInfo) {
            $this->load->model('setting/store');
            $storeInfo = $this->model_setting_store->getStore($customerInfo['store_id']);

            if ($storeInfo) {
                $storeName = html_entity_decode($storeInfo['name'], ENT_QUOTES, 'UTF-8');
                $storeUrl = $storeInfo['url'];
            } else {
                $storeName = html_entity_decode(
                    $this->config->get('config_name')[$this->config->get('config_country_id')],
                    ENT_QUOTES,
                    'UTF-8'
                );
                $storeUrl = HTTP_CATALOG;
            }

            $this->load->model('localisation/language');
            $languageInfo = $this->model_localisation_language->getLanguage($customerInfo['language_id']);
            if ($languageInfo) {
                $languageCode = $languageInfo['code'];
            } else {
                $languageCode = $this->config->get('config_language');
            }

            $language = new Language($languageCode);
            $language->load($languageCode);
            $language->load('mail/customer_confirm');

            $subject = sprintf($language->get('text_subject'), $storeName);
            $data['html'] = $this->getHtmlForEmail($language, $customerInfo['language_id'], $storeUrl);
            $data['text_welcome'] = sprintf($language->get('text_welcome'), $storeName);
            $data['text_thanks'] = $language->get('text_thanks');
            $data['store'] = $storeName;
            $data['store_url'] = $storeUrl;

            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

            $mail->setTo($customerInfo['email']);
            $mail->setFrom($this->config->get('config_email')[$this->config->get('config_country_id')]);
            $mail->setSender($storeName);
            $mail->setSubject($subject);
            $mail->setHtml($this->load->view('mail/customer_confirm', $data));
            $mail->send();
        }
    }

    /**
     * Send message on email user registered from 1C
     *
     * @param array $array
     * @throws Exception
     * @return void
     */
    public function registeredUser(array $array)
    {
        $this->load->model('customer/customer');
        $customerInfo = $this->model_customer_customer->getCustomer($array['customer_id']);
        if ($customerInfo) {
            $this->load->model('setting/store');

            $storeInfo = $this->model_setting_store->getStore($customerInfo['store_id']);
            if ($storeInfo) {
                $storeName = html_entity_decode($storeInfo['name'], ENT_QUOTES, 'UTF-8');
                $storeUrl = $storeInfo['url'];
            } else {
                $storeName = html_entity_decode(
                    $this->config->get('config_name')[$this->config->get('config_country_id')],
                    ENT_QUOTES,
                    'UTF-8'
                );
                $storeUrl = HTTP_CATALOG;
            }

            $this->load->model('localisation/language');
            $languageInfo = $this->model_localisation_language->getLanguage($customerInfo['language_id']);
            if ($languageInfo) {
                $languageCode = $languageInfo['code'];
            } else {
                $languageCode = $this->config->get('config_language');
            }

            $language = new Language($languageCode);
            $language->load($languageCode);
            $language->load('mail/customer');

            $subject = sprintf($language->get('text_subject'), $storeName, HTTP_DOMAIN);
            $data['text_welcome'] = sprintf(
                $language->get('text_welcome'),
                $storeName,
                $array['email'],
                $array['password']
            );
            $data['html'] = $this->getHtmlForEmail($language, $customerInfo['language_id'], $storeUrl);
            $data['text_thanks'] = $language->get('text_thanks');
            $data['store'] = $storeName;
            $data['store_url'] = $storeUrl;

            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode(
                $this->config->get('config_mail_smtp_password'),
                ENT_QUOTES,
                'UTF-8'
            );
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

            $mail->setTo($customerInfo['email']);
            $mail->setFrom($this->config->get('config_email')[$this->config->get('config_country_id')]);
            $mail->setSender($storeName);
            $mail->setSubject($subject);
            $mail->setHtml($this->load->view('mail/customer_registered', $data));
            $mail->send();
            $this->alert($array, $customerInfo);
        }
    }

    /**
     * Send message on email admin, that user registered
     *
     * @param array $array
     * @param array $customerInfo
     * @throws Exception
     * @return void
     */
    public function alert(array $array, array $customerInfo)
    {
        // Send to main admin email if new account email is enabled
        if (in_array('account', (array)$this->config->get('config_mail_alert'))) {
            $language = new Language('ru-ru');
            $language->load('ru-ru');
            $language->load('mail/customer');

            $data['html'] = $this->getHtmlForEmail($language, 2, HTTP_CATALOG);
            $data['firstname'] = $customerInfo['firstname'];
            $data['lastname'] = $customerInfo['lastname'];
            $data['email'] = $array['email'];
            $data['text_firstname'] = $language->get('text_firstname');
            $data['text_lastname'] = $language->get('text_lastname');
            $data['text_customer_group'] = $language->get('text_customer_group');
            $data['text_new_customer'] = $language->get('text_new_customer');
            $data['text_email'] = $language->get('text_email');
            $data['text_telephone'] = $language->get('text_telephone');
            $data['telephone'] = $customerInfo['telephone'];
            $data['store_url'] = HTTP_SERVER;
            $data['store'] = html_entity_decode(
                $this->config->get('config_name')[$this->config->get('config_country_id')],
                ENT_QUOTES,
                'UTF-8'
            );

            if (isset($customerInfo['customer_group_id'])) {
                $customerGroupId = $customerInfo['customer_group_id'];
            } else {
                $customerGroupId = $this->config->get('config_customer_group_id');
            }

            $this->load->model('customer/customer_group');
            $customerGroupInfo = $this->model_customer_customer_group->getCustomerGroup($customerGroupId);
            if ($customerGroupInfo) {
                $data['customer_group'] = $customerGroupInfo['name'];
            } else {
                $data['customer_group'] = '';
            }

            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode(
                $this->config->get('config_mail_smtp_password'),
                ENT_QUOTES,
                'UTF-8'
            );
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

            $mail->setTo($this->config->get('config_email')[$this->config->get('config_country_id')]);
            $mail->setFrom($this->config->get('config_email')[$this->config->get('config_country_id')]);
            $mail->setSender(html_entity_decode(
                $this->config->get('config_name')[$this->config->get('config_country_id')],
                ENT_QUOTES,
                'UTF-8'
            ));
            $mail->setSubject(html_entity_decode(
                $language->get('text_new_customer'),
                ENT_QUOTES,
                'UTF-8'
            ));
            $mail->setHtml($this->load->view('mail/register_alert', $data));
            $mail->send();
            $emails = explode(',', $this->config->get('config_mail_alert_email'));
            foreach ($emails as $email) {
                if (utf8_strlen($email) > 0 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $mail->setTo($email);
                    $mail->send();
                }
            }
        }
    }

    /**
     * Get html content for email message
     *
     * @param Language $language
     * @param int $languageCustomer
     * @param string $storeUrl
     * @return mixed
     */
    public function getHtmlForEmail(Language $language, int $languageCustomer, string $storeUrl)
    {
        $this->load->model('catalog/information');
        if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
            $data['logo'] = $storeUrl . 'image/prolmlogo.png';
        } else {
            $data['logo'] = '';
        }
        $data['splitter'] = $storeUrl . 'image/catalog/splitter.png';
        $data['domainLang'] = $this->model_catalog_information->getAddressInfo($languageCustomer);
        $data['telephone'] = empty($data['domainLang']['phone']) ?
            $data['domainLang']['phone'] :
            $this->config->get('config_telephone');
        $url = new Url(HTTP_CATALOG, $this->config->get('config_secure') ? HTTP_CATALOG : HTTPS_CATALOG);
        $data['menu'] = [
            'catalog' => [
                'text' => $language->get('text_catalog'),
                'link' => $url->link('product/category'),
            ],
            'payAndDelivary' => [
                'text' => $language->get('text_pay_delivery'),
                'link' => $url->link('information/delivery'),
            ],
            'historyOrder' => [
                'text' => $language->get('text_history_order'),
                'link' => $url->link('account/order'),
            ]
        ];
        $data['menuBelow'] = [
            'termsOfUse' => [
                'text' => $language->get('text_terms_of_use'),
                'link' => $url->link('information/articles/view', '&article_id=' . 5),
            ],
            'contacts' => [
                'text' => $language->get('text_contacts'),
                'link' => $url->link('information/contact'),
            ]
        ];
        return $data;
    }
}
