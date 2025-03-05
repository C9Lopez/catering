<?php
require '../db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Fetch initial status counts
try {
    $stmt = $db->query("SELECT booking_status, COUNT(*) as count FROM event_bookings GROUP BY booking_status");
    $counts = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'count', 'booking_status');
    $counts = array_merge(['pending' => 0, 'approved' => 0, 'rejected' => 0, 'completed' => 0, 'cancelled' => 0], $counts);
} catch (PDOException $e) {
    error_log("Error fetching status counts: " . $e->getMessage());
    $counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'completed' => 0, 'cancelled' => 0];
}

function getStatusClass($status) {
    return match ($status) {
        'pending' => 'bg-warning text-dark',
        'approved' => 'bg-success text-white',
        'rejected' => 'bg-danger text-white',
        'completed' => 'bg-info text-white',
        'cancelled' => 'bg-danger text-white',
        default => 'bg-secondary text-white'
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Orders Management - Catering Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom CSS -->
    <link href="../css/admin.css" rel="stylesheet">
    <style>
        body { background: #f4f7fa; font-family: 'Segoe UI', sans-serif; }
        .main-content { padding: 20px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .card-header { color: white; border-radius: 12px 12px 0 0; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .pending-card-header { background: #007bff; }
        .approved-card-header { background: #28a745; }
        .rejected-card-header { background: #dc3545; }
        .completed-card-header { background: #17a2b8; }
        .cancelled-card-header { background: #dc3545; }
        .status-counts { margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap; }
        .status-counts .badge { font-size: 1rem; padding: 8px 15px; }
        .table { background: white; border-radius: 10px; overflow: hidden; }
        .table th { background: #007bff; color: white; padding: 12px; }
        .table td { padding: 12px; vertical-align: middle; font-size: 0.9rem; }
        .status-btn { padding: 6px 15px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer; }
        .description-col { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .description-col:hover { white-space: normal; overflow: visible; }
    </style>
</head>
<body class="admin-dashboard">
    <?php include '../layout/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <h1 class="mb-4">Orders Management</h1>
            <div class="status-counts" id="statusCounts">
                <span class="badge bg-warning text-dark">Pending: <?=$counts['pending']?></span>
                <span class="badge bg-success text-white">Approved: <?=$counts['approved']?></span>
                <span class="badge bg-danger text-white">Rejected: <?=$counts['rejected']?></span>
                <span class="badge bg-info text-white">Completed: <?=$counts['completed']?></span>
                <span class="badge bg-danger text-white">Cancelled: <?=$counts['cancelled']?></span>
            </div>

            <!-- Cards -->
            <?php
            $cards = [
                'pending' => ['Pending Bookings', 'pending-card-header', true, true],
                'approved' => ['Approved Bookings', 'approved-card-header', true, false],
                'rejected' => ['Rejected Bookings', 'rejected-card-header', false, false],
                'completed' => ['Completed Bookings', 'completed-card-header', false, false],
                'cancelled' => ['Cancelled Bookings', 'cancelled-card-header', false, false]
            ];
            foreach ($cards as $status => [$title, $headerClass, $hasChat, $hasActions]) {
                $colspan = $hasChat ? ($hasActions ? 10 : 9) : 8;
                echo "
                <div class='card'>
                    <div class='card-header $headerClass'>
                        <h5 class='mb-0'>$title</h5>
                        <button class='btn btn-light btn-sm' onclick=\"exportTableToCSV('{$status}-bookings.csv', '{$status}BookingsTable')\">
                            <i class='fas fa-download'></i> Export
                        </button>
                    </div>
                    <div class='card-body'>
                        <div class='table-responsive'>
                            <table class='table table-striped' id='{$status}BookingsTable'>
                                <thead>
                                    <tr>
                                        <th>ID</th><th>Customer</th><th>Package</th><th>Category</th><th>Guests</th>
                                        <th>Description</th><th>Status</th>" . ($hasChat ? "<th>Chat</th>" : "") . "<th>Amount</th>" . ($hasActions ? "<th>Actions</th>" : "") . "
                                    </tr>
                                </thead>
                                <tbody id='{$status}BookingsBody'></tbody>
                            </table>
                        </div>
                    </div>
                </div>";
            }
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const statusMap = {
            'pending': '#pendingBookingsBody',
            'approved': '#approvedBookingsBody',
            'rejected': '#rejectedBookingsBody',
            'completed': '#completedBookingsBody',
            'cancelled': '#cancelledBookingsBody'
        };

        function exportTableToCSV(filename, tableId) {
            const table = document.getElementById(tableId);
            const rows = table.querySelectorAll("tr");
            let csv = ["\ufeff"];
            rows.forEach(row => {
                const cols = Array.from(row.querySelectorAll("td, th")).map(col => 
                    `"${(col.querySelector("select")?.value || col.textContent.trim()).replace(/"/g, '""')}"`
                );
                csv.push(cols.join(","));
            });
            const blob = new Blob([csv.join("\n")], { type: "text/csv;charset=utf-8;" });
            const link = document.createElement("a");
            link.download = filename;
            link.href = URL.createObjectURL(blob);
            link.style.display = "none";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function updateStatus(event, selectElement) {
            event.preventDefault();
            const form = selectElement.closest('form');
            const bookingId = form.querySelector('input[name="booking_id"]').value;
            const newStatus = selectElement.value;
            const row = selectElement.closest('tr');
            const previousValue = selectElement.dataset.previousValue || selectElement.value;

            $.ajax({
                url: 'update_booking_status.php',
                type: 'POST',
                data: { booking_id: bookingId, booking_status: newStatus },
                dataType: 'json',
                success: response => {
                    if (response.success) {
                        moveBookingRow(bookingId, newStatus, row);
                        updateStatusCounts();
                    } else {
                        alert("Failed to update: " + (response.message || "Unknown error"));
                        selectElement.value = previousValue;
                    }
                },
                error: (xhr, status, error) => {
                    alert("Error updating status: " + error);
                    selectElement.value = previousValue;
                }
            });
            selectElement.dataset.previousValue = newStatus;
        }

        function moveBookingRow(bookingId, newStatus, row) {
            $(row).fadeOut(200, function() {
                $(this).remove();
                $.ajax({
                    url: `fetch_${newStatus}_bookings.php`,
                    type: 'GET',
                    data: { booking_id: bookingId },
                    dataType: 'html',
                    cache: false,
                    success: data => {
                        const $newRow = $(data).filter('tr').first();
                        if ($newRow.length) {
                            $(statusMap[newStatus]).append($newRow);
                            $newRow.hide().fadeIn(200);
                        }
                    },
                    error: (xhr, status, error) => console.error(`Error fetching ${newStatus} booking: ${error}`)
                });
            });
        }

        function updateStatusCounts() {
            $.ajax({
                url: 'fetch_status_counts.php',
                type: 'GET',
                dataType: 'json',
                cache: false,
                success: counts => {
                    $('#statusCounts .badge').each(function() {
                        const status = $(this).text().split(':')[0].toLowerCase();
                        $(this).text(`${status.charAt(0).toUpperCase() + status.slice(1)}: ${counts[status] || 0}`);
                    });
                },
                error: (xhr, status, error) => console.error("Error fetching status counts: " + error)
            });
        }

        function loadInitialBookings() {
            const statuses = ['pending', 'approved', 'rejected', 'completed', 'cancelled'];
            statuses.forEach(status => {
                $.ajax({
                    url: `fetch_${status}_bookings.php`,
                    type: 'GET',
                    dataType: 'html',
                    cache: false,
                    success: data => $(`#${status}BookingsBody`).html(data),
                    error: (xhr, status, error) => console.error(`Error loading ${status} bookings: ${error}`)
                });
            });
            updateStatusCounts();
        }

        $(document).ready(() => loadInitialBookings());
    </script>
</body>
</html>