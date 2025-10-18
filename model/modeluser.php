<?php
    include_once('modelconnect.php');
    class mUser{
        public function mLogin ($id,$pwd){
            $p = new mConnect();
            $p -> moKetNoi();
            $conn = $p ->moKetNoi();
                if($conn==true){
                    $query = "select * from users where username='$id' and password='$pwd'";
                    $result = mysqli_query ($conn,$query);
                    $p ->dongKetNoi($conn);
                        return $result;}
                else{
                    return false;
                
        }
    }
        public function mRegister($email,$fullname,$username,$password) {
            $p = new mConnect();
            $conn = $p->moKetNoi();
            if ($conn) {
                // Kiểm tra xem tên đăng nhập đã tồn tại chưa
                $checkQuery = "SELECT * FROM users WHERE username='$username'";
                $checkResult = mysqli_query($conn, $checkQuery);
                if ($checkResult->num_rows > 0) {
                  
                    $p->dongKetNoi($conn);
                    return false;
                } else {
                    // Chèn người dùng mới
                    $insertQuery = "INSERT INTO users (email,FullName,username, password, ID_role) VALUES ('$email','$fullname', '$username', '$password', 5)";
                    if (mysqli_query($conn, $insertQuery)) {
                        // Thành công
                        $p->dongKetNoi($conn);
                        return true;
                    } else {
                        // Lỗi chèn 
                        $p->dongKetNoi($conn); 
                        return false;
                    }
                }
            } else {
                return false;
            }
    }
}
    ?>