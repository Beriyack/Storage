<?php

/**
 * Fichier d'exemple pour la bibliothèque beriyack/storage.
 *
 * Ce script démontre comment utiliser les différentes méthodes statiques
 * de la classe Storage pour manipuler le système de fichiers.
 *
 * Pour l'exécuter :
 * 1. Assurez-vous d'avoir lancé `composer install` à la racine du projet.
 * 2. Lancez un serveur PHP local, par exemple : `php -S localhost:8000 -t example`
 * 3. Ouvrez http://localhost:8000 dans votre navigateur.
 */

// Affiche les erreurs pour un débogage facile
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définit l'en-tête pour un affichage HTML propre
header('Content-Type: text/html; charset=utf-8');

// Inclut l'autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

use Beriyack\Storage;

// --- Début de la structure HTML ---
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemple d'utilisation de la bibliothèque Storage</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 2em; line-height: 1.6; color: #333; }
        h1, h2 { color: #0056b3; border-bottom: 2px solid #eee; padding-bottom: 5px; }
        pre { background-color: #f4f4f4; padding: 1em; border: 1px solid #ddd; border-radius: 5px; white-space: pre-wrap; word-break: break-all; }
        .output { margin-left: 20px; font-style: italic; color: #555; }
        .code { font-family: monospace; background: #eee; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Démonstration de la bibliothèque <code>beriyack/storage</code></h1>

    <?php
    // Définition des chemins pour les tests
    $testDir = __DIR__ . '/test_directory';
    $testFile = $testDir . '/document.txt';
    $copiedFile = $testDir . '/document_copie.txt';
    $movedFile = $testDir . '/subfolder/document_deplace.txt';

    // Nettoyage initial pour s'assurer que l'on part d'un état propre
    if (Storage::isDirectory($testDir)) {
        Storage::deleteDirectory($testDir);
    }
    ?>

    <h2>1. Gestion des répertoires</h2>
    <p>Création d'un répertoire avec <code class="code">makeDirectory()</code>.</p>
    <?php Storage::makeDirectory($testDir); ?>
    <p class="output">Répertoire <code class="code"><?= htmlspecialchars($testDir) ?></code> créé.</p>
    <p>Vérification avec <code class="code">isDirectory()</code> : <?= Storage::isDirectory($testDir) ? 'Vrai' : 'Faux' ?></p>

    <h2>2. Écriture de fichiers</h2>
    <p>Écriture dans un fichier avec <code class="code">put()</code>.</p>
    <?php Storage::put($testFile, "Ceci est la ligne initiale.\n"); ?>
    <p class="output">Contenu écrit dans <code class="code"><?= htmlspecialchars($testFile) ?></code>.</p>

    <p>Ajout de contenu à la fin avec <code class="code">append()</code>.</p>
    <?php Storage::append($testFile, "Ceci est une ligne ajoutée à la fin.\n"); ?>
    <p class="output">Contenu ajouté.</p>

    <p>Ajout de contenu au début avec <code class="code">prepend()</code>.</p>
    <?php Storage::prepend($testFile, "--- DÉBUT DU FICHIER ---\n"); ?>
    <p class="output">Contenu ajouté.</p>

    <h2>3. Lecture de fichiers</h2>
    <p>Lecture du contenu complet avec <code class="code">get()</code>.</p>
    <pre><?= htmlspecialchars(Storage::get($testFile)) ?></pre>

    <h2>4. Informations sur les fichiers</h2>
    <p>Vérification de l'existence avec <code class="code">exists()</code> : <?= Storage::exists($testFile) ? 'Vrai' : 'Faux' ?></p>
    <p>Vérification du type avec <code class="code">isFile()</code> : <?= Storage::isFile($testFile) ? 'Vrai' : 'Faux' ?></p>
    <p>Obtention de la taille avec <code class="code">size()</code> : <?= Storage::size($testFile) ?> octets</p>
    <p>Obtention de l'extension avec <code class="code">extension()</code> : "<?= Storage::extension($testFile) ?>"</p>
    <p>Obtention du nom avec <code class="code">name()</code> : "<?= Storage::name($testFile) ?>"</p>
    <p>Obtention du type MIME avec <code class="code">mimeType()</code> : "<?= Storage::mimeType($testFile) ?>"</p>
    <p>Dernière modification avec <code class="code">lastModified()</code> : <?= date('Y-m-d H:i:s', Storage::lastModified($testFile)) ?></p>

    <h2>5. Copie et déplacement de fichiers</h2>
    <p>Copie du fichier avec <code class="code">copy()</code>.</p>
    <?php Storage::copy($testFile, $copiedFile); ?>
    <p class="output">Fichier copié vers <code class="code"><?= htmlspecialchars($copiedFile) ?></code>.</p>
    <p>Vérification de l'existence de la copie : <?= Storage::exists($copiedFile) ? 'Vrai' : 'Faux' ?></p>

    <p>Déplacement du fichier avec <code class="code">move()</code> (crée le sous-dossier au besoin).</p>
    <?php Storage::move($copiedFile, $movedFile); ?>
    <p class="output">Fichier déplacé vers <code class="code"><?= htmlspecialchars($movedFile) ?></code>.</p>
    <p>Vérification de l'existence de l'original après déplacement : <?= Storage::exists($copiedFile) ? 'Vrai' : 'Faux' ?></p>
    <p>Vérification de l'existence du fichier déplacé : <?= Storage::exists($movedFile) ? 'Vrai' : 'Faux' ?></p>

    <h2>6. Lister le contenu des répertoires</h2>
    <p>Liste des fichiers directs avec <code class="code">files()</code>.</p>
    <pre><?php print_r(Storage::files($testDir)); ?></pre>

    <p>Liste des répertoires directs avec <code class="code">directories()</code>.</p>
    <pre><?php print_r(Storage::directories($testDir)); ?></pre>

    <p>Liste de tous les fichiers (récursif) avec <code class="code">allFiles()</code>.</p>
    <pre><?php print_r(Storage::allFiles($testDir)); ?></pre>

    <p>Liste de tous les répertoires (récursif) avec <code class="code">allDirectories()</code>.</p>
    <pre><?php print_r(Storage::allDirectories($testDir)); ?></pre>

    <h2>7. Nettoyage et suppression</h2>
    <p>Nettoyage du contenu d'un répertoire avec <code class="code">cleanDirectory()</code>.</p>
    <?php
    // On crée un fichier temporaire pour le test
    Storage::put($testDir . '/temp.txt', 'à supprimer');
    Storage::cleanDirectory($testDir . '/subfolder'); // Vide le sous-dossier
    rmdir($testDir . '/subfolder'); // Supprime le sous-dossier vide
    ?>
    <p class="output">Le contenu de <code class="code"><?= htmlspecialchars($testDir . '/subfolder') ?></code> a été vidé.</p>
    <p>Contenu restant dans <code class="code"><?= htmlspecialchars($testDir) ?></code> :</p>
    <pre><?php print_r(Storage::files($testDir)); ?></pre>

    <p>Suppression complète du répertoire de test avec <code class="code">deleteDirectory()</code>.</p>
    <?php Storage::deleteDirectory($testDir); ?>
    <p class="output">Répertoire <code class="code"><?= htmlspecialchars($testDir) ?></code> supprimé.</p>
    <p>Vérification de l'existence après suppression : <?= Storage::exists($testDir) ? 'Vrai' : 'Faux' ?></p>

</body>
</html>
