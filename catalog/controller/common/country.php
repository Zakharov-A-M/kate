<?php

class ControllerCommonCountry extends Controller
{
    /**
     * @return string
     */
    public function index()
    {
        $this->load->language('common/language');

        $data['action'] = $this->url->link('common/country/language', '', $this->request->server['HTTPS']);

        $this->load->model('localisation/country');

        $data['countries'] = array();

        $results = $this->model_localisation_country->getCountriesForMenu();

        foreach ($results as $result) {
            if ($this->config->get('config_current_country') == $result['country_id']) {
                $data['country_current'] = [
                    'name'        => $result['name'],
                    'langCode'    => $result['langCode'],
                ];
            }
            $data['countries'][] = [
                'country_id'  => $result['country_id'],
                'name'        => $result['name'],
                'langCode'    => $result['langCode'],
                'currCode'    => $result['currCode'],
                'domain'      => $result['domain']
            ];
        }

        return $this->load->view('common/country', $data);
    }

    /**
     * Change country and language
     */
    public function language()
    {
        if (isset($this->request->post['redirect'])) {
            $this->response->redirect($this->request->post['redirect']);
        } else {
            $this->response->redirect($this->url->link('common/home'));
        }
    }
}
