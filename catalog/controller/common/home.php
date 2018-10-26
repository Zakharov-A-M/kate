<?php

class ControllerCommonHome extends Controller
{
    /**
     * View start page
     */
	public function index()
    {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));
        $this->load->language('common/home');

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}
        if ($this->cart->hasProducts()) {
            $productsCart = $this->cart->getProducts();
        }

        $data['linkCheckOut'] = $this->url->link('checkout/uni_checkout');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

        /*$bannerInfo = $this->config->get('config_banner_main_page');
        if (!empty($bannerInfo[$this->config->get('config_current_country')]['array']) &&
            $bannerInfo[$this->config->get('config_current_country')]['status']
        ) {
            foreach ($bannerInfo[$this->config->get('config_current_country')]['array'] as $key => $item) {
                if (is_file(DIR_IMAGE . $item['image'])) {
                    $image = $item['image'];

                    $data['banners'][$key] = [
                        'title'      => $item['title'],
                        'link'       => $item['link'],
                        'image'      => $image,
                        'sort_order' => $item['sort_order']
                    ];
                }
            }
            usort( $data['banners'], function ($item1, $item2) {
                return $item1['sort_order'] <=> $item2['sort_order'];
            });
        }*/

        $productInfo = $this->config->get('config_product_main_page');
        $this->load->model('catalog/product');
        if (!empty($productInfo[$this->config->get('config_current_country')]['array']) &&
            !empty(trim($productInfo[$this->config->get('config_current_country')]['name']))
        ) {
            $data['nameProduct'] = $productInfo[$this->config->get('config_current_country')]['name'];
            foreach ($productInfo[$this->config->get('config_current_country')]['array'] as $key => $item) {
                $product = $this->model_catalog_product->getProduct($item);
                if ($product && !empty($product['amount'])) {
                    if ($product['image']) {
                        $image = $this->model_tool_image->resize(
                            $product['image'],
                            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
                            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')
                        );
                    } else {
                        $image = $this->model_tool_image->resize(
                            'placeholder.png',
                            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
                            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')
                        );
                    }

                    if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                        $price = $this->currency->format(
                            $this->currency->convert(
                                $this->tax->calculate(
                                    $product['price'],
                                    $product['tax_class_id'],
                                    $this->config->get('config_tax')
                                ),
                                $product['currency'],
                                $this->session->data['currency']
                            ),
                            $this->session->data['currency'],
                            true
                        );
                    } else {
                        $price = false;
                    }

                    if ((float)$product['special']) {
                        $special = $this->currency->format(
                            $this->currency->convert(
                                $this->tax->calculate(
                                    $product['special'],
                                    $product['tax_class_id'],
                                    $this->config->get('config_tax')
                                ),
                                $product['currency'],
                                $this->session->data['currency']
                            ),
                            $this->session->data['currency'],
                            true
                        );
                    } else {
                        $special = false;
                    }

                    if (!empty($productsCart)) {
                        foreach ($productsCart as $item) {
                            if ($item['product_id'] == $product['product_id']) {
                                $inCart = true;
                                break;
                            } else {
                                $inCart = false;
                            }
                        }
                    } else {
                        $inCart = false;
                    }

                    $data['products'][] = [
                        'product_id'  => $product['product_id'],
                        'thumb'       => $image,
                        'name'        => $product['name'],
                        'amount'      => $product['amount'],
                        'model'       => $product['model'],
                        'description' => utf8_substr(trim(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                        'price'       => $price,
                        'special'     => $special,
                        'inCart'      => $inCart,
                        'minimum'     => $product['minimum'] > 0 ? $product['minimum'] : 1,
                        'rating'      => $product['rating'],
                        'href'        => $this->url->link(
                            'product/product',
                            'product_id=' . $product['product_id']
                        )
                    ];
                }
            }
        }

        $categoryInfo = $this->config->get('config_category_main_page');
        if (!empty($categoryInfo[$this->config->get('config_current_country')]['array']) &&
            !empty(trim($categoryInfo[$this->config->get('config_current_country')]['name']))
        ) {
            $data['nameCategory'] = $categoryInfo[$this->config->get('config_current_country')]['name'];
            foreach ($categoryInfo[$this->config->get('config_current_country')]['array'] as $key => $item) {
                if (!empty(trim($item['title'])) &&
                    !empty($this->model_catalog_product->getTotalProducts([
                        'filter_category_id' => $item['link'],
                        'filter_sub_category'   => true,
                        ])
                    )
                ) {
                    if ($item['image']) {
                        $image = $this->model_tool_image->resize(
                            $item['image'],
                            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
                            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')
                        );
                    } else {
                        $image = $this->model_tool_image->resize(
                            'placeholder.png',
                            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
                            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')
                        );
                    }

                    $data['categories'][$key] = [
                        'image'       => $image,
                        'title'       => $item['title'],
                        'href'        => (!empty($item['link'])) ? $this->url->link('product/category', 'path=' . $item['link']) : '',
                        'sort_order'   => $item['sort_order'],
                    ];
                }
            }
            usort( $data['categories'], function ($item1, $item2) {
                return $item1['sort_order'] <=> $item2['sort_order'];
            });

            if (!empty($categoryInfo[$this->config->get('config_current_country')]['allCategory']) &&
                $categoryInfo[$this->config->get('config_current_country')]['allCategory']['status'] &&
                $categoryInfo[$this->config->get('config_current_country')]['allCategory']['text'] &&
                $categoryInfo[$this->config->get('config_current_country')]['allCategory']['link']
            ) {
                $data['linkCategory'] =  $categoryInfo[$this->config->get('config_current_country')]['allCategory']['link'];
                $data['textCategory'] =  $categoryInfo[$this->config->get('config_current_country')]['allCategory']['text'];
            }
        }

        $newsArticleInfo = $this->config->get('config_news_article_main_page');
        if (!empty($newsArticleInfo[$this->config->get('config_current_country')]['array']) &&
            !empty(trim($newsArticleInfo[$this->config->get('config_current_country')]['name'])) &&
            $newsArticleInfo[$this->config->get('config_current_country')]['status']
        ) {
            $this->load->model('catalog/news');
            $this->load->model('catalog/information');
            $data['nameNewsArticle'] = trim($newsArticleInfo[$this->config->get('config_current_country')]['name']);
            foreach ($newsArticleInfo[$this->config->get('config_current_country')]['array'] as $key => $item) {
                if ($item['is_news']) {
                    $info = $this->model_catalog_news->getNewsStory($item['id']);
                    $href = $this->url->link('information/news/info', 'news_id=' . $item['id']);
                } else {
                    $info = $this->model_catalog_information->getInformation($item['id']);
                    $info['status'] = $info['is_visible'];
                    $href = $this->url->link('information/articles/view', 'article_id=' . $item['id']);
                }

                if ($info['status']) {
                    if (is_file(DIR_IMAGE . $info['image'])) {
                        $image = $this->model_tool_image->resize(
                            $info['image'],
                            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
                            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')
                        );
                    } else {
                        $image = $this->model_tool_image->resize(
                            'placeholder.png',
                            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
                            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')
                        );
                    }

                    if ($this->config->get('news_setting')) {
                        $news_setting = $this->config->get('news_setting');
                    }else{
                        $news_setting['description_limit'] = '300';
                        $news_setting['news_thumb_width'] = '220';
                        $news_setting['news_thumb_height'] = '220';
                    }

                    $data['newsArticles'][$key] = [
                        'image'       => $image,
                        'title'       => $info['title'],
                        'description' => utf8_substr(
                            strip_tags(html_entity_decode($info['description'], ENT_QUOTES, 'UTF-8')),
                            0,
                            $news_setting['description_limit']
                        ),
                        'href'        => $href,
                        'data'        => $info['date_added']
                    ];
                }
            }
            usort( $data['newsArticles'], function ($item1, $item2) {
                return $item1['data'] <=> $item2['data'];
            });

            if (!empty($newsArticleInfo[$this->config->get('config_current_country')]['allCategory']) &&
                $newsArticleInfo[$this->config->get('config_current_country')]['allCategory']['status'] &&
                $newsArticleInfo[$this->config->get('config_current_country')]['allCategory']['text'] &&
                $newsArticleInfo[$this->config->get('config_current_country')]['allCategory']['link']
            ) {
                $data['linkNewsArticles'] =  $newsArticleInfo[$this->config->get('config_current_country')]['allCategory']['link'];
                $data['textNewsArticles'] =  $newsArticleInfo[$this->config->get('config_current_country')]['allCategory']['text'];
            }
        }

        $tabInfo = $this->config->get('config_tab_main_page');
        if (!empty($tabInfo[$this->config->get('config_current_country')]['array'])) {
            foreach ($tabInfo[$this->config->get('config_current_country')]['array'] as $key => $item) {
                $data['tabs'][] = [
                    'name' => $item['name'],
                    'text' => html_entity_decode($item['text'], ENT_QUOTES, 'UTF-8'),
                ];
            }
        }
        $data['subscribe'] = $this->load->controller('extension/module/subscribe');
		$this->response->setOutput($this->load->view('common/home', $data));
	}
}
