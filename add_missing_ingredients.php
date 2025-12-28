<?php
/**
 * Bu script içeriği olmayan ürünlere içerik ekler
 * Stok azaltma işleminin çalışması için gerekli
 */

require_once __DIR__ . '/includes/cruds.php';

try {
    $crud = new CRUD();
    
    echo "İçeriği olmayan ürünlere içerik ekleniyor...\n\n";
    
    // Yardımcı fonksiyon: İçerik var mı kontrol et
    function hasIngredient($crud, $productId, $ingredientId) {
        $existing = $crud->readOne('ProductIngredients', 
            'product_id = :pid AND ingredient_id = :iid', 
            [':pid' => $productId, ':iid' => $ingredientId]
        );
        return $existing !== false;
    }
    
    // French Fries (product_id: 4)
    echo "French Fries (ID: 4) için içerik ekleniyor...\n";
    if (!hasIngredient($crud, 4, 4)) {
        $crud->create('ProductIngredients', [
            'product_id' => 4,
            'ingredient_id' => 4, // Potatoes
            'quantity_required' => 0.200
        ]);
    }
    if (!hasIngredient($crud, 4, 13)) {
        $crud->create('ProductIngredients', [
            'product_id' => 4,
            'ingredient_id' => 13, // Olive Oil
            'quantity_required' => 0.030
        ]);
    }
    if (!hasIngredient($crud, 4, 12)) {
        $crud->create('ProductIngredients', [
            'product_id' => 4,
            'ingredient_id' => 12, // Salt
            'quantity_required' => 0.005
        ]);
    }
    echo "✓ French Fries içerikleri eklendi\n\n";
    
    // Tomato Soup (product_id: 5)
    echo "Tomato Soup (ID: 5) için içerik ekleniyor...\n";
    $crud->create('ProductIngredients', [
        'product_id' => 5,
        'ingredient_id' => 1, // Tomatoes
        'quantity_required' => 0.300
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 5,
        'ingredient_id' => 9, // Milk
        'quantity_required' => 0.200
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 5,
        'ingredient_id' => 14, // Garlic
        'quantity_required' => 0.010
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 5,
        'ingredient_id' => 12, // Salt
        'quantity_required' => 0.005
    ]);
    echo "✓ Tomato Soup içerikleri eklendi\n\n";
    
    // Chicken Soup (product_id: 6)
    echo "Chicken Soup (ID: 6) için içerik ekleniyor...\n";
    $crud->create('ProductIngredients', [
        'product_id' => 6,
        'ingredient_id' => 5, // Chicken Breast
        'quantity_required' => 0.200
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 6,
        'ingredient_id' => 3, // Onions
        'quantity_required' => 0.100
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 6,
        'ingredient_id' => 1, // Tomatoes
        'quantity_required' => 0.100
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 6,
        'ingredient_id' => 12, // Salt
        'quantity_required' => 0.005
    ]);
    echo "✓ Chicken Soup içerikleri eklendi\n\n";
    
    // Spaghetti Bolognese (product_id: 9)
    echo "Spaghetti Bolognese (ID: 9) için içerik ekleniyor...\n";
    $crud->create('ProductIngredients', [
        'product_id' => 9,
        'ingredient_id' => 7, // Ground Beef
        'quantity_required' => 0.250
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 9,
        'ingredient_id' => 1, // Tomatoes
        'quantity_required' => 0.200
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 9,
        'ingredient_id' => 3, // Onions
        'quantity_required' => 0.080
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 9,
        'ingredient_id' => 14, // Garlic
        'quantity_required' => 0.015
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 9,
        'ingredient_id' => 13, // Olive Oil
        'quantity_required' => 0.020
    ]);
    echo "✓ Spaghetti Bolognese içerikleri eklendi\n\n";
    
    // Margherita Pizza (product_id: 10)
    echo "Margherita Pizza (ID: 10) için içerik ekleniyor...\n";
    $crud->create('ProductIngredients', [
        'product_id' => 10,
        'ingredient_id' => 8, // Mozzarella Cheese
        'quantity_required' => 0.150
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 10,
        'ingredient_id' => 1, // Tomatoes
        'quantity_required' => 0.200
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 10,
        'ingredient_id' => 13, // Olive Oil
        'quantity_required' => 0.015
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 10,
        'ingredient_id' => 12, // Salt
        'quantity_required' => 0.003
    ]);
    echo "✓ Margherita Pizza içerikleri eklendi\n\n";
    
    // Tiramisu (product_id: 12)
    echo "Tiramisu (ID: 12) için içerik ekleniyor...\n";
    $crud->create('ProductIngredients', [
        'product_id' => 12,
        'ingredient_id' => 9, // Milk
        'quantity_required' => 0.300
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 12,
        'ingredient_id' => 10, // Butter
        'quantity_required' => 0.050
    ]);
    echo "✓ Tiramisu içerikleri eklendi\n\n";
    
    // Chocolate Cake (product_id: 13)
    echo "Chocolate Cake (ID: 13) için içerik ekleniyor...\n";
    $crud->create('ProductIngredients', [
        'product_id' => 13,
        'ingredient_id' => 9, // Milk
        'quantity_required' => 0.250
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 13,
        'ingredient_id' => 10, // Butter
        'quantity_required' => 0.100
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 13,
        'ingredient_id' => 4, // Potatoes (un için alternatif olarak)
        'quantity_required' => 0.050
    ]);
    echo "✓ Chocolate Cake içerikleri eklendi\n\n";
    
    // Cheesecake (product_id: 14)
    echo "Cheesecake (ID: 14) için içerik ekleniyor...\n";
    $crud->create('ProductIngredients', [
        'product_id' => 14,
        'ingredient_id' => 8, // Mozzarella Cheese
        'quantity_required' => 0.200
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 14,
        'ingredient_id' => 9, // Milk
        'quantity_required' => 0.150
    ]);
    $crud->create('ProductIngredients', [
        'product_id' => 14,
        'ingredient_id' => 10, // Butter
        'quantity_required' => 0.080
    ]);
    echo "✓ Cheesecake içerikleri eklendi\n\n";
    
    // Fresh Orange Juice (product_id: 16)
    echo "Fresh Orange Juice (ID: 16) için içerik ekleniyor...\n";
    // Portakal malzemesini kontrol et veya ekle
    $orangeIngredient = $crud->readOne('Ingredients', 'ingredient_name = :name', [':name' => 'Portakal']);
    if (!$orangeIngredient) {
        $orangeIngredient = $crud->readOne('Ingredients', 'ingredient_name = :name', [':name' => 'Orange']);
    }
    if (!$orangeIngredient) {
        // Portakal malzemesi yoksa ekle
        $orangeSupplier = $crud->readOne('Suppliers', 'supplier_name LIKE :name', [':name' => '%Fresh Produce%']);
        if ($orangeSupplier) {
            $orangeId = $crud->create('Ingredients', [
                'supplier_id' => $orangeSupplier['supplier_id'],
                'ingredient_name' => 'Portakal',
                'unit' => 'kg',
                'unit_price' => 20.00
            ]);
            // Stok ekle
            $crud->create('Stocks', [
                'ingredient_id' => $orangeId,
                'quantity' => 30.00,
                'minimum_quantity' => 10.00
            ]);
            $orangeIngredientId = $orangeId;
        } else {
            echo "⚠ Supplier bulunamadı, Fresh Orange Juice atlanıyor\n\n";
            $orangeIngredientId = null;
        }
    } else {
        $orangeIngredientId = $orangeIngredient['ingredient_id'];
    }
    
    if ($orangeIngredientId) {
        $crud->create('ProductIngredients', [
            'product_id' => 16,
            'ingredient_id' => $orangeIngredientId,
            'quantity_required' => 0.500
        ]);
        echo "✓ Fresh Orange Juice içerikleri eklendi\n\n";
    }
    
    // Turkish Coffee (product_id: 17)
    echo "Turkish Coffee (ID: 17) için içerik ekleniyor...\n";
    // Kahve malzemesini kontrol et veya ekle
    $coffeeIngredient = $crud->readOne('Ingredients', 'ingredient_name LIKE :name', [':name' => '%Coffee%']);
    if (!$coffeeIngredient) {
        // Kahve malzemesi yoksa ekle
        $coffeeSupplier = $crud->readOne('Suppliers', 'supplier_name LIKE :name', [':name' => '%Spice%']);
        if ($coffeeSupplier) {
            $coffeeId = $crud->create('Ingredients', [
                'supplier_id' => $coffeeSupplier['supplier_id'],
                'ingredient_name' => 'Coffee Beans',
                'unit' => 'kg',
                'unit_price' => 150.00
            ]);
            // Stok ekle
            $crud->create('Stocks', [
                'ingredient_id' => $coffeeId,
                'quantity' => 20.00,
                'minimum_quantity' => 5.00
            ]);
            $coffeeIngredientId = $coffeeId;
        } else {
            echo "⚠ Supplier bulunamadı, Turkish Coffee atlanıyor\n\n";
            $coffeeIngredientId = null;
        }
    } else {
        $coffeeIngredientId = $coffeeIngredient['ingredient_id'];
    }
    
    if ($coffeeIngredientId) {
        $crud->create('ProductIngredients', [
            'product_id' => 17,
            'ingredient_id' => $coffeeIngredientId,
            'quantity_required' => 0.020
        ]);
        echo "✓ Turkish Coffee içerikleri eklendi\n\n";
    }
    
    // Cappuccino (product_id: 18)
    echo "Cappuccino (ID: 18) için içerik ekleniyor...\n";
    if (!hasIngredient($crud, 18, 9)) {
        $crud->create('ProductIngredients', [
            'product_id' => 18,
            'ingredient_id' => 9, // Milk
            'quantity_required' => 0.200
        ]);
    }
    echo "✓ Cappuccino içerikleri eklendi\n\n";
    
    // Büryan (product_id: 19) - Kuzu eti yemeği
    echo "Büryan (ID: 19) için içerik ekleniyor...\n";
    // Büryan için kuzu eti malzemesi kontrol et veya ekle
    $lambIngredient = $crud->readOne('Ingredients', 'ingredient_name LIKE :name', [':name' => '%Kuzu%']);
    if (!$lambIngredient) {
        $lambIngredient = $crud->readOne('Ingredients', 'ingredient_name LIKE :name', [':name' => '%Lamb%']);
    }
    if (!$lambIngredient) {
        // Kuzu eti malzemesi yoksa ekle
        $meatSupplier = $crud->readOne('Suppliers', 'supplier_name LIKE :name', [':name' => '%Quality Meats%']);
        if ($meatSupplier) {
            $lambId = $crud->create('Ingredients', [
                'supplier_id' => $meatSupplier['supplier_id'],
                'ingredient_name' => 'Kuzu eti',
                'unit' => 'kg',
                'unit_price' => 200.00
            ]);
            // Stok ekle
            $crud->create('Stocks', [
                'ingredient_id' => $lambId,
                'quantity' => 30.00,
                'minimum_quantity' => 10.00
            ]);
            $lambIngredientId = $lambId;
        } else {
            // Kuzu eti yoksa, Beef Steak kullan (alternatif)
            $beefSteak = $crud->readOne('Ingredients', 'ingredient_name LIKE :name', [':name' => '%Dana Biftek%']);
            if (!$beefSteak) {
                $beefSteak = $crud->readOne('Ingredients', 'ingredient_name LIKE :name', [':name' => '%Beef Steak%']);
            }
            $lambIngredientId = $beefSteak ? $beefSteak['ingredient_id'] : null;
        }
    } else {
        $lambIngredientId = $lambIngredient['ingredient_id'];
    }
    
    if ($lambIngredientId) {
        if (!hasIngredient($crud, 19, $lambIngredientId)) {
            $crud->create('ProductIngredients', [
                'product_id' => 19,
                'ingredient_id' => $lambIngredientId,
                'quantity_required' => 0.400
            ]);
        }
        // Baharatlar ekle
        $saltIngredient = $crud->readOne('Ingredients', 'ingredient_name = :name', [':name' => 'Tuz']);
        if (!$saltIngredient) {
            $saltIngredient = $crud->readOne('Ingredients', 'ingredient_name = :name', [':name' => 'Salt']);
        }
        $pepperIngredient = $crud->readOne('Ingredients', 'ingredient_name LIKE :name', [':name' => '%Karabiber%']);
        if (!$pepperIngredient) {
            $pepperIngredient = $crud->readOne('Ingredients', 'ingredient_name LIKE :name', [':name' => '%Black Pepper%']);
        }
        $onionIngredient = $crud->readOne('Ingredients', 'ingredient_name = :name', [':name' => 'Soğan']);
        if (!$onionIngredient) {
            $onionIngredient = $crud->readOne('Ingredients', 'ingredient_name = :name', [':name' => 'Onions']);
        }
        
        if ($saltIngredient && !hasIngredient($crud, 19, $saltIngredient['ingredient_id'])) {
            $crud->create('ProductIngredients', [
                'product_id' => 19,
                'ingredient_id' => $saltIngredient['ingredient_id'],
                'quantity_required' => 0.010
            ]);
        }
        if ($pepperIngredient && !hasIngredient($crud, 19, $pepperIngredient['ingredient_id'])) {
            $crud->create('ProductIngredients', [
                'product_id' => 19,
                'ingredient_id' => $pepperIngredient['ingredient_id'],
                'quantity_required' => 0.008
            ]);
        }
        if ($onionIngredient && !hasIngredient($crud, 19, $onionIngredient['ingredient_id'])) {
            $crud->create('ProductIngredients', [
                'product_id' => 19,
                'ingredient_id' => $onionIngredient['ingredient_id'],
                'quantity_required' => 0.150
            ]);
        }
        echo "✓ Büryan içerikleri eklendi\n\n";
    }
    
    echo "✅ İşlem tamamlandı! İçeriği olmayan ürünlere içerik eklendi.\n";
    echo "Artık sipariş verildiğinde stok otomatik olarak azalacak.\n";
    
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
