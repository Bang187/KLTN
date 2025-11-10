<?php
include_once('control/controltourna.php');
$controller = new cTourna();
$idtourna = $_REQUEST["id"];
$tbl = $controller-> getTournaById($idtourna);
if($tbl != null && $tbl != -1 && $tbl != -2){
    $row = $tbl;
    $tournaName = $row['tournaName'];
} else {
    echo "<script>alert('Không tìm thấy giải đấu!'); window.location='dashboard.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa giải đấu</title>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: #f8f9fa;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 40px;
            margin: 40px auto;
            max-width: 600px;
        }

        .form_edittourna {
            width: 100%;
        }

        .form_edittourna h1 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 26px;
            color: #333;
        }

        .form_edittourna table {
            width: 100%;
            border-collapse: collapse;
        }

        .form_edittourna td {
            padding: 10px 0;
        }

        .form_edittourna label {
            font-weight: 600;
            color: #444;
        }

        .form_edittourna input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form_edittourna button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        .form_edittourna button:hover {
            background-color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="container">
        <form class="form_edittourna" action="index.php?page=update_tourna&id=<?php echo $idtourna; ?>" method="post">
            <h1>Chỉnh sửa giải đấu</h1>
            <table>
                <tr>
                    <td><label for="tournaName">Tên giải đấu:</label></td>
                    <td><input type="text" id="tournaName" name="tournaName" value="<?php echo htmlspecialchars($tournaName); ?>" required></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center; padding-top: 20px;">
                        <button type="submit">Cập nhật</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</body>
</html>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = $_POST['tournaName'];
    $res = $controller->editTourna($idtourna, $newName);
    if ($res == 1) {
        echo "<script>alert('Cập nhật giải đấu thành công!'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('Cập nhật giải đấu thất bại! Vui lòng thử lại.');</script>";
    }
}
?>
