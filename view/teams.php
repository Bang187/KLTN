
        <?php
            include_once("control/controlteam.php");
            $p = new cteam();
            if(isset($_REQUEST["btnSearch"])){
                $kq = $p->getTeamByName($_REQUEST["keyword"]);
            }else{
                $kq = $p->getAllTeams();
            }
            $dem = 1; 
            if ($kq === -1) {
                echo '<div class="alert alert-warning text-center mt-4">⚠️ Không tìm thấy đội bóng nào phù hợp với từ khóa của bạn.</div>';
            } elseif ($kq === -2) {
                echo '<div class="alert alert-danger text-center mt-4">❌ Lỗi kết nối cơ sở dữ liệu.</div>';
            }else{ 
                echo '<div class="row g-4">'; // Bootstrap grid
                while($row = $kq->fetch_assoc()) {
                    $id = $row['id_team']; // hoặc $row['teamID'] tùy theo tên cột
                    $logo = $row['logo'];
                    $teamName = $row['teamName'];
                    echo '
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <a href="view/team_detail.php?id='.$id.'" style="text-decoration:none; color:inherit;">
                            <div class="card text-center shadow-sm border-0" style="border-radius: 20px; overflow: hidden; transition: transform 0.3s;">
                                <div class="card-banner" style="position: relative;">
                                    <img src="img/doibong/banner1.jpg" class="card-img-top" alt="banner" style="height:130px; object-fit:cover;">
                                    <div style="
                                        position:absolute;
                                        top:60px;
                                        left:50%;
                                        transform:translateX(-50%);
                                        width:90px;
                                        height:90px;
                                        border-radius:90%;
                                        overflow:hidden;
                                        border:4px solid #fff;
                                        background:#fff;">
                                        <img src="img/doibong/'.$logo.'" alt="'.$teamName.'" style="width:100%; height:100%; object-fit:cover;">
                                    </div>
                                </div>
                                <div class="card-body mt-4">
                                    <div style="color:red; font-size:18px; margin-bottom:5px;">★★★★★</div>
                                    <h5 class="card-title">'.$teamName.'</h5>
                                </div>
                            </div>
                        </a>
                    </div>';
                }
                echo '</div>';
            }
            ?>