<?php

class ControllerMailOrder extends Controller
{
    public function index(&$route, &$args)
    {
        if (isset($args[0])) {
            $order_id = $args[0];
        } else {
            $order_id = 0;
        }

        if (isset($args[1])) {
            $order_status_id = $args[1];
        } else {
            $order_status_id = 0;
        }

        if (isset($args[2])) {
            $comment = $args[2];
        } else {
            $comment = '';
        }

        if (isset($args[3])) {
            $notify = $args[3];
        } else {
            $notify = '';
        }

        // We need to grab the old order status ID
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ($order_info) {
            // If order status is 0 then becomes greater than 0 send main html email
            if (!$order_info['order_status_id'] && $order_status_id) {
                $this->add($order_info, $order_status_id, $comment, $notify);
            }

            // If order status is not 0 then send update text email
            if ($order_info['order_status_id'] && $order_status_id && $notify) {
                $this->edit($order_info, $order_status_id, $comment, $notify);
            }
        }
    }

    /**
     * Send message about order on the email customer
     *
     * @param array $orderInfo
     * @param int $orderStatusId
     * @param string $comment
     * @param string $notify
     * @throws Exception
     */
    public function add(array $orderInfo, int $orderStatusId, string $comment, string $notify)
    {
        $orderProducts = $this->model_checkout_order->getOrderProducts($orderInfo['order_id']);
        $this->load->language('mail/order_add');
        // HTML Mail

        $data['html'] = $this->getHtmlForEmail();
        $data['store_name'] = $orderInfo['store_name'];
        $data['store_url'] = $orderInfo['store_url'];
        $data['customer_id'] = $orderInfo['customer_id'];
        $data['link'] = $orderInfo['store_url'] .
            'index.php?route=account/order/info&order_id=' .
            $orderInfo['order_id'];
        $data['order_id'] = $orderInfo['order_id'];
        $data['date_added'] = date($this->language->get('date_format_short'), strtotime($orderInfo['date_added']));
        $data['payment_method'] = $orderInfo['payment_method'];
        $data['shipping_method'] = $orderInfo['shipping_method'];
        $data['email'] = $orderInfo['email'];
        $data['telephone'] = $orderInfo['telephone'];
        $data['ip'] = $orderInfo['ip'];
        $data['comment'] = strip_tags($orderInfo['comment']);

        $orderStatusQuery = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "order_status
            WHERE order_status_id = '" . (int)$orderStatusId . "' AND
            language_id = '" . (int)$orderInfo['language_id'] . "'"
        );

        if ($orderStatusQuery->num_rows) {
            $data['order_status'] = $orderStatusQuery->row['name'];
        } else {
            $data['order_status'] = '';
        }

        if ($orderInfo['shipping_address_1']) {
            $format = '{firstname} {lastname}' . "\n" . '{address_1}';
            $find = ['{firstname}', '{lastname}', '{address_1}'];
            $replace = [
                'firstname' => $orderInfo['shipping_firstname'],
                'lastname' => $orderInfo['shipping_lastname'],
                'address' => $orderInfo['shipping_address_1'],
            ];

            $data['shipping_address'] = str_replace(
                ["\r\n", "\r", "\n"],
                '<br />',
                preg_replace(
                    ["/\s\s+/", "/\r\r+/", "/\n\n+/"],
                    '<br />',
                    trim(str_replace($find, $replace, $format))
                )
            );
        }
        $this->load->model('tool/upload');

        // Products
        $data['products'] = [];
        foreach ($orderProducts as $orderProduct) {
            $data['products'][] = [
                'name' => $orderProduct['name'],
                'model' => $orderProduct['model'],
                'quantity' => $orderProduct['quantity'],
                'price' => $this->currency->format(
                    $this->currency->convert(
                        $orderProduct['price'],
                        $orderInfo['currency_code'],
                        $this->session->data['currency']
                    ),
                    $this->session->data['currency'],
                    true
                ),
                'total' => $this->currency->format(
                    $this->currency->convert(
                        $orderProduct['price'] * $orderProduct['quantity'],
                        $orderInfo['currency_code'],
                        $this->session->data['currency']
                    ),
                    $this->session->data['currency'],
                    true
                ),
            ];
        }

