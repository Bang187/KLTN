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
 public function getStandingsLive(int $idTourna): array {
    $p = new mConnect();
    $c = method_exists($p,'moketnoi') ? $p->moketnoi() : $p->moKetNoi();
    if(!$c) return [];

    // 1) Đọc rule của giải (điểm T/H/B)
    //    Sử dụng modeltourna->getDetail() nếu có, còn không thì join nhanh
    $pw = 3; $pd = 1; $pl = 0;
    $rs = $c->query("
        SELECT r.pointwin, r.pointdraw, r.pointloss
        FROM tournament t
        LEFT JOIN rule r ON r.id_rule = t.id_rule
        WHERE t.idtourna = ".(int)$idTourna." LIMIT 1
    ");
    if ($rs && ($r = $rs->fetch_assoc())) {
        $pw = (int)($r['pointwin']  ?? 3);
        $pd = (int)($r['pointdraw'] ?? 1);
        $pl = (int)($r['pointloss'] ?? 0);
    }

    // 2) Lấy toàn bộ đội đã duyệt để có mặt trên BXH dù chưa đá
    $teams = [];
    $nameCol = 'teamName';
    $chk = $c->query("SHOW COLUMNS FROM team LIKE 'team_name'");
    if ($chk && $chk->num_rows > 0) $nameCol = 'team_name';

    $st = $c->prepare("
        SELECT tm.id_team, tm.{$nameCol} AS team_name
        FROM tournament_team tt
        JOIN team tm ON tm.id_team = tt.id_team
        WHERE tt.id_tourna=? AND tt.reg_status='approved'
        ORDER BY tm.{$nameCol}
    ");
    $st->bind_param('i',$idTourna);
    $st->execute();
    $rs = $st->get_result();
    while ($row = $rs->fetch_assoc()) {
        $tid = (int)$row['id_team'];
        $teams[$tid] = [
            'team_id'=>$tid,'team_name'=>$row['team_name'],
            'p'=>0,'w'=>0,'d'=>0,'l'=>0,'gf'=>0,'ga'=>0,'gd'=>0,'pts'=>0
        ];
    }
    $st->close();

    if (empty($teams)) { $c->close(); return []; }

    // 3) Cộng dồn từ các trận đã có tỷ số (không phụ thuộc status)
    $st = $c->prepare("
        SELECT home_team_id, away_team_id, home_score, away_score
        FROM `match`
        WHERE id_tourna=? AND home_team_id IS NOT NULL AND away_team_id IS NOT NULL
          AND home_score IS NOT NULL AND away_score IS NOT NULL
    ");
    $st->bind_param('i',$idTourna);
    $st->execute();
    $rs = $st->get_result();
    while ($m = $rs->fetch_assoc()) {
        $h = (int)$m['home_team_id']; $a = (int)$m['away_team_id'];
        $hs = (int)$m['home_score'];  $as = (int)$m['away_score'];

        // home
        if (isset($teams[$h])) {
            $teams[$h]['p']++; $teams[$h]['gf'] += $hs; $teams[$h]['ga'] += $as;
            if     ($hs > $as) { $teams[$h]['w']++; $teams[$h]['pts'] += $pw; }
            elseif ($hs == $as){ $teams[$h]['d']++; $teams[$h]['pts'] += $pd; }
            else               { $teams[$h]['l']++; $teams[$h]['pts'] += $pl; }
        }
        // away
        if (isset($teams[$a])) {
            $teams[$a]['p']++; $teams[$a]['gf'] += $as; $teams[$a]['ga'] += $hs;
            if     ($as > $hs) { $teams[$a]['w']++; $teams[$a]['pts'] += $pw; }
            elseif ($as == $hs){ $teams[$a]['d']++; $teams[$a]['pts'] += $pd; }
            else               { $teams[$a]['l']++; $teams[$a]['pts'] += $pl; }
        }
    }
    $st->close();
    $c->close();

    foreach ($teams as &$t) $t['gd'] = $t['gf'] - $t['ga'];
    unset($t);

    // 4) Sắp xếp + tie-break H2H (2 đội)
    $rows = array_values($teams);
    usort($rows, function($A,$B){
        if ($A['pts'] !== $B['pts']) return $B['pts'] <=> $A['pts'];
        if ($A['gd']  !== $B['gd'])  return $B['gd']  <=> $A['gd'];
        if ($A['gf']  !== $B['gf'])  return $B['gf']  <=> $A['gf'];
        return strcasecmp($A['team_name'],$B['team_name']);
    });

    // H2H chỉ áp cho 2 đội kề nhau khi bằng PTS/GD/GF
    for ($i=0; $i<count($rows)-1; $i++){
        $A = $rows[$i]; $B = $rows[$i+1];
        if ($A['pts']===$B['pts'] && $A['gd']===$B['gd'] && $A['gf']===$B['gf']) {
            $h2h = $this->headToHead($idTourna, $A['team_id'], $B['team_id'], $pw,$pd,$pl);
            if ($h2h['ptsA'] < $h2h['ptsB']) { $rows[$i] = $B; $rows[$i+1] = $A; }
            elseif ($h2h['ptsA']===$h2h['ptsB']) {
                $gdA = $h2h['gfA'] - $h2h['gaA']; $gdB = $h2h['gfB'] - $h2h['gaB'];
                if ($gdA < $gdB || ($gdA===$gdB && $h2h['gfA'] < $h2h['gfB'])) { $rows[$i] = $B; $rows[$i+1] = $A; }
            }
        }
    }

    return $rows;
}
    // Hỗ trợ tính đối đầu trực tiếp giữa 2 đội
private function headToHead(int $idTourna, int $A, int $B, int $pw,int $pd,int $pl): array {
    $c = (new mConnect())->moKetNoi(); if(!$c)
        return ['ptsA'=>0,'ptsB'=>0,'gfA'=>0,'gaA'=>0,'gfB'=>0,'gaB'=>0];

    $st = $c->prepare("
        SELECT home_team_id, away_team_id, home_score, away_score
        FROM `match`
        WHERE id_tourna=? AND
              ((home_team_id=? AND away_team_id=?) OR (home_team_id=? AND away_team_id=?)) AND
              home_score IS NOT NULL AND away_score IS NOT NULL
    ");
    $st->bind_param('iiiii',$idTourna,$A,$B,$B,$A);
    $st->execute();
    $rs = $st->get_result();

    $ptsA=0;$ptsB=0;$gfA=0;$gaA=0;$gfB=0;$gaB=0;
    while ($m=$rs->fetch_assoc()){
        $hs=(int)$m['home_score']; $as=(int)$m['away_score'];
        if ((int)$m['home_team_id']===$A){
            $gfA+=$hs; $gaA+=$as; $gfB+=$as; $gaB+=$hs;
            if ($hs>$as) $ptsA += $pw; elseif ($hs==$as) { $ptsA += $pd; $ptsB += $pd; } else $ptsB += $pw;
        } else {
            $gfA+=$as; $gaA+=$hs; $gfB+=$hs; $gaB+=$as;
            if ($as>$hs) $ptsA += $pw; elseif ($hs==$as) { $ptsA += $pd; $ptsB += $pd; } else $ptsB += $pw;
        }
    }
    $st->close(); $c->close();
    return ['ptsA'=>$ptsA,'ptsB'=>$ptsB,'gfA'=>$gfA,'gaA'=>$gaA,'gfB'=>$gfB,'gaB'=>$gaB];
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

    $sql = "
      SELECT 
        bn.*,
        m.id_match, m.round_no, m.status, m.kickoff_date,
        m.home_team_id, m.away_team_id, m.home_score, m.away_score,
        m.home_placeholder, m.away_placeholder,
        t1.teamName AS home_team_name,
        t2.teamName AS away_team_name,
        ts.teamName AS seed_team_name
      FROM bracket_node bn
      LEFT JOIN `match` m ON m.id_match = bn.id_match
      LEFT JOIN team t1 ON t1.id_team = m.home_team_id
      LEFT JOIN team t2 ON t2.id_team = m.away_team_id
      LEFT JOIN team ts ON ts.id_team = bn.seed_team_id
      WHERE bn.id_stage = ?
        AND (m.id_group IS NULL OR m.id_group = 0)  -- chỉ lấy KO, tránh lẫn vòng bảng
      ORDER BY bn.round_no ASC, bn.position_in_round ASC, m.id_match ASC
    ";
    $stmt = mysqli_prepare($c, $sql);
    mysqli_stmt_bind_param($stmt, "i", $stageId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $rows = [];
    while($res && ($r = mysqli_fetch_assoc($res))){
        // nhãn đội
        $homeLabel = $r['home_team_name'] ?: ($r['home_placeholder'] ?: '');
        $awayLabel = $r['away_team_name'] ?: ($r['away_placeholder'] ?: '');
        if (!$homeLabel && !$awayLabel && !empty($r['seed_team_name'])) {
            $homeLabel = $r['seed_team_name'];
        }
        $r['home_label'] = $homeLabel ?: '—';
        $r['away_label'] = $awayLabel ?: '—';

        $r['home_win'] = ($r['status'] === 'played' && (int)$r['home_score'] > (int)$r['away_score']) ? 1 : 0;
        $r['away_win'] = ($r['status'] === 'played' && (int)$r['away_score'] > (int)$r['home_score']) ? 1 : 0;

        $rows[] = $r;
    }
    mysqli_stmt_close($stmt);

    if (empty($rows)) { mysqli_close($c); return []; }

    // Gom theo round + đánh STT trong mỗi round
    $byRound = [];
    $maxRound = 0;
    $seq = [];
    foreach ($rows as $r){
        $rn = (int)($r['round_no'] ?? 0);
        if ($rn <= 0) $rn = 1;
        $maxRound = max($maxRound, $rn);
        if (!isset($seq[$rn])) $seq[$rn] = 0;
        $r['_seq'] = ++$seq[$rn]; // STT hiển thị "Trận #<seq>" thay vì id_match

        $byRound[$rn][] = $r;
    }

    // Tên vòng KO: 16 đội -> 1/8; 8 đội -> Tứ kết; 4 đội -> Bán kết; 2 đội -> Chung kết
    $titleOf = function(int $roundNo, int $maxRound) use ($byRound){
        // Suy vòng từ số trận của round đầu
        $firstRound = min(array_keys($byRound));
        $numMatchesFirst = count($byRound[$firstRound] ?? []);
        switch ($numMatchesFirst) {
            case 8:  $map = [1=>'Vòng 1/8', 2=>'Tứ kết', 3=>'Bán kết', 4=>'Chung kết']; break;
            case 4:  $map = [1=>'Tứ kết',   2=>'Bán kết', 3=>'Chung kết']; break;
            case 2:  $map = [1=>'Bán kết',  2=>'Chung kết']; break;
            case 1:  $map = [1=>'Chung kết']; break;
            default: $map = []; // fallback
        }
        return $map[$roundNo] ?? ('Vòng KO '.$roundNo);
    };

    // Gắn round_title
    foreach ($byRound as $rn => &$arr){
        $rt = $titleOf($rn, $maxRound);
        foreach ($arr as &$x) $x['round_title'] = $rt;
        unset($x);
    }
    unset($arr);

    mysqli_close($c);
    return $byRound;
}

// Fallback: dựng cây đấu trực tiếp từ bảng match khi không có stage/bracket_node
public function getBracketFromMatches(int $tournaId): array {
    $p = new mConnect();
    $c = method_exists($p,'moketnoi') ? $p->moketnoi() : $p->moKetNoi();
    if (!$c) return [];

    $sql = "
      SELECT 
        m.id_match, m.round_no, m.status, m.kickoff_date,
        m.home_team_id, m.away_team_id,
        m.home_placeholder, m.away_placeholder,
        t1.teamName AS home_team_name,
        t2.teamName AS away_team_name,
        m.home_score, m.away_score
      FROM `match` m
      LEFT JOIN team t1 ON t1.id_team = m.home_team_id
      LEFT JOIN team t2 ON t2.id_team = m.away_team_id
      WHERE m.id_tourna = ?
      ORDER BY m.round_no ASC, m.id_match ASC
    ";
    $stm = mysqli_prepare($c,$sql);
    mysqli_stmt_bind_param($stm,"i",$tournaId);
    mysqli_stmt_execute($stm);
    $res = mysqli_stmt_get_result($stm);

    $byRound = [];
    while ($res && ($r = mysqli_fetch_assoc($res))) {
        // Nhãn hiển thị hai bên
        $homeLabel = $r['home_team_name'] ?: ($r['home_placeholder'] ?: '');
        $awayLabel = $r['away_team_name'] ?: ($r['away_placeholder'] ?: '');

        $r['home_label'] = $homeLabel ?: '—';
        $r['away_label'] = $awayLabel ?: '—';

        $r['home_win'] = ($r['status']==='played' && (int)$r['home_score'] > (int)$r['away_score']) ? 1 : 0;
        $r['away_win'] = ($r['status']==='played' && (int)$r['away_score'] > (int)$r['home_score']) ? 1 : 0;

        $byRound[(int)$r['round_no']][] = $r;
    }
    mysqli_stmt_close($stm);
    mysqli_close($c);
    return $byRound;
}
// Xử lý vòng bảng
private function getPointRule(mysqli $c, int $idTourna): array {
    $pw = 3; $pd = 1; $pl = 0; $tie = 'GD,GF,H2H';
    $rs = $c->query("
        SELECT r.pointwin, r.pointdraw, r.pointloss, r.tiebreak_rule
        FROM tournament t
        LEFT JOIN rule r ON r.id_rule = t.id_rule
        WHERE t.idtourna = ".(int)$idTourna." LIMIT 1 
    ");
    if ($rs && ($r=$rs->fetch_assoc())){
        $pw = (int)($r['pointwin']  ?? 3);
        $pd = (int)($r['pointdraw'] ?? 1);
        $pl = (int)($r['pointloss'] ?? 0);
        if (!empty($r['tieRule'])) $tie = $r['tieRule'];
    }
    return ['W'=>$pw,'D'=>$pd,'L'=>$pl,'tie'=>$tie];
}

/** BXH cho 1 bảng (id_group) – có tie-break PTS>GD>GF>H2H */
public function getGroupStandings(int $idTourna, int $idGroup): array {
    $p = new mConnect(); $c = method_exists($p,'moketnoi') ? $p->moketnoi() : $p->moKetNoi();
    if (!$c) return [];
    $rule = $this->getPointRule($c,$idTourna); $W=$rule['W']; $D=$rule['D']; $L=$rule['L'];

    // Lấy tất cả đội thuộc bảng này (kể cả chưa đá)
    $teams = [];
    $rs = $c->query("
        SELECT tm.id_team, tm.teamName AS name
        FROM group_slot gt
        JOIN team tm ON tm.id_team = gt.id_team
        WHERE gt.id_group = ".(int)$idGroup."
        ORDER BY name
    ");
    while ($rs && ($r=$rs->fetch_assoc())){
        $tid = (int)$r['id_team'];
        $teams[$tid] = [
            'id_team'=>$tid, 'teamName'=>$r['name'],
            'p'=>0,'w'=>0,'d'=>0,'l'=>0,'gf'=>0,'ga'=>0,'gd'=>0,'pts'=>0
        ];
    }
    if (empty($teams)) { $c->close(); return []; }

    // Cộng dồn match đã có tỉ số trong bảng này
    $st = $c->prepare("
        SELECT home_team_id, away_team_id, home_score, away_score
        FROM `match`
        WHERE id_tourna=? AND id_group=? 
          AND home_team_id IS NOT NULL AND away_team_id IS NOT NULL
          AND home_score IS NOT NULL AND away_score IS NOT NULL
    ");
    $st->bind_param('ii',$idTourna,$idGroup);
    $st->execute();
    $rs = $st->get_result();
    while ($m = $rs->fetch_assoc()){
        $h=(int)$m['home_team_id']; $a=(int)$m['away_team_id'];
        $hs=(int)$m['home_score'];  $as=(int)$m['away_score'];

        if (isset($teams[$h])){
            $t =& $teams[$h];
            $t['p']++; $t['gf'] += $hs; $t['ga'] += $as;
            if     ($hs>$as){ $t['w']++; $t['pts'] += $W; }
            elseif ($hs==$as){ $t['d']++; $t['pts'] += $D; }
            else { $t['l']++; $t['pts'] += $L; }
        }
        if (isset($teams[$a])){
            $t =& $teams[$a];
            $t['p']++; $t['gf'] += $as; $t['ga'] += $hs;
            if     ($as>$hs){ $t['w']++; $t['pts'] += $W; }
            elseif ($as==$hs){ $t['d']++; $t['pts'] += $D; }
            else { $t['l']++; $t['pts'] += $L; }
        }
    }
    $st->close();
    foreach ($teams as &$t) $t['gd'] = $t['gf'] - $t['ga']; unset($t);

    // sắp xếp theo PTS>GD>GF
    $rows = array_values($teams);
    usort($rows, function($A,$B){
        if ($A['pts']!==$B['pts']) return $B['pts'] <=> $A['pts'];
        if ($A['gd'] !==$B['gd'])  return $B['gd']  <=> $A['gd'];
        if ($A['gf'] !==$B['gf'])  return $B['gf']  <=> $A['gf'];
        return $A['id_team'] <=> $B['id_team']; // fallback
    });

    // tieRule có H2H? → mini-table cho nhóm vẫn còn bằng nhau
    if (stripos($rule['tie'],'H2H') !== false){
        $i=0; $n=count($rows);
        while ($i<$n){
            $j=$i;
            while($j+1<$n
               && $rows[$j+1]['pts']===$rows[$i]['pts']
               && $rows[$j+1]['gd'] ===$rows[$i]['gd']
               && $rows[$j+1]['gf'] ===$rows[$i]['gf']) $j++;
            if ($j>$i){
                $slice = array_slice($rows,$i,$j-$i+1);
                $ids   = array_column($slice,'id_team');
                $mini  = $this->miniTableH2HGroup($idTourna,$idGroup,$ids,$rule);
                $map=[]; foreach($mini as $r){ $map[$r['id_team']]=$r; }
                usort($slice,function($A,$B) use($map){
                    $a=$map[$A['id_team']]??['pts'=>0,'gd'=>0,'gf'=>0];
                    $b=$map[$B['id_team']]??['pts'=>0,'gd'=>0,'gf'=>0];
                    if ($a['pts']!==$b['pts']) return $b['pts']<=>$a['pts'];
                    if ($a['gd'] !==$b['gd'])  return $b['gd'] <=>$a['gd'];
                    if ($a['gf'] !==$b['gf'])  return $b['gf'] <=>$a['gf'];
                    return $A['id_team'] <=> $B['id_team'];
                });
                array_splice($rows,$i,count($slice),$slice);
            }
            $i=$j+1;
        }
    }

    $c->close();
    return $rows;
}

// Mini-table H2H trong phạm vi 1 bảng cho 1 tập đội
private function miniTableH2HGroup(int $idTourna,int $idGroup,array $ids,array $rule): array {
    if (count($ids)<2) return [];
    $idList = implode(',', array_map('intval',$ids));
    $p = new mConnect(); $c = $p->moKetNoi(); if(!$c) return [];
    $W=$rule['W']; $D=$rule['D']; // thường 3-1-0
    $sql = "
      SELECT t.id_team,
             SUM(CASE WHEN m.home_team_id=t.id_team THEN m.home_score ELSE m.away_score END) AS gf,
             SUM(CASE WHEN m.home_team_id=t.id_team THEN m.away_score ELSE m.home_score END) AS ga,
             SUM(CASE
                   WHEN (m.home_team_id=t.id_team AND m.home_score>m.away_score) OR
                        (m.away_team_id=t.id_team AND m.away_score>m.home_score) THEN 1 ELSE 0
                 END) AS w,
             SUM(CASE WHEN m.home_score=m.away_score THEN 1 ELSE 0 END) AS d
      FROM team t
      JOIN `match` m ON m.id_tourna=$idTourna AND m.id_group=$idGroup
                    AND m.status='played'
      WHERE t.id_team IN ($idList)
        AND (m.home_team_id IN ($idList) AND m.away_team_id IN ($idList))
      GROUP BY t.id_team
    ";
    $rows=[]; if ($rs=$c->query($sql)){
        while($r=$rs->fetch_assoc()){
            $gf=(int)$r['gf']; $ga=(int)$r['ga']; $w=(int)$r['w']; $d=(int)$r['d'];
            $rows[] = [
                'id_team'=>(int)$r['id_team'],
                'gf'=>$gf,'ga'=>$ga,'gd'=>$gf-$ga,
                'pts'=>$w*$W + $d*$D
            ];
        }
    }
    $c->close();
    return $rows;
}

// Lấy BXH của TẤT CẢ các bảng: ['A'=>[...], 'B'=>[...], ...]
public function getAllGroupStandings(int $idTourna): array {
    $p = new mConnect(); $c = method_exists($p,'moketnoi') ? $p->moketnoi() : $p->moKetNoi();
    if (!$c) return [];
    $rows = $c->query("SELECT id_group, label FROM `group` WHERE id_tourna=".(int)$idTourna." ORDER BY sort_order, label");
    $out=[];
    while ($rows && ($g=$rows->fetch_assoc())){
        $out[$g['label']] = $this->getGroupStandings($idTourna, (int)$g['id_group']);
    }
    $c->close();
    return $out;
}

// Kiểm tra có trận vòng bảng / KO (phục vụ trang thống kê hỗn hợp)
public function hasGroupMatches(int $idTourna): bool {
    $c=(new mConnect())->moKetNoi(); if(!$c) return false;
    $rs=$c->query("SELECT 1 FROM `match` WHERE id_tourna=".(int)$idTourna." AND id_group IS NOT NULL LIMIT 1");
    $ok=$rs && $rs->num_rows>0; $c->close(); return $ok;
}
public function hasKoMatches(int $idTourna): bool {
    $c=(new mConnect())->moKetNoi(); if(!$c) return false;
    $rs=$c->query("SELECT 1 FROM `match` WHERE id_tourna=".(int)$idTourna." AND id_group IS NULL LIMIT 1");
    $ok=$rs && $rs->num_rows>0; $c->close(); return $ok;
}
}
?>
