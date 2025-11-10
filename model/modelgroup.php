<?php
require_once __DIR__.'/modelconnect.php';

class mGroup {
  /* ==== ĐỌC THÔNG TIN GIẢI + RULE ==== */
  public function getTournaWithRule(int $id): ?array {
    $c = (new mConnect())->moKetNoi(); if(!$c) return null;
    $sql = "SELECT t.idtourna, t.tournaName, t.team_count, t.id_rule,
                   r.ruletype, r.rr_rounds,
                   r.hy_group_count, r.hy_take_1st, r.hy_take_2nd, r.hy_take_3rd, r.hy_take_4th
            FROM tournament t
            LEFT JOIN rule r ON r.id_rule = t.id_rule
            WHERE t.idtourna=?";
    $st = $c->prepare($sql);
    $st->bind_param('i',$id); $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close(); $c->close();
    return $row ?: null;
  }

  /* ==== NHÓM (GROUP) ==== */
  public function countGroups(int $idTourna): int {
    $c = (new mConnect())->moKetNoi(); if(!$c) return 0;
    $st = $c->prepare("SELECT COUNT(*) c FROM `group` WHERE id_tourna=?");
    $st->bind_param('i',$idTourna); $st->execute();
    $n = (int)$st->get_result()->fetch_assoc()['c'];
    $st->close(); $c->close(); return $n;
  }

  public function createGroupsAndSlots(int $idTourna, int $groupCount, int $teamCount): bool {
    $c = (new mConnect())->moKetNoi(); if(!$c) return false;
    $c->begin_transaction();
    try {
      if ($teamCount < $groupCount) $teamCount = $groupCount; // tối thiểu mỗi bảng 1 slot
      $base = intdiv($teamCount, $groupCount);
      $rem  = $teamCount % $groupCount;

      for ($i=0; $i<$groupCount; $i++) {
        $label = chr(65+$i); // A,B,C,…
        $quota = $base + ($i < $rem ? 1 : 0);

        $st = $c->prepare("INSERT INTO `group`(id_tourna,label,team_quota,sort_order) VALUES (?,?,?,?)");
        $ord = $i+1;
        $st->bind_param('isii', $idTourna, $label, $quota, $ord);
        $st->execute();
        $gid = $c->insert_id;
        $st->close();

        $insS = $c->prepare("INSERT INTO group_slot(id_group,slot_no,id_team) VALUES (?,?,NULL)");
        for ($s=1; $s<=$quota; $s++) {
          $insS->bind_param('ii',$gid,$s);
          $insS->execute();
        }
        $insS->close();
      }
      $c->commit(); $c->close(); return true;
    } catch (\Throwable $e) {
      $c->rollback(); $c->close(); return false;
    }
  }

