<!-- 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <style>


article.dn{
  min-height: calc(100vh - 200px);
  height: auto !important;              /* ghi đè rule article cũ nếu còn */
  display:flex;align-items:center;justify-content:center;
  padding:40px 20px;

  background-image:
    linear-gradient(0deg, rgba(0,0,0,.35), rgba(0,0,0,.35)),
    url('img/bglogin.jpg');
  background-position: top center;      /* lấy phần trên của ảnh */
  background-repeat:no-repeat;
  background-size: cover;               /* phủ kín */
}

/* Card “glass” trong hơn một chút */
.login-card{
  width:100%;max-width:460px;
  padding:28px 30px;
  border-radius:16px;
  background: rgba(255,255,255,.70);    /* từ .92 -> .85 để trong hơn */
  backdrop-filter: blur(8px) saturate(160%); /* blur mạnh hơn */
  -webkit-backdrop-filter: blur(8px) saturate(160%);
  box-shadow:0 12px 30px rgba(0,0,0,.18);
  border:1px solid rgba(255,255,255,.65);
}
    .login-title{
      margin-bottom:18px;
      font-size:26px;
      font-weight:700;
      text-align:center;
      color:#222;
      letter-spacing:.2px;
    }
    .login-sub{
      margin-top:-6px;
      margin-bottom:18px;
      text-align:center;
      font-size:13px;
      color:#666;
    }

    .form-group{margin-bottom:14px}
    .form-group label{
      display:block;
      margin-bottom:6px;
      font-size:14px;
      color:#333;
      font-weight:600;
    }
    .form-control{
      width:100%;
      height:44px;
      padding:0 14px;
      border:1px solid #e5e7eb;
      border-radius:12px;
      outline:none;
      font-size:15px;
      background:#fff;
      transition:box-shadow .15s, border-color .15s, transform .05s;
    }
    .form-control:focus{
      border-color:#0ea5e9;
      box-shadow:0 0 0 4px rgba(14,165,233,.15);
    }

    .btn-submit{
      width:100%;
      height:46px;
      border:none;
      border-radius:12px;
      font-size:16px;
      font-weight:700;
      cursor:pointer;
      background:linear-gradient(90deg,#0ea5e9,#22c55e);
      color:#fff;
      transition:transform .05s ease, filter .15s ease;
    }
    .btn-submit:hover{filter:brightness(1.02)}
    .btn-submit:active{transform:translateY(1px)}

    .actions{
      margin-top:10px;
      display:flex;
      justify-content:space-between;
      gap:12px;
      font-size:13px;
    }
    .link{color:#0ea5e9;text-decoration:none}
    .link:hover{text-decoration:underline}


    </style>
</head>
<body >
<article class="dn">
  <form class="login-card" method="post" action="" >
    <div class="login-title">Đăng nhập</div>
    <div class="login-sub">Chào mừng bạn quay lại TOUNAPRO</div>

    <div class="form-group">
      <label for="username">Tên đăng nhập</label>
      <input class="form-control" type="text" id="username" name="username" required />
    </div>

    <div class="form-group">
      <label for="password">Mật khẩu</label>
      <input class="form-control" type="password" id="password" name="password" required />
    </div>

    <button class="btn-submit" name="btnDN" type="submit">Đăng nhập</button>

    <div class="actions">
      <a class="link" href="#">Quên mật khẩu?</a>
      <a class="link" href="register.php">Tạo tài khoản</a>
    </div>
  </form>
</article>

</body>
</html>
login.php (partial) -->
<style>
/* GIỮ các rule bạn đã viết, nhưng sửa/đè thêm vài cái dưới đây */

article.dn{
  min-height: calc(100vh - 200px);
  height: auto !important;             /* đè mọi rule height cũ */
  display:flex; align-items:center; justify-content:center;
  padding:40px 20px;

  background-image:
    linear-gradient(0deg, rgba(0,0,0,.35), rgba(0,0,0,.35)),
    url('img/bglogin.jpg');
  background-position: top center;
  background-repeat: no-repeat;
  background-size: cover;
}

/* ĐÈ các rule article chung của index */
body.login-page article:not(.dn){
  background: transparent !important;
  height: auto !important;
  padding: 0 !important;
}

/* Card trong hơn chút */
.login-card{
  width:100%; max-width:460px;
  padding:28px 30px;
  border-radius:16px;
  background: rgba(255,255,255,.82);
  backdrop-filter: blur(10px) saturate(160%);
  -webkit-backdrop-filter: blur(10px) saturate(160%);
  box-shadow:0 12px 30px rgba(0,0,0,.18);
  border:1px solid rgba(255,255,255,.6);
}
    .login-title{
      margin-bottom:18px;
      font-size:26px;
      font-weight:700;
      text-align:center;
      color:#222;
      letter-spacing:.2px;
    }
    .login-sub{
      margin-top:-6px;
      margin-bottom:18px;
      text-align:center;
      font-size:13px;
      color:#666;
    }

    .form-group{margin-bottom:14px}
    .form-group label{
      display:block;
      margin-bottom:6px;
      font-size:14px;
      color:#333;
      font-weight:600;
    }
    .form-control{
      width:100%;
      height:44px;
      padding:0 14px;
      border:1px solid #e5e7eb;
      border-radius:12px;
      outline:none;
      font-size:15px;
      background:#fff;
      transition:box-shadow .15s, border-color .15s, transform .05s;
    }
    .form-control:focus{
      border-color:#0ea5e9;
      box-shadow:0 0 0 4px rgba(14,165,233,.15);
    }

    .btn-submit{
      width:100%;
      height:46px;
      border:none;
      border-radius:12px;
      font-size:16px;
      font-weight:700;
      cursor:pointer;
      background:linear-gradient(90deg,#0ea5e9,#22c55e);
      color:#fff;
      transition:transform .05s ease, filter .15s ease;
    }
    .btn-submit:hover{filter:brightness(1.02)}
    .btn-submit:active{transform:translateY(1px)}

    .actions{
      margin-top:10px;
      display:flex;
      justify-content:space-between;
      gap:12px;
      font-size:13px;
    }
    .link{color:#0ea5e9;text-decoration:none}
    .link:hover{text-decoration:underline}
</style>

<article class="dn">
  <form class="login-card" method="post" action="">
    <div class="login-title">Đăng nhập</div>
    <div class="login-sub">Chào mừng bạn quay lại TOUNAPRO</div>

    <div class="form-group">
      <label for="username">Tên đăng nhập</label>
      <input class="form-control" type="text" id="username" name="username" required />
    </div>

    <div class="form-group">
      <label for="password">Mật khẩu</label>
      <input class="form-control" type="password" id="password" name="password" required />
    </div>

    <button class="btn-submit" name="btnDN" type="submit">Đăng nhập</button>

    <div class="actions">
      <a class="link" href="#">Quên mật khẩu?</a>
      <a class="link" href="register.php">Tạo tài khoản</a>
    </div>
  </form>
</article>

<?php
// if(isset($_REQUEST["btnDN"])){
//   include "control/controluser.php";
//   $p = new cUser();
//   $res = $p->clogin($_REQUEST["username"], $_REQUEST["password"]);
//   if($res){
//     echo "<script>alert('Đăng nhập thành công!')</script>";
//     echo "<script>window.location.href='admin.php?page=dashboard';</script>";
//   } else {
//     echo "<script>alert('Sai tên đăng nhập hoặc mật khẩu!')</script>";
//   }
// }
?>

<?php
    if(isset($_REQUEST["btnDN"])){
        include("control/controluser.php");
        $p = new cUser();
        $res = $p->clogin($_REQUEST["username"],$_REQUEST["password"]);
        if($res==true){
          $_SESSION['login'] = true;
            if(isset($_SESSION['ID_role']) && $_SESSION['ID_role'] == 1){
                echo "<script>alert('Đăng nhập thành công!')</script>";
                echo "<script>window.location.href = 'admin.php';</script>";
            } elseif ($_SESSION['ID_role'] > 1 ){
                echo "<script>alert('Đăng nhập thành công!')</script>";
                echo "<script>window.location.href = 'dashboard.php';</script>";
            }
        }
        else{
            echo "<script>alert('Sai tên đăng nhập hoặc mật khẩu!')</script>";
        }
    }
?> 