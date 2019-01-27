<?php

class ControllerCommonPotolki extends Controller
{
    /**
     * View start page
     */
    public function index()
    {
        $bannerInfo = $this->config->get('config_banner_stretch_ceiling');
        if (!empty($bannerInfo[$this->config->get('config_current_country')]['array']) &&
            $bannerInfo[$this->config->get('config_current_country')]['status']
        ) {
            foreach ($bannerInfo[$this->config->get('config_current_country')]['array'] as $key => $item) {
                if (is_file(DIR_IMAGE . $item['image'])) {
                    $image = '/image/'. $item['image'];

                    $data['banners'][$key] = [
                        'title'      => $item['title'],
                        'link'       => $item['link'],
                        'image'      => $image,
                        'sort_order' => $item['sort_order']
                    ];
                }
            }
            if (!empty($data['banners'])) {
                usort( $data['banners'], function ($item1, $item2) {
                    return $item1['sort_order'] <=> $item2['sort_order'];
                });
            }
        }


        $potolkiInfo = $this->config->get('config_viewed_stretch_ceiling');
        if (!empty($potolkiInfo[$this->config->get('config_current_country')]['array']) &&
            $potolkiInfo[$this->config->get('config_current_country')]['status']
        ) {
            foreach ($potolkiInfo[$this->config->get('config_current_country')]['array'] as $key => $item) {
                if (is_file(DIR_IMAGE . $item['image'])) {
                    $image = '/image/'. $item['image'];

                    $data['viewPoloks'][$key] = [
                        'title'      => $item['title'],
                        'image'      => $image,
                        'sort_order' => $item['sort_order']
                    ];
                }
            }
            $data['namePoloks'] = html_entity_decode($potolkiInfo[$this->config->get('config_current_country')]['name'], ENT_QUOTES, 'UTF-8');
            if (!empty($data['viewPoloks'])) {
                usort( $data['viewPoloks'], function ($item1, $item2) {
                    return $item1['sort_order'] <=> $item2['sort_order'];
                });
            }
        }

        $data['text'] = html_entity_decode($this->config->get('config_stretch_ceiling_text')[$this->config->get('config_current_country')]['text'], ENT_QUOTES, 'UTF-8');
        $this->document->setTitle(html_entity_decode($this->config->get('config_stretch_ceiling_text')[$this->config->get('config_current_country')]['title'], ENT_QUOTES, 'UTF-8'));

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $data['subscribe'] = $this->load->controller('extension/module/subscribe');
        $this->response->setOutput($this->load->view('common/potolki', $data));
    }
}
