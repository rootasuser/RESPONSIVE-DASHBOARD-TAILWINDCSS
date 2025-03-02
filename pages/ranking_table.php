<?php
require_once __DIR__ . '/../Config/database.php';

use Config\Database;

try {
    $db = new Database();
    $conn = $db->connect();
} catch (PDOException $e) {
    die("Db Err: " . $e->getMessage());
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../../index.php");
    exit();
}


// Fetch scores grouped by participant and criteria
$queryFinalScores = "
    SELECT participant_name, 
           criteria,  
           SUM(score) AS criteria_total
    FROM score_results
    GROUP BY participant_name, criteria
    ORDER BY participant_name, criteria;
";

$scoresData = $conn->query($queryFinalScores)->fetchAll(PDO::FETCH_ASSOC);

// Normalize participant names to merge duplicates
$participants = [];

foreach ($scoresData as $row) {
    // Normalize participant name: trim, lowercase, remove numbers/dots, replace multiple spaces
    $participantKey = strtolower(trim(preg_replace('/\s+/', ' ', preg_replace("/[0-9.]/", "", $row['participant_name']))));
    $participantName = $row['participant_name']; // Store original name for display
    $criteria = $row['criteria'];
    $score = $row['criteria_total'];

    // Initialize participant if not exists
    if (!isset($participants[$participantKey])) {
        $participants[$participantKey] = [
            'name' => $participantName,
            'criteria_scores' => [],
            'total_score' => 0
        ];
    }

    // Sum criteria scores
    if (!isset($participants[$participantKey]['criteria_scores'][$criteria])) {
        $participants[$participantKey]['criteria_scores'][$criteria] = 0;
    }
    $participants[$participantKey]['criteria_scores'][$criteria] += $score;

    // Update total score
    $participants[$participantKey]['total_score'] += $score;
}

// Sort participants by total score (descending)
$sortedParticipants = array_values($participants);
usort($sortedParticipants, function ($a, $b) {
    return $b['total_score'] <=> $a['total_score'];
});

// Assign ranks with tie handling
$finalRanking = [];
$prevTotal = null;
$rank = 1;
$actualRank = 1;

foreach ($sortedParticipants as $data) {
    if ($prevTotal !== null && $data['total_score'] < $prevTotal) {
        $rank = $actualRank;
    }

    $finalRanking[] = [
        'rank' => $rank,
        'participant' => $data['name'],
        'criteria_scores' => $data['criteria_scores'],
        'total' => $data['total_score']
    ];

    $prevTotal = $data['total_score'];
    $actualRank++;
}
?>

<table class="table table-bordered table-striped text-center">
    <thead class="thead-dark">
        <tr>
            <th style="white-space: nowrap;">Rank</th>
            <th style="white-space: nowrap;">Participant</th>
            <th style="white-space: nowrap;">Criteria Scores</th>
            <th style="white-space: nowrap;">Overall Score</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($finalRanking as $rankData): ?>
        <tr>
            <td><strong><?php echo $rankData['rank']; ?></strong></td>
            <td><?php echo htmlspecialchars($rankData['participant']); ?></td>
            <td>
                <?php
                $criteriaStrings = [];
                foreach ($rankData['criteria_scores'] as $criteria => $score) {
                    $criteriaStrings[] = htmlspecialchars($criteria) . ": " . number_format($score, 2);
                }
                echo implode(" | ", $criteriaStrings);
                ?>
            </td>
            <td class="text-end"><strong><?php echo number_format($rankData['total'], 2); ?></strong></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
