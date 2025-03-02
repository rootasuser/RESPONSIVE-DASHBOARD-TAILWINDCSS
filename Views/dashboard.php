<?php
session_start();
require_once __DIR__ . '/../Config/database.php';

use Config\Database;


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Judge') {
    header("Location: ../../index.php");
    exit();
}
session_regenerate_id(true);
$user = $_SESSION['user'];
$userId = $user['id'];


$db = new Database();
$conn = $db->connect();
if (!$conn) {
    die(json_encode(['error' => 'Db failed.']));
}


$query = "SELECT username, fullname FROM users WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindValue(":id", $userId, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$userData) {
    die(json_encode(['error' => 'User not found']));
}


$queryEvent = "SELECT event_name, event_logo, event_banner FROM events WHERE status = 'Active' LIMIT 1";
$stmtEvent = $conn->prepare($queryEvent);
$stmtEvent->execute();
$eventData = $stmtEvent->fetch(PDO::FETCH_ASSOC);
if (!$eventData) {
    die(json_encode(['error' => 'Event not found or inactive']));
}


$queryParticipants = "SELECT id, fullname FROM participants WHERE status = 'Active'";
$stmtParticipants = $conn->prepare($queryParticipants);
$stmtParticipants->execute();
$participants = $stmtParticipants->fetchAll(PDO::FETCH_ASSOC);
if (!$participants) {
    die(json_encode(['error' => 'No participants found']));
}


$queryCriteria = "SELECT category, criteria, percentage FROM evaluation_criteria";
$stmtCriteria = $conn->prepare($queryCriteria);
$stmtCriteria->execute();
$criteria = $stmtCriteria->fetchAll(PDO::FETCH_ASSOC);
if (!$criteria) {
    die(json_encode(['error' => 'No evaluation criteria found']));
}


$queryScores = "SELECT participant_name, category, criteria, score FROM score_results WHERE judge_id = :judge_id";
$stmtScores = $conn->prepare($queryScores);
$stmtScores->bindValue(":judge_id", $userId, PDO::PARAM_INT);
$stmtScores->execute();
$scores = $stmtScores->fetchAll(PDO::FETCH_ASSOC);
$scoresMap = [];
foreach ($scores as $score) {
    // Map by participant, then category, then criteria
    $scoresMap[$score['participant_name']][$score['category']][$score['criteria']] = $score['score'];
}

