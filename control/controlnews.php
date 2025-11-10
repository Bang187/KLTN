<?php
include_once("model/modelnews.php");
include_once("controluploadnews.php");
class cnews{
    public function getAllNews(){
            $p = new mnews();
            $tblSP = $p->selectnews();
            if($tblSP == false){
                return -2;
            }else{
                if($tblSP->num_rows > 0){
                    return $tblSP;
                }else{
                    return -1;
                }
            }
        }
    public function get01News($id){
            $p = new mnews();
            $tblSP = $p->select01news($id);
            if($tblSP == false){
                return -2;
            }else{
                if($tblSP->num_rows > 0){
                    return $tblSP;
                }else{
                    return -1;
                }
            }
        }
    public function editnews($id, $title, $content, $img, $img_news){
            if($img["tmp_name"]!=""){
                $pu = new clsUpload();
                $kq = $pu->uploadAnh($img, $title, $img_news);
                if(!$kq){
                    return false;
                }
            }
            $p = new mnews();
            $kq = $p->updatenews($id, $title, $content, $img_news);
            return $kq;
    }
    public function removeNews($id){
            $p = new mnews();
            return $p->deleteNews($id);
        }
    public function addNews($title, $content, $img_news){
        $file_img = "";
            if($img_news["tmp_name"]!=""){
                $pu = new clsUpload();
                $kq = $pu->uploadAnh($img_news, $title, $file_img);
                if(!$kq){
                    return false;
                }
            }
            $p = new mnews();
            $kq = $p->insertNews($title, $content, $file_img);
            return $kq;
        }
    public function getNewsByName($keyword){
            $p = new mnews();
            $tblnew = $p->selectNewsByName($keyword);
            if($tblnew == false){
                return -2;
            }else{
                if($tblnew->num_rows > 0){
                    return $tblnew;
                }else{
                    return -1;
                }
            }
        }
}
?>