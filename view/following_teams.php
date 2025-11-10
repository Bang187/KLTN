<?php
include_once("control/controljointeam.php");
$p = new cJoinTeam();

$id_user = $_SESSION['id_user'] ?? 0;

if ($id_user == 0) {
    echo "<div class='alert alert-warning text-center mt-4'>Bạn cần đăng nhập để xem các đội đã tham gia.</div>";
    exit;
}

$kq = $p->getTeamsByUser($id_user);
?>

<style>
.team-container {
    max-width: 1200px;
    margin: 50px auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 30px;
    padding: 20px;
}

.team-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    overflow: hidden;
    position: relative;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.team-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.team-banner {
    width: 100%;
    height: 140px;
    overflow: hidden;
}

.team-banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Logo ra ngoài banner */
.team-logo {
    position: absolute;
    top: 100px; /* vị trí giữa banner và phần trắng */
    left: 50%;
    transform: translateX(-50%);
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 5px solid #fff;
    background: #fff;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    z-index: 10;
}

.team-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.team-info {
    margin-top: 60px; /* chừa chỗ cho logo */
    padding-bottom: 15px;
}

.team-info .stars {
    color: red;
    font-size: 18px;
    margin-bottom: 6px;
}

.team-info h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #222;
}
</style>

<div class="team-container">
<?php
if ($kq && $kq->num_rows > 0) {
    while ($row = $kq->fetch_assoc()) {
        $id = $row['id_team'];
        $logo = $row['logo'];
        $teamName = $row['teamName'];
        $joinTime = date('d/m/Y', strtotime($row['joinTime']));

        echo '
        <a href="view/team_detail.php?id='.$id.'" style="text-decoration:none; color:inherit;">
            <div class="team-card">
                <div class="team-banner">
                    <img src="img/doibong/banner1.jpg" alt="banner">
                </div>
                <div class="team-logo">
                    <img src="img/doibong/'.$logo.'" alt="'.$teamName.'">
                </div>
                <div class="team-info">
                    <div class="stars">★★★★★</div>
                    <h4>'.$teamName.'</h4>
                    <i>Ngày gia nhập: '.$joinTime.'</i>
                </div>
            </div>
        </a>';
    }
} else {
    echo "<div class='text-center text-muted'>Bạn chưa tham gia đội nào.</div>";
}
?>
</div>