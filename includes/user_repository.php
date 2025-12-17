<?php
require_once __DIR__ . '/cruds.php';
require_once __DIR__ . '/functions.php';

class UserRepository
{
    private $crud;

    public function __construct()
    {
        $this->crud = new CRUD();
    }

    /**
     * Username veya email ile kullanıcı bul
     */
    public function findByUsernameOrEmail(string $identifier)
    {
        return $this->crud->readOne(
            'Users',
            '(username = :id OR email = :id)',
            [':id' => $identifier]
        );
    }

    /**
     * Rol adından role_id bul (ör: 'Customer', 'Manager')
     */
    public function getRoleIdByName(string $roleName): ?int
    {
        $result = $this->crud->customQuery(
            "SELECT role_id FROM Roles WHERE role_name = :name LIMIT 1",
            [':name' => $roleName]
        );

        if ($result && count($result) > 0) {
            return (int)$result[0]['role_id'];
        }

        return null;
    }

    /**
     * Yeni kullanıcı oluştur (şifreyi burada hash'ler)
     */
    public function createUser(int $roleId, string $username, string $email, string $plainPassword)
    {
        $hashedPassword = hashPassword($plainPassword);

        return $this->crud->create('Users', [
            'role_id'  => $roleId,
            'username' => $username,
            'password' => $hashedPassword,
            'email'    => $email
        ]);
    }

    /**
     * Customer profili oluştur
     */
    public function createCustomerProfile(
        int $userId,
        string $firstName,
        string $lastName,
        ?string $phone,
        ?string $address
    ) {
        return $this->crud->create('Customers', [
            'user_id'    => $userId,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'phone'      => $phone,
            'address'    => $address
        ]);
    }

    /**
     * Kullanıcıyı rol bilgisi ile birlikte getir (login sonrası işine yarar)
     */
    public function getUserWithRoleById(int $userId): ?array
    {
        $sql = "
            SELECT u.*, r.role_name
            FROM Users u
            JOIN Roles r ON u.role_id = r.role_id
            WHERE u.user_id = :id
            LIMIT 1
        ";

        $result = $this->crud->customQuery($sql, [':id' => $userId]);

        return $result && count($result) > 0 ? $result[0] : null;
    }

    /**
     * Customer rolü ile birlikte (Users + Customers) tek seferde kayıt aç
     * (Register için Barkın burada rahat eder)
     */
    public function createCustomerUserWithProfile(
        string $username,
        string $email,
        string $plainPassword,
        string $firstName,
        string $lastName,
        ?string $phone,
        ?string $address
    ) {
        // Önce customer rolünün id'sini bul
        $roleId = $this->getRoleIdByName('Customer');
        if ($roleId === null) {
            return false;
        }

        // Transaction: Users ve Customers birlikte başarılı olmalı
        $this->crud->beginTransaction();

        try {
            // Users tablosuna kayıt
            $userId = $this->createUser($roleId, $username, $email, $plainPassword);
            if (!$userId) {
                $this->crud->rollback();
                return false;
            }

            // Customers tablosuna kayıt
            $customerId = $this->createCustomerProfile(
                (int)$userId,
                $firstName,
                $lastName,
                $phone,
                $address
            );

            if (!$customerId) {
                $this->crud->rollback();
                return false;
            }

            $this->crud->commit();
            return $userId; // Başarılıysa user_id dön

        } catch (Exception $e) {
            $this->crud->rollback();
            error_log('CreateCustomerUser Error: ' . $e->getMessage());
            return false;
        }
    }
}