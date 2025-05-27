<?php
// File: application/controllers/Cron_LiveScores.php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron_livescores extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Prevent direct access from browser
        /*if (!is_cli()) {
            show_error('Direct access not allowed');
        }*/

        $this->load->model('LiveScores_model');
    }

    public function sync_all()
{
    $this->load->model('LiveScores_model');

    $token = 'f6e27d96f2e647339f1925d7e8086fb7';
    $baseUrl = 'https://api.football-data.org/v4/matches';
    $totalInserted = 0;
    $requestCounter = 0;

    for ($i = -60; $i <= 14; $i++) {
        $targetDate = date('Y-m-d', strtotime("$i days"));
        $nextDay = date('Y-m-d', strtotime($targetDate . ' +1 day'));
        $url = "$baseUrl?dateFrom=$targetDate&dateTo=$nextDay";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-Auth-Token: $token"]);
        $response = curl_exec($ch);
        curl_close($ch);

        $requestCounter++;

        $insertedToday = 0;

        if ($response) {
            file_put_contents(APPPATH . 'logs/api_' . $targetDate . '.json', $response); // DEBUG
            $data = json_decode($response, true);

            if (!empty($data['matches'])) {
                foreach ($data['matches'] as $match) {
                    if (strpos($match['utcDate'], $targetDate) === 0) {
                        if (!empty($match['id']) && $this->LiveScores_model->upsert_match($match)) {
                            $insertedToday++;
                        }
                    }
                }
            } else {
                log_message('info', "[LiveScores] No matches returned for $targetDate. Response size: " . strlen($response));
            }

            log_message('info', "[LiveScores] $targetDate - Inserted $insertedToday matches.");
            $totalInserted += $insertedToday;
        } else {
            log_message('error', "[LiveScores] API call failed for $targetDate.");
            file_put_contents(APPPATH . "logs/api_error_$targetDate.txt", "Failed call: $url\n");
        }

        // 🕒 Throttle: after 10 requests, pause 60 seconds
        if ($requestCounter % 10 === 0) {
            log_message('info', "[LiveScores] Rate limit pause: waiting 60 seconds after 10 requests.");
            sleep(60);
        } else {
            sleep(1); // small delay between requests
        }
    }

    log_message('info', "--- LiveScores Sync Completed ---");
    log_message('info', "Total Matches Synced: $totalInserted");
    echo "Total Matches Synced: $totalInserted\n";
}




    public function sync_day($date = null)
{
    $this->load->model('LiveScores_model');

    $token = 'f6e27d96f2e647339f1925d7e8086fb7';
    $baseUrl = 'https://api.football-data.org/v4/matches';

    $targetDate = $date ?: date('Y-m-d');
    $nextDay = date('Y-m-d', strtotime($targetDate . ' +1 day'));
    $url = "$baseUrl?dateFrom=$targetDate&dateTo=$nextDay";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-Auth-Token: $token"]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        $inserted = 0;

        if (!empty($data['matches'])) {
            foreach ($data['matches'] as $match) {
                // Keep only matches that start on the exact target date in UTC
                if (strpos($match['utcDate'], $targetDate) === 0) {
                    if (!empty($match['id']) && $this->LiveScores_model->upsert_match($match)) {
                        $inserted++;
                    }
                }
            }
        }

        log_message('info', "[LiveScores] sync_day for $targetDate - Inserted $inserted matches.");
        echo "Synced $inserted matches for $targetDate\n";
    } else {
        log_message('error', "[LiveScores] API call failed for date $targetDate.");
        echo "API call failed for $targetDate\n";
    }
}


}
