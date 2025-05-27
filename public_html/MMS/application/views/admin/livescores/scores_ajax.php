<?php if (empty($matches)) : ?>
    <p>No matches to show right now.</p>
<?php else: ?>
    <?php
    $grouped_matches = [];
    foreach ($matches as $match) {
        $comp = $match['competition']['name'];
        $grouped_matches[$comp]['matches'][] = $match;
        $grouped_matches[$comp]['emblem'] = $match['competition']['emblem'] ?? '';
        $grouped_matches[$comp]['area'] = $match['area']['name'] ?? '';
        $grouped_matches[$comp]['flag'] = $match['area']['flag'] ?? '';
    }
    ?>
    <?php foreach ($grouped_matches as $comp => $info): ?>
        <div class="score-card">
            <div class="competition" style="display: flex; align-items: center; gap: 10px;">
                <?php if ($info['flag']): ?>
                    <img src="<?= $info['flag'] ?>" style="width: 20px; height: 14px;">
                <?php endif; ?>
                <strong><?= $comp ?></strong>
                <span style="margin-left:auto; font-size: 12px; color: #aaa;"><?= $info['area'] ?></span>
            </div>
            <?php foreach ($info['matches'] as $match): ?>
                <div class="match-row" style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px;">
                    <div class="team">
                        <img src="<?= $match['homeTeam']['crest'] ?>" width="20" height="20">
                        <?= $match['homeTeam']['shortName'] ?? $match['homeTeam']['name'] ?>
                    </div>
                    <div class="vs">
                        <?php
                        $homeScore = $match['score']['fullTime']['home'];
                        $awayScore = $match['score']['fullTime']['away'];
                        if ($match['status'] === 'FINISHED') {
                            echo "$homeScore - $awayScore";
                        } elseif ($match['status'] === 'LIVE') {
                            echo "<span style='color: green;'>Live</span>";
                        } else {
                            echo '<span class="utc-time" data-utc="' . $match['utcDate'] . '"></span>';
                        }
                        ?>
                    </div>
                    <div class="team">
                        <?= $match['awayTeam']['shortName'] ?? $match['awayTeam']['name'] ?>
                        <img src="<?= $match['awayTeam']['crest'] ?>" width="20" height="20">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
