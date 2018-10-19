<?php

class ControllerMailOrder extends Controller
{
    /**
     * Send message to email if confirmation user from 1C
     *
     * @param $array
     * @throws Exception
     */
    public function implementationOrder($array)
    {
        $this->load->model('customer/customer');
        $this->load->model('sale/order');
        $this->load->model('mail/mail');
        $orderInfo = $this->model_sale_order->getOrderGUID($array['guid']);
        $customerInfo = $this->model_customer_customer->getCustomer($orderInfo['customer_id']);

        if ($customerInfo && $orderInfo) {
            $data['to'] = $customerInfo['email'];
            $this->load->model('setting/store');
            $storeInfo = $this->model_setting_store->getStore($customerInfo['store_id']);
            if ($storeInfo) {
                $data['store'] = html_entity_decode($storeInfo['name'], ENT_QUOTES, 'UTF-8');
                $data['store_url'] = $storeInfo['url'];
            } else {
                $data['store'] = html_entity_decode(
                    $this->config->get('config_name')[$this->config->get('config_country_id')],
                    ENT_QUOTES,
                    'UTF-8'
                );
                $data['store_url'] = HTTP_CATALOG;
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
            $language->load('mail/order_implementation');

            $data['html'] = $this->load->controller(
                'mail/customer/getHtmlForEmail',
                $language,
                $customerInfo['language_id'],
                $data['store_url']
            );
            $data['subject'] = sprintf($language->get('text_subject'), $data['store'], $orderInfo['order_id']);
            $this->load->model('localisation/order_status');
            $orderStatus = $this->model_localisation_order_status->getOrderStatusDescriptions($array['orderStatus']);
            $data['text_order_status'] = sprintf(
                $language->get('text_order_status'),
                $orderInfo['order_id'],
                $orderStatus[$customerInfo['language_id']]['name']
            );

            $data['text_thanks'] = $language->get('text_thanks');
            $data['html'] = $this->load->view('mail/order_implementation', $data);
            $this->model_mail_mail->saveMessageMail($data);
        }
    }

    /**
     * Send message to email if there is an invoice for payment
     *
     * @param $array
     * @throws Exception
     */
    public function sendFile($array)
    {
        if (!empty($array['score'])) {
            $this->load->model('customer/customer');
            $this->load->model('sale/order');
            $this->load->model('mail/mail');
            $customerInfo = $this->model_customer_customer->getCustomer($array['customerId']);
            if ($customerInfo) {
                $this->load->model('setting/store');
                $data['to'] = $customerInfo['email'];
                $storeInfo = $this->model_setting_store->getStore($customerInfo['store_id']);

                if ($storeInfo) {
                    $data['store'] = html_entity_decode($storeInfo['name'], ENT_QUOTES, 'UTF-8');
                    $data['store_url'] = $storeInfo['url'];
                } else {
                    $data['store'] = html_entity_decode(
                        $this->config->get('config_name')[$this->config->get('config_country_id')],
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    $data['store_url'] = HTTP_CATALOG;
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
                $language->load('mail/order_score');
                $data['html'] = $this->load->controller(
                    'mail/customer/getHtmlForEmail',
                    $language,
                    $customerInfo['language_id'],
                    $data['store_url']
                );
                $data['subject'] = sprintf($language->get('text_subject'), $data['store'], $array['orderId']);
                $data['text_order_score'] = sprintf($language->get('text_order_score'), $array['orderId']);
                $data['text_thanks'] = $language->get('text_thanks');

                $data['html'] = $this->load->view('mail/order_send_file', $data);
                $data['file'] = $array['score'];
                $this->model_mail_mail->saveMessageMail($data);
            }
        }
    }
}
