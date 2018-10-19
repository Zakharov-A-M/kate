<?php

class ControllerMailRegister extends Controller
{
    /**
     * Send message on email user registered
     *
     * @param string $route
     * @param array $args
     * @param int $output
     * @throws Exception
     * @return void
     */
    public function index(string &$route, array &$args, int &$output)
    {
        $this->load->language('mail/register');
        $data['text_welcome'] = sprintf(
            $this->language->get('text_welcome'),
            html_entity_decode(
                $this->config->get('config_name')[$this->config->get('config_current_country')],
                ENT_QUOTES,
                'UTF-8'
            )
        );
        $data['html'] = $this->load->controller('mail/order/getHtmlForEmail');
        $this->load->model('account/customer_group');
        if (isset($args[0]['customer_group_id'])) {
            $customerGroupId = $args[0]['customer_group_id'];
        } else {
            $customerGroupId = $this->config->get('config_customer_group_id');
        }

        if (!empty($args[0]['email']) && !empty($args[0]['new_password'])) {
            $data['email'] = $args[0]['email'];
            $data['new_password'] = (!empty($args[0]['new_password'])) ? $args[0]['new_password'] : '';
        } elseif (!empty($args[0]['email']) && !empty($args[0]['password'])) {
            $data['email'] = $args[0]['email'];
            $data['new_password'] = (!empty($args[0]['password'])) ? $args[0]['password'] : '';
        }

        $customerGroupInfo = $this->model_account_customer_group->getCustomerGroup($customerGroupId);
        $data['login'] = $this->url->link('account/login');
        if ($customerGroupInfo) {
            $data['approval'] = $customerGroupInfo['approval'];
            if ($customerGroupInfo['approval']) {
                $data['login'] = $this->url->link('account/login', 'token=' . md5($args[0]['email']));
            }
        } else {
            $data['approval'] = '';
        }

        $data['store_url'] = HTTP_SERVER;
        $data['store'] = html_entity_decode(
            $this->config->get('config_name')[$this->config->get('config_current_country')],
            ENT_QUOTES,
            'UTF-8'
        );

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

        $mail->setTo($args[0]['email']);
        $mail->setFrom($this->config->get('config_email')[$this->config->get('config_current_country')]);
        $mail->setSender(html_entity_decode(
            $this->config->get('config_name')[$this->config->get('config_current_country')],
            ENT_QUOTES,
            'UTF-8'
        ));
        $mail->setSubject(sprintf(
            $this->language->get('text_subject'),
            html_entity_decode(
                $this->config->get('config_name')[$this->config->get('config_current_country')],
                ENT_QUOTES,
                'UTF-8'
            ),
            HTTP_DOMAIN
        ));
        $mail->setHtml($this->load->view('mail/register', $data));
        $mail->send();
    }

    /**
     * Send message on email admin, that user registered
     *
     * @param string $route
     * @param array $args
     * @param int $output
     * @throws Exception
     * @return void
     */
    public function alert(string &$route, array &$args, int &$output)
    {
        // Send to main admin email if new account email is enabled
        if (in_array('account', (array)$this->config->get('config_mail_alert'))) {
            $this->load->language('mail/register');

            $data['html'] = $this->load->controller('mail/order/getHtmlForEmail');
            $data['firstname'] = $args[0]['firstname'];
            $data['lastname'] = $args[0]['lastname'];
            $data['login'] = $this->url->link('account/login');
            $data['email'] = $args[0]['email'];
            $data['telephone'] = $args[0]['telephone'];
            $data['store_url'] = HTTP_SERVER;
            $data['store'] = html_entity_decode(
                $this->config->get('config_name')[$this->config->get('config_current_country')],
                ENT_QUOTES,
                'UTF-8'
            );

            $this->load->model('account/customer_group');
            if (isset($args[0]['customer_group_id'])) {
                $customerGroupId = $args[0]['customer_group_id'];
            } else {
                $customerGroupId = $this->config->get('config_customer_group_id');
            }

            $customerGroupInfo = $this->model_account_customer_group->getCustomerGroup($customerGroupId);

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

            $mail->setTo($this->config->get('config_email')[$this->config->get('config_current_country')]);
            $mail->setFrom($this->config->get('config_email')[$this->config->get('config_current_country')]);
            $mail->setSender(html_entity_decode(
                $this->config->get('config_name')[$this->config->get('config_current_country')],
                ENT_QUOTES,
                'UTF-8'
            ));
            $mail->setSubject(html_entity_decode(
                $this->language->get('text_new_customer'),
                ENT_QUOTES,
                'UTF-8'
            ));
            $mail->setHtml($this->load->view('mail/register_alert', $data));
            $mail->send();

            // Send to additional alert emails if new account email is enabled
            $emails = explode(',', $this->config->get('config_mail_alert_email'));
            foreach ($emails as $email) {
                if (utf8_strlen($email) > 0 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $mail->setTo($email);
                    $mail->send();
                }
            }
        }
    }
}
