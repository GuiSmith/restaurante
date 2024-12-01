<?php

class User extends CRUDModel {
    protected static $table = 'users';

    public function __construction(){
        parent::__construct();
    }

    // Buscar usuário por email
    public static function findByEmail($email): array {
        return self::$db->search(static::$table, ['email' => $email], ['id']);
    }

    // Verificar se um email já existe no banco
    public static function emailExists($email): bool {
        return !empty(self::findByEmail($email));
    }

    // Exemplo de método customizado: Buscar usuários ativos
    public static function findActiveUsers(): array {
        return self::$db->search(static::$table, ['active' => true]);
    }
}

?>