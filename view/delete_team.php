<?php
    include_once("control/controlteam.php");
    $p = new cteam();
    $idteam = $_REQUEST["id"];
    $tblSP = $p->delete01Team($idteam);
    if($tblSP){
        echo "<script>alert('Xóa thành công!')</script>";
        header("refresh:0;url='dashboard.php?page=man_team'");
    }else{
        echo "<script>alert('Xóa thất bại!')</script>";
    }
?>