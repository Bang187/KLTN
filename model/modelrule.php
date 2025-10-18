<?php
require_once __DIR__ . '/modelconnect.php';

class mRuleSet {
    public function getById($id) {
        if (!$id) return null;
        $p = new mConnect();
        $conn = $p->moKetNoi();
        $row = null;

        if ($conn) {
            $sql = "SELECT id_rule, rule_name, ruletype, rr_rounds, pointwin, pointdraw, pointloss, tiebreak_rule
                    FROM rule_set WHERE id_rule=?";
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

    // tìm rule có cùng tham số; chưa có thì tạo mới, trả về id_rule
    public function findOrCreate(string $type, $rr, $pw, $pd, $pl, $tie, string $name): int {
        $p = new mConnect();
        $conn = $p->moKetNoi();
        $id = 0;

        if ($conn) {
            $sql = "SELECT id_rule FROM rule
                    WHERE ruletype=? AND IFNULL(rr_rounds,-1)=IFNULL(?, -1)
                      AND IFNULL(pointwin,-1)=IFNULL(?, -1)
                      AND IFNULL(pointdraw,-1)=IFNULL(?, -1)
                      AND IFNULL(pointloss,-1)=IFNULL(?, -1)
                      AND IFNULL(tiebreak_rule,'')=IFNULL(?, '')
                    LIMIT 1";
            $stm = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stm, "siiiis", $type, $rr, $pw, $pd, $pl, $tie);
            mysqli_stmt_execute($stm);
            $res = mysqli_stmt_get_result($stm);
            if ($row = mysqli_fetch_assoc($res)) {
                $id = (int)$row['id_rule'];
            } else {
                $ins = "INSERT INTO rule(rule_name, ruletype, rr_rounds, pointwin, pointdraw, pointloss, tiebreak_rule)
                        VALUES(?,?,?,?,?,?,?)";
                $stm2 = mysqli_prepare($conn, $ins);
                mysqli_stmt_bind_param($stm2, "ssiiiis", $name, $type, $rr, $pw, $pd, $pl, $tie);
                if (mysqli_stmt_execute($stm2)) $id = mysqli_insert_id($conn);
                mysqli_stmt_close($stm2);
            }
            mysqli_stmt_close($stm);
            $p->dongKetNoi($conn);
        }
        return $id;
    }
}
