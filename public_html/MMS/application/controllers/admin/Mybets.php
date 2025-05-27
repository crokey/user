<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Mybets extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        
    }

    public function index()
{
    $userid = get_staff_user_id();

    $this->db->where('userid', $userid);
    $bets = $this->db->order_by('date', 'DESC')->get('tblmybets')->result_array();

    foreach ($bets as &$bet) {
        if ($bet['type'] === 'parlay') {
            $bet['items'] = $this->db
                ->where('bet_id', $bet['id'])
                ->get('tblmybet_items')
                ->result_array();
        }
    }

    $data['mybets'] = $bets;

    $this->db->where('userid', $userid);
    $history = $this->db->get_where('tblmybets_history', ['userid' => get_staff_user_id()])->result_array();

foreach ($history as &$row) {
    if (strtolower($row['event']) === 'parlay') {
        $row['is_parlay'] = true;
        $row['parlay_items'] = $this->db
            ->get_where('tblmybet_items', ['bet_id' => $row['bet_id']])
            ->result_array();
    } else {
        $row['is_parlay'] = false;
    }
}

$data['mybets_history'] = $history;


    $this->load->view('admin/mybets/view', $data);
}



  public function get_tips_statistics()
{
    $this->load->model('tips_model');
    $data = $this->tips_model->get_tips_win_loss_data(); // Returns ['win' => 10, 'lose' => 5]

    echo json_encode($data);
}

public function add_user_bet()
{
    $this->load->helper('security');
    $this->load->library('form_validation');

    if ($this->input->post()) {
        $type = $this->input->post('type');
        $events = $this->input->post('events');
        $categories = $this->input->post('categories');
        $tips = $this->input->post('tips');
        $odds = $this->input->post('odds');

        $bet_data = [
            'userid'      => get_staff_user_id(),
            'type'        => $type,
            'date'        => $this->input->post('date'),
            'total_bet'   => $this->input->post('total_bet'),
            'information' => $this->input->post('information')
        ];

        if ($type === 'single') {
            $bet_data['event'] = $events[0];
            $bet_data['category'] = $categories[0];
            $bet_data['tip'] = $tips[0];
            $bet_data['odds'] = $odds[0];
        }

        $this->db->insert('tblmybets', $bet_data);
        $bet_id = $this->db->insert_id();

        if ($type === 'parlay') {
            for ($i = 0; $i < count($events); $i++) {
                $item = [
                    'bet_id'   => $bet_id,
                    'event'    => $events[$i],
                    'category' => $categories[$i],
                    'tip'      => $tips[$i],
                    'odds'     => $odds[$i],
                ];
                $this->db->insert('tblmybet_items', $item);
            }
        }

        set_alert('success', 'Bet slip added.');
        redirect(admin_url('mybets'));
    }
}


public function record_result()
{
    $bet_id = $this->input->post('bet_id');
    $result = $this->input->post('result');
    $userid = get_staff_user_id();

    if (!$bet_id || !$result || !$userid) {
        show_error('Missing data for recording result.');
    }

    // Start transaction
    $this->db->trans_start();

    // Fetch bet from tblmybets
    $bet = $this->db->get_where('tblmybets', ['id' => $bet_id, 'userid' => $userid])->row_array();
    if (!$bet) {
        $this->db->trans_complete();
        show_error('Invalid bet or unauthorized access');
    }

    // Fetch extra info for parlay
    $event = $bet['type'] === 'single' ? $bet['event'] : 'Parlay';
    $tip   = $bet['type'] === 'single'
        ? $bet['tip']
        : count($this->db->get_where('tblmybet_items', ['bet_id' => $bet_id])->result_array()) . ' selections';

    // Insert into history FIRST before any delete
    $this->db->insert('tblmybets_history', [
        'userid'     => $userid,
        'bet_id'     => $bet_id,
        'event'      => $event,
        'tip'        => $tip,
        'result'     => $result,
        'settled_at' => date('Y-m-d H:i:s'),
    ]);

    if ($this->db->affected_rows() === 0) {
        $this->db->trans_rollback();
        show_error('Failed to insert into mybets_history.');
    }

    // Now delete only AFTER insert success
    if ($bet['type'] === 'parlay') {
        $this->db->delete('tblmybet_items', ['bet_id' => $bet_id]);
    }

    $this->db->delete('tblmybets', ['id' => $bet_id]);

    // End transaction
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
        log_message('error', 'Transaction failed during result record.');
        show_error('Could not complete transaction.');
    }

    set_alert('success', 'Bet marked as ' . ucfirst($result) . ' and moved to history.');
    redirect(admin_url('mybets'));
}









    public function add()
{
    if ($this->input->post()) {
        $data = [
            'userid'      => get_staff_user_id(), // or get_user_id() for clients
            'date'        => $this->input->post('date'),
            'event'       => $this->input->post('event'),
            'category'    => $this->input->post('category'),
            'tip'         => $this->input->post('tip'),
            'odds'        => $this->input->post('odds'),
            'information' => $this->input->post('information'),
        ];

        $this->db->insert('tblmybets', $data);
        redirect(admin_url('mybets'));
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


  
  
  public function mybets($id)
{
    

    if (!$data['tip']) {
        show_404();
    }

    
    $this->load->view('admin/mybets/mybet', $data);
}


  
public function view()
{
   


    

    // Load the view and pass the data
    $this->load->view('admin/mybets/view', $data);
}




}
