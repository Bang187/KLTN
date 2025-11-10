<?php
include_once("modelconnect.php");

class mJoinTeam {
    // Hàm kiểm tra xem user đã gửi yêu cầu chưa
    public function checkExistingRequest($id_team, $id_user) {
        $p = new mConnect();
        $conn = $p->moKetNoi();
        if ($conn) {
            $query = "SELECT * FROM team_join_request 
                      WHERE id_team = '$id_team' 
                      AND id_user = '$id_user' 
                      AND status = 'pending'";
            $result = mysqli_query($conn, $query);
            $p->dongKetNoi($conn);
            return $result;
        } else {
            return false;
        }
    }

    // Hàm kiểm tra thành viên đã có trong đội
    public function checkIsTeamMember($id_team, $id_user) {
        $p = new mConnect();
        $conn = $p->moKetNoi();
        if ($conn) {
            $query = "
                SELECT tm.id_member
                FROM team_member tm
                JOIN player p ON tm.id_player = p.id_player
                WHERE tm.id_team = '$id_team' AND p.id_user = '$id_user' AND tm.status = 1
            ";
            $result = mysqli_query($conn, $query);
            $p->dongKetNoi($conn);
            return $result; 
        } else {
            return false;
        }
    }
    // Hàm thêm yêu cầu mới
    public function insertJoinRequest($id_team, $id_user, $message = null) {
        $p = new mConnect();
        $conn = $p->moKetNoi();
        if ($conn) {
            $message = mysqli_real_escape_string($conn, $message ?? '');
            $query = "INSERT INTO team_join_request (id_team, id_user, message)
                      VALUES ('$id_team', '$id_user', '$message')";
            $result = mysqli_query($conn, $query);
            $p->dongKetNoi($conn);
            return $result;
        } else {
            return false;
        }
    }

    // Lấy danh sách yêu cầu của các đội do người quản lý này quản lý
    public function getPendingRequestsByManager($id_manager) {
    $p = new mConnect();
    $conn = $p->moKetNoi();
    if (!$conn) return false;

    $query = "
        SELECT 
            r.id_request,
            u.FullName AS nguoi_gui,
            t.teamName AS ten_doi,
            r.message,
            r.status,
            r.created_at
        FROM team_join_request r
        JOIN team t ON r.id_team = t.id_team
        JOIN users u ON r.id_user = u.id_user
        WHERE t.id_user = '$id_manager'
          AND r.status = 'pending'
        ORDER BY r.id_request ASC
    ";
    
    $result = mysqli_query($conn, $query);
    $p->dongKetNoi($conn);
    return $result;
}
/*
    // Duyệt yêu cầu
    public function approveRequest($id_request) {
        $p = new mConnect();
        $conn = $p->moKetNoi();
        if (!$conn) return false;

        // 1️⃣ Lấy thông tin id_team và id_user từ yêu cầu
        $query_info = "SELECT id_team, id_user FROM team_join_request WHERE id_request = '$id_request'";
        $res_info = mysqli_query($conn, $query_info);
        if (!$res_info || mysqli_num_rows($res_info) == 0) {
            $p->dongKetNoi($conn);
            return false;
        }
        $info = mysqli_fetch_assoc($res_info);
        $id_team = $info['id_team'];
        $id_user = $info['id_user'];

        // 2️⃣ Tìm id_player tương ứng với id_user trong bảng player
        $query_player = "SELECT id_player FROM player WHERE id_user = '$id_user'";
        $res_player = mysqli_query($conn, $query_player);
        if (!$res_player || mysqli_num_rows($res_player) == 0) {
            $p->dongKetNoi($conn);
            return false;
        }
        $player = mysqli_fetch_assoc($res_player);
        $id_player = $player['id_player'];

        // 3️⃣ Thêm vào bảng team_member (tránh trùng lặp)
        $query_check = "SELECT * FROM team_member WHERE id_team='$id_team' AND id_player='$id_player'";
        $res_check = mysqli_query($conn, $query_check);
        if (mysqli_num_rows($res_check) == 0) {
            $query_insert = "
                INSERT INTO team_member (id_team, id_player, joinTime, roleInTeam)
                VALUES ('$id_team', '$id_player', NOW(), 'thành viên')
            ";
            mysqli_query($conn, $query_insert);
        }

        // 4️⃣ Cập nhật trạng thái yêu cầu
        $query_update = "UPDATE team_join_request SET status='approved' WHERE id_request='$id_request'";
        $result = mysqli_query($conn, $query_update);

        $p->dongKetNoi($conn);
        return $result;
    }*/

