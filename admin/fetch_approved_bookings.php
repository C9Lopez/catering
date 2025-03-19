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

    $bookingIds = array_column($bookings, 'booking_id');

    
    $unreadCounts = [];
    
    foreach ($bookingIds as $bookingId) {
        $chatStmt = $db->prepare("SELECT COUNT(*) as unread FROM chat_messages WHERE order_id = :booking_id AND is_unread != 0");
        $chatStmt->execute([
            ':booking_id' => $bookingId
        ]);
        $unreadCount = $chatStmt->fetchColumn();
    
        // Store unread count in an associative array with booking_id as the key
        $unreadCounts[$bookingId] = $unreadCount;
    }

    if (empty($bookings)) {
        echo "<tr><td colspan='10' class='text-center text-muted'>No approved bookings found.</td></tr>";
    } else {
        foreach ($bookings as $row) {
            // Main row content
            $descContent = "Date: " . htmlspecialchars($row['event_date']) . " | Time: " . htmlspecialchars($row['event_time']) . 
                           " | Setup: " . htmlspecialchars($row['setup_time']) . " | Location: " . htmlspecialchars($row['location']);
            $customerName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);

            // Expanded row content
            $detailsContent = "
                <div class='details-content'>
                    <div>
                        <h6>Customer Details</h6>
                        <p><strong>Name:</strong> " . htmlspecialchars($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']) . "</p>
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
                    </div>
                </div>
            ";

            // Main row
            echo "<tr>
            <td><i class='fas fa-chevron-down expand-btn'></i></td>
            <td>" . htmlspecialchars($row['booking_id']) . "</td>
            <td class='customer-col'>" . htmlspecialchars($customerName) . "</td>
            <td>" . htmlspecialchars($row['package_name']) . "</td>
            <td>" . htmlspecialchars($row['package_category']) . "</td>
            <td>" . htmlspecialchars($row['number_of_guests'] ?? 'N/A') . "</td>
            <td class='description-col'>" . htmlspecialchars($descContent) . "</td>
            <td>
                <form class='status-form' data-id='" . htmlspecialchars($row['booking_id']) . "'>
                    <input type='hidden' name='booking_id' value='" . htmlspecialchars($row['booking_id']) . "'>
                    <select name='booking_status' class='status-btn " . getStatusClass($row['booking_status']) . "' onchange='updateStatus(event, this)'>
                        <option value='pending' " . ($row['booking_status'] === 'pending' ? 'selected' : '') . ">Pending</option>
                        <option value='approved' " . ($row['booking_status'] === 'approved' ? 'selected' : '') . ">Approved</option>
                        <option value='rejected' " . ($row['booking_status'] === 'rejected' ? 'selected' : '') . ">Rejected</option>
                        <option value='completed' " . ($row['booking_status'] === 'completed' ? 'selected' : '') . ">Completed</option>
                        <option value='cancelled' " . ($row['booking_status'] === 'cancelled' ? 'selected' : '') . ">Cancelled</option>
                    </select>
                </form>
            </td>
            <td>
                <a href='chat.php?booking_id=" . htmlspecialchars($row['booking_id']) . "' class='btn btn-secondary btn-sm'>
                    <i class='fas fa-comments'></i>";
        
            // Fetch the correct unread count for this booking_id
            $bookingId = $row['booking_id'];
            $unreadCount = $unreadCounts[$bookingId] ?? 0;

            if ($unreadCount > 0) {
                echo " <span class='badge bg-danger'>" . htmlspecialchars($unreadCount) . "</span>";
            }
        
        echo "</a>
            </td>
            <td>₱" . number_format($row['total_amount'], 2) . "</td>
            <td>
                <a href='view_order.php?id=" . htmlspecialchars($row['booking_id']) . "' class='btn btn-info btn-sm'><i class='fas fa-eye'></i></a>
                <a href='update_booking.php?id=" . htmlspecialchars($row['booking_id']) . "' class='btn btn-primary btn-sm'><i class='fas fa-edit'></i></a>
            </td>
        </tr>";
        
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching approved bookings: " . $e->getMessage());
    echo "<tr><td colspan='10' class='text-center text-muted'>Error loading bookings.</td></tr>";
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