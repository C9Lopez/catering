<?php
require '../db.php';

$whereClause = "WHERE eb.booking_status = 'approved'";
$params = [];
if (isset($_GET['booking_id'])) {
    $whereClause .= " AND eb.booking_id = :booking_id";
    $params[':booking_id'] = $_GET['booking_id'];
}

try {
    $stmt = $db->prepare("
        SELECT eb.booking_id, eb.location, eb.event_date, eb.event_time, eb.setup_time, eb.number_of_guests, eb.total_amount, eb.booking_status,
               cp.name AS package_name, cp.category AS package_category,
               u.first_name, u.middle_name, u.last_name, u.birthdate, u.gender, u.address, u.contact_no, u.email
        FROM event_bookings eb
        LEFT JOIN catering_packages cp ON eb.package_id = cp.package_id
        LEFT JOIN users u ON eb.user_id = u.user_id
        $whereClause
    ");
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($bookings)) {
        echo "<tr><td colspan='11' class='text-center text-muted'>No approved bookings found.</td></tr>";
    } else {
        foreach ($bookings as $row) {
            // Main row content
            $descContent = "Date: " . htmlspecialchars($row['event_date']) . " | Time: " . htmlspecialchars($row['event_time']) . 
                           " | Setup: " . htmlspecialchars($row['setup_time']) . " | Location: " . htmlspecialchars($row['location']);
            $customerName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);

            // Expanded row content
            $isCustom = (strtolower($row['package_name']) === 'custom wedding package');
            $customDetails = '';
            if ($isCustom) {
                // Fetch customizations from event_bookings table (assume field: customizations)
                $customStmt = $db->prepare("SELECT customizations FROM event_bookings WHERE booking_id = ?");
                $customStmt->execute([$row['booking_id']]);
                $customRow = $customStmt->fetch(PDO::FETCH_ASSOC);
                $customizations = [];
                if ($customRow && !empty($customRow['customizations'])) {
                    $customizations = json_decode($customRow['customizations'], true);
                }
                $customDetails .= "<div class='mt-3'><h6>Custom Menu Details</h6>";
                if (!empty($customizations) && is_array($customizations)) {
                    $customDetails .= "<table class='table table-bordered table-sm'><thead><tr><th>Menu Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>";
                    $grandTotal = 0;
                    foreach ($customizations as $item) {
                        $itemTitle = htmlspecialchars(isset($item['title']) ? $item['title'] : (isset($item['label']) ? $item['label'] : ''));
                        $itemQty = intval($item['quantity'] ?? ($item['qty'] ?? 0));
                        $itemPrice = floatval($item['price'] ?? 0);
                        $itemTotal = $itemQty * $itemPrice;
                        $grandTotal += $itemTotal;
                        $customDetails .= "<tr><td>{$itemTitle}</td><td>{$itemQty}</td><td>₱" . number_format($itemPrice, 2) . "</td><td>₱" . number_format($itemTotal, 2) . "</td></tr>";
                    }
                    $customDetails .= "<tr><th colspan='3' class='text-end'>Total</th><th>₱" . number_format($grandTotal, 2) . "</th></tr>";
                    $customDetails .= "</tbody></table>";
                } else {
                    $customDetails .= "<div class='text-danger'>No custom menu items found. <small>(Check if customizations were saved in the booking record.)</small></div>";
                }
                $customDetails .= "<div><strong>Number of Guests:</strong> " . htmlspecialchars($row['number_of_guests'] ?? 'N/A') . "</div>";
                $customDetails .= "</div>";
            }
            $detailsContent = "
                <div class='details-content'>
                    <div>
                        <h6>Customer Details</h6>
                        <p><strong>Name:</strong> " . htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']) . "</p>
                        <p><strong>Birthdate:</strong> " . htmlspecialchars($row['birthdate']) . "</p>
                        <p><strong>Gender:</strong> " . htmlspecialchars($row['gender']) . "</p>
                        <p><strong>Address:</strong> " . htmlspecialchars($row['address']) . "</p>
                        <p><strong>Contact:</strong> " . htmlspecialchars($row['contact_no']) . "</p>
                        <p><strong>Email:</strong> " . htmlspecialchars($row['email']) . "</p>
                    </div>
                    <div>
                        <h6>Event Description</h6>
                        <p><strong>Date:</strong> " . htmlspecialchars($row['event_date']) . "</p>
                        <p><strong>Time:</strong> " . htmlspecialchars($row['event_time']) . "</p>
                        <p><strong>Setup Time:</strong> " . htmlspecialchars($row['setup_time']) . "</p>
                        <p><strong>Location:</strong> " . htmlspecialchars($row['location']) . "</p>
                        $customDetails
                    </div>
                </div>
            ";

            // Main row
            echo "<tr data-booking-id='{$row['booking_id']}'>";
            echo "<td><i class='fas fa-chevron-down expand-btn'></i></td>";
            echo "<td>" . htmlspecialchars($row['booking_id']) . "</td>";
            echo "<td class='customer-col'>" . $customerName . "</td>";
            echo "<td>" . htmlspecialchars($row['package_name'] ?? 'Custom') . "</td>";
            echo "<td>" . htmlspecialchars($row['package_category'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['number_of_guests'] ?? 'N/A') . "</td>";
            echo "<td class='description-col'>" . $descContent . "</td>";
            echo "<td><form class='status-form' data-id='{$row['booking_id']}'>";
            echo "<input type='hidden' name='booking_id' value='{$row['booking_id']}'>";
            echo "<select name='booking_status' class='status-btn " . getStatusClass($row['booking_status']) . "' onchange='updateStatus(event, this)'>";
            echo "<option value='pending' " . ($row['booking_status'] === 'pending' ? 'selected' : '') . ">Pending</option>";
            echo "<option value='on_process' " . ($row['booking_status'] === 'on_process' ? 'selected' : '') . ">On Process</option>";
            echo "<option value='approved' " . ($row['booking_status'] === 'approved' ? 'selected' : '') . ">Approved</option>";
            echo "<option value='rejected' " . ($row['booking_status'] === 'rejected' ? 'selected' : '') . ">Rejected</option>";
            echo "<option value='completed' " . ($row['booking_status'] === 'completed' ? 'selected' : '') . ">Completed</option>";
            echo "<option value='cancelled' " . ($row['booking_status'] === 'cancelled' ? 'selected' : '') . ">Cancelled</option>";
            echo "</select></form></td>";
            echo "<td>";
            if (in_array($row['booking_status'], ['approved', 'on_process'])) {
                echo "<a href='chat.php?booking_id={$row['booking_id']}' class='btn btn-secondary btn-sm'><i class='fas fa-comments'></i></a>";
            }
            echo "</td>";
            echo "<td>₱" . number_format($row['total_amount'], 2) . "</td>";

            echo "</tr>";

            // Details row (hidden by default)
            echo "<tr class='details-row'>";
            echo "<td colspan='11'>" . $detailsContent . "</td>";
            echo "</tr>";
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching approved bookings: " . $e->getMessage());
    echo "<tr><td colspan='11' class='text-center text-muted'>Error loading bookings.</td></tr>";
}

// New collapsible Bootstrap card layout for each approved booking
echo '<div class="container">';
echo '<div class="row">';
foreach ($bookings as $row) {
    $collapseId = 'collapse' . $row['booking_id'];
    echo '<div class="col-lg-4 col-md-6 mb-3">';
    echo '<div class="card h-100">';
    // Header with collapse trigger, using bg-success for approved
    echo '<div class="card-header bg-success text-white" data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '" style="cursor:pointer;">';
    echo '<h5 class="card-title mb-0">' . htmlspecialchars($row["package_name"]) . ' <i class="fas fa-chevron-down"></i></h5>';
    echo '</div>';
    // Collapsible card body
    echo '<div id="' . $collapseId . '" class="collapse">';
    echo '<div class="card-body">';
    echo '<p class="card-text">' . nl2br(htmlspecialchars($row["package_description"])) . '</p>';
    echo '<p><strong>User:</strong> ' . htmlspecialchars($row["first_name"] . ' ' . $row["last_name"]) . '</p>';
    echo '<p><strong>Date:</strong> ' . htmlspecialchars($row["event_date"]) . '</p>';
    echo '<p><strong>Status:</strong> <span class="' . getStatusClass($row["booking_status"]) . '">' . htmlspecialchars($row["booking_status"]) . '</span></p>';
    if(!empty($row["customizations"])) {
        $customs = json_decode($row["customizations"], true);
        if(is_array($customs)) {
            echo '<ul class="list-unstyled">';
            foreach($customs as $key => $val) {
                echo '<li><i class="fa fa-check text-success"></i> ' . htmlspecialchars($key) . ': ' . htmlspecialchars($val) . '</li>';
            }
            echo '</ul>';
        }
    }
    echo '</div>'; // card-body
    echo '</div>'; // collapse
    echo '</div>'; // card
    echo '</div>'; // col
}
echo '</div>'; // row
echo '</div>'; // container

function getStatusClass($status) {
    return match ($status) {
        'pending' => 'bg-warning text-dark',
        'on_process' => 'bg-primary text-white',
        'approved' => 'bg-success text-white',
        'rejected' => 'bg-danger text-white',
        'completed' => 'bg-info text-white',
        'cancelled' => 'bg-danger text-white',
        default => 'bg-secondary text-white'
    };
}
?>