<?php
include '../includes/session.php';
include '../includes/config.php';
require_admin();

$message = "";
$message_class = "";

// Save settings
if (isset($_POST['save_settings'])) {
    $donation_months = (int) $_POST['donation_threshold'];
    $site_name = mysqli_real_escape_string($conn, $_POST['site_name']);
    if ($donation_months < 1) $donation_months = 1;
    $update1 = "UPDATE settings SET setting_value='$donation_months' WHERE setting_key='donation_threshold_months'";
    $update2 = "UPDATE settings SET setting_value='$site_name' WHERE setting_key='site_name'";
    if (mysqli_query($conn, $update1) && mysqli_query($conn, $update2)) {
        $message = "Settings saved.";
        $message_class = "success";
    } else {
        $message = "Error saving settings.";
        $message_class = "error";
    }
}

// Fetch current settings
$res = mysqli_query($conn, "SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = mysqli_fetch_assoc($res)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$donation_threshold = $settings['donation_threshold_months'] ?? 6;
$site_name = $settings['site_name'] ?? 'ReUnite';

$page_title = "Settings - ReUnite";
$page_heading = "Settings";
$page_description = "System configuration and preferences.";
ob_start();
if(isset($_GET['message'])): ?>
    <p class="notice warning"><?php echo htmlspecialchars($_GET['message']); ?></p>
<?php endif; ?>
<form method="POST">
    <label>Site Name</label>
    <input type="text" name="site_name" value="<?php echo htmlspecialchars($site_name); ?>" required>
    <label>Donation Threshold (months after which unclaimed items can be donated)</label>
    <input type="number" name="donation_threshold" value="<?php echo (int)$donation_threshold; ?>" min="1" required>
    <button type="submit" name="save_settings" class="btn">Save Settings</button>
</form>
<?php
$page_content = ob_get_clean();
include 'includes/page_template.php';
?>