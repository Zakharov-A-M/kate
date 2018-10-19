<?php

class ControllerRestapiAddnomenclature extends Controller
{
    /**
     * Add nomenclature
     */
    public function index()
    {
        $this->registry->set('user', new Cart\User($this->registry));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->isLogged()) {
            $json = ['status' => true, 'lang' => $this->request->post['language']];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } else {
            $json = ['status' => false];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
    }
}
