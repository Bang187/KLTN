<?php
include_once(__DIR__ . '/modelconnect.php');

class mRank {

    // 1) Tổng quan giải
    public function getOverviewByTournament($tournaId){
        $p = new mConnect();
        $c = method_exists($p,'moketnoi') ? $p->moketnoi() : $p->moKetNoi();
        if(!$c){ return null; }

        // Số đội
        $sqlTeams = "
            SELECT COUNT(DISTINCT t.team_id) AS num_teams FROM (
                SELECT home_team_id AS team_id FROM `match` WHERE id_tourna = ?
                UNION
                SELECT away_team_id AS team_id FROM `match` WHERE id_tourna = ?
            ) t
        ";
        $stmt = mysqli_prepare($c, $sqlTeams);
        mysqli_stmt_bind_param($stmt, "ii", $tournaId, $tournaId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        $numTeams = $row ? (int)$row['num_teams'] : 0;
        mysqli_stmt_close($stmt);

        // Trận đã đấu
        $sqlPlayed = "SELECT COUNT(*) AS c FROM `match` WHERE id_tourna=? AND status='played'";
        $stmt = mysqli_prepare($c, $sqlPlayed);
        mysqli_stmt_bind_param($stmt, "i", $tournaId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        $played = $row ? (int)$row['c'] : 0;
        mysqli_stmt_close($stmt);

        // Tổng bàn thắng (match_event)
        $sqlGoals = "
            SELECT COUNT(*) AS g
            FROM match_event me
            JOIN `match` m ON m.id_match = me.id_match
            WHERE m.id_tourna=? AND m.status='played' AND me.event_type IN ('goal')
        ";
        $stmt = mysqli_prepare($c, $sqlGoals);
        mysqli_stmt_bind_param($stmt, "i", $tournaId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        $goals = $row ? (int)$row['g'] : 0;
        mysqli_stmt_close($stmt);

        mysqli_close($c);

        return [
            'num_teams'          => $numTeams,
            'num_matches_played' => $played,
            'total_goals'        => $goals,
            'goals_per_match'    => $played > 0 ? number_format($goals / $played, 2) : '0.00',
        ];
    }

    // 2) Lấy các stage kiểu vòng tròn (nếu có)
    public function getLeagueStages($tournaId){
        $p = new mConnect();
        $c = method_exists($p,'moketnoi') ? $p->moketnoi() : $p->moKetNoi();
        if(!$c){ return []; }

        $sql = "SELECT * FROM stage WHERE id_tourna=? AND stage_type IN ('round_robin','group') ORDER BY order_no";
        $stmt = mysqli_prepare($c, $sql);
        mysqli_stmt_bind_param($stmt, "i", $tournaId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        $rows = [];
        while($res && ($r = mysqli_fetch_assoc($res))){
            $rows[] = $r;
        }
        mysqli_stmt_close($stmt);
        mysqli_close($c);
        return $rows;
    }

    // 3) BXH đơn giản (3-1-0) – tính trên toàn bộ match của giải
    public function getStandingsLive($tournaId){
        $p = new mConnect();
        $c = method_exists($p,'moketnoi') ? $p->moketnoi() : $p->moKetNoi();
        if(!$c){ return []; }

        // LƯU Ý: nếu cột tên đội của bạn là team_name thì thay tất cả teamName -> team_name
        $sql = "
            SELECT
              t.team_id,
              t.teamName,
              SUM(p)  AS p, SUM(w) AS w, SUM(d) AS d, SUM(l) AS l,
              SUM(gf) AS gf, SUM(ga) AS ga,
              SUM(gf - ga) AS gd,
              SUM(pts) AS pts
            FROM (
              SELECT 
                m.home_team_id AS team_id, tmh.teamName,
                COUNT(*) AS p,
                SUM(CASE WHEN m.home_score > m.away_score THEN 1 ELSE 0 END) AS w,
                SUM(CASE WHEN m.home_score = m.away_score THEN 1 ELSE 0 END) AS d,
                SUM(CASE WHEN m.home_score < m.away_score THEN 1 ELSE 0 END) AS l,
                SUM(m.home_score) AS gf,
                SUM(m.away_score) AS ga,
                SUM(CASE 
                    WHEN m.home_score > m.away_score THEN 3
                    WHEN m.home_score = m.away_score THEN 1
                    ELSE 0
                END) AS pts
              FROM `match` m
              LEFT JOIN team tmh ON tmh.id_team = m.home_team_id
              WHERE m.id_tourna=? AND m.status='played'
              GROUP BY m.home_team_id, tmh.teamName

              UNION ALL

              SELECT 
                m.away_team_id AS team_id, tma.teamName,
                COUNT(*) AS p,
                SUM(CASE WHEN m.away_score > m.home_score THEN 1 ELSE 0 END) AS w,
                SUM(CASE WHEN m.away_score = m.home_score THEN 1 ELSE 0 END) AS d,
                SUM(CASE WHEN m.away_score < m.home_score THEN 1 ELSE 0 END) AS l,
                SUM(m.away_score) AS gf,
                SUM(m.home_score) AS ga,
                SUM(CASE 
                    WHEN m.away_score > m.home_score THEN 3
                    WHEN m.away_score = m.home_score THEN 1
                    ELSE 0
                END) AS pts
              FROM `match` m
              LEFT JOIN team tma ON tma.id_team = m.away_team_id
              WHERE m.id_tourna=? AND m.status='played'
              GROUP BY m.away_team_id, tma.teamName
            ) t
            GROUP BY t.team_id, t.teamName
            ORDER BY pts DESC, gd DESC, gf DESC, teamName ASC
        ";
        $stmt = mysqli_prepare($c, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $tournaId, $tournaId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        $rows = [];
        while($res && ($r = mysqli_fetch_assoc($res))){
            $rows[] = $r;
        }
        mysqli_stmt_close($stmt);
        mysqli_close($c);
        return $rows;
    }

    // 4) LẤY STAGE KNOCKOUT (1 bản duy nhất)
    public function getKnockoutStage($tournaId){
        $p = new mConnect();
        $c = method_exists($p,'moketnoi') ? $p->moketnoi() : $p->moKetNoi();
        if(!$c){ return null; }

        $sql = "SELECT * FROM stage WHERE id_tourna=? AND stage_type='knockout' ORDER BY order_no LIMIT 1";
        $stmt = mysqli_prepare($c, $sql);
        mysqli_stmt_bind_param($stmt, "i", $tournaId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;

        mysqli_stmt_close($stmt);
        mysqli_close($c);
        return $row;
    }

    // 5) LẤY NODES CỦA CÂY ĐẤU (1 bản duy nhất)
    public function getBracketNodes($stageId){
        $p = new mConnect();
        $c = method_exists($p,'moketnoi') ? $p->moketnoi() : $p->moKetNoi();
        if(!$c){ return []; }

        // LƯU Ý: nếu cột tên đội là team_name thì đổi toàn bộ teamName -> team_name
        $sql = "
          SELECT 
            bn.*,
            m.home_team_id, m.away_team_id, m.home_score, m.away_score, m.status,
            m.home_placeholder, m.away_placeholder,
            t1.teamName AS home_team_name, t2.teamName AS away_team_name,
            ts.teamName AS seed_team_name
          FROM bracket_node bn
          LEFT JOIN `match` m ON m.id_match = bn.id_match
          LEFT JOIN team t1 ON t1.id_team = m.home_team_id
          LEFT JOIN team t2 ON t2.id_team = m.away_team_id
          LEFT JOIN team ts ON ts.id_team = bn.seed_team_id
          WHERE bn.id_stage = ?
          ORDER BY bn.round_no ASC, bn.position_in_round ASC
        ";
        $stmt = mysqli_prepare($c, $sql);
        mysqli_stmt_bind_param($stmt, "i", $stageId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        $byRound = [];
        while($res && ($r = mysqli_fetch_assoc($res))){
            // quyết định label
            $homeLabel = $r['home_team_name'] ? $r['home_team_name'] : ($r['home_placeholder'] ?: '');
            $awayLabel = $r['away_team_name'] ? $r['away_team_name'] : ($r['away_placeholder'] ?: '');
            if (!$homeLabel && !$awayLabel && !empty($r['seed_team_name'])) {
                $homeLabel = $r['seed_team_name'];
            }
            $r['home_label'] = $homeLabel;
            $r['away_label'] = $awayLabel;

            $r['home_win'] = ($r['status'] === 'played' && (int)$r['home_score'] > (int)$r['away_score']) ? 1 : 0;
            $r['away_win'] = ($r['status'] === 'played' && (int)$r['away_score'] > (int)$r['home_score']) ? 1 : 0;

            $byRound[$r['round_no']][] = $r;
        }

        mysqli_stmt_close($stmt);
        mysqli_close($c);
        return $byRound;
    }
}
?>
