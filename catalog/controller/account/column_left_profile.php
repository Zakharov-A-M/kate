<?php

class ControllerAccountColumnLeftProfile extends Controller
{
    /**
     * Get left column in page account
     *
     * @return string
     */
    public function index()
    {
        $this->load->language('account/left_column');
        if ($this->customer->getId() && !$this->customer->getRegistred()) {
            $data['confirmed'] = false;
        } else {
            $data['confirmed'] = true;
        }

        return $this->load->view('account/leftprofile', $data);
    }
}