    // Từ chối yêu cầu
    public function rejectRequest($id_request) {
        $p = new mConnect();
        $conn = $p->moKetNoi();
        if (!$conn) return false;
        $query = "UPDATE team_join_request SET status='rejected' WHERE id_request='$id_request'";
        $result = mysqli_query($conn, $query);
        $p->dongKetNoi($conn);
        return $result;
    }
    // Duyệt yêu cầu
public function approveRequest($id_request) {
    $p = new mConnect();
    $conn = $p->moKetNoi();
    if (!$conn) return false;

    // 1️⃣ Lấy thông tin id_team và id_user từ yêu cầu
    $query_info = "SELECT id_team, id_user FROM team_join_request WHERE id_request = '$id_request'";
    $res_info = mysqli_query($conn, $query_info);
    if (!$res_info || mysqli_num_rows($res_info) == 0) {
        $p->dongKetNoi($conn);
        return false;
    }
    $info = mysqli_fetch_assoc($res_info);
    $id_team = $info['id_team'];
    $id_user = $info['id_user'];

    // 2️⃣ Tìm id_player tương ứng với id_user trong bảng player
    $query_player = "SELECT id_player FROM player WHERE id_user = '$id_user'";
    $res_player = mysqli_query($conn, $query_player);

    if (!$res_player || mysqli_num_rows($res_player) == 0) {
        // ⚠️ Nếu chưa có player (thường là người có role 5), thì tạo mới player để tránh lỗi
        $query_insert_player = "INSERT INTO player (id_user, status) VALUES ('$id_user','đang tham gia')";
        $res_insert = mysqli_query($conn, $query_insert_player);
        if (!$res_insert) {
            echo "SQL Error (insert player): " . mysqli_error($conn);
            exit;
        }
        $id_player = mysqli_insert_id($conn);
    } else {
        $player = mysqli_fetch_assoc($res_player);
        $id_player = $player['id_player'];

        // 🔹 Cập nhật trạng thái player thành 'đang tham gia'
        $query_update_player = "UPDATE player SET status = 'đang tham gia' WHERE id_player = '$id_player'";
        mysqli_query($conn, $query_update_player);
    }

    // 3️⃣ Thêm vào bảng team_member (dù có dòng cũ status=0 cũng thêm dòng mới)
    $query_insert = "
        INSERT INTO team_member (id_team, id_player, joinTime, roleInTeam, status)
        VALUES ('$id_team', '$id_player', NOW(), 'thành viên', 1)
    ";
    $res_insert_member = mysqli_query($conn, $query_insert);
    if (!$res_insert_member) {
        echo "SQL Error (insert team_member): " . mysqli_error($conn);
        exit;
    }

    // 4️⃣ Cập nhật trạng thái yêu cầu
    $query_update_request = "UPDATE team_join_request SET status='approved' WHERE id_request='$id_request'";
    $result_update = mysqli_query($conn, $query_update_request);
    if (!$result_update) {
        echo "SQL Error (update request): " . mysqli_error($conn);
        exit;
    }

    // 5️⃣ Nếu duyệt thành công → kiểm tra role hiện tại của người được duyệt
    $query_get_role = "SELECT id_role FROM users WHERE id_user = '$id_user'";
    $res_role = mysqli_query($conn, $query_get_role);
    if ($res_role && mysqli_num_rows($res_role) > 0) {
        $current = mysqli_fetch_assoc($res_role)['id_role'];
        if ($current == 5) {
            $query_role = "UPDATE users SET id_role = 4 WHERE id_user = '$id_user'";
            mysqli_query($conn, $query_role);
        }
    }

    $p->dongKetNoi($conn);
    return $result_update;
}

// Lấy danh sách đội mà người chơi đã tham gia
public function selectTeamsByUser($id_user) {
    $p = new mConnect();
    $conn = $p->moKetNoi();
    if (!$conn) return false;

    $query = "
        SELECT DISTINCT t.id_team, t.teamName, t.logo, tm.joinTime
        FROM team_member tm
        JOIN player p ON tm.id_player = p.id_player
        JOIN team t ON tm.id_team = t.id_team
        WHERE p.id_user = '$id_user'
    ";

    $result = mysqli_query($conn, $query);
    $p->dongKetNoi($conn);
    return $result;
}

// Kiểm tra xem người chơi hiện tại có đang thuộc đội nào không
public function checkPlayerActiveTeam($id_user) {
    $p = new mConnect();
    $conn = $p->moKetNoi();
    if (!$conn) return false;

    $query = "
        SELECT tm.id_team
        FROM team_member tm
        JOIN player p ON tm.id_player = p.id_player
        WHERE p.id_user = '$id_user' AND tm.status = 1
    ";

    $result = mysqli_query($conn, $query);
    $p->dongKetNoi($conn);
    return $result;
}

}
?>