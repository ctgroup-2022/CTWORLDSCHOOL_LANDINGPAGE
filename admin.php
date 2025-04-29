<?php
// filepath: c:\xampp\htdocs\CTWORLDSCHOOL_LANDINGPAGE-main\CTWORLDSCHOOL_LANDINGPAGE-main\admin.php

// First include database connection
require_once 'config/controller.php';

// Check if admin is logged in - don't start a second session
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Handle payment status update
if(isset($_POST['update_payment']) && isset($_POST['registration_id'])) {
    $registration_id = (int)$_POST['registration_id'];
    $payment_status = $conn->real_escape_string($_POST['payment_status']);
    $transaction_id = $conn->real_escape_string($_POST['transaction_id'] ?? '');
    $payment_notes = $conn->real_escape_string($_POST['payment_notes'] ?? '');
    
    // Update payment status
    $update_query = "UPDATE registrations SET 
                    payment_status = ?, 
                    transaction_id = ?,
                    notes = CONCAT(IFNULL(notes, ''), '\n', NOW(), ' - Payment update: ', ?)
                    WHERE id = ?";
                    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $payment_status, $transaction_id, $payment_notes, $registration_id);
    
    if($stmt->execute()) {
        // Redirect to refresh the page
        header("Location: admin.php?success=payment_updated");
        exit;
    } else {
        // Handle error
        $error_message = "Failed to update payment: " . $conn->error;
    }
}

// Handle logout action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header("Location: login.php");
    exit;
}

// Initialize statistics array
$stats = [];

// Fetch dashboard statistics
try {
    // Total registrations
    $total_query = "SELECT COUNT(*) as count FROM registrations";
    $result = $conn->query($total_query);
    $stats['total_registrations'] = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['count'] : 0;
    
    // Paid registrations
    $paid_query = "SELECT COUNT(*) as count FROM registrations WHERE payment_status = 'Paid'";
    $result = $conn->query($paid_query);
    $stats['paid_registrations'] = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['count'] : 0;
    
    // Pending registrations
    $pending_query = "SELECT COUNT(*) as count FROM registrations WHERE payment_status != 'Paid'";
    $result = $conn->query($pending_query);
    $stats['pending_registrations'] = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['count'] : 0;
    
    // School participants
    $school_query = "SELECT COUNT(*) as count FROM registrations WHERE participants = 1";
    $result = $conn->query($school_query);
    $stats['school_participants'] = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['count'] : 0;
    
    // Club participants
    $club_query = "SELECT COUNT(*) as count FROM registrations WHERE participants = 2";
    $result = $conn->query($club_query);
    $stats['club_participants'] = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['count'] : 0;
    
    // Individual participants
    $individual_query = "SELECT COUNT(*) as count FROM registrations WHERE participants = 3";
    $result = $conn->query($individual_query);
    $stats['individual_participants'] = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['count'] : 0;
    
} catch (Exception $e) {
    // If there's an error, initialize with zeros to prevent the page from breaking
    $stats = [
        'total_registrations' => 0,
        'paid_registrations' => 0,
        'pending_registrations' => 0,
        'school_participants' => 0,
        'club_participants' => 0,
        'individual_participants' => 0
    ];
    
    // Add an admin notice about the error
    $admin_notice = "Error fetching statistics: " . $e->getMessage();
}

// Define filters and search variables if not already defined
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare query based on filters and search
$query = "SELECT * FROM registrations";
$where_clauses = [];

// Apply filters
if ($filter === 'pending_payment') {
    $where_clauses[] = "payment_status != 'Paid'";
} elseif ($filter === 'paid') {
    $where_clauses[] = "payment_status = 'Paid'";
} elseif ($filter === 'school') {
    $where_clauses[] = "participants = 1";
} elseif ($filter === 'club') {
    $where_clauses[] = "participants = 2";
} elseif ($filter === 'individual') {
    $where_clauses[] = "participants = 3";
}

// Apply search
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $where_clauses[] = "(name LIKE '%$search%' OR phone LIKE '%$search%' OR transaction_id LIKE '%$search%')";
}

