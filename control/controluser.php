<?php
    include_once("model/modeluser.php");
    include_once("controluploaduser.php");
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
    public function getUserByPhone($sdt){
        $p = new mUser();
        $tblUser = $p->selectUserByPhone($sdt);
        if($tblUser == false){
            return -2;
        }else{
            if($tblUser->num_rows>0){
                return $tblUser;
            }else{
                return -1;
            }
        }
    }
public function get01User($id){
        $p = new mUser();
        $tblUser = $p->select01User($id);
        if($tblUser == false){
            return -2;
        }else{
            if($tblUser->num_rows>0){
                return $tblUser;
            }else{
                return -1;
            }
        }
    }
    public function editUser($id, $username, $fullname, $email, $phone){
        $p = new mUser();
        $result = $p->updateUser($id, $username, $fullname, $email, $phone);
        return $result; // chỉ trả về true/false thôi
    }
    public function updatePassword($id, $newpass) {
        $p = new mUser();
        return $p->updatePassword($id, $newpass);
    }
public function getAllUser(){
        $p = new mUser();
        $tblUser = $p->selectAllUser();
        if($tblUser == false){
            return -2;
        }else{
            if($tblUser->num_rows>0){
                return $tblUser;
            }else{
                return -1;
            }
        }
    }
public function remove01user($id){
        $p = new mUser();
        $tblUser = $p->delete01User($id);
        if($tblUser == false){
            return -2;
        }else{
            if($tblUser->num_rows>0){
                return $tblUser;
            }else{
                return -1;
            }
        }
    }
public function manageEditUser($id, $username, $fullname, $email, $phone, $id_role, $fileavatar, $avatar){
    if($fileavatar["tmp_name"]!=""){
                    $pu = new clsUploadImg();
                    $kq = $pu->uploadAnh($fileavatar, $id, $avatar);
                    if(!$kq){
                        return false;
                    }
                }
            $p = new mUser();
            $kq = $p->manageUpdateUser($id, $username, $fullname, $email, $phone, $id_role, $avatar);
            return $kq;
        }
public function getUserByName($keyword){
        $p = new mUser();
        $tblUser = $p->selectUserByName($keyword);
        if($tblUser == false){
            return -2;
        }else{
            if($tblUser->num_rows>0){
                return $tblUser;
            }else{
                return -1;
            }
        }
    }
public function uploadImageAva($id, $fileavatar, $avatar){
    if($fileavatar["tmp_name"]!=""){
                    $pu = new clsUploadImg();
                    $kq = $pu->uploadAnh($fileavatar, $id, $avatar);
                    if(!$kq){
                        return false;
                    }
                }
                $p = new mUser();
                $kq = $p->updateImageAva($id, $avatar);
                return $kq;
        }
}
    ?>