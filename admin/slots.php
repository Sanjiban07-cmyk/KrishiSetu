<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* =========================
   SELECTED ADMIN CENTRE
   ========================= */

$selectedCentreId = (int)($_SESSION['admin_centre_id'] ?? 0);

if ($selectedCentreId <= 0) {
    header("Location: dashboard.php");
    exit;
}

$centreStmt = $conn->prepare("
    SELECT id, centre_name, centre_code, block, district
    FROM procurement_centres
    WHERE id = ?
      AND status = 'active'
    LIMIT 1
");
$centreStmt->bind_param("i", $selectedCentreId);
$centreStmt->execute();
$selectedCentre = $centreStmt->get_result()->fetch_assoc();
$centreStmt->close();

if (!$selectedCentre) {
    die("Selected procurement centre is invalid or inactive.");
}

/* =========================
   CREATE SLOT
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_slot"])) {

    /* Always create the slot for the centre selected by the admin. */
    $centre_id = $selectedCentreId;
    $slot_date = $_POST["slot_date"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];
    $capacity = (int)$_POST["capacity"];

    if (
        $centre_id <= 0 ||
        empty($slot_date) ||
        empty($start_time) ||
        empty($end_time) ||
        $capacity <= 0
    ) {
        header("Location: slots.php?error=invalid");
        exit;
    }

    /* Prevent invalid timing */
    if ($start_time >= $end_time) {
        header("Location: slots.php?error=time");
        exit;
    }

    /* Prevent duplicate slot */
    $check = $conn->prepare("
        SELECT id
        FROM slots
        WHERE centre_id = ?
          AND slot_date = ?
          AND start_time = ?
          AND end_time = ?
    ");

    $check->bind_param(
        "isss",
        $centre_id,
        $slot_date,
        $start_time,
        $end_time
    );

    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows > 0) {
        $check->close();

        header("Location: slots.php?error=duplicate");
        exit;
    }

    $check->close();

    /* Create slot */

    $stmt = $conn->prepare("
        INSERT INTO slots
        (
            centre_id,
            slot_date,
            start_time,
            end_time,
            capacity,
            booked_count,
            status
        )
        VALUES (?, ?, ?, ?, ?, 0, 'available')
    ");

    $stmt->bind_param(
        "isssi",
        $centre_id,
        $slot_date,
        $start_time,
        $end_time,
        $capacity
    );

    $stmt->execute();
    $stmt->close();

    header("Location: slots.php?success=created");
    exit;
}


/* =========================
   UPDATE SLOT STATUS
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {

    $slot_id = (int)$_POST["slot_id"];
    $status = $_POST["status"];

    $allowedStatuses = [
        "available",
        "full",
        "closed"
    ];

    if (!in_array($status, $allowedStatuses, true)) {
        header("Location: slots.php?error=status");
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE slots
        SET status = ?
        WHERE id = ?
          AND centre_id = ?
    ");

    $stmt->bind_param(
        "sii",
        $status,
        $slot_id,
        $selectedCentreId
    );

    $stmt->execute();
    $stmt->close();

    header("Location: slots.php?success=status");
    exit;
}


/* =========================
   DELETE SLOT
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_slot"])) {

    $slot_id = (int)$_POST["slot_id"];

    /* Only delete slots with no bookings */

    $check = $conn->prepare("
        SELECT booked_count
        FROM slots
        WHERE id = ?
          AND centre_id = ?
    ");

    $check->bind_param("ii", $slot_id, $selectedCentreId);
    $check->execute();

    $data = $check->get_result()->fetch_assoc();

    $check->close();

    if (!$data || (int)$data["booked_count"] > 0) {
        header("Location: slots.php?error=booked");
        exit;
    }

    $stmt = $conn->prepare("
        DELETE FROM slots
        WHERE id = ?
          AND centre_id = ?
    ");

    $stmt->bind_param("ii", $slot_id, $selectedCentreId);
    $stmt->execute();
    $stmt->close();

    header("Location: slots.php?success=deleted");
    exit;
}


/* =========================
   CENTRES
========================= */

/* The selected centre is the only centre available for slot creation. */
$centres = $conn->prepare("
    SELECT id, centre_name, centre_code
    FROM procurement_centres
    WHERE id = ?
      AND status = 'active'
");
$centres->bind_param("i", $selectedCentreId);
$centres->execute();
$centres = $centres->get_result();


/* =========================
   SLOTS
========================= */

$sql = "
    SELECT
        s.id,
        s.centre_id,
        s.slot_date,
        s.start_time,
        s.end_time,
        s.capacity,
        s.booked_count,
        s.status,
        pc.centre_name,
        pc.centre_code
    FROM slots s
    JOIN procurement_centres pc
        ON s.centre_id = pc.id
    WHERE s.centre_id = ?
    ORDER BY
        s.slot_date ASC,
        s.start_time ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $selectedCentreId);
$stmt->execute();
$slots = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Slot Management | KrishiSetu</title>

<link rel="stylesheet"
      href="../assets/css/style.css">

<style>

body {
    background: #f7f9f7;
    margin: 0;
    font-family: Arial, sans-serif;
    color: #17352a;
}

.admin-page {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.back-link {
    color: #087443;
    text-decoration: none;
    font-weight: 600;
}

.page-title {
    margin-top: 30px;
    margin-bottom: 8px;
}

.page-description {
    color: #61716a;
    margin-bottom: 30px;
}

.working-centre {
    background: #e8f5ee;
    border: 1px solid #cce8d9;
    border-radius: 12px;
    padding: 14px 18px;
    margin-bottom: 25px;
    color: #61716a;
}

.working-centre strong {
    color: #087443;
}

/* FORM */

.form-card {
    background: white;
    border: 1px solid #dfe7e2;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 30px;
}

.form-card h2 {
    margin-top: 0;
}

.slot-form {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 15px;
    align-items: end;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 7px;
}

.form-group input,
.form-group select {
    padding: 11px;
    border: 1px solid #dfe7e2;
    border-radius: 8px;
    font-size: 14px;
}

.create-btn {
    background: #087443;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

/* TABLE */

.table-card {
    background: white;
    border: 1px solid #dfe7e2;
    border-radius: 16px;
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #edf1ee;
}

th {
    background: #f7f9f7;
}

.status {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.available {
    background: #e8f5ee;
    color: #087443;
}

.full {
    background: #fff7e6;
    color: #9a6a00;
}

.closed {
    background: #fdecec;
    color: #c93434;
}

.actions {
    display: flex;
    gap: 8px;
}

.actions select {
    padding: 8px;
    border: 1px solid #dfe7e2;
    border-radius: 7px;
}

.update-btn {
    background: #087443;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 7px;
    cursor: pointer;
}

.delete-btn {
    background: #fdecec;
    color: #c93434;
    border: none;
    padding: 8px 12px;
    border-radius: 7px;
    cursor: pointer;
}

.message {
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.success {
    background: #e8f5ee;
    color: #087443;
}

.error {
    background: #fdecec;
    color: #c93434;
}

@media (max-width: 900px) {

    .slot-form {
        grid-template-columns: 1fr 1fr;
    }

    .table-card {
        overflow-x: auto;
    }

    table {
        min-width: 900px;
    }
}

</style>

</head>

<body>

<div class="admin-page">

    <a href="dashboard.php" class="back-link">
        ← Back to Admin Dashboard
    </a>

    <h1 class="page-title">
        Slot Management
    </h1>

    <p class="page-description">
        Create and manage procurement dates, timings, capacity and availability.
    </p>

    <div class="working-centre">
        <strong>Working Centre:</strong>
        <?= htmlspecialchars($selectedCentre["centre_name"]) ?>
        —
        <?= htmlspecialchars($selectedCentre["block"]) ?>,
        <?= htmlspecialchars($selectedCentre["district"]) ?>
        (<?= htmlspecialchars($selectedCentre["centre_code"]) ?>)
    </div>


    <?php if (isset($_GET["success"])): ?>

        <div class="message success">
            ✓ Slot information updated successfully.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET["error"])): ?>

        <div class="message error">

            <?php if ($_GET["error"] === "duplicate"): ?>

                A slot with the same date and timing already exists.

            <?php elseif ($_GET["error"] === "time"): ?>

                End time must be later than start time.

            <?php elseif ($_GET["error"] === "booked"): ?>

                This slot cannot be deleted because farmers have already booked it.

            <?php else: ?>

                Invalid slot information.

            <?php endif; ?>

        </div>

    <?php endif; ?>


    <!-- CREATE SLOT -->

    <div class="form-card">

        <h2>Create New Slot</h2>

        <form method="POST"
              class="slot-form">

            <div class="form-group">

                <label>Procurement Centre</label>

                <input
                    type="text"
                    value="<?= htmlspecialchars($selectedCentre["centre_name"]) ?>"
                    readonly
                >

                <small style="margin-top:6px; color:#61716a;">
                    Slot will be created for the currently selected centre.
                </small>

            </div>


            <div class="form-group">

                <label>Date</label>

                <input
                    type="date"
                    name="slot_date"
                    min="<?= date('Y-m-d') ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Start Time</label>

                <input
                    type="time"
                    name="start_time"
                    required
                >

            </div>


            <div class="form-group">

                <label>End Time</label>

                <input
                    type="time"
                    name="end_time"
                    required
                >

            </div>


            <div class="form-group">

                <label>Capacity</label>

                <input
                    type="number"
                    name="capacity"
                    min="1"
                    max="1000"
                    placeholder="20"
                    required
                >

            </div>


            <button
                type="submit"
                name="create_slot"
                class="create-btn">

                + Create Slot

            </button>

        </form>

    </div>


    <!-- SLOT LIST -->

    <div class="table-card">

        <table>

            <thead>

                <tr>

                    <th>Centre</th>

                    <th>Date</th>

                    <th>Timing</th>

                    <th>Capacity</th>

                    <th>Booked</th>

                    <th>Status</th>

                    <th>Actions</th>

                </tr>

            </thead>


            <tbody>

            <?php if ($slots && $slots->num_rows > 0): ?>

                <?php while ($slot = $slots->fetch_assoc()): ?>

                    <?php
                    $remaining =
                        (int)$slot["capacity"]
                        -
                        (int)$slot["booked_count"];
                    ?>

                    <tr>

                        <td>

                            <strong>
                                <?= htmlspecialchars($slot["centre_name"]) ?>
                            </strong>

                            <br>

                            <small>
                                <?= htmlspecialchars($slot["centre_code"]) ?>
                            </small>

                        </td>


                        <td>

                            <?= date(
                                "d M Y",
                                strtotime($slot["slot_date"])
                            ) ?>

                        </td>


                        <td>

                            <?= date(
                                "h:i A",
                                strtotime($slot["start_time"])
                            ) ?>

                            -

                            <?= date(
                                "h:i A",
                                strtotime($slot["end_time"])
                            ) ?>

                        </td>


                        <td>
                            <?= (int)$slot["capacity"] ?>
                        </td>


                        <td>

                            <?= (int)$slot["booked_count"] ?>

                            <br>

                            <small>
                                <?= $remaining ?> remaining
                            </small>

                        </td>


                        <td>

                            <span class="status <?= htmlspecialchars($slot["status"]) ?>">

                                <?= ucfirst($slot["status"]) ?>

                            </span>

                        </td>


                        <td>

                            <div class="actions">

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="slot_id"
                                        value="<?= (int)$slot["id"] ?>"
                                    >

                                    <select name="status">

                                        <option
                                            value="available"
                                            <?= $slot["status"] === "available" ? "selected" : "" ?>
                                        >
                                            Available
                                        </option>

                                        <option
                                            value="full"
                                            <?= $slot["status"] === "full" ? "selected" : "" ?>
                                        >
                                            Full
                                        </option>

                                        <option
                                            value="closed"
                                            <?= $slot["status"] === "closed" ? "selected" : "" ?>
                                        >
                                            Closed
                                        </option>

                                    </select>

                                    <button
                                        type="submit"
                                        name="update_status"
                                        class="update-btn">

                                        Update

                                    </button>

                                </form>


                                <?php if ((int)$slot["booked_count"] === 0): ?>

                                    <form method="POST">

                                        <input
                                            type="hidden"
                                            name="slot_id"
                                            value="<?= (int)$slot["id"] ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="delete_slot"
                                            class="delete-btn"
                                            onclick="return confirm('Delete this slot?');">

                                            Delete

                                        </button>

                                    </form>

                                <?php endif; ?>

                            </div>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="7"
                        style="text-align:center; padding:30px;">

                        No slots created yet.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>