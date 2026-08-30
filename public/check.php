<?php
// check.php
use Symfony\Component\Dotenv\Dotenv;
use App\Kernel;

require_once __DIR__.'/../vendor/autoload_runtime.php';

// Charger les variables d'environnement
if (file_exists(__DIR__.'/../.env.local')) {
    (new Dotenv())->load(__DIR__.'/../.env.local');
}

echo "<h2> Vérification Symfony & Base de données</h2>";

// Tester la connexion à la base
try {
    $kernel = new Kernel($_ENV['APP_ENV'] ?? 'prod', (bool)($_ENV['APP_DEBUG'] ?? 0));
    $container = $kernel->getContainer();

    $conn = $container->get('doctrine')->getConnection();
    $conn->connect();

    echo "<p style='color:green;'> Connexion à la base de données réussie !</p>";
} catch (\Exception $e) {
    echo "<p style='color:red;'> Erreur connexion BDD : ".$e->getMessage()."</p>";
}

// Tester la génération du cache prod
try {
    echo "<p>🗂️ Génération du cache prod...</p>";
    $cacheDir = __DIR__.'/../var/cache/prod';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0775, true);
        echo "<p style='color:green;'> Dossier cache prod créé.</p>";
    } else {
        echo "<p style='color:green;'> Dossier cache prod déjà existant.</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color:red;'> Erreur création cache : ".$e->getMessage()."</p>";
}

echo "<p>✔ Check terminé.</p>";