<?php

include_once(__DIR__ . '/../control/controltourna.php');
$controller = new cTourna();
if (isset($_GET['id'])) {
    $idTourna = intval($_GET['id']);
    $result = $controller->deleteTourna($idTourna);
    if ($result) {
        echo "<script>alert('Xóa giải đấu thành công!'); window.location.href='dashboard.php?page=man_tourna';</script>";
    } else {
        echo "<script>alert('Xóa giải đấu thất bại!'); window.location.href=dashboard.php?page=man_tourna';</script>";
    }
} else {
    echo "<script>alert('ID giải đấu không hợp lệ!'); window.location.href='dashboard.php?page=man_tourna';</script>";
}
?>