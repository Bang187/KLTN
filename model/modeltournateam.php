<?php
include_once(__DIR__.'/modelconnect.php');

class mtournateam {
    public function listByTournament($idTourna){
        $p = new mConnect(); $con = $p->moKetNoi();
        if($con){
            $sql = "SELECT tt.id_tournateam, tt.reg_status, tt.reg_source,
               t.id_team, t.teamName, t.logo
        FROM tournament_team tt
        JOIN team t ON tt.id_team = t.id_team
        WHERE tt.id_tourna = ?
        ORDER BY t.teamName";
            $stm = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stm, "i", $idTourna);
            mysqli_stmt_execute($stm);
            $res = mysqli_stmt_get_result($stm); 
            mysqli_stmt_close($stm);
            $p->dongKetNoi($con);
            return $res;
        }
        return false;
    }

    public function register($idTourna, $idTeam){
        $p = new mConnect(); $con = $p->moKetNoi();
        if($con){
            $sql = "INSERT INTO tournament_team(id_tourna, id_team, reg_status, reg_source) VALUES(?, ?, 'pending','org')";
            $stm = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stm, "ii", $idTourna, $idTeam);
            $ok  = @mysqli_stmt_execute($stm); // tránh lỗi duplicate entry
            mysqli_stmt_close($stm);
            $p->dongKetNoi($con);
            return $ok;
        }
        return false;
    }

    public function updateStatus($idTournateam, $status){
        $allowed = ['pending','approved','rejected'];
        if(!in_array($status,$allowed,true)) return false;
        $p = new mConnect(); $con = $p->moKetNoi();
        if($con){
            $sql = "UPDATE tournament_team SET reg_status=? WHERE id_tournateam=?";
            $stm = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stm, "si", $status, $idTournateam);
            $ok  = mysqli_stmt_execute($stm);
            mysqli_stmt_close($stm);
            $p->dongKetNoi($con);
            return $ok;
        }
        return false;
    }
    public function approveRegistration(int $ttId, int $adminId, bool $approve = true): bool {
        $status = $approve ? 'approved' : 'rejected';
        $conn = (new mConnect())->moKetNoi();
        $sql  = "UPDATE tournament_team
                SET reg_status = ?, approved_by = ?, approved_at = NOW()
                WHERE id_tournateam = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sii', $status, $adminId, $ttId);
        $ok = $stmt->execute();
        $stmt->close(); $conn->close();
        return $ok;
    }
    // Phân hạt giống
public function setSeed(int $idTourna, int $idTeam, ?int $seed): bool {
    $p = new mConnect(); 
    $c = $p->moKetNoi(); 
    if (!$c) return false;

    $sql = "UPDATE tournament_team SET seed = ? WHERE id_tourna = ? AND id_team = ?";
    $st  = $c->prepare($sql);

    // MySQLi require kiểu ràng buộc → dùng i,i,i; nếu $seed null thì gán NULL bằng set_null sau:
    $seedParam = $seed === null ? null : (int)$seed;
    $st->bind_param('iii', $seedParam, $idTourna, $idTeam);

    $ok = $st->execute();
    $st->close(); 
    $c->close();
    return $ok;
}

}
