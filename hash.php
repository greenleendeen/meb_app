<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use App\Entity\User;

// instancier le password hasher pour Symfony 6+
$hasher = new \Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher();

$password = 'superpassword'; // ton mot de passe Super Admin
$hash = $hasher->hash($password);

echo "Mot de passe hashé : $hash\n";