<?php

class ModelDesignGallery extends Model
{
	public function addGallery($data)
	{
		$this->db->query("INSERT INTO " . DB_PREFIX . "gallery SET sort_order = '" . $data['sort_order'] . "'");

		$gallery_id = $this->db->getLastId();

		$this->db->query("INSERT INTO " . DB_PREFIX . "gallery_description SET gallery_id = '" . (int) $gallery_id . "', name = '" . $data['name'] . "', language_id = 2");

		if (!empty($data['image'])) {
			foreach ($data['image'] as $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "gallery_image SET title = '" . $value['title'] . "', sort_order = '" . (int) $value['sort_order'] . "', image = '" .  $this->db->escape($value['image']) . "', gallery_id = '" .  (int) $gallery_id . "'");
			}
		}

		return $gallery_id;
	}

	public function editGallery($gallery_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "gallery SET sort_order = '" . $data['sort_order'] . "' WHERE gallery_id = '" . (int) $gallery_id . "'");
		$this->db->query("UPDATE " . DB_PREFIX . "gallery_description SET name = '" . $data['name'] . "' WHERE gallery_id = '" . (int) $gallery_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "gallery_image WHERE gallery_id = '" . (int) $gallery_id . "'");

		if (!empty($data['image'])) {
			foreach ($data['image'] as $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "gallery_image SET title = '" . $value['title'] . "', sort_order = '" . (int) $value['sort_order'] . "', image = '" .  $this->db->escape($value['image']) . "', gallery_id = '" .  (int) $gallery_id . "'");
			}
		}
	}

	public function deleteGallery($gallery_id)
	{
		$this->db->query("DELETE FROM " . DB_PREFIX . "gallery WHERE gallery_id = '" . (int)$gallery_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "gallery_description WHERE gallery_id = '" . (int)$gallery_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "gallery_image WHERE gallery_id = '" . (int)$gallery_id . "'");
	}


	public function getGalleries()
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "gallery g
			LEFT JOIN " . DB_PREFIX . "gallery_description gd ON (gd.gallery_id = g.gallery_id)
			 WHERE gd.language_id = " . (int)$this->config->get('config_language_id');

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getGalleryImages($galleryId)
	{
		$galleryImages = [];
		$this->load->model('tool/image');

		$gallery = $this->db->query("SELECT * FROM " . DB_PREFIX . "gallery_image WHERE gallery_id = '" . (int)$galleryId . "' ORDER BY sort_order ASC");

		foreach ($gallery->rows as $image) {

			if (is_file(DIR_IMAGE . $image['image'])) {
				$img = $image['image'];
				$thumb = $image['image'];
			} else {
				$img = '';
				$thumb = 'no_image.png';
			}

			$galleryImages[] = [
				'title'      => $image['title'],
				'image'      => $img,
				'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
				'sort_order' => $image['sort_order']
			];
		}

		return $galleryImages;
	}

	public function getGallery($galleryId)
	{
		$galleries = [];
		$sql = "SELECT * FROM " . DB_PREFIX . "gallery g
			LEFT JOIN " . DB_PREFIX . "gallery_description gd ON (gd.gallery_id = g.gallery_id)
			 WHERE g.gallery_id = ". (int) $galleryId . "
			 ";

		$query = $this->db->query($sql);
		foreach ($query->rows as $gallery) {
			$image = $this->getGalleryImages($galleryId);
			$galleries[$gallery['language_id']] = [
				'name' => $gallery['name'],
				'sort_order' => $gallery['sort_order'],
				'image' => $image
			];
		}

		return $galleries;
	}
}
