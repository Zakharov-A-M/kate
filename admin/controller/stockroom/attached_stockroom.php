<?php

class ControllerStockroomAttachedStockroom extends Controller
{
    private $error = [];

    /**
     * Get list attached stockroom ID
     */
    public function index()
    {
        $this->load->language('stockroom/attached_stockroom');
        $this->load->model('stockroom/attached_stockroom');
        $this->load->model('stockroom/stockroom');
        $this->getList();
    }

    /**
     * View list all stockroom
     */
    protected function getList()
    {
        if (!isset($this->request->get['stockroom_id']) || empty($this->request->get['stockroom_id'])) {
            $this->response->redirect(
                $this->url->link(
                    'stockroom/stockroom',
                    'user_token=' . $this->session->data['user_token']
                )
            );
        }
        $this->document->setTitle($this->language->get('heading_title'));
        $stockroomId = $this->request->get['stockroom_id'];
        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link(
                'common/dashboard',
                'user_token=' . $this->session->data['user_token']
            )
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link(
                'stockroom/stockroom',
                'user_token=' . $this->session->data['user_token']
            )
        ];

        $data['add'] = $this->url->link(
            'stockroom/attached_stockroom/add',
            'user_token=' . $this->session->data['user_token'] . '&stockroom_id=' . $stockroomId . ''
        );
        $data['delete'] = $this->url->link(
            'stockroom/attached_stockroom/delete',
            'user_token=' . $this->session->data['user_token'] . '&stockroom_id=' . $stockroomId . ''
        );

        $data['stockrooms'] = [];

        $results = $this->model_stockroom_attached_stockroom->getAttachedStockrooms($stockroomId);

