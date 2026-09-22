<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$selectedCentreId = (int)($_SESSION["admin_centre_id"] ?? 0);

if ($selectedCentreId <= 0) {
    header("Location: dashboard.php");
    exit;
}

$centreStmt = $conn->prepare("
    SELECT id, centre_name, centre_code, block, district
    FROM procurement_centres
    WHERE id = ? AND status = 'active'
    LIMIT 1
");
$centreStmt->bind_param("i", $selectedCentreId);
$centreStmt->execute();
$selectedCentre = $centreStmt->get_result()->fetch_assoc();
$centreStmt->close();

if (!$selectedCentre) {
    die("Selected procurement centre is invalid or inactive.");
}

$crop_rates = [
    "Paddy"   => 45.00,
    "Wheat"   => 30.00,
    "Maize"   => 22.00,
    "Mustard" => 55.00
];

$sql = "
    SELECT
        b.id AS booking_id,
        b.booking_token,
        b.status AS booking_status,
        u.name AS farmer_name,
        u.mobile AS farmer_mobile,
        pc.centre_name,
        s.slot_date,
        s.start_time,
        s.end_time,
        p.id AS procurement_id,
        p.crop_name,
        p.quantity,
        p.unit,
        p.status AS procurement_status,
        p.updated_at
    FROM bookings b
    INNER JOIN farmers f ON b.farmer_id = f.id
    INNER JOIN users u ON f.user_id = u.id
    INNER JOIN procurement_centres pc ON b.centre_id = pc.id
    INNER JOIN slots s ON b.slot_id = s.id
    LEFT JOIN procurement p ON b.id = p.booking_id
    WHERE b.centre_id = ?
    ORDER BY b.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $selectedCentreId);
$stmt->execute();
$result = $stmt->get_result();

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}
$stmt->close();

$totalRecords = count($records);

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function statusLabel(string $status): string
{
    return match ($status) {
        "pending"  => "Pending",
        "arrived"  => "Farmer Arrived",
        "weighed"  => "Paddy Weighed",
        "accepted" => "Accepted",
        "cancelled" => "Cancelled",
        default    => ucfirst($status)
    };
}

