<?php

class ControllerAccountFeedback extends Controller
{
    private $error = [];

    /**
     * validateForm user on site
     */
    public function validateForm()
    {
        $json = [];
        if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
            $json['status'] = true;
        } else {
            $json['error'] = $this->error;
            $json['status'] = false;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Send message call me with site
     */
    public function sendMessage()
    {
        $json = [];
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->controller('mail/feedback/feedback', $this->request->post);
            $json['status'] = true;
        } else {
            $json['status'] = false;
            $json['error'] = $this->error;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Validate form registration
     *
     * @return bool
     */
    private function validate()
    {
        if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) ||
            (utf8_strlen(trim($this->request->post['firstname'])) > 32)
        ) {
            $this->error['firstname'] = 'Поле Имя должно быть заполнено';
        }

        if ((utf8_strlen($this->request->post['email']) > 96) ||
            !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)
        ) {
            $this->error['email'] = 'Поле E-mail должно быть указано верно';
        }

        if ((utf8_strlen(trim($this->request->post['telephone'])) < 1) ||
            (utf8_strlen(trim($this->request->post['telephone'])) > 32)
        ) {
            $this->error['telephone'] = 'Поле Фамилия должно быть заполнено';
        }

        if (utf8_strlen(trim($this->request->post['message'])) < 1) {
            $this->error['message'] = 'Сообщение не было указано';
        }

        return !$this->error;
    }
}
