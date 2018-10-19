<?php

class ControllerRestapiStatusCustomer extends Controller
{
    /**
     * Change price for product
     */
    public function index()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->controller('customer/customer/exchange');
            $json = ['status' => true, 'process' => 'Данные пользователей обновлены'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } else {
            $json = ['status' => false, 'description' => 'Данный введенные не верны!','met'=>$this->request->server['REQUEST_METHOD']];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
        }
    }
}
