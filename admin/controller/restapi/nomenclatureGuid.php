<?php

class ControllerRestapiNomenclatureGuid extends Controller
{
    /**
     * Update product and category
     */
    public function index()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' &&  $this->request->post['guid']) {
            $this->load->model('catalog/exchange');
            $response = $this->model_catalog_exchange->getNomenclatureGuid($this->request->post['guid']);
            $this->model_catalog_exchange->parseNomenclatureResponse($response[0]);
            $json = ['status' => true, 'process' => 'Товар '.$this->request->post['guid'].' обновлен'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } else {
            $json = ['status' => false, 'description' => 'Данный введенные не верны!'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
        }
    }
}
