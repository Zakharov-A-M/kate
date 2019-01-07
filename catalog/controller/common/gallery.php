<?php

class ControllerCommonGallery extends Controller
{
    public function index()
    {
        $this->load->model('design/gallery');
        $this->load->language('design/gallery');

        if (empty($this->request->get['gallery'])) {
            $data['galleries'] = $this->model_design_gallery->getGalleries();
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');
            $this->response->setOutput($this->load->view('common/gallery', $data));
        } else {
            $data['galleries'] = $this->model_design_gallery->getGallery($this->request->get['gallery']);
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');
            $this->response->setOutput($this->load->view('common/gallery_view', $data));
        }
    }

}