        // Order Totals
        $data['totals'] = [];
        $orderTotals = $this->model_checkout_order->getOrderTotals($orderInfo['order_id']);
        foreach ($orderTotals as $orderTotal) {
            $data['totals'][] = [
                'title' => $orderTotal['title'],
                'text' => $this->currency->format(
                    $this->currency->convert(
                        $orderTotal['value'],
                        $orderInfo['currency_code'],
                        $this->session->data['currency']
                    ),
                    $this->session->data['currency'],
                    true
                ),
            ];
        }

        $this->load->model('setting/setting');
        $from = json_decode(
            $this->model_setting_setting->getSettingValue('config_email', $orderInfo['store_id']),
            true
        )[$this->config->get('config_current_country')];

        if (!$from) {
            $from = $this->config->get('config_email')[$this->config->get('config_current_country')];
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

        $mail->setTo($orderInfo['email']);
        $mail->setFrom($from);
        $mail->setSender(html_entity_decode($orderInfo['store_name'], ENT_QUOTES, 'UTF-8'));
        $mail->setSubject(html_entity_decode(
            sprintf(
                $this->language->get('text_subject'),
                $orderInfo['store_name'],
                $orderInfo['order_id']
            ),
            ENT_QUOTES,
            'UTF-8'
        ));
        $mail->setHtml($this->load->view('mail/order_add', $data));
        $mail->send();
    }

