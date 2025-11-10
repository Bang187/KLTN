<?php
include_once(__DIR__ . "/../control/controljointeam.php");

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<script>alert('Thiếu mã yêu cầu!'); history.back();</script>";
    exit;
}

$c = new cJoinTeam();
$result = $c->cApproveRequest($id);

if ($result == 1) {
    echo "<script>alert('Đã duyệt yêu cầu thành công!');
          window.location.href='dashboard.php?page=man_team_requests';</script>";
} else {
    echo "<script>alert('Lỗi khi duyệt yêu cầu!'); history.back();</script>";
}
?>