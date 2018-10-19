<?php

/**
 * Save message from email
 *
 * Class ModelMailMail
 */
class ModelMailMail extends Model
{
    /**
     * Save notification and send them on email
     *
     * @param array $data
     */
    public function saveMessageMail(array $data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "cron_email 
            SET `to` = '" . $data['to'] . "',
            sender = '" . $data['store'] . "', 
            subject = '" . $data['subject'] . "', 
            html = '" . $data['html'] . "'"
        );
        $id = $this->db->getLastId();
        if (!empty($data['file'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "cron_email SET attachment = '" . $this->db->escape((string)$data['file']) . "' WHERE id = '" . (int)$id . "'");
        }
    }

    /**
     * Send notification on the email
     */
    public function sendNotifications()
    {
        $notifications = $this->getAllNotification();
        if ($notifications) {
            foreach ($notifications as $notification) {
                /** @var Mail $mail */
                $mail = new Mail($this->config->get('config_mail_engine'));
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                $mail->setTo($notification['to']);
                $mail->setFrom($this->config->get('config_email')[$this->config->get('config_country_id')]);
                $mail->setSender($notification['sender']);
                if (!empty($notification['attachment'])) {
                    if (is_file(DIR_IMAGE . 'catalog/' . $notification['attachment'])) {
                        $mail->addAttachment(DIR_IMAGE . 'catalog/' . $notification['attachment']);
                    } else {
                        continue;
                    }
                }
                $mail->setSubject($notification['subject']);
                $mail->setHtml($notification['html']);
                $mail->send();
                $this->db->query("UPDATE " . DB_PREFIX . "cron_email SET status = 1 WHERE id = '" . (int)$notification['id'] . "'");
            }
        }
    }

    /**
     * Get all notifications
     */
    public function getAllNotification()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cron_email WHERE status = 0");
        return $query->rows;
    }

}
