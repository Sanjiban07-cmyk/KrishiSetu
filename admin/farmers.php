<?php

session_start();

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Admin Access
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Fetch Registered Farmers
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        u.id,
        u.name,
        u.mobile,
        u.language,
        u.created_at,
        f.village,
        f.district,
        f.state
    FROM users u
    LEFT JOIN farmers f
        ON f.user_id = u.id
    WHERE u.role = 'farmer'
    ORDER BY u.created_at DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Farmer Records | KrishiSetu</title>

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
            padding: 24px 32px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-brand {
            color: var(--primary);
            font-size: 27px;
            font-weight: 700;
        }

        .admin-subtitle {
            color: var(--text-secondary);
            margin-top: 5px;
            font-size: 14px;
        }

        .logout-btn {
            text-decoration: none;
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 11px 20px;
            border-radius: var(--radius-md);
            font-weight: 600;
        }

        .logout-btn:hover {
            background: var(--primary);
            color: white;
        }

        .page-container {
            max-width: 1250px;
            margin: 0 auto;
            padding: 55px 25px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 28px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .page-title {
            font-size: 34px;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .page-description {
            color: var(--text-secondary);
            margin-bottom: 30px;
        }

        .farmer-count {
            display: inline-block;
            margin-bottom: 22px;
            padding: 9px 14px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }

        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th {
            text-align: left;
            padding: 17px 18px;
            background: #f7f9f7;
            color: var(--text-primary);
            font-size: 13px;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 18px;
            color: var(--text-secondary);
            font-size: 14px;
            border-bottom: 1px solid var(--border);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #fafcfb;
        }

        .farmer-name {
            color: var(--text-primary);
            font-weight: 700;
        }

        .farmer-id {
            display: block;
            margin-top: 4px;
            color: var(--text-light);
            font-size: 11px;
        }

        .mobile {
            color: var(--text-primary);
            font-weight: 600;
        }

        .language-badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 15px;
            background: var(--primary-light);
            color: var(--primary);
            font-size: 12px;
            font-weight: 600;
        }

        .empty-state {
            padding: 55px 25px;
            text-align: center;
            color: var(--text-secondary);
        }

        .prototype-note {
            margin-top: 25px;
            padding: 16px 18px;
            background: var(--accent-light);
            border: 1px solid #f0d69d;
            border-radius: var(--radius-md);
            color: #765615;
            font-size: 13px;
            line-height: 1.6;
        }

        @media (max-width: 600px) {

            .admin-header {
                padding: 20px;
            }

            .page-container {
                padding: 35px 15px;
            }

            .page-title {
                font-size: 28px;
            }

        }

    </style>

</head>


<body>

<div class="admin-page">


    <!-- Header -->

    <header class="admin-header">

        <div>

            <div class="admin-brand">
                KrishiSetu
            </div>

            <div class="admin-subtitle">
                Admin Portal
            </div>

        </div>

        <a href="../logout.php" class="logout-btn">
            Logout
        </a>

    </header>


    <!-- Main -->

    <main class="page-container">


        <a href="dashboard.php" class="back-link">
            ← Back to Admin Dashboard
        </a>


        <h1 class="page-title">
            Farmer Records
        </h1>

        <p class="page-description">
            View registered farmers and their basic profile information.
        </p>


        <?php if ($result && $result->num_rows > 0): ?>


            <div class="farmer-count">

                <?= $result->num_rows ?>

                Registered Farmer<?= $result->num_rows !== 1 ? "s" : "" ?>

            </div>


            <div class="table-card">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Farmer
                            </th>

                            <th>
                                Mobile
                            </th>

                            <th>
                                Village
                            </th>

                            <th>
                                District
                            </th>

                            <th>
                                State
                            </th>

                            <th>
                                Language
                            </th>

                            <th>
                                Registered
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php while ($farmer = $result->fetch_assoc()): ?>


                            <tr>


                                <td>

                                    <span class="farmer-name">

                                        <?= htmlspecialchars(
                                            $farmer["name"] ?: "—"
                                        ) ?>

                                    </span>

                                    <span class="farmer-id">

                                        Farmer ID:
                                        <?= (int)$farmer["id"] ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="mobile">

                                        <?= htmlspecialchars(
                                            $farmer["mobile"] ?: "—"
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $farmer["village"] ?: "—"
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $farmer["district"] ?: "—"
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $farmer["state"] ?: "—"
                                    ) ?>

                                </td>


                                <td>

                                    <span class="language-badge">

                                        <?= strtoupper(
                                            htmlspecialchars(
                                                $farmer["language"] ?: "EN"
                                            )
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <?php

                                    if (!empty($farmer["created_at"])) {

                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $farmer["created_at"]
                                            )
                                        );

                                    } else {

                                        echo "—";

                                    }

                                    ?>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <div class="table-card">

                <div class="empty-state">

                    No registered farmers found.

                </div>

            </div>


        <?php endif; ?>


        <div class="prototype-note">

            <strong>Privacy note:</strong>
            This admin view displays only the farmer information
            required for procurement management. Passwords are never
            displayed here.

        </div>


    </main>

</div>

</body>

</html>