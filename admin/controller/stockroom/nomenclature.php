<?php

class ControllerStockroomNomenclature extends Controller
{
    private $error = array();

    /**
     * View all nomenclature from ID stockroom
     */
    public function index()
    {
        $this->load->language('stockroom/nomenclature');
        $this->load->model('stockroom/nomenclature');
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

        $stockroomId = $this->request->get['stockroom_id'];

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = '';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
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
            'text' => $this->language->get('heading_titles'),
            'href' => $this->url->link(
                'stockroom/stockroom',
                'user_token=' . $this->session->data['user_token'] . $url
            )
        ];

        $data['add'] = $this->url->link(
            'stockroom/nomenclature/add',
            'user_token=' . $this->session->data['user_token'] . $url . '&stockroom_id=' . $stockroomId . ''
        );
        $data['delete'] = $this->url->link(
            'stockroom/nomenclature/delete',
            'user_token=' . $this->session->data['user_token'] . $url . '&stockroom_id=' . $stockroomId . ''
        );

        $data['nomenclature'] = [];

        $filterData = [
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        ];

        $stockroom_total = $this->model_stockroom_nomenclature->getTotalNomenclatures($stockroomId);

        $results = $this->model_stockroom_nomenclature->getNomenclatures($stockroomId, $filterData);

