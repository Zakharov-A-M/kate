<?php

class ModelExtensionPaymentAlipay extends Model
{
	private $apiMethodName="alipay.trade.page.pay";
	private $postCharset = "UTF-8";
	private $aliPaySdkVersion = "alipay-sdk-php-20161101";
	private $apiVersion="1.0";
	private $logFileName = "alipay.log";
	private $gatewayUrl = "https://openapi.alipay.com/gateway.do";
	private $aliPayPublicKey;
	private $privateKey;
	private $appId;
	private $notifyUrl;
	private $returnUrl;
	private $format = "json";
	private $signType = "RSA2";

	private $apiParas = [];

	public function getMethod($address, $total)
    {
		$this->load->language('extension/payment/alipay');

		$query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone 
            WHERE geo_zone_id = '" . (int)$this->config->get('payment_alipay_geo_zone_id') . "' 
            AND country_id = '" . (int)$address['country_id'] . "' 
            AND (zone_id = '" . (int)$address['zone_id'] . "' 
            OR zone_id = '0')"
        );

		if ($this->config->get('payment_alipay_total') > 0 && $this->config->get('payment_alipay_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_alipay_geo_zone_id') || $query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$methodData = [];

		if ($status) {
			$methodData = array(
				'code'       => 'alipay',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_alipay_sort_order')
			);
		}

		return $methodData;
	}

	private function setParams($aliPayConfig)
    {
		$this->gatewayUrl = $aliPayConfig['gateway_url'];
		$this->appId = $aliPayConfig['app_id'];
		$this->privateKey = $aliPayConfig['merchant_private_key'];
		$this->aliPayPublicKey = $aliPayConfig['alipay_public_key'];
		$this->postCharset = $aliPayConfig['charset'];
		$this->signType = $aliPayConfig['sign_type'];
		$this->notifyUrl = $aliPayConfig['notify_url'];
		$this->returnUrl = $aliPayConfig['return_url'];

		if (empty($this->appId)||trim($this->appId)=="") {
			throw new Exception("appid should not be NULL!");
		}
		if (empty($this->privateKey)||trim($this->privateKey)=="") {
			throw new Exception("private_key should not be NULL!");
		}
		if (empty($this->aliPayPublicKey)||trim($this->aliPayPublicKey)=="") {
			throw new Exception("alipay_public_key should not be NULL!");
		}
		if (empty($this->postCharset)||trim($this->postCharset)=="") {
			throw new Exception("charset should not be NULL!");
		}
		if (empty($this->gatewayUrl)||trim($this->gatewayUrl)=="") {
			throw new Exception("gateway_url should not be NULL!");
		}
	}

	public function pagePay($builder, $config)
    {
		$this->setParams($config);
        $bizContent = null;
		if(!empty($builder)){
			$bizContent = json_encode($builder,JSON_UNESCAPED_UNICODE);
		}

		$log = new Log($this->logFileName);
		$log->write($bizContent);

		$this->apiParas["biz_content"] = $bizContent;

		$response = $this->pageExecute($this, "post");
		$log = new Log($this->logFileName);
		$log->write("response: " . var_export($response,true));

		return $response;
	}

    public function check($arr, $config)
    {
		$this->setParams($config);

		$result = $this->rsaCheckV1($arr, $this->signType);

		return $result;
	}

	public function pageExecute($request, $httpmethod = "POST")
    {
		$iv = $this->apiVersion;

		$sysParams["app_id"] = $this->appId;
		$sysParams["version"] = $iv;
		$sysParams["format"] = $this->format;
		$sysParams["sign_type"] = $this->signType;
		$sysParams["method"] = $this->apiMethodName;
		$sysParams["timestamp"] = date("Y-m-d H:i:s");
		$sysParams["alipay_sdk"] = $this->aliPaySdkVersion;
		$sysParams["notify_url"] = $this->notifyUrl;
		$sysParams["return_url"] = $this->returnUrl;
		$sysParams["charset"] = $this->postCharset;
		$sysParams["gateway_url"] = $this->gatewayUrl;

		$apiParams = $this->apiParas;

		$totalParams = array_merge($apiParams, $sysParams);

		$totalParams["sign"] = $this->generateSign($totalParams, $this->signType);

		if ("GET" == strtoupper($httpmethod)) {
			$preString = $this->getSignContentUrlencode($totalParams);
			$requestUrl = $this->gatewayUrl . "?" . $preString;
			return $requestUrl;
		} else {
			foreach ($totalParams as $key => $value) {
				if (false === $this->checkEmpty($value)) {
					$value = str_replace("\"", "&quot;", $value);
					$totalParams[$key] = $value;
				} else {
					unset($totalParams[$key]);
				}
			}
			return $totalParams;
		}
	}

	protected function checkEmpty($value)
    {
		if (!isset($value) || $value === null || trim($value) === "")
			return true;

		return false;
	}

	public function rsaCheckV1($params, $signType='RSA')
    {
		$sign = $params['sign'];
		$params['sign_type'] = null;
		$params['sign'] = null;
		return $this->verify($this->getSignContent($params), $sign, $signType);
	}

    public function verify($data, $sign, $signType = 'RSA')
    {
		$pubKey = $this->alipay_public_key;
		$res = "-----BEGIN PUBLIC KEY-----\n" .
			wordwrap($pubKey, 64, "\n", true) .
			"\n-----END PUBLIC KEY-----";

		(trim($pubKey)) or die('Alipay public key error!');

		if ("RSA2" == $signType) {
			$result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
		} else {
			$result = (bool)openssl_verify($data, base64_decode($sign), $res);
		}

		return $result;
	}

	public function getSignContent($params)
    {
		ksort($params);

		$stringToBeSigned = "";
		$i = 0;
		foreach ($params as $k => $v) {
			if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
				if ($i == 0) {
					$stringToBeSigned .= "$k" . "=" . "$v";
				} else {
					$stringToBeSigned .= "&" . "$k" . "=" . "$v";
				}
				$i++;
			}
		}

		unset ($k, $v);
		return $stringToBeSigned;
	}

	public function generateSign($params, $signType = "RSA")
    {
		return $this->sign($this->getSignContent($params), $signType);
	}

	protected function sign($data, $signType = "RSA")
    {
		$priKey = $this->private_key;
		$res = "-----BEGIN RSA PRIVATE KEY-----\n" .
			wordwrap($priKey, 64, "\n", true) .
			"\n-----END RSA PRIVATE KEY-----";

		if ("RSA2" == $signType) {
			openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
		} else {
			openssl_sign($data, $sign, $res);
		}

		$sign = base64_encode($sign);
		return $sign;
	}

	public function getPostCharset()
    {
		return trim($this->postCharset);
	}
}
