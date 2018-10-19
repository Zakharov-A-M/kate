<?php

class ControllerRestapiAmount extends Controller
{
    /**
     * Change amount for product in stockroom
     */
    public function index()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->controller('stockroom/exchange/getStockroom');
            $json = ['status' => true, 'process' => 'Данные по складам изменены'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } else {
            $json = ['status' => false, 'description' => 'Данный введенные не верны!'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
        }
    }
}
