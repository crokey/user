<?php
// Start or resume a session
session_start();

// Basic CSRF protection check (if you have CSRF tokens implemented)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the CSRF token is set and valid
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token mismatch.');
    }

    // Assuming you have a database connection set up
    require_once 'database_connection.php'; // Adjust this path as necessary

    // Sanitize the input to prevent SQL Injection
    $columnId = filter_input(INPUT_POST, 'column_id', FILTER_SANITIZE_NUMBER_INT);

    // Authentication and authorization checks should go here
    // Ensure the user is logged in and has permission to delete the column

    if ($columnId) {
        // Prepare the SQL statement to prevent SQL injection
        $stmt = $pdo->prepare("DELETE FROM tblreconciliation WHERE id = :columnId");

        // Bind the parameter to the query
        $stmt->bindParam(':columnId', $columnId, PDO::PARAM_INT);

        // Execute the query
        if ($stmt->execute()) {
            // If the delete was successful, redirect or inform the user
            echo "Column deleted successfully.";
            // Redirect back to the client details page or wherever appropriate
            // header('Location: client_details_page.php');
        } else {
            // Handle the error case
            echo "Error deleting the column.";
        }
    } else {
        // Handle the case where column ID is not set or invalid
        echo "Invalid request.";
    }
} else {
    // If not a POST request, handle the error or redirect
    die('Invalid request method.');
}
?>
