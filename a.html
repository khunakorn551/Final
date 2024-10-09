<?php
session_start();
include '../controllers/Delivery.Controller.php';

$deliveryController = new DeliveryController();

// Check if client ID is set in the session
if (!isset($_SESSION['client_id'])) {
    header("Location: login.php");
    exit;
}

$orders = $deliveryController->getClientOrders($_SESSION['client_id']);
$pastOrders = $deliveryController->getClientPastOrders($_SESSION['client_id']);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Orders</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="text-center mb-4">Client Orders</h1>
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Tracking Number</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order) { ?>
                    <tr>
                        <td><?= $order['order_id'] ?></td>
                        <td><?= $order['tracking_number'] ?></td>
                        <td><?= $order['status'] ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <h1 class="text-center mb-4 mt-5">Past Orders</h1>
            <?php if ($pastOrders) { ?>
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Tracking Number</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pastOrders as $pastOrder) { ?>
                        <tr>
                            <td><?= $pastOrder['order_id'] ?></td>
                            <td><?= $pastOrder['tracking_number'] ?></td>
                            <td><?= $pastOrder['status'] ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p class="text-center">No past orders recorded.</p>
            <?php } ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>