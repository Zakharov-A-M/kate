<?php

class ControllerRestapiNomenclature extends Controller
{
    /**
     * Update product and category
     */
    public function index()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->controller('catalog/exchange/getUpdateProducts1C');
            $this->load->controller('restapi/amount');
            $json = ['status' => true, 'process' => 'Данные по категориям и товарам изменены'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } else {
            $json = ['status' => false, 'description' => 'Данный введенные не верны!'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
        }
    }
}
