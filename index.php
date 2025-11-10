<?php
$page = $_GET['page'] ?? '';
$isLoginPage = ($page === 'login');
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.3/font/bootstrap-icons.min.css">
    <title>Trang chủ</title>
    <link rel="stylesheet" href="css/style_index.css?v=10.0">
</head>
<body class="<?= $isLoginPage ? 'login-page' : '' ?>">
    <header> 
    <img src="img/banner.jpg" alt="" width="100%" height="150px">
    </header>
    <!-- <nav>
        <img class="logo" src="img/logo.png" alt="" width="100px" height="100px"> 
        <h2>TOUNAPRO</h2>
        <ul>
            <li><a href="index.php">Trang chủ</a></li>
            <li><a href="tourna-follow.php">Giải đang theo dõi</a></li>
            <li><a href="?page=team">Đội bóng</a></li>    
            <li><a href="?page=about">Về chúng tôi</a></li>
            <li><a href="contact.php">Liên hệ</a></li>
            <li><a href="news.php">Tin tức</a></li>
            <li><a href="?page=login">Đăng nhập</a></li>
        </ul>
        
    </nav> -->
    <?php include_once('view/partials/nav.php'); ?>
    <section class="hero-section">
    <div class="hero-content">
        <h1>HỆ THỐNG QUẢN LÝ GIẢI ĐẤU CHUYÊN NGHIỆP</h1>
        <form action="index.php" method="get">
            <?php if (isset($_REQUEST["page"])) { ?>
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
            <?php } ?>
            <input type="text" name="keyword" placeholder="Nhập từ khóa..." size="150"> 
            <button type="submit" name="btnSearch"><i class="fa fa-search"></i></button>
        </form>
    </div>
</section>
<article class="container my-5">
  <div class="row g-4">
        <?php
                if(isset($_REQUEST["page"])){
                    $p = $_REQUEST["page"];
                    switch($p){
                        case 'team': include_once("view/teams.php"); break;
                        case 'login': include_once("view/login.php"); break;
                        case 'register': include_once("view/register.php"); break;
                        case 'about': include_once("view/about.php"); break;
                        case 'contact': include_once("view/contact.php"); break;
                        case 'listnews': include_once("view/listnews.php"); break;
                        case 'view_news_detail' : include_once("view/view_news_detail.php"); break;
                        case 'detail_team' : include_once("view/team_detail.php"); break; 
                        case 'tournaments_followed' : include_once("view/tournaments_followed.php"); break;
                        case 'register_tourna':
                          include_once __DIR__.'/control/controltourna.php';
                          $c = new cTourna();
                          $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['doRegister'])) {
        $res = $c->submitRegisterTeam($id, (int)($_POST['team_id'] ?? 0));
        if (!empty($res['ok'])) {
            if (session_status()===PHP_SESSION_NONE) session_start();
            $_SESSION['flash'] = [
              'type' => 'success',
              'text' => 'Đã gửi đăng ký ghi danh tham gia giải thành công. Vui lòng chờ BTC duyệt.'
            ];
            $base = rtrim(dirname($_SERVER['PHP_SELF']), '/'); // ví dụ /Kltn
            header('Location: ' . $base . '/view/tourna_detail.php?id=' . $id);
            exit;
        }
        // lỗi -> hiện lại form + message như bạn đang làm
        $msg = $res['err'] ?? 'Có lỗi xảy ra';
        $c->showRegisterTeamScreen($id);
    } else {
        $c->showRegisterTeamScreen($id);
    }
                          break;

                        // case 'detail_tourna' : include_once("view/tourna_detail.php"); break;
                        case 'detail_tourna':  include_once("view/tourna_detail.php"); break;

                        
                        
                    }
                }else{
                    include_once("view/tournaments_list.php");
                }
            ?>
  </div>
</article>

<footer class="bg-dark text-white pt-5 pb-3">
  <div class="container">
    <div class="row">
    
      <div class="col-md-4 mb-4">
        <img src="img/logo.png" alt="TOUNAPRO" class="img-fluid mb-2" style="max-width:130px;">
        <p class="big mb-0">
          TOURNAPRO — Hệ thống quản lý giải đấu chuyên nghiệp. Cập nhật lịch thi đấu, bảng xếp hạng và quản lý đội bóng.
        </p>
      </div>

      
      <div class="col-md-3 mb-4">
        <h6 class="fw-bold">Liên kết</h6>
        <ul>
        <li><a href="about.php" >Về chúng tôi</a></li>  <br>
        <li><a href="contact.php" >Liên hệ</a></li>
        <li><a href="terms.php" >Điều khoản sử dụng</a></li>
        <li><a href="privacy.php" >Chính sách bảo mật</a></li>
        </ul>
      </div>

      <!-- contact -->
      <div class="col-md-3 mb-4">
        
        <p>Địa chỉ: 12 Nguyễn Văn Bảo, Phường 1, Gò Vấp, Hồ Chí Minh </p>
        <p>Email: <a href="congbang180703@gmail.com" >congbang180703@gmail.com</a></p>
        <p>Hotline: <span class="fw-bold">0376 583 553 </span></p>
      </div>

    <div class="row">
      <div class="col-12">
        <p class="mb-2 mb-md-0 ">©2025 TOURNAPRO. All rights reserved.</p>

        <div class="d-flex gap-2">
          <a class="btn btn-outline-light btn-sm" href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a class="btn btn-outline-light btn-sm" href="#" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
          <a class="btn btn-outline-light btn-sm" href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
        </div>
      </div>
    </div>
  </div>
