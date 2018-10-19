<?php

class ControllerExtensionModuleSubscribe extends Controller
{
    /**
     * Subscribe users
     *
     * @return string
     */
    public function index()
    {
        $this->load->language('extension/module/subscribe');
        $uniset = $this->config->get('config_unishop2');
        $points = isset($uniset['subscribe_points']) ? $uniset['subscribe_points'] : 0;
        $data['text_subscribe_info'] = $points ?
            sprintf($this->language->get('text_subscribe_info_points'), $points) :
            $this->language->get('text_subscribe_info');
        $data['customer_email'] = $this->customer->getEmail();
        return $this->load->view('extension/module/subscribe', $data);
    }

    /**
     * Added subscribe from user
     *
     * @return bool
     * @throws Exception
     */
    public function add()
    {
        $uniset = $this->config->get('config_unishop2');

        if (!isset($uniset['show_subscribe'])) {
            return false;
        }

        $this->load->language('extension/module/subscribe');
        $this->load->model('account/customer');
        $this->load->model('extension/module/subscribe');

        $customerMail = isset($this->request->post['email']) ?
            htmlspecialchars(strip_tags($this->request->post['email'])) : '';
        $customerPassword = isset($this->request->post['password']) ?
            html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8') :
            '';
        $customerIp = $this->request->server['REMOTE_ADDR'];

        $link = $this->url->link('account/newsletter', '', true);
        $attempts = isset($uniset['subscribe_attempt']) ? $uniset['subscribe_attempt'] : 3;

        $points = isset($uniset['subscribe_points']) ? $uniset['subscribe_points'] : 0;
        $pointsDescription = $this->language->get('text_points_description');

        $json = [];

        if ((utf8_strlen($customerMail) > 96) || !filter_var($customerMail, FILTER_VALIDATE_EMAIL)) {
            $json['error'] = $this->language->get('error_email');
        }

        if (isset($this->request->post['password']) && !$this->customer->login($customerMail, $customerPassword)) {
            $json['error'] = $this->language->get('error_password');
        }

        if (!$this->customer->isLogged() && $this->model_account_customer->getTotalCustomersByEmail($customerMail)) {
            $json['alert'] = true;
        }

        $attemptsInfo = $this->model_extension_module_subscribe->getAttempts($customerIp);

        if ($attemptsInfo && ($attemptsInfo['total'] >= $attempts) &&
            strtotime('-1 hour') < strtotime($attemptsInfo['date_modified'])
        ) {
            $json['error'] = $this->language->get('error_limit');
        }

        $rewardInfo = '';

        if (!$json) {
            if ($this->customer->isLogged()) {
                $customerId = $this->customer->isLogged();

                $customerInfo = $this->model_account_customer->getCustomerByEmail($customerMail);

                if ($customerInfo && $customerInfo['status'] && ($customerId == $customerInfo['customer_id'])) {
                    $this->model_extension_module_subscribe->editSubscribe($customerId, true);

                    $rewardInfo = $this->model_extension_module_subscribe->getRewards(
                        $customerId,
                        $pointsDescription,
                        $points
                    );

                    if ($points && !$rewardInfo) {
                        $json['success'] = sprintf(
                            $this->language->get('success_customer_subscribe_points'),
                            $this->customer->getFirstName(),
                            $points
                        );
                    } else {
                        $json['success'] = sprintf(
                            $this->language->get('success_customer_subscribe'),
                            $this->customer->getFirstName()
                        );
                    }
                } else {
                    $json['error'] = $this->language->get('error_customer_mail');
                }
            } else {
                $chars = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP';
                $passLength = 8;
                $charsLength = utf8_strlen($chars) - 1;
                $pass = '';

                while ($passLength - 1) {
                    $pass .= $chars[rand(0, $charsLength)];
                    $passLength--;
                }

                $data['firstname'] = $customerMail;
                $data['lastname'] = '';
                $data['email'] = $customerMail;
                $data['telephone'] = '';
                $data['password'] = $pass;
                $data['customer_group_id'] = $this->config->get('config_customer_group_id');
                $data['fax'] = '';
                $data['newsletter'] = true;
                $data['company'] = '';
                $data['address_1'] = '';
                $data['address_2'] = '';
                $data['postcode'] = '';
                $data['city'] = '';
                $data['country_id'] = $this->config->get('config_country_id') ?
                    $this->config->get('config_country_id') :
                    0;
                $data['zone_id'] = $this->config->get('config_zone_id') ? $this->config->get('config_zone_id') : 0;

                $customerId = $this->model_account_customer->addCustomer($data);

                $this->load->model('account/activity');

                $activityData = array(
                    'customer_id' => $customerId,
                    'name' => $data['firstname'] . ' ' . $data['lastname']
                );

                $this->model_account_activity->addActivity('register', $activityData);
                $subject = sprintf(
                    $this->language->get('text_subscribe_mail_subject'),
                    $this->config->get('config_name')
                );

                if ($points) {
                    $mailText = sprintf(
                        $this->language->get('text_subscribe_mail_body_points'),
                        $this->config->get('config_name'),
                        $points,
                        $link,
                        $customerMail,
                        $pass
                    );
                } else {
                    $mailText = sprintf(
                        $this->language->get('text_subscribe_mail_body'),
                        $this->config->get('config_name'),
                        $link,
                        $customerMail,
                        $pass
                    );
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

                $mail->setTo($customerMail);
                $mail->setFrom($this->config->get('config_email'));
                $mail->setSender($this->config->get('config_name'));
                $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
                $mail->setHtml(html_entity_decode($mailText, ENT_QUOTES, 'UTF-8'));
                $mail->send();

                if ($points) {
                    $json['success'] = sprintf($this->language->get('success_guest_subscribe_points'), $points);
                } else {
                    $json['success'] = $this->language->get('success_guest_subscribe');
                }
            }

            $json['success_title'] = $this->language->get('success_title');
            if (!isset($json['error']) && !$rewardInfo && $points) {
                $this->model_extension_module_subscribe->addReward($customerId, $pointsDescription, $points);
            }
            $this->model_extension_module_subscribe->addAttempt($customerMail, $customerIp);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
