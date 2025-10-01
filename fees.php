<?php
session_start();
if(!isset($_SESSION['user'])) header("Location: login.php");

$host="localhost"; $user="root"; $pass=""; $db="erp_db";
$conn = new mysqli($host,$user,$pass,$db);
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// Create fees table if not exists
$check_table = $conn->query("SHOW TABLES LIKE 'fees'");
if($check_table->num_rows == 0) {
    $create_table = "CREATE TABLE fees (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        student_id INT(6) UNSIGNED,
        total_fee DECIMAL(10,2) NOT NULL,
        paid_amount DECIMAL(10,2) DEFAULT 0,
        due_amount DECIMAL(10,2) DEFAULT 0,
        status ENUM('paid', 'pending', 'executive') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id)
    )";
    $conn->query($create_table);
    
    // Insert sample data
    $sample_data = [
        [1, 50000, 50000, 0, 'paid'],
        [2, 50000, 30000, 20000, 'pending'],
        [3, 45000, 15000, 30000, 'executive'],
        [4, 48000, 48000, 0, 'paid'],
        [5, 52000, 40000, 12000, 'pending']
    ];
    
    $stmt = $conn->prepare("INSERT INTO fees (student_id, total_fee, paid_amount, due_amount, status) VALUES (?, ?, ?, ?, ?)");
    foreach($sample_data as $data) {
        $stmt->bind_param("iddds", $data[0], $data[1], $data[2], $data[3], $data[4]);
        $stmt->execute();
    }
    $stmt->close();
}

// Add payment
if(isset($_POST['add_payment'])){
    $student_id = $_POST['student_id'];
    $amount = $_POST['amount'];
    
    // Get current fee record
    $fee_result = $conn->query("SELECT * FROM fees WHERE student_id='$student_id'");
    if($fee_result->num_rows > 0) {
        $fee = $fee_result->fetch_assoc();
        $new_paid = $fee['paid_amount'] + $amount;
        $new_due = $fee['total_fee'] - $new_paid;
        $status = $new_due <= 0 ? 'paid' : ($new_due > 20000 ? 'executive' : 'pending');
        
        $conn->query("UPDATE fees SET paid_amount='$new_paid', due_amount='$new_due', status='$status' WHERE student_id='$student_id'");
        $success = "Payment added successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fees - ERP System</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>ERP System</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span>🏠</span>
                    Dashboard
                </a>
                <a href="students.php" class="nav-item">
                    <span>👨‍🎓</span>
                    Students
                </a>
                <a href="attendance.php" class="nav-item">
                    <span>📊</span>
                    Attendance
                </a>
                <a href="fees.php" class="nav-item active">
                    <span>💰</span>
                    Fees
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <span>🚪</span>
                    Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="module-container">
                <div class="module-header">
                    <div>
                        <h1>ERP System</h1>
                        <div class="module-subtitle">Fee Management - Track payments and manage outstanding dues</div>
                    </div>
                </div>

                <?php if(isset($success)): ?>
                    <div class="alert success"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Search Bar -->
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Search fee records...">
                </div>

                <!-- Fees Table -->
                <div class="content-card">
                    <table class="fees-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Total Fee</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $records = $conn->query("SELECT f.*, s.name, s.roll_no, s.class 
                                                    FROM fees f 
                                                    JOIN students s ON f.student_id = s.id 
                                                    ORDER BY s.name");
                            while($r = $records->fetch_assoc()){
                                $initials = getInitials($r['name']);
                                $status_class = 'status-' . $r['status'];
                                echo "<tr>
                                        <td>
                                            <div class='student-info'>
                                                <div class='student-avatar'>$initials</div>
                                                <div>
                                                    <div class='student-name'>{$r['name']}</div>
                                                    <div style='font-size: 0.875rem; color: #64748b;'>
                                                        {$r['roll_no']} • {$r['class']}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class='currency'>₹" . number_format($r['total_fee']) . "</td>
                                        <td class='currency paid'>₹" . number_format($r['paid_amount']) . "</td>
                                        <td class='currency due'>₹" . number_format($r['due_amount']) . "</td>
                                        <td><span class='$status_class'>" . ucfirst($r['status']) . "</span></td>
                                        <td>
                                            <button type='button' class='payment-btn' onclick='openPaymentModal({$r['student_id']}, \"{$r['name']}\")'>
                                                Add Payment
                                            </button>
                                        </td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 400px;">
            <h3 style="margin-bottom: 1rem;">Add Payment</h3>
            <form method="POST" id="paymentForm">
                <input type="hidden" name="student_id" id="modalStudentId">
                <div class="form-group">
                    <label class="form-label">Student</label>
                    <input type="text" id="modalStudentName" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="Enter amount" required>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" name="add_payment" class="btn btn-primary">Add Payment</button>
                    <button type="button" onclick="closePaymentModal()" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPaymentModal(studentId, studentName) {
            document.getElementById('modalStudentId').value = studentId;
            document.getElementById('modalStudentName').value = studentName;
            document.getElementById('paymentModal').style.display = 'flex';
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
            document.getElementById('paymentForm').reset();
        }

        // Close modal when clicking outside
        document.getElementById('paymentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePaymentModal();
            }
        });
    </script>
</body>
</html>

<?php
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return substr($initials, 0, 2);
}
?>