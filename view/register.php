<?php
    session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.3/font/bootstrap-icons.min.css">
    <style>
        *{
            
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            
  ;
        }
        body{
            
            width: 100%;
            height: auto;
           
            ;
        }
        header{
            width: 100%;
            height: 150px;
            border-bottom: 1px solid black;
        }

        nav{
            text-align: center;
            width: 100%;
            height: 50px;
            background-color: #333;
            border-bottom: 1px solid black;
        }
        nav::after { content:""; display:block; clear:both; }

        ul li{
            
            display: inline-block;  
            list-style: none;
            padding: 10px;
            
            margin: 0;
            color: #fff;  
        }
        li a{
            
            text-decoration: none;
            color: #fff;
            font-size: 20px;
        }
        li a:hover{
            color: #ffd400;
        }
        .logo{
            float: left;
            position: relative;
            top: -29px; 
            left: 20px;
        }
        h2{
            float: left;
            position: relative;
            top: 10px; 
            left: 15px;
            color: #fff;
            font-size: 20px;
        }


article.dn{
  /* 150(header) + 50(nav) = 200 */
  min-height: calc(100vh - 200px);

  /* Canh giữa */
  display: flex;
  align-items: center;
  justify-content: center;

  padding: 40px 20px;

  /* Background phủ kín */
  background-image:
    linear-gradient(0deg, rgba(0,0,0,.35), rgba(0,0,0,.35)),
    url('../img/bglogin.jpg');
  background-position: center top ;
  background-repeat: no-repeat;
  background-size: cover;        /* <- thiếu dòng này nên nhìn lệch/cắt */
}
        .login-card{
    
      width:100%;
      max-width:460px;
      padding:28px 30px;
      border-radius:16px;
      background:rgba(255,255,255,.92);
      backdrop-filter:saturate(140%) blur(3px);
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
        footer{
            width: 100%;
            height: auto;
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 20px 0;
        }
        footer a{
            color: #fff;
            text-decoration: none;
        }
        footer a:hover{
            color: #ffd400;
        }
        footer .logo{
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
        }
        footer .social-icons a{
            margin: 0 10px;
            color: #fff;
            font-size: 20px;
        }
        footer .social-icons a:hover{
            color: #ffd400;
        }
        
/* ====== Card style cho tournaments (Bootstrap + thêm chút CSS) ====== */

.btn-follow {
  border-radius:20px;
  padding:6px 16px;
  border:1px solid #b33;
  color:#b33;
  background:transparent;
}
.btn-follow:hover { background:#b33; color:#fff; }

    </style>
</head>
<body>
    
    <?php
include_once('partials/header.php');
include_once('partials/nav.php');
?>
    
<article class="dn">
  <form class="login-card" method="post" action="" >
    <div class="login-title">Đăng ký</div>
    <div class="login-sub">Chào mừng bạn đến TOUNAPRO</div>
    <div class="form-group">
      <label for="email">Email</label>
      <input class="form-control" type="text"  name="email" required />
    </div>
    <div class="form-group">
      <label for="fullname">Họ tên đầy đủ</label>
      <input class="form-control" type="text"  name="fullname" required />
    </div>
    <div class="form-group">
      <label for="username">Tên đăng nhập</label>
      <input class="form-control" type="text" id="username" name="username" required />
    </div>

    <div class="form-group">
      <label for="password">Mật khẩu</label>
      <input class="form-control" type="password" id="password" name="password" required />
    </div>

    <button class="btn-submit" name="btnDK" type="submit">Đăng ký</button>

    <div class="actions">
        Bạn đã có tài khoản?
      <a class="link" href="login.php">Đăng nhập ngay</a>
    </div>
  </form>
</article>

<?php include_once('partials/footer.php'); ?>
</body>
</html>
<?php
    if(isset($_POST['btnDK'])){
        $email = $_POST['email'];
        $fullname = $_POST['fullname'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        include_once('../control/controluser.php');
        $p = new cUser();
        $res = $p->cregister($email,$fullname,$username,$password);
        if($res == 1){
            echo "<script>alert('Đăng ký thành công!'); window.location='login.php';</script>";
        }elseif($res == -1){
            echo "<script>alert('Tên đăng nhập đã tồn tại! Vui lòng chọn tên khác.');</script>";
        }elseif($res == -2){
            echo "<script>alert('Lỗi kết nối cơ sở dữ liệu! Vui lòng thử lại sau.');</script>";
        }else{
            echo "<script>alert('Đăng ký thất bại! Vui lòng thử lại.');</script>";
        }
    }