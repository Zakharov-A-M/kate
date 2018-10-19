<?php

class ControllerStockroomExchange extends Controller
{
    /**
     * View form for get stockroom from 1C
     */
    public function index()
    {
        $this->load->language('stockroom/exchange');

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link(
                'common/dashboard',
                'user_token=' . $this->session->data['user_token']
            )
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link(
                'stockroom/stockroom',
                'user_token=' . $this->session->data['user_token']
            )
        );

        $data['getStockroom'] = $this->url->link(
            'stockroom/exchange/getStockroom',
            'user_token=' . $this->session->data['user_token']
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('stockroom/exchange', $data));
    }

    /**
     * Get values stockroom and nomenclature from 1C
     */
    public function getStockroom()
    {
        $this->load->model('stockroom/exchange');
        $response = $this->model_stockroom_exchange->getStockroom();
        if ($response) {
            foreach ($response as $stockroom) {
                $this->model_stockroom_exchange->parseStockroomResponse($stockroom);
            }
        }
        $flag = true;
        while ($flag) {
            $response = $this->model_stockroom_exchange->getAmount();
            if ($response) {
                foreach ($response as $product) {
                    $this->model_stockroom_exchange->parseAmountResponse($product);
                }
            } else {
                $flag = false;
            }
        }
        if ($this->request->server['REQUEST_METHOD'] == 'GET') {
            $this->response->redirect(
                $this->url->link(
                    'stockroom/stockroom',
                    'user_token=' . $this->session->data['user_token'],
                    true
                )
            );
        }
    }
}
