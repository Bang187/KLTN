<?php

class cUploadTourna
{
    public static function saveUploadOrDefault(string $fileKey, string $defaultWebPath, string $subFolder = 'tournaments'): string
    {
        //Không có file hoặc có lỗi → dùng mặc định
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            return $defaultWebPath;
        }

        $f = $_FILES[$fileKey];

        $allow = ['jpg','jpeg','png','gif','webp'];
        $ext   = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allow)) return $defaultWebPath;


        if ($f['size'] > 5 * 1024 * 1024) return $defaultWebPath;

    
        $baseFsDir = __DIR__ . '/../uploads';
        if (!is_dir($baseFsDir)) @mkdir($baseFsDir, 0777, true);

        $targetDir = $baseFsDir . '/' . trim($subFolder, '/');
        if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
        

        // Move file
        $fsPath = $targetDir . '/' . $filename;
        if (!move_uploaded_file($f['tmp_name'], $fsPath)) {
            return $defaultWebPath;
        }

        // Đường dẫn WEB (tương đối từ view/)
        return '../uploads/' . trim($subFolder, '/') . '/' . $filename;
    }
}
