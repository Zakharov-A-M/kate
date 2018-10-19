<?php

class ControllerAccountTracking extends Controller
{
    /**
     * Viewed tracking user
     *
     * @return Action
     */
    public function index()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/tracking');
            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->model('account/affiliate');
        $affiliateInfo = $this->model_account_affiliate->getAffiliate($this->customer->getId());
        if ($affiliateInfo) {
            $this->load->language('account/tracking');
            $this->document->setTitle($this->language->get('heading_title'));
            $data['breadcrumbs'] = [];
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_account'),
                'href' => $this->url->link('account/account')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('account/tracking')
            ];

            $data['text_description'] = sprintf(
                $this->language->get('text_description'),
                $this->config->get('config_name')
            );

            $data['code'] = $affiliateInfo['tracking'];
            $data['continue'] = $this->url->link('account/account');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('account/tracking', $data));
        } else {
            return new Action('error/not_found');
        }
    }

    /**
     * Auto complete for search product
     */
    public function autocomplete()
    {
        $json = [];

        if (isset($this->request->get['filter_name'])) {
            if (isset($this->request->get['tracking'])) {
                $tracking = $this->request->get['tracking'];
            } else {
                $tracking = '';
            }

            $this->load->model('catalog/product');
            $filterData = [
                'filter_name' => $this->request->get['filter_name'],
                'start' => 0,
                'limit' => 5
            ];

            $results = $this->model_catalog_product->getProducts($filterData);
            foreach ($results as $result) {
                $json[] = [
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'link' => str_replace(
                        '&amp;',
                        '&',
                        $this->url->link(
                            'product/product',
                            'product_id=' . $result['product_id'] . '&tracking=' . $tracking
                        )
                    )
                ];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
