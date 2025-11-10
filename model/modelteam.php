<?php
include_once("modelconnect.php");
class mteam{
    public function selectAllTeams(){
        $p = new mconnect();
        $con = $p->moketnoi();
        if($con){
            $query = "SELECT * FROM team";
            $result = $con->query($query);
            $p->dongketnoi($con);
            return $result;
        }else{
            return false;
        }
    }
    public function selectTeamByName($keyword){
        $p = new mConnect();
        $con = $p->moKetNoi();
        if($con){
            $query = "SELECT * FROM team where teamName LIKE '%$keyword%'";
            $result = $con->query($query);
            $p->dongketnoi($con);
            return $result;
        }else{
            return false;
        }
    }
    public function select01Team($id){
        $p = new mConnect();
        $conn = $p->moketnoi();
        if($conn){
            $query = "SELECT * FROM team where id_team ='$id'";
            $result = $conn->query($query);
            $p->dongketnoi($conn);
            return $result;
        }else{
            return false;
        }
    }
 public function selectTeamDetails($id){
        $p = new mconnect();
        $con = $p->moketnoi();
        if($con){
            $query = "
            SELECT 
                t.id_team, t.teamName, t.logo,
                u.FullName AS manager_name, u.email AS manager_email,
                tm.id_member, tm.roleInTeam, tm.joinTime,
                p.id_player, p.position, p.age, p.status, u2.FullName AS player_name, u2.avatar AS avatar
            FROM team t
            JOIN users u ON t.id_user = u.id_user
            LEFT JOIN team_member tm ON t.id_team = tm.id_team AND tm.status = 1
            LEFT JOIN player p ON tm.id_player = p.id_player
            LEFT JOIN users u2 ON p.id_user = u2.id_user
            WHERE t.id_team = '$id'";
            
            $result = $con->query($query);
            $p->dongketnoi($con);
            return $result;
        } else {
            return false;
        }
}
    public function getApprovedTeamsByTourna($idTourna){
    $p = new mConnect(); 
    $c = $p->moKetNoi(); 
    // $rows = [];

    // // Lấy các đội đã được duyệt cho giải $idTourna
    // $sql = "SELECT t.id_team, t.teamName
    //         FROM tournament_team tt
    //         JOIN team t ON t.id_team = tt.id_team
    //         WHERE tt.id_tourna = ? AND tt.reg_status = 'approved'
    //         ORDER BY t.teamName";

    // $stm = mysqli_prepare($c, $sql);
    // mysqli_stmt_bind_param($stm, "i", $idTourna);
    // mysqli_stmt_execute($stm);
    // $res = mysqli_stmt_get_result($stm);
    // while($r = $res->fetch_assoc()) $rows[] = $r;
    // mysqli_stmt_close($stm);
    // $p->dongketnoi($c);
    // return $rows;   
    if (!$c) return false;

    $sql = "SELECT t.id_team, t.teamName,tt.seed
            FROM tournament_team tt
            JOIN team t ON t.id_team = tt.id_team
            WHERE tt.id_tourna = ?
              AND tt.reg_status = 'approved'
            ORDER BY t.teamName";
    $stm = mysqli_prepare($c, $sql);
    mysqli_stmt_bind_param($stm, "i", $idTourna);
    mysqli_stmt_execute($stm);
    $res = mysqli_stmt_get_result($stm);
    mysqli_stmt_close($stm);
    $p->dongketnoi($c);               // hoặc dongKetNoi() theo dự án
    return $res;                      // <-- PHẢI trả về mysqli_result
}
    public function selectTeamByUser($id){
        $p = new mConnect();
        $conn = $p->moketnoi();
        if($conn){
            $query = "SELECT * FROM team where id_user ='$id'";
            $result = $conn->query($query);
            $p->dongketnoi($conn);
            return $result;
        }else{
            return false;
        }
    }
    public function delete01Team($id){
        $p = new mconnect();
        $conn = $p->moketnoi();
        if($conn){
            $query = "DELETE FROM team where id_team='$id'";
            $result = $conn->query($query);
            $p->dongketnoi($conn);
            return $result;
        }else{
            return false;
        }
    }
    public function insertTeam($teamName, $logoname, $id_user){
        $p = new mconnect();
        $conn = $p->moketnoi();
        if($conn){
            $query = "INSERT INTO team (teamName, logo, id_user) values ('$teamName', '$logoname', '$id_user')";
            $result = $conn->query($query);
            $p->dongketnoi($conn);
            return $result;
        }else{
            return false;
        }
    }
    public function uploadTeam($idteam, $tendoimoi, $logo, $id_user){
        $p = new mconnect();
        $conn = $p->moketnoi();
        if($conn){
            $query = "UPDATE `team` SET `teamName`='$tendoimoi',`logo`='$logo', id_user = '$id_user'  WHERE id_team='$idteam'";
            $result = $conn->query($query);
            $p->dongketnoi($conn);
            return $result;
        }else{
            return false;
        }
    }
}
?>