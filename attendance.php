<?php
session_start();
if(!isset($_SESSION['user'])) header("Location: login.php");

$host="localhost"; $user="root"; $pass=""; $db="erp_db";
$conn = new mysqli($host,$user,$pass,$db);
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// Create attendance table if not exists
$check_table = $conn->query("SHOW TABLES LIKE 'attendance'");
if($check_table->num_rows == 0) {
    $create_table = "CREATE TABLE attendance (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        student_id INT(6) UNSIGNED,
        date DATE NOT NULL,
        status ENUM('present', 'absent') DEFAULT 'present',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id)
    )";
    $conn->query($create_table);
}

// Handle attendance submission
if(isset($_POST['save_attendance'])) {
    $date = $_POST['date'];
    
    // Check if attendance data exists
    if(isset($_POST['attendance']) && is_array($_POST['attendance'])) {
        foreach($_POST['attendance'] as $student_id => $status) {
            // Check if record exists
            $check = $conn->query("SELECT id FROM attendance WHERE student_id='$student_id' AND date='$date'");
            if($check->num_rows > 0) {
                // Update existing record
                $conn->query("UPDATE attendance SET status='$status' WHERE student_id='$student_id' AND date='$date'");
            } else {
                // Insert new record
                $stmt = $conn->prepare("INSERT INTO attendance (student_id, date, status) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $student_id, $date, $status);
                $stmt->execute();
                $stmt->close();
            }
        }
        $success = "Attendance saved successfully!";
    } else {
        $error = "No attendance data submitted!";
    }
}

// Get students
$students = $conn->query("SELECT * FROM students ORDER BY class, name");

// Initialize counts
$present_count = 0;
$absent_count = 0;

// Get today's attendance if available
$today = date('Y-m-d');
$today_attendance = [];
$attendance_result = $conn->query("SELECT * FROM attendance WHERE date='$today'");
while($row = $attendance_result->fetch_assoc()) {
    $today_attendance[$row['student_id']] = $row['status'];
    if($row['status'] == 'present') $present_count++;
    else $absent_count++;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance - ERP System</title>
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
                <a href="attendance.php" class="nav-item active">
                    <span>📊</span>
                    Attendance
                </a>
                <a href="fees.php" class="nav-item">
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
                        <div class="module-subtitle">Attendance - Mark and track student attendance</div>
                    </div>
                </div>

                <?php if(isset($success)): ?>
                    <div class="alert success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="attendance-container">
                    <!-- Attendance Form -->
                    <div class="attendance-card">
                        <form method="POST" id="attendanceForm">
                            <div class="attendance-header">
                                <div class="attendance-date">
                                    <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" 
                                           class="form-control" style="font-size: 1.25rem; font-weight: 600; border: none; background: transparent; padding: 0;" 
                                           id="attendanceDate">
                                </div>
                            </div>
                            
                            <div class="attendance-stats">
                                <div class="stat-item">
                                    <span class="stat-value" id="presentCount"><?php echo $present_count; ?></span>
                                    <span class="stat-label">Present</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value" id="absentCount"><?php echo $absent_count; ?></span>
                                    <span class="stat-label">Absent</span>
                                </div>
                            </div>
                            
                            <button type="submit" name="save_attendance" class="attendance-btn">
                                Save Attendance
                            </button>
                        </form>
                    </div>

                    <!-- Student List -->
                    <div class="attendance-card">
                        <h3 style="margin-bottom: 1.5rem;">Students</h3>
                        <div class="student-list" id="studentList">
                            <?php 
                            if($students->num_rows > 0) {
                                while($student = $students->fetch_assoc()): 
                                    $initials = getInitials($student['name']);
                                    $current_status = isset($today_attendance[$student['id']]) ? $today_attendance[$student['id']] : 'present';
                            ?>
                            <div class="student-item">
                                <div class="student-avatar"><?php echo $initials; ?></div>
                                <div class="student-details">
                                    <div class="student-main"><?php echo $student['name']; ?></div>
                                    <div class="student-sub"><?php echo $student['roll_no']; ?> • <?php echo $student['class']; ?></div>
                                </div>
                                <div style="display: flex; gap: 1rem; align-items: center;">
                                    <label style="display: flex; align-items: center; gap: 0.25rem; cursor: pointer;">
                                        <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" 
                                               <?php echo $current_status == 'present' ? 'checked' : ''; ?> class="attendance-radio">
                                        Present
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.25rem; cursor: pointer;">
                                        <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent"
                                               <?php echo $current_status == 'absent' ? 'checked' : ''; ?> class="attendance-radio">
                                        Absent
                                    </label>
                                </div>
                            </div>
                            <?php 
                                endwhile;
                            } else {
                                echo '<div style="text-align: center; padding: 2rem; color: #64748b;">No students found. Please add students first.</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update present/absent counts in real-time
        document.addEventListener('DOMContentLoaded', function() {
            const radioButtons = document.querySelectorAll('.attendance-radio');
            const presentCount = document.getElementById('presentCount');
            const absentCount = document.getElementById('absentCount');
            
            function updateCounts() {
                let present = 0;
                let absent = 0;
                
                // Count checked radio buttons
                radioButtons.forEach(radio => {
                    if (radio.checked) {
                        if (radio.value === 'present') present++;
                        else if (radio.value === 'absent') absent++;
                    }
                });
                
                presentCount.textContent = present;
                absentCount.textContent = absent;
            }
            
            // Add event listeners to all radio buttons
            radioButtons.forEach(radio => {
                radio.addEventListener('change', updateCounts);
            });
            
            // Initial count
            updateCounts();
            
            // Handle form submission
            document.getElementById('attendanceForm').addEventListener('submit', function(e) {
                const totalStudents = <?php echo $students->num_rows; ?>;
                const markedStudents = document.querySelectorAll('.attendance-radio:checked').length / 2; // Divide by 2 because each student has 2 radios
                
                if (markedStudents !== totalStudents) {
                    e.preventDefault();
                    alert('Please mark attendance for all students before saving.');
                }
            });
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