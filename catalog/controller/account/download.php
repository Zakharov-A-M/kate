<?php

class ControllerAccountDownload extends Controller
{
    public function index()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/download');
            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->language('account/download');
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
            'text' => $this->language->get('text_downloads'),
            'href' => $this->url->link('account/download')
        ];

        $this->load->model('account/download');

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['downloads'] = [];

        $downloadTotal = $this->model_account_download->getTotalDownloads();
        $results = $this->model_account_download->getDownloads(
            ($page - 1) * $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'),
            $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')
        );

        foreach ($results as $result) {
            if (is_file(DIR_DOWNLOAD . $result['filename'])) {
                $size = filesize(DIR_DOWNLOAD . $result['filename']);
                $i = 0;
                $suffix = array(
                    'B',
                    'KB',
                    'MB',
                    'GB',
                    'TB',
                    'PB',
                    'EB',
                    'ZB',
                    'YB'
                );

                while (($size / 1024) > 1) {
                    $size = $size / 1024;
                    $i++;
                }

                $data['downloads'][] = [
                    'order_id' => $result['order_id'],
                    'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                    'name' => $result['name'],
                    'size' => round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i],
                    'href' => $this->url->link('account/download/download', 'download_id=' . $result['download_id'])
                ];
            }
        }

        $pagination = new Pagination();
        $pagination->total = $downloadTotal;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
        $pagination->url = $this->url->link('account/download', 'page={page}');

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf(
            $this->language->get('text_pagination'),
            ($downloadTotal) ? (($page - 1) * 10) + 1 : 0,
            ((($page - 1) * 10) > ($downloadTotal - 10)) ? $downloadTotal : ((($page - 1) * 10) + 10),
            $downloadTotal,
            ceil($downloadTotal / 10)
        );
        $data['continue'] = $this->url->link('account/account');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/download', $data));
    }

    /**
     * Download file for register user
     */
    public function download()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/download');
            $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->model('account/download');

        if (isset($this->request->get['download_id'])) {
            $downloadId = $this->request->get['download_id'];
        } else {
            $downloadId = 0;
        }

        $downloadInfo = $this->model_account_download->getDownload($downloadId);
        if ($downloadInfo) {
            $file = DIR_DOWNLOAD . $downloadInfo['filename'];
            $mask = basename($downloadInfo['mask']);
            if (!headers_sent()) {
                if (file_exists($file)) {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($file));

                    if (ob_get_level()) {
                        ob_end_clean();
                    }

                    readfile($file, 'rb');
                    $this->model_account_download->addDownloadReport(
                        $downloadId,
                        $this->request->server['REMOTE_ADDR']
                    );

                    exit();
                } else {
                    exit('Error: Could not find file ' . $file . '!');
                }
            } else {
                exit('Error: Headers already sent out!');
            }
        } else {
            $this->response->redirect($this->url->link('account/download'));
        }
    }
}
