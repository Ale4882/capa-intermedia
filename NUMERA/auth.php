<?php
require_once 'models/User.php';
require_once 'config/Database.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $this->user = new Usuario($this->db);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $gender = $_POST['gender'];
            $birthdate = $_POST['birthdate'];
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $role = $_POST['role'];

            // 1. Validar contraseñas iguales
            if ($password !== $confirm_password) {
                echo "<script>alert('Las contraseñas no coinciden'); window.history.back();</script>";
                return;
            }

            // 2. Validar si el correo ya existe
            if ($this->user->emailExists($email)) {
                echo "<script>alert('El correo electrónico ya está registrado'); window.history.back();</script>";
                return;
            }

            // 3. Manejar subida de foto de perfil
            $photoName = "default.png";
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['photo']['tmp_name'];
                $fileName = $_FILES['photo']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $uploadFileDir = 'uploads/';
                    
                    if(!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }
                    
                    $dest_path = $uploadFileDir . $newFileName;
                    if(move_uploaded_file($fileTmpPath, $dest_path)) {
                        $photoName = $newFileName;
                    }
                }
            }

            // 4. Registrar en la base de datos
            if ($this->user->register($name, $gender, $birthdate, $email, $password, $photoName, $role)) {
                // Redireccionar según el rol o al login
                if($role === 'Estudiante') {
                    header("Location: estudiante.html");
                } else {
                    header("Location: login.html");
                }
                exit();
            } else {
                echo "<script>alert('Error al registrar el usuario'); window.history.back();</script>";
            }
        }
    }
}
?>