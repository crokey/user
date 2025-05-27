<?php

defined('BASEPATH') or exit('No direct script access allowed');

class LiveScores extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('LiveScores_model');
    }

    public function index()
{
    $selectedDate = $this->input->get('date') ?: gmdate('Y-m-d');
    $timezone = $this->input->get('tz') ?: 'UTC';

    $data['selectedDate'] = $selectedDate;
    $data['timezone'] = $timezone;
    $data['matches'] = $this->LiveScores_model->get_matches_by_local_date($selectedDate, $timezone);

    $this->load->view('admin/livescores/livescores_view', $data);
}



    public function sync_matches($days_offset = 0)
{
    $token = 'f6e27d96f2e647339f1925d7e8086fb7';
    $date = date('Y-m-d', strtotime("$days_offset days"));
    $url = "https://api.football-data.org/v4/matches?dateFrom=$date&dateTo=$date";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Auth-Token: $token"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $inserted = 0;
    if ($response) {
        $data = json_decode($response, true);
        if (!empty($data['matches'])) {
            foreach ($data['matches'] as $match) {
                if ($this->LiveScores_model->upsert_match($match)) {
                    $inserted++;
                }
            }
        }
    }

    echo "[$date] Inserted/updated $inserted matches.";
}

}
