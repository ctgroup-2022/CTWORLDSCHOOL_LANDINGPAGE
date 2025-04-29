<?php
// filepath: c:\xampp\htdocs\CTWORLDSCHOOL_LANDINGPAGE-main\CTWORLDSCHOOL_LANDINGPAGE-main\download.php
// Download handler for admin panel

// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die('Unauthorized access');
}

// Get file path from query parameter and sanitize it
if (!isset($_GET['file']) || empty($_GET['file'])) {
    die('No file specified');
}

// Security check - only allow downloads from the uploads directory
$file = $_GET['file'];
$file = str_replace('..', '', $file); // Prevent directory traversal
$file = str_replace('\\', '/', $file); // Normalize slashes

// Make sure the file is within the uploads directory
if (strpos($file, 'uploads/payment_screenshots/') !== 0) {
    die('Invalid file path');
}

// Check if file exists
if (!file_exists($file)) {
    die('File not found: ' . htmlspecialchars($file));
}

// Get file information
$fileInfo = pathinfo($file);
$fileName = $fileInfo['basename'];
$fileExt = strtolower($fileInfo['extension']);

// Only allow image files
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($fileExt, $allowedExtensions)) {
    die('Invalid file type');
}

// Log the download activity
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_name'])) {
    require_once 'config/controller.php';
    
    $admin_id = $_SESSION['admin_id'];
    $admin_name = $_SESSION['admin_name'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $log_query = "INSERT INTO admin_activity_logs (admin_id, admin_name, action, details, ip_address, user_agent) 
                  VALUES (?, ?, 'download', 'Downloaded payment proof: {$fileName}', ?, ?)";
    
    $stmt = $conn->prepare($log_query);
    $stmt->bind_param("isss", $admin_id, $admin_name, $ip, $user_agent);
    $stmt->execute();
}

// Set appropriate headers for file download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
?>

<?php
// filepath: c:\xampp\htdocs\CTWORLDSCHOOL_LANDINGPAGE-main\CTWORLDSCHOOL_LANDINGPAGE-main\batch_download.php
// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die('Unauthorized access');
}

// Check if files are specified
if (!isset($_POST['files']) || empty($_POST['files'])) {
    die('No files selected');
}

$files = $_POST['files'];

// Create a temporary zip file
$zipname = 'payment_screenshots_' . date('Y-m-d_H-i-s') . '.zip';
$zippath = 'uploads/temp/' . $zipname;

// Make sure temp directory exists
if (!file_exists('uploads/temp/')) {
    mkdir('uploads/temp/', 0755, true);
}

// Create new zip object
$zip = new ZipArchive();
if ($zip->open($zippath, ZipArchive::CREATE) !== TRUE) {
    die("Cannot create zip archive");
}

// Add files to the zip
$validFiles = 0;
foreach ($files as $file) {
    // Security checks
    $file = str_replace('..', '', $file);
    $file = str_replace('\\', '/', $file);
    
    if (strpos($file, 'uploads/payment_screenshots/') !== 0) {
        continue; // Skip invalid paths
    }
    
    if (file_exists($file)) {
        $filename = basename($file);
        $zip->addFile($file, $filename);
        $validFiles++;
    }
}

// Close zip file
$zip->close();

// If no valid files, exit
if ($validFiles === 0) {
    unlink($zippath);
    die('No valid files found');
}

// Log the batch download
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_name'])) {
    require_once 'config/controller.php';
    
    $admin_id = $_SESSION['admin_id'];
    $admin_name = $_SESSION['admin_name'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $log_query = "INSERT INTO admin_activity_logs (admin_id, admin_name, action, details, ip_address, user_agent) 
                  VALUES (?, ?, 'batch_download', 'Downloaded {$validFiles} payment screenshots', ?, ?)";
    
    $stmt = $conn->prepare($log_query);
    $stmt->bind_param("isss", $admin_id, $admin_name, $ip, $user_agent);
    $stmt->execute();
}

// Download the zip file
header('Content-Type: application/zip');
header('Content-disposition: attachment; filename=' . $zipname);
header('Content-Length: ' . filesize($zippath));
readfile($zippath);

// Delete the temporary zip file
unlink($zippath);
exit;
?>

<form id="batchDownloadForm" action="download.php" method="POST" style="display: none;">
    <input type="hidden" name="files" id="selectedFiles" value="">
</form>

<script>
    // Add this inside your existing script, after the toggleBatchDownload click handler
    
    function downloadSelected() {
        const checkboxes = document.querySelectorAll('.screenshot-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one screenshot to download');
            return;
        }
        
        const files = Array.from(checkboxes).map(checkbox => checkbox.dataset.path);
        document.getElementById('selectedFiles').value = JSON.stringify(files);
        document.getElementById('batchDownloadForm').submit();
    }
    
    // Modify your existing toggleBatchDownload click handler:
    document.getElementById('toggleBatchDownload').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.batch-download-checkbox');
        const isVisible = checkboxes.length > 0 && checkboxes[0].style.display !== 'none';
        
        checkboxes.forEach(checkbox => {
            checkbox.style.display = isVisible ? 'none' : 'block';
        });
        
        if (isVisible) {
            this.innerHTML = '<i class="fas fa-download me-2"></i> Batch Download';
            this.onclick = null; // Remove the download function
        } else {
            this.innerHTML = '<i class="fas fa-check me-2"></i> Download Selected';
            // This sets up a one-time click handler for the next click
            this.onclick = function(e) {
                e.preventDefault();
                downloadSelected();
                return false;
            };
        }
    });
</script>