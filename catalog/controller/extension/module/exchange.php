<?php

class ControllerExtensionModuleExchange extends Controller {

    const WITH_ERROR = true;
    const NO_ERROR = false;

    public function index()
    {
        $this->load->model('extension/module/exchange');
        $this->load->model('catalog/product');
        $errorStatus = self::WITH_ERROR;
        if ($this->model_extension_module_exchange->auth($this->request->get['token'])) {
            $response = $this->model_extension_module_exchange->getProducts();
            if ($response) {
                foreach ($response as $product) {
                    $parsedData = $this->model_extension_module_exchange->parseExchangeResponse($product);
                    $this->model_catalog_product->receiveProducts($parsedData);
                }
                $errorStatus = self::NO_ERROR;
            }
        }
        $this->model_extension_module_exchange->updateStatus($errorStatus);

    }
}
