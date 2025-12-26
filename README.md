# Restaurant Management System

Restoran yönetim sistemi - Müşteri siparişleri, admin paneli, stok takibi ve personel yönetimi.

## 🚀 Kurulum

### Gereksinimler

- **XAMPP** (veya AMPPS) - PHP ve MySQL için
- **PHP 7.4+**
- **MySQL 5.7+** veya **MariaDB 10.2+**

### Adım 1: Projeyi İndirin

```bash
git clone <repository-url>
cd Restaurant-Management-System
```

### Adım 2: XAMPP'i Başlatın

1. XAMPP Control Panel'i açın
2. **Apache** ve **MySQL** servislerini başlatın
3. MySQL'in çalıştığından emin olun (yeşil ışık)

### Adım 3: Veritabanını Kurun

1. Tarayıcınızda şu adresi açın:
   ```
   http://localhost/Restaurant-Management-System/setup.php
   ```

2. Setup script'i otomatik olarak:
   - Veritabanını oluşturacak
   - Tabloları kuracak
   - Örnek verileri ekleyecek
   - **Trigger'ları kuracak** (stok azaltma için)

3. Kurulum tamamlandığında "✅ Kurulum Tamamlandı!" mesajını göreceksiniz.

### Adım 4: Post-Setup Kontrolleri (ÖNEMLİ!)

**Mutlaka** post-setup script'ini çalıştırın:

1. Tarayıcınızda şu adresi açın:
   ```
   http://localhost/Restaurant-Management-System/post_setup.php
   ```

2. Bu script:
   - Trigger'ların kurulu olduğunu kontrol eder
   - Eksik ürün içeriklerini otomatik ekler
   - Stok durumunu kontrol eder

3. Eğer eksik içerikler varsa, "Eksik İçerikleri Ekle" butonuna tıklayın.

**⚠️ ÖNEMLİ:** Post-setup script'ini çalıştırmadan sipariş verirseniz, stok azalmayabilir!

### Adım 5: Yapılandırmayı Kontrol Edin

Eğer MySQL şifreniz farklıysa, `config/database.php` dosyasını düzenleyin:

```php
private $host = "localhost";
private $dbname = "restaurant_db";
private $username = "root";
private $password = ""; // XAMPP için genellikle boş
private $port = 3306;
```

### Adım 6: Test Edin

1. **Admin Paneli**: `http://localhost/Restaurant-Management-System/admin/login.php`
   - Kullanıcı: `admin`
   - Şifre: `password`

2. **Müşteri Menüsü**: `http://localhost/Restaurant-Management-System/customer/menu.php`

## 🔧 Sorun Giderme

### Sipariş Verildiğinde Stok Azalmıyor

Bu sorun genellikle şu nedenlerden kaynaklanır:

1. **Trigger'lar kurulmamış**: `setup.php` dosyasını çalıştırın
2. **Ürün içerikleri eksik**: `setup.php` otomatik olarak ekler, ama manuel kontrol için:
   ```bash
   php add_missing_ingredients.php
   ```

### Veritabanı Bağlantı Hatası

1. MySQL servisinin çalıştığından emin olun
2. `config/database.php` dosyasındaki şifreyi kontrol edin
3. XAMPP için genellikle şifre boştur (`""`)

### Trigger'ları Manuel Kontrol Etme

```bash
php test_stock_reduction.php
```

Bu script şunları kontrol eder:
- Trigger'ların varlığı
- Ürün içerik kayıtları
- Stok durumu
- Son stok hareketleri

## 📁 Proje Yapısı

```
Restaurant-Management-System/
├── admin/              # Admin paneli
├── customer/           # Müşteri arayüzü
├── api/                # API endpoint'leri
├── config/             # Yapılandırma dosyaları
├── database/           # Veritabanı şemaları
├── includes/            # Ortak PHP dosyaları
├── assets/              # CSS, JS, resimler
├── setup.php            # Kurulum script'i
└── README.md            # Bu dosya
```

## 🔑 Varsayılan Hesaplar

Kurulum sonrası şu hesaplar oluşturulur:

| Rol | Kullanıcı Adı | Şifre |
|-----|--------------|-------|
| Admin | `admin` | `password` |
| Manager | `manager1` | `password` |
| Waiter | `waiter1` | `password` |
| Customer | `customer1` | `password` |

## 📝 Önemli Notlar

### Stok Takibi

- Sipariş verildiğinde stok **otomatik olarak azalır**
- Trigger (`trg_orderdetails_ai`) her sipariş kalemi eklendiğinde çalışır
- Ürünlerin içerikleri (`ProductIngredients` tablosu) tanımlı olmalıdır

### Yeni Ürün Ekleme

Yeni bir ürün eklediğinizde, stok takibi için içeriklerini de eklemelisiniz:

1. Admin panelinden ürün ekleyin
2. Ürünün içeriklerini (`ProductIngredients`) ekleyin
3. Veya `add_missing_ingredients.php` script'ini çalıştırın

### Git İşlemleri

Projeyi çektiğinizde (`git pull`):

1. **Mutlaka** `setup.php` dosyasını çalıştırın
2. Veritabanı şeması güncellenmişse trigger'lar yeniden kurulur
3. Eksik ürün içerikleri otomatik eklenir

## 🐛 Bilinen Sorunlar

- İçeriği olmayan ürünler için stok azalmaz (bu normaldir)
- Eski siparişler için stok hareketi görünmeyebilir (içerikler sonradan eklendiyse)

## 📞 Destek

Sorun yaşarsanız:
1. `test_stock_reduction.php` script'ini çalıştırın
2. `check_order_stock.php` ile son siparişi kontrol edin
3. Hata mesajlarını kontrol edin

## 🔄 Güncelleme

Yeni bir branch çektiğinizde:

```bash
git pull origin main
# veya
git pull origin <branch-name>
```

**Sonra mutlaka şu adımları izleyin:**

1. **Setup script'ini çalıştırın** (eğer veritabanı şeması değiştiyse):
   ```
   http://localhost/Restaurant-Management-System/setup.php
   ```

2. **Post-setup script'ini çalıştırın** (MUTLAKA):
   ```
   http://localhost/Restaurant-Management-System/post_setup.php
   ```

3. Eksik içerikler varsa "Eksik İçerikleri Ekle" butonuna tıklayın.

**⚠️ ÖNEMLİ:** Her branch çektiğinizde post-setup script'ini çalıştırın! Bu, trigger'ların ve ürün içeriklerinin güncel olduğundan emin olur.

Setup script'leri güvenli şekilde çalışır - mevcut verileri silmez, sadece eksikleri ekler.

