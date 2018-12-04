<?php

class ControllerCommonOboi extends Controller
{
    /**
     * View start page
     */
    public function index()
    {
        $bannerInfo = $this->config->get('config_banner_liquid_wallpaper');
        if (!empty($bannerInfo[$this->config->get('config_current_country')]['array']) &&
            $bannerInfo[$this->config->get('config_current_country')]['status']
        ) {
            foreach ($bannerInfo[$this->config->get('config_current_country')]['array'] as $key => $item) {
                if (is_file(DIR_IMAGE . $item['image'])) {
                    $image = '/image/'. $item['image'];

                    $data['banners'][$key] = [
                        'title'      => $item['title'],
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

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $data['subscribe'] = $this->load->controller('extension/module/subscribe');
        $this->response->setOutput($this->load->view('common/oboi', $data));
    }
}