	public function edit($order_info, $order_status_id, $comment) {
		$language = new Language($order_info['language_code']);
		$language->load($order_info['language_code']);
		$language->load('mail/order_edit');

		$data['text_order_id'] = $language->get('text_order_id');
		$data['text_date_added'] = $language->get('text_date_added');
		$data['text_order_status'] = $language->get('text_order_status');
		$data['text_link'] = $language->get('text_link');
		$data['text_comment'] = $language->get('text_comment');
		$data['text_footer'] = $language->get('text_footer');

		$data['order_id'] = $order_info['order_id'];
		$data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
		$data['store_url'] = HTTP_SERVER;
		$data['store'] = html_entity_decode($this->config->get('config_name')[$this->config->get('config_current_country')], ENT_QUOTES, 'UTF-8');

		$this->load->model('tool/image');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), $this->config->get('theme_default_image_location_width'), $this->config->get('theme_default_image_cart_height'));
		} else {
			$data['logo'] = '';
		}

		$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");

		if ($order_status_query->num_rows) {
			$data['order_status'] = $order_status_query->row['name'];
		} else {
			$data['order_status'] = '';
		}

		if ($order_info['customer_id']) {
			$data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_info['order_id'];
		} else {
			$data['link'] = '';
		}

		$data['comment'] = strip_tags($comment);

		$this->load->model('setting/setting');

        $from = json_decode($this->model_setting_setting->getSettingValue('config_email', $order_info['store_id']), true)[$this->config->get('config_current_country')];

		if (!$from) {
			$from = $this->config->get('config_email')[$this->config->get('config_current_country')];
		}

		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($order_info['email']);
		$mail->setFrom($from);
		$mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode(sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']), ENT_QUOTES, 'UTF-8'));
		$mail->setHtml($this->load->view('mail/order_edit', $data));
		$mail->send();
	}

    /**
     * @param string $route
     * @param array $args
     * @throws Exception
     */
    public function alert(string &$route, array &$args)
    {
        if (isset($args[0])) {
            $orderId = $args[0];
        } else {
            $orderId = 0;
        }
        if (isset($args[1])) {
            $orderStatusId = $args[1];
        } else {
            $orderStatusId = 0;
        }

        $orderInfo = $this->model_checkout_order->getOrder($orderId);
        if ($orderInfo &&
            !$orderInfo['order_status_id'] &&
            $orderStatusId &&
            in_array('order', (array)$this->config->get('config_mail_alert'))
        ) {
            $this->load->language('mail/order_alert');
            $data['html'] = $this->getHtmlForEmail();
            $data['order_id'] = $orderInfo['order_id'];
            $data['date_added'] = date($this->language->get('date_format_short'), strtotime($orderInfo['date_added']));
            $orderStatusQuery = $this->db->query("
                SELECT * FROM " . DB_PREFIX . "order_status
                WHERE order_status_id = '" . (int)$orderStatusId . "' AND
                 language_id = '" . (int)$this->config->get('config_language_id') . "'"
            );

            if ($orderStatusQuery->num_rows) {
                $data['order_status'] = $orderStatusQuery->row['name'];
            } else {
                $data['order_status'] = '';
            }
            $data['store_url'] = HTTP_SERVER;
            $data['store'] = html_entity_decode(
                $this->config->get('config_name')[$this->config->get('config_current_country')],
                ENT_QUOTES,
                'UTF-8'
            );
            $this->load->model('tool/upload');
            $data['products'] = [];
            $orderProducts = $this->model_checkout_order->getOrderProducts($orderId);
            foreach ($orderProducts as $orderProduct) {
                $data['products'][] = [
                    'name' => $orderProduct['name'],
                    'model' => $orderProduct['model'],
                    'quantity' => $orderProduct['quantity'],
                    'price' => $this->currency->format(
                        $this->currency->convert(
                            $orderProduct['price'],
                            $orderInfo['currency_code'],
                            $this->session->data['currency']
                        ),
                        $this->session->data['currency'],
                        true
                    ),
                    'total' => $this->currency->format(
                        $this->currency->convert(
                            $orderProduct['price'] * $orderProduct['quantity'],
                            $orderInfo['currency_code'],
                            $this->session->data['currency']
                        ),
                        $this->session->data['currency'],
                        true
                    ),
                ];
            }

            $data['totals'] = [];
            $orderTotals = $this->model_checkout_order->getOrderTotals($orderId);
            foreach ($orderTotals as $orderTotal) {
                $data['totals'][] = [
                    'title' => $orderTotal['title'],
                    'value' => html_entity_decode(
                        $this->currency->format(
                            $this->currency->convert(
                                $orderTotal['value'],
                                $orderInfo['currency_code'],
                                $this->session->data['currency']
                            ),
                            $this->session->data['currency'],
                            true
                        ),
                        ENT_NOQUOTES,
                        'UTF-8'
                    )
                ];
            }

            $data['comment'] = strip_tags($orderInfo['comment']);
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

            $mail->setTo($this->config->get('config_email')[$this->config->get('config_current_country')]);
            $mail->setFrom($this->config->get('config_email')[$this->config->get('config_current_country')]);
            $mail->setSender(html_entity_decode($orderInfo['store_name'], ENT_QUOTES, 'UTF-8'));
            $mail->setSubject(html_entity_decode(
                sprintf(
                    $this->language->get('text_subject'),
                    $this->config->get('config_name')[$this->config->get('config_current_country')],
                    $orderInfo['order_id']
                ), ENT_QUOTES,
                'UTF-8'
            ));
            $mail->setHtml($this->load->view('mail/order_alert', $data));
            $mail->send();
            $emails = explode(',', $this->config->get('config_mail_alert_email'));
            foreach ($emails as $email) {
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $mail->setTo($email);
                    $mail->send();
                }
            }
        }
    }

    /**
     * Get html content for email message
     *
     * @return mixed
     */
    public function getHtmlForEmail()
    {
        $this->load->model('catalog/information');
        if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
            $data['logo'] = $this->config->get('config_url') . 'image/prolmlogo.png';
        } else {
            $data['logo'] = '';
        }
        $data['splitter'] = $this->config->get('config_url') . 'image/catalog/splitter.png';
        $data['domainLang'] = $this->model_catalog_information->getAddressInfo($this->session->data['country']);
        $data['telephone'] = empty($data['domainLang']['phone']) ?
            $data['domainLang']['phone'] :
            $this->config->get('config_telephone');
        $data['menu'] = [
            'catalog' => [
                'text' => $this->language->get('text_catalog'),
                'link' => $this->url->link('product/category'),
            ],
            'payAndDelivary' => [
                'text' => $this->language->get('text_pay_delivery'),
                'link' => $this->url->link('information/delivery'),
            ],
            'historyOrder' => [
                'text' => $this->language->get('text_history_order'),
                'link' => $this->url->link('account/order'),
            ]
        ];
        $data['menuBelow'] = [
            'termsOfUse' => [
                'text' => $this->language->get('text_terms_of_use'),
                'link' => $this->url->link('information/articles/view', '&article_id=' . 5),
            ],
            'contacts' => [
                'text' => $this->language->get('text_contacts'),
                'link' => $this->url->link('information/contact'),
            ]
        ];
        return $data;
    }

}
