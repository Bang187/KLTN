<?php
    class clsUploadImg{
        public function uploadAnh($hinhanh, $tenhinh, & $hinh){
            $size = $hinhanh["size"];
            $type = $hinhanh["type"];
            $temp = $hinhanh["tmp_name"];
            $name = $hinhanh["name"];
            if(!$this->chkSize($size)){
                return false;
            }
            if(!$this->chkType($type)){
                return false;
            }

            $folder = "img/doibong/";
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $hinh = date("Y-m-d-H-i-s") . "-" . uniqid() . "." . $ext;
            $des = $folder.$hinh;
            if(move_uploaded_file($temp, $des)){
                return true;
            }else{
                return false;
            }
        }
        public function chkSize($size){
            if($size<3*1024*1024){
                return true;
            }else{
                return false;
            }
        }
        public function chkType($type){
            $arr = array("image/jpeg", "image/jpg", "image/png", "image/webp");
            if(in_array($type, $arr)){
                return true;
            }else{
                return false;
            }
        }
    }
?>