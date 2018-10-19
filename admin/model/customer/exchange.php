<?php

class ModelCustomerExchange extends Model
{
    const PARTNERS = SERVER_1C_TEST . 'partners';
    const ORDER = SERVER_1C_TEST . 'Invoices';

    /**
     * Send request to 1C
     *
     * @param string $exchangeUrl
     * @return mixed
     * @throws Exception
     */
    public function execRequest(string $exchangeUrl)
    {
        $query = "SELECT * FROM " . DB_PREFIX . "exchange";
        $result = $this->db->query($query);
        $login = $result->row['login'];
        $password = $result->row['password'];
        $headers = ['all: 1'];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $exchangeUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_USERPWD, "$login:$password");
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            throw new Exception('Error http request');
        }

        curl_close($curl);
        $responseInfo = json_decode($response, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new Exception('Invalid json format');
        }

        return $responseInfo;
    }

    /**
     * Get amount partners from 1C
     *
     * @return mixed|string
     * @throws Exception
     */
    public function getPartners()
    {
        $result = $this->execRequest(self::PARTNERS);
        return $result['data'];
    }
    /**
     * Get order status from 1C
     *
     * @return mixed|string
     * @throws Exception
     */
    public function getOrder()
    {
        $result = $this->execRequest(self::ORDER);
        return $result['data'];
    }

    /**
     * Update order status
     *
     * @param string $guid
     * @param array $data
     * @return mixed|string
     */
    public function updateOrder($guid, $data)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order WHERE GUID = '" . $guid . "'");
        if ($query->row['order_status_id'] != $data['statusId']) {
            $this->db->query("UPDATE `" . DB_PREFIX .
                "order` SET order_status_id = '" . (int)$data['statusId'] . "',
            date_modified = NOW() WHERE GUID = '" . $guid . "'");
            if ($data['statusId'] == 5) {
                $this->load->controller('mail/order/implementationOrder', [
                    'guid'        => $guid,
                    'orderStatus' => (int)$data['statusId']
                ]);
            }

            $this->load->model('customer/customer');
            $customerInfo = $this->model_customer_customer->getCustomer($query->row['customer_id']);
            $this->load->model('localisation/language');
            $languageInfo = $this->model_localisation_language->getLanguage($customerInfo['language_id']);
            if ($languageInfo) {
                $languageCode = $languageInfo['code'];
            } else {
                $languageCode = $this->config->get('config_language');
            }

            $language = new Language($languageCode);
            $language->load($languageCode);
            $language->load('customer/customer');
            $comment = sprintf($language->get('text_change_status'));

            $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET 
                order_id = '" . (int)$query->row['order_id'] . "', 
                order_status_id = '" . (int)$data['statusId'] . "', 
                notify = 1, comment = '" . $this->db->escape($comment) . "', 
                date_added = NOW()"
            );
        }

        if ($query->row['score'] != $data['score']) {
            $this->db->query("UPDATE `" . DB_PREFIX . "order` 
                SET score = '" . $data['score'] . "',
                date_modified = NOW() WHERE GUID = '" . $guid . "'"
            );
            $this->load->controller('mail/order/sendFile', [
                'orderId'     => (int)$query->row['order_id'],
                'customerId'  => (int)$query->row['customer_id'],
                'score'       => $data['score']
            ]);
        }

        return true;
    }

    /**
     * Parse and save or edit partners from 1C
     *
     * @param array $responseItem
     * @return array
     */
    public function parsePartnersResponse(array $responseItem)
    {
        $data = [];
        $data['GUID'] = $responseItem['GUIDUser'];
        $data['GUIDPartner'] = !empty($responseItem['GUIDPartner']) ? $responseItem['GUIDPartner'] : '';
        $data['OKPO']  = !empty($responseItem['OKPO']) ? $responseItem['OKPO'] : $responseItem['KPP'];
        $data['customer_group_id'] = ($responseItem['UserType'] == 'UR' ?  3 : 2);
        $data['login'] = $responseItem['login'];
        $data['password']  = !empty($responseItem['password']) ? $responseItem['password'] : '';
        $data['status']            = 1;
        $data['safe']              = 0;
        if ($responseItem['registred'] == 'true') {
            $data['registred'] = 1;
        } else {
            $data['registred'] = 0;
        }
        $data['patronymic'] = !empty($responseItem['patronymic']) ? $responseItem['patronymic'] : '';
        $data['lastname']   = !empty($responseItem['lastname']) ? $responseItem['lastname'] : '';
        $data['firstname']  = !empty($responseItem['name']) ? $responseItem['name'] : '';
        $data['telephone']  = !empty($responseItem['phone']) ? $responseItem['phone'] : '';
        $data['email']      = !empty($responseItem['email']) ? $responseItem['email'] : $data['login'];
        $data['newsletter'] = !empty($responseItem['newsletter']) ? $responseItem['newsletter'] : 0;
        $data['custom_field'] =  [
            5  => $responseItem['NameFull'],
            6  => $responseItem['INN'],
            7  => (!empty($responseItem['OKPO']) ? $responseItem['OKPO'] : $responseItem['KPP']),
            9  => $responseItem['legal_address'],
            8  => $responseItem['checking_account'],
            10 => $responseItem['serial'],
            11 => $responseItem['passport_number'],
            12 => $responseItem['issued'],
            13 => $responseItem['place_residence'],
            14 => $responseItem['position_work'],
            15 => $responseItem['delivery_address'],
            16 => $responseItem['issued_by']
        ];

        $this->load->model('customer/customer');

        if ($this->model_customer_customer->getCountCustomerGUID($data['GUID']) > 0) {
            $customerId = $this->model_customer_customer->getIDCustomerGUID($data['GUID']);
            $registred = $this->model_customer_customer->checkRegisteredUser($customerId);
            $this->model_customer_customer->editCustomer($customerId, $data);
            $registeredNow = $this->model_customer_customer->checkRegisteredUser($customerId);
            if ($data['registred'] && ($registred != $registeredNow) && ($registeredNow)) {
                $this->load->controller('mail/customer/confirmation1C', [
                    'customer_id' => $customerId,
                ]);
            }
        } else {
            $customerId = $this->model_customer_customer->addCustomer1C($data);
            if (!empty($customerId)) {
                $this->load->controller('mail/customer/registeredUser', [
                    'customer_id' => $customerId,
                    'password' => $data['password'],
                    'email' => $data['email'],
                ]);
            }
        }
        return $data;
    }
}
