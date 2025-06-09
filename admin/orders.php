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
    $counts = array_merge(['pending' => 0, 'on_process' => 0, 'approved' => 0, 'rejected' => 0, 'completed' => 0, 'cancelled' => 0], $counts);
} catch (PDOException $e) {
    error_log("Error fetching status counts: " . $e->getMessage());
    $counts = ['pending' => 0, 'on_process' => 0, 'approved' => 0, 'rejected' => 0, 'completed' => 0, 'cancelled' => 0];
}

// Get the booking_id to highlight from the URL
$highlightBookingId = isset($_GET['highlight']) ? (int)$_GET['highlight'] : null;

function getStatusClass($status) {
    return match ($status) {
        'pending' => 'bg-warning text-dark',
        'on_process' => 'bg-primary text-white', // New status class for "On Process"
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="../css/admin.css" rel="stylesheet">
    <style>
        body { background: #f4f7fa; font-family: 'Segoe UI', sans-serif; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .card-header { color: white; border-radius: 12px 12px 0 0; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .pending-card-header { background: #007bff; }
        .on-process-card-header { background: #17a2b8; } /* New header class for "On Process" */
        .approved-card-header { background: #28a745; }
        .rejected-card-header { background: #dc3545; }
        .completed-card-header { background: #17a2b8; }
        .cancelled-card-header { background: #dc3545; }
        .status-counts { margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap; }
        .status-counts .badge { font-size: 1rem; padding: 8px 15px; }
        .table { background: white; border-radius: 10px; margin-bottom: 0; }
        .table th { background: #007bff; color: white; padding: 12px; text-align: center; }
        .table td { padding: 12px; vertical-align: middle; font-size: 0.9rem; text-align: center; }
        .status-btn { padding: 6px 15px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer; }
        .description-col, .customer-col {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            position: relative;
            cursor: pointer;
        }
        /* Tooltip-like popup for description on hover (desktop) */
        .description-col:hover .description-tooltip {
            display: block;
        }
        .description-tooltip {
            display: none;
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            z-index: 1000;
            white-space: normal;
            font-size: 0.85rem;
            max-width: 300px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        /* Description toggle for mobile */
        .description-expanded {
            display: none;
            background: #f8f9fa;
            padding: 10px;
            font-size: 0.9rem;
            text-align: left;
            border-top: 1px solid #dee2e6;
        }
        .description-expanded.active {
            display: block;
        }
        .table-responsive {
            position: relative;
        }
        .expand-btn {
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .expand-btn.expanded {
            transform: rotate(180deg);
        }
        .details-row {
            display: none;
            background-color: #f8f9fa;
        }
        .details-row td {
            padding: 15px;
            border-top: none;
        }
        .details-content {
            display: flex;
            gap: 20px;
        }
        .details-content div {
            flex: 1;
        }
        .details-content h6 {
            margin-bottom: 10px;
            color: #007bff;
        }
        .details-content p {
            margin: 5px 0;
            font-size: 0.9rem;
        }
        /* Highlight style for the selected booking */
        .highlight {
            background-color: #fff3cd !important;
            transition: background-color 0.5s ease;
        }
        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .table th, .table td {
                font-size: 0.85rem;
                padding: 8px;
            }
            .description-col {
                max-width: 150px;
            }
            .customer-col {
                max-width: 120px;
            }
            /* Disable hover on mobile, enable click */
            .description-col:hover .description-tooltip {
                display: none;
            }
        }
        @media (max-width: 576px) {
            .table th, .table td {
                font-size: 0.8rem;
                padding: 6px;
            }
            .description-col {
                max-width: 100px;
            }
            .customer-col {
                max-width: 100px;
            }
            .details-content {
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="admin-dashboard">
    <?php include '../layout/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <h1 class="mb-4">Orders Management</h1>
            <div class="status-counts" id="statusCounts">
                <span class="badge bg-warning text-dark">Pending: <?=$counts['pending']?></span>
                <span class="badge bg-primary text-white">On Process: <?=$counts['on_process']?></span>
                <span class="badge bg-success text-white">Approved: <?=$counts['approved']?></span>
                <span class="badge bg-danger text-white">Rejected: <?=$counts['rejected']?></span>
                <span class="badge bg-info text-white">Completed: <?=$counts['completed']?></span>
                <span class="badge bg-danger text-white">Cancelled: <?=$counts['cancelled']?></span>
            </div>

            <!-- Cards -->
            <?php
            $cards = [
                'pending' => ['Pending Bookings', 'pending-card-header'],
                'on_process' => ['On Process Bookings', 'on-process-card-header'], // New section for "On Process"
                'approved' => ['Approved Bookings', 'approved-card-header'],
                'rejected' => ['Rejected Bookings', 'rejected-card-header'],
                'completed' => ['Completed Bookings', 'completed-card-header'],
                'cancelled' => ['Cancelled Bookings', 'cancelled-card-header']
            ];
            foreach ($cards as $status => [$title, $headerClass]) {
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
                                        <th></th> <!-- Expand/Collapse Column -->
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Package</th>
                                        <th>Category</th>
                                        <th>Guests</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Chat</th>
                                        <th>Amount</th>
                                   
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
    <script src="../js/admin.js"></script>
    <script>
        const statusMap = {
            'pending': '#pendingBookingsBody',
            'on_process': '#on_processBookingsBody', // Added for "On Process"
            'approved': '#approvedBookingsBody',
            'rejected': '#rejectedBookingsBody',
            'completed': '#completedBookingsBody',
            'cancelled': '#cancelledBookingsBody'
        };

        $(document).on('click', '.expand-btn', function() {
            const $btn = $(this);
            const $row = $btn.closest('tr');
            const $detailsRow = $row.next('.details-row');

            $btn.toggleClass('expanded');
            $detailsRow.slideToggle(300);
        });

        // Handle hover/click for description column
        $(document).on('click', '.description-col', function(e) {
            // Only toggle on mobile (screen width <= 991.98px)
            if (window.innerWidth <= 991.98) {
                const $row = $(this).closest('tr');
                const $descriptionExpanded = $row.next('.description-expanded');
                $descriptionExpanded.toggleClass('active');
            }
        });

        function exportTableToCSV(filename, tableId) {
            const table = document.getElementById(tableId);
            const rows = table.querySelectorAll("tr:not(.details-row):not(.description-expanded)");
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
                $(this).next('.details-row').remove();
                $(this).next('.description-expanded').remove();
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
            const statuses = ['pending', 'on_process', 'approved', 'rejected', 'completed', 'cancelled'];
            statuses.forEach(status => {
                $.ajax({
                    url: `fetch_${status}_bookings.php`,
                    type: 'GET',
                    dataType: 'html',
                    cache: false,
                    success: data => {
                        $(`#${status}BookingsBody`).html(data);
                        // Highlight the booking if it matches the highlightBookingId
                        highlightBooking();
                    },
                    error: (xhr, status, error) => console.error(`Error loading ${status} bookings: ${error}`)
                });
            });
            updateStatusCounts();
        }

        function highlightBooking() {
            const highlightBookingId = <?php echo json_encode($highlightBookingId); ?>;
            if (highlightBookingId) {
                const $row = $(`tr[data-booking-id="${highlightBookingId}"]`);
                if ($row.length) {
                    // Highlight the row
                    $row.addClass('highlight');
                    // Expand the section if it's not already expanded
                    const $card = $row.closest('.card');
                    const $cardBody = $card.find('.card-body');
                    if ($cardBody.is(':hidden')) {
                        $card.find('.card-header').click(); // Simulate a click to expand the section
                    }
                    // Scroll to the row
                    $('html, body').animate({
                        scrollTop: $row.offset().top - 100
                    }, 500);
                }
            }
        }

        $(document).ready(() => loadInitialBookings());
    </script>
</body>
</html>