<?php
/**
 * Helper functions for Impocred Credit Management System
 * Includes calculations for due dates, penalties, and interest
 */

/**
 * Calculate due date (3 months from purchase date)
 * @param string $purchaseDate Date in Y-m-d format
 * @return string Due date in Y-m-d format
 */
function calculateDueDate($purchaseDate) {
    try {
        $date = new DateTime($purchaseDate);
        $date->add(new DateInterval('P3M')); // Add 3 months
        return $date->format('Y-m-d');
    } catch (Exception $e) {
        error_log("Error calculating due date: " . $e->getMessage());
        return false;
    }
}

/**
 * Calculate penalty based on days late
 * $2 penalty after 3 days, $3 penalty after 5 days
 * @param string $dueDate Due date in Y-m-d format
 * @param string $paymentDate Payment date in Y-m-d format
 * @return float Penalty amount
 */
function calculatePenalty($dueDate, $paymentDate) {
    try {
        $due = new DateTime($dueDate);
        $payment = new DateTime($paymentDate);
        
        // If payment is on time or early, no penalty
        if ($payment <= $due) {
            return 0.00;
        }
        
        // Calculate days late
        $interval = $due->diff($payment);
        $daysLate = $interval->days;
        
        if ($daysLate >= 5) {
            return 3.00; // $3 penalty after 5 days
        } elseif ($daysLate >= 3) {
            return 2.00; // $2 penalty after 3 days
        }
        
        return 0.00;
    } catch (Exception $e) {
        error_log("Error calculating penalty: " . $e->getMessage());
        return 0.00;
    }
}

/**
 * Calculate monthly interest (7.5% per month after due date)
 * @param string $dueDate Due date in Y-m-d format
 * @param string $currentDate Current date in Y-m-d format
 * @param float $productPrice Original product price
 * @return float Interest amount
 */
function calculateInterest($dueDate, $currentDate, $productPrice) {
    try {
        $due = new DateTime($dueDate);
        $current = new DateTime($currentDate);
        
        // If current date is before or on due date, no interest
        if ($current <= $due) {
            return 0.00;
        }
        
        // Calculate months past due
        $interval = $due->diff($current);
        $monthsPastDue = ($interval->y * 12) + $interval->m;
        
        // Add partial month if there are remaining days
        if ($interval->d > 0) {
            $monthsPastDue++;
        }
        
        // Calculate 7.5% interest per month
        $interestRate = 0.075;
        $totalInterest = $productPrice * $interestRate * $monthsPastDue;
        
        return round($totalInterest, 2);
    } catch (Exception $e) {
        error_log("Error calculating interest: " . $e->getMessage());
        return 0.00;
    }
}

/**
 * Validate and sanitize input data
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 * @param string $email Email address
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic validation)
 * @param string $phone Phone number
 * @return bool True if valid, false otherwise
 */
function validatePhone($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    // Check if it has at least 10 digits
    return strlen($phone) >= 10;
}

/**
 * Validate cedula (basic validation - should be numeric)
 * @param string $cedula Cedula number
 * @return bool True if valid, false otherwise
 */
function validateCedula($cedula) {
    // Remove all non-numeric characters
    $cedula = preg_replace('/[^0-9]/', '', $cedula);
    // Check if it has at least 8 digits
    return strlen($cedula) >= 8;
}

/**
 * Hash password using PHP's password_hash
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if password matches, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Start secure session
 */
function startSecureSession() {
    if (session_status() == PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
        
        session_start();
        
        // Regenerate session ID for security
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
    }
}

/**
 * Check if collector is logged in
 * @return bool True if logged in, false otherwise
 */
function isCollectorLoggedIn() {
    startSecureSession();
    return isset($_SESSION['collector_id']) && !empty($_SESSION['collector_id']);
}

/**
 * Redirect to login page if not authenticated
 */
function requireCollectorLogin() {
    if (!isCollectorLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Format currency for display
 * @param float $amount Amount to format
 * @return string Formatted currency
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format date for display
 * @param string $date Date in Y-m-d format
 * @return string Formatted date
 */
function formatDate($date) {
    try {
        $dateObj = new DateTime($date);
        return $dateObj->format('d/m/Y');
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Get credit status with color coding
 * @param string $status Credit status
 * @return array Status with CSS class
 */
function getStatusDisplay($status) {
    $statusMap = [
        'active' => ['text' => 'Activo', 'class' => 'status-active'],
        'paid' => ['text' => 'Pagado', 'class' => 'status-paid'],
        'overdue' => ['text' => 'Vencido', 'class' => 'status-overdue'],
        'cancelled' => ['text' => 'Cancelado', 'class' => 'status-cancelled']
    ];
    
    return $statusMap[$status] ?? ['text' => $status, 'class' => 'status-default'];
}
?>
