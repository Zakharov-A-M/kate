<?php 
class ModelExtensionModuleUniNewData extends Controller {
	
	public function getNewData($result = [], $setting = []) {
		$uniset = $this->config->get('config_unishop2');
		
		if(!isset($result['product_id'])) {
			return ['stickers' => '', 'special_date_end' => '', 'additional_image' => '', 'attributes' => '', 'options' => '', 'discounts' => '', 'quantity_indicator' => ''];
		}
		
		$img_width = isset($this->request->get['product_id']) ? $this->config->get('theme_'.$this->config->get('config_theme') . '_image_related_width') : $this->config->get('theme_'.$this->config->get('config_theme') . '_image_product_width');
		$img_height = isset($this->request->get['product_id']) ? $this->config->get('theme_'.$this->config->get('config_theme') . '_image_related_height') : $this->config->get('theme_'.$this->config->get('config_theme') . '_image_product_height');
			
		$img_width = isset($setting['width']) ? $setting['width'] : $img_width;
		$img_height = isset($setting['height']) ? $setting['height'] : $img_height;
		
		$data['stickers'] = $this->getStickers($result);
		$data['special_date_end'] = $this->getSpecialDateEnd($result['product_id']);
		
		if(!isset($result['product_page'])) {
			$data['additional_image'] = $this->getAdditionalImage($result['product_id'], $img_width, $img_height);
			
			$data['attributes'] = $this->getAttributes($result['product_id']);
			
			$data['options'] = $this->getOptions($result['product_id'], $result['quantity'], $result['tax_class_id'], $img_width, $img_height);
			
			$options = $this->config->get('unishop2_quantity_indicator_options');
			$quantity = $this->config->get('unishop2_quantity_indicator');
		} else {
			$quantity = $result['quantity'];
			$options = $result['options'];
		}
		
		$data['discounts'] = $this->getDiscounts($result['product_id'], $result['tax_class_id']);
		$data['quantity_indicator'] = $this->getQuantityIndicator($quantity, $options);
		
		return $data;
	}
	
	private function getAttributes($product_id) {
		$uniset = $this->config->get('config_unishop2');
		
		$data['show_attr_group'] = $uniset['show_attr_group'];
		$data['show_attr_item'] = $uniset['show_attr_item'];
		
		$data['attributes'] = [];
		
		if(isset($uniset['show_attr'])) {
			$data['show_attr_name'] = isset($uniset['show_attr_name']) ? true : false;
			
			$attributes = $this->model_catalog_product->getProductAttributes($product_id);
			
			foreach($attributes as $key => $attribute) {
				if($key < $uniset['show_attr_group']) {
					foreach($attribute['attribute'] as $key => $attribute_value) {
						if($key < $uniset['show_attr_item']) {
							$data['attributes'][] = array(
								'name' => $attribute_value['name'],
								'text' => $attribute_value['text']
							);
						}
					}	
				}
			}
		}
		
		return $this->load->view('extension/module/uni_attributes', $data);
	}
	