// Combine where clauses
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Order by most recent first
$query .= " ORDER BY created_at DESC LIMIT 100";

// Execute the query
$result = $conn->query($query);

// After your database and data fetching logic:
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CT Shooting Championship</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-hover: #0b5ed7;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-bg: #212529;
            --card-bg-light: #ffffff;
            --card-bg-dark: #2c3034;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --topbar-height: 60px;
            --border-radius: 10px;
            --transition-speed: 0.3s;
            --box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        [data-bs-theme="dark"] {
            --primary-color: #3a86ff;
            --card-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: #333;
            overflow-x: hidden;
            position: relative;
        }

        [data-bs-theme="dark"] body {
            background-color: var(--dark-bg);
            color: #f8f9fa;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--card-bg-light);
            z-index: 1000;
            transition: var(--transition-speed);
            box-shadow: var(--box-shadow);
            overflow-y: auto;
            scrollbar-width: thin;
        }

        [data-bs-theme="dark"] .sidebar {
            background-color: var(--card-bg-dark);
            box-shadow: var(--card-shadow);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        [data-bs-theme="dark"] .sidebar-header {
            border-color: rgba(255,255,255,0.05);
        }

        .sidebar-logo {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-logo i {
            margin-right: 0.75rem;
            font-size: 1.5rem;
        }

        .sidebar.collapsed .sidebar-logo span {
            display: none;
        }

        .sidebar-toggle {
            background: transparent;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 1.25rem;
            margin-left: auto;
            padding: 0.25rem;
            transition: color 0.2s;
        }

        .sidebar-toggle:hover {
            color: var(--primary-color);
        }

        [data-bs-theme="dark"] .sidebar-toggle {
            color: #ccc;
        }

        .nav-section {
            padding: 1rem 0;
        }

        .nav-section-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            padding: 0.5rem 1.5rem;
            margin-bottom: 0.5rem;
            overflow: hidden;
            white-space: nowrap;
        }

        [data-bs-theme="dark"] .nav-section-title {
            color: #aaa;
        }

        .sidebar.collapsed .nav-section-title {
            display: none;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
        }

        .nav-item {
            margin-bottom: 0.25rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #555;
            text-decoration: none;
            border-radius: 0;
            transition: all 0.2s;
            overflow: hidden;
            white-space: nowrap;
        }

        [data-bs-theme="dark"] .nav-link {
            color: #ccc;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(13, 110, 253, 0.1);
            color: var(--primary-color);
        }

        [data-bs-theme="dark"] .nav-link:hover, 
        [data-bs-theme="dark"] .nav-link.active {
            background-color: rgba(58, 134, 255, 0.2);
        }

        .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .nav-link {
            padding: 0.75rem;
            justify-content: center;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 1.5rem;
            transition: var(--transition-speed);
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        [data-bs-theme="dark"] .topbar {
            border-color: rgba(255,255,255,0.05);
        }

        .page-title {
            font-weight: 600;
            font-size: 1.5rem;
            margin: 0;
        }

        .user-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            background: transparent;
            border: none;
            padding: 0;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 0.5rem;
        }

        .user-info {
            text-align: left;
            margin-right: 0.5rem;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            margin: 0;
        }

        .user-role {
            font-size: 0.75rem;
            color: #888;
            margin: 0;
        }

        [data-bs-theme="dark"] .user-role {
            color: #aaa;
        }

        /* Dashboard Cards */
        .stats-card {
            position: relative;
            background: var(--card-bg-light);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            height: 100%;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
        }

        [data-bs-theme="dark"] .stats-card {
            background-color: var(--card-bg-dark);
            box-shadow: var(--card-shadow);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        [data-bs-theme="dark"] .stats-card:hover {
            box-shadow: 0 8px 15px rgba(0,0,0,0.3);
        }

        .stats-icon {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stats-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .stats-label {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        [data-bs-theme="dark"] .stats-label {
            color: #aaa;
        }

        .stats-progress {
            display: flex;
            align-items: center;
            margin-top: 0.75rem;
        }

        .stats-percentage {
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .indicator-up {
            color: var(--success-color);
        }

        .indicator-down {
            color: var(--danger-color);
        }

        /* Table and Data */
        .data-card {
            background: var(--card-bg-light);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: none;
        }

        [data-bs-theme="dark"] .data-card {
            background-color: var(--card-bg-dark);
            box-shadow: var(--card-shadow);
        }

        .card-header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .card-title {
            font-weight: 600;
            margin: 0;
            font-size: 1.1rem;
        }

        .table-filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .filter-btn {
            border-radius: 20px;
            font-size: 0.85rem;
            padding: 0.375rem 0.75rem;
            background-color: #f1f3f5;
            border: none;
            color: #495057;
            font-weight: 500;
            transition: all 0.2s;
        }

        [data-bs-theme="dark"] .filter-btn {
            background-color: #343a40;
            color: #dee2e6;
        }

        .filter-btn:hover, .filter-btn.active {
            background-color: var(--primary-color);
            color: white;
        }

        .search-container {
            position: relative;
        }

        .search-input {
            border-radius: 20px;
            padding-left: 2.5rem;
            border: 1px solid #dee2e6;
            font-size: 0.9rem;
        }

        [data-bs-theme="dark"] .search-input {
            background-color: #343a40;
            border-color: #495057;
            color: #dee2e6;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }

        [data-bs-theme="dark"] .search-icon {
            color: #aaa;
        }

        /* Table Styling */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .data-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #dee2e6;
        }

        [data-bs-theme="dark"] .data-table th {
            background-color: #343a40;
            border-color: #495057;
            color: #dee2e6;
        }

        .data-table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }

        [data-bs-theme="dark"] .data-table td {
            border-color: #343a40;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover td {
            background-color: rgba(0,0,0,0.02);
        }

        [data-bs-theme="dark"] .data-table tr:hover td {
            background-color: rgba(255,255,255,0.02);
        }

        .status-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }

        .status-badge i {
            margin-right: 0.35rem;
            font-size: 0.7rem;
        }

        .status-pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #f0ad4e;
        }

        .status-paid {
            background-color: rgba(25, 135, 84, 0.2);
            color: #20c997;
        }

        .status-failed {
            background-color: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
        }

        .plan-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .plan-standard {
            background-color: rgba(108, 117, 125, 0.2);
            color: #6c757d;
        }

        .plan-premium {
            background-color: rgba(13, 110, 253, 0.2);
            color: #0d6efd;
        }

        [data-bs-theme="dark"] .plan-standard {
            color: #adb5bd;
        }

        [data-bs-theme="dark"] .plan-premium {
            color: #6ea8fe;
        }

        .action-btn {
            padding: 0.4rem 0.5rem;
            border-radius: 6px;
            font-size: 0.8rem;
            margin-right: 0.25rem;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn i {
            margin-right: 0.35rem;
            font-size: 0.8rem;
        }

        /* Modals */
        .modal-content {
            border: none;
            border-radius: calc(var(--border-radius) - 2px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        [data-bs-theme="dark"] .modal-content {
            background-color: var(--card-bg-dark);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .modal-header {
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem 1.5rem;
        }

        [data-bs-theme="dark"] .modal-header {
            border-color: rgba(255,255,255,0.05);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem 1.5rem;
        }

        [data-bs-theme="dark"] .modal-footer {
            border-color: rgba(255,255,255,0.05);
        }

        /* Footer */
        .footer {
            margin-top: auto;
            padding: 1rem 0;
            text-align: center;
            font-size: 0.85rem;
            color: #888;
        }

        [data-bs-theme="dark"] .footer {
            color: #aaa;
        }

        /* Image Preview */
        .payment-proof-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s;
            border: 2px solid #eee;
        }

        [data-bs-theme="dark"] .payment-proof-thumbnail {
            border-color: #495057;
        }

        .payment-proof-thumbnail:hover {
            transform: scale(1.1);
        }

        .preview-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .preview-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .preview-container {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }

        .preview-image {
            max-width: 100%;
            max-height: 80vh;
            border-radius: 8px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.25);
        }

        .preview-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            background: transparent;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .preview-download {
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--primary-color);
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 30px;
            border: none;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .preview-download i {
            margin-right: 0.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .sidebar {
                width: var(--sidebar-collapsed-width);
            }
            
            .sidebar-logo span {
                display: none;
            }
            
            .nav-section-title {
                display: none;
            }
            
            .nav-link {
                padding: 0.75rem;
                justify-content: center;
            }
            
            .nav-link i {
                margin-right: 0;
            }
            
            .nav-link span {
                display: none;
            }
            
            .main-content {
                margin-left: var(--sidebar-collapsed-width);
            }
        }

        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 1rem;
            }
            
            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-dropdown {
                margin-top: 1rem;
                align-self: flex-end;
            }
            
            .data-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .filter-btn {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
            
            .action-btn .btn-text {
                display: none;
            }
            
            .action-btn i {
                margin-right: 0;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 1rem;
            }
            
            .page-title {
                font-size: 1.25rem;
            }
            
            .stats-value {
                font-size: 1.75rem;
            }
            
            .data-card {
                padding: 1rem;
            }
            
            .card-header-actions {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .card-title {
                margin-bottom: 0.75rem;
            }
            
            .data-table td, .data-table th {
                padding: 0.5rem;
                font-size: 0.8rem;
            }
            
            .status-badge, .plan-badge {
                padding: 0.25rem 0.5rem;
            }
        }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: var(--primary-color);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        .dark-mode-toggle {
            display: flex;
            align-items: center;
            margin-left: 1rem;
        }

        .dark-mode-toggle i {
            margin-right: 0.5rem;
            color: #888;
        }

        [data-bs-theme="dark"] .dark-mode-toggle i {
            color: #aaa;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="admin.php" class="sidebar-logo">
                <i class="fas fa-trophy"></i>
                <span>CT Championship</span>
            </a>
            <button class="sidebar-toggle" id="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="admin.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-user-check"></i>
                        <span>Registrations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Payments</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_logs.php" class="nav-link">
                        <i class="fas fa-history"></i>
                        <span>Activity Logs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?action=logout" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content" id="main-content">
        <div class="topbar">
            <h1 class="page-title">Dashboard</h1>
            
            <div class="d-flex align-items-center">
                <div class="dark-mode-toggle">
                    <i class="fas fa-moon"></i>
                    <label class="toggle-switch">
                        <input type="checkbox" id="theme-toggle">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="dropdown user-dropdown ms-3">
                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            <?php 
                            // Get the first letter of admin name
                            $initial = isset($_SESSION['admin_name']) ? substr($_SESSION['admin_name'], 0, 1) : 'A';
                            echo $initial;
                            ?>
                        </div>
                        <div class="user-info d-none d-md-block">
                            <h6 class="user-name"><?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin'; ?></h6>
                            <p class="user-role">Administrator</p>
                        </div>
                        <i class="fas fa-chevron-down ms-2"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?action=logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-value"><?php echo $stats['total_registrations']; ?></div>
                    <div class="stats-label">Total Registrations</div>
                    <div class="stats-progress">
                        <div class="progress" style="height: 6px; width: 100%;">
                            <div class="progress-bar bg-primary" style="width: 100%"></div>
                        </div>
                        <span class="stats-percentage indicator-up">
                            <i class="fas fa-arrow-up"></i> 100%
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: rgba(25, 135, 84, 0.1); color: #198754;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-value"><?php echo $stats['paid_registrations']; ?></div>
                    <div class="stats-label">Paid Registrations</div>
                    <div class="stats-progress">
                        <div class="progress" style="height: 6px; width: 100%;">
                            <?php 
                            $paidPercentage = $stats['total_registrations'] > 0 
                                ? round(($stats['paid_registrations'] / $stats['total_registrations']) * 100) 
                                : 0;
                            ?>
                            <div class="progress-bar bg-success" style="width: <?php echo $paidPercentage; ?>%"></div>
                        </div>
                        <span class="stats-percentage indicator-up">
                            <i class="fas fa-arrow-up"></i> <?php echo $paidPercentage; ?>%
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: rgba(255, 193, 7, 0.1); color: #ffc107;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-value"><?php echo $stats['pending_registrations']; ?></div>
                    <div class="stats-label">Pending Payments</div>
                    <div class="stats-progress">
                        <div class="progress" style="height: 6px; width: 100%;">
                            <?php 
                            $pendingPercentage = $stats['total_registrations'] > 0 
                                ? round(($stats['pending_registrations'] / $stats['total_registrations']) * 100) 
                                : 0;
                            ?>
                            <div class="progress-bar bg-warning" style="width: <?php echo $pendingPercentage; ?>%"></div>
                        </div>
                        <span class="stats-percentage <?php echo $pendingPercentage > 30 ? 'indicator-down' : 'indicator-up'; ?>">
                            <i class="fas <?php echo $pendingPercentage > 30 ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i> <?php echo $pendingPercentage; ?>%
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: rgba(220, 53, 69, 0.1); color: #dc3545;">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="stats-value"><?php echo ($stats['school_participants'] + $stats['club_participants'] + $stats['individual_participants']); ?></div>
                    <div class="stats-label">Total Participants</div>
                    <div class="stats-progress">
                        <div class="progress" style="height: 6px; width: 100%;">
                            <div class="progress-bar bg-danger" style="width: 85%"></div>
                        </div>
                        <span class="stats-percentage indicator-up">
                            <i class="fas fa-arrow-up"></i> 85%
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Participant Type Stats -->
        <div class="row mb-4">
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                        <i class="fas fa-school"></i>
                    </div>
                    <div class="stats-value"><?php echo $stats['school_participants']; ?></div>
                    <div class="stats-label">School Participants</div>
                    <div class="stats-progress">
                        <div class="progress" style="height: 6px; width: 100%;">
                            <?php 
                            $schoolPercentage = $stats['total_registrations'] > 0 
                                ? round(($stats['school_participants'] / $stats['total_registrations']) * 100) 
                                : 0;
                            ?>
                            <div class="progress-bar bg-primary" style="width: <?php echo $schoolPercentage; ?>%"></div>
                        </div>
                        <span class="stats-percentage indicator-up">
                            <i class="fas fa-arrow-up"></i> <?php echo $schoolPercentage; ?>%
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: rgba(220, 53, 69, 0.1); color: #dc3545;">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div class="stats-value"><?php echo $stats['club_participants']; ?></div>
                    <div class="stats-label">Club Participants</div>
                    <div class="stats-progress">
                        <div class="progress" style="height: 6px; width: 100%;">
                            <?php 
                            $clubPercentage = $stats['total_registrations'] > 0 
                                ? round(($stats['club_participants'] / $stats['total_registrations']) * 100) 
                                : 0;
                            ?>
                            <div class="progress-bar bg-danger" style="width: <?php echo $clubPercentage; ?>%"></div>
                        </div>
                        <span class="stats-percentage indicator-up">
                            <i class="fas fa-arrow-up"></i> <?php echo $clubPercentage; ?>%
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: rgba(255, 193, 7, 0.1); color: #ffc107;">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="stats-value"><?php echo $stats['individual_participants']; ?></div>
                    <div class="stats-label">Individual Participants</div>
                    <div class="stats-progress">
                        <div class="progress" style="height: 6px; width: 100%;">
                            <?php 
                            $individualPercentage = $stats['total_registrations'] > 0 
                                ? round(($stats['individual_participants'] / $stats['total_registrations']) * 100) 
                                : 0;
                            ?>
                            <div class="progress-bar bg-warning" style="width: <?php echo $individualPercentage; ?>%"></div>
                        </div>
                        <span class="stats-percentage indicator-up">
                            <i class="fas fa-arrow-up"></i> <?php echo $individualPercentage; ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Registrations Table -->
        <div class="data-card">
            <div class="card-header-actions">
                <h5 class="card-title">Recent Registrations</h5>
                
                <div>
                    <button class="btn btn-sm btn-primary" id="toggleBatchDownload">
                        <i class="fas fa-download me-2"></i> Batch Download
                    </button>
                </div>
            </div>
            
            <div class="table-filter-row">
                <a href="admin.php" class="filter-btn <?php echo empty($filter) ? 'active' : ''; ?>">All</a>
                <a href="?filter=pending_payment" class="filter-btn <?php echo $filter === 'pending_payment' ? 'active' : ''; ?>">Pending</a>
                <a href="?filter=paid" class="filter-btn <?php echo $filter === 'paid' ? 'active' : ''; ?>">Paid</a>
                <a href="?filter=school" class="filter-btn <?php echo $filter === 'school' ? 'active' : ''; ?>">School</a>
                <a href="?filter=club" class="filter-btn <?php echo $filter === 'club' ? 'active' : ''; ?>">Club</a>
                <a href="?filter=individual" class="filter-btn <?php echo $filter === 'individual' ? 'active' : ''; ?>">Individual</a>
                
                <div class="search-container ms-auto">
                    <form action="admin.php" method="GET">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="form-control search-input" name="search" placeholder="Search registrations..." value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Plan</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Screenshot</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                // Determine participant type
                                $participantType = '';
                                switch($row['participants']) {
                                    case 1: $participantType = 'School'; break;
                                    case 2: $participantType = 'Club'; break;
                                    case 3: $participantType = 'Individual'; break;
                                }
                                
                                // Format date
                                $date = date('M d, Y', strtotime($row['created_at']));
                                
                                echo "<tr>
                                    <td>#{$row['id']}</td>
                                    <td>
                                        <div class='fw-semibold'>{$row['name']}</div>
                                        <div class='small text-muted'>{$row['phone']}</div>
                                    </td>
                                    <td>{$participantType}</td>
                                    <td>
                                        <span class='plan-badge " . ($row['payment_plan'] == 'premium' ? 'plan-premium' : 'plan-standard') . "'>
                                            " . ucfirst($row['payment_plan'] ?? 'standard') . "
                                        </span>
                                    </td>
                                    <td>
                                        <span class='status-badge " . ($row['payment_status'] == 'Paid' ? 'status-paid' : ($row['payment_status'] == 'Failed' ? 'status-failed' : 'status-pending')) . "'>
                                            <i class='fas " . ($row['payment_status'] == 'Paid' ? 'fa-check-circle' : ($row['payment_status'] == 'Failed' ? 'fa-times-circle' : 'fa-clock')) . "'></i>
                                            {$row['payment_status']}
                                        </span>
                                    </td>
                                    <td>{$date}</td>";
                                    
                                // Image thumbnail
                                echo "<td>";
                                if (!empty($row['payment_proof']) && file_exists($row['payment_proof'])) {
                                    echo "<img src='{$row['payment_proof']}' class='payment-proof-thumbnail' data-src='{$row['payment_proof']}' alt='Payment Screenshot' onclick='openPreview(this)'>";
                                    
                                    echo "<div class='batch-download-checkbox' style='display: none;'>
                                        <input type='checkbox' class='screenshot-checkbox' data-path='{$row['payment_proof']}'>
                                    </div>";
                                } else {
                                    echo "<span class='small text-muted'>No image</span>";
                                }
                                echo "</td>";
                                
                                // Action buttons
                                echo "<td>
                                    <button class='action-btn btn-warning' onclick='openPaymentUpdateModal({$row['id']}, \"{$row['payment_status']}\", \"" . htmlspecialchars($row['transaction_id'] ?? '') . "\")'>
                                        <i class='fas fa-edit'></i> <span class='btn-text'>Update</span>
                                    </button>
                                    <button class='action-btn btn-info' data-bs-toggle='modal' data-bs-target='#updateModal{$row['id']}'>
                                        <i class='fas fa-eye'></i> <span class='btn-text'>View</span>
                                    </button>
                                </td>
                                </tr>";
                                
                                // Modal for each registration (keep your existing modal code)
                                // Make sure to update the modal styling to match the new design
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center py-4'>No registrations found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="small text-muted">Showing <?php echo $result ? $result->num_rows : 0; ?> registrations</div>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        
        <div class="footer">
            &copy; <?php echo date('Y'); ?> CT Shooting Championship Administration
        </div>
    </main>
    
    <!-- Image Preview Overlay -->
    <div class="preview-overlay" id="previewOverlay">
        <div class="preview-container">
            <button class="preview-close" id="previewClose">
                <i class="fas fa-times"></i>
            </button>
            <img src="" id="previewImage" class="preview-image" alt="Payment Screenshot">
            <a href="#" class="preview-download" id="previewDownload">
                <i class="fas fa-download"></i> Download
            </a>
        </div>
    </div>
    
    <!-- Payment Update Modal -->
    <div class="modal fade" id="paymentUpdateModal" tabindex="-1" aria-labelledby="paymentUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentUpdateModalLabel">Update Payment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="paymentUpdateForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="registration_id" id="update_registration_id">
                        <input type="hidden" name="update_payment" value="1">
                        
                        <div class="mb-3">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select class="form-select" id="payment_status" name="payment_status" required>
                                <option value="Pending">Pending</option>
                                <option value="Paid">Paid</option>
                                <option value="Failed">Failed</option>
                                <option value="Pending Verification">Pending Verification</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="transaction_id" class="form-label">Transaction ID</label>
                            <input type="text" class="form-control" id="transaction_id" name="transaction_id">
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="payment_notes" name="payment_notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php
    // Reset the result pointer so we can iterate through it again
    if ($result) {
        $result->data_seek(0);
        while($row = $result->fetch_assoc()) {
            ?>
            <div class="modal fade" id="updateModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Registration Details #<?php echo $row['id']; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3">Participant Information</h6>
                                    <dl class="row">
                                        <dt class="col-sm-4">Name</dt>
                                        <dd class="col-sm-8"><?php echo htmlspecialchars($row['name']); ?></dd>
                                        
                                        <dt class="col-sm-4">Phone</dt>
                                        <dd class="col-sm-8"><?php echo htmlspecialchars($row['phone']); ?></dd>
                                        
                                        <dt class="col-sm-4">Age</dt>
                                        <dd class="col-sm-8"><?php echo htmlspecialchars($row['age']); ?></dd>
                                        
                                        <dt class="col-sm-4">Gender</dt>
                                        <dd class="col-sm-8"><?php echo $row['gender'] == 1 ? 'Male' : 'Female'; ?></dd>
                                        
                                        <dt class="col-sm-4">Type</dt>
                                        <dd class="col-sm-8">
                                            <?php 
                                            switch($row['participants']) {
                                                case 1: echo 'School'; break;
                                                case 2: echo 'Club'; break;
                                                case 3: echo 'Individual'; break;
                                            }
                                            ?>
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3">Payment Information</h6>
                                    <dl class="row">
                                        <dt class="col-sm-4">Plan</dt>
                                        <dd class="col-sm-8">
                                            <span class="plan-badge <?php echo $row['payment_plan'] == 'premium' ? 'plan-premium' : 'plan-standard'; ?>">
                                                <?php echo ucfirst($row['payment_plan'] ?? 'Standard'); ?>
                                            </span>
                                        </dd>
                                        
                                        <dt class="col-sm-4">Status</dt>
                                        <dd class="col-sm-8">
                                            <span class="status-badge <?php echo $row['payment_status'] == 'Paid' ? 'status-paid' : ($row['payment_status'] == 'Failed' ? 'status-failed' : 'status-pending'); ?>">
                                                <i class="fas <?php echo $row['payment_status'] == 'Paid' ? 'fa-check-circle' : ($row['payment_status'] == 'Failed' ? 'fa-times-circle' : 'fa-clock'); ?>"></i>
                                                <?php echo $row['payment_status']; ?>
                                            </span>
                                        </dd>
                                        
                                        <dt class="col-sm-4">Transaction ID</dt>
                                        <dd class="col-sm-8"><?php echo !empty($row['transaction_id']) ? htmlspecialchars($row['transaction_id']) : '<span class="text-muted">Not available</span>'; ?></dd>
                                        
                                        <dt class="col-sm-4">Date</dt>
                                        <dd class="col-sm-8"><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></dd>
                                    </dl>
                                </div>
                            </div>
                            
                            <?php if (!empty($row['payment_proof']) && file_exists($row['payment_proof'])): ?>
                            <div class="mt-4">
                                <h6 class="fw-bold mb-3">Payment Proof</h6>
                                <div class="text-center">
                                    <img src="<?php echo $row['payment_proof']; ?>" style="max-width: 100%; max-height: 300px;" class="img-thumbnail" alt="Payment Proof">
                                    <div class="mt-2">
                                        <a href="download.php?file=<?php echo urlencode($row['payment_proof']); ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-download me-1"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($row['notes'])): ?>
                            <div class="mt-4">
                                <h6 class="fw-bold mb-2">Notes</h6>
                                <div class="p-3 bg-light rounded">
                                    <?php echo nl2br(htmlspecialchars($row['notes'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-warning" onclick="openPaymentUpdateModal(<?php echo $row['id']; ?>, '<?php echo $row['payment_status']; ?>', '<?php echo htmlspecialchars($row['transaction_id'] ?? ''); ?>')">
                                <i class="fas fa-edit me-1"></i> Update Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
            
            // Theme toggle
            const themeToggle = document.getElementById('theme-toggle');
            const htmlElement = document.documentElement;
            
            // Check local storage for saved theme preference
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                htmlElement.setAttribute('data-bs-theme', 'dark');
                themeToggle.checked = true;
            }
            
            themeToggle.addEventListener('change', function() {
                if (this.checked) {
                    htmlElement.setAttribute('data-bs-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    htmlElement.setAttribute('data-bs-theme', 'light');
                    localStorage.setItem('theme', 'light');
                }
            });
            
            // Image preview
            window.openPreview = function(img) {
                const overlay = document.getElementById('previewOverlay');
                const previewImage = document.getElementById('previewImage');
                const downloadLink = document.getElementById('previewDownload');
                
                previewImage.src = img.getAttribute('data-src');
                downloadLink.href = 'download.php?file=' + img.getAttribute('data-src');
                overlay.classList.add('active');
            };
            
            document.getElementById('previewClose').addEventListener('click', function() {
                document.getElementById('previewOverlay').classList.remove('active');
            });

            // Add to your existing event listeners
            document.getElementById('previewOverlay').addEventListener('click', function(e) {
                // Only close if clicking directly on the overlay, not on the image or buttons
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
        
        // Include all your existing JavaScript for:
        // 1. Toggle batch download
        // 2. Open payment update modal
        // 3. Mobile responsiveness
        
        // Make sure you preserve all your existing modal functionality

        // Add this to your existing JavaScript section
        // Payment update modal function
        window.openPaymentUpdateModal = function(registrationId, currentStatus, transactionId) {
            document.getElementById('update_registration_id').value = registrationId;
            
            // Set current status if available
            if(currentStatus) {
                const statusSelect = document.getElementById('payment_status');
                for(let i = 0; i < statusSelect.options.length; i++) {
                    if(statusSelect.options[i].value === currentStatus) {
                        statusSelect.selectedIndex = i;
                        break;
                    }
                }
            }
            
            // Set transaction ID if available
            if(transactionId) {
                document.getElementById('transaction_id').value = transactionId;
            }
            
            // Open the modal
            const paymentModal = new bootstrap.Modal(document.getElementById('paymentUpdateModal'));
            paymentModal.show();
        };

        // Toggle batch download
        document.getElementById('toggleBatchDownload').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.batch-download-checkbox');
            const isVisible = checkboxes.length > 0 && checkboxes[0].style.display !== 'none';
            
            checkboxes.forEach(checkbox => {
                checkbox.style.display = isVisible ? 'none' : 'block';
            });
            
            this.innerHTML = isVisible ? 
                '<i class="fas fa-download me-2"></i> Batch Download' :
                '<i class="fas fa-check me-2"></i> Download Selected';
        });
    </script>
</body>
</html>