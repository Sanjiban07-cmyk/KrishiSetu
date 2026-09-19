<?php

session_start();

require_once "../config/database.php";

// Farmer access only
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "farmer") {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["user_id"];


// --------------------------------------------------
// GET FARMER PAYMENT RECORDS
// --------------------------------------------------

$sql = "
    SELECT
        p.id AS payment_id,
        p.amount,
        p.payment_reference,
        p.status AS payment_status,
        p.paid_at,

        pr.crop_name,
        pr.quantity,
        pr.unit,
        pr.status AS procurement_status,

        b.booking_token,

        pc.centre_name,
        pc.venue

    FROM payments p

    INNER JOIN procurement pr
        ON p.procurement_id = pr.id

    INNER JOIN bookings b
        ON pr.booking_id = b.id

    INNER JOIN procurement_centres pc
        ON b.centre_id = pc.id

    WHERE b.farmer_id = ?

    ORDER BY p.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Payment Status | KrishiSetu</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">


    <style>

        .payment-page {
            min-height: 100vh;
            background: var(--background);
        }

        .payment-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 22px 32px;

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
            padding: 10px 18px;
            border: 1px solid var(--primary);
            border-radius: var(--radius-md);

            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .payment-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 50px 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 25px;

            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
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

        .payment-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);

            padding: 30px;
            margin-bottom: 25px;
        }

        .payment-top {
            display: flex;
            align-items: center;
            justify-content: space-between;

            margin-bottom: 25px;
        }

        .payment-title {
            font-size: 21px;
            color: var(--text-primary);
        }

        .status {
            display: inline-block;

            padding: 7px 14px;
            border-radius: 20px;

            font-size: 13px;
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

        .amount-box {
            background: var(--primary-light);
            border-radius: var(--radius-md);

            padding: 22px;
            margin-bottom: 22px;
        }

        .amount-label {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .amount {
            color: var(--primary);
            font-size: 32px;
            font-weight: 700;
            margin-top: 5px;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .info-box {
            background: var(--background);
            border-radius: var(--radius-md);
            padding: 17px;
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

        .reference {
            color: var(--primary);
        }

        .empty-state {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);

            padding: 55px 30px;
            text-align: center;
        }

        .empty-icon {
            font-size: 42px;
            margin-bottom: 15px;
        }

        .empty-state h3 {
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .empty-state p {
            color: var(--text-secondary);
        }


        @media (max-width: 650px) {

            .payment-header {
                padding: 18px;
            }

            .payment-container {
                padding: 35px 15px;
            }

            .page-title {
                font-size: 28px;
            }

            .payment-card {
                padding: 22px;
            }

            .payment-top {
                align-items: flex-start;
                gap: 15px;
                flex-direction: column;
            }

            .payment-grid {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>

<div class="payment-page">


    <!-- HEADER -->

    <header class="payment-header">

        <div>

            <div class="brand-name">
                KrishiSetu
            </div>

            <div class="brand-subtitle">
                Farmer Portal
            </div>

        </div>

        <a href="../login.php"
           class="logout-btn">
            Logout
        </a>

    </header>


    <!-- CONTENT -->

    <main class="payment-container">


        <a href="dashboard.php"
           class="back-link">
            ← Back to Dashboard
        </a>


        <h1 class="page-title">
            Payment Status
        </h1>

        <p class="page-description">
            View the payment status of your paddy procurement.
        </p>


        <?php if ($result->num_rows > 0): ?>


            <?php while ($row = $result->fetch_assoc()): ?>

                <div class="payment-card">


                    <!-- TOP -->

                    <div class="payment-top">

                        <div>

                            <div class="payment-title">
                                Procurement Payment
                            </div>

                            <div style="color:var(--text-secondary);margin-top:5px;">

                                Booking Token:
                                <strong>
                                    <?= htmlspecialchars($row["booking_token"]) ?>
                                </strong>

                            </div>

                        </div>


                        <span class="status status-<?= htmlspecialchars($row["payment_status"]) ?>">

                            <?= htmlspecialchars($row["payment_status"]) ?>

                        </span>

                    </div>


                    <!-- AMOUNT -->

                    <div class="amount-box">

                        <div class="amount-label">
                            Payment Amount
                        </div>

                        <div class="amount">

                            ₹<?= number_format((float)$row["amount"], 2) ?>

                        </div>

                    </div>


                    <!-- INFORMATION -->

                    <div class="payment-grid">


                        <div class="info-box">

                            <span class="info-label">
                                Crop
                            </span>

                            <span class="info-value">

                                <?= htmlspecialchars($row["crop_name"] ?? "Paddy") ?>

                            </span>

                        </div>


                        <div class="info-box">

                            <span class="info-label">
                                Quantity
                            </span>

                            <span class="info-value">

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


                        <div class="info-box">

                            <span class="info-label">
                                Venue
                            </span>

                            <span class="info-value">

                                <?= htmlspecialchars($row["venue"] ?? "—") ?>

                            </span>

                        </div>


                        <div class="info-box">

                            <span class="info-label">
                                Payment Reference
                            </span>

                            <span class="info-value reference">

                                <?= !empty($row["payment_reference"])
                                    ? htmlspecialchars($row["payment_reference"])
                                    : "Not available yet" ?>

                            </span>

                        </div>


                        <div class="info-box">

                            <span class="info-label">
                                Paid On
                            </span>

                            <span class="info-value">

                                <?php if (!empty($row["paid_at"])): ?>

                                    <?= date(
                                        "d M Y, h:i A",
                                        strtotime($row["paid_at"])
                                    ) ?>

                                <?php else: ?>

                                    Payment not completed

                                <?php endif; ?>

                            </span>

                        </div>


                    </div>


                </div>

            <?php endwhile; ?>


        <?php else: ?>


            <div class="empty-state">

                <div class="empty-icon">
                    💰
                </div>

                <h3>
                    Payment Not Available Yet
                </h3>

                <p>
                    Your payment information will appear here
                    after your procurement record is processed.
                </p>

            </div>


        <?php endif; ?>


    </main>

</div>

</body>

</html>

<?php

$stmt->close();

?>