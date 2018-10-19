<?php
class ModelAccountCustomer extends Model
{
    /**
     * Registered new customer
     *
     * @param array $data
     * @return int
     * @throws Exception
     */
    public function addCustomer(array $data)
    {
        if (isset($data['customer_group_id']) &&
            is_array($this->config->get('config_customer_group_display')) &&
            in_array($data['customer_group_id'], $this->config->get('config_customer_group_display'))
        ) {
            $customerGroupId = $data['customer_group_id'];
        } else {
            $customerGroupId = $this->config->get('config_customer_group_id');
        }

        $this->load->model('account/customer_group');
        $customerGroupInfo = $this->model_account_customer_group->getCustomerGroup($customerGroupId);

        $this->db->query("INSERT INTO " . DB_PREFIX . "customer SET customer_group_id = '" . (int)$customerGroupId . "', store_id = '" . (int)$this->config->get('config_store_id') . "', language_id = '" . (int)$this->config->get('config_language_id') . "', firstname = '" . $this->db->escape((string)$data['firstname']) . "', lastname = '" . $this->db->escape((string)$data['lastname']) . "', patronymic = '" . $this->db->escape((string)$data['patronymic']) . "', email = '" . $this->db->escape((string)$data['email']) . "', telephone = '" . $this->db->escape((string)$data['telephone']) . "', custom_field = '" . $this->db->escape(isset($data['custom_field']['account']) ? json_encode($data['custom_field']['account']) : '') . "', salt = '', password = '" . $this->db->escape(password_hash($data['password'], PASSWORD_DEFAULT)) . "', newsletter = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', status = '" . (int)!$customerGroupInfo['approval'] . "', date_added = NOW()");
        $customerId = $this->db->getLastId();
        if ($customerGroupInfo['approval']) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_approval` SET customer_id = '" . (int)$customerId . "', type = 'customer', date_added = NOW()");
            $this->editToken($data['email'], md5($data['email']));
        } else {
            $this->sendAccount1C($customerId, $data);
        }

        return $customerId;
    }

    /**
     * Check current password user
     *
     * @param $password
     * @return bool
     */
	public function checkCurrentPassword($password)
    {
        $queryCustomer = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$this->customer->getId() . "'"
        );
        if (!empty($queryCustomer->row) && !empty($queryCustomer->row['password'])) {
             return password_verify($password, $queryCustomer->row['password']);
        }
        return false;
    }

    /**
     * Save address new user
     *
     * @param $userId
     * @param $address
     */
	public function addNewAddress($userId, $address)
    {
        $address = [
            15 => [
                $address
            ]
        ];
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET  custom_field = '" . json_encode($address, JSON_UNESCAPED_UNICODE) . "' WHERE customer_id = '" . (int)$userId . "'");
    }

    /**
     * Save new address from user
     *
     * @param $userId
     * @param $address
     */
    public function addNewAddressUser($userId, $address)
    {
        $user = $this->getCustomer($userId);
        if ($user) {
            $customerInfo = json_decode($user['custom_field'], true);
            if ($customerInfo) {
                if (!empty($customerInfo[15])) {
                    array_push($customerInfo[15], $address);
                } else {
                    $customerInfo[15] = [
                        $address
                    ];
                }
                $this->db->query("UPDATE " . DB_PREFIX . "customer SET  custom_field = '" . json_encode($customerInfo, JSON_UNESCAPED_UNICODE) . "' WHERE customer_id = '" . (int)$userId . "'");
            } else {
                $this->addNewAddress($userId, $address);
            }
        }
    }

    /**
     * For FX user
     *
     * @param $customer_id
     * @param $data
     */
	public function editCustomerFX($customer_id, $data)
    {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET firstname = '" . $this->db->escape((string)$data['firstname']) . "', lastname = '" . $this->db->escape((string)$data['lastname']) . "', patronymic = '" . $this->db->escape((string)$data['patronymic']) . "', email = '" . $this->db->escape((string)$data['email']) . "', telephone = '" . $this->db->escape((string)$data['telephone']) . "', custom_field = '" . $this->db->escape((isset($data['custom_field']['account_edit'])) ? json_encode($data['custom_field']['account_edit']) : '') . "' WHERE customer_id = '" . (int)$customer_id . "'");
	}

    /**
     * For UR user
     *
     * @param $customer_id
     * @param $data
     */
    public function editCustomerUR($customer_id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET firstname = '" . $this->db->escape((string)$data['firstname']) . "', lastname = '" . $this->db->escape((string)$data['lastname']) . "', patronymic = '" . $this->db->escape((string)$data['patronymic']) . "', email = '" . $this->db->escape((string)$data['email']) . "', telephone = '" . $this->db->escape((string)$data['telephone']) . "', custom_field = '" . $this->db->escape((isset($data['custom_field']['account']) &&  isset($data['custom_field']['account_edit'])) ? json_encode($data['custom_field']['account'] + $data['custom_field']['account_edit']) : '') . "' WHERE customer_id = '" . (int)$customer_id . "'");
    }

	public function editPassword($email, $password) {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET salt = '', password = '" . $this->db->escape(password_hash($password, PASSWORD_DEFAULT)) . "', code = '' WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
	}

	public function editAddressId($customer_id, $address_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
	}

	public function editCode($email, $code) {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET code = '" . $this->db->escape($code) . "' WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
	}

	public function editToken($email, $token) {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET token = '" . $this->db->escape($token) . "' WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
	}

	public function editNewsletter($newsletter) {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET newsletter = '" . (int)$newsletter . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function getCustomer($customer_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row;
	}

	public function getCustomerByEmail($email) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row;
	}

	public function getCustomerByCode($code) {
		$query = $this->db->query("SELECT customer_id, firstname, lastname, email FROM `" . DB_PREFIX . "customer` WHERE code = '" . $this->db->escape($code) . "' AND code != ''");

		return $query->row;
	}

	public function getCustomerByToken($token) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE token = '" . $this->db->escape($token) . "' AND token != ''");

		$this->db->query("UPDATE " . DB_PREFIX . "customer SET token = ''");

		return $query->row;
	}

	public function getTotalCustomersByEmail($email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row['total'];
	}

	public function addTransaction($customer_id, $description, $amount = '', $order_id = 0) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_transaction SET customer_id = '" . (int)$customer_id . "', order_id = '" . (float)$order_id . "', description = '" . $this->db->escape($description) . "', amount = '" . (float)$amount . "', date_added = NOW()");
	}

	public function deleteTransactionByOrderId($order_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_transaction WHERE order_id = '" . (int)$order_id . "'");
	}

	public function getTransactionTotal($customer_id) {
		$query = $this->db->query("SELECT SUM(amount) AS total FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row['total'];
	}

	public function getTotalTransactionsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_transaction WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

	public function getRewardTotal($customer_id) {
		$query = $this->db->query("SELECT SUM(points) AS total FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row['total'];
	}

	public function getIps($customer_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_ip` WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->rows;
	}

