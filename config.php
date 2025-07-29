<?php
/**
 * MÜMDER Admin Panel - Veritabanı Bağlantı Dosyası
 * Bu dosyayı mumder-admin klasörüne config.php olarak kaydet
 */

// Hata raporlama ayarları (geliştirme aşamasında)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Veritabanı bağlantı bilgileri
define('DB_HOST', 'localhost');     // Sunucu adresi
define('DB_NAME', 'mumder_db');     // Veritabanı adı
define('DB_USER', 'root');          // Kullanıcı adı (XAMPP varsayılan)
define('DB_PASS', '');              // Şifre (XAMPP'te boş)
define('DB_CHARSET', 'utf8');       // Karakter seti

// Admin giriş bilgileri (güvenlik için değiştir!)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'mumder2025');

// Dosya yükleme ayarları
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB

/**
 * Veritabanı bağlantısı oluştur
 */
function getDatabase() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        return $pdo;
        
    } catch (PDOException $e) {
        // Hata durumunda bilgilendirici mesaj
        die("Veritabanı bağlantı hatası: " . $e->getMessage());
    }
}

/**
 * Güvenli şekilde SQL sorgusu çalıştır
 */
function executeQuery($sql, $params = []) {
    try {
        $pdo = getDatabase();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("SQL Hatası: " . $e->getMessage());
        return false;
    }
}

/**
 * Admin girişi kontrol et
 */
function checkAdminLogin() {
    session_start();
    
    // POST ile giriş yapılıyorsa
    if ($_POST && isset($_POST['username']) && isset($_POST['password'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        
        if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            return true;
        } else {
            return false;
        }
    }
    
    // Oturum kontrolü
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Admin çıkışı yap
 */
function adminLogout() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

/**
 * Form ayarlarını getir
 */
function getFormSettings($formType) {
    $sql = "SELECT setting_name, setting_value FROM admin_settings WHERE setting_name LIKE ?";
    $stmt = executeQuery($sql, [$formType . '%']);
    
    if ($stmt) {
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_name']] = $row['setting_value'];
        }
        return $settings;
    }
    
    return [];
}

/**
 * Form ayarını güncelle
 */
function updateFormSetting($settingName, $settingValue) {
    $sql = "UPDATE admin_settings SET setting_value = ? WHERE setting_name = ?";
    $stmt = executeQuery($sql, [$settingValue, $settingName]);
    return $stmt !== false;
}

/**
 * Form aktif mi kontrol et
 */
function isFormActive($formType) {
    $settings = getFormSettings($formType);
    
    if (!isset($settings[$formType . '_active']) || $settings[$formType . '_active'] !== 'true') {
        return false;
    }
    
    // Tarih kontrolü
    $startDate = $settings[$formType . '_start_date'] ?? '';
    $endDate = $settings[$formType . '_end_date'] ?? '';
    
    if ($startDate && $endDate) {
        $now = new DateTime();
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        
        return $now >= $start && $now <= $end;
    }
    
    return true;
}

/**
 * Güvenli dosya yükleme
 */
function uploadFile($file, $applicationId, $applicationType, $fileType) {
    // Upload klasörü yoksa oluştur
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
    
    // Dosya kontrolü
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return false;
    }
    
    // Dosya boyutu kontrolü
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Güvenli dosya adı oluştur
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeFileName = $applicationType . '_' . $applicationId . '_' . $fileType . '_' . time() . '.' . $extension;
    $uploadPath = UPLOAD_PATH . $safeFileName;
    
    // Dosyayı yükle
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Veritabanına kaydet
        $sql = "INSERT INTO file_uploads (application_type, application_id, file_type, original_filename, stored_filename) VALUES (?, ?, ?, ?, ?)";
        executeQuery($sql, [$applicationType, $applicationId, $fileType, $file['name'], $safeFileName]);
        
        return $safeFileName;
    }
    
    return false;
}

/**
 * Tarih formatını düzenle (Türkçe format)
 */
function formatDate($date) {
    if (empty($date)) return '-';
    
    $dateObj = new DateTime($date);
    return $dateObj->format('d.m.Y H:i');
}

/**
 * JSON yanıt gönder
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Başvuru istatistiklerini getir
 */
function getApplicationStats() {
    $stats = [];
    
    // Burs başvuru sayısı
    $stmt = executeQuery("SELECT COUNT(*) as count FROM scholarship_applications");
    $stats['scholarships'] = $stmt ? $stmt->fetch()['count'] : 0;
    
    // Staj başvuru sayısı
    $stmt = executeQuery("SELECT COUNT(*) as count FROM internship_applications");
    $stats['internships'] = $stmt ? $stmt->fetch()['count'] : 0;
    
    // Üyelik başvuru sayısı
    $stmt = executeQuery("SELECT COUNT(*) as count FROM membership_applications");
    $stats['members'] = $stmt ? $stmt->fetch()['count'] : 0;
    
    // Bağış taahhüt sayısı
    $stmt = executeQuery("SELECT COUNT(*) as count FROM donation_commitments");
    $stats['donors'] = $stmt ? $stmt->fetch()['count'] : 0;
    
    return $stats;
}

// Timezone ayarla
date_default_timezone_set('Europe/Istanbul');

// Güvenlik headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

?>