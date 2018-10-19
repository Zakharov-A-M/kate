<?php
class ControllerRestapiLogout extends Controller
{
    /**
     * Logout user
     */
    public function index() {

        $this->registry->set('user', new Cart\User($this->registry));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->isLogged()) {
            $this->user->logout();
            $json = ['status' => true, 'description' => 'Вы вышли с учетной записи!'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
        } else {
            $json = ['status' => false, 'description' => 'Данный введенные не верны!'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
        }
    }
}
