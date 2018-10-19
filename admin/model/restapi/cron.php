<?php

class ModelRestapiCron extends Model
{
    const ADD_USER = 1;
    const ADDINVOICES = 2;
    const APDATEPAYMENT = 3;

    /**
     * Get all notification for 1C
     * Send current json to 1C
     */
    public function sendNotification()
    {
       $notifications = $this->getCronNotification();
       foreach ($notifications as $notification) {
           switch ((int)$notification['event']) {
               case self::ADD_USER :
                   $response = $this->execRequest($notification['json'], 'ADDUser');
                   if ($response) {
                       if ($response['data'][0]['done']) {
                           $this->db->query("UPDATE " . DB_PREFIX . "customer SET GUID = '" . $response['data'][0]['GUIDUser'] . "' WHERE customer_id = '" . (int)$notification['order_user_id'] . "'");
                           $this->setStatusNotification($notification['notification_id']);
                       }
                   }
                   break;
               case self::ADDINVOICES :
                   $response = $this->execRequest($notification['json'], 'ADDInvoices');
                   if ($response) {
                       if (isset($response['data'][0]['done']) && $response['data'][0]['done'] === "true") {
                           $this->db->query("UPDATE " . DB_PREFIX . "order SET GUID = '" . $this->db->escape((string)$response['data'][0]['InvoiceGUID']) . "' WHERE order_id = '" . (int)$notification['order_user_id'] . "'");
                           $this->setStatusNotification($notification['notification_id']);
                       }
                   }
                   break;
               case self::APDATEPAYMENT :
                   $response = $this->execRequest($notification['json'], 'ApdatePayment');
                   if ($response) {
                       if ($response['data'][0]['Error'] == 0) {
                           $this->setStatusNotification($notification['notification_id']);
                       }
                   }
                   break;
           }
           //die();
       }
    }

    /***
     * Send json to 1C notification
     *
     * @param string $json
     * @param string $exchangeUrl
     * @return mixed
     */
    public function execRequest(string $json, string $exchangeUrl)
    {
        $curl = curl_init();

        $query = "SELECT * FROM " . DB_PREFIX . "exchange";
        $result = $this->db->query($query);
        $login = $result->row['login'];
        $password = $result->row['password'];

        curl_setopt($curl, CURLOPT_URL, SERVER_1C_TEST . $exchangeUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERPWD, "$login:$password");
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Error http request';
        }

        curl_close($curl);
        $responseInfo = json_decode($response, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            echo 'Invalid json format' . $response;
        }

        return $responseInfo;
    }

    /**
     * Get notification
     *
     * @return mixed
     */
    public function getCronNotification()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cron_notification cn WHERE cn.status = 0");

        return $query->rows;
    }

    /**
     * Set status = 1 if success
     *
     * @param $id
     */
    public function setStatusNotification($id)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "cron_notification SET status = 1 WHERE notification_id = '" . (int)$id . "'");
    }
}
