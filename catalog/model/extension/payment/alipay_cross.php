<?php

class ModelExtensionPaymentAlipayCross extends Model
{
	public $httpsVerifyUrl = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    public $httpsVerifyUrlTest = 'https://openapi.alipaydev.com/gateway.do?service=notify_verify&';
    public $aliPayConfig;

	public function getMethod($address, $total)
    {
		$this->load->language('extension/payment/alipay_cross');

		$query = $this->db->query(
		    "SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone 
		    WHERE geo_zone_id = '" . (int)$this->config->get('payment_alipay_cross_geo_zone_id') . "' 
		    AND country_id = '" . (int)$address['country_id'] . "' 
		    AND (zone_id = '" . (int)$address['zone_id'] . "' 
		    OR zone_id = '0')"
        );

		if ($this->config->get('payment_alipay_cross_total') > 0 &&
            $this->config->get('payment_alipay_cross_total') > $total
        ) {
			$status = false;
		} elseif (!$this->config->get('payment_alipay_cross_geo_zone_id') || $query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$methodData = [];

		if ($status) {
			$methodData = [
				'code'       => 'alipay_cross',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_alipay_cross_sort_order')
			];
		}

		return $methodData;
	}

	public function buildRequestMysign($paraSort)
    {
		$preStr = $this->createLinkstring($paraSort);

		$mySign = "";
		switch (strtoupper(trim($this->aliPayConfig['sign_type']))) {
			case "MD5" :
				$mySign = $this->md5Sign($preStr, $this->aliPayConfig['key']);
				break;
			default :
				$mySign = "";
		}

		return $mySign;
	}


	public function buildRequestPara($aliPayConfig, $paraTemp)
    {
		$this->aliPayConfig = $aliPayConfig;

		$paraFilter = $this->paraFilter($paraTemp);

		$paraSort = $this->argSort($paraFilter);

		$mySign = $this->buildRequestMysign($paraSort);

		$paraSort['sign'] = $mySign;
        $paraSort['sign_type'] = strtoupper(trim($this->aliPayConfig['sign_type']));

		return $paraSort;
	}

	public function verifyNotify($aliPayConfig)
    {
		$this->aliPayConfig = $aliPayConfig;

		if(empty($_POST)) {
			return false;
		}
		else {
			$isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);

			$responseTxt = 'false';
			if (! empty($_POST["notify_id"])) {
				$responseTxt = $this->getResponse($_POST["notify_id"]);
			}

			//Veryfy
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
				return true;
			} else {
				$this->log->write($responseTxt);
				return false;
			}
		}
	}

	public function getSignVeryfy($paraTemp, $sign)
    {
		$paraFilter = $this->paraFilter($paraTemp);

		$paraSort = $this->argSort($paraFilter);

		$preStr = $this->createLinkstring($paraSort);

		switch (strtoupper(trim($this->aliPayConfig['sign_type']))) {
			case "MD5" :
				$isSgin = $this->md5Verify($preStr, $sign, $this->aliPayConfig['key']);
				break;
			default :
				$isSgin = false;
		}

		return $isSgin;
	}

	public function getResponse($notify_id)
    {
		$partner = trim($this->aliPayConfig['partner']);
		$veryfyUrl = $this->config->get('payment_alipay_cross_test') == "sandbox" ? $this->httpsVerifyUrlTest : $this->httpsVerifyUrl;
        $veryfyUrl .= "partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = $this->getHttpResponseGET($veryfyUrl, $this->aliPayConfig['cacert']);

		return $responseTxt;
	}

	public function createLinkstring($para)
    {
		$arg  = "";
		while (list ($key, $val) = each ($para)) {
			$arg .= $key . "=" . $val . "&";
		}
		//remove the last char '&'
		$arg = substr($arg, 0, count($arg)-2);

		return $arg;
	}

	public function paraFilter($para)
    {
		$paraFilter = [];
		while (list ($key, $val) = each ($para)) {
			if($key == "sign" || $key == "sign_type" || $val == "")continue;
			else	$paraFilter[$key] = $para[$key];
		}
		return $paraFilter;
	}

	public function argSort($para)
    {
		ksort($para);
		reset($para);
		return $para;
	}

	public function getHttpResponseGET($url,$cacertUrl)
    {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, 0 );
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_CAINFO,$cacertUrl);
		$responseText = curl_exec($curl);
		if (!$responseText) {
			$this->log->write('ALIPAY NOTIFY CURL_ERROR: ' . var_export(curl_error($curl), true));
		}
		curl_close($curl);

		return $responseText;
	}

	public function md5Sign($prStr, $key)
    {
        $prStr .= $key;
		return md5($prStr);
	}

	public function md5Verify($prStr, $sign, $key)
	{
        $prStr .= $key;
		$mysgin = md5($prStr);

		if ($mysgin == $sign) {
			return true;
		} else {
			return false;
		}
	}
}

