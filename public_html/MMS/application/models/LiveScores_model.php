<?php defined('BASEPATH') or exit('No direct script access allowed');

class LiveScores_model extends CI_Model
{
    private $apiToken = 'f6e27d96f2e647339f1925d7e8086fb7'; // Replace with your real token

    public function get_live_scores()
{
    $liveUrl = 'https://api.football-data.org/v4/matches?status=LIVE';

    $liveMatches = $this->fetch_matches_from_api($liveUrl);

    if (empty($liveMatches)) {
        // Fetch only top European leagues
        $upcomingUrl = 'https://api.football-data.org/v4/matches?status=SCHEDULED';

        return $this->fetch_matches_from_api($upcomingUrl);
    }

    return $liveMatches;
}


private function fetch_matches_from_api($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Auth-Token: {$this->apiToken}"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    // Debug output
    file_put_contents(FCPATH . 'api_log.json', $response); // Save to file to inspect

    if ($response) {
        $data = json_decode($response, true);
        return $data['matches'] ?? [];
    }

    return [];
}

/*public function get_matches_by_date($date)
{
    // Extend dateTo by one day to capture late UTC matches
    $nextDay = date('Y-m-d', strtotime($date . ' +1 day'));
    $url = "https://api.football-data.org/v4/matches?dateFrom=$date&dateTo=$nextDay";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Auth-Token: {$this->apiToken}"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    file_put_contents(FCPATH . 'api_log.json', $response); // ✅ Optional debug

    if ($response) {
        $data = json_decode($response, true);
        $matches = $data['matches'] ?? [];

        // Only keep matches that are actually on the selected date
        return array_filter($matches, function ($match) use ($date) {
            return strpos($match['utcDate'], $date) === 0;
        });
    }

    return [];
}*/


public function get_matches_by_date($date)
{
    $this->db->where('match_date', $date);
    return $this->db->get('tbllivescores')->result_array();
}


public function upsert_match($match)
{
    $data = [
        'match_id'      => $match['id'] ?? null,
        'match_date'    => date('Y-m-d', strtotime($match['utcDate'] ?? 'now')),
        'competition'   => $match['competition']['name'] ?? '',
        'area'          => $match['area']['name'] ?? '',
        'home_team'     => $match['homeTeam']['name'] ?? '',
        'away_team'     => $match['awayTeam']['name'] ?? '',
        'home_score'    => $match['score']['fullTime']['home'] ?? null,
        'away_score'    => $match['score']['fullTime']['away'] ?? null,
        'status'        => $match['status'] ?? '',
        'kickoff_time'  => date('Y-m-d H:i:s', strtotime($match['utcDate'] ?? 'now')),
        'raw_data'      => json_encode($match),
        'last_updated'  => date('Y-m-d H:i:s')
    ];

    if (!$data['match_id']) {
        return false; // Don't insert invalid match
    }

    return $this->db->replace('tbllivescores', $data);
}


public function get_matches_by_local_date($localDate, $timezone = 'UTC')
{
    $tz = new DateTimeZone($timezone);
    $start = new DateTime($localDate . ' 00:00:00', $tz);
    $end = new DateTime($localDate . ' 23:59:59', $tz);

    // Convert to UTC
    $start->setTimezone(new DateTimeZone('UTC'));
    $end->setTimezone(new DateTimeZone('UTC'));

    $this->db->where('kickoff_time >=', $start->format('Y-m-d H:i:s'));
    $this->db->where('kickoff_time <=', $end->format('Y-m-d H:i:s'));

    return $this->db->get('tbllivescores')->result_array();
}



}
