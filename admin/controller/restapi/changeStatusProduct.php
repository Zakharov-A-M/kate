<?php
class ControllerRestapiChangeStatusProduct extends Controller
{
    /**
     * Update all product if not active category
     */
    public function index()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('catalog/product');
            $this->model_catalog_product->updateStatusProduct();
            $json = ['status' => true, 'process' => 'Товары деактивированы, которые не иммеют активные категории'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
        } else {
            $json = ['status' => false, 'description' => 'Данные введены не верно!'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
        }
    }
}