	public function addLogin($customer_id, $ip, $country = '') {
		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_ip SET customer_id = '" . (int)$customer_id . "', store_id = '" . (int)$this->config->get('config_store_id') . "', ip = '" . $this->db->escape($ip) . "', country = '" . $this->db->escape($country) . "', date_added = NOW()");
	}

	public function addLoginAttempt($email) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_login WHERE email = '" . $this->db->escape(utf8_strtolower((string)$email)) . "' AND ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_login SET email = '" . $this->db->escape(utf8_strtolower((string)$email)) . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', total = 1, date_added = '" . $this->db->escape(date('Y-m-d H:i:s')) . "', date_modified = '" . $this->db->escape(date('Y-m-d H:i:s')) . "'");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "customer_login SET total = (total + 1), date_modified = '" . $this->db->escape(date('Y-m-d H:i:s')) . "' WHERE customer_login_id = '" . (int)$query->row['customer_login_id'] . "'");
		}
	}

	public function getLoginAttempts($email) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_login` WHERE email = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row;
	}

	public function deleteLoginAttempts($email) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_login` WHERE email = '" . $this->db->escape(utf8_strtolower($email)) . "'");
	}

    /**
     * Send request on order to 1C
     *
     * @param int $id
     * @return mixed
     * @throws Exception
     */
    public function sendAccount1C(int $id)
    {

        $customer = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . $id . "'");
        $country = $this->db->query("SELECT * FROM " . DB_PREFIX . "country
            WHERE country_id = '" . $this->config->get('config_current_country') . "'");
        $data = json_decode($customer->row['custom_field'], true);
        $array = [
            'Users' => [[
                'INN' => empty($data['custom_field']['account_edit'][6]) ? '' :
                    $data['custom_field']['account_edit'][6],
                'UserType' => ($this->config->get('config_customer_group_id') == 2) ? 'FX' : 'UR',
                'OKPO' => empty($data['custom_field']['account_edit'][7]) ? '' :
                    $data['custom_field']['account_edit'][7],
                'NameFull' => empty($data['custom_field']['account'][5]) ? '' :
                    addslashes($data['custom_field']['account'][5]),
                'KPP' => empty($data['custom_field']['account_edit'][7]) ? '' :
                    $data['custom_field']['account_edit'][7],
                'checking_account' => empty($data['custom_field']['account_edit'][8]) ? '' :
                    $data['custom_field']['account_edit'][8],
                'legal_address' => empty($data['custom_field']['account_edit'][9]) ? '' :
                    $data['custom_field']['account_edit'][9],
                'position_work' => empty($data['custom_field']['account_edit'][14]) ? '' :
                    $data['custom_field']['account_edit'][14],
                'country' => $country->row['iso'],
                'login' => $customer->row['email'],
                'password' => '',
                'number' => $id,
                'partnerguid' => empty($customer->row['partner_guid']) ? '' : $customer->row['partner_guid'],
                'registred' => ($customer->row['registred']) ? 'true' : 'false',
                'firstname' => $customer->row['firstname'],
                'lastname' => $customer->row['lastname'],
                'patronymic' => $customer->row['patronymic'],
                'phone' => $customer->row['telephone'],
                'email' => $customer->row['email'],
                'place_residence' => empty($data['custom_field']['account_edit'][13]) ? '' :
                    addslashes($data['custom_field']['account_edit'][13]),
                'delivery_address' => empty($data['custom_field']['account_edit'][15]) ? []:
                    $data['custom_field']['account_edit'][15],
                'passport' => [
                    'number' => empty($data['custom_field']['account_edit'][11]) ? '' :
                        addslashes($data['custom_field']['account_edit'][11]),
                    'serial' => empty($data['custom_field']['account_edit'][10]) ? '' :
                        addslashes($data['custom_field']['account_edit'][10]),
                    'issued' => empty($data['custom_field']['account_edit'][12]) ? '' :
                        $this->changeDate($data['custom_field']['account_edit'][12]),
                    'issued_by' => empty($data['custom_field']['account_edit'][16]) ? '' :
                        addslashes($data['custom_field']['account_edit'][16]),
                ]
            ]],
        ];

        $array = json_encode($array, JSON_UNESCAPED_UNICODE);
        $this->load->model('restapi/cron');
        $this->model_restapi_cron->addNotification($array, 1, $id);
        return $array;
    }

    /**
     * Get date this another format for 1C
     *
     * @param string $date
     * @return string
     */
    public function changeDate(string $date)
    {
        $date = \DateTime::createFromFormat('d:m:Y', $date);
        if ($date) {
            return $date->format('Ymd');
        }
        return '';
    }

    /**
     * Approval user from token
     *
     * @param string $token
     * @throws Exception
     */
    public function approveUser(string $token)
    {
        $customer = $this->getCustomerByToken($token);
        if (!empty($customer)) {
            $this->changeStatusUser($customer['customer_id']);
            $this->sendAccount1C($customer['customer_id']);
        }
    }

    /**
     * Change status user
     *
     * @param int $customerId
     * @throws Exception
     */
    public function changeStatusUser(int $customerId)
    {
        $this->db->query('
            UPDATE ' . DB_PREFIX . 'customer SET status = 1  
            WHERE customer_id = ' . $customerId
        );
    }
}
