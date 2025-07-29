<?php
include("db.php");

$ad_soyad       = $_POST["ad_soyad"] ?? '';
$email          = $_POST["email"] ?? '';
$telefon        = $_POST["telefon"] ?? '';
$dogum_yeri     = $_POST["dogum_yeri"] ?? '';
$dogum_tarihi   = $_POST["dogum_tarihi"] ?? '';
$cinsiyet       = $_POST["cinsiyet"] ?? '';
$ikametgah      = $_POST["ikametgah"] ?? '';
$universite     = $_POST["universite"] ?? '';
$bolum          = $_POST["bolum"] ?? '';
$sinif          = $_POST["sinif"] ?? '';
$mezuniyet_yili = $_POST["mezuniyet_yili"] ?? '';
$tc_no          = $_POST["tc_no"] ?? '';
$pasaport_turu  = $_POST["pasaport_turu"] ?? '';
$pasaport_no    = $_POST["pasaport_no"] ?? '';



$belge_adi = "";
if (isset($_FILES["belge"]) && $_FILES["belge"]["error"] === 0) {
    $belge_adi = time() . "_" . basename($_FILES["belge"]["name"]);
    move_uploaded_file($_FILES["belge"]["tmp_name"], "uploads/" . $belge_adi);
}

$sql = "INSERT INTO uyelik_basvurulari 
(ad_soyad, email, telefon, dogum_yeri, dogum_tarihi, cinsiyet, ikametgah, universite, bolum, sinif, mezuniyet_yili, tc_no, pasaport_turu, pasaport_no, belge_adi)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ssssssssssssssssi",  // sonundaki 'i' → integer: onay
    $ad_soyad, $email, $telefon, $dogum_yeri, $dogum_tarihi,
    $cinsiyet, $ikametgah, $universite, $bolum, $sinif,
    $mezuniyet_yili, $tc_no, $pasaport_turu, $pasaport_no, $belge_adi
);


if ($stmt->execute()) {
    echo "Başvurunuz başarıyla alındı.";
} else {
    echo "HATA: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
