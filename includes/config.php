<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "reunite_db";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function column_exists($conn, $table, $column)
{
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);

    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");

    return $result && mysqli_num_rows($result) > 0;
}

$claims_table = "CREATE TABLE IF NOT EXISTS claims (
    claim_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    lost_item_id INT NULL,
    claimant_id INT NOT NULL DEFAULT 0,
    proof TEXT NOT NULL,
    proof_photo VARCHAR(255) NULL,
    match_score DECIMAL(5,2) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    admin_note TEXT NULL,
    date_claimed DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_decided DATETIME NULL,
    INDEX (item_id),
    INDEX (lost_item_id),
    INDEX (claimant_id),
    INDEX (status)
)";

mysqli_query($conn, $claims_table);

$claim_columns = array(
    "lost_item_id" => "ALTER TABLE claims ADD lost_item_id INT NULL AFTER item_id",
    "claimant_id" => "ALTER TABLE claims ADD claimant_id INT NOT NULL DEFAULT 0 AFTER lost_item_id",
    "proof" => "ALTER TABLE claims ADD proof TEXT NOT NULL AFTER claimant_id",
    "proof_photo" => "ALTER TABLE claims ADD proof_photo VARCHAR(255) NULL AFTER proof",
    "match_score" => "ALTER TABLE claims ADD match_score DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER proof_photo",
    "status" => "ALTER TABLE claims ADD status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER match_score",
    "admin_note" => "ALTER TABLE claims ADD admin_note TEXT NULL AFTER status",
    "date_claimed" => "ALTER TABLE claims ADD date_claimed DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER admin_note",
    "date_decided" => "ALTER TABLE claims ADD date_decided DATETIME NULL AFTER date_claimed"
);

foreach ($claim_columns as $column => $alter_sql) {
    if (!column_exists($conn, "claims", $column)) {
        mysqli_query($conn, $alter_sql);
    }
}

if (column_exists($conn, "claims", "user_id")) {
    mysqli_query($conn, "UPDATE claims SET claimant_id=user_id WHERE claimant_id=0 OR claimant_id IS NULL");
}

if (column_exists($conn, "claims", "claim_date")) {
    mysqli_query($conn, "UPDATE claims SET date_claimed=claim_date WHERE date_claimed IS NULL");
}

$upload_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "claims";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

?>
