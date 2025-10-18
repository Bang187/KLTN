<?php
require_once __DIR__ . '/modelconnect.php';

class mLocation {
    public function listAll(): array {
        $p = new mConnect();
        $conn = $p->moKetNoi();
        $rows[] = [];
        if ($conn) {
            $sql = "SELECT id_local, localname, address FROM location ORDER BY localname";
            $res = mysqli_query($conn, $sql);
            if ($res) {
                while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
            }
            $p->dongKetNoi($conn);
        }
        return $rows;
    }
    

    public function create(string $name, ?string $addr): int {
        $p = new mConnect();
        $conn = $p->moKetNoi();
        $newId = 0;

        if ($conn) {
            $sql = "INSERT INTO location(localname, address) VALUES(?, ?)";
            $stm = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stm, "ss", $name, $addr);
            if (mysqli_stmt_execute($stm)) {
                $newId = mysqli_insert_id($conn);
            }
            mysqli_stmt_close($stm);
            $p->dongKetNoi($conn);
        }
        return $newId;
    }
}
