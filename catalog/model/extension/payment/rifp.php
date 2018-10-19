<?php
class ModelExtensionPaymentRifp extends Model
{
	public function getMethod()
    {
		$this->load->language('extension/payment/rifp');
        $method_data = array(
            'code'       => 'rifp',
            'title'      => $this->language->get('text_title'),
            'terms'      => '',
            'sort_order' => $this->config->get('payment_rifp_sort_order')
        );

		return $method_data;
	}
}
