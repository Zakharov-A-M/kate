<?php

class ControllerRestapiChangePriceNomenclature extends Controller
{
    /**
     * Change price for product
     */
    public function index()
    {
        $this->registry->set('user', new Cart\User($this->registry));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->isLogged()) {
            if (isset($this->request->post['GUID']) && isset($this->request->post['price'])) {
                $this->load->model('restapi/nomenclature');
                 $result = $this->model_restapi_nomenclature->SetPrice($this->request->post['GUID'], $this->request->post['price']);
                if ($result) {
                    $json = ['status' => true, 'description' => 'Цена на товар изменена!'];
                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
                }
            }
        } else {
            $json = ['status' => false, 'description' => 'Данный введенные не верны!'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
        }
    }
}
