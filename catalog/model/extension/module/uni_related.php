<?php
class ModelExtensionModuleUniRelated extends Model {	
	public function getAutoRelated($product_id, $limit, $stock) {
		$product_data = $stock ? $this->cache->get('product.unishop.autorelated.stock.'.(int)$product_id) : $this->cache->get('product.unishop.autorelated.'.(int)$product_id);
		
		if(!$product_data) {
			$product_data = $this->getAutoRelatedProducts($product_id, $limit, $stock, $sign = '>');
					
			if(count($product_data) < (int)$limit) {
				$product_data = $this->getAutoRelatedProducts($product_id, $limit, $stock, $sign = '<>');
			}

			$stock ? $this->cache->set('product.unishop.autorelated.stock.'.(int)$product_id, $product_data) : $this->cache->set('product.unishop.autorelated.'.(int)$product_id, $product_data);
		}

		return $product_data;
	}
	
	private function getAutoRelatedProducts($product_id, $limit, $stock, $sign) {
		$product_data = array();
		
		$sql = "SELECT category_id FROM ".DB_PREFIX . "product_to_category WHERE product_id = '".(int)$product_id."'";
			
		$main_category = $this->db->query("show columns FROM ".DB_PREFIX."product_to_category WHERE Field = 'main_category'");
			
		if ($main_category->num_rows) {
			$sql .= " AND main_category = '1'";
		}
			
		$category = $this->db->query($sql);
			
		if($category->rows) {
			foreach ($category->rows as $category) {
				$sql = "SELECT p.product_id FROM ".DB_PREFIX."product p LEFT JOIN ".DB_PREFIX."product_to_category p2c ON (p.product_id = p2c.product_id) LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p2c.category_id = '".(int)$category['category_id']."'";
				$stock ? $sql .=" AND p.quantity >= '1'" : '';
				$sql .="  AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '".(int)$this->config->get('config_store_id')."' AND p.product_id ".$sign." '".(int)$product_id."' ORDER BY p.product_id ASC LIMIT ".(int)$limit;
				$query = $this->db->query($sql);
		
				foreach ($query->rows as $result) {
					$product_data[] = $this->model_catalog_product->getProduct((int)$result['product_id']);
				}
			}
		}
		
		return $product_data;
	}
	
	public function getRelated() {
		$uniset = $this->config->get('config_unishop2');
		
		$product_data = [];
		$results = [];
		
		if($this->cart->getProducts()) {
			
			$related1 = isset($uniset['checkout_related_product1']) ? $uniset['checkout_related_product1'] : '';
			$related2 = isset($uniset['checkout_related_product2']) ? $uniset['checkout_related_product2'] : '';	
			
			foreach($this->cart->getProducts() as $result) {			
				if ($related1) {
					$result1 = $this->getRelated1($result['product_id']);
					
					if($result1) {
						$results = array_merge($results, $result1);
					}
				} 
				
				if($related2) {
					$result2 = $this->getRelated2($result['product_id']);
					
					if($result2) {
						$results = array_merge($results, $result2);
					}
				}
				
				$in_cart[] = $result['product_id'];
			}
			
			$products = array_unique(array_diff($results, $in_cart));
			
			foreach ($products as $product_id) {
				$product_data[] = $this->model_catalog_product->getProduct((int)$product_id);
			}
		}
		
		return $product_data;
	}
	
	public function getRelated1($product_id) {
		$product_data = [];
		$limit = 10;

		$query = $this->db->query("SELECT pr.related_id FROM ".DB_PREFIX."product_related pr LEFT JOIN ".DB_PREFIX."product p ON (pr.related_id = p.product_id) LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE pr.product_id = '".(int)$product_id."' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '".(int)$this->config->get('config_store_id')."' LIMIT ".(int)$limit);
		
		foreach ($query->rows as $result) {
			$product_data[] = $result['related_id'];
		}
		
		return $product_data;
	}

	public function getRelated2($product_id) {
		$product_data = [];
		$limit = 5;
		$limit2 = 2;
		
		$query = $this->db->query("SELECT op.product_id FROM `".DB_PREFIX."order_product` op LEFT JOIN `".DB_PREFIX."product` p ON (op.product_id = p.product_id) JOIN (SELECT op.order_id FROM `".DB_PREFIX."order_product` op JOIN `".DB_PREFIX."order` o ON (op.order_id = o.order_id) WHERE o.order_status_id > '0' AND op.product_id = '".(int)$product_id."' AND o.store_id = '".(int)$this->config->get('config_store_id')."' LIMIT ".(int)$limit.") mp ON (op.order_id = mp.order_id) WHERE 1 AND op.product_id != '".(int)$product_id."' AND p.status = '1' AND p.date_available <= NOW() LIMIT ".(int)$limit2);
		
		foreach ($query->rows as $result) {
			$product_data[] = $result['product_id'];
		}
		
		return $product_data;
	}
}
?>