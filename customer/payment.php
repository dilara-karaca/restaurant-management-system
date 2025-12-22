<?php
require_once __DIR__ . '/../includes/layout/top.php';
require_once __DIR__ . '/../includes/layout/customer_nav.php';

$paymentMethod = isset($_GET['method']) ? $_GET['method'] : 'mobile';
$methodName = 'Mobil Ödeme';
?>

<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 20px;
    }

    .payment-container {
        max-width: 500px;
        margin: 40px auto;
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .payment-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .payment-header h2 {
        color: #1f2937;
        margin-bottom: 5px;
    }

    .payment-header p {
        color: #6b7280;
        font-size: 14px;
    }

    .order-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 20px;
        color: white;
        margin-bottom: 25px;
    }

    .order-summary__items {
        margin-bottom: 15px;
    }

    .order-summary__item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 14px;
    }

    .order-summary__total {
        border-top: 2px solid rgba(255, 255, 255, 0.3);
        padding-top: 15px;
        margin-top: 15px;
        display: flex;
        justify-content: space-between;
        font-size: 18px;
        font-weight: 700;
    }

    .payment-form {
        margin-top: 25px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #374151;
        font-weight: 600;
        font-size: 14px;
    }

    .form-input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 16px;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }

    .form-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 15px;
    }

    .btn-pay {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 10px;
    }

    .btn-pay:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
    }

    .btn-pay:active {
        transform: translateY(0);
    }

    .btn-cancel {
        width: 100%;
        padding: 12px;
        background: white;
        color: #6b7280;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-top: 10px;
    }

    .btn-cancel:hover {
        border-color: #9ca3af;
        color: #374151;
    }

    .payment-icon {
        text-align: center;
        font-size: 48px;
        margin-bottom: 15px;
    }

    .secure-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #10b981;
        font-size: 12px;
        margin-top: 15px;
    }

    .mobile-payment-info {
        background: #f3f4f6;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        text-align: center;
    }

    .mobile-payment-info p {
        color: #6b7280;
        font-size: 14px;
        margin: 5px 0;
    }

    .qr-code-placeholder {
        width: 200px;
        height: 200px;
        margin: 20px auto;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
    }

    .payment-method-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 25px;
    }

    .method-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 20px 15px;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        font-weight: 600;
        color: #6b7280;
    }

    .method-btn:hover {
        border-color: #667eea;
        transform: translateY(-2px);
    }

    .method-btn.active {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }

    .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        transform: translateX(400px);
        transition: transform 0.3s ease;
        z-index: 10000;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .toast-notification.show {
        transform: translateX(0);
    }

    .confirm-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10001;
        backdrop-filter: blur(4px);
    }

    .confirm-modal-overlay.show {
        display: flex;
    }

    .confirm-modal {
        background: white;
        border-radius: 20px;
        padding: 30px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        text-align: center;
        animation: modalSlideUp 0.3s ease;
    }

    @keyframes modalSlideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .confirm-modal__icon {
        font-size: 48px;
        margin-bottom: 15px;
    }

    .confirm-modal__title {
        color: #1f2937;
        margin: 0 0 10px 0;
        font-size: 20px;
    }

    .confirm-modal__message {
        color: #6b7280;
        margin: 0 0 25px 0;
        font-size: 14px;
    }

    .confirm-modal__buttons {
        display: flex;
        gap: 10px;
    }

    .confirm-modal__btn {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .confirm-modal__btn--cancel {
        background: #f3f4f6;
        color: #6b7280;
    }

    .confirm-modal__btn--cancel:hover {
        background: #e5e7eb;
    }

    .confirm-modal__btn--confirm {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .confirm-modal__btn--confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }
</style>

<div class="payment-container">
    <div class="payment-icon">�</div>
    
    <div class="payment-header">
        <h2>Mobil Ödeme</h2>
        <p>Güvenli ödeme sayfası</p>
    </div>

    <div class="order-summary">
        <h3 style="margin-top: 0; margin-bottom: 15px;">Sipariş Özeti</h3>
        <div class="order-summary__items" id="orderItems"></div>
        <div class="order-summary__total">
            <span>Toplam:</span>
            <span id="orderTotal">0.00 ₺</span>
        </div>
    </div>

    <!-- Kart Formu -->
    <form class="payment-form" id="paymentForm">
        <div class="form-group">
            <label>Kart Numarası *</label>
            <input type="text" class="form-input" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" required>
        </div>

        <div class="form-group">
            <label>Kart Sahibi *</label>
            <input type="text" class="form-input" id="cardHolder" placeholder="AD SOYAD" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Son Kullanma Tarihi *</label>
                <input type="text" class="form-input" id="expiryDate" placeholder="MM/YY" maxlength="5" required>
            </div>
            <div class="form-group">
                <label>CVV *</label>
                <input type="text" class="form-input" id="cvv" placeholder="123" maxlength="3" required>
            </div>
        </div>

        <button type="submit" class="btn-pay">Ödemeyi Tamamla</button>
    </form>

    <button class="btn-cancel" onclick="cancelPayment()">İptal Et</button>

    <div class="secure-badge">
        <span>🔒</span>
        <span>Güvenli ödeme sayfası</span>
    </div>
</div>

<!-- Toast Notification -->
<div class="toast-notification" id="toastNotification">
    <div class="toast__message">Başarılı!</div>
</div>

<!-- Confirmation Modal -->
<div class="confirm-modal-overlay" id="confirmModal">
    <div class="confirm-modal">
        <div class="confirm-modal__icon">⚠️</div>
        <h3 class="confirm-modal__title">Ödemeyi İptal Et</h3>
        <p class="confirm-modal__message">Ödemeyi iptal etmek istediğinize emin misiniz?</p>
        <div class="confirm-modal__buttons">
            <button class="confirm-modal__btn confirm-modal__btn--cancel" onclick="hideConfirmModal()">Vazgeç</button>
            <button class="confirm-modal__btn confirm-modal__btn--confirm" onclick="confirmCancel()">Evet, İptal Et</button>
        </div>
    </div>
</div>

<script>
// LocalStorage'dan sipariş bilgilerini al
const pendingOrder = JSON.parse(localStorage.getItem('pendingOrder'));

if (pendingOrder) {
    // Sipariş özetini göster
    const itemsContainer = document.getElementById('orderItems');
    let itemsHTML = '';
    
    pendingOrder.items.forEach(item => {
        const itemTotal = item.price * item.quantity;
        itemsHTML += `
            <div class="order-summary__item">
                <span>${item.name} x${item.quantity}</span>
                <span>${itemTotal.toFixed(2)} ₺</span>
            </div>
        `;
    });
    
    itemsContainer.innerHTML = itemsHTML;
    document.getElementById('orderTotal').textContent = pendingOrder.total.toFixed(2) + ' ₺';
}

// Kart numarası formatlama
const cardNumberInput = document.getElementById('cardNumber');
if (cardNumberInput) {
    cardNumberInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        e.target.value = formattedValue;
    });
}

