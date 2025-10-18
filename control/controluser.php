<?php
    include_once("model/modeluser.php");
    class cUser{
        public function clogin ($id,$pwd){
            $p = new mUser();
            $res = $p -> mLogin($id,$pwd);
            if($res->num_rows >0 ){ 
                $row = $res->fetch_assoc(); 
                $_SESSION["login"] = true;
                $_SESSION['ID_role'] = $row['ID_role']; 
                $_SESSION['username'] = $row['username'];  
                $_SESSION['id_user']  = (int)($row['id_user'] ?? 0);     // id của bảng users 
                session_regenerate_id(true);
                if ($_SESSION['ID_role'] == 2) {            
                $_SESSION['id_org'] = $_SESSION['id_user'];      
    }           elseif ($_SESSION['ID_role'] == 3) {      
                $_SESSION['id_manateam'] = $_SESSION['id_user'];  
    }
       
            return true;
            
        }else{
            return false;
        }
    }
        public function cregister($email,$fullname,$username,$password) {
            $p = new mUser();
            $res = $p->mRegister($email,$fullname,$username,$password);
            return $res;
        }
}
    ?>