	private function getOptions($product_id, $prod_quantity, $tax_class_id, $img_width, $img_height) {
		$uniset = $this->config->get('config_unishop2');
		$currency = $this->session->data['currency'];
		
		$o_quantity = 0;
		$required = false;
		$o_quantity_arr = [];
			
		$data['options'] = [];
		
		if (isset($uniset['show_options']) && $uniset['show_options_item'] > 0) {		
			foreach ($this->model_catalog_product->getProductOptions($product_id) as $key => $option) {
				if ($key < $uniset['show_options_item'] && ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox')) {
				
					$product_option_value_data = array();
					
					if($option['required']) {
						$o_quantity = 0;
						$required = true;
					}

					foreach ($option['product_option_value'] as $option_value) {
						if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
							if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
								$option_price = $this->currency->format($this->tax->calculate($option_value['price'], $tax_class_id, $this->config->get('config_tax') ? 'P' : false), $currency);
							} else {
								$option_price = false;
							}
							
							$product_option_value_data[] = array(
								'product_option_value_id' => $option_value['product_option_value_id'],
								'option_value_id'         => $option_value['option_value_id'],
								'name'                    => $option_value['name'],
								'image'                   => $option_value['image'] ? $this->model_tool_image->resize($option_value['image'], 30, 30) : '',
								'small' 				  => $this->model_tool_image->resize($option_value['image'], $img_width, $img_height),
								'price'                   => $option_price,
								'price_value'             => $this->tax->calculate($option_value['price'], $tax_class_id, $this->config->get('config_tax'))*$this->currency->getValue($currency),
								'price_prefix'            => $option_value['price_prefix']
							);
						}
						
						$o_quantity = $o_quantity + $option_value['quantity'];
					}
					
					if($option['required']) {
						$o_quantity_arr[] = $o_quantity;
					}

					$data['options'][] = array(
						'product_option_id'    => $option['product_option_id'],
						'product_option_value' => $product_option_value_data,
						'option_id'            => $option['option_id'],
						'name'                 => $option['name'],
						'type'                 => $option['type'],
						'value'                => $option['value'],
						'required'             => $option['required']
					);
				}
			}
		}
		
		$quantity = $required ? min($o_quantity_arr) : $prod_quantity + $o_quantity;
		
		$this->config->set('unishop2_quantity_indicator', $quantity);
		$this->config->set('unishop2_quantity_indicator_options', $data['options'] ? true : false);
		
		return $this->load->view('extension/module/uni_options', $data);
	}
	
	private function getStickers($result) {
		$uniset = $this->config->get('config_unishop2');
		$language_id = $this->config->get('config_language_id');
		$currency = $this->session->data['currency'];
		
		$data['stickers'] = [];
		
		if($result) {			
			if (isset($uniset['sticker_reward']) && $result['reward']) {
				$data['stickers'][] = array(
					'name' 			=> 'reward',
					'text' 			=> $uniset[$language_id]['sticker_reward_text'],
					'text_after'	=> $uniset[$language_id]['sticker_reward_text_after'],
					'value' 		=> round($result['reward'], 0),
					'length' 		=> strlen($uniset[$language_id]['sticker_reward_text']) + strlen($uniset[$language_id]['sticker_reward_text_after'])
				);
			}
			
			if (isset($uniset['sticker_special']) && $result['special'] && $result['special'] > 0) {
				$percent = round((($result['special'] - $result['price'])/$result['price'])*100, 0) . '%';
				$value = $this->currency->format($this->tax->calculate($result['price'] - $result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);

				$data['stickers'][] = array(
					'name' 		 => 'special',
					'text' 		 => $uniset[$language_id]['sticker_special_text'],
					'text_after' => '',
					'value' 	 => isset($uniset['sticker_special_percent']) ? $percent : $value,
					'length'	 => strlen($uniset[$language_id]['sticker_special_text']) + strlen(isset($uniset['sticker_special_percent']) ? $percent : $value),
				);
			}
			
			if(isset($uniset['sticker_bestseller'])) {
				$bestseller = $this->getBestSellerSticker($result['product_id']);
				
				if ($bestseller) {
					$data['stickers'][] = array(
						'name'		 => 'bestseller',
						'text' 		 => $uniset[$language_id]['sticker_bestseller_text'],
						'text_after' => '',
						'value' 	 => '',
						'length' 	 => strlen($uniset[$language_id]['sticker_bestseller_text'])
					);
				}
			}
			
			$date = strtotime($result['date_available']) + $uniset['sticker_new_date'] * 24 * 3600;
				
			if (isset($uniset['sticker_new']) && $date >= strtotime('now')) {
				$data['stickers'][] = array(
					'name' 		 => 'new',
					'text' 		 => $uniset[$language_id]['sticker_new_text'],
					'text_after' => '',
					'value' 	 => '',
					'length' 	 => strlen($uniset[$language_id]['sticker_new_text'])
				);		
			}
			
			if (isset($uniset['upc_as_sticker']) && $result['upc']) {
				$data['stickers'][] = array(
					'name'		 => 'upc',
					'text'       => $result['upc'],
					'text_after' => '',
					'value' 	 => '',
					'length' 	 => strlen($result['upc'])
				);
			}
			
			if (isset($uniset['ean_as_sticker']) && $result['ean']) {
				$data['stickers'][] = array(
					'name' 		 => 'ean',
					'text' 		 => $result['ean'],
					'text_after' => '',
					'value'		 => '',
					'length' 	 => strlen($result['ean'])
				);
			}
			
			if (isset($uniset['jan_as_sticker']) && $result['jan']) {
				$data['stickers'][] = array(
					'name' 		 => 'jan',
					'text'		 => $result['jan'],
					'text_after' => '',
					'value' 	 => '',
					'length' 	 => strlen($result['jan'])
				);
			}
			
			if (isset($uniset['isbn_as_sticker']) && $result['isbn']) {
				$data['stickers'][] = array(
					'name' 		 => 'isbn',
					'text' 	 	 => $result['isbn'],
					'text_after' => '',
					'value' 	 => '',
					'length' 	 => strlen($result['isbn'])
				);
			}
			
			if (isset($uniset['mpn_as_sticker']) && $result['mpn']) {
				$data['stickers'][] = array(
					'name' 		 => 'mpn',
					'text' 		 => $result['mpn'],
					'text_after' => '',
					'value' 	 => '',
					'length' 	 => strlen($result['mpn'])
				);
			}
			
			if(count($data['stickers']) > 1) { 
				foreach ($data['stickers'] as $key => $value) {
					$sort[$key] = $value['length'];
				}
			
				array_multisort($sort, SORT_DESC, $data['stickers']);
			}	
		}
		
		return $this->load->view('extension/module/uni_stickers', $data);
	}
	
	private function getQuantityIndicator($quantity, $options) {
		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		
		$data['indicator'] = [];
		
		if(isset($uniset['show_stock_indicator'])) {
			$full = $options ? $uniset['stock_indicator_full_opt'] : $uniset['stock_indicator_full'];
				
			$stock = round($quantity / $full * 100, 0);
				
			$stock = $stock > 100 ? 100 : $stock;
			$stock = $stock < 1 ? 0.5 : $stock;
			
			switch($stock) {
				case ($stock >= 80):
					$title = $uniset[$lang_id]['stock_i_t_5'];
					$items = 5;
					break;
				case ($stock >= 60):
					$title = $uniset[$lang_id]['stock_i_t_4'];
					$items = 4;
					break;
				case ($stock >= 40):
					$title = $uniset[$lang_id]['stock_i_t_3'];
					$items = 3;
					break;
				case ($stock >= 20):
					$title = $uniset[$lang_id]['stock_i_t_2'];
					$items = 2;
					break;
				case ($stock >= 1):
					$title = $uniset[$lang_id]['stock_i_t_1'];
					$items = 1;
					break;
				default:
					$title = $uniset[$lang_id]['stock_i_t_0'];
					$items = 0;
			}
			
			$data['indicator'] = ['title' => $title, 'items' => $items];
		}
		
		return $this->load->view('extension/module/uni_quantity_indicator', $data);
	}
	
	private function getAdditionalImage($product_id, $img_width, $img_height) {
		$uniset = $this->config->get('config_unishop2');
		
		$image = '';
		
		if(isset($uniset['show_additional_image'])) {
			$results = $this->model_catalog_product->getProductImages($product_id);
		
			foreach ($results as $result) {
				$image = $this->model_tool_image->resize($result['image'], $img_width, $img_height);
				break;
			}
		}
		
		return $image;
	}
	
	private function getSpecialDateEnd($product_id) {
		$uniset = $this->config->get('config_unishop2');
		
		$date_end = '';
		
		if(isset($uniset['show_special_timer'])) {
			$query = $this->db->query("SELECT date_end FROM `".DB_PREFIX."product_special` WHERE product_id = '".(int)$product_id."' AND customer_group_id = '".(int)$this->config->get('config_customer_group_id')."' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY priority ASC, price ASC LIMIT 1");
			$date_end = ($query->num_rows) ? $query->row['date_end'] : false;
		}
		
		return $date_end;
	}
	
	private function getDiscounts($product_id, $tax_class_id) {
		$uniset = $this->config->get('config_unishop2');
		$currency = $this->session->data['currency'];

		$result = array();
		
		if(isset($uniset['liveprice'])) {
			$discounts = $this->model_catalog_product->getProductDiscounts($product_id);

			foreach ($discounts as $discount) {
				$result[] = array(
					'quantity' => $discount['quantity'],
					'price'    => $this->tax->calculate($discount['price'], $tax_class_id, $this->config->get('config_tax'))*$this->currency->getValue($currency),
				);
			}
		}
		
		return $result ? str_replace('"', "'", json_encode($result, true)) : '';
	}
	
	private function getBestSellerSticker($product_id) {
		$uniset = $this->config->get('config_unishop2');
		
		$result = $this->cache->get('unishop.sticker.bestseller');
		
		if(!$result) {
			$query = $this->db->query("SELECT op.product_id, SUM(op.quantity) AS total FROM `".DB_PREFIX."order_product` op LEFT JOIN `".DB_PREFIX."product` p ON (op.product_id = p.product_id) LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) WHERE o.order_status_id > '0' AND p.date_available <= NOW() AND p.status = '1' GROUP BY op.product_id");
			
			$result = array();
			
			foreach($query->rows as $product) {
				if((int)$product['total'] > $uniset['sticker_bestseller_item']) {
					$result[] = $product['product_id'];
				}
			}
			
			if($result) {
				$this->cache->set('unishop.sticker.bestseller', $result);
			}
		}
		
		if(in_array($product_id, $result)) {
			return true;
		} else {
			return false;
		}
	}
}
?>