<?php

class ModelDesignGallery extends Model {
	
	public function getGallery($gallery_id)
	{
		$galleries = [];
		$this->load->model('tool/image');
		$query = $this->db->query("
			SELECT * FROM " . DB_PREFIX . "gallery_image gi
				WHERE gi.gallery_id = '" . (int) $gallery_id . "' ORDER BY gi.sort_order ASC");
		foreach ($query->rows as $gallery) {
			if (!empty($gallery['image']) && is_file(DIR_IMAGE . $gallery['image'])) {
				$imageGallery = $this->model_tool_image->resize(
					$gallery['image'],
					400,
					250
				);
			
				$galleries[] = [
					'title' => $gallery['title'],
					'image' => $imageGallery,
					'image_main' => $gallery['image']
				];
			}
		}

		return $galleries;
	}

	public function getGalleries()
	{
		$galleries = [];
		$this->load->model('tool/image');
		$query = $this->db->query("
			SELECT gd.name as name, g.gallery_id as gallery_id   FROM " . DB_PREFIX . "gallery g
			LEFT JOIN " . DB_PREFIX . "gallery_description gd ON (gd.gallery_id = g.gallery_id)
			 WHERE gd.language_id = '" . (int)$this->config->get('config_language_id') . "'
			  ORDER BY g.sort_order");
		foreach ($query->rows as $gallery) {
			$image = $this->getFirstImage($gallery['gallery_id']);
//			if (!empty($image)) {
//				$imageGallery = $this->model_tool_image->resize(
//					$image,
//					300,
//					300
//				);
//			} else {
//				$imageGallery = $this->model_tool_image->resize(
//					'placeholder.png',
//					300,
//					300
//				);
//			}

			$galleries[] = [
				'name' => $gallery['name'],
				'link' => $this->url->link(
					'common/gallery',
					'gallery=' . $gallery['gallery_id']
				),
				'image' => $image
			];
		}

		return $galleries;
	}

	public function getFirstImage($gallery_id)
	{
		$query = $this->db->query("
			SELECT gi.image as image FROM " . DB_PREFIX . "gallery_image gi
			WHERE gi.gallery_id= '" . (int) $gallery_id . "' ORDER BY gi.sort_order LIMIT 1
		");
		if (!empty($query->row['image'])) {
			return $query->row['image'];
		}
		return '';
	}
}
