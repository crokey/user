<?php defined("BASEPATH") or exit("No direct script access allowed"); ?>
<?php init_head(); ?>

<title>Football Scores</title>
<style>
    .score-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        max-width: 580px: box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .competition {
        font-size: 14px;
        color: #777;
        margin-bottom: 10px;
    }

    .match-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 18px;
        font-weight: bold;
    }

    .match-status {
        text-align: center;
        font-size: 14px;
        color: #555;
        margin-top: 10px;
    }

    .team {
        width: 40%;
        text-align: center;
    }

    .vs {
        width: 20%;
        text-align: center;
    }

    .date-selector-container {
        background: #ffffff;
        border: 1px solid #ddd;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .date-selector-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        font-size: 14px;
        color: #111;
    }

    .date-scroll-wrapper {
    text-align: center;
}

.date-scroll {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    overflow-x: auto;
    padding: 4px 0;
}


    .date-button {
        padding: 10px 6px;
        width: 60px;
        border-radius: 8px;
        border: none;
        text-align: center;
        background-color: #f1f1f1;
        font-size: 13px;
        color: #333;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .date-button:hover {
        background-color: #e2e6ea;
    }

    .date-button.active {
        background-color: #007bff;
        color: #fff;
        font-weight: 600;
    }

    .date-button.today {
        border: 2px solid #007bff;
        font-weight: bold;
    }

    .arrow-button {
        padding: 10px;
        border-radius: 8px;
        background-color: #e9ecef;
        color: #333;
        font-weight: bold;
        border: none;
        text-decoration: none;
        min-width: 30px;
        text-align: center;
        font-size: 18px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .arrow-button:hover {
        background-color: #d6d8db;
    }

    /* Make the datepicker larger */
    .ui-datepicker {
        font-size: 16px;
        transform: scale(1.15);
        /* Increase overall size */
        transform-origin: top left;
    }

    .arrow-button i.fa-calendar-day {
        margin-right: 4px;
    }

    .sidebar-nav li:hover a {
    color: #007bff;
}
.sidebar-nav li.active a {
    font-weight: bold;
    color: #007bff;
}
</style>

<div id="wrapper">
    <div class="content">
        <div class="row">
<?php
$activeTab = $this->input->get('tab') ?? 'matches';
if ($activeTab === 'matches'):
?>
   


            <?php
            // Get user-selected date or default to today in user's timezone
$tzString = $this->input->get("tz") ?: "UTC";

try {
    $userTimezone = new DateTimeZone($tzString);
} catch (Exception $e) {
    $userTimezone = new DateTimeZone("UTC");
}

// Get today's date in user's timezone
$today = (new DateTime('now', $userTimezone))->format('Y-m-d');

// Get selected date, default to "today" in user's timezone
$selectedDate = $this->input->get("date") ?: $today;

// Generate the 7-day range centered around selected date
$start = (new DateTime($selectedDate, $userTimezone))->modify('-3 days')->getTimestamp();

$dates = [];

for ($i = 0; $i < 7; $i++) {
    $date = strtotime("+$i day", $start);
    $formatted = date("Y-m-d", $date);
    $dates[] = [
        "label" => date("D", $date) . "<br>" . date("j", $date),
        "value" => $formatted,
        "isToday" => $formatted === $today,
    ];
}

            ?>

            <!-- Left Sidebar -->
<div class="col-md-3">
    <div class="panel_s" style="border: none; background: #fff; box-shadow: none;">
        <div class="panel-body" style="padding: 0;">
            <ul class="sidebar-nav" style="list-style: none; padding: 0; margin: 0; font-size: 15px;">
                <li style="padding: 12px 20px; display: flex; align-items: center;" class="<?= $activeTab === 'matches' ? 'active' : '' ?>">
    <i class="fa fa-futbol-o" style="margin-right: 8px;"></i>
    <a href="?tab=matches" style="color: #111; text-decoration: none;">Matches</a>
</li>

                <li style="padding: 12px 20px; display: flex; align-items: center;">
                    <i class="fa fa-star" style="margin-right: 8px;"></i>
                    <a href="?tab=watchlist" style="color: #111; text-decoration: none;">My Watchlist</a>
                </li>
                <li style="padding: 12px 20px; display: flex; align-items: center;">
                    <i class="fa fa-globe" style="margin-right: 8px;"></i>
                    <a href="#" style="color: #111; text-decoration: none;">All Leagues</a>
                </li>
                <!-- Dropdown -->
                <li style="padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;" onclick="toggleLeagues()">
                    <div style="display: flex; align-items: center;">
                        <i class="fa fa-trophy" style="margin-right: 8px;"></i>
                        <span style="color: #111;">Top Leagues</span>
                    </div>
                    <i class="fa fa-chevron-down" id="chevron" style="transition: 0.3s;"></i>
                </li>
                <ul id="topLeagues" style="display: none; list-style: none; margin-left: 32px; padding-left: 0;">
                    <li style="padding: 8px 0;"><a href="#" style="text-decoration: none; color: #333;">Premier League</a></li>
                    <li style="padding: 8px 0;"><a href="#" style="text-decoration: none; color: #333;">La Liga</a></li>
                    <li style="padding: 8px 0;"><a href="#" style="text-decoration: none; color: #333;">Bundesliga</a></li>
                    <li style="padding: 8px 0;"><a href="#" style="text-decoration: none; color: #333;">Serie A</a></li>
                    <li style="padding: 8px 0;"><a href="#" style="text-decoration: none; color: #333;">Ligue 1</a></li>
                </ul>
            </ul>
        </div>
    </div>
</div>




            <div class="col-md-6">
                <div class="date-selector-container">
                    <div class="date-selector-header">
                        <div>Calendar month: <strong><?= date(
                            "F Y",
                            strtotime($selectedDate)
                        ) ?></strong></div>
                        <div>
                            <div id="calendar-wrapper" style="position: relative; display: inline-block;">
                                <div id="calendar-trigger" style="cursor: pointer;">
                                    <i class="fa fa-calendar"></i> View calendar
                                </div>
                                <input type="text" id="calendar-picker" style="display: none;" autocomplete="off" />
                            </div>


                            <input type="text" id="calendar-picker" class="form-control" style="display:none;" readonly>

                        </div>
                    </div>
                    <div class="date-scroll-wrapper">
                    <div class="date-scroll">
                        <?php $today = date("Y-m-d"); ?>
                        <a href="?date=<?= $today ?>&tz=<?= urlencode(
                              $timezone
                          ) ?>" class="arrow-button" title="Go to today">
                            <i class="fa fa-calendar-day"></i> Today
                        </a>
                        <?php $prevDate = date(
                            "Y-m-d",
                            strtotime($selectedDate . " -1 day")
                        ); ?>
                        <a href="?date=<?= $prevDate ?>&tz=<?= urlencode(
                              $timezone
                          ) ?>" class="arrow-button">&larr;</a>


                        <?php foreach ($dates as $day): ?>
                            <a href="?date=<?= $day[
                                "value"
                            ] ?>&offset=<?= $offset ?>&tz=<?= urlencode($timezone) ?>" class="date-button <?= $selectedDate === $day["value"]
                                     ? "active"
                                     : "" ?> <?= $day["isToday"] ? "today" : "" ?>">
                                <span><?= $day["isToday"]
                                    ? "TODAY"
                                    : strtoupper(
                                        date("D", strtotime($day["value"]))
                                    ) ?></span>
                                <strong><?= date("j", strtotime($day["value"])) ?></strong>
                            </a>
                        <?php endforeach; ?>

                        <?php $nextDate = date(
                            "Y-m-d",
                            strtotime($selectedDate . " +1 day")
                        ); ?>
                        <a href="?date=<?= $nextDate ?>&tz=<?= urlencode(
                              $timezone
                          ) ?>" class="arrow-button">&rarr;</a>

                    </div>
                    </div>
                </div>




                <?php function get_competition_image($competition, $area)
                {
                    // Use a flag for national leagues
                    $countryToCode = [
                        "Brazil" => "br",
                        "England" => "gb",
                        "Germany" => "de",
                        "France" => "fr",
                        "Spain" => "es",
                        "Italy" => "it",
                        "USA" => "us",
                        "Argentina" => "ar",
                        "Portugal" => "pt",
                        "Netherlands" => "nl",
                    ]; // Logos for special competitions
                    $competitionLogos = [
                        "UEFA Champions League" => base_url(
                            "assets/images/uefa-champions-league-1.svg"
                        ),
                        "Copa Libertadores" => base_url(
                            "assets/images/Copa_Libertadores_logo.svg.png"
                        ),
                        // Add more logos as needed
                    ];
                    if (isset($competitionLogos[$competition])) {
                        return $competitionLogos[$competition];
                    }
                    $code = strtolower($countryToCode[$area] ?? "xx");
                    return "https://flagcdn.com/24x18/{$code}.png";
                } ?>
                <?php if (empty($matches)): ?>
                    <p>No matches to show right now.</p>
                <?php // Ensure it's a proper ISO 8601 format (UTC)
                    // Ensure it's a proper ISO 8601 format (UTC)
                    else:
                    $grouped_upcoming = [];
