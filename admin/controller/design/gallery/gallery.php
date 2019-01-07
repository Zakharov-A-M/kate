<?php

class ControllerDesignGalleryGallery extends Controller
{
	private $error = [];
	private $countries = [];

	public function index()
    {
        $this->load->language('design/gallery');

		$this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('localisation/country');
        $this->countries = $this->model_localisation_country->getCountriesContact();

        $this->getList();
	}

    /**
     * Get list gallery
     */
    protected function getList()
    {
        $this->load->model('design/gallery');
        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link(
                'common/dashboard',
                'user_token=' . $this->session->data['user_token']
            )
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link(
                'catalog/category',
                'user_token=' . $this->session->data['user_token']
            )
        ];

        $data['add'] = $this->url->link(
            'design/gallery/gallery/add',
            'user_token=' . $this->session->data['user_token']
        );
        $data['delete'] = $this->url->link(
            'design/gallery/gallery/delete',
            'user_token=' . $this->session->data['user_token']
        );

        $data['galleries'] = [];
        
        $results = $this->model_design_gallery->getGalleries();

        foreach ($results as $result) {
            $data['galleries'][] = [
                'gallery_id' => $result['gallery_id'],
                'name' => $result['name'],
                'sort_order' => $result['sort_order'],
                'edit' => $this->url->link(
                    'design/gallery/gallery/edit',
                    'user_token=' . $this->session->data['user_token'] . '&gallery_id=' . $result['gallery_id']
                ),
                'delete' => $this->url->link(
                    'design/gallery/gallery/delete',
                    'user_token=' . $this->session->data['user_token'] . '&gallery_id=' . $result['gallery_id']
                )
            ];
        }
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('design/gallery/gallery_list', $data));
    }

    /**
     * View form for banner in the main page catalog
     */
	protected function getForm()
    {
		$data['text_form'] = $this->language->get('text_edit');

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];


		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link(
			    'design/gallery/gallery',
                'user_token=' . $this->session->data['user_token']
            )
		];

		$data['action'] = $this->url->link(
		    'design/gallery/gallery',
            'user_token=' . $this->session->data['user_token']
        );

		$data['user_token'] = $this->session->data['user_token'];
		$data['countries'] = $this->countries;

        if (isset($this->request->get['gallery_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $data['galleryInfo'] = $this->model_design_gallery->getGallery($this->request->get['gallery_id']);
            $data['action'] = $this->url->link(
                'design/gallery/gallery/edit',
                'user_token=' . $this->session->data['user_token'] . '&gallery_id=' . $this->request->get['gallery_id']
            );
        } else {
            $data['action'] = $this->url->link(
                'design/gallery/gallery/add',
                'user_token=' . $this->session->data['user_token']
            );
            $data['galleryInfo'] = [];
        }
        
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/gallery/gallery_form', $data));
	}

    /**
     * View page add gallery
     */
    public function add()
    {
        $this->load->language('design/gallery');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('design/gallery');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->model_design_gallery->addGallery($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect(
                $this->url->link(
                    'design/gallery/gallery',
                    'user_token=' . $this->session->data['user_token']
                )
            );
        }

        $this->load->model('localisation/country');
        $this->countries = $this->model_localisation_country->getCountriesContact();

        $this->getForm();
    }

    /**
     * View page edit gallery
     */
    public function edit()
    {
        $this->load->language('design/gallery');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('design/gallery');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->model_design_gallery->editGallery($this->request->get['gallery_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect(
                $this->url->link(
                    'design/gallery/gallery',
                    'user_token=' . $this->session->data['user_token']
                )
            );
        }
        $this->load->model('localisation/country');
        $this->countries = $this->model_localisation_country->getCountriesContact();

        $this->getForm();
    }

    /**
     * Delete this category
     */
    public function delete()
    {
        $this->load->language('catalog/category');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('design/gallery');

        if (isset($this->request->post['selected'])) {
            foreach ($this->request->post['selected'] as $gallery_id) {
                $this->model_design_gallery->deleteGallery($gallery_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect(
                $this->url->link(
                    'design/gallery/gallery',
                    'user_token=' . $this->session->data['user_token']
                )
            );
        }

        $this->getList();
    }

}
