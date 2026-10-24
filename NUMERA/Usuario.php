<?php
class Usuario {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($name, $gender, $birthdate, $email, $password, $photo, $role) {
        $query = "INSERT INTO " . $this->table_name . " (name, gender, birthdate, email, password, photo, role) VALUES (:name, :gender, :birthdate, :email, :password, :photo, :role)";
        
        $stmt = $this->conn->prepare($query);

        // Hash de la contraseña por seguridad
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":gender", $gender);
        $stmt->bindParam(":birthdate", $birthdate);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $passwordHash);
        $stmt->bindParam(":photo", $photo);
        $stmt->bindParam(":role", $role);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }
}
?>