<?php
include 'includes/connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['itemId'])) {
        $itemId = mysqli_real_escape_string($con, $_POST['itemId']);

        // Check if the item is associated with any order details
        $checkOrderQuery = "SELECT COUNT(*) FROM order_details WHERE item_id = ?";
        $checkOrderStmt = mysqli_prepare($con, $checkOrderQuery);
        mysqli_stmt_bind_param($checkOrderStmt, "i", $itemId);
        mysqli_stmt_execute($checkOrderStmt);
        mysqli_stmt_bind_result($checkOrderStmt, $orderCount);
        mysqli_stmt_fetch($checkOrderStmt);
        mysqli_stmt_close($checkOrderStmt);

        // If the item is associated with orders, delete order details first
        if ($orderCount > 0) {
            $deleteOrderQuery = "DELETE FROM order_details WHERE item_id = ?";
            $deleteOrderStmt = mysqli_prepare($con, $deleteOrderQuery);
            mysqli_stmt_bind_param($deleteOrderStmt, "i", $itemId);
            $orderDeletionResult = mysqli_stmt_execute($deleteOrderStmt);
            mysqli_stmt_close($deleteOrderStmt);

            if (!$orderDeletionResult) {
                $response = [
                    'status' => 'error',
                    'message' => 'Error deleting associated order details'
                ];
                echo json_encode($response);
                exit;
            }
        }

        // Continue with deleting the item
        $deleteItemQuery = "DELETE FROM items WHERE id = ?";
        $deleteItemStmt = mysqli_prepare($con, $deleteItemQuery);
        mysqli_stmt_bind_param($deleteItemStmt, "i", $itemId);
        $itemDeletionResult = mysqli_stmt_execute($deleteItemStmt);
        mysqli_stmt_close($deleteItemStmt);

        // Check if the deletion was successful
        if ($itemDeletionResult) {
            $response = [
                'status' => 'success',
                'message' => 'Item deleted successfully'
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Error deleting item'
            ];
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Invalid request'
        ];
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Invalid request method'
    ];
}

// Output JSON response
echo json_encode($response);
?>
