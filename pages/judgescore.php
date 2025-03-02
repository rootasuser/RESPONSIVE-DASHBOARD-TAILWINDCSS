<?php
require_once __DIR__ . '/../Config/database.php';

use Config\Database;

try {
    $db = new Database();
    $conn = $db->connect();
} catch (PDOException $e) {
    die("DB fail: " . $e->getMessage());
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
  header("Location: ../../index.php");
  exit();
}


// Fetch scores
$queryAllScores = "SELECT judge_id, judge_name, participant_name, score, percentage, criteria 
                   FROM score_results 
                   ORDER BY judge_id ASC, participant_name ASC, criteria ASC";

try {
    $stmt = $conn->prepare($queryAllScores);
    $stmt->execute();
    $allResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

// Group scores by judge and sum participant scores
$groupedResults = [];
$participantTotals = [];
$criteriaBreakdown = [];

foreach ($allResults as $row) {
    $judge = $row['judge_name'];
    $participant = $row['participant_name'];
    $criteria = $row['criteria'];

    if (!isset($groupedResults[$judge])) {
        $groupedResults[$judge] = [];
    }
    $groupedResults[$judge][] = $row;

    if (!isset($participantTotals[$judge][$participant])) {
        $participantTotals[$judge][$participant] = 0;
    }
    $participantTotals[$judge][$participant] += $row['score'];

    if (!isset($criteriaBreakdown[$judge][$participant])) {
        $criteriaBreakdown[$judge][$participant] = [];
    }
    $criteriaBreakdown[$judge][$participant][] = [
        'criteria' => $criteria,
        'percentage' => $row['percentage'],
        'score' => $row['score']
    ];
}

// Sort participants by total score (Descending) for each judge
foreach ($participantTotals as $judge => &$participants) {
    arsort($participants);
}
unset($participants);

// Calculate overall totals with normalized participant names
$overallTotals = [];
foreach ($participantTotals as $judge => $participants) {
    foreach ($participants as $participant => $score) {
        // Normalize participant name: trim, lowercase, remove numbers/dots, and extra spaces
        $participantClean = preg_replace('/\s+/', ' ', strtolower(trim(preg_replace("/[0-9.]/", "", $participant))));
        if (!isset($overallTotals[$participantClean])) {
            $overallTotals[$participantClean] = 0;
        }
        $overallTotals[$participantClean] += $score;
    }
}
arsort($overallTotals);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Leaderboard</title>
  <style>
    .table thead th { background-color: #f8f9fa; color: #000; }
    .table tbody tr:nth-child(odd) { background-color: #f2f2f2; }
    .table tbody tr:hover { background-color: #e6e6e6; }
    .judge-header { background-color: #007bff; color: white; font-weight: bold; padding: 10px; }
    .trophy { font-size: 20px; margin-right: 5px; }
  </style>
</head>
<body>
<div class="container">

  <ul class="nav nav-tabs" id="judgeTabs" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" id="overall-tab" data-toggle="tab" href="#overall" role="tab">
        Overall
      </a>
    </li>
    <?php foreach ($groupedResults as $judge => $scores): ?>
      <li class="nav-item">
        <a class="nav-link" id="<?= preg_replace('/\s+/', '_', $judge) ?>-tab" data-toggle="tab" href="#<?= preg_replace('/\s+/', '_', $judge) ?>" role="tab">
          <?= htmlspecialchars($judge) ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <div class="tab-content" id="judgeTabsContent">
    <div class="tab-pane fade show active" id="overall" role="tabpanel">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Rank</th>
            <th>Participant</th>
            <th>Total Score</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $rank = 1;
          foreach ($overallTotals as $participant => $totalScore):
            $trophy = ($rank == 1) ? "🏆" : (($rank == 2) ? "🥈" : (($rank == 3) ? "🥉" : ""));
          ?>
            <tr>
              <td><?= $rank . " " . $trophy ?></td>
              <td><?= htmlspecialchars($participant) ?></td>
              <td><?= htmlspecialchars($totalScore) ?></td>
            </tr>
          <?php
            $rank++;
          endforeach;
          ?>
        </tbody>
      </table>
    </div>

    <?php foreach ($groupedResults as $judge => $scores): ?>
      <div class="tab-pane fade" id="<?= preg_replace('/\s+/', '_', $judge) ?>" role="tabpanel">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Rank</th>
              <th>Participant</th>
              <th>Total Score</th>
              <th>Criteria Breakdown</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $rank = 1;
            foreach ($participantTotals[$judge] as $participant => $totalScore):
              $trophy = ($rank == 1) ? "🏆" : (($rank == 2) ? "🥈" : (($rank == 3) ? "🥉" : ""));
            ?>
              <tr>
                <td><?= $rank . " " . $trophy ?></td>
                <td><?= htmlspecialchars(preg_replace("/[0-9.]/", "", $participant)) ?></td>
                <td><?= htmlspecialchars($totalScore) ?></td>
                <td>
                  <ul>
                    <?php foreach ($criteriaBreakdown[$judge][$participant] as $criterion): ?>
                      <li><?= htmlspecialchars($criterion['criteria']) . " - " . htmlspecialchars($criterion['percentage']) . "% (Score: " . htmlspecialchars($criterion['score']) . ")" ?></li>
                    <?php endforeach; ?>
                  </ul>
                </td>
              </tr>
            <?php
              $rank++;
            endforeach;
            ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>