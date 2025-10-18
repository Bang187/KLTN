<?php
include_once(__DIR__ . '/../model/modelteam.php');
include_once('controluploadteam.php');
class cteam{
    public function getAllTeams(){
        $p = new mteam();
        $tblTeam = $p->selectAllTeams();
        if($tblTeam == false){
            return -2;
        }else{
            if($tblTeam->num_rows>0){
                return $tblTeam;
            }else{
                return -1;
            }
        }
    }
    public function getTeamByName($keyword){
        $p = new mteam();
        $tblTeam = $p->selectTeamByName($keyword);
        if($tblTeam == false){
            return -2;
        }else{
            if($tblTeam->num_rows>0){
                return $tblTeam;
            }else{
                return -1;
            }
        }
    }
    public function get01Team($id){
        $p = new mteam();
        $tblTeam = $p->select01Team($id);
        if($tblTeam == false){
            return -2;
        }else{
            if($tblTeam->num_rows>0){
                return $tblTeam;
            }else{
                return -1;
            }
        }
    }
    public function getTeamDetails($id){
        $p = new mteam();
        $tblTeam = $p->selectTeamDetails($id);
        if($tblTeam == false){
            return -2;
        }else{
            if($tblTeam->num_rows>0){
                return $tblTeam;
            }else{
                return -1;
            }
        }
    }
    public function getRegisteredTeamsByTourna($idTourna){
        $mtt = new mtournateam();
        $res = $mtt->listByTournament((int)$idTourna);  // mysqli_result | false
        if($res === false) return -2;
        return ($res->num_rows > 0) ? $res : -1;
    }

    
    public function registerTeamToTournament($idTourna, $teamKeyword){
        $teamKeyword = trim($teamKeyword);
        if($teamKeyword === '') return ['ok'=>false,'msg'=>'Tên đội không được rỗng'];

        
        $res = $this->getTeamByName($teamKeyword);   // mysqli_result | -1 | -2
        if ($res === -2)       return ['ok'=>false,'msg'=>'Lỗi kết nối CSDL'];
        if ($res === -1)       return ['ok'=>false,'msg'=>'Không tìm thấy đội phù hợp'];

        // lấy kết quả đầu tiên
        $row = $res->fetch_assoc();
        
        $idTeam = isset($row['id_team']) ? (int)$row['id_team'] : 0;
        if ($idTeam <= 0) return ['ok'=>false,'msg'=>'Không lấy được ID đội'];

        $mtt = new mtournateam();
        $ok  = $mtt->register((int)$idTourna, $idTeam);
        return ['ok'=>$ok, 'msg'=>$ok ? 'Đã thêm vào danh sách chờ duyệt' : 'Đội đã tồn tại trong giải hoặc lỗi CSDL'];
    }

    
    public function updateRegisteredTeamStatus($idTournateam, $status){
        $mtt = new mtournateam();
        $ok  = $mtt->updateStatus((int)$idTournateam, $status);
        return ['ok'=>$ok, 'msg'=>$ok ? 'Cập nhật trạng thái thành công' : 'Cập nhật thất bại'];
    }
    public function getTeamByUser($id){
        $p = new mteam();
        $tblTeam = $p->selectTeamByUser($id);
        if($tblTeam == false){
            return -2;
        }else{
            if($tblTeam->num_rows>0){
                return $tblTeam;
            }else{
                return -1;
            }
        }
    }
    public function delete01Team($id){
            $p = new mteam();
            $tblTeam = $p->delete01Team($id);
            if($tblTeam == false){
                return -2;
            }else{
                if($tblTeam->num_rows>0){
                    return $tblTeam;
                }else{
                    return -1;
                }
            }
        }
    public function addTeam($teamName, $logo, $id_user){
        $logoname = "";
        if (!empty($logo["tmp_name"])) {
            $uploader = new clsUploadImg();
            if (!$uploader->uploadAnh($logo, $teamName, $logoname)) {
                return false; // upload thất bại
            }
        }
        $p = new mteam();
        return $p->insertTeam($teamName, $logoname, $id_user);
    }
    public function edit01Team($idteam, $tendoimoi, $logomoi, $logo, $id_user){
            if($logomoi["tmp_name"] != ""){
                $pu = new clsUploadImg();
                $kq = $pu->uploadAnh($logomoi, $tendoimoi, $logo);
                if(!$kq){
                    return false;
                }
            }
            $p = new mteam();
            $kq = $p->uploadTeam($idteam, $tendoimoi, $logo, $id_user);
            return $kq;
        }
}
?>