        foreach ($results as $result) {
            $stockroomInfo = $this->model_stockroom_stockroom->getStockroom($result['attach_stockroom_id']);
            $stockroomInfoDescription = $this->model_stockroom_stockroom->getStockroomDescription($result['attach_stockroom_id']);
            $data['stockrooms'][] = [
                'id' => $result['id'],
                'name' => $stockroomInfoDescription[$this->config->get('config_language_id')]['name'],
                'delivery'   => $result['description_delivery'],
                'country'    => $stockroomInfo['country_name'],
                'sort_order' => $result['sort_order'],
                'edit' => $this->url->link(
                    'stockroom/attached_stockroom/edit',
                    'user_token=' . $this->session->data['user_token'] .
                    '&id=' . $result['id']
                )
            ];
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('stockroom/attached_stockroom_list', $data));
    }

    /**
     * Delete attached stockroom
     */
    public function delete()
    {
        $this->load->language('stockroom/attached_stockroom');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('stockroom/attached_stockroom');

        if (isset($this->request->post['selected'])) {
            foreach ($this->request->post['selected'] as $stockroomId) {
                $this->model_stockroom_attached_stockroom->deleteAttachedStockroom($stockroomId);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect(
                $this->url->link(
                    'stockroom/stockroom',
                    'user_token=' . $this->session->data['user_token']
                )
            );
        }
        $this->getList();
    }

    /**
     * Edit this attached stockroom
     */
    public function edit()
    {
        $this->load->language('stockroom/attached_stockroom');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('stockroom/attached_stockroom');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_stockroom_attached_stockroom->editAttachedStockroom(
                $this->request->get['id'],
                $this->request->post
            );

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect(
                $this->url->link(
                    'stockroom/stockroom',
                    'user_token=' . $this->session->data['user_token']
                ));
        }

        $this->getForm();
    }

    /**
     * Added stockroom
     */
    public function add()
    {
        $this->load->language('stockroom/attached_stockroom');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('stockroom/attached_stockroom');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_stockroom_attached_stockroom->addAttachedStockroom(
                $this->request->get['stockroom_id'],
                $this->request->post
            );
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect(
                $this->url->link(
                    'stockroom/attached_stockroom',
                    'user_token=' . $this->session->data['user_token'] .
                    '&stockroom_id=' . $this->request->get['stockroom_id']
                )
            );
        }

        $this->getForm();
    }

    /**
     * validate form
     *
     * @return bool
     */
    protected function validateForm()
    {
        if ((utf8_strlen(trim($this->request->post['delivery'])) < 3) ||
            (utf8_strlen(trim($this->request->post['delivery'])) > 50)
        ) {
            $this->error['delivery'] = $this->language->get('error_delivery');
        }

        if (!empty($this->request->get['stockroom_id'])) {
            $this->load->model('stockroom/attached_stockroom');
            if ($this->model_stockroom_attached_stockroom->dublicateAttachedStockroom(
                $this->request->get['stockroom_id'],
                $this->request->post
            )
            ) {
                $this->error['stockroom'] = $this->language->get('error_stockroom_dublicate');
            }
        }

        if (utf8_strlen(trim($this->request->post['attach_stockroom_name'])) < 1) {
            $this->error['stockroom'] = $this->language->get('error_stockroom');
        }

        if (empty($this->request->post['attach_stockroom_id'])) {
            $this->error['stockroom'] = $this->language->get('error_stockroom');
        }

        return !$this->error;
    }


    /**
     * Get form for attached stockroom
     */
    protected function getForm()
    {
        $data['text_form'] = !isset($this->request->get['id']) ?
            $this->language->get('text_add') :
            $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['delivery'])) {
            $data['error_delivery'] = $this->error['delivery'];
        } else {
            $data['error_delivery'] = '';
        }

        if (isset($this->error['stockroom'])) {
            $data['error_stockroom'] = $this->error['stockroom'];
        } else {
            $data['error_stockroom'] = '';
        }

        if (isset($this->error['stockroom_dublicate'])) {
            $data['error_stockroom_dublicate'] = $this->error['stockroom_dublicate'];
        } else {
            $data['error_stockroom_dublicate'] = '';
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link(
                'common/dashboard',
                'user_token=' . $this->session->data['user_token']
            )
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link(
                'stockroom/stockroom',
                'user_token=' . $this->session->data['user_token']
            )
        ];

        if (isset($this->request->get['id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $stockroomInfo = $this->model_stockroom_attached_stockroom->getStockroom($this->request->get['id']);
            $stockroomName = $this->model_stockroom_attached_stockroom->getAttachedStockroomName(
                $stockroomInfo['attach_stockroom_id']
            );
            $data['current'] = $stockroomInfo['stockroom_id'];
            $data['attached'] = $stockroomInfo['attach_stockroom_id'];
        }
        if (!empty($this->request->get['stockroom_id'])) {
            $data['current'] = $this->request->get['stockroom_id'];
        }


        if (!isset($this->request->get['id'])) {
            $data['action'] = $this->url->link(
                'stockroom/attached_stockroom/add',
                'user_token=' . $this->session->data['user_token'] .
                '&stockroom_id=' . $data['current']
            );
        } else {
            $data['action'] = $this->url->link(
                'stockroom/attached_stockroom/edit',
                'user_token=' . $this->session->data['user_token'] .
                '&id=' . $this->request->get['id']
            );
        }
        $data['cancel'] = $this->url->link(
            'stockroom/stockroom',
            'user_token=' . $this->session->data['user_token']
        );

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->post['delivery'])) {
            $data['delivery'] = $this->request->post['delivery'];
        } elseif (!empty($stockroomInfo)) {
            $data['delivery'] = $stockroomInfo['description_delivery'];
        } else {
            $data['delivery'] = '';
        }

        if (isset($this->request->post['attach_stockroom_id'])) {
            $data['attach_stockroom_id'] = (int)$this->request->post['attach_stockroom_id'];
        } elseif (!empty($stockroomInfo)) {
            $data['attach_stockroom_id'] = (int)$stockroomInfo['attach_stockroom_id'];
        } else {
            $data['attach_stockroom_id'] = '';
        }

        if (isset($this->request->post['sort_order'])) {
            $data['sort_order'] = (int)$this->request->post['sort_order'];
        } elseif (!empty($stockroomInfo)) {
            $data['sort_order'] = (int)$stockroomInfo['sort_order'];
        } else {
            $data['sort_order'] = '';
        }

        if (isset($this->request->post['attach_stockroom_name'])) {
            $data['attach_stockroom_name'] = $this->request->post['attach_stockroom_name'];
        } elseif (!empty($stockroomName)) {
            $data['attach_stockroom_name'] = $stockroomName['name'];
        } else {
            $data['attach_stockroom_name'] = '';
        }
        $this->load->model('design/layout');
        $data['layouts'] = $this->model_design_layout->getLayouts();
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('stockroom/attached_stockroom_form', $data));
    }

    /**
     * Auto complete for search stockroom
     */
    public function autoComplete()
    {
        $json = [];
        $currentId = [$this->request->get['currentId']];
        $this->load->model('stockroom/attached_stockroom');
        $filterData = [
            'filter_name' => $this->request->get['filter_name'],
            'sort' => 'name',
            'order' => 'ASC',
            'start' => 0,
            'limit' => 5,
        ];
        if (!empty($this->request->get['attachedId'])) {
            array_push($currentId, $this->request->get['attachedId']);
        }

        $stockroomInfo = $this->model_stockroom_attached_stockroom->getNomenclatureForAutoComplete($filterData, $currentId);

        foreach ($stockroomInfo as $result) {
            $json[] = [
                'stockroom_id' => $result['stockroom_id'],
                'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
            ];
        }
        $sortOrder = [];

        foreach ($json as $key => $value) {
            $sortOrder[$key] = $value['name'];
        }

        array_multisort($sortOrder, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
