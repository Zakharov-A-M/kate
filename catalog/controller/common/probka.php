<?php

class ControllerCommonProbka extends Controller
{
    /**
     * View start page
     */
    public function index()
    {
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $data['subscribe'] = $this->load->controller('extension/module/subscribe');
        $this->response->setOutput($this->load->view('common/probka', $data));
    }
}
