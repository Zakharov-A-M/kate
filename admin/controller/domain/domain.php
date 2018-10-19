<?php

class ControllerDomainDomain extends Controller
{
    private $error = array();

    /**
     * View all domain
     */
    public function index()
    {
        $this->load->language('domain/domain');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('domain/domain');

        $this->getList();
    }

    /**
     * View list all domain
     */
    protected function getList()
    {
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

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('domain/domain', 'user_token=' . $this->session->data['user_token'] . $url)
        );

        //$data['add'] = $this->url->link('domain/domain/add', 'user_token=' . $this->session->data['user_token'] . $url);
        //$data['delete'] = $this->url->link('domain/domain/delete', 'user_token=' . $this->session->data['user_token'] . $url);

        $data['domains'] = array();

        $filter_data = array(
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $domains_total = $this->model_domain_domain->getTotalDomains();

        $results = $this->model_domain_domain->getDomains($filter_data);

        foreach ($results as $result) {
            $data['domains'][] = array(
                'country_id'     => $result['country_id'],
                'country_name'   => $result['country_name'],
                'domain'         => $result['domain'],
                'language_name'  => $result['language_name'],
                'currency_title' => $result['currency_title'],
                'edit'         => $this->url->link('domain/domain/edit', 'user_token=' . $this->session->data['user_token'] . '&country_id=' . $result['country_id'] . $url),
            );
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

       /* $data['sort_sort_name'] = $this->url->link('stockroom/stockroom', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
        $data['sort_sort_country'] = $this->url->link('stockroom/stockroom', 'user_token=' . $this->session->data['user_token'] . '&sort=country_name' . $url);
        $data['sort_sort_address'] = $this->url->link('stockroom/stockroom', 'user_token=' . $this->session->data['user_token'] . '&sort=address' . $url);
*/
        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $domains_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('domain/domain', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($domains_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($domains_total - $this->config->get('config_limit_admin'))) ? $domains_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $domains_total, ceil($domains_total / $this->config->get('config_limit_admin')));

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('domain/domain_list', $data));
    }


    /**
     * Get form for domain
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

        $data['text_form'] = !isset($this->request->get['country_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['domain'])) {
            $data['error_domain'] = $this->error['domain'];
        } else {
            $data['error_domain'] = array();
        }

        if (isset($this->error['currency'])) {
            $data['error_currency'] = $this->error['currency'];
        } else {
            $data['error_currency'] = '';
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

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('domain/domain', 'user_token=' . $this->session->data['user_token'] . $url)
        );

        if (!isset($this->request->get['country_id'])) {
            $data['action'] = $this->url->link('domain/domain/add', 'user_token=' . $this->session->data['user_token'] . $url);
        } else {
            $data['action'] = $this->url->link('domain/domain/edit', 'user_token=' . $this->session->data['user_token'] . '&country_id=' . $this->request->get['country_id'] . $url);
        }

        $data['cancel'] = $this->url->link('domain/domain', 'user_token=' . $this->session->data['user_token'] . $url);

        if (isset($this->request->get['country_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $data['domainLang'] = $this->model_domain_domain->getDomain($this->request->get['country_id']);
            if(isset($data['domainLang'][0] )){
                $domain_info = current($data['domainLang']);
            }else{
                $domain_info = $data['domainLang'];
            }

        }

        $data['user_token'] = $this->session->data['user_token'];


        if (isset($this->request->post['domain'])) {
            $data['domain'] = $this->request->post['domain'];
        } elseif (!empty($domain_info)) {
            $data['domain'] = $domain_info['domain'];
        } else {
            $data['domain'] = '';
        }

        if (isset($this->request->post['currency_id'])) {
            $data['currency_id'] = $this->request->post['currency_id'];
        } elseif (!empty($domain_info)) {
            $data['currency_id'] = $domain_info['currency_id'];
        } else {
            $data['currency_id'] = '';
        }

        if (isset($this->request->post['currency_title'])) {
            $data['currency_title'] = $this->request->post['currency_title'];
        } elseif (!empty($domain_info)) {
            $data['currency_title'] = $domain_info['currency_title'];
        } else {
            $data['currency_title'] = '';
        }

        $this->load->model('design/layout');

        $data['layouts'] = $this->model_design_layout->getLayouts();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('domain/domain_form', $data));
    }

    /**
     * Edit this domain
     */
    public function edit()
    {
        $this->load->language('domain/domain');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('domain/domain');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_domain_domain->editDomain($this->request->get['country_id'], $this->request->post);

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

            $this->response->redirect($this->url->link('domain/domain', 'user_token=' . $this->session->data['user_token'] . $url));
        }

        $this->getForm();
    }

    /**
     * Autocomplete for country
     */
    public function autocomplete()
    {
        $json = array();

        if (isset($this->request->get['filter_name'])) {
            $this->load->model('domain/domain');

            $filter_data = array(
                'filter_name' => $this->request->get['filter_name'],
                'sort'        => 'name',
                'order'       => 'ASC',
                'start'       => 0,
                'limit'       => 5
            );

            $results = $this->model_domain_domain->getCurrencyForAutocomplete($filter_data);

            foreach ($results as $result) {
                $json[] = [
                    'currency_id' => $result['currency_id'],
                    'title'        => strip_tags(html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8'))
                ];
            }
        }

        $sort_order = array();

        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['title'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Validate form for domain
     *
     * @return bool
     */
    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'domain/domain')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['domain']) || !isset($this->request->post['domain'])) {
            $this->error['domain'] = $this->language->get('error_domain');
        } else {
            if (!preg_match('~^https?://(?:[a-z0-9](?:[-a-z0-9]*[a-z0-9])?\.)+[a-z](?:[-a-z0-9]*[a-z0-9])?\.?(?:$|/)~', $this->request->post['domain'])) {
                $this->error['domain'] = $this->language->get('error_domain');
            }
        }

        if (empty($this->request->post['currency_id'] || !isset($this->request->post['currency_id']))) {
            $this->error['currency'] = $this->language->get('error_currency');
        }

        if (empty($this->request->post['currency_title'] || !isset($this->request->post['currency_title']))) {
            $this->error['currency'] = $this->language->get('error_currency');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }
}
