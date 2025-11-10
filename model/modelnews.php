<?php
include_once("modelconnect.php");
class mnews{
    public function selectnews(){
        $p = new mConnect();
            $conn = $p->moketnoi();
            if($conn){
                $strquery = "select * from news";
                $tblnews = $conn->query($strquery);
                $p->dongketnoi($conn);
                return $tblnews;
            }else{
                return false;
            }
    }
    public function select01news($id){
        $p = new mConnect();
            $conn = $p->moketnoi();
            if($conn){
                $strquery = "select * from news where id_news='$id'";
                $tblnews = $conn->query($strquery);
                $p->dongketnoi($conn);
                return $tblnews;
            }else{
                return false;
            }
    }
    public function updatenews($id, $title, $content, $img_news){
        $p = new mConnect();
            $conn = $p->moketnoi();
            if($conn){
                $strquery = "UPDATE news 
                     SET title = '$title',
                     content = '$content',
                     img_news = '$img_news'
                     WHERE id_news = '$id'";
                $tblnews = $conn->query($strquery);
                $p->dongketnoi($conn);
                return $tblnews;
            }else{
                return false;
            }
    }
    public function deleteNews($id){
        $p = new mConnect();
            $conn = $p->moketnoi();
            if($conn){
                $strquery = "delete from news where id_news='$id'";
                $tblnews = $conn->query($strquery);
                $p->dongketnoi($conn);
                return $tblnews;
            }else{
                return false;
            }
    }
    public function insertNews($title, $content, $img_news){
        $p = new mConnect();
            $conn = $p->moketnoi();
            if($conn){
                $strquery = "INSERT INTO `news`(`title`, `content`, `img_news`) VALUES ('$title','$content','$img_news')";
                $tblnews = $conn->query($strquery);
                $p->dongketnoi($conn);
                return $tblnews;
            }else{
                return false;
            }
    }
    public function selectNewsByName($keyword){
        $p = new mConnect();
            $conn = $p->moketnoi();
            if($conn){
                $strquery = "select * from news where title LIKE'%$keyword%'";
                $tblnews = $conn->query($strquery);
                $p->dongketnoi($conn);
                return $tblnews;
            }else{
                return false;
            }
    }
}
?>