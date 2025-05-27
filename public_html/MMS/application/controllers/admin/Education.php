<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Education extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        
    }

    public function index()
{
    $userid = get_staff_user_id();

    // Example controller logic
$completedModules = 10;
$totalModules = 26;

$progress = $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0;

$data['progress'] = $progress;


    // Fetch education data if needed
    $this->db->where('userid', $userid);
    $data['educations'] = $this->db->get('tbleducation')->result_array(); // or however your table is named

    $this->load->view('admin/education/view', $data);
}




  public function education($id)
{
    

    if (!$data['education']) {
        show_404();
    }

    
    $this->load->view('admin/education/strategys', $data);
}

public function module($id)
{
    $modules = [
        [
            'title' => 'Introduction to Betting',
            'time' => '45 Min • 4 Topics',
            'topics' => ['How Odds Work', 'Types of Bets', 'Risk vs Reward']
        ],
        [
            'title' => 'Bankroll Management',
            'time' => '30 Min • 3 Topics',
            'topics' => ['Setting Limits', 'Unit Size', 'Avoiding Tilt']
        ],
        [
            'title' => 'Value Betting Explained',
            'time' => '40 Min • 3 Topics',
            'topics' => ['Finding Value', 'Implied Probability', 'Market Inefficiencies']
        ],
        [
            'title' => 'Betting Psychology',
            'time' => '35 Min • 3 Topics',
            'topics' => ['Emotional Discipline', 'Cognitive Biases', 'Long-term Mindset']
        ],
        [
            'title' => 'Live/In-Play Betting',
            'time' => '50 Min • 5 Topics',
            'topics' => ['Live Odds Movement', 'Momentum Shifts', 'Timing Entries']
        ],
        [
            'title' => 'Advanced Strategies',
            'time' => '60 Min • 6 Topics',
            'topics' => ['Arbitrage Betting', 'Line Shopping', 'Data-Driven Decisions']
        ]
    ];

    if (!isset($modules[$id])) {
        show_404(); // Invalid ID
    }

    $data['module'] = $modules[$id];
    $data['module_id'] = $id;
    $data['total_modules'] = count($modules); // ✅ This is the key

    $this->load->view('admin/education/module', $data);
}



  
public function view()
{

    // Load the view and pass the data
    $this->load->view('admin/education/view', $data);
}




}
