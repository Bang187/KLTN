<?php
// class cUploadTourna
// {
//     public static function saveUploadOrDefault(string $fileKey, string $defaultWebPath, string $subFolder = 'tournaments'): string
//     {
//         //Không có file hoặc có lỗi → dùng mặc định
//         if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
//             return $defaultWebPath;
//         }

//         $f = $_FILES[$fileKey];

//         $allow = ['jpg','jpeg','png','gif','webp'];
//         $ext   = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
//         if (!in_array($ext, $allow)) return $defaultWebPath;


//         if ($f['size'] > 5 * 1024 * 1024) return $defaultWebPath;

    
//         $baseFsDir = __DIR__ . '/../uploads';
//         if (!is_dir($baseFsDir)) @mkdir($baseFsDir, 0777, true);

//         $targetDir = $baseFsDir . '/' . trim($subFolder, '/');
//         if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
        

//         // Move file
//         $fsPath = $targetDir . '/' . $filename;
//         if (!move_uploaded_file($f['tmp_name'], $fsPath)) {
//             return $defaultWebPath;
//         }

//         // Đường dẫn WEB (tương đối từ view/)
//         return '../uploads/' . trim($subFolder, '/') . '/' . $filename;
//     }
// }
class cUploadTourna
{
    private static function ensureDir(string $dir): void {
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
    }

    private static function uniqueFilename(string $origName): string {
        $ext  = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $base = pathinfo($origName, PATHINFO_FILENAME);
        $base = preg_replace('~[^a-z0-9\-]+~i', '-', $base);
        $base = trim($base, '-');
        return $base . '-' . date('Ymd_His') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
    }

    /** Dùng cho ảnh (logo/banner). Không có file hoặc file lỗi -> trả về $defaultWebPath */
    public static function saveImageOrDefault(string $fileKey, string $defaultWebPath, string $subFolder = 'tournaments'): string
    {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            return $defaultWebPath;
        }
        $f = $_FILES[$fileKey];

        $allowExt  = ['jpg','jpeg','png','gif','webp'];
        $ext       = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowExt)) return $defaultWebPath;

        if ($f['size'] > 5 * 1024 * 1024) return $defaultWebPath;

        $allowMime = ['image/jpeg','image/png','image/gif','image/webp'];
        $mime = @mime_content_type($f['tmp_name']);
        if (!in_array($mime, $allowMime)) return $defaultWebPath;

        $baseFsDir = __DIR__ . '/../uploads';
        self::ensureDir($baseFsDir);

        $targetDir = $baseFsDir . '/' . trim($subFolder, '/');
        self::ensureDir($targetDir);

        $filename = self::uniqueFilename($f['name']);
        $fsPath   = $targetDir . '/' . $filename;

        if (!move_uploaded_file($f['tmp_name'], $fsPath)) {
            return $defaultWebPath;
        }

        // đường dẫn web tương đối (từ view/)
        return '../uploads/' . trim($subFolder, '/') . '/' . $filename;
    }

    /** Dùng cho PDF/Word của điều lệ. Không có/không hợp lệ -> trả về null */
    public static function saveDoc(string $fileKey, string $subFolder = 'tournaments'): ?array
    {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $f = $_FILES[$fileKey];

        $allowExt  = ['pdf','doc','docx'];
        $ext       = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowExt)) return null;

        if ($f['size'] > 10 * 1024 * 1024) return null;

        $allowMime = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $mime = @mime_content_type($f['tmp_name']);
        if (!in_array($mime, $allowMime)) return null;

        $baseFsDir = __DIR__ . '/../uploads';
        self::ensureDir($baseFsDir);

        $targetDir = $baseFsDir . '/' . trim($subFolder, '/');
        self::ensureDir($targetDir);

        $filename = self::uniqueFilename($f['name']);
        $fsPath   = $targetDir . '/' . $filename;

        if (!move_uploaded_file($f['tmp_name'], $fsPath)) {
            return null;
        }

        $webPath = '../uploads/' . trim($subFolder, '/') . '/' . $filename;
        return [
            'file_name' => $f['name'],
            'file_path' => $webPath,
            'mime_type' => @mime_content_type($fsPath),
            'file_size' => (int)$f['size'],
        ];
    }

    /** Giữ tương thích với code cũ của bạn (nếu nơi nào còn gọi) */
    public static function saveUploadOrDefault(string $fileKey, string $defaultWebPath, string $subFolder = 'tournaments'): string
    {
        return self::saveImageOrDefault($fileKey, $defaultWebPath, $subFolder);
    }
}

?>