<?php

session_start();

require_once "../config/database.php";

// Admin access only
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}


// --------------------------------------------------
// UPDATE PAYMENT
// --------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $payment_id = intval($_POST["payment_id"] ?? 0);
    $amount = floatval($_POST["amount"] ?? 0);
    $reference = trim($_POST["payment_reference"] ?? "");
    $status = $_POST["status"] ?? "pending";


    $allowed_statuses = [
        "pending",
        "processing",
        "paid",
        "failed"
    ];


    if (
        $payment_id > 0 &&
        $amount >= 0 &&
        in_array($status, $allowed_statuses, true)
    ) {

        if ($status === "paid") {

            $stmt = $conn->prepare(
                "UPDATE payments
                 SET amount = ?,
                     payment_reference = ?,
                     status = ?,
                     paid_at = NOW()
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "dssi",
                $amount,
                $reference,
                $status,
                $payment_id
            );

        } else {

            $stmt = $conn->prepare(
                "UPDATE payments
                 SET amount = ?,
                     payment_reference = ?,
                     status = ?,
                     paid_at = NULL
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "dssi",
                $amount,
                $reference,
                $status,
                $payment_id
            );
        }


        $stmt->execute();
        $stmt->close();
    }


    header("Location: payment.php?updated=1");
    exit;
}


// --------------------------------------------------
// GET PAYMENT RECORDS
// --------------------------------------------------