$grouped_live = [];
$grouped_finished = [];

foreach ($matches as $match) {
    $comp = $match["competition"];
    $area = $match["area"];
    $image = get_competition_image($comp, $area);

    switch ($match["status"]) {
        case "IN_PLAY":
        case "LIVE":
            $grouped_live[$comp]["matches"][] = $match;
            $grouped_live[$comp]["area"] = $area;
            $grouped_live[$comp]["flag"] = $image;
            break;

        case "TIMED":
        case "SCHEDULED":
            $grouped_upcoming[$comp]["matches"][] = $match;
            $grouped_upcoming[$comp]["area"] = $area;
            $grouped_upcoming[$comp]["flag"] = $image;
            break;

        case "FINISHED":
            $grouped_finished[$comp]["matches"][] = $match;
            $grouped_finished[$comp]["area"] = $area;
            $grouped_finished[$comp]["flag"] = $image;
            break;
    }
}

                    ?>
                <?php if (!empty($grouped_live)): ?>
                    <div class="panel_s">
                            <div class="panel-body">
                                <h2>
                                    <?php
                                    $hasLive = false;
                                    foreach ($matches as $m) {
                                        if ($m["status"] === "LIVE") {
                                            $hasLive = true;
                                            break;
                                        }
                                    }
                                    echo $hasLive
                                        ? "Live Football Scores"
                                        : "Upcoming Football Fixtures";
                                    ?>
                                </h2>
                                <div style="font-size: 14px; color: #555; margin: 10px 0;">
                                    Showing matches for <?= $selectedDate ?> in timezone: <strong><?= $timezone ?></strong>
                                </div>


                                <?php foreach ($grouped_live as $comp => $info): ?>
                                    <div class="score-card">
                                        <div class="competition" style="display: flex; align-items: center; gap: 10px;">
                                            <?php if ($info["flag"]): ?>
                                                <img src="<?= $info[
                                                    "flag"
                                                ] ?>" alt="" style="width: 20px; height: 14px;">
                                            <?php endif; ?>
                                            <strong><?= $comp ?></strong>
                                            <span style="font-size: 12px; color: #aaa; margin-left: auto;">
                                                <?= $info["area"] ?>
                                            </span>
                                        </div>

                                        <?php foreach ($info["matches"] as $match): ?>
                                            <div class="match-row"
                                                style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px;">
                                                <div class="team" style="display: flex; align-items: center; gap: 6px;">
                                                    <?php $raw = json_decode(
                                                        $match["raw_data"],
                                                        true
                                                    ); ?>
                                                    <img src="<?= $raw["homeTeam"]["crest"] ??
                                                        "" ?>" style="width: 20px; height: 20px;">
                                                    <span><?= $raw["homeTeam"]["shortName"] ??
                                                        $match["home_team"] ?></span>
                                                </div>
                                                <div class="vs" style="text-align: center; font-size: 14px;">
                                                    <?php
                                                    $homeScore = $match["home_score"];
                                                    $awayScore = $match["away_score"];
                                                    if ($match["status"] === "LIVE") {
                                                        echo "<span style='color: green;'>Live</span>";
                                                    } elseif ($match["status"] === "TIMED") {
                                                        $utcFormatted = gmdate(
                                                            "Y-m-d\TH:i:s\Z",
                                                            strtotime($match["kickoff_time"])
                                                        );
                                                        echo '<span class="utc-time" data-utc="' .
                                                            $utcFormatted .
                                                            '"></span>';
                                                    } elseif (
                                                        $homeScore !== null &&
                                                        $awayScore !== null
                                                    ) {
                                                        echo "{$homeScore} - {$awayScore}";
                                                    } else {
                                                        echo "-";
                                                    }
                                                    ?>
                                                </div>
                                                <div class="team"
                                                    style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                                    <span><?= $raw["awayTeam"]["shortName"] ??
                                                        $match["away_team"] ?></span>
                                                    <img src="<?= $raw["awayTeam"]["crest"] ??
                                                        "" ?>" style="width: 20px; height: 20px;">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>

                                </div> 
                            </div>
                            <?php endif; ?>  
                    


                    <?php if (!empty($grouped_upcoming)): ?>


                        <div class="panel_s">
                            <div class="panel-body">
                                <h2>
                                    <?php
                                    $hasLive = false;
                                    foreach ($matches as $m) {
                                        if ($m["status"] === "LIVE") {
                                            $hasLive = true;
                                            break;
                                        }
                                    }
                                    echo $hasLive
                                        ? "Live Football Scores"
                                        : "Upcoming Football Fixtures";
                                    ?>
                                </h2>
                                <div style="font-size: 14px; color: #555; margin: 10px 0;">
                                    Showing matches for <?= $selectedDate ?> in timezone: <strong><?= $timezone ?></strong>
                                </div>


                                <?php foreach ($grouped_upcoming as $comp => $info): ?>
                                    <div class="score-card">
                                        <div class="competition" style="display: flex; align-items: center; gap: 10px;">
                                            <?php if ($info["flag"]): ?>
                                                <img src="<?= $info[
                                                    "flag"
                                                ] ?>" alt="" style="width: 20px; height: 14px;">
                                            <?php endif; ?>
                                            <strong><?= $comp ?></strong>
                                            <span style="font-size: 12px; color: #aaa; margin-left: auto;">
                                                <?= $info["area"] ?>
                                            </span>
                                        </div>

                                        <?php foreach ($info["matches"] as $match): ?>
                                            <div class="match-row"
                                                style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px;">
                                                <div class="team" style="display: flex; align-items: center; gap: 6px;">
                                                    <?php $raw = json_decode(
                                                        $match["raw_data"],
                                                        true
                                                    ); ?>
                                                    <img src="<?= $raw["homeTeam"]["crest"] ??
                                                        "" ?>" style="width: 20px; height: 20px;">
                                                    <span><?= $raw["homeTeam"]["shortName"] ??
                                                        $match["home_team"] ?></span>
                                                </div>
                                                <div class="vs" style="text-align: center; font-size: 14px;">
                                                    <?php
                                                    $homeScore = $match["home_score"];
                                                    $awayScore = $match["away_score"];
                                                    if ($match["status"] === "LIVE") {
                                                        echo "<span style='color: green;'>Live</span>";
                                                    } elseif ($match["status"] === "TIMED") {
                                                        $utcFormatted = gmdate(
                                                            "Y-m-d\TH:i:s\Z",
                                                            strtotime($match["kickoff_time"])
                                                        );
                                                        echo '<span class="utc-time" data-utc="' .
                                                            $utcFormatted .
                                                            '"></span>';
                                                    } elseif (
                                                        $homeScore !== null &&
                                                        $awayScore !== null
                                                    ) {
                                                        echo "{$homeScore} - {$awayScore}";
                                                    } else {
                                                        echo "-";
                                                    }
                                                    ?>
                                                </div>
                                                <div class="team"
                                                    style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                                    <span><?= $raw["awayTeam"]["shortName"] ??
                                                        $match["away_team"] ?></span>
                                                    <img src="<?= $raw["awayTeam"]["crest"] ??
                                                        "" ?>" style="width: 20px; height: 20px;">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>

                                </div> 
                            </div>
                            <?php endif; ?>   
                                              
                    
                <?php endif; ?>



                <?php if (!empty($grouped_finished)): ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <h2>
                            <?php
                            $hasLive = false;
                            foreach ($matches as $m) {
                                if ($m["status"] === "LIVE") {
                                    $hasLive = true;
                                    break;
                                }
                            }
                            echo $hasLive
                                ? "Live Football Scores"
                                : "Football Fixtures Results";
                            ?>
                        </h2>
                        <div style="font-size: 14px; color: #555; margin: 10px 0;">
                            Showing matches for <?= $selectedDate ?> in timezone: <strong><?= $timezone ?></strong>
                        </div>


                        <?php foreach ($grouped_finished as $comp => $info): ?>
                            <div class="score-card">
                                <div class="competition" style="display: flex; align-items: center; gap: 10px;">
                                    <?php if ($info["flag"]): ?>
                                        <img src="<?= $info[
                                            "flag"
                                        ] ?>" alt="" style="width: 20px; height: 14px;">
                                    <?php endif; ?>
                                    <strong><?= $comp ?></strong>
                                    <span style="font-size: 12px; color: #aaa; margin-left: auto;">
                                        <?= $info["area"] ?>
                                    </span>
                                </div>

                                <?php foreach ($info["matches"] as $match): ?>
                                    <div class="match-row" style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px;">
                                        <div class="team" style="display: flex; align-items: center; gap: 6px;">
                                        <span style="font-size: 12px; font-weight: bold; color: #555; 8px;">FT</span>
                                            <?php $raw = json_decode($match["raw_data"], true); ?>
                                            <img src="<?= $raw["homeTeam"]["crest"] ??
                                                "" ?>" style="width: 20px; height: 20px;">
                                            <span><?= $raw["homeTeam"]["shortName"] ??
                                                $match["home_team"] ?></span>
                                        </div>
                                        <div class="vs" style="text-align: center; font-size: 14px;">
                                            <?php
                                            $homeScore = $match["home_score"];
                                            $awayScore = $match["away_score"];
                                            if (
                                                $match["status"] === "FINISHED" &&
                                                $homeScore !== null &&
                                                $awayScore !== null
                                            ) {
                                                echo "{$homeScore} - {$awayScore}";
                                            } elseif ($match["status"] === "LIVE") {
                                                echo "<span style='color: green;'>Live</span>";
                                            } elseif ($match["status"] === "TIMED") {
                                                // Ensure it's a proper ISO 8601 format (UTC)
                                                $utcFormatted = gmdate(
                                                    "Y-m-d\TH:i:s\Z",
                                                    strtotime($match["kickoff_time"])
                                                );
                                                echo '<span class="utc-time" data-utc="' .
                                                    $utcFormatted .
                                                    '"></span>';
                                            } elseif ($homeScore !== null && $awayScore !== null) {
                                                echo "{$homeScore} - {$awayScore}";
                                            } else {
                                                echo "-";
                                            }
                                            ?>
                                        </div>
                                        <div class="team"
                                            style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                            <span><?= $raw["awayTeam"]["shortName"] ??
                                                $match["away_team"] ?></span>
                                            <img src="<?= $raw["awayTeam"]["crest"] ??
                                                "" ?>" style="width: 20px; height: 20px;">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>



                    </div>
                </div>
                <?php endif; ?>
            </div>  
            
            <!-- Right Sidebar 1 -->
    <div class="col-md-3">
        <div class="panel_s">
            <div class="panel-body">
                <h4>Featured Matches</h4>
                <hr>
            </div>
        </div>
    </div>

    <!-- Right Sidebar 2 -->
    <div class="col-md-3">
        <div class="panel_s">
            <div class="panel-body">
                <h4>Top players</h4>
                <hr>
            </div>
        </div>
    </div>
            

        </div>

    </div>

 <!-- All your existing match content (date scroll, fixtures, results, etc.) goes here -->
