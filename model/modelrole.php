<?php
include_once("modelconnect.php");
class mrole{
    public function selectAllRole(){
        $p = new mConnect();
        $con = $p->moKetNoi();
        if($con){
            $query = "SELECT * FROM role";
            $result = $con->query($query);
            $p->dongketnoi($con);
            return $result;
        }else{
            return false;
        }
    }
}
?>