$sql = "
    SELECT
        p.id AS payment_id,
        p.amount,
        p.payment_reference,
        p.status AS payment_status,
        p.paid_at,

        pr.id AS procurement_id,
        pr.crop_name,
        pr.quantity,
        pr.unit,
        pr.status AS procurement_status,

        b.booking_token,

        u.name AS farmer_name,
        u.mobile AS farmer_mobile,

        pc.centre_name

    FROM payments p

    INNER JOIN procurement pr
        ON p.procurement_id = pr.id

    INNER JOIN bookings b
        ON pr.booking_id = b.id

    INNER JOIN users u
        ON b.farmer_id = u.id

    INNER JOIN procurement_centres pc
        ON b.centre_id = pc.id

    ORDER BY p.id DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Payment Management | KrishiSetu</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">


    <style>

        .admin-page {
            min-height: 100vh;
            background: var(--background);
        }

        .admin-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 25px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-name {
            color: var(--primary);
            font-size: 28px;
            font-weight: 700;
        }

        .brand-subtitle {
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .logout-btn {
            padding: 11px 20px;
            border: 1px solid var(--primary);
            border-radius: var(--radius-md);
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .admin-container {
            max-width: 1250px;
            margin: 0 auto;
            padding: 55px 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 25px;
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        .page-title {
            font-size: 36px;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .page-description {
            color: var(--text-secondary);
            margin-bottom: 35px;
        }

        .success-message {
            background: var(--primary-light);
            border: 1px solid #b8dfca;
            color: var(--primary-dark);
            padding: 14px 18px;
            border-radius: var(--radius-md);
            margin-bottom: 25px;
            font-weight: 600;
        }

        .payment-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 25px;
        }

        .payment-info {
            padding: 25px;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .info-box {
            background: var(--background);
            padding: 17px;
            border-radius: var(--radius-md);
        }

        .info-label {
            display: block;
            color: var(--text-light);
            font-size: 13px;
            margin-bottom: 6px;
        }

        .info-value {
            color: var(--text-primary);
            font-weight: 600;
        }

        .token {
            color: var(--primary);
            font-weight: 700;
        }

        .payment-form {
            border-top: 1px solid var(--border);
            padding-top: 22px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 7px;
            color: var(--text-primary);
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: white;
            font-size: 14px;
            outline: none;
        }

        .form-input:focus,
        .form-select:focus {
            border-color: var(--primary);
        }

        .update-btn {
            padding: 12px 20px;
            border: none;
            border-radius: var(--radius-md);
            background: var(--primary);
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        .update-btn:hover {
            background: var(--primary-dark);
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .status-pending {
            background: #fff4df;
            color: #a96800;
        }

        .status-processing {
            background: #e8f2ff;
            color: #28659b;
        }

        .status-paid {
            background: var(--primary-light);
            color: var(--primary-dark);
        }

        .status-failed {
            background: #ffe9e9;
            color: #b52d2d;
        }

        .empty-state {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 50px;
            text-align: center;
            color: var(--text-secondary);
        }


        @media (max-width: 900px) {

            .payment-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .payment-form {
                grid-template-columns: 1fr 1fr;
            }

        }


        @media (max-width: 600px) {

            .admin-header {
                padding: 20px;
            }

            .admin-container {
                padding: 35px 15px;
            }

            .page-title {
                font-size: 28px;
            }

            .payment-grid {
                grid-template-columns: 1fr;
            }

            .payment-form {
                grid-template-columns: 1fr;
            }

            .logout-btn {
                padding: 9px 14px;
            }

        }

    </style>

</head>


<body>

<div class="admin-page">


    <!-- HEADER -->

    <header class="admin-header">

        <div>

            <div class="brand-name">
                KrishiSetu
            </div>

            <div class="brand-subtitle">
                Admin Portal
            </div>

        </div>

        <a href="../login.php"
           class="logout-btn">
            Logout
        </a>

    </header>


    <!-- CONTENT -->

    <main class="admin-container">


        <a href="dashboard.php"
           class="back-link">
            ← Back to Admin Dashboard
        </a>


        <h1 class="page-title">
            Payment Management
        </h1>

        <p class="page-description">
            Manage farmer procurement payments and update payment status.
        </p>


        <?php if (isset($_GET["updated"])): ?>

            <div class="success-message">
                ✓ Payment information updated successfully.
            </div>

        <?php endif; ?>


        <?php if ($result && $result->num_rows > 0): ?>


            <?php while ($row = $result->fetch_assoc()): ?>

                <div class="payment-card">

                    <div class="payment-info">


                        <div class="payment-grid">


                            <div class="info-box">

                                <span class="info-label">
                                    Farmer
                                </span>

                                <span class="info-value">
                                    <?= htmlspecialchars($row["farmer_name"]) ?>
                                </span>

                                <small>
                                    <?= htmlspecialchars($row["farmer_mobile"]) ?>
                                </small>

                            </div>


                            <div class="info-box">

                                <span class="info-label">
                                    Booking Token
                                </span>

                                <span class="token">
                                    <?= htmlspecialchars($row["booking_token"]) ?>
                                </span>

                            </div>


                            <div class="info-box">

                                <span class="info-label">
                                    Crop & Quantity
                                </span>

                                <span class="info-value">

                                    <?= htmlspecialchars($row["crop_name"] ?? "Paddy") ?>

                                    -

                                    <?= htmlspecialchars($row["quantity"] ?? "0") ?>

                                    <?= htmlspecialchars($row["unit"] ?? "kg") ?>

                                </span>

                            </div>


                            <div class="info-box">

                                <span class="info-label">
                                    Procurement Centre
                                </span>

                                <span class="info-value">
                                    <?= htmlspecialchars($row["centre_name"]) ?>
                                </span>

                            </div>


                        </div>


                        <!-- CURRENT STATUS -->

                        <div style="margin-bottom:20px;">

                            <strong>
                                Current Payment Status:
                            </strong>

                            <span class="status status-<?= htmlspecialchars($row["payment_status"]) ?>">

                                <?= htmlspecialchars($row["payment_status"]) ?>

                            </span>

                            <?php if (!empty($row["paid_at"])): ?>

                                <span style="margin-left:10px;color:var(--text-secondary);">

                                    Paid:
                                    <?= date("d M Y, h:i A", strtotime($row["paid_at"])) ?>

                                </span>

                            <?php endif; ?>

                        </div>


                        <!-- UPDATE FORM -->

                        <form method="POST"
                              class="payment-form">


                            <input
                                type="hidden"
                                name="payment_id"
                                value="<?= $row["payment_id"] ?>"
                            >


                            <div class="form-group">

                                <label>
                                    Payment Amount (₹)
                                </label>

                                <input
                                    type="number"
                                    name="amount"
                                    class="form-input"
                                    step="0.01"
                                    min="0"
                                    value="<?= htmlspecialchars($row["amount"] ?? "0") ?>"
                                    required
                                >

                            </div>


                            <div class="form-group">

                                <label>
                                    Payment Reference
                                </label>

                                <input
                                    type="text"
                                    name="payment_reference"
                                    class="form-input"
                                    placeholder="e.g. PAY-2026-001"
                                    value="<?= htmlspecialchars($row["payment_reference"] ?? "") ?>"
                                >

                            </div>


                            <div class="form-group">

                                <label>
                                    Payment Status
                                </label>

                                <select
                                    name="status"
                                    class="form-select"
                                >

                                    <option value="pending"
                                        <?= $row["payment_status"] === "pending" ? "selected" : "" ?>>
                                        Pending
                                    </option>

                                    <option value="processing"
                                        <?= $row["payment_status"] === "processing" ? "selected" : "" ?>>
                                        Processing
                                    </option>

                                    <option value="paid"
                                        <?= $row["payment_status"] === "paid" ? "selected" : "" ?>>
                                        Paid
                                    </option>

                                    <option value="failed"
                                        <?= $row["payment_status"] === "failed" ? "selected" : "" ?>>
                                        Failed
                                    </option>

                                </select>

                            </div>


                            <button
                                type="submit"
                                class="update-btn"
                            >
                                Update
                            </button>


                        </form>

                    </div>

                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <div class="empty-state">

                <h3>
                    No Payment Records
                </h3>

                <p>
                    Payment records will appear here after procurement records are created.
                </p>

            </div>


        <?php endif; ?>


    </main>

</div>

</body>

</html>