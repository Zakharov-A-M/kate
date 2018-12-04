<?php

class ControllerAccountDiscount extends Controller
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
        $this->load->model('account/customer');
        $json = [];
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $json['redirect'] = 'sdfsdfsdf';
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

        if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) ||
            (utf8_strlen(trim($this->request->post['lastname'])) > 32)
        ) {
            $this->error['lastname'] = 'Поле Фамилия должно быть заполнено';
        }

        if ((utf8_strlen(trim($this->request->post['telephone'])) < 1) ||
            (utf8_strlen(trim($this->request->post['telephone'])) > 32)
        ) {
            $this->error['telephone'] = 'Поле Фамилия должно быть заполнено';
        }


        return !$this->error;
    }
}