        $this->document->setTitle(
            $this->language->get('heading_title') . ' ' .
            $this->model_stockroom_stockroom->getStockroomName($stockroomId)
        );

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title') . ' ' . $this->model_stockroom_stockroom->getStockroomName(
                    $stockroomId
                ),
            'href' => $this->url->link(
                'stockroom/nomenclature',
                'user_token=' . $this->session->data['user_token'] . $url .
                '&stockroom_id=' . $stockroomId . ''
            )
        ];

        $this->load->model('tool/image');

        foreach ($results as $result) {
            if ($result['image'] && file_exists(DIR_IMAGE . $result['image'])) {
                $image = $this->model_tool_image->resize($result['image'], 40, 40);
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', 40, 40);
            }

            $data['nomenclatures'][] = [
                'id' => $result['id'],
                'image' => $image,
                'name' => $result['name'],
                'description' => $result['description'],
                'amount' => $result['amount'],
                'edit' => $this->url->link(
                    'stockroom/nomenclature/edit',
                    'user_token=' . $this->session->data['user_token'] .
                    '&id=' . $result['id'] . $url .
                    '&stockroom_id=' . $stockroomId . ''
                ),
                'delete' => $this->url->link(
                    'stockroom/nomenclature/delete',
                    'user_token=' . $this->session->data['user_token'] .
                    '&id=' . $result['id'] .
                    '&stockroom_id=' . $stockroomId . ' ' . $url
                ),
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

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_sort_amount'] = $this->url->link(
            'stockroom/nomenclature',
            'user_token=' . $this->session->data['user_token'] .
            '&sort=amount' .
            '&stockroom_id=' . $stockroomId . $url
        );
        $data['sort_sort_description'] = $this->url->link(
            'stockroom/nomenclature',
            'user_token=' . $this->session->data['user_token'] .
            '&sort=description' .
            '&stockroom_id=' . $stockroomId . $url
        );
        $data['sort_sort_name'] = $this->url->link(
            'stockroom/nomenclature',
            'user_token=' . $this->session->data['user_token'] .
            '&sort=name' .
            '&stockroom_id=' . $stockroomId . $url
        );

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $stockroom_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link(
            'stockroom/nomenclature',
            'user_token=' . $this->session->data['user_token'] . $url . '&page={page}'
        );

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf(
            $this->language->get('text_pagination'),
            ($stockroom_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0,
            (
                (($page - 1) * $this->config->get('config_limit_admin')) >
                ($stockroom_total - $this->config->get('config_limit_admin'))
            ) ?
                $stockroom_total : (
                (($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')
            ),
            $stockroom_total,
            ceil($stockroom_total / $this->config->get('config_limit_admin'))
        );

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('stockroom/nomenclature_list', $data));
    }


    /**
     * Delete this nomenclature
     */
    public function delete()
    {
        $this->load->language('stockroom/nomenclature');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('stockroom/nomenclature');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $nomenclature_id) {
                $this->model_stockroom_nomenclature->deleteNomenclature($nomenclature_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            $url .= '&stockroom_id=' . $this->request->get['stockroom_id'];

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect(
                $this->url->link(
                    'stockroom/nomenclature',
                    'user_token=' . $this->session->data['user_token'] . $url
                )
            );
        }
        $this->getList();
    }

    /**
     * validate process delete
     *
     * @return bool
     */
    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'stockroom/nomenclature')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    /**
     * Edit this nomenclature
     */
    public function edit()
    {
        $this->load->language('stockroom/nomenclature');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('stockroom/nomenclature');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_stockroom_nomenclature->editNomenclature($this->request->get['id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect(
                $this->url->link(
                    'stockroom/nomenclature',
                    'user_token=' . $this->session->data['user_token'] . $url .
                    '&stockroom_id=' . $this->request->get['stockroom_id'] . ''
                )
            );
        }

        $this->getForm();
    }

    /**
     * Get form for nomenclature
     */
    protected function getForm()
    {
        $this->document->addStyle('view/javascript/codemirror/lib/codemirror.css');
        $this->document->addStyle('view/javascript/codemirror/theme/monokai.css');
        $this->document->addStyle('view/javascript/summernote/summernote.css');

        $this->document->addScript('view/javascript/codemirror/lib/codemirror.js');
        $this->document->addScript('view/javascript/codemirror/lib/xml.js');
        $this->document->addScript('view/javascript/codemirror/lib/formatting.js');
        $this->document->addScript('view/javascript/summernote/summernote.js');
        $this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');
        $this->document->addScript('view/javascript/summernote/opencart.js');

        $data['text_form'] = !isset($this->request->get['id']) ?
            $this->language->get('text_add') :
            $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $stockroomId = $this->request->get['stockroom_id'];

        if (isset($this->error['amount'])) {
            $data['error_amount'] = $this->error['amount'];
        } else {
            $data['error_amount'] = array();
        }

        if (isset($this->error['nomenclature'])) {
            $data['error_nomenclature'] = $this->error['nomenclature'];
        } else {
            $data['error_nomenclature'] = '';
        }

        if (isset($this->error['nomenclature_dublicate'])) {
            $data['error_nomenclature_dublicate'] = $this->error['nomenclature_dublicate'];
        } else {
            $data['error_nomenclature_dublicate'] = '';
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
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
            'text' => $this->language->get('heading_titles'),
            'href' => $this->url->link(
                'stockroom/stockroom',
                'user_token=' . $this->session->data['user_token'] . $url
            )
        ];


        if (!isset($this->request->get['id'])) {
            $data['action'] = $this->url->link(
                'stockroom/nomenclature/add',
                'user_token=' . $this->session->data['user_token'] . $url . '&stockroom_id=' . $stockroomId . ''
            );
        } else {
            $data['action'] = $this->url->link(
                'stockroom/nomenclature/edit',
                'user_token=' . $this->session->data['user_token'] .
                '&id=' . $this->request->get['id'] . $url .
                '&stockroom_id=' . $stockroomId . ''
            );
        }
        $data['cancel'] = $this->url->link(
            'stockroom/nomenclature',
            'user_token=' . $this->session->data['user_token'] . $url .
            '&stockroom_id=' . $this->request->get['stockroom_id'] . ''
        );

        if (isset($this->request->get['id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $nomenclature_info = $this->model_stockroom_nomenclature->getNomenclature($this->request->get['id']);
        }

        $data['user_token'] = $this->session->data['user_token'];


        if (isset($this->request->post['amount'])) {
            $data['amount'] = (int)$this->request->post['amount'];
        } elseif (!empty($nomenclature_info)) {
            $data['amount'] = (int)$nomenclature_info['amount'];
        } else {
            $data['amount'] = '';
        }

        if (isset($this->request->post['nomenclature_id'])) {
            $data['nomenclature_id'] = $this->request->post['nomenclature_id'];
        } elseif (!empty($nomenclature_info)) {
            $data['nomenclature_id'] = $nomenclature_info['nomenclature_id'];
        } else {
            $data['nomenclature_id'] = '';
        }

        if (isset($this->request->post['nomenclature_name'])) {
            $data['nomenclature_name'] = $this->request->post['nomenclature_name'];
        } elseif (!empty($nomenclature_info)) {
            $data['nomenclature_name'] = $nomenclature_info['nomenclature_name'];
        } else {
            $data['nomenclature_name'] = '';
        }

        $this->load->model('design/layout');

        $data['layouts'] = $this->model_design_layout->getLayouts();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('stockroom/nomenclature_form', $data));
    }

    /**
     * Validate form for nomenclature
     *
     * @return bool
     */
    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'stockroom/nomenclature')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['amount']) || !isset($this->request->post['amount'])) {
            $this->error['amount'] = $this->language->get('error_amount');
        } else {
            if ((int)$this->request->post['amount'] < 0) {
                $this->error['amount'] = $this->language->get('error_amount');
            }
        }

        if (empty($this->request->post['nomenclature_id'] || !isset($this->request->post['nomenclature_id']))) {
            $this->error['nomenclature'] = $this->language->get('error_nomenclature');
        }

        if (empty($this->request->post['nomenclature_name'] || !isset($this->request->post['nomenclature_name']))) {
            $this->error['nomenclature'] = $this->language->get('error_nomenclature');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    /**
     * Check dublicate on nomenclature
     *
     * @return bool
     */
    protected function noDublicate()
    {
        if (!empty($this->request->post['nomenclature_id'] || isset($this->request->post['nomenclature_id']))) {
            if ($this->model_stockroom_nomenclature->dublicateNomenclature(
                    $this->request->get['stockroom_id'],
                    $this->request->post) > 0
            ) {
                $this->error['nomenclature_dublicate'] = $this->language->get('error_nomenclature_dublicate');
            }
        }
        return !$this->error;
    }

    /**
     * Autocomplete for nomenclature
     */
    public function autocomplete()
    {
        $json = array();

        if (isset($this->request->get['filter_name'])) {
            $this->load->model('stockroom/nomenclature');

            $filterData = array(
                'filter_name' => $this->request->get['filter_name'],
                'sort' => 'name',
                'order' => 'ASC',
                'start' => 0,
                'limit' => 5
            );

            $results = $this->model_stockroom_nomenclature->getNomenclatureForAutocomplete($filterData);

            foreach ($results as $result) {
                $json[] = [
                    'product_id' => $result['product_id'],
                    'name' => strip_tags(
                        html_entity_decode($result['name'],
                            ENT_QUOTES,
                            'UTF-8'
                        )
                    )
                ];
            }
        }

        $sortOrder = array();

        foreach ($json as $key => $value) {
            $sortOrder[$key] = $value['name'];
        }

        array_multisort($sortOrder, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Added nomenclature
     */
    public function add()
    {
        $this->load->language('stockroom/nomenclature');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('stockroom/nomenclature');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            if ($this->noDublicate()) {

                $this->model_stockroom_nomenclature->addNomenclature(
                    $this->request->get['stockroom_id'],
                    $this->request->post
                );

                $this->session->data['success'] = $this->language->get('text_success');

                $url = '';

                if (isset($this->request->get['sort'])) {
                    $url .= '&sort=' . $this->request->get['sort'];
                }

                if (isset($this->request->get['order'])) {
                    $url .= '&order=' . $this->request->get['order'];
                }

                if (isset($this->request->get['page'])) {
                    $url .= '&page=' . $this->request->get['page'];
                }

                $this->response->redirect(
                    $this->url->link(
                        'stockroom/nomenclature',
                        'user_token=' . $this->session->data['user_token'] . $url .
                        '&stockroom_id=' . $this->request->get['stockroom_id'] . ''
                    )
                );
            }
        }
        $this->getForm();
    }
}
