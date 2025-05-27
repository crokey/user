<?php
$start = strtotime("+{$offset} days", strtotime('-3 days'));
$dates = [];
for ($i = 0; $i < 7; $i++) {
    $date = strtotime("+$i day", $start);
    $dates[] = [
        'value' => date('Y-m-d', $date),
        'label' => strtoupper(date('D', $date)),
        'day' => date('j', $date),
        'isToday' => date('Y-m-d', $date) === date('Y-m-d'),
    ];
}
?>

<div class="date-selector-container">
    <div class="date-selector-header">
        <div>Calendar month: <strong><?= date('F Y', strtotime($selectedDate)) ?></strong></div>
        <div><i class="fa fa-calendar"></i> View calendar</div>
    </div>
    <div class="date-scroll">
        <a href="javascript:void(0);" onclick="loadCalendar(<?= $offset - 7 ?>, '<?= $selectedDate ?>')" class="arrow-button">&larr;</a>
        <?php foreach ($dates as $day): ?>
            <a href="javascript:void(0);" onclick="loadCalendar(<?= $offset ?>, '<?= $day['value'] ?>')"
               class="date-button <?= $selectedDate === $day['value'] ? 'active' : '' ?> <?= $day['isToday'] ? 'today' : '' ?>">
                <?= $day['isToday'] ? 'TODAY' : $day['label'] ?><br><?= $day['day'] ?>
            </a>
        <?php endforeach; ?>
        <a href="javascript:void(0);" onclick="loadCalendar(<?= $offset + 7 ?>, '<?= $selectedDate ?>')" class="arrow-button">&rarr;</a>
    </div>
</div>
