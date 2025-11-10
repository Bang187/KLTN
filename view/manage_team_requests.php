<?php
include_once(__DIR__ . "/../control/controljointeam.php");

// Kiểm tra đăng nhập
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Vui lòng đăng nhập để xem yêu cầu gia nhập đội!');
          window.location.href='../index.php?page=login';</script>";
    exit;
}

$id_manager = $_SESSION['id_user'];
$c = new cJoinTeam();
$tblRequest = $c->cGetPendingRequests($id_manager);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý yêu cầu gia nhập đội</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #0d6efd;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-approve { background-color: #28a745; }
        .btn-reject { background-color: #dc3545; }
        
        .btn-approve, .btn-reject {
            font-size: 18px;
            padding: 6px 10px;
            display: inline-block;
            border-radius: 6px;
            text-decoration: none;
            transition: 0.2s;
        }
        .btn-approve {
            background-color: #28a745;
        }
        .btn-reject {
            background-color: #dc3545;
        }
        .btn-approve:hover {
            background-color: #218838;
        }
        .btn-reject:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
<h2>📋 Danh sách yêu cầu gia nhập đội (Đang chờ)</h2>

<?php
if ($tblRequest === false) {
    echo "<p>Lỗi kết nối cơ sở dữ liệu!</p>";
} elseif (mysqli_num_rows($tblRequest) === 0) {
    echo "<p style='text-align:center;'>Không có yêu cầu nào đang chờ duyệt.</p>";
} else {
    echo "<table>
            <tr>
                <th>STT</th>
                <th>Người gửi</th>
                <th>Đội bóng</th>
                <th>Lời nhắn</th>
                <th>Trạng thái</th>
                <th>Ngày gửi</th>
                <th>Thao tác</th>
            </tr>";
    $stt = 1;
    while ($row = mysqli_fetch_assoc($tblRequest)) {
        // Xử lý nội dung lời nhắn an toàn & rút gọn
        $message = trim($row['message'] ?? '');
        if ($message === '') {
            $shortMessage = $safeMessage = "Không có";
            $isEmptyMsg = true;
        } else {
            $safeMessage = htmlspecialchars($message, ENT_QUOTES);
            $words = explode(' ', $message);
            if (count($words) > 5) {
                $shortMessage = htmlspecialchars(implode(' ', array_slice($words, 0, 5)) . '...', ENT_QUOTES);
            } else {
                $shortMessage = htmlspecialchars($message, ENT_QUOTES);
            }
            $isEmptyMsg = false;
        }
        echo "<tr>
                <td>{$stt}</td>
                <td>{$row['nguoi_gui']}</td>
                <td>{$row['ten_doi']}</td>
                <td class='message-cell' data-full=\"$safeMessage\" data-empty='" . ($isEmptyMsg ? "1" : "0") . "'>$shortMessage</td>
                <td>{$row['status']}</td>
                <td>" . date('d/m/Y', strtotime($row['created_at'])) . "</td>
                <td>
                    <a class='btn btn-approve' href='?page=approve_requests&id={$row['id_request']}' title='Duyệt yêu cầu'>✔</a>
                    <a class='btn btn-reject' href='?page=reject_requests&id={$row['id_request']}' title='Từ chối yêu cầu' onclick=\"return confirm('Xác nhận từ chối yêu cầu này?');\">✖</a>
                </td>
              </tr>";
        $stt++;
    }
    echo "</table>";
}
?>
</body>
<!-- Popup hiển thị toàn bộ lời nhắn -->
<div id="popup-message" style="
    display:none;
    position:fixed;
    top:0; left:0; right:0; bottom:0;
    background:rgba(0,0,0,0.6);
    justify-content:center;
    align-items:center;
    z-index:999;
">
    <div style="
        background:white;
        padding:20px;
        max-width:600px;
        max-height:70vh;
        overflow:auto;
        border-radius:10px;
        position:relative;
    ">
        <span id="close-popup" style="
            position:absolute;
            top:10px; right:15px;
            cursor:pointer;
            font-size:20px;
            color:#666;
        ">&times;</span>
        <p id="popup-content" style="white-space:pre-wrap; line-height:1.5;"></p>
    </div>
</div>

<script>
document.querySelectorAll('.message-cell').forEach(cell => {
    cell.addEventListener('click', () => {
        // Nếu là "Không có" thì không bật popup
        if (cell.getAttribute('data-empty') === '1') return;
        const fullMessage = cell.getAttribute('data-full');
        document.getElementById('popup-content').textContent = fullMessage;
        document.getElementById('popup-message').style.display = 'flex';
    });
});

document.getElementById('close-popup').addEventListener('click', () => {
    document.getElementById('popup-message').style.display = 'none';
});

document.getElementById('popup-message').addEventListener('click', (e) => {
    if (e.target === document.getElementById('popup-message')) {
        document.getElementById('popup-message').style.display = 'none';
    }
});
</script>

</html>