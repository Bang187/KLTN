<?php
include_once("control/controlteammember.php");   
$p = new cteamMember();
$idmember = $_REQUEST["id"];

// Lấy thông tin member trước khi xóa để biết id_team
$memberInfo = $p->get01Member($idmember);
if($memberInfo === -1 || $memberInfo === -2){
    echo "<script>alert('Không tìm thấy thành viên!'); window.history.back();</script>";
    exit;
}
$member = $memberInfo->fetch_assoc();
$id_team = $member['id_team'];

// Thực hiện xóa
$deleted = $p->close01Member($idmember);

if($deleted){
    echo "<script>
        alert('Xóa thành công!');
        window.location.href='dashboard.php?page=dash_team_member&id=$id_team';
    </script>";
}else{
    echo "<script>alert('Xóa thất bại!'); window.history.back();</script>";
}
?>