<?php ob_start(); ?>
<?php
session_start();  
define('APP_INIT', true);
$BASE = __DIR__;
set_include_path(
    get_include_path()
    . PATH_SEPARATOR . $BASE . '/view'      // views
    . PATH_SEPARATOR . $BASE . '/view/partials' // header, nav, footer
    . PATH_SEPARATOR . $BASE . '/control'   // controllers
    . PATH_SEPARATOR . $BASE . '/model'     // models (nếu cần)
);
if ($_SESSION['login'] !== true) {
    header('Location: index.php?page=login');
    echo "<script>alert('Bạn cần đăng nhập để truy cập trang này!');</script>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
/* Reset nhẹ & biến */
* { box-sizing: border-box; margin: 0; padding: 0; }
:root{
  --bg: #f6f7fb;
  --card: #ffffff;
  --text: #1f2937;
  --muted: #6b7280;
  --primary: #2563eb;          
  --sidebar-w: 260px;
  --radius: 14px;
  --shadow: 0 8px 20px rgba(0,0,0,.06);
}

html, body { height: 100%; }
body { background: var(--bg); color: var(--text); font-family: system-ui, -apple-system, "Segoe UI", Roboto, Inter, Arial, sans-serif; }


.topnav{
  display:block;
  width:100%;
  height:50px;
  background:#333;
  border-bottom:1px solid #000;
}
.topnav ul{ list-style:none; text-align:center; height:100% }
.topnav ul li{ display:inline-block; padding:10px; color:#fff }
.topnav a{ text-decoration:none; color:#fff; font-size:20px }
.topnav a:hover{ color:#ffd400 }
.topnav .logo{ float:left; position:relative; top:-29px; left:20px }
.topnav h2{ float:left; position:relative; top:10px; left:15px; color:#fff; font-size:20px }

.layout{
  display: flex;
  gap: 20px;
  padding: 0;               
}

/* Sidebar trái */
.sidebar{
  flex: 0 0 var(--sidebar-w);
  background: var(--card);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 18px 14px;
  max-height: calc(100vh - 220px); 
  position: sticky; 
  top: 20px;                  
  align-self: flex-start;     
}

/* Tiêu đề sidebar */
.sidebar h2{
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 12px;
  color: var(--text);
}

/* Menu trong sidebar */
.sidebar ul{ list-style: none; }
.sidebar li + li{ margin-top: 6px; }

.sidebar a{
  display: block;
  text-decoration: none;
  color: var(--text);
  padding: 12px 12px;
  border-radius: 10px;
  font-weight: 500;
  line-height: 1.2;
  transition: background .15s ease, color .15s ease, transform .05s ease;
}


.sidebar a:hover{
  background: #eef2ff;        
  color: var(--primary);
}

.sidebar a.active{
  background: var(--primary);
  color: #fff;
  box-shadow: 0 6px 16px rgba(37,99,235,.25);
}

/*/ Phía article */
.content{
  flex: 1 1 auto;
  min-width: 0;                
  background: var(--card);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 20px;
  min-height: 60vh;            
}


main.layout { margin-top: 10px; }
/* --- Submenu cho sideba Tài khoản --- */
.sidebar ul ul.submenu {
  display: none;
  list-style: none;
  padding-left: 15px;
  margin-top: 6px;
}

.sidebar li.has-submenu:hover > ul.submenu {
  display: block;
  animation: fadeIn 0.2s ease-in-out;
}

.sidebar ul ul.submenu li a {
  padding: 10px 12px;
  background: #f9fafb;
  border-radius: 8px;
  color: #374151;
  font-size: 15px;
}

.sidebar ul ul.submenu li a:hover {
  background: #eef2ff;
  color: #2563eb;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}


    </style>
</head>
<body>
    <header>    
        <img src="img/banner.jpg" alt="" width="100%" height="150px"> 
    </header>
    <?php include_once('nav.php'); ?>
  <main class="layout">
    <section class="sidebar">
      <h2>Dashboard</h2>
      
      <ul>
      <?php if (isset($_SESSION['ID_role']) && $_SESSION['ID_role'] == 2):?>
        <li><a href="?page=man_tourna">Quản lý giải đấu</a></li>
      
      <?php endif; ?>
      <?php if (isset($_SESSION['ID_role']) && $_SESSION['ID_role'] == 3): ?>
        <li><a href="?page=man_team">Quản lý đội bóng</a></li>
        <li><a href="?page=man_team_requests">Yêu cầu tham gia đội</a></li>
        <?php endif; ?>
       <?php if (isset($_SESSION['ID_role']) && $_SESSION['ID_role'] == 5): ?>
      <li><a href="following_tournaments.php">Giải đấu đang theo dõi</a></li>
      <?php endif; ?>
      <?php if (isset($_SESSION['ID_role']) && $_SESSION['ID_role'] == 4): ?>
      <li><a href="?page=following_teams">Đội tham gia</a></li>
      <?php endif; ?>
<li class="has-submenu">
          <a href="?page=man_user">Quản lý tài khoản ▾</a>
          <ul class="submenu">
            <li><a href="?page=man_user">Thông tin cá nhân</a></li>
            <li><a href="?page=change_password">Đổi mật khẩu</a></li>
          </ul>
        </li>

        <li><a href="?page=logout">Đăng xuất</a></li>
        
      </ul>
    </section>

    <article class="content">
      
<?php if (isset($_REQUEST['page'])) {
  $p = $_REQUEST['page'];

  switch ($p) {
    case 'man_tourna':    include_once 'manage_tourna.php'; break;
    case 'man_team':      include_once 'manage_team.php';   break;
    case 'man_user':      include_once 'manage_user.php';   break;
    case 'change_password':   include_once 'change_password.php';   break;
    case 'create_tourna': include_once 'create_tourna.php'; break;
    case 'update_tourna': include_once 'updatetourna.php';  break;
    case 'delete_tourna': include_once 'delete_tourna.php'; break;
    case 'edit_tourna':  include_once 'edit_tourna.php';   break;
    case 'logout':        include_once 'logout.php';        break;
    case 'rank':          include_once 'manage_ranktourna.php'; break;
    case 'regulation':   include_once 'manage_regulation.php'; break;
    case 'following_teams': include_once("following_teams.php") ; break ;
                    case 'delete_team': include_once("delete_team.php") ; break ;
                    case 'create_team': include_once("create_team.php") ; break ;
                    case 'edit_team': include_once("edit_team.php") ; break ;
                    case 'update_team': include_once("update_team.php") ; break ;
                    // member
                    case 'man_team_requests':      include_once 'manage_team_requests.php';   break;
                    case 'approve_requests': include_once 'view/approve_request.php'; break;
                    case 'reject_requests': include_once 'view/reject_request.php'; break;
                    case 'dash_team_member': include_once("dash_team_member.php") ; break ;
                    case 'edit_member': include_once("edit_member.php") ; break ;
                    case 'delete_member': include_once("delete_member.php") ; break ;
                    
                    case 'add_member': include_once("add_member.php") ; break ;
    case 'draw_group': include_once("draw_group.php") ;break;

    case 'match_stats':
      require_once 'controlmatchstat.php';
      $idMatch  = isset($_GET['id_match']) ? (int)$_GET['id_match'] : 0;
      $idTourna = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['id_tourna']) ? (int)$_GET['id_tourna'] : 0);
      (new cMatchStats())->screen($idMatch);
      break;

    case 'addteam':
      require_once 'controltourna.php';
      $id_tourna = isset($_GET['id']) ? (int)$_GET['id'] : 0;
      (new cTourna())->addTeamScreen($id_tourna);
      break;

    case 'draw':
      require_once 'controldraw.php';
      $idTourna  = isset($_GET['id_tourna']) ? (int)$_GET['id_tourna'] : 0;
      $teamCount = isset($_GET['team_count']) ? (int)$_GET['team_count'] : 0;
      (new cDraw())->screen($idTourna, $teamCount);
      break;

    case 'schedule':
      require_once 'controlschedule.php';
      $idTourna = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['id_tourna']) ? (int)$_GET['id_tourna'] : 0);
      (new cSchedule())->screen($idTourna);
      break;
    case 'draw_save_seed': {
      require_once 'controldraw.php';
      $idTourna = isset($_GET['id_tourna']) ? (int)$_GET['id_tourna'] : 0;
      (new cDraw())->saveSeeds($idTourna);  // << lưu tournament_team.seed
      break;
    }

    case 'draw_place_seeded': {
      require_once 'controldraw.php';
      $idTourna = isset($_GET['id_tourna']) ? (int)$_GET['id_tourna'] : 0;
      (new cDraw())->placeSeeded($idTourna); // << điền draw_slot theo seed
      break;
    }

    case 'schedule_generate': {
      require_once 'controlschedule.php';
      $idTourna = isset($_GET['id_tourna']) ? (int)$_GET['id_tourna'] : 0;
      (new cSchedule())->generate($idTourna); // << đọc ruletype để biết KO/RR
      break;
    }
    case 'gen_group_schedule':
    $id = isset($_GET['id_tourna']) ? (int)$_GET['id_tourna'] : 0;
    if ($id <= 0) { echo "Thiếu id_tourna"; break; }
    require_once __DIR__.'/control/controlschedule.php';
    $cs  = new cSchedule();
    $res = $cs->genGroupSchedule($id);
    $msg = $res['ok'] ? 'gen_ok' : 'gen_fail';
    header('Location: dashboard.php?page=schedule&id='.$id.'&msg='.$msg);
    exit;
  
  }
    
    

} else { ?>
  <h3>Chào mừng <?= htmlspecialchars($_SESSION['username'] ?? 'người dùng') ?> đến với Dashboard</h3>
  <p>Chọn mục bên trái để quản lý.</p>
<?php } ?>

    </article>
  </main>

</body>
</html>
<?php ob_end_flush(); ?>