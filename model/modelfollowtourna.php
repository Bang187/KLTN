<?php
include_once(__DIR__ . '/modelconnect.php');

class mFollow {

    // 🔍 Kiểm tra người dùng đã theo dõi giải này chưa
    public function checkFollowExists($id_user, $id_tourna) {
        $p = new mConnect();
        $conn = $p->moKetNoi();
        $stmt = $conn->prepare("SELECT id_follow FROM follow_tournament WHERE id_user=? AND idtourna=?");
        $stmt->bind_param("ii", $id_user, $id_tourna);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        $conn->close();
        return $exists;
    }

    // ➕ Theo dõi giải
    public function follow($id_user, $id_tourna) {
        $p = new mConnect();
        $conn = $p->moKetNoi();
        $stmt = $conn->prepare("
            INSERT INTO follow_tournament (id_user, idtourna, followed_at, is_active)
            VALUES (?, ?, NOW(), 1)
        ");
        $stmt->bind_param("ii", $id_user, $id_tourna);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }

    // ❌ Hủy theo dõi (xóa hẳn bản ghi)
    public function unfollow($id_user, $id_tourna) {
        $p = new mConnect();
        $conn = $p->moKetNoi();
        $stmt = $conn->prepare("DELETE FROM follow_tournament WHERE id_user=? AND idtourna=?");
        $stmt->bind_param("ii", $id_user, $id_tourna);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }
    // 📋 Lấy danh sách giải mà người dùng đã theo dõi
    public function getFollowedTournaments($id_user) {
        $p = new mConnect();
        $conn = $p->moKetNoi();

        $sql = "
            SELECT t.idtourna, t.tournaName, t.logo, t.banner, t.startdate, t.enddate
            FROM follow_tournament f
            JOIN tournament t ON f.idtourna = t.idtourna
            WHERE f.id_user = ? AND f.is_active = 1
            ORDER BY f.followed_at DESC
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_user);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
        $conn->close();
        return $data;
    }
}