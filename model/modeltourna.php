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
                           t.team_count, t.id_rule, t.id_local,t.allow_online_reg, t.regis_open_at, t.regis_close_at,
                           rs.ruletype, rs.rr_rounds, rs.pointwin, rs.pointdraw, rs.pointloss, rs.tiebreak_rule,rs.hy_group_count, rs.hy_take_1st, rs.hy_take_2nd, rs.hy_take_3rd, rs.hy_take_4th
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
// Cập nhật cài đặt đăng ký giải trực tuyến
    public function updateRegistrationSettings($id, $allow, $open, $close){
    $conn = (new mConnect())->moKetNoi();
    $stmt = $conn->prepare("UPDATE tournament
                            SET allow_online_reg=?, regis_open_at=?, regis_close_at=?
                            WHERE idtourna=?");
    $stmt->bind_param('issi', $allow, $open, $close, $id);
    $ok = $stmt->execute();
    $stmt->close(); $conn->close();
    return $ok;
}
public function getTournamentById($id){
    $c=(new mConnect())->moKetNoi();
    $st=$c->prepare("SELECT * FROM tournament WHERE idtourna=?");
    $st->bind_param('i',$id); $st->execute();
    $res=$st->get_result()->fetch_assoc();
    $st->close(); $c->close(); return $res;
}
public function editTourna($id, $name) {
    // Chức năng chỉnh sửa thông tin giải đấu
    $c = (new mConnect())->moKetNoi();
    if (!$c) return false;
    $sql = "UPDATE tournament
            SET tournaName = ?
            WHERE idtourna = ?";
    $stm = mysqli_prepare($c, $sql);
    mysqli_stmt_bind_param($stm, "si", $name, $id);
    $ok = mysqli_stmt_execute($stm);
    mysqli_stmt_close($stm);
    $p->dongKetNoi($c);
    return $ok;
}

// public function isTeamRegistered($tournaId,$teamId){
//     $c=(new mConnect())->moKetNoi();
//     $st=$c->prepare("SELECT 1 FROM tournament_team WHERE tournament_id=? AND team_id=? LIMIT 1");
//     $st->bind_param('ii',$tournaId,$teamId); $st->execute();
//     $has=$st->get_result()->num_rows>0;
//     $st->close(); $c->close(); return $has;
// }
public function isTeamRegistered(int $tournaId, int $teamId): bool {
    $p = new mConnect(); $c = $p->moKetNoi(); if(!$c) return false;
    $st = $c->prepare("SELECT 1 FROM tournament_team
                       WHERE id_tourna=? AND id_team=? LIMIT 1");
    $st->bind_param('ii',$tournaId,$teamId);
    $st->execute();
    $has = $st->get_result()->num_rows>0;
    $st->close(); $c->close();
    return $has;
}
// Thêm bản ghi đăng ký đội vào giải

public function getTeamCount(int $idTourna): int {
    $p = new mConnect(); $c = $p->moKetNoi();
    if (!$c) return 0;

    $sql = "SELECT team_count FROM tournament WHERE idtourna = ?";
    $stm = mysqli_prepare($c, $sql);
    mysqli_stmt_bind_param($stm, "i", $idTourna);
    mysqli_stmt_execute($stm);
    $res = mysqli_stmt_get_result($stm);

    $val = 0;
    if ($res) {
        $row = $res->fetch_assoc();      // có thể là null
        if (is_array($row) && isset($row['team_count'])) {
            $val = (int)$row['team_count'];
        }
    }
    mysqli_stmt_close($stm);
    $p->dongKetNoi($c);
    return $val;
}
public function countRegisteredTeams(int $idTourna): int {
    $p = new mConnect();
    $c = $p->moKetNoi();
    if(!$c) return 0;

    $sql = "SELECT COUNT(*) AS c
            FROM tournament_team
            WHERE id_tourna = ?
              AND reg_status IN ('pending','approved')";
    $stm = mysqli_prepare($c, $sql);
    mysqli_stmt_bind_param($stm, "i", $idTourna);
    mysqli_stmt_execute($stm);
    $res = mysqli_stmt_get_result($stm);
    $row = $res ? $res->fetch_assoc() : ['c'=>0];

    mysqli_stmt_close($stm);
    $p->dongKetNoi($c);
    return (int)$row['c'];
}
// public function insertTournamentTeam($tournaId,$teamId,$src,$status){
//     $c=(new mConnect())->moKetNoi();
//     $st=$c->prepare("INSERT INTO tournament_team (tournament_id, team_id, reg_source, reg_status)
//                      VALUES (?,?,?,?)");
//     $st->bind_param('iiss',$tournaId,$teamId,$src,$status);
//     $ok=$st->execute();
//     $st->close(); $c->close(); return $ok;
// }
public function insertTournamentTeam(int $tournaId, int $teamId, string $src, string $status) {
    $p = new mConnect(); $c = $p->moKetNoi(); if(!$c) return false;
    $st = $c->prepare("INSERT INTO tournament_team
        (id_tourna, id_team, reg_source, reg_status, registered_at)
        VALUES (?,?,?,?, NOW())");
    $st->bind_param('iiss',$tournaId,$teamId,$src,$status);
    $ok = $st->execute();
    $st->close(); $c->close();
    return $ok;
}
// liệt kê đội user chưa đăng ký
public function listUserTeamsNotInTournament(int $userId, int $idTourna): array {
    $p = new mConnect(); $c = $p->moKetNoi(); if(!$c) return [];

    // phát hiện cột owner
    $ownerCol = 'id_user';
    $rs = $c->query("SHOW COLUMNS FROM team LIKE 'owner_id'");
    if ($rs && $rs->num_rows > 0) $ownerCol = 'owner_id';

    $nameCol = 'teamName';
    $rs = $c->query("SHOW COLUMNS FROM team LIKE 'team_name'");
    if ($rs && $rs->num_rows > 0) $nameCol = 'team_name';

    $sql = "SELECT tm.id_team, tm.{$nameCol} AS name, tm.logo
            FROM team tm
            WHERE tm.{$ownerCol} = ?
              AND NOT EXISTS (
                 SELECT 1 FROM tournament_team tt
                 WHERE tt.id_team = tm.id_team AND tt.id_tourna = ?
              )
            ORDER BY tm.{$nameCol}";
    $st = $c->prepare($sql);
    $st->bind_param('ii',$userId,$idTourna);
    $st->execute();
    $rs = $st->get_result();
    $rows=[]; while($r=$rs->fetch_assoc()) $rows[]=$r;
    $st->close(); $c->close();
    return $rows;
}

// Chi tiết giải gồm cả thông tin luật và địa điểm
public function selectTournamentFullDetails(int $id) {
    $conn = (new mConnect())->moKetNoi(); if(!$conn) return false;
    $sql = $sql = "SELECT 
    t.idtourna, t.tournaName, t.startdate, t.enddate, t.logo, t.banner,
    t.status, t.team_count, t.id_rule, t.id_local,
    t.regis_open_at, t.regis_close_at,                   -- cửa sổ đăng ký
    t.fee_type, t.fee_amount,                  -- lệ phí
    t.regulation_summary,                      -- tóm tắt điều lệ
    r.ruletype, r.rr_rounds, r.pointwin, r.pointdraw, r.pointloss, r.tiebreak_rule,
    lc.LocalName AS location, lc.Address AS address
FROM tournament t
LEFT JOIN rule r      ON r.id_rule   = t.id_rule
LEFT JOIN location lc ON lc.id_local = t.id_local
WHERE t.idtourna = ?";
;
    $st = $conn->prepare($sql);
    $st->bind_param('i',$id); $st->execute();
    $res = $st->get_result();
    $st->close(); $conn->close();
    return $res; // mysqli_result
}
// Lấy danh sách đội đã duyệt tham gia giải
public function selectApprovedTeams(int $idTourna) {
    $c = (new mConnect())->moKetNoi();
    if (!$c) return [];
    $sql = "SELECT tt.id_tournateam, tm.id_team, tm.teamName, tm.logo,tt.seed
            FROM tournament_team tt
            JOIN team tm ON tt.id_team = tm.id_team
            WHERE tt.id_tourna = ? AND tt.reg_status = 'approved'
            ORDER BY tm.teamName";
    $st = $c->prepare($sql);
    $st->bind_param('i', $idTourna);
    $st->execute();
    $rs = $st->get_result();
    $rows = [];
    while ($r = $rs->fetch_assoc()) $rows[] = $r;
    $st->close(); $c->close();
    return $rows;
}
// public function selectMatchesByTournament(int $idTourna, string $mode = 'all', int $limit = 10) {
//     $c = (new mConnect())->moKetNoi();
//     if (!$c) return [];

//     // kickoff_date trong DB của bạn đang lưu DATETIME; kickoff_time có thể bỏ trống => chỉ dùng kickoff_date
//     $whereStatus = "1=1";
//     if ($mode === 'upcoming') $whereStatus = "m.status = 'scheduled' AND m.kickoff_date >= NOW()";
//     if ($mode === 'played')   $whereStatus = "m.status = 'played'";

//     $sql = "SELECT 
//                 m.id_match, m.round_no, m.leg_no,
//                 m.kickoff_date, m.status,
//                 m.home_team_id, m.away_team_id,
//                 m.home_score, m.away_score,
//                 m.location_id, m.pitch_label,
//                 th.teamName AS home_name,
//                 ta.teamName AS away_name,
//                 l.LocalName AS local_name, l.Address AS local_addr
//             FROM `match` m
//             LEFT JOIN team th    ON m.home_team_id = th.id_team
//             LEFT JOIN team ta    ON m.away_team_id = ta.id_team
//             LEFT JOIN location l ON m.location_id = l.id_local
//             WHERE m.id_tourna = ? AND $whereStatus
//             ORDER BY m.kickoff_date ".($mode==='played' ? "DESC" : "ASC")."
//             LIMIT ?";
//     $st = $c->prepare($sql);
//     $st->bind_param('ii', $idTourna, $limit);
//     $st->execute();
//     $rs  = $st->get_result();
//     $out = [];
//     while ($r = $rs->fetch_assoc()) $out[] = $r;
//     $st->close(); $c->close();
//     return $out;
// }
public function selectMatches(int $idTourna, string $type, int $limit = 10){
    $c = (new mConnect())->moKetNoi(); if(!$c) return [];
    if ($type === 'upcoming') {
        $where = "m.status = 'scheduled' AND m.kickoff_date >= NOW()";
        $order = "ASC";
    } else {
        $where = "m.status = 'played'"; // đã diễn ra
        $order = "DESC";
    }

    $sql = "SELECT 
                m.id_match,
                m.round_no,
                m.kickoff_date,
                m.status,
                m.home_team_id, m.away_team_id,
                m.home_score,  m.away_score,
                m.location_id, m.pitch_label,
                th.teamName AS home_name,
                ta.teamName AS away_name,
                lc.LocalName AS local_name,
                lc.Address   AS local_addr
            FROM `match` m
            LEFT JOIN team     th ON th.id_team   = m.home_team_id
            LEFT JOIN team     ta ON ta.id_team   = m.away_team_id
            LEFT JOIN location lc ON lc.id_local  = m.location_id
            WHERE m.id_tourna = ? AND $where
            ORDER BY m.kickoff_date $order
            LIMIT ?";
    $st = $c->prepare($sql);
    $st->bind_param('ii',$idTourna,$limit);
    $st->execute();
    $rs = $st->get_result();
    $rows=[]; while($r=$rs->fetch_assoc()) $rows[]=$r;
    $st->close(); $c->close(); 
    return $rows;
}

// --- Thống kê nhanh
public function selectQuickStats(int $idTourna): array {
    // Luôn có giá trị trả về
    $agg = [
        'matches_total'    => 0,
        'matches_played'   => 0,
        'matches_upcoming' => 0,
        'goals_total'      => 0,
        'champion'         => null,
        'runner_up'        => null,
    ];
    $champ  = null;   // <- KHỞI TẠO TỪ ĐẦU
    $runner = null;

    $conn = (new mConnect())->moKetNoi();
    if (!$conn) return $agg;

    // helper: có cột không?
    $hasCol = function($table, $col) use ($conn): bool {
        $rs = $conn->query("SHOW COLUMNS FROM `{$table}` LIKE '{$col}'");
        return $rs && $rs->num_rows > 0;
    };

    try {
        // 1) Xác định bảng trận
        $matchTbl = null;
        foreach (['match','matches'] as $t) {
            $rs = $conn->query("SHOW TABLES LIKE '{$t}'");
            if ($rs && $rs->num_rows > 0) { $matchTbl = $t; break; }
        }
        if (!$matchTbl) return $agg;

        // 2) Cột liên quan
        $idCol = $hasCol($matchTbl,'id_tourna') ? 'id_tourna'
               : ($hasCol($matchTbl,'tournament_id') ? 'tournament_id' : null);
        if (!$idCol) return $agg;

        $timeCol = $hasCol($matchTbl,'kickoff_date') ? 'kickoff_date'
                 : ($hasCol($matchTbl,'match_time')   ? 'match_time'
                 : ($hasCol($matchTbl,'start_time')   ? 'start_time'
                 : ($hasCol($matchTbl,'kickoff_at')   ? 'kickoff_at' : null)));

        $wherePlayed   = "(m.status IN ('played','finished',2) OR (m.home_score IS NOT NULL AND m.away_score IS NOT NULL))";
        $whereUpcoming = $timeCol
            ? "(m.status IN ('scheduled','upcoming',0,1) AND m.`{$timeCol}` >= NOW())"
            : "(m.status IN ('scheduled','upcoming',0,1))";

        // 3) Tổng hợp
        $sql = "
          SELECT
            COUNT(*)                                                 AS matches_total,
            SUM(CASE WHEN {$wherePlayed} THEN 1 ELSE 0 END)         AS matches_played,
            SUM(CASE WHEN {$whereUpcoming} THEN 1 ELSE 0 END)       AS matches_upcoming,
            SUM(COALESCE(m.home_score,0)+COALESCE(m.away_score,0))  AS goals_total
          FROM `{$matchTbl}` m
          WHERE m.`{$idCol}` = ?";
        $st = $conn->prepare($sql);
        $st->bind_param('i',$idTourna);
        $st->execute();
        if ($row = $st->get_result()->fetch_assoc()) {
            $agg['matches_total']    = (int)$row['matches_total'];
            $agg['matches_played']   = (int)$row['matches_played'];
            $agg['matches_upcoming'] = (int)$row['matches_upcoming'];
            $agg['goals_total']      = (int)$row['goals_total'];
        }
        $st->close();

        // 4) Champion/Runner (nếu có cột thì lấy, không thì suy)
        $hasChampion = $hasCol('tournament','champion_id');
        $hasRunner   = $hasCol('tournament','runner_up_id');
        if ($hasChampion || $hasRunner) {
            $sel = "SELECT ".($hasChampion?'champion_id':'NULL')." AS champion_id, "
                         .($hasRunner?'runner_up_id':'NULL')." AS runner_up_id
                    FROM tournament WHERE idtourna=?";
            $st = $conn->prepare($sel);
            $st->bind_param('i',$idTourna); 
            $st->execute();
            $tr = $st->get_result()->fetch_assoc(); 
            $st->close();

            if ($tr && !empty($tr['champion_id'])) {
                $r = $conn->query("SELECT teamName FROM team WHERE id_team=".(int)$tr['champion_id'])->fetch_assoc();
                $champ = $r['teamName'] ?? null;
            }
            if ($tr && !empty($tr['runner_up_id'])) {
                $r = $conn->query("SELECT teamName FROM team WHERE id_team=".(int)$tr['runner_up_id'])->fetch_assoc();
                $runner = $r['teamName'] ?? null;
            }
        }

        if (!$champ || !$runner) {
            // Suy từ trận có round/time lớn nhất
            $roundCol = $hasCol($matchTbl,'round_no') ? 'round_no'
                      : ($hasCol($matchTbl,'stage_round') ? 'stage_round' : null);

            $orderParts = [];
            if ($roundCol) $orderParts[] = "`{$roundCol}` DESC";
            if ($timeCol)  $orderParts[] = "`{$timeCol}` DESC";
            if (!$roundCol && !$timeCol) $orderParts[] = "m.`{$idCol}` DESC";
            $orderTail = implode(', ', $orderParts);

            $sqlF = "SELECT home_team_id, away_team_id, home_score, away_score
                     FROM `{$matchTbl}` m
                     WHERE m.`{$idCol}`=?
                     ORDER BY {$orderTail}
                     LIMIT 1";
            $st = $conn->prepare($sqlF); 
            $st->bind_param('i',$idTourna);
            $st->execute(); 
            $f = $st->get_result()->fetch_assoc(); 
            $st->close();

            if ($f && $f['home_score'] !== null && $f['away_score'] !== null) {
                $hid  = (int)$f['home_team_id'];
                $aid  = (int)$f['away_team_id'];
                $rowH = $conn->query("SELECT teamName FROM team WHERE id_team={$hid}")->fetch_assoc();
                $rowA = $conn->query("SELECT teamName FROM team WHERE id_team={$aid}")->fetch_assoc();
                $hn = ($rowH && isset($rowH['teamName'])) ? $rowH['teamName'] : 'Đội A';
                $an = ($rowA && isset($rowA['teamName'])) ? $rowA['teamName'] : 'Đội B';

                if ((int)$f['home_score'] > (int)$f['away_score']) { 
                    $champ = $hn;  $runner = $an;
                } elseif ((int)$f['home_score'] < (int)$f['away_score']) { 
                    $champ = $an;  $runner = $hn;
                }
            }
        }
    } catch (\Throwable $e) {
        error_log('[selectQuickStats] '.$e->getMessage());
    } finally {
        if ($conn) $conn->close();
    }

    // Gắn vào kết quả và trả về
    $agg['champion']  = $champ;
    $agg['runner_up'] = $runner;
    return $agg;
}


public function selectBracketByRounds(int $id){
    $c = (new mConnect())->moKetNoi(); if(!$c) return [];

    // helper: kiểm tra cột
    $has = function(string $table, string $col) use ($c): bool {
        $rs = $c->query("SHOW COLUMNS FROM `{$table}` LIKE '{$col}'");
        return $rs && $rs->num_rows > 0;
    };

    // Bảng & cột của match
    $matchTbl = 'match'; // bạn đang dùng 'match'
    $idCol    = $has($matchTbl,'id_match')     ? 'id_match'     : ($has($matchTbl,'idmatch') ? 'idmatch' : 'id');
    $tourCol  = $has($matchTbl,'id_tourna')    ? 'id_tourna'    : ($has($matchTbl,'tournament_id') ? 'tournament_id' : null);
    $roundCol = $has($matchTbl,'round_no')     ? 'round_no'     : ($has($matchTbl,'stage_round')   ? 'stage_round'   : null);
    $homeCol  = 'home_team_id';
    $awayCol  = 'away_team_id';
    $hsCol    = $has($matchTbl,'home_score')   ? 'home_score'   : 'home_goals';
    $asCol    = $has($matchTbl,'away_score')   ? 'away_score'   : 'away_goals';

    if (!$tourCol || !$roundCol) { $c->close(); return []; } // thiếu cột bắt buộc

    // Bảng & cột của team
    $teamTbl      = 'team';
    $teamNameCol  = $has($teamTbl,'teamName') ? 'teamName' : ($has($teamTbl,'team_name') ? 'team_name' : 'name');

    $sql = "
        SELECT 
            m.`{$roundCol}`                  AS round_no,
            m.`{$idCol}`                     AS id_match,
            m.`{$homeCol}`                   AS home_team_id,
            m.`{$awayCol}`                   AS away_team_id,
            t1.`{$teamNameCol}`              AS home_name,
            t2.`{$teamNameCol}`              AS away_name,
            m.`{$hsCol}`                     AS home_score,
            m.`{$asCol}`                     AS away_score
        FROM `{$matchTbl}` m
        LEFT JOIN `{$teamTbl}` t1 ON t1.id_team = m.`{$homeCol}`
        LEFT JOIN `{$teamTbl}` t2 ON t2.id_team = m.`{$awayCol}`
        WHERE m.`{$tourCol}` = ?
        ORDER BY m.`{$roundCol}` ASC, m.`{$idCol}` ASC";
        
    $st = $c->prepare($sql);
    $st->bind_param('i', $id);
    $st->execute();
    $rs = $st->get_result();

    $rounds = [];
    while ($r = $rs->fetch_assoc()) {
        $rn = (int)($r['round_no'] ?? 0);
        if (!isset($rounds[$rn])) $rounds[$rn] = [];
        $rounds[$rn][] = $r;
    }
    $st->close(); $c->close();
    return $rounds; // mảng: round_no => list trận
}
// trả về giải có thể đk online
// Trả về danh sách giải đang mở đăng ký cho user (không trùng giải đã có đội của user đăng ký)
public function listOpenForUser(int $userId, int $limit = 50): array {
    $c = (new mConnect())->moKetNoi(); if(!$c) return [];

    // Kiểm tra cột tồn tại an toàn
    $has = fn($col)=> ($c->query("SHOW COLUMNS FROM tournament LIKE '{$col}'")->num_rows>0);

    $colAllow = $has('allow_online_reg') ? 'allow_online_reg' : null;   // có thể không tồn tại
    $colOpen  = $has('regis_open_at')    ? 'regis_open_at'    : null;
    $colClose = $has('regis_close_at')   ? 'regis_close_at'   : null;

    // điều kiện cửa sổ thời gian
    $cond = ['1=1'];
    if ($colAllow) $cond[] = "t.{$colAllow} = 1";
    if ($colOpen)  $cond[] = "(t.{$colOpen} IS NULL OR t.{$colOpen} <= NOW())";
    if ($colClose) $cond[] = "(t.{$colClose} IS NULL OR t.{$colClose} >= NOW())";
    $where = implode(' AND ', $cond);

    // cột owner của team
    $ownerCol = 'id_user';
    $rs = $c->query("SHOW COLUMNS FROM team LIKE 'owner_id'");
    if ($rs && $rs->num_rows > 0) $ownerCol = 'owner_id';

    // tên đội
    $nameCol = 'teamName';
    $rs = $c->query("SHOW COLUMNS FROM team LIKE 'team_name'");
    if ($rs && $rs->num_rows > 0) $nameCol = 'team_name';

    // Subquery đếm đã đăng ký
    // tournament_team: dùng id_tourna (bạn đang dùng cột này)
    $sql = "
      SELECT
        t.idtourna,
        t.TournaName  AS name,
        t.startdate, t.enddate,
        t.team_count,
        (SELECT COUNT(*) FROM tournament_team tt
          WHERE tt.id_tourna = t.idtourna
            AND tt.reg_status IN ('pending','approved')) AS reg_count
      FROM tournament t
      WHERE {$where}
        AND NOT EXISTS (
          SELECT 1
          FROM team tm
          JOIN tournament_team tt ON tt.id_team = tm.id_team AND tt.id_tourna = t.idtourna
          WHERE tm.{$ownerCol} = ?
                AND tt.reg_status IN ('pending','approved')
        )
      HAVING (team_count IS NULL OR reg_count < team_count)
      ORDER BY COALESCE(t.startdate, NOW()) ASC
      LIMIT ?";

    $st = $c->prepare($sql);
    $st->bind_param('ii', $userId, $limit);
    $st->execute();
    $rs = $st->get_result();

    $rows = [];
    while ($r = $rs->fetch_assoc()) $rows[] = $r;
    $st->close(); $c->close();
    return $rows;
}

// Kiểm tra user đã có đội đăng ký giải này chưa (pending/approved)
public function userHasTeamInTournament(int $userId, int $idTourna): bool {
    $c = (new mConnect())->moKetNoi(); if(!$c) return false;
    $ownerCol = 'id_user';
    $rs = $c->query("SHOW COLUMNS FROM team LIKE 'owner_id'");
    if ($rs && $rs->num_rows > 0) $ownerCol = 'owner_id';

    $sql = "SELECT 1
            FROM team tm
            JOIN tournament_team tt ON tt.id_team = tm.id_team
            WHERE tm.{$ownerCol} = ? AND tt.id_tourna = ?
              AND tt.reg_status IN ('pending','approved')
            LIMIT 1";
    $st=$c->prepare($sql);
    $st->bind_param('ii',$userId,$idTourna);
    $st->execute();
    $has = $st->get_result()->num_rows>0;
    $st->close(); $c->close(); return $has;
}
public function updateTournaImages($id, $logo, $banner) {
    $p = new mConnect(); $conn = $p->moKetNoi();
    $sql = "UPDATE tournament SET logo=?, banner=? WHERE idtourna=?";
    $stm = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stm, "ssi", $logo, $banner, $id);
    $ok = mysqli_stmt_execute($stm);
    mysqli_stmt_close($stm); $p->dongKetNoi($conn);
    return $ok;
}
// public function updateRegulationFields($id, $fee_type, $fee_amount, $summary, $reg_open, $reg_close) {
//     $p = new mConnect(); $conn = $p->moKetNoi();
//     $sql = "UPDATE tournament
//                SET fee_type=?, fee_amount=?, regulation_summary=?
//              WHERE idtourna=?";
//     $stm = mysqli_prepare($conn, $sql);
//     mysqli_stmt_bind_param($stm, "sisssi", $fee_type, $fee_amount, $summary, $reg_open, $reg_close, $id);
//     $ok = mysqli_stmt_execute($stm);
//     mysqli_stmt_close($stm); $p->dongKetNoi($conn);
//     return $ok;
// }
// modeltourna.php
public function updateRegulationFields(int $id, string $fee_type, $fee_amount, ?string $summary)
{
    $p = new mConnect();
    $conn = $p->moKetNoi();

    if ($fee_type === 'FREE' || $fee_amount === '' || $fee_amount === null) {
        $sql = "UPDATE tournament
                   SET fee_type = ?, fee_amount = NULL, regulation_summary = ?
                 WHERE idtourna = ?";
        $stm = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stm, "ssi", $fee_type, $summary, $id);
    } else {
        $fee_amount_num = (int)$fee_amount; // số tiền nguyên
        $sql = "UPDATE tournament
                   SET fee_type = ?, fee_amount = ?, regulation_summary = ?
                 WHERE idtourna = ?";
        $stm = mysqli_prepare($conn, $sql);
        // fee_type(s), fee_amount(i), summary(s), id(i)
        mysqli_stmt_bind_param($stm, "sisi", $fee_type, $fee_amount_num, $summary, $id);
    }

    $ok = mysqli_stmt_execute($stm);
    mysqli_stmt_close($stm);
    $p->dongKetNoi($conn);
    return $ok;
}


public function insertTournamentFile($id_tourna, $file_name, $file_path, $mime, $size, $version_no, $is_public, $uploaded_by) {
    $p = new mConnect(); $conn = $p->moKetNoi();
    $sql = "INSERT INTO tournament_file
               (id_tourna, file_name, file_path, mime_type, file_size, version_no, is_public, uploaded_by)
            VALUES (?,?,?,?,?,?,?,?)";
    $stm = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stm, "isssiiii", $id_tourna, $file_name, $file_path, $mime, $size, $version_no, $is_public, $uploaded_by);
    $ok = mysqli_stmt_execute($stm);
    mysqli_stmt_close($stm); $p->dongKetNoi($conn);
    return $ok;
}
// Lấy version tiếp theo cho 1 giải
private function nextRegVersion(mysqli $conn, int $id_tourna): int {
    $sql = "SELECT IFNULL(MAX(version_no),0)+1 FROM tournament_file WHERE id_tourna=?";
    $stm = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stm, "i", $id_tourna);
    mysqli_stmt_execute($stm);
    mysqli_stmt_bind_result($stm, $v);
    mysqli_stmt_fetch($stm);
    mysqli_stmt_close($stm);
    return (int)$v;
}

