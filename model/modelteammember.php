<?php
include_once("modelconnect.php");
class mteamMember{
    public function selectAllTeamMember(){
    $p = new mConnect();
    $conn = $p->moketnoi();
    if($conn){
        $query = "SELECT 
            tm.id_member,
            tm.joinTime,
            tm.roleInTeam,
            t.id_team,
            t.teamName,
            t.logo,
            p.id_player,
            p.position,
            p.age,
            p.status,
            u.id_user,
            u.FullName,
            u.phone,
            u.email
        FROM team_member tm
        JOIN team t ON tm.id_team = t.id_team
        JOIN player p ON tm.id_player = p.id_player
        JOIN users u ON p.id_user = u.id_user
        WHERE tm.status = 1";
        
        $result = $conn->query($query);
        $p->dongketnoi($conn);
        return $result;
    }else{
        return false;
    }
}
    public function selectTeamMember($id) {
    $p = new mConnect();
    $conn = $p->moketnoi();
    if ($conn) {
        $query = "
        SELECT 
            tm.id_member,
            tm.id_team,
            tm.roleInTeam,
            tm.joinTime, -- thêm dòng này
            p.position,
            p.age,
            p.status,
            u.FullName,
            u.phone
        FROM team_member tm
        JOIN player p ON tm.id_player = p.id_player
        JOIN users u ON p.id_user = u.id_user
        WHERE tm.id_team = '$id' AND tm.status = 1;
        ";
        $result = $conn->query($query);
        $p->dongketnoi($conn);
        return $result;
    } else {
        return false;
    }
}
public function select01eamMember($id_member) {
    $p = new mConnect();
    $conn = $p->moketnoi();
    if ($conn) {
        $query = "
        SELECT 
            tm.id_member,
            tm.id_team,
            tm.roleInTeam,
            tm.joinTime,
            p.position,
            p.age,
            p.status,
            u.FullName,
            u.phone
        FROM team_member tm
        JOIN player p ON tm.id_player = p.id_player
        JOIN users u ON p.id_user = u.id_user
        WHERE tm.id_member = '$id_member' AND tm.status = 1;
        ";
        $result = $conn->query($query);
        $p->dongketnoi($conn);
        return $result;
    } else {
        return false;
    }
}
   public function update01Member($id_member, $FullName, $position, $age, $phone, $status, $roleInTeam) {
    $p = new mConnect();
    $conn = $p->moketnoi();
    if ($conn) {
        // Lấy id_player và id_user từ team_member
        $queryGet = "
            SELECT tm.id_player, p.id_user
            FROM team_member tm
            JOIN player p ON tm.id_player = p.id_player
            WHERE tm.id_member = ?
        ";
        $stmtGet = $conn->prepare($queryGet);
        $stmtGet->bind_param("i", $id_member);
        $stmtGet->execute();
        $result = $stmtGet->get_result()->fetch_assoc();
        if (!$result) {
            $p->dongketnoi($conn);
            return false;
        }
        $id_player = $result['id_player'];
        $id_user = $result['id_user'];

        // Cập nhật team_member
        $queryTM = "UPDATE team_member SET roleInTeam = ?, joinTime = NOW() WHERE id_member = ?";
        $stmtTM = $conn->prepare($queryTM);
        $stmtTM->bind_param("si", $roleInTeam, $id_member);
        $stmtTM->execute();

        // Cập nhật player
        $queryP = "UPDATE player SET position = ?, age = ?, status = ? WHERE id_player = ?";
        $stmtP = $conn->prepare($queryP);
        $stmtP->bind_param("sisi", $position, $age, $status, $id_player);
        $stmtP->execute();

        // Cập nhật user
        $queryU = "UPDATE users SET FullName = ?, phone = ? WHERE id_user = ?";
        $stmtU = $conn->prepare($queryU);
        $stmtU->bind_param("ssi", $FullName, $phone, $id_user);
        $stmtU->execute();

        $p->dongketnoi($conn);
        return true;
    } else {
        return false;
    }
}

public function delete01TeamMember($id){
        $p = new mconnect();
        $conn = $p->moketnoi();
        if($conn){
            $query = "
                UPDATE team_member 
                SET status = 0, leaveTime = NOW() 
                WHERE id_member = '$id'
            ";
            $result = $conn->query($query);
            $p->dongketnoi($conn);
            return $result;
        }else{
            return false;
        }
    }
    public function selectMemberByPhone($phone){
        $p = new mconnect();
        $conn = $p->moketnoi();
        if($conn){
            $query = "SELECT tm.*, u.FullName, u.phone, u.email 
                      FROM team_member tm
                      JOIN users u ON tm.id_user = u.id_user
                      WHERE u.phone = '$phone' AND tm.status = 1;";
            $result = $conn->query($query);
            $p->dongketnoi($conn);
            return $result;
        }
        return false;
    }
public function insertMember($id_user, $id_team) {
    $p = new mConnect();
    $conn = $p->moketnoi();
    if ($conn) {
        // 1. Kiểm tra user đã là cầu thủ chưa
        $checkPlayer = $conn->query("SELECT id_player FROM player WHERE id_user = '$id_user'");
        if ($checkPlayer && $checkPlayer->num_rows > 0) {
            $row = $checkPlayer->fetch_assoc();
            $id_player = $row['id_player'];
        } else {
            // Nếu chưa có thì thêm mới
            $insertPlayer = "INSERT INTO player ( position, age, status, id_user) VALUES ('', 0, 'Đang hoạt động', '$id_user')";
            if ($conn->query($insertPlayer)) {
                $id_player = $conn->insert_id;
            } else {
                $p->dongketnoi($conn);
                return false;
            }
        }

        // 2. Kiểm tra người này đã trong team chưa
        $checkTeam = $conn->query("SELECT * FROM team_member WHERE id_player = '$id_player' AND id_team = '$id_team'");
        if ($checkTeam && $checkTeam->num_rows > 0) {
            $p->dongketnoi($conn);
            return false; // đã có trong team
        }

        // 3. Thêm vào team_member
        $sql = "INSERT INTO team_member (joinTime, roleInTeam, id_team, id_player)
                VALUES (NOW(), 'Thành viên', '$id_team', '$id_player')";
        $result = $conn->query($sql);

        // 4️ Cập nhật lại ID_role trong bảng users
        if ($result) {
            $updateRole = "
                UPDATE users
                SET ID_role = CASE 
                    WHEN ID_role = 5 THEN 4 
                    ELSE ID_role 
                END
                WHERE id_user = '$id_user'
            ";
            $conn->query($updateRole);
        }
        $p->dongketnoi($conn);
        return $result;
    } else {
        return false;
    }
}

}
?>