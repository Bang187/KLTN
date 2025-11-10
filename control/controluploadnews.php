<?php
    class clsUpload{
        public function uploadAnh($hinhanh, $tenhinh, & $hinh){
            $size = $hinhanh["size"];
            $loai = $hinhanh["type"];
            $temp = $hinhanh["tmp_name"];
            $name = $hinhanh["name"];
            if(!$this->chkSize($size)){
                return false;
            }
            if(!$this->chkType($loai)){
                return false;
            }
            $folder = "img/news/";
            $dir = pathinfo($name)["dirname"];
            $ext = pathinfo($name)["extension"];
            $hinh = date("Y-m-d-H-i-s").$dir.$ext;
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
            $arrType = array("image/jpeg", "image/jpg", "image/png", "image/webp");
            if(in_array($type, $arrType)){
                return true;
            }else{
                return false;
            }
        }
    }
?>