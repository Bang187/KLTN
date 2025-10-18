<?php
    class mConnect{
        public function moKetNoi(){
            $host = "localhost";
            $username = "root";
            $pass = "";
            $dbname = "football_tournament";
            return mysqli_connect($host,$username,$pass,$dbname);
            
        }
        public function dongKetNoi($conn){
            $conn->close();
        }
        
    }

?>
