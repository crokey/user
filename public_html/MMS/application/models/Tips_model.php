<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tips_model extends App_Model
  {
  public function get_tips_win_loss_data()
{
    $this->db->select('result, COUNT(*) as total');
    $this->db->group_by('result');
    $query = $this->db->get('tblbettingtips_results'); // Ensure 'result' column has 'win' or 'lose'

    $stats = ['win' => 0, 'lose' => 0];
    foreach ($query->result() as $row) {
        if (strtolower($row->result) === 'win') {
            $stats['win'] = (int)$row->total;
        } elseif (strtolower($row->result) === 'lose') {
            $stats['lose'] = (int)$row->total;
        }
    }

    return $stats;
}

public function get_todays_tips_grouped_by_sport()
{
    $this->db->where('DATE(date)', date('Y-m-d'));
    $query = $this->db->get('tblbettingtips');
    $results = $query->result_array();

    $grouped = [];
    foreach ($results as $tip) {
        $sport = strtolower($tip['category']);
        $grouped[$sport][] = $tip;
    }

    return $grouped;
}


public function get_win_rate_summary()
{
    $now = strtotime(date('Y-m-d'));
    $last7 = strtotime('-7 days', $now);
    $monthStart = strtotime(date('Y-m-01'));

    $summary = [
        'last7' => ['wins' => 0, 'total' => 0],
        'month' => ['wins' => 0, 'total' => 0],
        'overall' => ['wins' => 0, 'total' => 0],
    ];

    // From tips
    $tips = $this->db->get('tblbettingtips_results')->result_array();
    foreach ($tips as $r) {
        $date = strtotime($r['date']);
        $result = strtolower($r['result']);
        if (in_array($result, ['win', 'lose'])) {
            $summary['overall']['total']++;
            if ($result === 'win') $summary['overall']['wins']++;

            if ($date >= $last7) {
                $summary['last7']['total']++;
                if ($result === 'win') $summary['last7']['wins']++;
            }

            if ($date >= $monthStart) {
                $summary['month']['total']++;
                if ($result === 'win') $summary['month']['wins']++;
            }
        }
    }

    // From personal history
    $my_bets = $this->db->get('tblmybets_history')->result_array();
    foreach ($my_bets as $r) {
        $date = strtotime($r['settled_at']);
        $result = strtolower($r['result']);
        if (in_array($result, ['win', 'lose'])) {
            $summary['overall']['total']++;
            if ($result === 'win') $summary['overall']['wins']++;

            if ($date >= $last7) {
                $summary['last7']['total']++;
                if ($result === 'win') $summary['last7']['wins']++;
            }

            if ($date >= $monthStart) {
                $summary['month']['total']++;
                if ($result === 'win') $summary['month']['wins']++;
            }
        }
    }

    // Final summary format
    foreach ($summary as $key => $data) {
        $wins = $data['wins'];
        $total = $data['total'];
        $summary[$key] = [
            'percentage' => $total > 0 ? round(($wins / $total) * 100) : 0,
            'count' => "{$wins}/{$total}"
        ];
    }

    return $summary;
}


public function get_last_7_days_history()
{
    $userid = get_staff_user_id();

    // Get user's datecreated from tblstaff
    $user = $this->db->select('datecreated')->get_where('tblusers', ['id' => $userid])->row();
    if (!$user) {
        return [];
    }

    // Convert to Y-m-d only
    $user_created = date('Y-m-d', strtotime($user->datecreated));
    $last7 = date('Y-m-d', strtotime('-7 days'));

    // Take the later of the two dates
    $cutoff = max($last7, $user_created);

    $this->db->select('event, result, date');
    $this->db->from('tblbettingtips_results');
    $this->db->where('date >=', $cutoff);
    $this->db->order_by('date', 'DESC');

    return $this->db->get()->result_array();
}




public function get_my_last_7_days_history()
{
    $userid = get_staff_user_id();

    $this->db->select('event, result, settled_at as date');
    $this->db->from('tblmybets_history');
    $this->db->where('userid', $userid);
    $this->db->where('settled_at >=', date('Y-m-d', strtotime('-7 days')));
    $this->db->order_by('settled_at', 'DESC');

    return $this->db->get()->result_array();
}



}