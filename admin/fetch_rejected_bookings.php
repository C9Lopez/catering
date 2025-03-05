<?php
require '../db.php';

$whereClause = "WHERE eb.booking_status = 'rejected'";
$params = [];
if (isset($_GET['booking_id'])) {
    $whereClause .= " AND eb.booking_id = :booking_id";
    $params[':booking_id'] = $_GET['booking_id'];
}

try {
    $stmt = $db->prepare("
        SELECT eb.*, u.first_name, u.last_name, p.name AS package_name, p.category AS package_category 
        FROM event_bookings eb 
        JOIN users u ON eb.user_id = u.user_id 
        JOIN catering_packages p ON eb.package_id = p.package_id 
        $whereClause
    ");
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($bookings)) {
        echo "<tr><td colspan='8' class='text-center text-muted'>No rejected bookings found.</td></tr>";
    } else {
        foreach ($bookings as $row) {
            $desc = "Date: " . htmlspecialchars($row['event_date']) . " | Time: " . htmlspecialchars($row['event_time']) . 
                    " | Setup: " . htmlspecialchars($row['setup_time']) . " | Location: " . htmlspecialchars($row['location']);
            echo "<tr>
                <td>" . htmlspecialchars($row['booking_id']) . "</td>
                <td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>
                <td>" . htmlspecialchars($row['package_name']) . "</td>
                <td>" . htmlspecialchars($row['package_category']) . "</td>
                <td>" . htmlspecialchars($row['number_of_guests'] ?? 'N/A') . "</td>
                <td class='description-col'>$desc</td>
                <td><form class='status-form' data-id='{$row['booking_id']}'>
                    <input type='hidden' name='booking_id' value='{$row['booking_id']}'>
                    <select name='booking_status' class='status-btn " . getStatusClass($row['booking_status']) . "' onchange='updateStatus(event, this)'>
                        <option value='pending' " . ($row['booking_status'] === 'pending' ? 'selected' : '') . ">Pending</option>
                        <option value='approved' " . ($row['booking_status'] === 'approved' ? 'selected' : '') . ">Approved</option>
                        <option value='rejected' " . ($row['booking_status'] === 'rejected' ? 'selected' : '') . ">Rejected</option>
                        <option value='completed' " . ($row['booking_status'] === 'completed' ? 'selected' : '') . ">Completed</option>
                        <option value='cancelled' " . ($row['booking_status'] === 'cancelled' ? 'selected' : '') . ">Cancelled</option>
                    </select></form></td>
                <td>₱" . number_format($row['total_amount'], 2) . "</td>
            </tr>";
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching rejected bookings: " . $e->getMessage());
    echo "<tr><td colspan='8' class='text-center text-muted'>Error loading bookings.</td></tr>";
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