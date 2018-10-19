<?php

class ModelExtensionShippingBusinessLine extends Model
{
	public function getQuote($address)
    {
		$this->load->language('extension/shipping/business_line');

		$query = $this->db->query(
		    "SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE 
             geo_zone_id = '" . (int)$this->config->get('shipping_business_line_geo_zone_id') . "' 
             AND country_id = '" . (int)$address['country_id'] . "' 
             AND (zone_id = '" . (int)$address['zone_id'] . "'
             OR zone_id = '0')"
        );

		if (!$this->config->get('shipping_business_line_geo_zone_id') || $query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$quote_data = [];

			$quote_data['business_line'] = array(
				'code'         => 'business_line.business_line',
				'title'        => $this->language->get('text_description'),
				'cost'         => 0.00,
				'tax_class_id' => 0,
				'text'         => ''
			);

			$method_data = array(
				'code'       => 'business_line',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_business_line_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}
