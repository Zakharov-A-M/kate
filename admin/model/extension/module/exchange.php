<?php

    class ModelExtensionModuleExchange extends Model
    {
        const MODULE_ROUTE = "/index.php?route=extension/module/exchange&token=";

        public function isConfigured()
        {
            $query = "SELECT * FROM " . DB_PREFIX . "exchange";
            $result = $this->db->query($query);

            return ($result->num_rows > 0) ? true : false;
        }

        public function generateToken()
        {
            $chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
            $max=32;
            $size=StrLen($chars)-1;
            $password=null;
            while($max--) {
                $password .= $chars[rand(0,$size)];
            }
            return $password;
        }

        public function saveToken()
        {
            $token = $this->generateToken();
            $query = "UPDATE " . DB_PREFIX . "exchange SET token='" . $token . "'";
            $this->db->query($query);
        }

        public function saveCredentials($login, $password)
        {
            $query = "UPDATE " . DB_PREFIX . "exchange SET login='" . $login . "', password='" . $password . "'";
            $this->db->query($query);
        }

        public function getData()
        {
            $query = "SELECT * FROM " . DB_PREFIX . "exchange";
            $result = $this->db->query($query);

            return $result->row;
        }


        public function configureModule($login, $password)
        {
            $token = $this->generateToken();
            $this->db->query("INSERT INTO `" . DB_PREFIX . "exchange`  (token,login,password,route,error) values ('" . $token ."','" . $login ."','" . $password ."','" . self::MODULE_ROUTE ."',0)");
            return $this->getData();

        }

        public function moduleSettings($post)
        {
            $login = $post['ex_login'];
            $password = $post['ex_password'];
            if ($this->isConfigured()) {
                $this->saveCredentials($login, $password);
            } else {
                $this->configureModule($login, $password);
            }
        }

        public function getStatistic()
        {
            $sql = "SELECT error, updated_at from " . DB_PREFIX . "exchange";
            $result = $this->db->query($sql);

            return $result->row;
        }
    }