<?php
include_once('modelconnect.php');
class mTourna {
    public function selectallTournament() {
        $query = "SELECT * FROM tournament ";
        $p = new mConnect();
        $conn = $p->moKetNoi();
        $result = mysqli_query($conn, $query);    
        $p->dongKetNoi($conn);
        if (!$result) {
            die("Query failed: " . mysqli_error($conn));
        }
        return $result;
    }
    public function selectTournamentByName($keyword){
        $p = new mConnect();
        $con = $p->moKetNoi();
        if($con){
            $query = "SELECT * FROM tournament where tournaName LIKE '%$keyword%'";
            $result = $con->query($query);
            $p->dongketnoi($con);
            return $result;
        }else{
            return false;
        }
    }
    public function selectByUser($idOrg) {
        $p = new mConnect();
        $conn = $p->moKetNoi();
        $sql  = "SELECT idtourna, TournaName, startdate, enddate, logo, banner
                 FROM tournament
                 WHERE id_org = ?
                 ORDER BY idtourna DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $idOrg);
        mysqli_stmt_execute($stmt);
        $rs   = mysqli_stmt_get_result($stmt);
        $rows = [];
        if ($rs) { while ($row = mysqli_fetch_assoc($rs)) $rows[] = $row; }
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $rows;
    }
    
    public function insertTourna($name, $idOrg, $startDate, $endDate, $logoPath, $bannerPath) {
        $p = new mConnect();
        $conn = $p->moKetNoi();
        $sql  = "INSERT INTO tournament (TournaName, id_org, startdate, enddate, logo, banner)
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sissss", $name, $idOrg, $startDate, $endDate, $logoPath, $bannerPath);
        $ok   = mysqli_stmt_execute($stmt);
        $newId = $ok ? mysqli_insert_id($conn) : false;
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $newId;
    }
    public function getDetail($id) {
        $p = new mConnect(); $conn = $p->moKetNoi();
        $row = null;
        if ($conn) {
            $sql = "SELECT t.idtourna, t.tournaName, t.startdate, t.enddate, t.logo, t.banner,
                           t.team_count, t.id_rule, t.id_local,
                           rs.ruletype, rs.rr_rounds, rs.pointwin, rs.pointdraw, rs.pointloss, rs.tiebreak_rule
                    FROM tournament t
                    LEFT JOIN rule rs ON t.id_rule = rs.id_rule
                    WHERE t.idtourna=?";
            $stm = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stm, "i", $id);
            mysqli_stmt_execute($stm);
            $res = mysqli_stmt_get_result($stm);
            $row = mysqli_fetch_assoc($res) ?: null;
            mysqli_stmt_close($stm);
            $p->dongKetNoi($conn);
        }
        return $row;
    }

    // cập nhật cấu hình giải
    public function updateConfig($id, $teamCount, $idRule, $idLocal) {
        $p = new mConnect(); $conn = $p->moKetNoi();
        $ok = false;
        if ($conn) {
            $sql = "UPDATE tournament SET team_count=?, id_rule=?, id_local=? WHERE idtourna=?";
            $stm = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stm, "iiii", $teamCount, $idRule, $idLocal, $id);
            $ok = mysqli_stmt_execute($stm);
            mysqli_stmt_close($stm);
            $p->dongKetNoi($conn);
        }
        return $ok;
    }
    // xóa
    public function deleteTourna($idTourna) {
        $p = new mConnect();
        $conn = $p->moKetNoi();
        $sql  = "DELETE FROM tournament WHERE idtourna = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $idTourna);
        $ok   = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $ok;
    }
    public function getById($id_tourna){
        $p = new mConnect();
        $con = $p->moKetNoi();
        if($con){
            $query = "SELECT * FROM tournament where idtourna = $id_tourna";
            $result = $con->query($query);
            $p->dongketnoi($con);
            if($result->num_rows>0){
                return $result->fetch_assoc();
            }else{
                return null;
            }
        }else{
            return null;
        }
    }
public function selectTournamentDetails(int $id) {
    $p = new mConnect();
    $conn = $p->moKetNoi();
    if (!$conn) return false;

    // Có join rule (nếu có), đặt alias theo đúng tên cột bạn đang dùng
    $sql = "SELECT 
                t.idtourna,
                t.tournaName,
                t.startdate,
                t.enddate,
                t.logo,
                t.banner,
                t.team_count,
                t.id_rule,
                t.id_local,
                rs.ruletype,
                rs.rr_rounds,
                rs.pointwin,
                rs.pointdraw,
                rs.pointloss,
                rs.tiebreak_rule
            FROM tournament t
            LEFT JOIN rule rs ON t.id_rule = rs.id_rule
            WHERE t.idtourna = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt); // Trả về mysqli_result để view duyệt/đọc
    mysqli_stmt_close($stmt);
    $p->dongKetNoi($conn);

    return $result; // có thể null/false nếu không tìm thấy
}

    

}
?>