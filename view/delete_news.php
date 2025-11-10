<?php
    include_once("control/controlnews.php");
    $p = new cnews();
    $id = $_REQUEST["id"];
    $sp = $p->removeNews($id);
    if($sp){
        echo "<script>
                    alert('Xóa thành công!');
                    window.location.href = 'admin.php?page=manage_news';
                </script>";
    }else{
        echo "<script>alert('Xóa thất bại!')</script>";
    }
?>