</footer>
<!-- ===== TournamentBot Floating Widget (fixed) ===== -->
<style>
  .tb-fab{
    position:fixed; right:22px; bottom:22px; z-index:2147483647;
    width:56px; height:56px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    background: linear-gradient(135deg,#111827 0%, #1f2937 60%, #0ea5e9 100%);
    color:#fff; border:none; box-shadow:0 10px 28px rgba(0,0,0,.22);
    cursor:pointer; pointer-events:auto;
  }
  .tb-panel{
    position:fixed; right:22px; bottom:90px; z-index:2147483647;
    width:360px; max-height:70vh; display:none; flex-direction:column;
    background:#fff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden;
    box-shadow:0 24px 56px rgba(0,0,0,.22); pointer-events:auto;
  }
  .tb-header{
    background: linear-gradient(135deg,#111827 0%, #111827 60%, #0ea5e9 100%);
    color:#fff; padding:12px 14px; display:flex; align-items:center; gap:10px;
  }
  .tb-avatar{ width:28px; height:28px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,.15) }
  .tb-log{ background:#f8fafc; padding:12px; overflow:auto; height:400px; }
  .tb-row{ margin:8px 0; display:flex }
  .tb-bot{ justify-content:flex-start }
  .tb-me{ justify-content:flex-end }
  .tb-bubble{ max-width:86%; padding:8px 10px; border-radius:10px; white-space:pre-wrap; border:1px solid #e5e7eb; background:#fff; color:#111827; }
  .tb-me .tb-bubble{ background:#111827; color:#fff; border:none; }
  .tb-input{ display:flex; gap:8px; padding:12px; border-top:1px solid #e5e7eb; background:#fff; }
  .tb-input input{ flex:1; padding:10px; border:1px solid #e5e7eb; border-radius:10px; }
  .tb-btn{ padding:10px 14px; border:1px solid #111827; background:#111827; color:#fff; border-radius:10px; }
  .tb-links{ margin-top:6px; display:flex; flex-wrap:wrap; gap:6px }
  .tb-links a{ font-size:12px; padding:4px 8px; border-radius:8px; border:1px solid #e5e7eb; background:#fff; text-decoration:none; color:#111827; }
  .typing{ display:flex; gap:6px; padding:4px 0; }
  .typing .dot{ width:6px; height:6px; border-radius:50%; background:#9CA3AF; animation: tbBlink 1.2s infinite ease-in-out; }
  .typing .dot:nth-child(2){ animation-delay:.2s } .typing .dot:nth-child(3){ animation-delay:.4s }
  @keyframes tbBlink { 0%,80%,100%{opacity:.2} 40%{opacity:1} }
  @media (max-width: 480px){ .tb-panel{ right:10px; left:10px; width:auto; } }
</style>

<button class="tb-fab" id="tbFab" title="Trợ lý giải đấu" aria-label="Trợ lý giải đấu">🤖</button>

<div class="tb-panel" id="tbPanel" role="dialog" aria-label="TournamentBot" aria-modal="true">
  <div class="tb-header">
    <span class="tb-avatar">🤖</span>
    <div>
      <div class="tb-title" style="font-weight:700">TournamentBot</div>
      <div class="tb-sub" style="font-size:12px;opacity:.8">Trợ lý giải đấu – hỏi là có dữ liệu ngay</div>
    </div>
    <button class="tb-close" id="tbClose" title="Đóng" aria-label="Đóng" style="margin-left:auto;background:transparent;border:none;color:#fff;font-size:18px;cursor:pointer">✕</button>
  </div>
  <div class="tb-log" id="tbLog">
    <div id="tbTyping" class="typing" style="display:none"><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
  </div>
  <div class="tb-input">
    <input id="tbMsg" placeholder="Hỏi về giải/đội/cầu thủ…">
    <button class="tb-btn" id="tbSend">Gửi</button>
  </div>
</div>

<script>
(function(){
  const panel = document.getElementById('tbPanel');
  const fab   = document.getElementById('tbFab');
  const closeBtn = document.getElementById('tbClose');
  const log   = document.getElementById('tbLog');
  const typing= document.getElementById('tbTyping');
  const msg   = document.getElementById('tbMsg');
  const send  = document.getElementById('tbSend');

  // ⛳ ctx: nếu muốn khoá theo 1 giải/đội ở trang chi tiết thì gán ở server-side, còn mặc định là 0
  const ctx = window.TB_CTX || { tourna_id: 0, team_id: 0 };

  function showTyping(on){ if(typing){ typing.style.display = on ? 'flex' : 'none'; log.scrollTop = log.scrollHeight; } }
  function addBubble(text, me=false, links=[]){
    const row = document.createElement('div'); row.className = 'tb-row ' + (me?'tb-me':'tb-bot');
    const b = document.createElement('div'); b.className = 'tb-bubble'; b.textContent = text; row.appendChild(b);
    if(!me && Array.isArray(links) && links.length){
      const wrap = document.createElement('div'); wrap.className='tb-links';
      links.forEach(l=>{ const a=document.createElement('a'); a.href=l.href; a.target=l.target||'_self'; a.textContent=l.label||'Xem'; wrap.appendChild(a); });
      b.appendChild(wrap);
    }
    log.insertBefore(row, typing || null); log.scrollTop = log.scrollHeight;
  }

  function openPanel(){
    panel.style.display='flex'; localStorage.setItem('tb_open','1');
    if(!sessionStorage.getItem('tb_greeted')){
      const row = document.createElement('div'); row.className='tb-row tb-bot';
      const b = document.createElement('div'); b.className='tb-bubble';
      b.innerHTML = "Xin chào, mình là TournamentBot 🤖<br>Nhấn <b>Bắt đầu</b> để mình gợi ý cách hỏi.";
      const btn = document.createElement('a'); btn.href="#"; btn.textContent="Bắt đầu";
      btn.style.cssText='display:inline-block;margin-top:6px;padding:6px 10px;border:1px solid #e5e7eb;border-radius:8px';
      btn.onclick = (e)=>{ e.preventDefault();
        addBubble("Chào bạn 👋 Mình là TournamentBot.\nMình có thể giúp bạn tra cứu: lịch/kết quả đội, BXH/điều lệ giải, đội hình, vua phá lưới, hồ sơ cầu thủ.\nVí dụ:\n• đội Golden Tigers lịch\n• BXH giải 11111\n• vua phá lưới giải 11111\n• cầu thủ Nguyễn Xuân Hinh");
        sessionStorage.setItem('tb_greeted','1'); row.remove();
      };
      const wrap = document.createElement('div'); wrap.className='tb-links'; wrap.appendChild(btn);
      b.appendChild(wrap); row.appendChild(b); log.insertBefore(row, typing || null); log.scrollTop=log.scrollHeight;
    }
  }
  function closePanel(){ panel.style.display='none'; localStorage.setItem('tb_open','0'); }

  // gắn sự kiện chắc chắn (nếu có lỗi JS ở đâu đó, log ra để bạn thấy)
  try {
    fab.addEventListener('click', ()=> panel.style.display==='flex' ? closePanel() : openPanel());
    closeBtn.addEventListener('click', closePanel);
  } catch(e){ console.error('TB bind error:', e); }
  if(localStorage.getItem('tb_open')==='1') openPanel();

  const TYPING_MIN = 800, TYPING_MAX = 1200;

  async function ask(){
    const text = (msg.value||'').trim(); if(!text) return;
    addBubble(text, true); msg.value='';

    showTyping(true); const t0 = Date.now();
    let data = { ok:false, answer:'⚠️ Lỗi kết nối API.' };

    try{
      const r = await fetch('api/chat.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ message:text, tourna_id: ctx.tourna_id||0, team_id: ctx.team_id||0 })
      });
      data = await r.json();
    }catch(e){
      console.error('TB fetch error:', e);
    }

    const need = Math.floor(Math.random()*(TYPING_MAX-TYPING_MIN))+TYPING_MIN;
    const elapsed = Date.now()-t0; if (elapsed<need) await new Promise(r=>setTimeout(r, need-elapsed));
    showTyping(false);

    const links = Array.isArray(data.links) ? data.links : [];
    addBubble(data.answer || '...', false, links);
  }

  send.addEventListener('click', ask);
  msg.addEventListener('keydown', e=>{ if(e.key==='Enter') ask(); });

  // Tooltip nho nhỏ
  fab.addEventListener('mouseenter', ()=> fab.title = "Trợ lý 24/7: bấm để hỏi!");
})();
</script>
<!-- ===== End TournamentBot Floating Widget ===== -->


</body>
</html>


