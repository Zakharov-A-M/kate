<?php

class ModelExtensionModuleLaybuyLayout extends Model
{
    /**
     * Get status label
     *
     * @param $id
     * @return mixed
     */
    public function getStatusLabel($id)
    {
        $statuses = $this->getTransactionStatuses();

        foreach ($statuses as $status) {
            if ($status['status_id'] == $id && $status['status_name'] != '') {
                return $status['status_name'];
            }
        }

        return $id;
    }

    /**
     * Get transaction by orderId
     *
     * @param int $orderId
     * @return mixed
     */
    public function getTransactionByOrderId($orderId)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "laybuy_transaction` 
		    WHERE `order_id` = '" . (int)$orderId . "' ORDER BY `laybuy_ref_no` DESC LIMIT 1"
        );

        return $query->row;
    }

    /**
     * Get transaction statuses
     *
     * @return array
     */
    public function getTransactionStatuses()
    {
        $this->load->language('extension/payment/laybuy');

        $transactionStatuses = [
            [
                'status_id' => 1,
                'status_name' => $this->language->get('text_status_1')
            ],
            [
                'status_id' => 5,
                'status_name' => $this->language->get('text_status_5')
            ],
            [
                'status_id' => 7,
                'status_name' => $this->language->get('text_status_7')
            ],
            [
                'status_id' => 50,
                'status_name' => $this->language->get('text_status_50')
            ],
            [
                'status_id' => 51,
                'status_name' => $this->language->get('text_status_51')
            ]
        ];

        return $transactionStatuses;
    }

    /**
     * Is lay buy order
     *
     * @param int $orderId
     * @return bool
     */
    public function isLayBuyOrder($orderId)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "laybuy_transaction` 
		WHERE `order_id` = '" . (int)$orderId . "'"
        );

        if ($query->num_rows) {
            return true;
        } else {
            return false;
        }
    }
}