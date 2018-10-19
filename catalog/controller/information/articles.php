<?php

class ControllerInformationArticles extends Controller
{
    /**
     * View page with all articles
     */
    public function index()
    {
        $this->load->language('catalog/articles');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('catalog/information');
        $this->load->model('catalog/news');
        $this->load->model('tool/image');

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/articles')
        ];

        $data['informations'] = [];
        $results = $this->model_catalog_information->getInformations(1);
        foreach ($results as $result) {
            $data['informations'][] = array(
                'information_id'   => $result['information_id'],
                'title'            => $result['title'],
                'image'            => $result['image'],
                'description'      => $result['description'],
                'meta_title'       => $result['meta_title'],
                'meta_description' => $result['meta_description'],
                'meta_keyword'     => $result['meta_keyword'],
                'view'             => $this->url->link('information/articles/view', '&article_id=' . $result['information_id'])
            );
        }

        $filterData = [
            'start' => 0,
            'limit' => 100
        ];

        $newsList = $this->model_catalog_news->getNews($filterData);
        if ($newsList) {
            $newsSetting = [];

            if ($this->config->get('news_setting')) {
                $newsSetting = $this->config->get('news_setting');
            } else {
                $newsSetting['description_limit'] = '300';
                $newsSetting['news_thumb_width'] = '220';
                $newsSetting['news_thumb_height'] = '220';
            }

            foreach ($newsList as $result) {
                if ($result['image']) {
                    $image = $result['image'];
                } else {
                    $image = false;
                }
                $data['news'][] = [
                    'title' => $result['title'],
                    'image' => $image,
                    'viewed' => sprintf($this->language->get('text_viewed'), $result['viewed']),
                    'description' => $result['meta_title'],
                    'href' => $this->url->link('information/news/info', 'news_id=' . $result['news_id']),
                    'posted' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
                ];
            }
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('information/articles', $data));
    }

    /**
     * View page with article to id
     */
    public function view()
    {
        $this->load->language('catalog/articles');
        $this->load->language('information/news');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('catalog/information');

        if (!empty($this->request->get['article_id'])) {
            $result = $this->model_catalog_information->getInformation($this->request->get['article_id']);
            $data['information'] = array(
                'information_id'   => $result['information_id'],
                'title'            => $result['title'],
                'description'      => html_entity_decode($result['description']),
                'meta_title'       => $result['meta_title'],
                'meta_description' => $result['meta_description'],
                'meta_keyword'     => $result['meta_keyword'],
            );

            $this->document->setTitle($result['title']);

            $data['breadcrumbs'] = [];
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('information/articles')
            ];

            $data['breadcrumbs'][] = [
                'text' => $result['title'],
                'href' => $this->url->link('information/articles/view', '&article_id=' . $result['information_id'])
            ];

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            //$data['catalog_menu'] = $this->load->controller('catalog/menu');

            $this->response->setOutput($this->load->view('information/article_view', $data));
        }

    }

}