<?php endif; ?>

</div>

<?php
$activeTab = $this->input->get('tab') ?? 'watchlist';
if ($activeTab === 'watchlist'):
?>
    <!-- All your existing match content (date scroll, fixtures, results, etc.) goes here -->
<?php endif; ?>

<?php init_tail(); ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.utc-time').forEach(function (el) {
            const utcDate = el.getAttribute('data-utc');
            const local = new Date(utcDate);
            const options = {
                hour: 'numeric',
                minute: 'numeric'
            };
            el.textContent = local.toLocaleTimeString([], options);
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        const currentUrl = new URL(window.location.href);

        if (!currentUrl.searchParams.has('tz')) {
            currentUrl.searchParams.set('tz', tz);
            window.location.href = currentUrl.toString(); // reload with timezone
        }
    });
</script>
<script>
    $(document).ready(function () {
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;

        $("#calendar-picker").datepicker({
            dateFormat: "yy-mm-dd",
            showAnim: "fadeIn",
            appendTo: "#calendar-wrapper", // force calendar to stay inside the wrapper
            beforeShow: function (input, inst) {
                setTimeout(function () {
                    const trigger = $("#calendar-trigger");
                    const picker = $("#ui-datepicker-div");
                    const offset = trigger.offset();
                    picker.css({
                        top: offset.top + trigger.outerHeight(),
                        left: offset.left - 180, // Shift 60px to the left
                        zIndex: 9999
                    });
                }, 0);
            },
            onSelect: function (dateText) {
                const url = new URL(window.location.href);
                url.searchParams.set("date", dateText);
                url.searchParams.set("offset", 0);
                url.searchParams.set("tz", tz);
                window.location.href = url.toString();
            }
        });

        $("#calendar-trigger").on("click", function () {
            $("#calendar-picker").datepicker("show");
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scrollContainer = document.querySelector('.date-scroll');
        const activeDate = document.querySelector('.date-scroll .date-button.active');

        if (scrollContainer && activeDate) {
            const scrollOffset = activeDate.offsetLeft - scrollContainer.offsetWidth / 2 + activeDate.offsetWidth /
                2;
            scrollContainer.scrollTo({
                left: scrollOffset,
                behavior: 'smooth'
            });
        }
    });
</script>

<script>
function toggleLeagues() {
    const list = document.getElementById('topLeagues');
    const chevron = document.getElementById('chevron');
    const isVisible = list.style.display === 'block';

    list.style.display = isVisible ? 'none' : 'block';
    chevron.className = isVisible ? 'fa fa-chevron-down' : 'fa fa-chevron-up';
}
</script>
