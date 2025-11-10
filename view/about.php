<style>

.about-section,
.features-section,
.team-section {
    padding: 60px 0;
    text-align: center;
}

.about-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: #222;
    margin-bottom: 30px;
    position: relative;
}

.about-title::after {
    content: "";
    width: 80px;
    height: 4px;
    background: #ffd400;
    display: block;
    margin: 10px auto 0;
    border-radius: 2px;
}

.about-text {
    max-width: 900px;
    margin: 0 auto;
    background: #fff;
    border-radius: 12px;
    padding: 30px 40px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    color: #555;
    font-size: 18px;
    line-height: 1.8;
    text-align: justify;
}

.features .feature-box {
    background: #fff;
    border-radius: 14px;
    padding: 40px 25px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    transition: .3s ease;
    height: 100%;
}

.features .feature-box:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.features i {
    font-size: 45px;
    color: #0077ff;
    margin-bottom: 15px;
}

.team-member {
    background: #fff;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    transition: .3s ease;
    text-align: center;
}

.team-member:hover {
    transform: translateY(-6px);
    box-shadow: 0 8px 22px rgba(0,0,0,0.1);
}
.team-member img {
          width:120px;
          height:120px;
          border-radius:50%;
          object-fit:cover;
          border:3px solid #ffd400;
          margin-bottom:10px;
      }
  </style>
    <div class="container">
        <div class="about-section">
            <h2 class="about-title">VỀ CHÚNG TÔI</h2>
            <p class="about-text">
                <b>TOUNAPRO</b> là nền tảng quản lý giải đấu thể thao chuyên nghiệp, được phát triển với mục tiêu
                <b>tự động hóa – minh bạch – hiệu quả</b>. Hệ thống giúp Ban tổ chức, đội bóng và khán giả dễ dàng
                theo dõi toàn bộ tiến trình giải đấu từ khâu đăng ký, lập lịch thi đấu, cập nhật kết quả đến thống kê và báo cáo.
            </p>
        </div>

        <div class="features-section">
            <h2 class="about-title">TÍNH NĂNG NỔI BẬT</h2>
            <div class="row g-4 features">
                <div class="col-md-4">
                    <div class="feature-box">
                        <i class="bi bi-calendar-check"></i>
                        <h5 class="mt-3">Quản lý lịch thi đấu</h5>
                        <p>Tự động tạo, chỉnh sửa và thông báo lịch thi đấu cho từng vòng, từng đội.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <i class="bi bi-people-fill"></i>
                        <h5 class="mt-3">Quản lý đội & cầu thủ</h5>
                        <p>Lưu trữ, phân quyền, thống kê chi tiết cho từng đội bóng và cầu thủ.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <i class="bi bi-bar-chart-line"></i>
                        <h5 class="mt-3">Thống kê & Báo cáo</h5>
                        <p>Cung cấp bảng xếp hạng, biểu đồ và báo cáo chi tiết, xuất file nhanh chóng.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="team-section">
            <h2 class="about-title">ĐỘI NGŨ PHÁT TRIỂN</h2>
            <div class="row justify-content-center g-4">
                <div class="col-md-3 team-member">
                    
                    <h6>Nguyễn Công Bằng</h6>

                </div>
                <div class="col-md-3 team-member">
                    <h6>Nguyễn Thanh Quốc Huy</h6>
                </div>
            </div>
        </div>
    </div>