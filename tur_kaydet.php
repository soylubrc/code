<?php
require_once 'config.php';

// POST verilerini kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Temel tur bilgilerini al
        $aracId = $_POST['aracId'] ?? null;
        $soforId = $_POST['soforId'] ?? null;
        $hazirlayanId = $_POST['hazirlayanId'] ?? null;
        $depoSorumlusuId = $_POST['depoSorumlusuId'] ?? null;
        $cikisTarihi = $_POST['cikisTarihi'] ?? null;
        $cikisSaati = $_POST['cikisSaati'] ?? null;
        $tahminiDonusTarihi = $_POST['tahminiDonusTarihi'] ?? null;
        $tahminiDonusSaati = $_POST['tahminiDonusSaati'] ?? null;
        $toplamMesafe = $_POST['toplamMesafe'] ?? 0;
        $nakitTahsilat = isset($_POST['nakitTahsilat']) ? $_POST['nakitTutar'] : null;
        $duraklar = json_decode($_POST['duraklar'] ?? '[]', true);
        
        // Takip kodu oluştur (rastgele 10 karakter)
        $takipKodu = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
        
        // Veritabanı işlemlerini başlat
        $db->beginTransaction();
        
        // Tur kaydını oluştur
        $turQuery = $db->prepare("
            INSERT INTO turlar (
                sofor_id, arac_id, cikis_tarihi, cikis_saati, 
                tahmini_donus_tarihi, tahmini_donus_saati, 
                toplam_mesafe, durum, paket_durumu, nakit_tahsilat, takip_kodu
            ) VALUES (
                :sofor_id, :arac_id, :cikis_tarihi, :cikis_saati,
                :tahmini_donus_tarihi, :tahmini_donus_saati,
                :toplam_mesafe, 'Planlandı', 'Hazırlanıyor', :nakit_tahsilat, :takip_kodu
            )
        ");
        $turQuery->execute([
            ':sofor_id' => $soforId,
            ':arac_id' => $aracId,
            ':cikis_tarihi' => $cikisTarihi,
            ':cikis_saati' => $cikisSaati,
            ':tahmini_donus_tarihi' => $tahminiDonusTarihi ?: null,
            ':tahmini_donus_saati' => $tahminiDonusSaati ?: null,
            ':toplam_mesafe' => $toplamMesafe,
            ':nakit_tahsilat' => $nakitTahsilat,
            ':takip_kodu' => $takipKodu
        ]);
        
        $turId = $db->lastInsertId();
        
        // Tur personelini kaydet
        $personelQuery = $db->prepare("
            INSERT INTO tur_personel (tur_id, personel_id, rol)
            VALUES (:tur_id, :personel_id, :rol)
        ");
        
        // Hazırlayan personel
        $personelQuery->execute([
            ':tur_id' => $turId,
            ':personel_id' => $hazirlayanId,
            ':rol' => 'Hazırlayan'
        ]);
        
        // Depo sorumlusu
        $personelQuery->execute([
            ':tur_id' => $turId,
            ':personel_id' => $depoSorumlusuId,
            ':rol' => 'Depo Sorumlusu'
        ]);
        
        // Durakları kaydet
        $durakQuery = $db->prepare("
            INSERT INTO tur_duraklar (tur_id, il_id, sira, yuk_tip_id, yuk_miktari)
            VALUES (:tur_id, :il_id, :sira, :yuk_tip_id, :yuk_miktari)
        ");
        
        foreach ($duraklar as $durak) {
            $durakQuery->execute([
                ':tur_id' => $turId,
                ':il_id' => $durak['il_id'],
                ':sira' => $durak['sira'],
                ':yuk_tip_id' => $durak['yuk_tip_id'] ?: null,
                ':yuk_miktari' => $durak['yuk_miktari'] ?: null
            ]);
        }
        
        // Şoför ve araç durumunu güncelle
        $db->prepare("UPDATE soforler SET durum = 'Yolda' WHERE sofor_id = :sofor_id")->execute([':sofor_id' => $soforId]);
        $db->prepare("UPDATE araclar SET durum = 'Yolda' WHERE arac_id = :arac_id")->execute([':arac_id' => $aracId]);
        
        // İşlemleri tamamla
        $db->commit();
        
        // Başarılı mesajı ve yönlendirme
        header("Location: tur_detay.php?id=" . $turId . "&success=1");
        exit;
        
    } catch (PDOException $e) {
        // Hata durumunda işlemleri geri al
        $db->rollBack();
        $errorMessage = "Tur kaydedilirken bir hata oluştu: " . $e->getMessage();
        header("Location: tur_olustur.php?error=" . urlencode($errorMessage));
        exit;
    }
} else {
    // POST isteği değilse ana sayfaya yönlendir
    header("Location: index.php");
    exit;
}
