<?php
// require_once __DIR__ . '/modelconnect.php';

// class mRuleSet {
//     public function getById($id) {
//         if (!$id) return null;
//         $p = new mConnect();
//         $conn = $p->moKetNoi();  if(!$conn) return null;

//         // Đúng bảng `rule`
//         $sql = "SELECT id_rule, ruletype, rr_rounds, pointwin, pointdraw, pointloss, tiebreak_rule
//                 FROM rule WHERE id_rule=?";
//         $stm = mysqli_prepare($conn, $sql);
//         mysqli_stmt_bind_param($stm, "i", $id);
//         mysqli_stmt_execute($stm);
//         $res = mysqli_stmt_get_result($stm);
//         $row = $res ? $res->fetch_assoc() : null;
//         mysqli_stmt_close($stm);
//         $p->dongKetNoi($conn);
//         return $row;
//     }

//     // Tìm rule trùng tham số; không có thì tạo mới
//     public function findOrCreate(string $type, $rr, $pw, $pd, $pl, $tie, string $name = null): int {
//         $p = new mConnect();
//         $conn = $p->moKetNoi();  if(!$conn) return 0;
//         $id = 0;

//         // 1) Tìm
//         $sql = "SELECT id_rule FROM rule
//                 WHERE ruletype=? AND IFNULL(rr_rounds,-1)=IFNULL(?, -1)
//                   AND IFNULL(pointwin,-1)=IFNULL(?, -1)
//                   AND IFNULL(pointdraw,-1)=IFNULL(?, -1)
//                   AND IFNULL(pointloss,-1)=IFNULL(?, -1)
//                   AND IFNULL(tiebreak_rule,'')=IFNULL(?, '')
//                 LIMIT 1";
//         $stm = mysqli_prepare($conn, $sql);
//         mysqli_stmt_bind_param($stm, "siiiis", $type, $rr, $pw, $pd, $pl, $tie);
//         mysqli_stmt_execute($stm);
//         $res = mysqli_stmt_get_result($stm);

//         if ($row = $res->fetch_assoc()) {
//             $id = (int)$row['id_rule'];
//             mysqli_stmt_close($stm);
//             $p->dongKetNoi($conn);
//             return $id;
//         }
//         mysqli_stmt_close($stm);

//         // 2) Không có → chèn (KHÔNG dùng rule_name vì cột này không tồn tại)
//         $ins = "INSERT INTO rule(ruletype, rr_rounds, pointwin, pointdraw, pointloss, tiebreak_rule)
//                 VALUES(?,?,?,?,?,?)";
//         $stm2 = mysqli_prepare($conn, $ins);
//         mysqli_stmt_bind_param($stm2, "siiiis", $type, $rr, $pw, $pd, $pl, $tie);
//         if (mysqli_stmt_execute($stm2)) $id = mysqli_insert_id($conn);
//         mysqli_stmt_close($stm2);

//         $p->dongKetNoi($conn);
//         return $id;
//     }
//}

require_once __DIR__ . '/modelconnect.php';

class mRuleSet {
    public function getById($id) {
        if (!$id) return null;
        $p = new mConnect();
        $conn = $p->moKetNoi();  if(!$conn) return null;

        $sql = "SELECT 
                    id_rule, ruletype, rr_rounds, pointwin, pointdraw, pointloss, tiebreak_rule,
                    hy_group_count, hy_take_1st, hy_take_2nd, hy_take_3rd, hy_take_4th
                FROM rule
                WHERE id_rule=?";
        $stm = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stm, "i", $id);
        mysqli_stmt_execute($stm);
        $res = mysqli_stmt_get_result($stm);
        $row = $res ? $res->fetch_assoc() : null;
        mysqli_stmt_close($stm);
        $p->dongKetNoi($conn);
        return $row;
    }

    /**
     * Find-or-create 1 rule theo mọi tham số.
     * - $type: 'knockout' | 'roundrobin' | 'hybrid'
     * - các tham số khác có thể để NULL khi không áp dụng (ví dụ knockout).
     *
     * Backward-compatible: controller cũ gọi 7 tham số vẫn chạy (hy_* sẽ là NULL).
     */
    public function findOrCreate(
        string $type,
        $rr, $pw, $pd, $pl, $tie,
        string $name = null,
        $hy_group_count = null, $hy_take_1st = null, $hy_take_2nd = null, $hy_take_3rd = null, $hy_take_4th = null
    ): int {
        $p = new mConnect();
        $conn = $p->moKetNoi();  if(!$conn) return 0;

        // Knockout không dùng rr/point*/tie => chuẩn hóa về NULL
        if ($type !== 'roundrobin' && $type !== 'hybrid') {
            $rr = $pw = $pd = $pl = null;
            $tie = null;
        }
        // Non-hybrid thì bỏ các giá trị hy_*
        if ($type !== 'hybrid') {
            $hy_group_count = $hy_take_1st = $hy_take_2nd = $hy_take_3rd = $hy_take_4th = null;
        }

        // 1) Tìm hàng rule trùng tham số (NULL-safe)
        $sqlSel = "SELECT id_rule FROM rule
                   WHERE ruletype = ?
                     AND rr_rounds     <=> ?
                     AND pointwin      <=> ?
                     AND pointdraw     <=> ?
                     AND pointloss     <=> ?
                     AND tiebreak_rule <=> ?
                     AND hy_group_count<=> ?
                     AND hy_take_1st   <=> ?
                     AND hy_take_2nd   <=> ?
                     AND hy_take_3rd   <=> ?
                     AND hy_take_4th   <=> ?
                   LIMIT 1";
        $st = $conn->prepare($sqlSel);
        // types: s (type) + iiii s i i i i i  => "siiiisiiiii"
        $st->bind_param(
            "siiiisiiiii",
            $type, $rr, $pw, $pd, $pl, $tie,
            $hy_group_count, $hy_take_1st, $hy_take_2nd, $hy_take_3rd, $hy_take_4th
        );
        $st->execute();
        $rs = $st->get_result();
        if ($row = $rs->fetch_assoc()) {
            $id = (int)$row['id_rule'];
            $st->close(); $conn->close();
            return $id;
        }
        $st->close();

        // 2) Không có → chèn mới
        $sqlIns = "INSERT INTO rule
                  (ruletype, rr_rounds, pointwin, pointdraw, pointloss, tiebreak_rule,
                   hy_group_count, hy_take_1st, hy_take_2nd, hy_take_3rd, hy_take_4th)
                   VALUES (?,?,?,?,?,?,?,?,?,?,?)";
        $st2 = $conn->prepare($sqlIns);
        $st2->bind_param(
            "siiiisiiiii",
            $type, $rr, $pw, $pd, $pl, $tie,
            $hy_group_count, $hy_take_1st, $hy_take_2nd, $hy_take_3rd, $hy_take_4th
        );
        $ok = $st2->execute();
        $newId = $ok ? $conn->insert_id : 0;
        $st2->close(); $conn->close();
        return (int)$newId;
    }
}

?>