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
            user-select: none;
        }
        
        .drag-item:hover {
            background-color: #f0f0f0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transform: translateY(-2px);
        }
        
        .drag-item.ui-draggable-dragging {
            transform: rotate(5deg);
            z-index: 1000;
        }
        
        .selected-container {
            background-color: #ffffff;
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            min-height: 100px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .selected-container.ui-droppable-hover {
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
        
        .item-title {
            font-weight: 600;
            color: #333;
            display: block;
        }
        
        .item-subtitle {
            font-size: 0.875rem;
            color: #666;
            display: block;
        }
        
        /* Diğer stiller aynı kalacak... */
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
                <!-- Şubeler Kartı - Aynı kalacak -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>Şubeler
                        </h5>
                    </div>
                    <div class="card-body">
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
                        
                        <div class="mt-3 text-center">
                            <span class="durak-counter" id="durakSayaci">
                                <i class="fas fa-map-pin me-1"></i>
                                Toplam Durak: <span id="durakSayisiSpan">0</span>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Araçlar Kartı - DÜZELTİLDİ -->
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-truck me-2"></i>Araçlar
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="drag-container" id="araclarContainer">
                            <?php foreach ($araclar as $arac): ?>
                            <div class="drag-item arac-item draggable-item" 
                                 data-id="<?= $arac['arac_id'] ?>" 
                                 data-type="arac"
                                 data-plaka="<?= htmlspecialchars($arac['plaka']) ?>"
                                 data-kapasite="<?= htmlspecialchars($arac['kapasite']) ?>">
                                <span class="item-title">
                                    <i class="fas fa-truck me-2"></i><?= $arac['plaka'] ?>
                                </span>
                                <span class="item-subtitle">
                                    Kapasite: <?= $arac['kapasite'] ?> kg
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Şoförler Kartı - DÜZELTİLDİ -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-tie me-2"></i>Şoförler
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="drag-container" id="soforlerContainer">
                            <?php foreach ($soforler as $sofor): ?>
                            <div class="drag-item sofor-item draggable-item" 
                                 data-id="<?= $sofor['sofor_id'] ?>" 
                                 data-type="sofor"
                                 data-ad="<?= htmlspecialchars($sofor['ad']) ?>"
                                 data-soyad="<?= htmlspecialchars($sofor['soyad']) ?>"
                                 data-telefon="<?= htmlspecialchars($sofor['telefon'] ?? '') ?>">
                                <span class="item-title">
                                    <i class="fas fa-user me-2"></i><?= $sofor['ad'] ?> <?= $sofor['soyad'] ?>
                                </span>
                                <span class="item-subtitle">
                                    <?= $sofor['telefon'] ?? 'Telefon belirtilmemiş' ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Personel Kartı - DÜZELTİLDİ -->
                <div class="card mb-3">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>Personel
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="drag-container" id="personelContainer">
                            <?php foreach ($personel as $p): ?>
                            <div class="drag-item personel-item draggable-item" 
                                 data-id="<?= $p['id'] ?>" 
                                 data-type="personel"
                                 data-ad="<?= htmlspecialchars($p['ad']) ?>"
                                 data-soyad="<?= htmlspecialchars($p['soyad']) ?>"
                                 data-pozisyon="<?= htmlspecialchars($p['pozisyon'] ?? 'Personel') ?>">
                                <span class="item-title">
                                    <i class="fas fa-user-cog me-2"></i><?= $p['ad'] ?> <?= $p['soyad'] ?>
                                </span>
                                <span class="item-subtitle">
                                    <?= $p['pozisyon'] ?? 'Personel' ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sağ Taraf - Form alanları aynı kalacak -->
            <div class="col-md-8">
                <!-- Tur Bilgileri -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Tur Bilgileri
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="turAdi" class="form-label">
                                    <i class="fas fa-tag me-1"></i>Tur Adı
                                </label>
                                <input type="text" class="form-control" id="turAdi" name="turAdi" required>
                            </div>
                            <div class="col-md-6">
                                <label for="cikisTarihi" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Çıkış Tarihi
                                </label>
                                <input type="date" class="form-control" id="cikisTarihi" name="cikisTarihi" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cikisSaati" class="form-label">
                                    <i class="fas fa-clock me-1"></i>Çıkış Saati
                                </label>
                                <input type="time" class="form-control" id="cikisSaati" name="cikisSaati" required>
                            </div>
                            <div class="col-md-6">
                                <label for="yukTipi" class="form-label">
                                    <i class="fas fa-boxes me-1"></i>Yük Tipi
                                </label>
                                <select class="form-control" id="yukTipi" name="yukTipi" required>
                                    <option value="">Yük Tipi Seçin</option>
                                    <?php foreach ($yuk_tipleri as $yuk): ?>
                                    <option value="<?= $yuk['tip_id'] ?>"><?= htmlspecialchars($yuk['tip_adi']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="aciklama" class="form-label">
                                <i class="fas fa-comment me-1"></i>Açıklama
                            </label>
                            <textarea class="form-control" id="aciklama" name="aciklama" rows="3" placeholder="Tur hakkında ek bilgiler..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Seçilen Araç -->
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-truck me-2"></i>Seçilen Araç
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="selected-container" id="secilenAracContainer" data-type="arac">
                            <div class="text-center text-muted">
                                <i class="fas fa-truck fa-2x mb-2"></i>
                                <p>Araç seçmek için sol taraftan sürükleyip bırakın</p>
                            </div>
                        </div>
                        <input type="hidden" id="secilenAracId" name="secilenAracId">
                    </div>
                </div>

                <!-- Seçilen Şoför -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-tie me-2"></i>Seçilen Şoför
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="selected-container" id="secilenSoforContainer" data-type="sofor">
                            <div class="text-center text-muted">
                                <i class="fas fa-user-tie fa-2x mb-2"></i>
                                <p>Şoför seçmek için sol taraftan sürükleyip bırakın</p>
                            </div>
                        </div>
                        <input type="hidden" id="secilenSoforId" name="secilenSoforId">
                    </div>
                </div>

                <!-- Seçilen Personel -->
                <div class="card mb-3">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>Seçilen Personel
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="selected-container" id="secilenPersonelContainer" data-type="personel">
                            <div class="text-center text-muted">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <p>Personel seçmek için sol taraftan sürükleyip bırakın (Çoklu seçim yapılabilir)</p>
                            </div>
                        </div>
                        <input type="hidden" id="secilenPersonelIds" name="secilenPersonelIds">
                    </div>
                </div>

                <!-- Duraklar -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>Tur Durakları
                        </h5>
                        <span class="badge bg-light text-dark" id="durakSayisiBadge">0 Durak</span>
                    </div>
                    <div class="card-body">
                        <div id="durakListesi" class="mb-3">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-map-marker-alt fa-2x mb-2"></i>
                                <p>Henüz durak eklenmedi. Sol taraftan şube seçerek durak ekleyebilirsiniz.</p>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="button" id="duraklariSiralaBtn" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-sort me-1"></i>Uzaklığa Göre Sırala
                                </button>
                                <button type="button" id="duraklariTemizleBtn" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash me-1"></i>Tümünü Temizle
                                </button>
                            </div>
                            <div>
                                <span class="text-muted small">
                                    <i class="fas fa-arrows-alt me-1"></i>Sürükleyerek sıralayabilirsiniz
                                </span>
                            </div>
                        </div>
                        
                        <input type="hidden" id="durakSirasi" name="durakSirasi">
                    </div>
                </div>

                <!-- Form Butonları -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                <i class="fas fa-arrow-left me-2"></i>Geri Dön
                            </button>
                            
                            <div>
                                <button type="button" id="onizlemeBtn" class="btn btn-info me-2">
                                    <i class="fas fa-eye me-2"></i>Önizleme
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>Turu Kaydet
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Önizleme Modal -->
<div class="modal fade" id="onizlemeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Tur Önizlemesi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="onizlemeIcerik">
                <!-- Önizleme içeriği buraya gelecek -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <button type="button" class="btn btn-success" onclick="$('#turForm').submit()">
                    <i class="fas fa-save me-2"></i>Turu Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
$(document).ready(function() {
    // Select2 başlatma
    $('.select2-multiple').select2({
        placeholder: "Şube seçin...",
        allowClear: true,
        width: '100%'
    });

    // Değişkenler
    let secilenDuraklar = [];
    let secilenPersonelIds = [];

    // Drag & Drop işlemleri
    $('.draggable-item').draggable({
        helper: 'clone',
        revert: 'invalid',
        zIndex: 1000,
        start: function(event, ui) {
            $(this).addClass('ui-draggable-dragging');
        },
        stop: function(event, ui) {
            $(this).removeClass('ui-draggable-dragging');
        }
    });

    // Drop alanları
    $('.selected-container').droppable({
        accept: function(draggable) {
            const containerType = $(this).data('type');
            const itemType = draggable.data('type');
            
            // Personel için çoklu seçim, diğerleri için tekli
            if (containerType === 'personel') {
                return itemType === 'personel';
            } else {
                return containerType === itemType && $(this).find('.selected-item').length === 0;
            }
        },
        hoverClass: 'ui-droppable-hover',
        drop: function(event, ui) {
            const $container = $(this);
            const $item = ui.draggable;
            const containerType = $container.data('type');
            
            if (containerType === 'personel') {
                // Personel için çoklu seçim
                handlePersonelDrop($container, $item);
            } else {
                // Araç ve şoför için tekli seçim
                handleSingleDrop($container, $item, containerType);
            }
        }
    });

    // Tekli seçim işlemi (Araç, Şoför)
    function handleSingleDrop($container, $item, type) {
        $container.empty();
        
        const itemData = $item.data();
        let content = '';
        
        if (type === 'arac') {
            content = `
                <div class="selected-item arac-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="item-title">
                                <i class="fas fa-truck me-2"></i>${itemData.plaka}
                            </span>
                            <span class="item-subtitle">
                                Kapasite: ${itemData.kapasite} kg
                            </span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#secilenAracId').val(itemData.id);
        } else if (type === 'sofor') {
            content = `
                <div class="selected-item sofor-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="item-title">
                                <i class="fas fa-user-tie me-2"></i>${itemData.ad} ${itemData.soyad}
                            </span>
                            <span class="item-subtitle">
                                ${itemData.telefon || 'Telefon belirtilmemiş'}
                            </span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#secilenSoforId').val(itemData.id);
        }
        
        $container.html(content);
    }

    // Personel çoklu seçim işlemi
    function handlePersonelDrop($container, $item) {
        const itemData = $item.data();
        const personelId = itemData.id;
        
        // Zaten seçili mi kontrol et
        if (secilenPersonelIds.includes(personelId)) {
            showAlert('Bu personel zaten seçilmiş!', 'warning');
            return;
        }
        
        // İlk personel ise container'ı temizle
        if (secilenPersonelIds.length === 0) {
            $container.empty();
        }
        
        secilenPersonelIds.push(personelId);
        
        const personelItem = `
            <div class="selected-item personel-item mb-2" data-personel-id="${personelId}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="item-title">
                            <i class="fas fa-user-cog me-2"></i>${itemData.ad} ${itemData.soyad}
                        </span>
                        <span class="item-subtitle">
                            ${itemData.pozisyon}
                        </span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-personel" data-personel-id="${personelId}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        
        $container.append(personelItem);
        $('#secilenPersonelIds').val(secilenPersonelIds.join(','));
    }

    // Seçilen öğeleri kaldırma
    $(document).on('click', '.remove-item', function() {
        const $container = $(this).closest('.selected-container');
        const containerType = $container.data('type');
        
        // Container'ı temizle ve varsayılan mesajı göster
        let defaultContent = '';
        if (containerType === 'arac') {
            defaultContent = `
                <div class="text-center text-muted">
                    <i class="fas fa-truck fa-2x mb-2"></i>
                    <p>Araç seçmek için sol taraftan sürükleyip bırakın</p>
                </div>
            `;
            $('#secilenAracId').val('');
        } else if (containerType === 'sofor') {
            defaultContent = `
                <div class="text-center text-muted">
                    <i class="fas fa-user-tie fa-2x mb-2"></i>
                    <p>Şoför seçmek için sol taraftan sürükleyip bırakın</p>
                </div>
            `;
            $('#secilenSoforId').val('');
        }
        
        $container.html(defaultContent);
    });

    // Personel kaldırma
    $(document).on('click', '.remove-personel', function() {
        const personelId = parseInt($(this).data('personel-id'));
        const $container = $('#secilenPersonelContainer');
        
        // Array'den kaldır
        secilenPersonelIds = secilenPersonelIds.filter(id => id !== personelId);
        
        // DOM'dan kaldır
        $(this).closest('.selected-item').remove();
        
        // Hidden input'u güncelle
        $('#secilenPersonelIds').val(secilenPersonelIds.join(','));
        
        // Eğer hiç personel kalmadıysa varsayılan mesajı göster
        if (secilenPersonelIds.length === 0) {
            $container.html(`
                <div class="text-center text-muted">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <p>Personel seçmek için sol taraftan sürükleyip bırakın (Çoklu seçim yapılabilir)</p>
                </div>
            `);
        }
    });

    // Şube/Durak işlemleri
    $('#ilEkleBtn').click(function() {
        const secilenIller = $('#ilSecimi').val();
        if (!secilenIller || secilenIller.length === 0) {
            showAlert('Lütfen en az bir şube seçin!', 'warning');
            return;
        }
        
        secilenIller.forEach(function(ilId) {
            const option = $(`#ilSecimi option[value="${ilId}"]`);
            const ilAdi = option.data('il-adi');
            const uzaklik = option.data('uzaklik');
            
            // Zaten ekli mi kontrol et
            if (!secilenDuraklar.find(d => d.id == ilId)) {
                secilenDuraklar.push({
                    id: ilId,
                    adi: ilAdi,
                    uzaklik: uzaklik
                });
            }
        });
        
        durakListesiniGuncelle();
        durakSayaciniGuncelle();
        $('#ilSecimi').val(null).trigger('change');
    });

    // Tümünü seç
    $('#tumunuSecBtn').click(function() {
        $('#ilSecimi option').prop('selected', true);
        $('#ilSecimi').trigger('change');
    });

    // Seçimi temizle
    $('#secimTemizleBtn').click(function() {
        $('#ilSecimi').val(null).trigger('change');
    });

    // Durakları temizle
    $('#duraklariTemizleBtn').click(function() {
        if (secilenDuraklar.length === 0) {
            showAlert('Temizlenecek durak bulunmuyor!', 'info');
            return;
        }
        
        if (confirm('Tüm durakları kaldırmak istediğinizden emin misiniz?')) {
            secilenDuraklar = [];
            durakListesiniGuncelle();
            durakSayaciniGuncelle();
        }
    });

    // Uzaklığa göre sırala
    $('#duraklariSiralaBtn').click(function() {
        if (secilenDuraklar.length < 2) {
            showAlert('Sıralamak için en az 2 durak gerekli!', 'info');
            return;
        }
        
        secilenDuraklar.sort((a, b) => a.uzaklik - b.uzaklik);
        durakListesiniGuncelle();
        showAlert('Duraklar uzaklığa göre sıralandı!', 'success');
    });

    // Durak listesini güncelle
    function durakListesiniGuncelle() {
        const $durakListesi = $('#durakListesi');
        
        if (secilenDuraklar.length === 0) {
            $durakListesi.html(`
                <div class="text-center text-muted py-4">
                    <i class="fas fa-map-marker-alt fa-2x mb-2"></i>
                    <p>Henüz durak eklenmedi. Sol taraftan şube seçerek durak ekleyebilirsiniz.</p>
                </div>
            `);
            return;
        }
        
        let html = '<div id="sortableDuraklar">';
        secilenDuraklar.forEach(function(durak, index) {
            html += `
                <div class="durak-item" data-durak-id="${durak.id}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary me-2">${index + 1}</span>
                            <div>
                                <span class="item-title">
                                    <i class="fas fa-map-marker-alt me-2"></i>${durak.adi}
                                </span>
                                <span class="item-subtitle">
                                    Merkeze uzaklık: ${durak.uzaklik} km
                                </span>
                            </div>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary me-2 drag-handle">
                                <i class="fas fa-grip-vertical"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-durak" data-durak-id="${durak.id}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        $durakListesi.html(html);
        
        // Sortable özelliği ekle
        new Sortable(document.getElementById('sortableDuraklar'), {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function(evt) {
                // Sıralamayı güncelle
                const yeniSira = [];
                $('#sortableDuraklar .durak-item').each(function() {
                    const durakId = $(this).data('durak-id');
                    const durak = secilenDuraklar.find(d => d.id == durakId);
                    if (durak) {
                        yeniSira.push(durak);
                    }
                });
                secilenDuraklar = yeniSira;
                durakListesiniGuncelle();
            }
        });
        
        // Hidden input'u güncelle
        $('#durakSirasi').val(JSON.stringify(secilenDuraklar.map(d => d.id)));
    }

    // Durak kaldırma
    $(document).on('click', '.remove-durak', function() {
        const durakId = $(this).data('durak-id');
        secilenDuraklar = secilenDuraklar.filter(d => d.id != durakId);
        durakListesiniGuncelle();
        durakSayaciniGuncelle();
    });

    // Durak sayacını güncelle
    function durakSayaciniGuncelle() {
        const sayi = secilenDuraklar.length;
        $('#durakSayisiSpan').text(sayi);
        $('#durakSayisiBadge').text(sayi + ' Durak');
    }

    // Önizleme
    $('#onizlemeBtn').click(function() {
        const turAdi = $('#turAdi').val();
        const cikisTarihi = $('#cikisTarihi').val();
        const cikisSaati = $('#cikisSaati').val();
        const yukTipi = $('#yukTipi option:selected').text();
        const aciklama = $('#aciklama').val();
        
        const secilenArac = $('#secilenAracContainer .selected-item');
        const secilenSofor = $('#secilenSoforContainer .selected-item');
        const secilenPersonelSayisi = secilenPersonelIds.length;
        
        let onizlemeHtml = `
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-info-circle me-2"></i>Tur Bilgileri</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Tur Adı:</strong></td><td>${turAdi || 'Belirtilmemiş'}</td></tr>
                        <tr><td><strong>Çıkış Tarihi:</strong></td><td>${cikisTarihi || 'Belirtilmemiş'}</td></tr>
                        <tr><td><strong>Çıkış Saati:</strong></td><td>${cikisSaati || 'Belirtilmemiş'}</td></tr>
                        <tr><td><strong>Yük Tipi:</strong></td><td>${yukTipi || 'Belirtilmemiş'}</td></tr>
                        <tr><td><strong>Açıklama:</strong></td><td>${aciklama || 'Yok'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-users me-2"></i>Atamalar</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Araç:</strong></td><td>${secilenArac.length ? secilenArac.find('.item-title').text().trim() : 'Seçilmemiş'}</td></tr>
                        <tr><td><strong>Şoför:</strong></td><td>${secilenSofor.length ? secilenSofor.find('.item-title').text().trim() : 'Seçilmemiş'}</td></tr>
                        <tr><td><strong>Personel:</strong></td><td>${secilenPersonelSayisi} kişi</td></tr>
                        <tr><td><strong>Durak Sayısı:</strong></td><td>${secilenDuraklar.length} durak</td></tr>
                    </table>
                </div>
            </div>
        `;
        
        if (secilenDuraklar.length > 0) {
            onizlemeHtml += `
                <hr>
                <h6><i class="fas fa-map-marker-alt me-2"></i>Durak Sırası</h6>
                <ol class="list-group list-group-numbered">
            `;
            secilenDuraklar.forEach(function(durak) {
                onizlemeHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold">${durak.adi}</div>
                            Merkeze uzaklık: ${durak.uzaklik} km
                        </div>
                    </li>
                `;
            });
            onizlemeHtml += '</ol>';
        }
        
        $('#onizlemeIcerik').html(onizlemeHtml);
        $('#onizlemeModal').modal('show');
    });

    // Form doğrulama
    $('#turForm').submit(function(e) {
        let hatalar = [];
        
        if (!$('#turAdi').val().trim()) {
            hatalar.push('Tur adı gerekli');
        }
        
        if (!$('#cikisTarihi').val()) {
            hatalar.push('Çıkış tarihi gerekli');
        }
        
        if (!$('#cikisSaati').val()) {
            hatalar.push('Çıkış saati gerekli');
        }
        
        if (!$('#yukTipi').val()) {
            hatalar.push('Yük tipi seçimi gerekli');
        }
        
        if (!$('#secilenAracId').val()) {
            hatalar.push('Araç seçimi gerekli');
        }
        
        if (!$('#secilenSoforId').val()) {
            hatalar.push('Şoför seçimi gerekli');
        }
        
        if (secilenDuraklar.length === 0) {
            hatalar.push('En az bir durak seçimi gerekli');
        }
        
        if (hatalar.length > 0) {
            e.preventDefault();
            showAlert('Lütfen aşağıdaki alanları kontrol edin:\n• ' + hatalar.join('\n• '), 'error');
            return false;
        }
        
        // Çıkış tarihi kontrolü
        const cikisTarihi = new Date($('#cikisTarihi').val());
        const bugun = new Date();
        bugun.setHours(0, 0, 0, 0);
        
        if (cikisTarihi < bugun) {
            e.preventDefault();
            showAlert('Çıkış tarihi bugünden önce olamaz!', 'error');
            return false;
        }
        
        return true;
    });

    // Yardımcı fonksiyonlar
    function showAlert(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        };
        
        const alertHtml = `
            <div class="alert ${alertClass[type]} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 
                                type === 'success' ? 'check-circle' : 
                                type === 'warning' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message.replace(/\n/g, '<br>')}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Mevcut alertleri kaldır
        $('.alert').remove();
        
        // Yeni alert'i sayfanın üstüne ekle
        $('body').prepend(alertHtml);
        
        // Sayfayı en üste kaydır
        $('html, body').animate({ scrollTop: 0 }, 500);
        
        // 5 saniye sonra otomatik kapat (error hariç)
        if (type !== 'error') {
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        }
    }

    // Sayfa yüklendiğinde varsayılan tarih ayarla
    const bugun = new Date();
    const yarin = new Date(bugun);
    yarin.setDate(bugun.getDate() + 1);
    
    const tarihStr = yarin.toISOString().split('T')[0];
    $('#cikisTarihi').val(tarihStr);
    
    // Varsayılan saat ayarla (08:00)
    $('#cikisSaati').val('08:00');
    
    // Tur adı otomatik oluşturma
    $('#cikisTarihi, #ilSecimi').change(function() {
        if ($('#turAdi').val() === '' || $('#turAdi').val().startsWith('Tur -')) {
            const tarih = $('#cikisTarihi').val();
            const secilenIlSayisi = $('#ilSecimi').val() ? $('#ilSecimi').val().length : 0;
            
            if (tarih) {
                const tarihObj = new Date(tarih);
                const tarihStr = tarihObj.toLocaleDateString('tr-TR');
                $('#turAdi').val(`Tur - ${tarihStr}${secilenIlSayisi > 0 ? ` (${secilenIlSayisi} Durak)` : ''}`);
            }
        }
    });

    // Klavye kısayolları
    $(document).keydown(function(e) {
        // Ctrl+S ile kaydet
        if (e.ctrlKey && e.which === 83) {
            e.preventDefault();
            $('#turForm').submit();
        }
        
        // Ctrl+P ile önizleme
        if (e.ctrlKey && e.which === 80) {
            e.preventDefault();
            $('#onizlemeBtn').click();
        }
        
        // ESC ile modal kapat
        if (e.which === 27) {
            $('.modal').modal('hide');
        }
    });

    // Tooltip'leri etkinleştir
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Form değişiklik takibi
    let formDegisti = false;
    $('#turForm input, #turForm select, #turForm textarea').change(function() {
        formDegisti = true;
    });
    
    // Sayfa kapatılırken uyarı
    $(window).on('beforeunload', function() {
        if (formDegisti) {
            return 'Kaydedilmemiş değişiklikler var. Sayfayı kapatmak istediğinizden emin misiniz?';
        }
    });
    
    // Form submit edildiğinde uyarıyı kaldır
    $('#turForm').submit(function() {
        formDegisti = false;
    });

    // İlk yükleme mesajı
    showAlert('Tur oluşturma sayfası hazır. Sol taraftan araç, şoför ve personel seçebilir, durak ekleyebilirsiniz.', 'info');
});
</script>

<style>
/* Ek CSS stilleri */
.alert {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    min-width: 400px;
    max-width: 600px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.drag-handle {
    cursor: grab;
}

.drag-handle:active {
    cursor: grabbing;
}

.sortable-ghost {
    opacity: 0.4;
}

.sortable-chosen {
    background-color: #f8f9fa;
}

.form-control:focus,
.form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.card-header {
    font-weight: 600;
}

.badge {
    font-size: 0.75em;
}

/* Responsive düzenlemeler */
@media (max-width: 768px) {
    .container-fluid {
        padding: 10px;
    }
    
    .col-md-4,
    .col-md-8 {
        margin-bottom: 20px;
    }
    
    .drag-item {
        display: block;
        margin: 5px 0;
    }
    
    .alert {
        min-width: 90%;
        left: 5%;
        transform: none;
    }
}

/* Print stilleri */
@media print {
    .drag-container,
    .btn,
    .card-header {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
}
</style>

</body>
</html>