// Son kullanma tarihi formatlama
const expiryInput = document.getElementById('expiryDate');
if (expiryInput) {
    expiryInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.slice(0, 2) + '/' + value.slice(2, 4);
        }
        e.target.value = value;
    });
}

// CVV sadece rakam
const cvvInput = document.getElementById('cvv');
if (cvvInput) {
    cvvInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });
}

// Kart ile ödeme formu
const paymentForm = document.getElementById('paymentForm');
if (paymentForm) {
    paymentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const cardNumber = document.getElementById('cardNumber').value;
        const cardHolder = document.getElementById('cardHolder').value;
        const expiryDate = document.getElementById('expiryDate').value;
        const cvv = document.getElementById('cvv').value;
        
        // Validasyon
        if (!cardNumber || cardNumber.replace(/\s/g, '').length < 16) {
            showToast('Lütfen geçerli bir kart numarası giriniz!', 'error');
            return;
        }
        
        if (!expiryDate || !expiryDate.includes('/')) {
            showToast('Lütfen geçerli bir son kullanma tarihi giriniz!', 'error');
            return;
        }
        
        if (!cvv || cvv.length < 3) {
            showToast('Lütfen geçerli bir CVV giriniz!', 'error');
            return;
        }
        
        // Ödeme verilerini hazırla
        const paymentData = {
            ...pendingOrder,
            cardLast4: cardNumber.slice(-4),
            cardHolder: cardHolder,
            paymentTimestamp: new Date().toISOString()
        };
        
        console.log('Mobil ödeme - Kart ile ödeme verileri:', paymentData);
        // TODO: API'ye gönderilecek
        
        completePayment();
    });
}

function completePayment() {
    // Sepeti temizle
    localStorage.removeItem('orderCart');
    localStorage.removeItem('pendingOrder');
    
    // Başarı toast göster
    showToast('Ödemeniz başarıyla tamamlandı! Siparişiniz alındı.', 'success');
    
    // 2 saniye sonra ana sayfaya dön
    setTimeout(() => {
        window.location.href = 'menu.php';
    }, 2000);
}

function cancelPayment() {
    document.getElementById('confirmModal').classList.add('show');
}

function hideConfirmModal() {
    document.getElementById('confirmModal').classList.remove('show');
}

function confirmCancel() {
    hideConfirmModal();
    showToast('Ödeme iptal edildi.', 'error');
    setTimeout(() => {
        window.location.href = 'menu.php';
    }, 1500);
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toastNotification');
    const toastMessage = toast.querySelector('.toast__message');
    
    // Mesajı güncelle
    toastMessage.textContent = message;
    
    // Renk temasını ayarla
    if (type === 'error') {
        toast.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
    } else {
        toast.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
    }
    
    toast.classList.add('show');
    
    // 3 saniye sonra toast'u gizle
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}
</script>

<?php require_once __DIR__ . '/../includes/layout/bottom.php'; ?>
