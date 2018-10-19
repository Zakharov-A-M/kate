<?php

class ControllerExtensionModuleViewedProduct extends Controller
{
    /**
     * @return string
     */
    public function index()
    {
        if (isset($this->request->cookie['products'])) {
            $this->load->language('extension/module/viewed_product');
            $result = json_decode($this->request->cookie['products'], true);
            $data['products'] = [];
            $this->load->model('catalog/product');
            $i = 0;
            $data['count'] = count($result);
            foreach ($result as $key => $item) {
                $productInfo = $this->model_catalog_product->getProduct($item);
                if ($productInfo) {
                    if ($productInfo['image']) {
                        $image = $this->model_tool_image->resize($productInfo['image'], 80, 80);
                    } else {
                        $image = $this->model_tool_image->resize('placeholder.png', 80, 80);
                    }
                    $price = $this->currency->format(
                        $this->currency->convert(
                            $productInfo['price'],
                            $productInfo['currency'],
                            $this->session->data['currency']
                        ),
                        $this->session->data['currency'],
                        true
                    );
                    if ((float)$productInfo['special']) {
                        $special = $this->currency->format(
                            $this->currency->convert(
                                $productInfo['special'],
                                $productInfo['currency'],
                                $this->session->data['currency']
                            ),
                            $this->session->data['currency'],
                            true
                        );
                    } else {
                        $special = false;
                    }

                    $data['products'][] = [
                        'product_id' => $productInfo['product_id'],
                        'price' => $price,
                        'style' => $i > 4 ? 'style="display: none"' : '',
                        'image' => $image,
                        'special' => $special,
                        'name' => $productInfo['name'],
                        'href' => $this->url->link(
                            'product/product',
                            '&product_id=' . $productInfo['product_id']
                        )
                    ];
                    $i++;
                }
            }
            return $this->load->view('extension/module/viewed_product', $data);
        }
    }
}
