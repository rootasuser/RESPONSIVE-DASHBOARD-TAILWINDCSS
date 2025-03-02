<?php

require_once __DIR__ . '/../Config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    echo "<script>window.location.href='../../index.php';</script>";
    exit();
}


if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $message = "CSRF token mismatch!";
            $messageType = "error";
        } else {
            if (isset($_POST['add_criteria'])) {
                $category = trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS));
                $criteria = trim(filter_input(INPUT_POST, 'criteria', FILTER_SANITIZE_SPECIAL_CHARS));
                $percentage = filter_input(INPUT_POST, 'percentage', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 100]]);

                if (empty($category) || empty($criteria) || $percentage === false) {
                    $message = "Invalid input values!";
                    $messageType = "error";
                } else {
        
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM evaluation_criteria WHERE category = :category AND criteria = :criteria");
                    $stmt->bindParam(":category", $category);
                    $stmt->bindParam(":criteria", $criteria);
                    $stmt->execute();
                    $exists = $stmt->fetchColumn();

                    if ($exists > 0) {
                        $message = "This category and criteria combination already exists!";
                        $messageType = "error";
                    } else {
                        // Insert the new record
                        $stmt = $conn->prepare("INSERT INTO evaluation_criteria (category, criteria, percentage) VALUES (:category, :criteria, :percentage)");
                        $stmt->bindParam(":category", $category);
                        $stmt->bindParam(":criteria", $criteria);
                        $stmt->bindParam(":percentage", $percentage, PDO::PARAM_INT);

                        if ($stmt->execute()) {
                            $message = "Added successfully!";
                            $messageType = "success";
                        } else {
                            $message = "Failed to add!";
                            $messageType = "error";
                        }
                    }
                }
            }

            if (isset($_POST['delete_id'])) {
                $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);

                if (!$delete_id) {
                    $message = "Invalid ID!";
                    $messageType = "error";
                } else {
                    $stmt = $conn->prepare("DELETE FROM evaluation_criteria WHERE id = :id");
                    $stmt->bindParam(":id", $delete_id, PDO::PARAM_INT);

                    if ($stmt->execute()) {
                        $message = "Deleted successfully!";
                        $messageType = "success";
                    } else {
                        $message = "Failed to delete!";
                        $messageType = "error";
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    $message = "An error occurred: " . $e->getMessage();
    $messageType = "error";
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Evaluation Criteria</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">

    <style>
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        .toast {
            opacity: 1;
            transition: opacity 0.5s ease-in-out;
        }

        /**.toast
            Wave text
         */

         @keyframes wave {
        0% { transform: translateY(0); }
        25% { transform: translateY(-8px); }
        50% { transform: translateY(5px); }
        75% { transform: translateY(-5px); }
        100% { transform: translateY(0); }
    }

    .wave-text span {
        display: inline-block;
        animation: wave 1.5s infinite ease-in-out;
    }

    .wave-text span:nth-child(1) { animation-delay: 0s; }
    .wave-text span:nth-child(2) { animation-delay: 0.1s; }
    .wave-text span:nth-child(3) { animation-delay: 0.2s; }
    .wave-text span:nth-child(4) { animation-delay: 0.3s; }
    .wave-text span:nth-child(5) { animation-delay: 0.4s; }
    .wave-text span:nth-child(6) { animation-delay: 0.5s; }
    .wave-text span:nth-child(7) { animation-delay: 0.6s; }
    .wave-text span:nth-child(8) { animation-delay: 0.7s; }
    .wave-text span:nth-child(9) { animation-delay: 0.8s; }
    .wave-text span:nth-child(10) { animation-delay: 0.9s; }
    .wave-text span:nth-child(11) { animation-delay: 1s; }
    .wave-text span:nth-child(12) { animation-delay: 1.1s; }
    .wave-text span:nth-child(13) { animation-delay: 1.2s; }

    /**.Calculator
     */
    .custom-calculator {
            max-width: 100%;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .custom-display {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            font-size: 1.2em;
            text-align: right;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .custom-buttons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .custom-button {
            padding: 10px;
            font-size: 1.2em;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            cursor: pointer;
        }
        .custom-button:hover {
            background-color: #e9e9e9;
        }
        .custom-operator {
            background-color: #ff9500;
            color: white;
        }
        .custom-operator:hover {
            background-color: #e08900;
        }
    </style>
</head>
<body>


<?php if (!empty($message)): ?>
    <div class="toast-container">
    <div class="toast text-white p-3" id="toastMessage"
         style="background-color: hsla(228,100%,10%,0.71);">
        <strong class="wave-text">
            <?php 
                $text = htmlspecialchars($message);
                $letters = str_split($text);
                foreach ($letters as $letter) {
                    echo "<span>$letter</span>";
                }
            ?>
        </strong>
    </div>
</div>

    <script>
        setTimeout(function() {
            document.getElementById('toastMessage').style.opacity = '0';
            setTimeout(function() {
                document.getElementById('toastMessage').remove();
            }, 500);
        }, 3000);
    </script>
<?php endif; ?>


    <div class="container mt-4">
        <div class="card border-0" style="background-color: hsla(227,89%,15%,0.41);">
        <div class="card-header d-flex justify-content-between align-items-center">
    <h3></h3>
    <div class="d-flex">
        <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#calculatorModal">
            <i class="fas fa-calculator"></i> Calculator
        </button>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addEvaluationModal">
            <i class="fa fa-plus"></i> Add
        </button>
    </div>
</div>
            <div class="card-body bg-white">

            <div class="table-responsive">
                <table id="evaluationTable" class="table table">
                    <thead>
                        <tr>
                            <th class='d-none'>ID</th>
                            <th>Category</th>
                            <th>Criteria</th>
                            <th>Percentage (%)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
try {
    $stmt = $conn->query("SELECT * FROM evaluation_criteria");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td class='d-none'>{$row['id']}</td>
                <td>{$row['category']}</td>
                <td>{$row['criteria']}</td>
                <td class='percentage'>{$row['percentage']}</td>
                <td>
                    <button class='btn btn-sm btn-primary view-percentage' 
                        data-bs-toggle='modal' 
                        data-bs-target='#byCategoryCriteriaModal' 
                        data-id='{$row['id']}'>
                        <i class='fas fa-eye'></i>
                    </button>
                    
                    <form method='POST' style='display:inline;'>
                        <input type='hidden' name='delete_id' value='{$row['id']}'>
                        <input type='hidden' name='csrf_token' value='{$_SESSION['csrf_token']}'>
                        <button type='submit' class='btn btn-sm btn-danger'><i class='fas fa-trash'></i> Delete</button>
                    </form>
                </td>
            </tr>";
    }
} catch (Exception $e) {
    echo "<tr><td colspan='5'>Err loading data</td></tr>";
}
?>

                    </tbody>
                </table>
               
                    <div id='totalPercentage' style="font-weight: bolder">Overall Percentage: <span id='percentageTotal'></span>%</div>
                    <div style="color: red;"><span id="percentageAlert"></span></div>

                    </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addEvaluationModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Evaluation Criteria</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="addCriteriaForm" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="add_criteria" value="1">
                        <div class="form-group">
                            <label>Category</label>
                            <input type="text" class="form-control" name="category" required>
                        </div>
                        <div class="form-group">
                            <label>Criteria</label>
                            <input type="text" class="form-control" name="criteria" required>
                        </div>
                        <div class="form-group">
                            <label>Percentage (%)</label>
                            <input type="number" class="form-control" name="percentage" min="1" max="100" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!--- By Category & Criteria Percentage ---> 
    <div class="modal fade" id="byCategoryCriteriaModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Category & Criteria Percentage</h5>
                <button type="button" class="btn-close btn-sm border-0 text-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div id="modalContent">Loading...</div>
            </div>
        </div>
    </div>
</div>


<!-- Calculator -->
<div class="modal fade" id="calculatorModal" tabindex="-1" role="dialog" aria-labelledby="calculatorModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
    <div class="d-flex align-items-end justify-content-end mx-2 mt-2">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
    </div>
      <div class="modal-body">
        <h3 class="text-center">System Calculator</h3>
      <div class="custom-calculator">
        <input type="text" class="custom-display" id="custom-display" readonly>
        <div class="custom-buttons">
            <div class="custom-button" onclick="appendNumber('7')">7</div>
            <div class="custom-button" onclick="appendNumber('8')">8</div>
            <div class="custom-button" onclick="appendNumber('9')">9</div>
            <div class="custom-button custom-operator" onclick="setOperator('/')">/</div>
            <div class="custom-button" onclick="appendNumber('4')">4</div>
            <div class="custom-button" onclick="appendNumber('5')">5</div>
            <div class="custom-button" onclick="appendNumber('6')">6</div>
            <div class="custom-button custom-operator" onclick="setOperator('*')">*</div>
            <div class="custom-button" onclick="appendNumber('1')">1</div>
            <div class="custom-button" onclick="appendNumber('2')">2</div>
            <div class="custom-button" onclick="appendNumber('3')">3</div>
            <div class="custom-button custom-operator" onclick="setOperator('-')">-</div>
            <div class="custom-button" onclick="appendNumber('0')">0</div>
            <div class="custom-button" onclick="appendNumber('.')">.</div>
            <div class="custom-button custom-operator" onclick="calculate()">=</div>
            <div class="custom-button custom-operator" onclick="setOperator('+')">+</div>
            <div class="custom-button" onclick="clearDisplay()">C</div>
        </div>
    </div>
      </div>
     
    </div>
  </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

<script>

    $(document).ready(function() {
        $('#evaluationTable').DataTable();
    });


    // ===> Overall percentage
    function calculateTotalPercentage() {
    let total = 0;
    document.querySelectorAll('.percentage').forEach(el => total += parseInt(el.textContent));
    document.getElementById('percentageTotal').textContent = total;

    let alertMessage = total > 100 ? '<i class="fas fa-exclamation-triangle"></i> You have exceeded the maximum percentage!' : '';
    document.getElementById('percentageAlert').innerHTML = alertMessage;
    }
    calculateTotalPercentage();

    // ===> Category, Criteria Percentage
    $(document).ready(function () {
    $(document).on("click", ".view-percentage", function () { 
        let id = $(this).data("id"); 

        $.ajax({
            url: "../../Models/percentageModel.php",
            type: "GET",
            data: { id: id },
            dataType: "json",
            success: function (response) {
                if (response.error) {
                    $("#modalContent").html("<p class='text-danger'>" + response.error + "</p>");
                } else {
                    let totalPercentage = 0;
                    let content = "<table class='table table-bordered'>";
                    content += "<thead><tr><th class='d-none'>ID</th><th>Category</th><th>Criteria</th><th>Percentage</th></tr></thead><tbody>";

                    response.forEach(function (row) {
                        totalPercentage += parseFloat(row.total_percentage);
                        content += "<tr><td class='d-none'>" + row.id + "</td><td>" + row.category + "</td><td>" + row.criteria + "</td><td>" + row.total_percentage + "%</td></tr>";
                    });

                    content += `<tr class="table-info">
                                    <td colspan="2"><strong>Total</strong></td>
                                    <td colspan="2"><strong>${totalPercentage}%</strong></td>
                                </tr>`;
                    content += "</tbody></table>";

                    $("#modalContent").html(content);
                }

                $("#byCategoryCriteriaModal").modal("show");
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
                $("#modalContent").html("<p class='text-danger'>Failed to load data.</p>");
                $("#byCategoryCriteriaModal").modal("show"); 
            }
        });
    });
});



// ===> Calculator
let currentInput = '';
let currentOperator = null;
let firstOperand = null;

function appendNumber(number) {
    currentInput += number;
    updateDisplay();
}

function setOperator(operator) {
    if (currentOperator) {
        calculate();
    }
    firstOperand = parseFloat(currentInput);
    currentOperator = operator;
    currentInput += ' ' + getDisplayOperator(operator) + ' ';
    updateDisplay();
}

function calculate() {
    if (currentOperator && currentInput) {
        const secondOperand = parseFloat(currentInput.split(' ')[2]);
        let result;
        switch (currentOperator) {
            case '+':
                result = firstOperand + secondOperand;
                break;
            case '-':
                result = firstOperand - secondOperand;
                break;
            case '*':
                result = firstOperand * secondOperand;
                break;
            case '/':
                result = firstOperand / secondOperand;
                break;
            default:
                return;
        }
        currentInput = result.toString();
        updateDisplay();
        currentOperator = null;
        firstOperand = null;
    }
}

function clearDisplay() {
    currentInput = '';
    currentOperator = null;
    firstOperand = null;
    document.getElementById('custom-display').value = '';
}

function updateDisplay() {
    document.getElementById('custom-display').value = currentInput;
}

function getDisplayOperator(operator) {
    switch (operator) {
        case '+':
            return '+';
        case '-':
            return '-';
        case '*':
            return 'x';
        case '/':
            return '%';
        default:
            return '';
    }
}
    
</script>


</body>
</html>
