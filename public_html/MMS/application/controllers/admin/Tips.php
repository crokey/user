<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tips extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        
    }

    public function index($clientid = '')
{
    $data['clientid'] = $clientid;

    // Fetch past tips with results
    $data['tips'] = $this->db->get('tblbettingtips_results')->result_array();

    // Fetch today's tips (no result yet)
    //$today = date('Y-m-d');
    //$this->db->where('date', $today);
    //$data['todays_tips'] = $this->db->get('tblbettingtips')->result_array();

    $data['todays_tips'] = $this->db->order_by('date', 'DESC')->get('tblbettingtips')->result_array();


    // Load the view
    $this->load->view('admin/tips/view', $data);
}

  public function get_tips_statistics()
{
    $this->load->model('tips_model');
    $data = $this->tips_model->get_tips_win_loss_data(); // Returns ['win' => 10, 'lose' => 5]

    echo json_encode($data);
}



    public function add()
{
    if ($this->input->post()) {
        $subscriptions = $this->input->post('subscriptions'); // array from multiselect

        $data = [
            'date'        => $this->input->post('date'),
            'event'       => $this->input->post('event'),
            'category'    => $this->input->post('category'),
            'tip'         => $this->input->post('tip'),
            'odds'        => $this->input->post('odds'),
            'information' => $this->input->post('information'),
            'visible_to_subscription' => !empty($subscriptions) ? implode(',', $subscriptions) : null,
        ];

        $this->db->insert('tblbettingtips', $data);
        redirect(admin_url('tips'));
    }
}




   public function edit($id = null)
{
    if ($id === null) {
        show_404();
    }

    
    $this->load->view('admin/tips/edit', $data);
}



    public function delete($id)
{
    
}


  
  
  public function tip($id)
{
    

    if (!$data['tip']) {
        show_404();
    }

    
    $this->load->view('admin/tips/tip', $data);
}


  
public function view()
{
   


    

    // Load the view and pass the data
    $this->load->view('admin/tips/view', $data);
}




}
