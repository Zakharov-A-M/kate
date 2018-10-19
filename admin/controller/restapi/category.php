<?php

class ControllerRestapiCategory extends Controller
{
    /**
     * Api update category
     */
    public function index()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('catalog/exchange');

            $response = $this->model_catalog_exchange->getCategory();
            if ($response) {
                $this->model_catalog_exchange->disableCategory();
                foreach ($response as $category) {
                    $this->model_catalog_exchange->parseCategoryResponse($category);
                }
            }

            $json = ['status' => true, 'process' => 'Данные по категории изменено'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } else {
            $json = ['status' => false, 'description' => 'Данный введенные не верны!','met'=>$this->request->server['REQUEST_METHOD']];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
        }
    }
}
