<?php
include_once(__DIR__ . '/../model/modelteammember.php');
include_once("controluploadteam.php");
class cteamMember{
    public function getAllTeamMember(){
        $p = new mteamMember();
        $tblTeam = $p->selectAllTeamMember();
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
    public function get01TeamMember($id){
        $p = new mteamMember();
        $tblTeam = $p->selectTeamMember($id);
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
    public function get01Member($id){
        $p = new mteamMember();
        $tblTeam = $p->select01eamMember($id);
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
    public function edit01TeamMember($id_member,$FullName, $position, $age, $phone, $status, $roleInTeam, $id_team, $id_player){
            $p = new mteamMember();
            $kq = $p->update01Member($id_member,$FullName, $position, $age, $phone, $status, $roleInTeam, $id_team, $id_player);
            return $kq;
        }
    public function close01Member($id){
            $p = new mteamMember();
            $tblTeam = $p->delete01TeamMember($id);
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
    
    public function getMemberByPhone($phone){
        $m = new mTeamMember();
        $tbl = $m->selectMemberByPhone($phone);
        if($tbl == false) return -2;
        return $tbl->num_rows > 0 ? $tbl : -1;
    }
    public function addMember($id_user, $id_team) {
        $p = new mteamMember();
        $result = $p->insertMember($id_user, $id_team);
        if ($result) {
            return 1;
        } else {
            return 0;
        }
    }
}
?>