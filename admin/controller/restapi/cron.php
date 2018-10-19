<?php

class ControllerRestapiCron extends Controller
{
    public function index()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'GET') {
            $this->load->model('restapi/cron');
            $this->model_restapi_cron->sendNotification();
            $this->load->model('mail/mail');
            $this->model_mail_mail->sendNotifications();
            $json = ['status' => true, 'process' => 'Данные отправлены в 1С'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } else {
            $json = ['status' => false, 'description' => 'Данный введенные не верны!'];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
        }
    }
}