/** Chuẩn hoá tên gọi để controller cũ dùng được */
public function insertRegulationFile(
    int $id_tourna,
    string $file_name,
    string $file_path,
    string $mime_type,
    int $file_size,
    int $uploaded_by = null,
    int $is_public   = 1
) {
    $p = new mConnect(); 
    $conn = $p->moKetNoi();

    $ver = $this->nextRegVersion($conn, $id_tourna);

    // tái sử dụng hàm bạn đã có
    $ok = $this->insertTournamentFile(
        $id_tourna,
        $file_name,
        $file_path,
        $mime_type,
        $file_size,
        $ver,           // version_no
        $is_public,     // is_public
        $uploaded_by ?? 0
    );

    $p->dongKetNoi($conn);
    return $ok;
}

// Hàm đếm đội đã duyệt 
public function countApprovedTeams(int $idTourna): int {
    $p = new mConnect(); $c = $p->moKetNoi();
    $sql = "SELECT COUNT(*) AS c FROM tournament_team WHERE id_tourna=? AND reg_status='approved'";
    $stm = mysqli_prepare($c, $sql);
    mysqli_stmt_bind_param($stm, "i", $idTourna);
    mysqli_stmt_execute($stm);
    $res = mysqli_stmt_get_result($stm);
    $row = $res ? $res->fetch_assoc() : ['c'=>0];
    mysqli_stmt_close($stm); $p->dongKetNoi($c);
    return (int)$row['c'];
}
public function selectRegulationFiles(int $idTourna): array {
    $p = new mConnect(); $c = $p->moKetNoi();
    $sql = "SELECT id, file_name, file_path, mime_type, file_size, uploaded_at, version_no
            FROM tournament_file
            WHERE id_tourna=? AND is_public=1
            ORDER BY uploaded_at DESC";
    $st = $c->prepare($sql);
    $st->bind_param('i',$idTourna);
    $st->execute();
    $rs = $st->get_result();
    $rows=[]; while($r=$rs->fetch_assoc()) $rows[]=$r;
    $st->close(); $c->close();
    return $rows;
}
//
public function isTeamOwnedByUser(int $teamId, int $userId): bool {
    $p = new mConnect(); $c = $p->moKetNoi(); if(!$c) return false;

    // tự phát hiện cột owner
    $ownerCol = 'id_user';
    $rs = $c->query("SHOW COLUMNS FROM team LIKE 'owner_id'");
    if ($rs && $rs->num_rows > 0) $ownerCol = 'owner_id';

    $st = $c->prepare("SELECT 1 FROM team WHERE id_team=? AND {$ownerCol}=? LIMIT 1");
    $st->bind_param('ii',$teamId,$userId);
    $st->execute();
    $ok = $st->get_result()->num_rows>0;
    $st->close(); $c->close();
    return $ok;
}




}
?>