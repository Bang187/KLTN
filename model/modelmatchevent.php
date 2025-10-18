<?php
include_once(__DIR__ . '/../model/modelconnect.php');
include_once(__DIR__ . '/../model/modelschedule.php');
  class mMatchEvent{
    public function getMatchBasic($idMatch){
        $p = new mConnect();
        $c = $p->moketnoi(); 
        if(!$c) return null;
        $sql="SELECT m.*, 
                 th.teamName AS home_name, ta.teamName AS away_name
          FROM `match` m
          LEFT JOIN team th ON th.id_team=m.home_team_id
          LEFT JOIN team ta ON ta.id_team=m.away_team_id
          WHERE m.id_match=?";
    $stm=mysqli_prepare($c,$sql);
    mysqli_stmt_bind_param($stm,"i",$idMatch);
    mysqli_stmt_execute($stm);
    $res=mysqli_stmt_get_result($stm);
    $row=$res->fetch_assoc();
    mysqli_stmt_close($stm);
    $p->dongketnoi($c);
    return $row;
    }
    public function listMembersOfTeam ($idTeam){
        $p = new mConnect();
        $c = $p->moketnoi(); 
        if(!$c) return [];
        $sql = "SELECT tm.id_member,
                 COALESCE(u.fullname, u.FullName) AS fullname
          FROM team_member tm
          JOIN player p  ON p.id_player = tm.id_player
          JOIN `users` u  ON u.id_user   = p.id_user   
          WHERE tm.id_team = ?
          ORDER BY fullname";
    $stm=mysqli_prepare($c,$sql);
    mysqli_stmt_bind_param($stm,"i",$idTeam);
    mysqli_stmt_execute($stm);
    $res=mysqli_stmt_get_result($stm);
    $rows=[];
    while($r=$res->fetch_assoc()) $rows[]=$r;
    mysqli_stmt_close($stm);
    return $rows;
    }
  
public function listEvents(int $idMatch){
    $p = new mConnect();
    $c = $p->moketnoi();
    if (!$c) return [];

    $sql = "SELECT 
                e.*, 
                tm.id_member,
                pl.id_player,
                COALESCE(u.fullname, u.Fullname) AS fullname
            FROM match_event e
            JOIN team_member tm  ON tm.id_member = e.id_member
            JOIN player      pl  ON pl.id_player = tm.id_player
            JOIN `users`      u   ON u.id_user    = pl.id_user   
            WHERE e.id_match = ?
            ORDER BY e.minute, e.id_event";

    $stm = mysqli_prepare($c, $sql);
    mysqli_stmt_bind_param($stm, "i", $idMatch);
    mysqli_stmt_execute($stm);
    $res = mysqli_stmt_get_result($stm);

    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;

    mysqli_stmt_close($stm);
    $p->dongketnoi($c);
    return $rows;
}

    public function addGoal($idMatch, string $teamSide, int $idMember, int $minute, string $type='goal'){
    $teamSide = ($teamSide==='away') ? 'away':'home';
    $type = in_array($type,['goal','penalty_goal','own_goal'],true)?$type:'goal';   
        $p = new mConnect();
        $c = $p->moketnoi(); 
        if(!$c) return false;
        $sql="INSERT INTO match_event (id_match,team_side,id_member,minute,event_type)
          VALUES (?,?,?,?,?)";
    $stm=mysqli_prepare($c,$sql);
    mysqli_stmt_bind_param($stm,"isiss",$idMatch,$teamSide,$idMember,$minute,$type);
    $ok=mysqli_stmt_execute($stm);
    mysqli_stmt_close($stm);
    $p->dongketnoi($c);
    if ($ok)
      $this->UpdateMatchScore($idMatch);
    return $ok;
    }
    public function UpdateMatchScore($idMatch){
    $p = new mConnect();
    $c = $p->moketnoi(); 
    if(!$c) return false;
    $sql="
      SELECT
        SUM(CASE 
              WHEN team_side='home' AND event_type IN ('goal','penalty_goal') THEN 1
              WHEN team_side='away' AND event_type='own_goal' THEN 1
              ELSE 0 END) AS home_goals,
        SUM(CASE 
              WHEN team_side='away' AND event_type IN ('goal','penalty_goal') THEN 1
              WHEN team_side='home' AND event_type='own_goal' THEN 1
              ELSE 0 END) AS away_goals
      FROM match_event WHERE id_match=?";
    $stm=mysqli_prepare($c,$sql);
    mysqli_stmt_bind_param($stm,"i",$idMatch);
    mysqli_stmt_execute($stm);
    $res=mysqli_stmt_get_result($stm);  
    $r = $res->fetch_assoc();
    $home=(int)($r['home_goals']??0); $away=(int)($r['away_goals']??0);
    mysqli_stmt_close($stm);

    $stm=mysqli_prepare($c,"UPDATE `match` SET home_score=?, away_score=? WHERE id_match=?");
    mysqli_stmt_bind_param($stm,"iii",$home,$away,$idMatch);
    mysqli_stmt_execute($stm);
    mysqli_stmt_close($stm); (new mconnect())->dongketnoi($c);
    return true;

  }
    public function deleteEvent(int $idEvent, int $idMatch){
    $p = new mConnect();
    $c = $p->moketnoi(); 
    if(!$c) return false;
    $stm=mysqli_prepare($c,"DELETE FROM match_event WHERE id_event=?");
    mysqli_stmt_bind_param($stm,"i",$idEvent);
    $ok=mysqli_stmt_execute($stm);
    mysqli_stmt_close($stm);
    $p->dongketnoi($c);
    if ($ok)
      $this->UpdateMatchScore($idMatch);
    return $ok;
  }
  public function FinalResultMatch ($idMatch){
    $this->UpdateMatchScore($idMatch);

    // 2) Lấy tỉ số hiện tại trong bảng match
    $p = new mConnect();
    $c = $p->moketnoi();
    if (!$c) return false;

    $res = $c->query("SELECT home_score, away_score FROM `match` WHERE id_match=".(int)$idMatch);
    $r   = $res ? $res->fetch_assoc() : null;
    $p->dongketnoi($c);

    $hs = (int)($r['home_score'] ?? 0);
    $as = (int)($r['away_score'] ?? 0);

    // 3) Chốt kết quả & propagate lên vòng sau
    $sched = new mSchedule();
    return $sched->setResultAndPropagate($idMatch, $hs, $as);
  }
}
?>