<?php

class ModelRestapiCron extends Model
{
    /**
     * Add json notification from 1C
     *
     * @param $json text message
     * @param $event number event
     * @param $id ID user or order
     */
    public function addNotification($json, $event, $id)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "cron_notification SET event = '" . (int)$event . "', json = '" . (string)$json . "', order_user_id = '" . (int)$id . "', date_added = NOW()");
    }
}
