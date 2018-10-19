<?php

class ControllerInformationLegalEntity extends Controller
{
    /**
     * View page About us
     */
    public function index()
    {
        $this->load->model('catalog/information');
        $info = $this->model_catalog_information->getInformation(11);
        if ($info) {
            $this->document->setTitle($info['meta_title']);
            $data['breadcrumbs'] = [];
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            ];
            $data['breadcrumbs'][] = [
                'text'     => $info['meta_title'],
                'href'     => $this->url->link('information/about')
            ];
            $data['description'] = html_entity_decode($info['description'], ENT_QUOTES, 'UTF-8');
        }

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('information/legal_entity', $data));
    }
}
