<?php

class ControllerDesignMainPageCatalogNewsArticle extends Controller
{
    private $countries = [];

    /**
     * View page news and articles
     */
    public function index()
    {
        $this->load->language('design/mainPageCatalog/news_articles');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('catalog/news');
        $this->load->model('catalog/information');
        $this->load->model('setting/setting');
        $this->load->model('localisation/country');
        $this->load->model('tool/image');
        $this->countries = $this->model_localisation_country->getCountriesContact();
        $this->getForm();
    }

    /**
     * Get page form from news and articles
     */
    protected function getForm()
    {
        $data['text_form'] = $this->language->get('text_edit');

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_edit_main_page'),
            'href' => ''
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('design/mainPageCatalog/newsArticle', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['user_token'] = $this->session->data['user_token'];
        $data['countries'] = $this->countries;
        $newsArticleInfo = $this->config->get('config_news_article_main_page');
        $data['name'] = [];
        $data['status'] = [];

        $filterData = [
            'sort' => 'nd.title',
            'order' => 'ASC',
            'start' => NULL,
            'limit' => NULL
        ];
        $newsS = $this->model_catalog_news->getNewsList($filterData);

        $filterData = [
            'sort' => 'id.title',
            'order' => 'ASC',
            'start' => NULL,
            'limit' => NULL
        ];
        $articlesS = $this->model_catalog_information->getInformations($filterData);

        foreach ($data['countries'] as $country) {
            foreach ($newsS as $key => $news) {
                if (is_file(DIR_IMAGE . $news['image'])) {
                    $image = $this->model_tool_image->resize($news['image'], 120, 120);
                } else {
                    $image = $this->model_tool_image->resize('no_image.png', 120, 120);
                }

                $isViewedNews = false;
                if (!empty($newsArticleInfo[$country['country_id']]['array'])) {
                    foreach ($newsArticleInfo[$country['country_id']]['array'] as $item) {
                        if ($item['is_news'] && $item['id'] == $news['news_id']) {
                            $isViewedNews = true;
                        }
                    }
                }

                $data['news'][$country['country_id']][$key] = [
                    'news_id' => $news['news_id'],
                    'title' => $news['title'],
                    'image' => $image,
                    'status' => $news['status'],
                    'isViewedNews' => $isViewedNews
                ];
            }

            foreach ($articlesS as $key => $articles) {
                if (is_file(DIR_IMAGE . $articles['image'])) {
                    $image = $this->model_tool_image->resize($articles['image'], 120, 120);
                } else {
                    $image = $this->model_tool_image->resize('no_image.png', 120, 120);
                }

                $isViewedArticle = false;
                if (!empty($newsArticleInfo[$country['country_id']]['array'])) {
                    foreach ($newsArticleInfo[$country['country_id']]['array'] as $item) {
                        if (!$item['is_news'] && $item['id'] == $articles['information_id']) {
                            $isViewedArticle = true;
                        }
                    }
                }

                $data['articles'][$country['country_id']][$key] = [
                    'information_id' => $articles['information_id'],
                    'title' => $articles['title'],
                    'image' => $image,
                    'status' => $articles['is_visible'],
                    'isViewedArticle' => $isViewedArticle
                ];

            }
            $data['name'][$country['country_id']] = $newsArticleInfo[$country['country_id']]['name'] ?? '';
            $data['status'][$country['country_id']] = $newsArticleInfo[$country['country_id']]['status'] ?? 0;
            $data['allCategory'][$country['country_id']] = [
                'status' => $newsArticleInfo[$country['country_id']]['allCategory']['status'] ?? 0,
                'link'   => $newsArticleInfo[$country['country_id']]['allCategory']['link'] ?? 0,
                'text'   => $newsArticleInfo[$country['country_id']]['allCategory']['text'] ?? 0,
            ];
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('design/mainPageCatalog/news_articles_form', $data));
    }

    /**
     * Added new news-articles in main page
     */
    public function addNewsArticles()
    {
        $this->load->model('setting/setting');
        $this->load->language('design/mainPageCatalog/news_articles');
        $json = [];

        if (isset($this->request->get['newsArticleId']) && isset($this->request->get['countryId']) && isset($this->request->get['isNews'])) {
            $newsArticlesInfo = $this->config->get('config_news_article_main_page');
            if (!empty($newsArticlesInfo[$this->request->get['countryId']]['array'])) {
                array_push(
                    $newsArticlesInfo[$this->request->get['countryId']]['array'],
                    [
                        'id' => $this->request->get['newsArticleId'],
                        'is_news' => $this->request->get['isNews'],
                    ]
                );

                $this->model_setting_setting->editSettingValue(
                    'config_news_article_main_page',
                    'config_news_article_main_page',
                    $newsArticlesInfo
                );

                $json['status'] = true;
                $json['text'] = ($this->request->get['isNews']) ?
                    $this->language->get('success_add_news') :
                    $this->language->get('success_add_article');
            } else {
                $newsArticlesInfo[$this->request->get['countryId']]['array'][] = [
                    'id' => $this->request->get['newsArticleId'],
                    'is_news' => $this->request->get['isNews'],
                ];
                $this->model_setting_setting->editSettingValue(
                    'config_news_article_main_page',
                    'config_news_article_main_page',
                    $newsArticlesInfo
                );

                $json['status'] = true;
                $json['text'] = ($this->request->get['isNews']) ?
                    $this->language->get('success_add_news') :
                    $this->language->get('success_add_article');
            }
        } else {
            $json['status'] = false;
            $json['text'] = $this->language->get('error_add_news_article');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Delete news-articles in main page
     */
    public function deleteNewsArticles()
    {
        $this->load->model('setting/setting');
        $this->load->language('design/mainPageCatalog/news_articles');
        $json = [];

        if (isset($this->request->get['newsArticleId']) && isset($this->request->get['countryId']) && isset($this->request->get['isNews'])) {
            $newsArticlesInfo = $this->config->get('config_news_article_main_page');
            if (!empty($newsArticlesInfo[$this->request->get['countryId']]['array'])) {
                foreach ($newsArticlesInfo[$this->request->get['countryId']]['array'] as $key => $item) {
                    if (
                        $item['id'] == $this->request->get['newsArticleId'] &&
                        $item['is_news'] == $this->request->get['isNews']
                    ) {
                        unset($newsArticlesInfo[$this->request->get['countryId']]['array'][$key]);
                        $json['status'] = true;
                        $json['text'] = ($this->request->get['isNews']) ?
                            $this->language->get('success_delete_news') :
                            $this->language->get('success_delete_article');
                    }
                }
                $this->model_setting_setting->editSettingValue(
                    'config_news_article_main_page',
                    'config_news_article_main_page',
                    $newsArticlesInfo
                );
            } else {
                $json['status'] = false;
                $json['text'] = ($this->request->get['isNews']) ?
                    $this->language->get('error_delete_news') :
                    $this->language->get('error_delete_article');
            }
        } else {
            $json['status'] = false;
            $json['text'] = $this->language->get('error_delete_news_article');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Change name and status news-articles
     */
    public function changeName()
    {
        $this->load->model('setting/setting');
        $this->load->language('design/mainPageCatalog/news_articles');

        if (isset($this->request->get['name']) && isset($this->request->get['countryId']) && isset($this->request->get['status'])) {
            $newsArticlesInfo = $this->config->get('config_news_article_main_page');
            $newsArticlesInfo[$this->request->get['countryId']]['name'] = $this->request->get['name'];
            $newsArticlesInfo[$this->request->get['countryId']]['status'] = $this->request->get['status'];
            $this->model_setting_setting->editSettingValue(
                'config_news_article_main_page',
                'config_news_article_main_page',
                $newsArticlesInfo
            );
            $json['status'] = true;
            $json['text'] = $this->language->get('success_change_name_status');
        } else {
            $json['status'] = false;
            $json['text'] = $this->language->get('error_change_name_status');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Change name,status,link on all news-articles
     */
    public function changeNameLink()
    {
        $this->load->model('setting/setting');
        $this->load->language('design/mainPageCatalog/news_articles');

        if (
            isset($this->request->get['text']) &&
            isset($this->request->get['countryId']) &&
            isset($this->request->get['status']) &&
            isset($this->request->get['link'])
        ) {
            $newsArticlesInfo = $this->config->get('config_news_article_main_page');
            $newsArticlesInfo[$this->request->get['countryId']]['allCategory']['status'] = $this->request->get['status'];
            $newsArticlesInfo[$this->request->get['countryId']]['allCategory']['text'] = $this->request->get['text'];
            $newsArticlesInfo[$this->request->get['countryId']]['allCategory']['link'] = $this->request->get['link'];

            $this->model_setting_setting->editSettingValue(
                'config_news_article_main_page',
                'config_news_article_main_page',
                $newsArticlesInfo
            );
            $json['status'] = true;
            $json['text'] = $this->language->get('success_change_name_status_link');

        } else {
            $json['status'] = false;
            $json['text'] = $this->language->get('error_change_name_status_link');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