function statusClass(string $status): string
{
    return match ($status) {
        "pending"   => "status-pending",
        "arrived"   => "status-arrived",
        "weighed"   => "status-weighed",
        "accepted"  => "status-accepted",
        "cancelled" => "status-cancelled",
        default     => "status-default"
    };
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurement Management | KrishiSetu</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        :root {
            --green: #087443;
            --green-dark: #055c35;
            --green-soft: #e8f5ee;
            --green-border: #cfe6d9;
            --bg: #f5f8f6;
            --surface: #ffffff;
            --text: #12352b;
            --muted: #61736b;
            --border: #dce7e1;
            --shadow: 0 8px 24px rgba(18,53,43,.055);
            --sidebar: 236px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        a { color: inherit; }

        .admin-shell { min-height: 100vh; }

        /* ---------- HEADER ---------- */
        .admin-header {
            height: 80px;
            background: #fff;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-logo {
            width: 44px;
            height: 44px;
            border-radius: 13px;
            background: var(--green-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 44px;
        }

        .brand-logo svg { width: 32px; height: 32px; display: block; }

        .brand-name {
            color: var(--green);
            font-size: 22px;
            font-weight: 800;
            line-height: 1.1;
        }

        .brand-subtitle {
            color: var(--muted);
            font-size: 12px;
            margin-top: 3px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .today-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--green-soft);
            color: var(--text);
            border: 1px solid #d8ebe0;
            border-radius: 12px;
            padding: 10px 13px;
            font-size: 13px;
        }

        .today-badge strong { color: var(--green); }

        .admin-user {
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 10px 14px;
            color: var(--muted);
            background: #fff;
            font-size: 13px;
        }

        .logout {
            text-decoration: none;
            color: var(--green);
            border: 1px solid #b9d8c8;
            border-radius: 9px;
            padding: 10px 15px;
            font-weight: 700;
            background: #fff;
        }

        .logout:hover { background: var(--green-soft); }

        /* ---------- LAYOUT ---------- */
        .admin-layout {
            display: grid;
            grid-template-columns: var(--sidebar) minmax(0, 1fr);
            min-height: calc(100vh - 80px);
        }

        .sidebar {
            background: #fff;
            border-right: 1px solid var(--border);
            padding: 25px 13px;
        }

        .sidebar-label {
            padding: 0 13px 12px;
            color: #7b8b84;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .7px;
        }

        .nav-list { display: grid; gap: 5px; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            min-height: 49px;
            padding: 0 13px;
            border-radius: 11px;
            text-decoration: none;
            color: var(--text);
            font-weight: 700;
            font-size: 14px;
        }

        .nav-item:hover { background: #f1f7f3; }

        .nav-item.active {
            color: var(--green);
            background: var(--green-soft);
            border-left: 3px solid var(--green);
            padding-left: 10px;
        }

        .nav-symbol {
            width: 24px;
            text-align: center;
            font-size: 17px;
            flex: 0 0 24px;
        }

        .sidebar-divider {
            border: 0;
            border-top: 1px solid var(--border);
            margin: 22px 0;
        }

        .main {
            min-width: 0;
            padding: 34px 40px 60px;
        }

        .content-width { max-width: 1500px; margin: 0 auto; }

        .back-link {
            display: inline-flex;
            text-decoration: none;
            color: var(--green);
            font-weight: 700;
            margin-bottom: 18px;
        }

        .page-heading { margin-bottom: 25px; }

        .page-heading h1 {
            margin: 0;
            font-size: 34px;
            line-height: 1.15;
            letter-spacing: -.5px;
        }

        .page-heading p {
            margin: 9px 0 0;
            color: var(--muted);
            font-size: 15px;
        }

        /* ---------- CENTRE CARD ---------- */
        .centre-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 17px;
            box-shadow: var(--shadow);
            padding: 20px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 18px;
        }

        .centre-info { display: flex; align-items: center; gap: 15px; min-width: 0; }

        .centre-symbol {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            background: var(--green-soft);
            color: var(--green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
            flex: 0 0 50px;
        }

        .small-label { color: var(--muted); font-size: 12px; margin-bottom: 4px; }

        .centre-name {
            font-size: 18px;
            font-weight: 800;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .centre-meta { margin-top: 5px; color: var(--muted); font-size: 13px; }

        .switch-centre {
            text-decoration: none;
            color: var(--green);
            border: 1px solid #b9d8c8;
            background: #fff;
            padding: 11px 16px;
            border-radius: 9px;
            font-weight: 700;
            white-space: nowrap;
        }

        .switch-centre:hover { background: var(--green-soft); }

        /* ---------- RATES ---------- */
        .rate-card {
            background: var(--green-soft);
            border: 1px solid var(--green-border);
            border-radius: 17px;
            padding: 18px 22px;
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 25px;
        }

        .rate-title { font-size: 15px; font-weight: 800; margin-bottom: 12px; }

        .rates { display: flex; flex-wrap: wrap; gap: 9px; }

        .rate-pill {
            background: rgba(255,255,255,.82);
            border: 1px solid #d3e8dc;
            border-radius: 9px;
            padding: 9px 12px;
            font-size: 13px;
        }

        .rate-pill strong { color: var(--green); }

        .summary { display: flex; gap: 10px; }

        .summary-box {
            min-width: 132px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 11px 14px;
        }

        .summary-label { color: var(--muted); font-size: 11px; margin-bottom: 5px; }
        .summary-value { font-weight: 800; font-size: 17px; }

        /* ---------- FILTERS ---------- */
        .filter-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 17px;
            padding: 15px;
            box-shadow: var(--shadow);
            display: grid;
            grid-template-columns: minmax(280px, 1fr) 165px 165px 82px;
            gap: 10px;
            margin-bottom: 18px;
        }

        .filter-input, .filter-select, .reset-btn {
            min-height: 43px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: #fff;
            color: var(--text);
            font-size: 13px;
        }

        .filter-input, .filter-select { width: 100%; padding: 10px 12px; }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(8,116,67,.08);
        }

        .reset-btn { padding: 0 13px; cursor: pointer; font-weight: 700; color: var(--muted); }
        .reset-btn:hover { background: #f5f8f6; }

        /* ---------- TABLE ---------- */
        .table-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 17px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .table-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .table-title { font-weight: 800; }
        .table-count { color: var(--muted); font-size: 12px; }

        .table-scroll { overflow-x: auto; }

        table {
            width: 100%;
            min-width: 1420px;
            border-collapse: collapse;
        }

        th {
            background: #f7faf8;
            color: var(--text);
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            font-size: 12px;
            white-space: nowrap;
        }

        td {
            padding: 15px 12px;
            border-bottom: 1px solid #edf2ef;
            vertical-align: middle;
            color: var(--muted);
            font-size: 13px;
        }

        tbody tr:hover { background: #fbfdfc; }
        tbody tr:last-child td { border-bottom: 0; }

        .token {
            color: var(--green);
            font-weight: 800;
            line-height: 1.35;
            max-width: 145px;
            word-break: break-word;
        }

        .copy-btn {
            border: 0;
            background: transparent;
            color: var(--green);
            cursor: pointer;
            font-size: 12px;
            padding: 2px;
            margin-left: 3px;
        }

        .farmer { color: var(--text); font-weight: 700; }
        .mobile { white-space: nowrap; }
        .centre-cell { color: var(--text); font-weight: 700; max-width: 175px; }
        .schedule { white-space: nowrap; line-height: 1.55; }
        .schedule-date { color: var(--text); font-weight: 700; }

        .crop-select, .quantity-input {
            height: 40px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            color: var(--text);
            font-size: 13px;
        }

        .crop-select { width: 112px; padding: 0 9px; }
        .quantity-input { width: 105px; padding: 0 9px; }

        .crop-select:focus, .quantity-input:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(8,116,67,.08);
        }

        .quantity-wrap { white-space: nowrap; }
        .unit { margin-left: 4px; font-size: 11px; color: var(--muted); }
        .rate-value, .payment-value { white-space: nowrap; font-weight: 800; color: var(--text); }
        .payment-value { color: var(--green); }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 7px 10px;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .status::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .status-pending { color: #ad6800; background: #fff5df; }
        .status-arrived { color: #2878b5; background: #eaf4ff; }
        .status-weighed { color: #7048a8; background: #f1ebff; }
        .status-accepted { color: #16834d; background: #e8f5ee; }
        .status-cancelled { color: #b23b3b; background: #fff0f0; }
        .status-default { color: #65736d; background: #eef2f0; }

        .action-cell { min-width: 190px; }
        .action-form { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

        .action-btn {
            border: 0;
            background: var(--green);
            color: #fff;
            border-radius: 8px;
            min-height: 40px;
            padding: 0 13px;
            cursor: pointer;
            font-weight: 800;
            font-size: 12px;
            white-space: nowrap;
        }

        .action-btn:hover { background: var(--green-dark); }

        .completed {
            display: inline-flex;
            align-items: center;
            min-height: 40px;
            border: 1px solid #c9e5d5;
            background: var(--green-soft);
            color: var(--green);
            border-radius: 8px;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .action-help { color: var(--muted); font-size: 11px; width: 100%; }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 14px 20px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: 12px;
        }

        .empty {
            padding: 55px 25px;
            text-align: center;
        }

        .empty-symbol {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: var(--green-soft);
            color: var(--green);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 13px;
            font-size: 21px;
        }

        .empty h3 { margin: 0 0 7px; }
        .empty p { margin: 0; color: var(--muted); }

        @media (max-width: 1100px) {
            .admin-layout { grid-template-columns: 78px minmax(0, 1fr); }
            .sidebar-label { display: none; }
            .nav-item { justify-content: center; padding: 0; }
            .nav-item.active { padding-left: 0; border-left: 0; border-bottom: 3px solid var(--green); }
            .nav-item span:last-child { display: none; }
            .main { padding: 28px 25px 50px; }
            .filter-card { grid-template-columns: 1fr 1fr; }
            .filter-input { grid-column: 1 / -1; }
            .rate-card { align-items: flex-start; flex-direction: column; }
        }

        @media (max-width: 700px) {
            .admin-header { height: 70px; padding: 0 15px; }
            .brand-logo { width: 40px; height: 40px; flex-basis: 40px; }
            .brand-name { font-size: 19px; }
            .today-badge, .admin-user { display: none; }
            .admin-layout { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .main { padding: 23px 13px 40px; }
            .page-heading h1 { font-size: 28px; }
            .centre-card { align-items: flex-start; flex-direction: column; }
            .centre-name { white-space: normal; }
            .switch-centre { width: 100%; text-align: center; }
            .summary { width: 100%; flex-direction: column; }
            .summary-box { width: 100%; }
            .filter-card { grid-template-columns: 1fr; }
            .filter-input { grid-column: auto; }
            .table-top, .table-footer { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>

<body>
<div class="admin-shell">

    <header class="admin-header">
        <a href="dashboard.php" class="brand">
            <span class="brand-logo">
                <!-- Same KrishiSetu leaf symbol used by the farmer dashboard -->
                <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M39 7C25 8 13 14 10 26c-2 8 3 13 10 12 11-1 17-12 19-31Z" fill="#087443"/>
                    <path d="M10 39c7-9 14-15 24-20" fill="none" stroke="#055c35" stroke-width="3" stroke-linecap="round"/>
                    <path d="M22 25c-1-5 0-10 4-14" fill="none" stroke="#f2b84b" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </span>
            <span>
                <div class="brand-name">KrishiSetu</div>
                <div class="brand-subtitle">Admin Portal</div>
            </span>
        </a>

        <div class="header-right">
            <div class="today-badge">▣ <strong>Today</strong> <?= date("d M Y") ?></div>
            <div class="admin-user">Administrator</div>
            <a href="../login.php" class="logout">Logout</a>
        </div>
    </header>

    <div class="admin-layout">

        <aside class="sidebar">
            <div class="sidebar-label">ADMIN</div>

            <nav class="nav-list">
                <a href="dashboard.php" class="nav-item">
                    <span class="nav-symbol">⌂</span><span>Dashboard</span>
                </a>
                <a href="centres.php" class="nav-item">
                    <span class="nav-symbol">▣</span><span>Centre Management</span>
                </a>
                <a href="procurement.php" class="nav-item active">
                    <span class="nav-symbol">▤</span><span>Procurement</span>
                </a>
                <a href="slots.php" class="nav-item">
                    <span class="nav-symbol">▦</span><span>Slots</span>
                </a>
                <a href="payment.php" class="nav-item">
                    <span class="nav-symbol">₹</span><span>Payments</span>
                </a>
                <a href="farmers.php" class="nav-item">
                    <span class="nav-symbol">♟</span><span>Farmers</span>
                </a>
            </nav>

            <hr class="sidebar-divider">

            <a href="../login.php" class="nav-item">
                <span class="nav-symbol">↪</span><span>Logout</span>
            </a>
        </aside>

        <main class="main">
            <div class="content-width">

                <a href="dashboard.php" class="back-link">← Back to Admin Dashboard</a>

                <div class="page-heading">
                    <h1>Procurement Management</h1>
                    <p>Record crop details, calculate procurement payment and update farmer procurement status.</p>
                </div>

                <section class="centre-card">
                    <div class="centre-info">
                        <div class="centre-symbol">▣</div>
                        <div>
                            <div class="small-label">Working Centre</div>
                            <div class="centre-name"><?= e($selectedCentre["centre_name"]) ?></div>
                            <div class="centre-meta">
                                <?= e($selectedCentre["block"]) ?>, <?= e($selectedCentre["district"]) ?> · <?= e($selectedCentre["centre_code"]) ?>
                            </div>
                        </div>
                    </div>
                    <a href="dashboard.php" class="switch-centre">Switch Centre</a>
                </section>

                <section class="rate-card">
                    <div>
                        <div class="rate-title">Demo Procurement Rates</div>
                        <div class="rates">
                            <div class="rate-pill">Paddy: <strong>₹45/kg</strong></div>
                            <div class="rate-pill">Wheat: <strong>₹30/kg</strong></div>
                            <div class="rate-pill">Maize: <strong>₹22/kg</strong></div>
                            <div class="rate-pill">Mustard: <strong>₹55/kg</strong></div>
                        </div>
                    </div>

                    <div class="summary">
                        <div class="summary-box">
                            <div class="summary-label">Today</div>
                            <div class="summary-value"><?= date("d M Y") ?></div>
                        </div>
                        <div class="summary-box">
                            <div class="summary-label">Total Records</div>
                            <div class="summary-value"><?= $totalRecords ?></div>
                        </div>
                    </div>
                </section>

                <section class="filter-card">
                    <input type="search" id="pmSearch" class="filter-input" placeholder="Search booking token, farmer or mobile..." oninput="filterProcurement()">

                    <select id="pmCropFilter" class="filter-select" onchange="filterProcurement()">
                        <option value="">All Crops</option>
                        <?php foreach ($crop_rates as $crop => $rate): ?>
                            <option value="<?= e(strtolower($crop)) ?>"><?= e($crop) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select id="pmStatusFilter" class="filter-select" onchange="filterProcurement()">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="arrived">Farmer Arrived</option>
                        <option value="weighed">Paddy Weighed</option>
                        <option value="accepted">Accepted</option>
                    </select>

                    <button type="button" class="reset-btn" onclick="resetProcurementFilters()">Reset</button>
                </section>

                <section class="table-card">
                    <?php if ($totalRecords > 0): ?>
                        <div class="table-top">
                            <div class="table-title">Procurement Records</div>
                            <div class="table-count" id="pmVisibleCount">Showing <?= $totalRecords ?> of <?= $totalRecords ?> records</div>
                        </div>

                        <div class="table-scroll">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Booking Token</th>
                                        <th>Farmer</th>
                                        <th>Mobile</th>
                                        <th>Centre</th>
                                        <th>Schedule</th>
                                        <th>Crop Type</th>
                                        <th>Quantity</th>
                                        <th>Rate</th>
                                        <th>Payment</th>
                                        <th>Current Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody id="pmTableBody">
                                <?php foreach ($records as $index => $row): ?>
                                    <?php
                                        $current_status = $row["procurement_status"] ?? "pending";
                                        $current_crop = !empty($row["crop_name"]) ? $row["crop_name"] : "Paddy";
                                        $current_quantity = !empty($row["quantity"]) ? (float)$row["quantity"] : 0;
                                        $current_rate = $crop_rates[$current_crop] ?? 0;
                                        $current_payment = $current_quantity * $current_rate;
                                    ?>

                                    <tr class="pm-record"
                                        data-search="<?= e(strtolower($row["booking_token"] . " " . $row["farmer_name"] . " " . $row["farmer_mobile"] . " " . $row["centre_name"])) ?>"
                                        data-crop="<?= e(strtolower($current_crop)) ?>"
                                        data-status="<?= e($current_status) ?>">

                                        <td><?= $index + 1 ?></td>

                                        <td>
                                            <div class="token">
                                                <?= e($row["booking_token"]) ?>
                                                <button type="button" class="copy-btn" title="Copy booking token" onclick="copyToken('<?= e($row["booking_token"]) ?>')">⧉</button>
                                            </div>
                                        </td>

                                        <td><div class="farmer"><?= e($row["farmer_name"]) ?></div></td>
                                        <td class="mobile"><?= e($row["farmer_mobile"]) ?></td>

                                        <td>
                                            <div class="centre-cell"><?= e($row["centre_name"]) ?></div>
                                        </td>

                                        <td>
                                            <div class="schedule">
                                                <div class="schedule-date"><?= date("d M Y", strtotime($row["slot_date"])) ?></div>
                                                <?= date("h:i A", strtotime($row["start_time"])) ?> - <?= date("h:i A", strtotime($row["end_time"])) ?>
                                            </div>
                                        </td>

                                        <td>
                                            <select name="crop_name" class="crop-select" form="procurementForm<?= (int)$row["booking_id"] ?>" onchange="calculatePayment(this)" required>
                                                <?php foreach ($crop_rates as $crop => $rate): ?>
                                                    <option value="<?= e($crop) ?>" data-rate="<?= $rate ?>" <?= $current_crop === $crop ? "selected" : "" ?>><?= e($crop) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>

                                        <td>
                                            <div class="quantity-wrap">
                                                <input type="number"
                                                       name="quantity"
                                                       class="quantity-input"
                                                       form="procurementForm<?= (int)$row["booking_id"] ?>"
                                                       value="<?= $current_quantity > 0 ? e($current_quantity) : "" ?>"
                                                       min="0.01"
                                                       step="0.01"
                                                       placeholder="Quantity"
                                                       data-booking-id="<?= (int)$row["booking_id"] ?>"
                                                       oninput="calculatePayment(this)"
                                                       <?= in_array($current_status, ["arrived", "weighed", "accepted"], true) ? "required" : "" ?>
                                                >
                                                <span class="unit">kg</span>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="rate-value">₹<span id="rate-<?= (int)$row["booking_id"] ?>"><?= number_format($current_rate, 2) ?></span>/kg</div>
                                        </td>

                                        <td>
                                            <div class="payment-value">₹<span id="payment-<?= (int)$row["booking_id"] ?>"><?= number_format($current_payment, 2) ?></span></div>
                                        </td>

                                        <td>
                                            <span class="status <?= e(statusClass($current_status)) ?>"><?= e(statusLabel($current_status)) ?></span>
                                        </td>

                                        <td class="action-cell">
                                            <form id="procurementForm<?= (int)$row["booking_id"] ?>" action="procurement_update.php" method="POST" class="action-form">
                                                <input type="hidden" name="booking_id" value="<?= (int)$row["booking_id"] ?>">

                                                <?php if ($current_status === "pending"): ?>
                                                    <button type="submit" name="status" value="arrived" class="action-btn">✓ Mark Farmer Arrived</button>

                                                <?php elseif ($current_status === "arrived"): ?>
                                                    <button type="submit" name="status" value="weighed" class="action-btn">⚖ Record Weight</button>
                                                    <div class="action-help">Enter quantity before recording weight.</div>

                                                <?php elseif ($current_status === "weighed"): ?>
                                                    <button type="submit" name="status" value="accepted" class="action-btn">✓ Accept Procurement</button>

                                                <?php elseif ($current_status === "accepted"): ?>
                                                    <span class="completed">✓ Procurement Completed</span>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div id="pmNoResults" class="empty" style="display:none;">
                            <div class="empty-symbol">⌕</div>
                            <h3>No matching records</h3>
                            <p>Try changing the search or filter.</p>
                        </div>

                        <div class="table-footer">
                            <span id="pmFooterText">Showing 1 to <?= $totalRecords ?> of <?= $totalRecords ?> records</span>
                            <span>All records are for the selected working centre.</span>
                        </div>

                    <?php else: ?>
                        <div class="empty">
                            <div class="empty-symbol">✓</div>
                            <h3>No procurement records found</h3>
                            <p>Farmer bookings for this centre will appear here.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>
</div>

<script>
function calculatePayment(element) {
    let bookingId = element.dataset.bookingId || "";

    if (!bookingId) {
        const formId = element.getAttribute("form");
        if (formId) {
            const match = formId.match(/(\d+)$/);
            if (match) bookingId = match[1];
        }
    }

    if (!bookingId) return;

    const cropSelect = document.querySelector('.crop-select[form="procurementForm' + bookingId + '"]');
    const quantityInput = document.querySelector('.quantity-input[form="procurementForm' + bookingId + '"]');
    const rateDisplay = document.getElementById("rate-" + bookingId);
    const paymentDisplay = document.getElementById("payment-" + bookingId);

    if (!cropSelect || !quantityInput || !rateDisplay || !paymentDisplay) return;

    const selectedOption = cropSelect.options[cropSelect.selectedIndex];
    const rate = parseFloat(selectedOption.dataset.rate) || 0;
    const quantity = parseFloat(quantityInput.value) || 0;
    const payment = quantity * rate;

    rateDisplay.textContent = rate.toFixed(2);
    paymentDisplay.textContent = payment.toLocaleString("en-IN", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function filterProcurement() {
    const search = document.getElementById("pmSearch").value.trim().toLowerCase();
    const crop = document.getElementById("pmCropFilter").value.toLowerCase();
    const status = document.getElementById("pmStatusFilter").value.toLowerCase();
    const rows = document.querySelectorAll(".pm-record");

    let visible = 0;

    rows.forEach(function(row) {
        const matchesSearch = search === "" || (row.dataset.search || "").includes(search);
        const matchesCrop = crop === "" || (row.dataset.crop || "") === crop;
        const matchesStatus = status === "" || (row.dataset.status || "") === status;
        const show = matchesSearch && matchesCrop && matchesStatus;

        row.style.display = show ? "" : "none";
        if (show) visible++;
    });

    const noResults = document.getElementById("pmNoResults");
    if (noResults) noResults.style.display = visible === 0 ? "block" : "none";

    const visibleCount = document.getElementById("pmVisibleCount");
    if (visibleCount) visibleCount.textContent = "Showing " + visible + " of <?= $totalRecords ?> records";

    const footer = document.getElementById("pmFooterText");
    if (footer) footer.textContent = visible === 0 ? "No records shown" : "Showing " + visible + " matching record(s)";
}

function resetProcurementFilters() {
    document.getElementById("pmSearch").value = "";
    document.getElementById("pmCropFilter").value = "";
    document.getElementById("pmStatusFilter").value = "";
    filterProcurement();
}

function copyToken(token) {
    if (!navigator.clipboard) {
        alert("Booking token: " + token);
        return;
    }

    navigator.clipboard.writeText(token).then(function() {
        alert("Booking token copied.");
    });
}
</script>

</body>
</html>
