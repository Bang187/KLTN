<?php
    include_once("control/controluser.php");
    $p = new cUser();
    $id = $_REQUEST["id"];
    $user = $p->remove01user($id);
    if($user){
        echo "<script>
                    alert('Xóa thành công!');
                    window.location.href = 'admin.php?page=manage_list_user';
                </script>";
    }else{
        echo "<script>alert('Xóa thất bại!')</script>";
    }
?>