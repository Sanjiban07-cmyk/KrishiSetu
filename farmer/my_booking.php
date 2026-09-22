<?php
session_start();
require_once "../config/database.php";
require_once "../config/language.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}


$sql = "
SELECT
    b.id AS booking_id,
    b.centre_id,
    b.slot_id,
    b.booking_token,
    b.status AS booking_status,
    pc.centre_name,
    pc.centre_code,
    pc.venue,
    s.slot_date,
    s.start_time,
    s.end_time
FROM bookings b
JOIN farmers f ON b.farmer_id = f.id
JOIN procurement_centres pc ON b.centre_id = pc.id
JOIN slots s ON b.slot_id = s.id
WHERE f.user_id = ?
ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="<?= e($currentLanguage) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars(t("my_booking")) ?> | KrishiSetu</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
*{box-sizing:border-box}
body{margin:0;background:#f7faf8;color:#172b24;font-family:Arial,Helvetica,sans-serif}
.booking-page{max-width:1100px;margin:0 auto;padding:28px 20px 60px}
.topbar{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-bottom:22px}
.back-link{color:#087443;text-decoration:none;font-weight:700;font-size:17px}
.top-actions{display:flex;gap:10px;align-items:center}.language-select{border:1px solid #d8e2dc;background:#fff;color:#26352f;border-radius:10px;padding:10px 12px;font-size:14px;font-weight:700;cursor:pointer}
.top-btn{border:1px solid #d8e2dc;background:#fff;color:#26352f;border-radius:12px;padding:11px 20px;font-size:15px;font-weight:700;cursor:pointer}
.page-title{font-size:34px;margin:10px 0 6px;color:#172b24}
.subtitle{margin:0 0 24px;color:#64736d}
.message{padding:14px 18px;border-radius:12px;margin-bottom:20px;font-weight:700}
.message.success{background:#e9f8ef;border:1px solid #b8dfc6;color:#087443}
.message.error{background:#fff0ef;border:1px solid #efb9b5;color:#b42318}
.booking-card{background:#fff;border:1px solid #dfe8e2;border-radius:18px;box-shadow:0 5px 20px rgba(20,60,40,.05);overflow:hidden;margin-bottom:24px}
.booking-head{padding:24px 28px 20px;border-bottom:1px solid #edf1ee;display:flex;align-items:center;justify-content:space-between;gap:20px}
.centre-title{display:flex;gap:16px;align-items:center}
.centre-icon{width:62px;height:62px;border-radius:14px;background:#e8f5ee;display:flex;align-items:center;justify-content:center;font-size:30px}
.centre-title h2{margin:0 0 6px;font-size:21px}
.centre-title p{margin:0 0 5px;color:#63746c}
.centre-code{font-size:14px;color:#50645b}
.token-box{background:#e8f5ee;border-radius:12px;padding:13px 17px;text-align:right;min-width:175px}
.token-label{display:block;color:#61716a;font-size:12px;margin-bottom:4px}
.token-value{color:#087443;font-weight:800;font-size:18px;letter-spacing:.5px}
.progress-wrap{padding:24px 28px 26px;background:linear-gradient(180deg,#f7fffa 0%,#fff 100%);border:1px solid #b8dfc6;margin:20px;border-radius:16px}
.progress-top{display:flex;justify-content:space-between;align-items:flex-start;gap:15px;margin-bottom:22px}
.progress-top h3{margin:0;color:#087443;font-size:24px}
.progress-top p{margin:6px 0 0;color:#5f7068}
.last-updated{text-align:right;color:#687871;font-size:13px}
.last-updated strong{display:block;color:#172b24;font-size:14px;margin-top:4px}
.flow{display:grid;grid-template-columns:repeat(4,1fr);gap:7px;overflow:hidden;padding:2px 0 4px}
.flow-step{position:relative;min-height:125px;background:#edf1ef;padding:15px 22px 13px 28px;clip-path:polygon(0 0,calc(100% - 24px) 0,100% 50%,calc(100% - 24px) 100%,0 100%,24px 50%);text-align:center;color:#5d6d66}
.flow-step:first-child{clip-path:polygon(0 0,calc(100% - 24px) 0,100% 50%,calc(100% - 24px) 100%,0 100%,0 50%,0 0)}
.flow-step.active{background:#dff5e8;color:#087443}
.flow-step.current{background:#087443;color:#fff}
.flow-icon{width:36px;height:36px;border-radius:50%;margin:0 auto 8px;background:#fff;color:#6d7b75;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:17px}
.flow-step.active .flow-icon,.flow-step.current .flow-icon{color:#087443}
.flow-step.current .flow-icon{background:#fff}
.flow-step strong{display:block;font-size:14px;line-height:1.25}
.flow-step span{display:block;font-size:12px;margin-top:5px;opacity:.9}
.flow-date{font-size:11px!important;margin-top:7px!important;font-weight:700}
.next-note{margin-top:20px;padding:14px 16px;background:#edf9f2;border:1px solid #cce9d7;border-radius:11px;color:#355248;font-size:14px}
.next-note strong{color:#087443}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;padding:0 28px 22px}
.detail{background:#f7f9f8;border-radius:12px;padding:17px 18px;min-height:76px}
.detail-label{font-weight:800;display:block;margin-bottom:7px;color:#24372f}
.detail-value{color:#61716a;font-size:15px}
.status{display:inline-block;padding:7px 13px;border-radius:20px;background:#e7f6ed;color:#087443;font-weight:800;text-transform:capitalize}
.status.cancelled{background:#fdecec;color:#b42318}
.important-note{margin:0 28px 22px;padding:16px 18px;background:#fff8e8;border:1px solid #f1d08b;border-radius:12px;color:#69552d}
.important-note strong{display:block;color:#996b00;margin-bottom:4px}
.booking-footer{border-top:1px solid #edf1ee;padding:20px 28px;display:flex;justify-content:space-between;gap:15px;align-items:center}
.cancel-btn{background:#fff;border:1.5px solid #e12d25;color:#d92720;padding:12px 24px;border-radius:10px;font-weight:800;font-size:15px;cursor:pointer}
.cancel-btn:hover{background:#fff2f1}
.back-bookings{display:inline-block;text-decoration:none;border:1.5px solid #087443;color:#087443;padding:12px 22px;border-radius:10px;font-weight:800}
.queue-card{margin:0 28px 22px;background:#e8f5ee;border:1px solid #b9dcc8;border-radius:12px;padding:17px}
.queue-card h3{margin:0 0 12px;color:#087443;font-size:16px}
.queue-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.queue-stats div{background:#fff;border-radius:9px;padding:11px;text-align:center}
.queue-stats strong{display:block;color:#087443;font-size:19px}
.queue-stats span{font-size:12px;color:#64736d}
.no-booking{background:#fff;border:1px solid #dfe8e2;border-radius:18px;padding:45px;text-align:center}
.no-booking h2{margin-top:0}.no-booking p{color:#61716a}
.btn{display:inline-block;margin-top:15px;background:#087443;color:#fff;padding:12px 20px;border-radius:9px;text-decoration:none;font-weight:700}
.help-card{background:#fff;border:1px solid #dfe8e2;border-radius:16px;padding:20px 24px;display:flex;justify-content:space-between;align-items:center;gap:20px}
.help-card h3{margin:0 0 5px}.help-card p{margin:0;color:#687871}
.help-btn{border:1px solid #9bb2a7;color:#087443;background:#fff;border-radius:10px;padding:11px 20px;text-decoration:none;font-weight:800;white-space:nowrap}
@media(max-width:760px){.booking-page{padding:18px 12px 40px}.topbar{align-items:flex-start}.top-actions{display:none}.page-title{font-size:28px}.booking-head{padding:18px;align-items:flex-start;flex-direction:column}.token-box{text-align:left}.progress-wrap{margin:12px;padding:17px}.progress-top{flex-direction:column}.last-updated{text-align:left}.flow{display:block}.flow-step,.flow-step:first-child{clip-path:none;min-height:auto;margin-bottom:6px;border-radius:10px;padding:14px}.flow-icon{display:inline-flex;margin:0 8px 0 0;vertical-align:middle}.flow-step strong,.flow-step span{display:inline}.flow-date{display:block!important}.info-grid{grid-template-columns:1fr;padding:0 18px 18px}.queue-card{margin:0 18px 18px}.important-note{margin:0 18px 18px}.booking-footer{padding:18px;flex-direction:column-reverse;align-items:stretch}.cancel-btn,.back-bookings{width:100%;text-align:center}.help-card{flex-direction:column;align-items:stretch}.help-btn{text-align:center}.queue-stats{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="booking-page">

<div class="topbar">
    <a href="dashboard.php" class="back-link">← <?= htmlspecialchars(t("my_booking")) ?></a>
    </div>

<h1 class="page-title"><?= htmlspecialchars(t("booking_details")) ?></h1>
<p class="subtitle"><?= htmlspecialchars(t("track_booking")) ?></p>

<?php if (isset($_SESSION['booking_message'])): ?>
    <div class="message <?= ($_SESSION['booking_message_type'] ?? 'success') === 'error' ? 'error' : 'success' ?>">
        <?= htmlspecialchars($_SESSION['booking_message']) ?>
    </div>
    <?php unset($_SESSION['booking_message'], $_SESSION['booking_message_type']); ?>
<?php endif; ?>

<?php if ($result->num_rows > 0): ?>
<?php while ($booking = $result->fetch_assoc()): ?>
<?php
$queueStmt = $conn->prepare("SELECT COUNT(*) AS farmers_ahead FROM bookings b LEFT JOIN procurement p ON p.booking_id=b.id WHERE b.centre_id=? AND b.slot_id=? AND b.created_at<(SELECT created_at FROM bookings WHERE id=?) AND b.status!='cancelled' AND (p.status IS NULL OR p.status!='accepted')");
$queueStmt->bind_param("iii",$booking['centre_id'],$booking['slot_id'],$booking['booking_id']);
$queueStmt->execute();
$queueData=$queueStmt->get_result()->fetch_assoc();
$farmersAhead=(int)($queueData['farmers_ahead']??0);
$queuePosition=$farmersAhead+1;
$estimatedWait=$farmersAhead*5;
$queueStmt->close();

$procurementStmt=$conn->prepare("SELECT status FROM procurement WHERE booking_id=? LIMIT 1");
$procurementStmt->bind_param("i",$booking['booking_id']);
$procurementStmt->execute();
$procurementData=$procurementStmt->get_result()->fetch_assoc();
$procurementStatus=$procurementData['status']??'pending';
$procurementStmt->close();

$stage=1;
if($procurementStatus==='arrived') $stage=2;
if($procurementStatus==='weighed') $stage=3;
if($procurementStatus==='accepted') $stage=4;
if($booking['booking_status']==='cancelled') $stage=0;
$canCancel=$booking['booking_status']!=='cancelled'
    && !in_array($booking['booking_status'],['completed','accepted'],true)
    && !in_array($procurementStatus,['arrived','weighed','accepted'],true);
?>

<div class="booking-card">
    <div class="booking-head">
        <div class="centre-title">
            <div class="centre-icon">🏪</div>
            <div>
                <h2><?= htmlspecialchars($booking['centre_name']) ?></h2>
                <p><?= htmlspecialchars($booking['venue']) ?></p>
                <div class="centre-code"><?= htmlspecialchars(t("centre_code")) ?>: <strong><?= htmlspecialchars($booking['centre_code']) ?></strong></div>
            </div>
        </div>
        <div class="token-box">
            <span class="token-label"><?= htmlspecialchars(t("booking_token")) ?></span>
            <span class="token-value"><?= htmlspecialchars($booking['booking_token']) ?></span>
        </div>
    </div>

    <div class="progress-wrap">
        <div class="progress-top">
            <div>
                <h3><?= htmlspecialchars($stage===0 ? t('booking_cancelled') : ($stage===1 ? t('booking_confirmed') : ($stage===2 ? t('farmer_arrived') : ($stage===3 ? t('paddy_weighed') : t('procurement_accepted'))))) ?></h3>
                <p><?= htmlspecialchars($stage===0 ? t('booking_cancelled_desc') : ($stage===1 ? t('booking_confirmed_desc') : ($stage===2 ? t('farmer_arrived_desc') : ($stage===3 ? t('paddy_weighed_desc') : t('procurement_accepted_desc'))))) ?></p>
            </div>
            <div class="last-updated"><?= htmlspecialchars(t("booking_date")) ?><strong><?= date('d M Y',strtotime($booking['slot_date'])) ?></strong></div>
        </div>

        <div class="flow">
            <?php
            $steps=[
                [1,'✓',t('booking_confirmed'),t('booking_confirmed_desc')],
                [2,'🚜',t('farmer_arrived'),t('farmer_arrived_desc')],
                [3,'⚖',t('paddy_weighed'),t('paddy_weighed_desc')],
                [4,'▣',t('procurement_accepted'),t('procurement_accepted_desc')]
            ];
            foreach($steps as $st):
                $isActive=$stage>=$st[0] && $stage!==0;
                $isCurrent=$stage===$st[0];
            ?>
            <div class="flow-step <?= $isCurrent?'current ':'' ?><?= $isActive?'active':'' ?>">
                <div class="flow-icon"><?= $isActive?'✓':$st[1] ?></div>
                <strong><?= $st[2] ?></strong>
                <span><?= $st[3] ?></span>
                <?php if($isCurrent): ?><span class="flow-date"><?= $stage===1 ? htmlspecialchars(t('booking_confirmed_desc')) : htmlspecialchars(t('booking_confirmed_desc')) ?></span><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if($stage===0): ?>
            <div class="next-note"><strong><?= htmlspecialchars(t("cancelled_released")) ?></strong> <?= htmlspecialchars(t("slot_released")) ?></div>
        <?php elseif($stage<4): ?>
            <div class="next-note"><strong><?= htmlspecialchars(t("whats_next")) ?></strong> <?= htmlspecialchars($stage===1 ? t("next_booked") : ($stage===2 ? t("next_arrived") : t("next_weighed"))) ?></div>
        <?php else: ?>
            <div class="next-note"><strong><?= htmlspecialchars(t("completed")) ?></strong> <?= htmlspecialchars(t("completed_desc")) ?></div>
        <?php endif; ?>
    </div>

    <?php if($booking['booking_status']!=='cancelled' && $stage<4): ?>
    <div class="queue-card">
        <h3>🕐 <?= htmlspecialchars(t("live_queue")) ?></h3>
        <div class="queue-stats">
            <div><strong>#<?= $queuePosition ?></strong><span><?= htmlspecialchars(t("queue_position")) ?></span></div>
            <div><strong><?= $farmersAhead ?></strong><span><?= htmlspecialchars(t("farmers_ahead")) ?></span></div>
            <div><strong>~<?= $estimatedWait ?> min</strong><span><?= htmlspecialchars(t("estimated_wait")) ?></span></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="info-grid">
        <div class="detail"><span class="detail-label">📅 <?= htmlspecialchars(t("date")) ?></span><span class="detail-value"><?= date('d M Y',strtotime($booking['slot_date'])) ?></span></div>
        <div class="detail"><span class="detail-label">▦ <?= htmlspecialchars(t("centre_code")) ?></span><span class="detail-value"><?= htmlspecialchars($booking['centre_code']) ?></span></div>
        <div class="detail"><span class="detail-label">◷ <?= htmlspecialchars(t("time")) ?></span><span class="detail-value"><?= date('h:i A',strtotime($booking['start_time'])) ?> - <?= date('h:i A',strtotime($booking['end_time'])) ?></span></div>
        <div class="detail"><span class="detail-label"># <?= htmlspecialchars(t("booking_token")) ?></span><span class="detail-value"><strong style="color:#087443"><?= htmlspecialchars($booking['booking_token']) ?></strong></span></div>
        <div class="detail"><span class="detail-label">📍 <?= htmlspecialchars(t("venue")) ?></span><span class="detail-value"><?= htmlspecialchars($booking['venue']) ?></span></div>
        <div class="detail"><span class="detail-label">🔖 <?= htmlspecialchars(t("status")) ?></span><span class="<?= $booking['booking_status']==='cancelled'?'status cancelled':'status' ?>"><?= htmlspecialchars(translateStatus($booking['booking_status'])) ?></span></div>
    </div>

    <?php if($canCancel): ?>
    <div class="important-note">
        <strong>⚠ <?= htmlspecialchars(t("important_note")) ?></strong>
        <?= htmlspecialchars(t("cancel_rule")) ?>
    </div>
    <?php endif; ?>

    <div class="booking-footer">
        <?php if($canCancel): ?>
        <form method="POST" onsubmit="return confirm('<?= addslashes(t('cancel_confirm')) ?>');">
            <input type="hidden" name="cancel_booking_id" value="<?= (int)$booking['booking_id'] ?>">
            <button type="submit" class="cancel-btn">✕ &nbsp; <?= htmlspecialchars(t("cancel_booking")) ?></button>
        </form>
        <?php else: ?><div></div><?php endif; ?>
        <a href="my_booking.php" class="back-bookings">← &nbsp; <?= htmlspecialchars(t("back_bookings")) ?></a>
    </div>
</div>

<?php endwhile; ?>
<?php else: ?>
<div class="no-booking"><h2><?= htmlspecialchars(t("no_booking")) ?></h2><p><?= htmlspecialchars(t("no_booking_desc")) ?></p><a href="centres.php" class="btn"><?= htmlspecialchars(t("find_centre")) ?></a></div>
<?php endif; ?>

<div class="help-card">
    <div><h3><?= htmlspecialchars(t("need_help")) ?></h3><p><?= htmlspecialchars(t("support_desc")) ?></p></div>
    <a href="tel:+919999999999" class="help-btn">☎ <?= htmlspecialchars(t("call_support")) ?></a>
</div>

</div>
<script>
function shareBooking(){
    if(navigator.share){navigator.share({title:'KrishiSetu Booking',text:'My KrishiSetu procurement booking details.'}).catch(()=>{});}
    else{navigator.clipboard?.writeText(window.location.href);alert('<?= addslashes(t('booking_token')) ?>');}
}
</script>
</body>
</html>
