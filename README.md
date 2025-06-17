# 📂 PHP File Storage Utility

[](https://opensource.org/licenses/MIT)

Une bibliothèque PHP simple et robuste offrant un ensemble de méthodes statiques pour interagir facilement avec le système de fichiers. Que vous ayez besoin de gérer des fichiers, des répertoires, de lire ou d'écrire du contenu, cette classe `Storage` simplifie ces opérations courantes.

-----

## ✨ Fonctionnalités

  * **Vérification d'existence et de type :** Déterminez si un chemin est un fichier, un répertoire ou s'il existe tout simplement.
  * **Lecture et écriture :** Récupérez, insérez, prépendez ou écrasez le contenu de fichiers.
  * **Manipulation de chemins :** Extrayez les noms de fichiers, les extensions.
  * **Gestion de répertoires :** Créez, nettoyez ou supprimez des répertoires, et listez leur contenu (fichiers et sous-répertoires).
  * **Informations sur les fichiers :** Obtenez la taille, la date de dernière modification et le type MIME d'un fichier.
  * **Opérations de déplacement/copie :** Déplacez ou copiez des fichiers vers de nouvelles destinations.
  * **Gestion robuste des erreurs :** Chaque méthode gère les échecs et déclenche des avertissements PHP clairs.

-----

## 🚀 Installation

Cette bibliothèque est conçue pour être utilisée en incluant simplement le fichier `Storage.php` dans votre projet.

1.  **Téléchargez** le fichier `Storage.php` directement depuis ce dépôt GitHub.

2.  **Incluez-le** dans votre projet PHP :

    ```php
    require_once 'Beriyack/Storage/Storage.php';
    ```

-----

## 📖 Utilisation

Toutes les méthodes de la classe `Storage` sont statiques, ce qui les rend faciles à appeler directement.

```php
<?php

require_once 'Beriyack/Storage/Storage.php'; // Assurez-vous que le chemin est correct

// --- Exemples de gestion de fichiers ---

$filePath = 'my_document.txt';
$directoryPath = 'my_data_folder';

// 1. Écrire du contenu dans un fichier (crée ou écrase)
Storage::put($filePath, "Salut le monde!\nCeci est un test.");
echo "Fichier créé ou mis à jour : " . $filePath . "\n";

// 2. Lire le contenu d'un fichier
$content = Storage::get($filePath);
if ($content !== false) {
    echo "Contenu de '" . $filePath . "' :\n" . $content . "\n";
}

// 3. Prépendre du contenu
Storage::prepend($filePath, "--- Début du fichier ---\n");
echo "Contenu après prépend : \n" . Storage::get($filePath) . "\n";

// 4. Appendre du contenu
Storage::append($filePath, "\n--- Fin du fichier ---\n");
echo "Contenu après append : \n" . Storage::get($filePath) . "\n";

// 5. Vérifier si un fichier existe
if (Storage::exists($filePath)) {
    echo "'" . $filePath . "' existe.\n";
}
if (Storage::isFile($filePath)) {
    echo "'" . $filePath . "' est un fichier.\n";
}

// 6. Obtenir des informations sur le fichier
echo "Taille de '" . $filePath . "' : " . Storage::size($filePath) . " octets\n";
echo "Dernière modification de '" . $filePath . "' : " . date('Y-m-d H:i:s', Storage::lastModified($filePath)) . "\n";
echo "Type MIME de '" . $filePath . "' : " . (Storage::mimeType($filePath) ?: 'Inconnu') . "\n";
echo "Extension de '" . $filePath . "' : " . Storage::extension($filePath) . "\n";
echo "Nom de '" . $filePath . "' (sans extension) : " . Storage::name($filePath) . "\n";


// --- Exemples de gestion de répertoires ---

// 1. Créer un répertoire
if (Storage::makeDirectory($directoryPath)) {
    echo "Répertoire créé : " . $directoryPath . "\n";
}

// 2. Vérifier si un chemin est un répertoire
if (Storage::isDirectory($directoryPath)) {
    echo "'" . $directoryPath . "' est un répertoire.\n";
}

// 3. Lister les fichiers et répertoires directs
file_put_contents($directoryPath . DIRECTORY_SEPARATOR . 'file_in_dir.txt', 'Contenu');
Storage::makeDirectory($directoryPath . DIRECTORY_SEPARATOR . 'sub_dir');

echo "\nFichiers dans '" . $directoryPath . "' : \n";
print_r(Storage::files($directoryPath));
echo "\nRépertoires dans '" . $directoryPath . "' : \n";
print_r(Storage::directories($directoryPath));

// 4. Lister tous les fichiers/répertoires (récursif)
echo "\nTous les fichiers (récursif) :\n";
print_r(Storage::allFiles('.')); // Scanne le répertoire courant
echo "\nTous les répertoires (récursif) :\n";
print_r(Storage::allDirectories('.')); // Scanne le répertoire courant

// 5. Déplacer un fichier
$newFilePath = 'temp_folder' . DIRECTORY_SEPARATOR . 'moved_document.txt';
if (Storage::move($filePath, $newFilePath)) {
    echo "Fichier déplacé vers : " . $newFilePath . "\n";
    $filePath = $newFilePath; // Mettre à jour le chemin pour les opérations futures
}

// --- Nettoyage ---

// Nettoyer un répertoire (supprime le contenu, pas le répertoire lui-même)
// if (Storage::cleanDirectory($directoryPath)) {
//     echo "\nRépertoire '" . $directoryPath . "' vidé.\n";
// }

// Supprimer un fichier
if (Storage::exists($filePath)) {
    unlink($filePath); // Utiliser unlink pour les fichiers
    echo "Fichier '" . $filePath . "' supprimé.\n";
}

// Supprimer un répertoire et son contenu
if (Storage::deleteDirectory($directoryPath)) {
    echo "Répertoire '" . $directoryPath . "' et son contenu supprimés.\n";
}

// Supprimer le dossier temporaire créé par le déplacement
if (Storage::deleteDirectory('temp_folder')) {
    echo "Répertoire 'temp_folder' supprimé.\n";
}

?>
```

-----

## 🤝 Contribution

Les contributions sont les bienvenues \! Si vous avez des idées d'améliorations, de nouvelles fonctionnalités ou des corrections de bugs, n'hésitez pas à ouvrir une *issue* ou à soumettre une *pull request*.

-----

## 📄 Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](https://www.google.com/search?q=LICENSE) pour plus de détails.

-----

## 📧 Contact

Pour toute question ou suggestion, vous pouvez me contacter via [Beriyack](https://github.com/Beriyack).

-----

### Points à personnaliser :

  * **Ajoutez un fichier `LICENSE` :** Créez un fichier nommé `LICENSE` à la racine de votre dépôt GitHub et collez-y le texte de la licence MIT (vous pouvez le trouver facilement en ligne). C'est essentiel pour tout projet open-source.
  * **Exemples supplémentaires :** Si vous avez des cas d'utilisation très spécifiques ou des astuces, ajoutez-les dans la section "Utilisation".