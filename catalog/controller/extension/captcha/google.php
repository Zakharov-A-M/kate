<?php
class ControllerExtensionCaptchaGoogle extends Controller {
    public function index($error = array()) {
        $this->load->language('extension/captcha/google');

        if (isset($error['captcha'])) {
			$data['error_captcha'] = $error['captcha'];
		} else {
			$data['error_captcha'] = '';
		}

		$data['site_key'] = $this->config->get('captcha_google_key');

        $data['route'] = (string)$this->request->get['route'];

		return $this->load->view('extension/captcha/google', $data);
    }

    /**
     * Validate captcha
     *
     * @return string
     */
    public function validate()
    {
        $this->load->language('extension/captcha/google');

        if (!isset($this->request->post['g-recaptcha-response'])) {
            return $this->language->get('error_captcha');
        }

        $recaptcha = file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify?secret=' .
            urlencode($this->config->get('captcha_google_secret')) .
            '&response=' . $this->request->post['g-recaptcha-response'] .
            '&remoteip=' . $this->request->server['REMOTE_ADDR']
        );

        $recaptcha = json_decode($recaptcha, true);

        if (!$recaptcha['success']) {
            return $this->language->get('error_captcha');
        }

        return false;
    }
}
