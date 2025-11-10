<?php
include_once("model/modelrole.php");
class crole{
    public function getAllRole(){
        $p = new mrole();
        $tblRole = $p->selectAllRole();
        if($tblRole == false){
            return -2;
        }else{
            if($tblRole->num_rows>0){
                return $tblRole;
            }else{
                return -1;
            }
        }
    }
}
?>