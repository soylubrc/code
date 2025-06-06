<?php
require_once 'config.php';

// İlleri getir
$iller_query = $db->query("SELECT * FROM iller ORDER BY il_adi ASC");
$iller = $iller_query->fetchAll(PDO::FETCH_ASSOC);

// Araçları getir
$araclar_query = $db->query("SELECT * FROM araclar WHERE durum = 'Aktif' ORDER BY plaka ASC");
$araclar = $araclar_query->fetchAll(PDO::FETCH_ASSOC);

// Şoförleri getir
$soforler_query = $db->query("SELECT * FROM soforler WHERE durum = 'Aktif' ORDER BY ad, soyad ASC");
$soforler = $soforler_query->fetchAll(PDO::FETCH_ASSOC);

// Personeli getir
$personel_query = $db->query("SELECT * FROM personel WHERE aktif = 1 ORDER BY ad, soyad ASC");
$personel = $personel_query->fetchAll(PDO::FETCH_ASSOC);

// Yük tiplerini getir
$yuk_tipleri_query = $db->query("SELECT * FROM yuk_tipleri ORDER BY tip_adi ASC");
$yuk_tipleri = $yuk_tipleri_query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tur Oluşturma</title>
    
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- favicon ============================================ -->
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .drag-container {
            min-height: 150px;
            border: 2px dashed #ccc;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        .drag-item {
            padding: 8px 12px;
            margin: 5px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: move;
            display: inline-block;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        .drag-item:hover {
            background-color: #f0f0f0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .drag-item.selected {
            background-color: #e7f3ff;
            border-color: #007bff;
        }
        .durak-item {
            background-color: #e7f7e7;
            border-left: 4px solid #28a745;
        }
        .arac-item {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .sofor-item {
            background-color: #cce5ff;
            border-left: 4px solid #007bff;
        }
        .personel-item {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .selected-container {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .select2-container {
            width: 100% !important;
        }
        .durak-sira {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #28a745;
        }
        .durak-sira .handle {
            cursor: move;
            margin-right: 10px;
            color: #6c757d;
        }
        .durak-sira .remove-durak {
            margin-left: auto;
            color: #dc3545;
            cursor: pointer;
        }
        .section-title {
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 15px;
            color: #0056b3;
        }
        
        .select2-container--default .select2-selection--multiple {
            min-height: 120px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #0d6efd;
            border: 1px solid #0d6efd;
            color: white;
            padding: 5px 10px;
            margin: 3px;
            border-radius: 15px;
            font-size: 14px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 8px;
            font-weight: bold;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #ffcccc;
        }
        
        .durak-counter {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .selected-duraks-preview {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            min-height: 60px;
        }
        
        .durak-preview-item {
            display: inline-block;
            background: #e3f2fd;
            border: 1px solid #2196f3;
            color: #1976d2;
            padding: 4px 8px;
            margin: 2px;
            border-radius: 12px;
            font-size: 12px;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <h2 class="text-center mb-4">
        <i class="fas fa-route me-2"></i>Yeni Tur Oluştur
    </h2>
    
    <form id="turForm" action="tur_kaydet.php" method="post">
        <div class="row">
            <!-- Sol Taraf - Seçim Alanları -->
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>Şubeler
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Çoklu Seçim Select Box -->
                        <label class="form-label">Durak Olarak Eklenecek Şubeler:</label>
                        <select id="ilSecimi" class="form-control select2-multiple" multiple="multiple">
                            <?php foreach ($iller as $il): ?>
                            <option value="<?= $il['il_id'] ?>"
                                     data-uzaklik="<?= $il['merkeze_uzaklik'] ?>"
                                    data-il-adi="<?= htmlspecialchars($il['il_adi']) ?>">
                                <?= $il['il_adi'] ?> (<?= $il['merkeze_uzaklik'] ?> km)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <!-- Seçilen Durakların Önizlemesi -->
                        <div class="selected-duraks-preview mt-3" id="durakOnizleme">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Seçilen şubeler durak olarak eklenecek
                            </small>
                        </div>
                        
                        <div class="d-grid gap-2 mt-3">
                            <button type="button" id="ilEkleBtn" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Seçilen Şubeleri Durak Olarak Ekle
                            </button>
                            <button type="button" id="tumunuSecBtn" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-check-double me-1"></i>Tümünü Seç
                            </button>
                            <button type="button" id="secimTemizleBtn" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Seçimi Temizle
                            </button>
                        </div>
                        
                        <!-- Durak Sayacı -->
                        <div class="mt-3 text-center">
                            <span class="durak-counter" id="durakSayaci">
                                <i class="fas fa-map-pin me-1"></i>
                                Toplam Durak: <span id="durakSayisiSpan">0</span>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-truck me-2"></i>Araçlar
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="drag-container" id="araclarContainer">
                            <?php foreach ($araclar as $arac): ?>
                            <div class="drag-item arac-item" data-id="<?= $arac['arac_id'] ?>" data-type="arac">
                                <i class="fas fa-truck me-2"></i><?= $arac['plaka'] ?> - <?= $arac['kapasite'] ?> kg
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-tie me-2"></i>Şoförler
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="drag-container" id="soforlerContainer">
                            <?php foreach ($soforler as $sofor): ?>
                            <div class="drag-item sofor-item" data-id="<?= $sofor['sofor_id'] ?>" data-type="sofor">
                                <i class="fas fa-user me-2"></i><?= $sofor['ad'] ?> <?= $sofor['soyad'] ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>Personel
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="drag-container" id="personelContainer">
                            <?php foreach ($personel as $p): ?>
                            <div class="drag-item personel-item" data-id="<?= $p['id'] ?>" data-type="personel">
                                <i class="fas fa-user-cog me-2"></i><?= $p['ad'] ?> <?= $p['soyad'] ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sağ Taraf - Seçilen Öğeler ve Form -->
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Tur Bilgileri
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cikisTarihi" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Çıkış Tarihi
                                </label>
                                <input type="date" class="form-control" id="cikisTarihi" name="cikisTarihi" required>
                            </div>
                            <div class="col-md-6">
                                <label for="cikisSaati" class="form-label">
                                    <i class="fas fa-clock me-1"></i>Çıkış Saati
                                </label>
                                <input type="time" class="form-control" id="cikisSaati" name="cikisSaati" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tahminiDonusTarihi" class="form-label">
                                    <i class="fas fa-calendar-check me-1"></i>Tahmini Dönüş Tarihi
                                </label>
                                <input type="date" class="form-control" id="tahminiDonusTarihi" name="tahminiDonusTarihi">
                            </div>
                            <div class="col-md-6">
                                <label for="tahminiDonusSaati" class="form-label">
                                    <i class="fas fa-clock me-1"></i>Tahmini Dönüş Saati
                                </label>
                                <input type="time" class="form-control" id="tahminiDonusSaati" name="tahminiDonusSaati">
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="nakitTahsilat" name="nakitTahsilat">
                            <label class="form-check-label" for="nakitTahsilat">
                                <i class="fas fa-money-bill-wave me-1"></i>Nakit Tahsilat Yapılacak
                            </label>
                        </div>
                        
                        <div id="nakitTutarDiv" class="mb-3" style="display: none;">
                            <label for="nakitTutar" class="form-label">
                                <i class="fas fa-lira-sign me-1"></i>Nakit Tahsilat Tutarı (TL)
                            </label>
                            <input type="number" step="0.01" class="form-control" id="nakitTutar" name="nakitTutar">
                        </div>
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-route me-2"></i>Duraklar
                            <span class="badge bg-light text-dark ms-2" id="durakBadge">0</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="durakListesi" class="mb-3">
                            <!-- Duraklar buraya eklenecek -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Henüz durak eklenmedi. Lütfen sol taraftan şube seçerek durak ekleyin.
                            </div>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-hand-paper me-2"></i>
                            <strong>Not:</strong> Durakları sürükleyerek sıralayabilirsiniz.
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">
                                    <i class="fas fa-truck me-2"></i>Seçilen Araç
                                </h5>
                            </div>
                            <div class="card-body selected-container" id="seciliArac">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Henüz araç seçilmedi. Lütfen sol taraftan bir araç sürükleyin.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-tie me-2"></i>Seçilen Şoför
                                </h5>
                            </div>
                            <div class="card-body selected-container" id="seciliSofor">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Henüz şoför seçilmedi. Lütfen sol taraftan bir şoför sürükleyin.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-cog me-2"></i>Tur Hazırlayan
                                </h5>
                            </div>
                            <div class="card-body selected-container" id="seciliHazirlayan">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Henüz tur hazırlayan seçilmedi. Lütfen sol taraftan bir personel sürükleyin.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-warehouse me-2"></i>Depo Sorumlusu
                                </h5>
                            </div>
                            <div class="card-body selected-container" id="seciliDepoSorumlusu">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Henüz depo sorumlusu seçilmedi. Lütfen sol taraftan bir personel sürükleyin.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>Tur Özeti
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="turOzeti">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Tur bilgilerini tamamladıktan sonra özet burada görüntülenecektir.
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" id="ozetGosterBtn" class="btn btn-primary">
                                <i class="fas fa-eye me-2"></i>Özeti Göster
                            </button>
                            <button type="submit" id="turKaydetBtn" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Turu Kaydet
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gizli input alanları -->
        <input type="hidden" id="seciliAracId" name="aracId">
        <input type="hidden" id="seciliSoforId" name="soforId">
        <input type="hidden" id="seciliHazirlayanId" name="hazirlayanId">
        <input type="hidden" id="seciliDepoSorumlusuId" name="depoSorumlusuId">
        <input type="hidden" id="duraklar" name="duraklar">
        <input type="hidden" id="toplamMesafe" name="toplamMesafe">
    </form>
    
    <!-- Durak Detay Modalı -->
    <div class="modal fade" id="durakDetayModal" tabindex="-1" aria-labelledby="durakDetayModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="durakDetayModalLabel">
                        <i class="fas fa-box me-2"></i>Durak Detayları
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="durakIndex">
                    <div class="mb-3">
                        <label for="yukTipi" class="form-label">
                            <i class="fas fa-tags me-1"></i>Yük Tipi
                        </label>
                        <select class="form-control" id="yukTipi">
                            <option value="">Yük Tipi Seçiniz</option>
                            <?php foreach ($yuk_tipleri as $yuk_tipi): ?>
                            <option value="<?= $yuk_tipi['yuk_tip_id'] ?>"><?= $yuk_tipi['tip_adi'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="yukMiktari" class="form-label">
                            <i class="fas fa-weight me-1"></i>Yük Miktarı (Ton)
                        </label>
                        <input type="number" step="0.01" class="form-control" id="yukMiktari">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>İptal
                    </button>
                    <button type="button" class="btn btn-primary" id="durakDetayKaydet">
                        <i class="fas fa-save me-1"></i>Kaydet
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<script>
    // Bugünün tarihini varsayılan olarak ayarla
    const tarihInput = document.getElementById('cikisTarihi');
    if(tarihInput) {
        const today = new Date();
        const yyyy = today.getFullYear();
        let mm = today.getMonth() + 1; // Ay 0-11 arası
        let dd = today.getDate();
        
        if (dd < 10) dd = '0' + dd;
        if (mm < 10) mm = '0' + mm;
        
        const formattedToday = yyyy + '-' + mm + '-' + dd;
        tarihInput.value = formattedToday;
    }
</script>

<script>
    $(document).ready(function() {
        // Select2 başlat - Çoklu seçim için
        $('.select2-multiple').select2({
            placeholder: "Durak olarak eklenecek şubeleri seçiniz...",
            allowClear: true,
            width: '100%',
            templateResult: function(option) {
                if (!option.id) return option.text;
                
                var $option = $(
                    '<span><i class="fas fa-map-marker-alt me-2"></i>' + option.text + '</span>'
                );
                return $option;
            },
            templateSelection: function(option) {
                if (!option.id) return option.text;
                
                return $('<span><i class="fas fa-map-pin me-1"></i>' + $(option.element).data('il-adi') + '</span>');
            }
        });
        
        // Duraklar dizisi
        let duraklar = [];
        
        // Durak önizlemesini güncelle
        function durakOnizlemesiniGuncelle() {
            const secilenIller = $('#ilSecimi').val();
            const onizlemeDiv = $('#durakOnizleme');
            
            if (!secilenIller || secilenIller.length === 0) {
                onizlemeDiv.html(`
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Seçilen şubeler durak olarak eklenecek
                    </small>
                `);
                return;
            }
            
            let html = '<div class="mb-2"><small class="text-success"><strong>Seçilen Şubeler:</strong></small></div>';
            secilenIller.forEach(ilId => {
                const option = $('#ilSecimi option[value="' + ilId + '"]');
                const ilAdi = option.data('il-adi');
                const uzaklik = option.data('uzaklik');
                html += `<span class="durak-preview-item">${ilAdi} (${uzaklik} km)</span>`;
            });
            
            onizlemeDiv.html(html);
        }
        
        // Select2 değişikliklerini dinle
        $('#ilSecimi').on('change', function() {
            durakOnizlemesiniGuncelle();
        });
        
        // Tümünü seç butonu
        $('#tumunuSecBtn').click(function() {
            $('#ilSecimi option').prop('selected', true);
            $('#ilSecimi').trigger('change');
        });
        
        // Seçimi temizle butonu
        $('#secimTemizleBtn').click(function() {
            $('#ilSecimi').val(null).trigger('change');
        });
        
        // Nakit tahsilat checkbox kontrolü
        $('#nakitTahsilat').change(function() {
            if($(this).is(':checked')) {
                $('#nakitTutarDiv').show();
            } else {
                $('#nakitTutarDiv').hide();
                $('#nakitTutar').val('');
            }
        });
        
        // Çoklu durak ekleme butonu
        $('#ilEkleBtn').click(function() {
            const secilenIller = $('#ilSecimi').val();
            
            if (!secilenIller || secilenIller.length === 0) {
                alert('Lütfen en az bir şube seçiniz!');
                return;
            }
            
            // Seçilen illeri durak olarak ekle
            secilenIller.forEach(ilId => {
                const option = $('#ilSecimi option[value="' + ilId + '"]');
                const ilAdi = option.data('il-adi');
                const uzaklik = option.data('uzaklik');
                
                // Zaten eklenmiş mi kontrol et
                const mevcutDurak = duraklar.find(d => d.il_id == ilId);
                if (!mevcutDurak) {
                    durakEkle(ilId, ilAdi, uzaklik);
                }
            });
            
            // Seçimi temizle
            $('#ilSecimi').val(null).trigger('change');
            durakOnizlemesiniGuncelle();
        });
        
        // Durak ekleme fonksiyonu
        function durakEkle(ilId, ilAdi, uzaklik) {
            const durakIndex = duraklar.length;
            const durak = {
                il_id: ilId,
                il_adi: ilAdi,
                uzaklik: uzaklik,
                yuk_tip_id: '',
                yuk_miktari: 0,
                sira: durakIndex + 1
            };
            
            duraklar.push(durak);
            
            const durakHtml = `
                <div class="durak-item" data-index="${durakIndex}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="durak-info">
                            <span class="durak-sira badge bg-primary me-2">${durak.sira}</span>
                            <strong>${ilAdi}</strong>
                            <small class="text-muted ms-2">(${uzaklik} km)</small>
                        </div>
                        <div class="durak-actions">
                            <button type="button" class="btn btn-sm btn-outline-info durak-detay-btn" data-index="${durakIndex}">
                                <i class="fas fa-edit"></i> Detay
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger durak-sil-btn" data-index="${durakIndex}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="durak-yuk-info mt-2" id="durakYukInfo${durakIndex}">
                        <small class="text-muted">Yük bilgisi eklenmedi</small>
                    </div>
                </div>
            `;
            
            if (duraklar.length === 1) {
                $('#durakListesi').html(durakHtml);
            } else {
                $('#durakListesi').append(durakHtml);
            }
            
            durakBadgeGuncelle();
            gizliInputlariGuncelle();
        }
        
        // Durak badge güncelleme
        function durakBadgeGuncelle() {
            $('#durakBadge').text(duraklar.length);
        }
        
        // Durak silme
        $(document).on('click', '.durak-sil-btn', function() {
            const index = $(this).data('index');
            duraklar.splice(index, 1);
            durakListesiniYenile();
        });
        
        // Durak detay butonu
        $(document).on('click', '.durak-detay-btn', function() {
            const index = $(this).data('index');
            const durak = duraklar[index];
            
            $('#durakIndex').val(index);
            $('#yukTipi').val(durak.yuk_tip_id);
            $('#yukMiktari').val(durak.yuk_miktari);
            
            $('#durakDetayModal').modal('show');
        });
        
        // Durak detay kaydet
        $('#durakDetayKaydet').click(function() {
            const index = $('#durakIndex').val();
            const yukTipi = $('#yukTipi').val();
            const yukMiktari = $('#yukMiktari').val();
            
            duraklar[index].yuk_tip_id = yukTipi;
            duraklar[index].yuk_miktari = yukMiktari;
            
            // Yük bilgisini güncelle
            const yukTipiText = yukTipi ? $('#yukTipi option:selected').text() : 'Belirtilmedi';
            const yukMiktariText = yukMiktari ? yukMiktari + ' Ton' : '0 Ton';
            
            $(`#durakYukInfo${index}`).html(`
                <small class="text-success">
                    <i class="fas fa-box me-1"></i>${yukTipiText} - ${yukMiktariText}
                </small>
            `);
            
            $('#durakDetayModal').modal('hide');
            gizliInputlariGuncelle();
        });
        
        // Durak listesini yenileme
        function durakListesiniYenile() {
            $('#durakListesi').empty();
            
            if (duraklar.length === 0) {
                $('#durakListesi').html(`
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Henüz durak eklenmedi. Lütfen sol taraftan şube seçerek durak ekleyin.
                    </div>
                `);
            } else {
                duraklar.forEach((durak, index) => {
                    durak.sira = index + 1;
                    const durakHtml = `
                        <div class="durak-item" data-index="${index}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="durak-info">
                                    <span class="durak-sira badge bg-primary me-2">${durak.sira}</span>
                                    <strong>${durak.il_adi}</strong>
                                    <small class="text-muted ms-2">(${durak.uzaklik} km)</small>
                                </div>
                                <div class="durak-actions">
                                    <button type="button" class="btn btn-sm btn-outline-info durak-detay-btn" data-index="${index}">
                                        <i class="fas fa-edit"></i> Detay
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger durak-sil-btn" data-index="${index}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="durak-yuk-info mt-2" id="durakYukInfo${index}">
                                ${durak.yuk_tip_id ? 
                                    `<small class="text-success"><i class="fas fa-box me-1"></i>${$('#yukTipi option[value="' + durak.yuk_tip_id + '"]').text()} - ${durak.yuk_miktari} Ton</small>` : 
                                    '<small class="text-muted">Yük bilgisi eklenmedi</small>'
                                }
                            </div>
                        </div>
                    `;
                    $('#durakListesi').append(durakHtml);
                });
            }
            
            durakBadgeGuncelle();
            gizliInputlariGuncelle();
        }
        
        // Durak sıralama (sortable)
        $('#durakListesi').sortable({
            handle: '.durak-sira',
            update: function(event, ui) {
                // Sıralama değiştiğinde duraklar dizisini güncelle
                const yeniSira = [];
                $('.durak-item').each(function() {
                    const index = $(this).data('index');
                    yeniSira.push(duraklar[index]);
                });
                duraklar = yeniSira;
                durakListesiniYenile();
            }
        });
        
        // Drag & Drop işlemleri
        $('.draggable-item').draggable({
            helper: 'clone',
            revert: 'invalid',
            zIndex: 1000
        });
        
        // Araç seçimi
        $('#seciliArac').droppable({
            accept: '.arac-item',
            drop: function(event, ui) {
                const aracId = ui.draggable.data('id');
                const aracPlaka = ui.draggable.find('.item-title').text();
                const aracModel = ui.draggable.find('.item-subtitle').text();
                
                $('#seciliAracId').val(aracId);
                $(this).html(`
                    <div class="selected-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${aracPlaka}</strong><br>
                                <small class="text-muted">${aracModel}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-selection">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `);
            }
        });
        
        // Şoför seçimi
        $('#seciliSofor').droppable({
            accept: '.sofor-item',
            drop: function(event, ui) {
                const soforId = ui.draggable.data('id');
                const soforAdi = ui.draggable.find('.item-title').text();
                const soforTelefon = ui.draggable.find('.item-subtitle').text();
                
                $('#seciliSoforId').val(soforId);
                $(this).html(`
                    <div class="selected-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${soforAdi}</strong><br>
                                <small class="text-muted">${soforTelefon}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-selection">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `);
            }
        });
        
        // Tur hazırlayan seçimi
        $('#seciliHazirlayan').droppable({
            accept: '.personel-item',
            drop: function(event, ui) {
                const personelId = ui.draggable.data('id');
                const personelAdi = ui.draggable.find('.item-title').text();
                const personelPozisyon = ui.draggable.find('.item-subtitle').text();
                
                $('#seciliHazirlayanId').val(personelId);
                $(this).html(`
                    <div class="selected-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${personelAdi}</strong><br>
                                <small class="text-muted">${personelPozisyon}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-selection">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `);
            }
        });
        
        // Depo sorumlusu seçimi
        $('#seciliDepoSorumlusu').droppable({
            accept: '.personel-item',
            drop: function(event, ui) {
                const personelId = ui.draggable.data('id');
                const personelAdi = ui.draggable.find('.item-title').text();
                const personelPozisyon = ui.draggable.find('.item-subtitle').text();
                
                $('#seciliDepoSorumlusuId').val(personelId);
                $(this).html(`
                    <div class="selected-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${personelAdi}</strong><br>
                                <small class="text-muted">${personelPozisyon}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-selection">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `);
            }
        });
        
        // Seçim kaldırma
        $(document).on('click', '.remove-selection', function() {
            const container = $(this).closest('.selected-container');
            const containerId = container.attr('id');
            
            // İlgili hidden input'u temizle
            if (containerId === 'seciliArac') {
                $('#seciliAracId').val('');
                container.html(`
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Henüz araç seçilmedi. Lütfen sol taraftan bir araç sürükleyin.
                    </div>
                `);
            } else if (containerId === 'seciliSofor') {
                $('#seciliSoforId').val('');
                container.html(`
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Henüz şoför seçilmedi. Lütfen sol taraftan bir şoför sürükleyin.
                    </div>
                `);
            } else if (containerId === 'seciliHazirlayan') {
                $('#seciliHazirlayanId').val('');
                container.html(`
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Henüz tur hazırlayan seçilmedi. Lütfen sol taraftan bir personel sürükleyin.
                    </div>
                `);
            } else if (containerId === 'seciliDepoSorumlusu') {
                $('#seciliDepoSorumlusuId').val('');
                container.html(`
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Henüz depo sorumlusu seçilmedi. Lütfen sol taraftan bir personel sürükleyin.
                    </div>
                `);
            }
        });
        
        // Gizli inputları güncelleme
        function gizliInputlariGuncelle() {
            $('#duraklar').val(JSON.stringify(duraklar));
            
            // Toplam mesafeyi hesapla
            let toplamMesafe = 0;
            duraklar.forEach(durak => {
                toplamMesafe += parseFloat(durak.uzaklik) || 0;
            });
            $('#toplamMesafe').val(toplamMesafe);
        }
        
        // Özet göster butonu
        $('#ozetGosterBtn').click(function() {
            turOzetiniGoster();
        });
        
        // Tur özetini göster
        function turOzetiniGoster() {
            const turAdi = $('#turAdi').val();
            const cikisTarihi = $('#cikisTarihi').val();
            const cikisSaati = $('#cikisSaati').val();
            const tahminiDonusTarihi = $('#tahminiDonusTarihi').val();
            const tahminiDonusSaati = $('#tahminiDonusSaati').val();
            const nakitTahsilat = $('#nakitTahsilat').is(':checked');
            const nakitTutar = $('#nakitTutar').val();
            
            const aracId = $('#seciliAracId').val();
            const soforId = $('#seciliSoforId').val();
            const hazirlayanId = $('#seciliHazirlayanId').val();
            const depoSorumlusuId = $('#seciliDepoSorumlusuId').val();
            
            let ozetHtml = '<div class="row">';
            
            // Temel bilgiler
            ozetHtml += `
                <div class="col-md-6">
                    <h6><i class="fas fa-info-circle me-2"></i>Temel Bilgiler</h6>
                    <ul class="list-unstyled">
                        <li><strong>Tur Adı:</strong> ${turAdi || 'Belirtilmedi'}</li>
                        <li><strong>Çıkış:</strong> ${cikisTarihi || 'Belirtilmedi'} ${cikisSaati || ''}</li>
                        <li><strong>Dönüş:</strong> ${tahminiDonusTarihi || 'Belirtilmedi'} ${tahminiDonusSaati || ''}</li>
                        <li><strong>Nakit Tahsilat:</strong> ${nakitTahsilat ? 'Evet (' + (nakitTutar || '0') + ' TL)' : 'Hayır'}</li>
                    </ul>
                </div>
            `;
            
            // Seçimler
            ozetHtml += `
                <div class="col-md-6">
                    <h6><i class="fas fa-users me-2"></i>Seçimler</h6>
                    <ul class="list-unstyled">
                        <li><strong>Araç:</strong> ${aracId ? 'Seçildi' : '<span class="text-danger">Seçilmedi</span>'}</li>
                        <li><strong>Şoför:</strong> ${soforId ? 'Seçildi' : '<span class="text-danger">Seçilmedi</span>'}</li>
                        <li><strong>Tur Hazırlayan:</strong> ${hazirlayanId ? 'Seçildi' : '<span class="text-danger">Seçilmedi</span>'}</li>
                        <li><strong>Depo Sorumlusu:</strong> ${depoSorumlusuId ? 'Seçildi' : '<span class="text-danger">Seçilmedi</span>'}</li>
                    </ul>
                </div>
            `;
            
            ozetHtml += '</div>';
            
            // Duraklar
            if (duraklar.length > 0) {
                let toplamMesafe = 0;
                let toplamYuk = 0;
                
                ozetHtml += `
                    <div class="mt-3">
                        <h6><i class="fas fa-route me-2"></i>Duraklar (${duraklar.length} adet)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Sıra</th>
                                        <th>Şehir</th>
                                        <th>Mesafe (km)</th>
                                        <th>Yük Tipi</th>
                                        <th>Miktar (Ton)</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;
                
                duraklar.forEach((durak, index) => {
                    const yukTipiText = durak.yuk_tip_id ? $('#yukTipi option[value="' + durak.yuk_tip_id + '"]').text() : 'Belirtilmedi';
                    toplamMesafe += parseFloat(durak.uzaklik) || 0;
                    toplamYuk += parseFloat(durak.yuk_miktari) || 0;
                    
                    ozetHtml += `
                        <tr>
                            <td><span class="badge bg-primary">${index + 1}</span></td>
                            <td>${durak.il_adi}</td>
                            <td>${durak.uzaklik}</td>
                            <td>${yukTipiText}</td>
                            <td>${durak.yuk_miktari || 0}</td>
                        </tr>
                    `;
                });
                
                ozetHtml += `
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <th colspan="2">Toplam</th>
                                        <th>${toplamMesafe.toFixed(2)} km</th>
                                        <th>-</th>
                                        <th>${toplamYuk.toFixed(2)} Ton</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                `;
            } else {
                ozetHtml += `
                    <div class="mt-3">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Henüz durak eklenmedi!
                        </div>
                    </div>
                `;
            }
            
            $('#turOzeti').html(ozetHtml);
        }
        
        // Form gönderimi
        $('#turForm').submit(function(e) {
            e.preventDefault();
            
            // Validasyon
            const turAdi = $('#turAdi').val().trim();
            const cikisTarihi = $('#cikisTarihi').val();
            const aracId = $('#seciliAracId').val();
            const soforId = $('#seciliSoforId').val();
            
            if (!turAdi) {
                alert('Lütfen tur adını giriniz!');
                $('#turAdi').focus();
                return;
            }
            
            if (!cikisTarihi) {
                alert('Lütfen çıkış tarihini seçiniz!');
                $('#cikisTarihi').focus();
                return;
            }
            
            if (!aracId) {
                alert('Lütfen bir araç seçiniz!');
                return;
            }
            
            if (!soforId) {
                alert('Lütfen bir şoför seçiniz!');
                return;
            }
            
            if (duraklar.length === 0) {
                alert('Lütfen en az bir durak ekleyiniz!');
                return;
            }
            
            // Nakit tahsilat kontrolü
            const nakitTahsilat = $('#nakitTahsilat').is(':checked');
            if (nakitTahsilat) {
                const nakitTutar = $('#nakitTutar').val();
                if (!nakitTutar || parseFloat(nakitTutar) <= 0) {
                    alert('Nakit tahsilat seçildiğinde tutar girilmelidir!');
                    $('#nakitTutar').focus();
                    return;
                }
            }
            
            // Gizli inputları güncelle
            gizliInputlariGuncelle();
            
            // Onay al
            if (confirm('Tur kaydedilsin mi?')) {
                // Form verilerini hazırla
                const formData = new FormData(this);
                
                // AJAX ile gönder
                $.ajax({
                    url: 'tur_kaydet.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                alert('Tur başarıyla kaydedildi!');
                                window.location.href = 'turlar.php';
                            } else {
                                alert('Hata: ' + result.message);
                            }
                        } catch (e) {
                            alert('Beklenmeyen bir hata oluştu!');
                            console.error('Response:', response);
                        }
                    },
                    error: function() {
                        alert('Sunucu hatası oluştu!');
                    }
                });
            }
        });
        
        // Sayfa yüklendiğinde özeti göster
        setTimeout(function() {
            turOzetiniGoster();
        }, 500);
    });
</script>

<style>
    .draggable-item {
        cursor: move;
        transition: all 0.3s ease;
    }
    
    .draggable-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .selected-container {
        min-height: 80px;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .selected-container:hover {
        border-color: #007bff;
        background-color: #f8f9fa;
    }
    
    .selected-item {
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
    }
    
    .durak-item {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    
    .durak-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-color: #007bff;
    }
    
    .durak-sira {
        cursor: move;
    }
    
    .durak-preview-item {
        display: inline-block;
        background: #e3f2fd;
        color: #1976d2;
        padding: 4px 8px;
        border-radius: 4px;
        margin: 2px;
        font-size: 0.875rem;
    }
    
    .sidebar-section {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .sidebar-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
    }
    
    .sidebar-content {
        padding: 15px;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .item-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }
    
    .item-card:hover {
        background: #e3f2fd;
        border-color: #2196f3;
        transform: translateX(5px);
    }
    
    .item-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }
    
    .item-subtitle {
        font-size: 0.875rem;
        color: #666;
    }
    
    .ui-sortable-helper {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: rotate(2deg);
    }
    
    .card-header {
        font-weight: 600;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    
    .btn {
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
    }
    
    .alert {
        border: none;
        border-radius: 8px;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        background-color: #f8f9fa;
    }
    
    .badge {
        font-size: 0.875rem;
    }
    
    @media (max-width: 768px) {
        .row {
            margin: 0;
        }
        
        .col-md-3, .col-md-9 {
            padding: 5px;
        }
        
        .sidebar-content {
            max-height: 200px;
        }
        
        .durak-item .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
        }
        
        .durak-actions {
            margin-top: 10px;
        }
    }
</style>

