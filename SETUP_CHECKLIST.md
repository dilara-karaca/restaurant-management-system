# ✅ Kurulum Kontrol Listesi

Bu dosya, projeyi ilk kez kurarken veya güncellerken takip etmeniz gereken adımları içerir.

## 🚀 İlk Kurulum

- [ ] XAMPP'i kurun ve Apache + MySQL'i başlatın
- [ ] Projeyi `htdocs` klasörüne kopyalayın
- [ ] `setup.php` dosyasını tarayıcıda açın ve çalıştırın
- [ ] `post_setup.php` dosyasını tarayıcıda açın ve çalıştırın
- [ ] Eksik içerikler varsa "Eksik İçerikleri Ekle" butonuna tıklayın
- [ ] Admin paneline giriş yaparak test edin
- [ ] Müşteri menüsünden sipariş vererek stok azalmasını test edin

## 🔄 Branch Güncelleme (Git Pull)

- [ ] `git pull` komutunu çalıştırın
- [ ] `setup.php` dosyasını çalıştırın (eğer veritabanı şeması değiştiyse)
- [ ] **MUTLAKA** `post_setup.php` dosyasını çalıştırın
- [ ] Eksik içerikler varsa "Eksik İçerikleri Ekle" butonuna tıklayın
- [ ] Sipariş vererek stok azalmasını test edin

## 🐛 Sorun Giderme

### Sipariş Verildiğinde Stok Azalmıyor

1. `post_setup.php` dosyasını çalıştırın
2. Trigger'ların kurulu olduğunu kontrol edin
3. Ürün içeriklerinin eksik olmadığını kontrol edin
4. `test_stock_reduction.php` script'ini çalıştırarak detaylı kontrol yapın

### Veritabanı Bağlantı Hatası

1. MySQL servisinin çalıştığından emin olun
2. `config/database.php` dosyasındaki şifreyi kontrol edin
3. XAMPP için genellikle şifre boştur (`""`)

## 📝 Notlar

- **Her branch çektiğinizde post_setup.php'yi çalıştırın!**
- Post-setup script'i güvenlidir - mevcut verileri silmez
- Trigger'lar veritabanı şemasında tanımlıdır, setup.php ile kurulur
- Ürün içerikleri post_setup.php ile otomatik eklenir

