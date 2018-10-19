<?php
class ControllerRestapiLogin extends Controller
{
    /**
     * login user
     */
	public function index() {

        $this->registry->set('user', new Cart\User($this->registry));
        if (isset($this->request->post['username']) && isset($this->request->post['password']) && $this->user->login($this->request->post['username'], html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8'))) {
            $this->session->data['user_token'] = token(32);
            $json = ['status' => true, 'description' => 'Вы зарегестрированы!'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
        } else {
            $json = ['status' => false, 'description' => 'Данный введенные не верны!'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
        }
	}
}