  public function listGroupsWithSlots(int $idTourna): array {
    $c = (new mConnect())->moKetNoi(); if(!$c) return [];
    $groups = [];
    $st = $c->prepare("SELECT id_group,label,team_quota,sort_order FROM `group`
                       WHERE id_tourna=? ORDER BY sort_order,id_group");
    $st->bind_param('i',$idTourna); $st->execute();
    $rs = $st->get_result();
    while ($g = $rs->fetch_assoc()) { $g['slots']=[]; $groups[]=$g; }
    $st->close();

    if (!$groups) { $c->close(); return []; }

    $ids = array_column($groups,'id_group');
    $in  = implode(',', array_fill(0,count($ids),'?'));
    $types = str_repeat('i', count($ids));

    $sqlS = "SELECT gs.id_group, gs.slot_no, gs.id_team, tm.teamName
             FROM group_slot gs
             LEFT JOIN team tm ON tm.id_team=gs.id_team
             WHERE gs.id_group IN ($in)
             ORDER BY gs.id_group, gs.slot_no";
    $st = $c->prepare($sqlS);
    $st->bind_param($types, ...$ids);
    $st->execute();
    $rs = $st->get_result();

    $by = [];
    foreach ($groups as $i=>$g) $by[(int)$g['id_group']] = $i;
    while ($r = $rs->fetch_assoc()) { $groups[$by[(int)$r['id_group']]]['slots'][] = $r; }
    $st->close(); $c->close();
    return $groups;
  }

  public function listApprovedTeams(int $idTourna): array {
    $c = (new mConnect())->moKetNoi(); if(!$c) return [];
    $sql = "SELECT tm.id_team, tm.teamName
            FROM tournament_team tt
            JOIN team tm ON tm.id_team = tt.id_team
            WHERE tt.id_tourna=? AND tt.reg_status='approved'
            ORDER BY tm.teamName";
    $st = $c->prepare($sql);
    $st->bind_param('i',$idTourna); $st->execute();
    $rs = $st->get_result();
    $rows=[]; while($r=$rs->fetch_assoc()) $rows[]=$r;
    $st->close(); $c->close(); return $rows;
  }

  public function clearAssignments(int $idTourna): bool {
    $c = (new mConnect())->moKetNoi(); if(!$c) return false;
    $st = $c->prepare("UPDATE group_slot gs
                       JOIN `group` g ON g.id_group=gs.id_group
                       SET gs.id_team=NULL
                       WHERE g.id_tourna=?");
    $st->bind_param('i',$idTourna);
    $ok = $st->execute();
    $st->close(); $c->close(); return $ok;
  }

  public function setAssignment(int $idGroup, int $slotNo, int $idTeam): bool {
    $c = (new mConnect())->moKetNoi(); if(!$c) return false;
    $st = $c->prepare("UPDATE group_slot SET id_team=? WHERE id_group=? AND slot_no=?");
    $st->bind_param('iii',$idTeam,$idGroup,$slotNo);
    $ok = $st->execute();
    $st->close(); $c->close(); return $ok;
  }
public function listGroups(int $idTourna): array {
    $c = (new mConnect())->moKetNoi(); if(!$c) return [];
    // dùng đúng cột hiện có; alias nếu bạn muốn tên "đẹp"
    $sql = "SELECT id_group, label AS group_name, sort_order AS group_order
            FROM `group`
            WHERE id_tourna=?
            ORDER BY sort_order, id_group";
    $st = $c->prepare($sql);
    $st->bind_param('i', $idTourna);
    $st->execute();
    $res = $st->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $st->close(); $c->close();
    return $rows;
}

public function listTeamsInGroup(int $idGroup): array {
    $c = (new mConnect())->moKetNoi(); if(!$c) return [];
    // Đúng tên bảng và cột hiện có
    $sql = "SELECT gs.slot_no, tm.id_team, tm.teamName
            FROM group_slot gs
            LEFT JOIN team tm ON tm.id_team = gs.id_team
            WHERE gs.id_group=?
            ORDER BY gs.slot_no";
    $st = $c->prepare($sql);
    $st->bind_param('i', $idGroup);
    $st->execute();
    $res = $st->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $st->close(); $c->close();
    return $rows;
}
// Thêm cuối class mGroup

// Lấy danh sách bảng gọn: id_group + label (A,B,C,...) theo sort_order
public function listGroupsSimple(int $idTourna): array {
    $c = (new mConnect())->moKetNoi(); if(!$c) return [];
    $st = $c->prepare("SELECT id_group, label, sort_order 
                       FROM `group` WHERE id_tourna=? 
                       ORDER BY sort_order, id_group");
    $st->bind_param('i',$idTourna); $st->execute();
    $rs = $st->get_result(); $rows=[];
    while($r=$rs->fetch_assoc()) $rows[]=$r;
    $st->close(); $c->close();
    return $rows;
}

// Lấy round_no lớn nhất của phần vòng bảng (có id_group) để biết playoff bắt đầu từ vòng mấy
public function maxGroupRoundNo(int $idTourna): int {
    $c = (new mConnect())->moKetNoi(); if(!$c) return 0;
    $sql = "SELECT COALESCE(MAX(round_no),0) AS mx 
            FROM `match` WHERE id_tourna=? AND id_group IS NOT NULL";
    $st = $c->prepare($sql); $st->bind_param('i',$idTourna);
    $st->execute(); $mx = (int)$st->get_result()->fetch_assoc()['mx'];
    $st->close(); $c->close();
    return $mx;
}


}
