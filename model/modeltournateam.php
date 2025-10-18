<?php
include_once(__DIR__.'/modelconnect.php');

class mtournateam {
    public function listByTournament($idTourna){
        $p = new mconnect(); $con = $p->moketnoi();
        if($con){
            $sql = "SELECT tt.id_tournateam, tt.status, t.id_team, t.teamName, t.logo
                    FROM tournament_team tt
                    JOIN team t ON tt.id_team = t.id_team
                    WHERE tt.id_tourna = ?
                    ORDER BY t.teamName";
            $stm = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stm, "i", $idTourna);
            mysqli_stmt_execute($stm);
            $res = mysqli_stmt_get_result($stm); 
            mysqli_stmt_close($stm);
            $p->dongketnoi($con);
            return $res;
        }
        return false;
    }

    public function register($idTourna, $idTeam){
        $p = new mconnect(); $con = $p->moketnoi();
        if($con){
            $sql = "INSERT INTO tournament_team(id_tourna, id_team, status) VALUES(?, ?, 'pending')";
            $stm = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stm, "ii", $idTourna, $idTeam);
            $ok  = @mysqli_stmt_execute($stm); // tránh lỗi duplicate entry
            mysqli_stmt_close($stm);
            $p->dongketnoi($con);
            return $ok;
        }
        return false;
    }

    public function updateStatus($idTournateam, $status){
        $allowed = ['pending','approved','rejected'];
        if(!in_array($status,$allowed,true)) return false;
        $p = new mconnect(); $con = $p->moketnoi();
        if($con){
            $sql = "UPDATE tournament_team SET status=? WHERE id_tournateam=?";
            $stm = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stm, "si", $status, $idTournateam);
            $ok  = mysqli_stmt_execute($stm);
            mysqli_stmt_close($stm);
            $p->dongketnoi($con);
            return $ok;
        }
        return false;
    }
}
