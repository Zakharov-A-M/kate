<?php

class ControllerRestapiChangeStatusOrder extends Controller
{
    /**
     * Change status for order
     */
    public function index()
    {
        $this->registry->set('user', new Cart\User($this->registry));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->isLogged()) {
            if (isset($this->request->post['order_GUID']) && isset($this->request->post['status'])) {
                $this->load->model('restapi/order');
                $result = $this->model_restapi_order->changeStatusOrder($this->request->post['order_GUID'], $this->request->post['status']);
                if ($result) {
                    $json = ['status' => true, 'description' => 'Статус заказа изменен!'];
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