$baseUrl = "/tabulation/Views/eventbanner/";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['score'], $_POST['participant_name'], $_POST['category'])) {
    $participantName = trim($_POST['participant_name']);
    $judgeName = $userData['fullname'];
    $scoreValue = floatval($_POST['score']);
    $criteriaText = isset($_POST['criteria']) ? trim($_POST['criteria']) : '';
    $percentage = isset($_POST['percentage']) ? floatval($_POST['percentage']) : 0;
    $category = trim($_POST['category']);


    if ($scoreValue > $percentage) {
        echo json_encode(['error' => 'Score exceeds maximum allowed value']);
        exit();
    }

    $queryInsert = "INSERT INTO score_results (judge_id, judge_name, participant_name, category, criteria, percentage, score)
                    VALUES (:judge_id, :judge_name, :participant_name, :category, :criteria, :percentage, :score)
                    ON DUPLICATE KEY UPDATE score = VALUES(score)";
    $stmtInsert = $conn->prepare($queryInsert);
    $result = $stmtInsert->execute([
        ':judge_id'          => $userId,
        ':judge_name'        => $judgeName,
        ':participant_name'  => $participantName,
        ':category'          => $category,
        ':criteria'          => $criteriaText,
        ':percentage'        => $percentage,
        ':score'             => $scoreValue
    ]);

    if ($result) {
         echo json_encode(['success' => true]);
    } else {
         echo json_encode(['error' => 'Failed to insert/update score']);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Judge Dashboard</title>
   <link rel="stylesheet" href="../public/assets/css/tartine.css">
   <link rel="stylesheet" href="../public/assets/css/fontawesome-free/css/all.min.css">
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
   <style>
      .card, .container { width: 100%; padding: 10px; }
      .card-header-banner {
         position: relative;
         background-size: cover;
         background-position: center;
         padding: 120px 20px 0 20px;
      }
      .card-header-banner .event-logo {
         position: absolute;
         left: 20px;
         top: 20px;
         max-width: 80px;
         max-height: 80px;
         object-fit: cover;
      }
      .event-logo {
         border-radius: 50%;
      }
      .card-header-info {
         background-color: hsla(228,100%,10%,0.71);
         padding: 10px 15px;
         color: yellow;
      }
      .card-header-info .event-name {
         font-size: 1.5rem;
         font-weight: bold;
      }
      .card-header-info .fullname {
         text-align: right;
         font-size: 1rem;
      }
      .logout-btn {
         font-size: 1rem;
         color: #007bff;
         background-color: transparent;
         border: none;
         cursor: pointer;
         text-decoration: underline;
      }
      .logout-btn:hover { color: #0056b3; }
      .card-body { padding: 20px; }
      /* Table styles */
      table {
         width: 100%;
         margin-top: 20px;
         border-collapse: collapse;
      }
      th, td {
         padding: 10px;
         text-align: left;
         border-bottom: 1px solid #ddd;
         font-size: 12px;
      }
      th { background-color: #f2f2f2; }
      .score-input {
         width: 100%;
         max-width: 80px;
         border-color: #007bff;
         padding: 12px 16px;
         font-size: 16px;
         font-weight: bolder;
         border-radius: 5px;
      }
      .criteria-label {
         font-weight: bold;
         margin-bottom: 5px;
      }
      /* Sidebar */
      .sidebar {
         position: fixed;
         top: 0;
         right: 0;
         width: 300px;
         height: 100%;
         background-color: #333;
         color: #fff;
         box-shadow: -4px 0 10px rgba(0, 0, 0, 0.3);
         transform: translateX(100%);
         transition: transform 0.3s ease-in-out;
         z-index: 1000;
      }
      .sidebar.open {
         transform: translateX(0);
      }

      .close-btn {
         position: absolute;
         top: 15px;
         left: 15px;
         font-size: 24px;
         color: #fff;
         cursor: pointer;
         color: red;
         font-weight: bolder;
      }
      .sidebar-content {
         padding: 20px;
         display: flex;
         flex-direction: column;
         justify-content: center;
         align-items: center;
      }
   
      #calculator {
         width: 100%;
         display: flex;
         flex-direction: column;
         align-items: center;
      }
      .calculator-display {
         width: 100%;
         height: 80px;
         background-color: #222;
         color: white;
         text-align: right;
         padding: 20px;
         font-size: 2.5rem;
         margin-bottom: 20px;
         border-radius: 5px;
      }
      .calculator-keys {
         display: grid;
         grid-template-columns: repeat(4, 1fr);
         gap: 10px;
         width: 100%;
      }
      .calculator-keys button {
         background-color: #444;
         border: none;
         padding: 20px;
         font-size: 1.5rem;
         color: white;
         border-radius: 5px;
         cursor: pointer;
         transition: background-color 0.3s ease;
      }
      .calculator-keys button:hover {
         background-color: #555;
      }
      .calculator-keys button:active {
         background-color: #666;
      }

      @media (max-width: 768px) {
         .sidebar { width: 100%; height: 100%; }
         .calculator-display { font-size: 2rem; }
         .calculator-keys button { padding: 15px; font-size: 1.2rem; }
      }
   </style>
</head>
<body>
   <div class="container mt-1 w-100">
      <div class="card w-100">
         <!-- Event Banner Section -->
         <div class="card-header-banner" style="background-image: url('<?php echo htmlspecialchars($baseUrl . $eventData['event_banner']); ?>');">
            <!-- Event Logo -->
            <?php if (!empty($eventData['event_logo'])): ?>
               <img src="<?php echo htmlspecialchars($baseUrl . $eventData['event_logo']); ?>" alt="Event Logo" class="event-logo">
            <?php endif; ?>
         </div>
         <!-- Header Info with Event Name and Judge's Full Name -->
         <div class="card-header-info">
            <div class="d-flex justify-content-between">
               <div class="event-name">
                  <h3 style="font-family: 'Tartine Script Black', sans-serif;"><?php echo htmlspecialchars($eventData['event_name']); ?></h3>
               </div>
               <div class="fullname">
                  <form method="POST" action="../Controllers/logout.php">
                     <button type="submit" class="btn btn-outline-warning btn-sm"><i class="fa fa-sign-out-alt"></i></button>
                  </form>
                  <p><?php echo htmlspecialchars($userData['fullname']); ?></p>
               </div>
            </div>
         </div>
         <!-- Main Content: Participants and Criteria Table -->
         <div class="card-body">
            <div class="table-wrapper">
               <div class="table-responsive">
                  <table>
                     <thead>
                        <tr>
                           <th style="font-size: 12px; white-space: nowrap;">Participants</th>
                           <?php foreach ($criteria as $criterion): ?>
                              <th style="font-size: 12px; white-space: nowrap;">Category: <?php echo ($criterion['category']); ?></th>
                           <?php endforeach; ?>
                        </tr>
                     </thead>
                     <tbody style="font-weight: bolder;">
                        <?php foreach ($participants as $index => $participant): ?>
                           <tr>
                              <td style="display: flex; align-items: center; white-space: nowrap;"><?php echo htmlspecialchars($participant['id']); ?>. <?php echo htmlspecialchars($participant['fullname']); ?></td>
                              <?php foreach ($criteria as $criterion): ?>
                                 <td>
                                    <div>
                                       <div style="display: flex; align-items: center; white-space: nowrap;">
                                          <?php echo htmlspecialchars($criterion['criteria']); ?>
                                       </div>
                                       <div style="color: red;">
                                          <?php echo htmlspecialchars($criterion['percentage']); ?>%
                                       </div>
                                    </div>
                                    <input type="number"
                                       name="scores[<?php echo $index; ?>][<?php echo htmlspecialchars($criterion['category']); ?>][<?php echo htmlspecialchars($criterion['criteria']); ?>]"
                                       class="score-input"
                                       max="<?php echo htmlspecialchars($criterion['percentage']); ?>"
                                       min="0"
                                       required
                                       onclick="handleScoreInput(this, '<?php echo htmlspecialchars($criterion['percentage']); ?>', '<?php echo htmlspecialchars($criterion['criteria']); ?>')"
                                       value="<?php echo isset($scoresMap[$participant['fullname']][$criterion['category']][$criterion['criteria']]) ? htmlspecialchars($scoresMap[$participant['fullname']][$criterion['category']][$criterion['criteria']]) : ''; ?>">
                                 </td>
                              <?php endforeach; ?>
                           </tr>
                        <?php endforeach; ?>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- Sidebar for Score Submission -->
   <div class="sidebar" id="sidebar">
      <div class="close-btn" onclick="closeSidebar()">X</div>
      <div class="sidebar-content">
         <form id="scoreForm" method="POST">
            <input type="hidden" name="participant_name" id="participant_name">
            <input type="hidden" name="category" id="category">
            <input type="hidden" name="criteria" id="criteria">
            <input type="hidden" name="percentage" id="percentage">
            <input type="hidden" name="score" id="score">
            <div class="d-flex justify-content-center align-items-center">
               <label for="scoreDisplay">Score (Max: <span id="maxScore"></span>):</label>
            </div>
            <div id="calculator">
               <div id="display" class="calculator-display">0</div>
               <div class="calculator-keys">
                  <button type="button" onclick="inputDigit('1')">1</button>
                  <button type="button" onclick="inputDigit('2')">2</button>
                  <button type="button" onclick="inputDigit('3')">3</button>
                  <button type="button" onclick="inputDigit('4')">4</button>
                  <button type="button" onclick="inputDigit('5')">5</button>
                  <button type="button" onclick="inputDigit('6')">6</button>
                  <button type="button" onclick="inputDigit('7')">7</button>
                  <button type="button" onclick="inputDigit('8')">8</button>
                  <button type="button" onclick="inputDigit('9')">9</button>
                  <button type="button" onclick="inputDigit('0')">0</button>
                  <button type="button" onclick="clearDisplay()">C</button>
                  <button type="button" onclick="submitScore()">Enter</button>
               </div>
            </div>
         </form>
      </div>
   </div>
   <script>
      let currentInput = '0';
      let activeInput = null; 

      function handleScoreInput(input, maxPercentage, criteriaText) {
         activeInput = input;
         const row = input.closest('tr');
         const participantName = row.cells[0].textContent.trim();
         document.getElementById('participant_name').value = participantName;
        
         const nameMatch = input.name.match(/scores\[\d+\]\[([^\]]+)\]/);
         if (nameMatch) {
            document.getElementById('category').value = nameMatch[1];
         }
         document.getElementById('criteria').value = criteriaText;
         document.getElementById('percentage').value = maxPercentage;
         document.getElementById('maxScore').textContent = maxPercentage;
         currentInput = '0';
         document.getElementById('display').textContent = currentInput;
         openSidebar();
      }

      function openSidebar() {
         document.getElementById('sidebar').classList.add('open');
      }

      function closeSidebar() {
         document.getElementById('sidebar').classList.remove('open');
      }

      function inputDigit(digit) {
         if (currentInput === '0') {
            currentInput = digit;
         } else {
            currentInput += digit;
         }
         document.getElementById('display').textContent = currentInput;
      }

      function clearDisplay() {
         currentInput = '0';
         document.getElementById('display').textContent = currentInput;
      }

      function submitScore() {
         const submittedScore = currentInput;
         document.getElementById('score').value = submittedScore;
         const formData = new FormData(document.getElementById('scoreForm'));
         fetch('', {
            method: 'POST',
            body: formData
         })
         .then(response => response.json())
         .then(data => {
            if (data.success) {
               // success
               if (activeInput) {
                  activeInput.value = submittedScore;
               }
               closeSidebar();
            } else {
               alert('Error submitting score: ' + data.error);
            }
            clearDisplay();
         })
         .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred');
            clearDisplay();
         });
      }
   </script>
</body>
</html>
