<?php

namespace Beriyack;

use FilesystemIterator;

/**
 * Class Storage
 *
 * Provides a robust and convenient set of static methods for
 * interacting with the filesystem. This includes operations for
 * managing directories, files, and their contents.
 *
 * @package Beriyack\Storage
 * @author Beriyack
 * @version 1.0.0
 */
class Storage
{
    /**
     * Returns an array of all the directories within a given directory and all of its sub-directories
     * 
     * @param string $directory Le chemin du répertoire à scanner.
     * @return array Un tableau de tous les répertoires trouvés.
     */
    public static function allDirectories(string $directory): array
    {
        // Vérifie si le répertoire existe et est un répertoire.
        if (!self::isDirectory($directory)) {
            // Vous pouvez choisir de lever une exception, de retourner un tableau vide, ou de logger une erreur.
            // Pour cet exemple, nous retournons un tableau vide si le répertoire n'est pas valide.
            trigger_error("Le répertoire spécifié n'existe pas ou n'est pas un répertoire valide : " . $directory, E_USER_WARNING);
            return [];
        }

        $allDirs = []; // Initialise le tableau qui contiendra tous les répertoires

        // Ouvre le répertoire
        if ($handle = opendir($directory)) {
            // Lit chaque entrée du répertoire
            while (false !== ($entry = readdir($handle))) {
                // Ignore les entrées '.' et '..'
                if ($entry != "." && $entry != "..") {
                    $path = $directory . DIRECTORY_SEPARATOR . $entry;

                    // Si l'entrée est un répertoire
                    if (self::isDirectory($path)) {
                        $allDirs[] = $path; // Ajoute le répertoire actuel au tableau

                        // Appelle récursivement la fonction pour les sous-répertoires
                        $subDirs = self::allDirectories($path);
                        // Fusionne les sous-répertoires trouvés avec le tableau principal
                        $allDirs = array_merge($allDirs, $subDirs);
                    }
                }
            }
            closedir($handle); // Ferme le répertoire
        }

        return $allDirs; // Retourne le tableau de tous les répertoires
    }

    /**
     * Returns an array of all the files in a given directory and all of its sub-directories.
     *
     * @param string $directory Le chemin du répertoire à scanner.
     * @return array Un tableau de tous les chemins de fichiers trouvés.
     */
    public static function allFiles(string $directory): array
    {
        // Vérifie si le répertoire existe et est un répertoire.
        if (!self::isDirectory($directory)) {
            trigger_error("Le répertoire spécifié n'existe pas ou n'est pas un répertoire valide : " . $directory, E_USER_WARNING);
            return [];
        }

        $allFiles = []; // Initialise le tableau qui contiendra tous les fichiers

        // Ouvre le répertoire
        if ($handle = opendir($directory)) {
            // Lit chaque entrée du répertoire
            while (false !== ($entry = readdir($handle))) {
                // Ignore les entrées '.' et '..'
                if ($entry != "." && $entry != "..") {
                    $path = $directory . DIRECTORY_SEPARATOR . $entry;

                    // Si l'entrée est un fichier
                    if (self::isFile($path)) {
                        $allFiles[] = $path; // Ajoute le fichier actuel au tableau
                    }
                    // Si l'entrée est un répertoire, on appelle récursivement pour trouver les fichiers à l'intérieur
                    elseif (self::isDirectory($path)) {
                        $subFiles = self::allFiles($path);
                        // Fusionne les fichiers trouvés dans les sous-répertoires avec le tableau principal
                        $allFiles = array_merge($allFiles, $subFiles);
                    }
                }
            }
            closedir($handle); // Ferme le répertoire
        }

        return $allFiles; // Retourne le tableau de tous les fichiers
    }

    /**
     * Inserts content at the end of a file.
     * Crée le fichier s'il n'existe pas.
     *
     * @param string $filePath Le chemin complet du fichier.
     * @param string $appendedText Le contenu à ajouter à la fin du fichier.
     * @return bool True si le contenu a été ajouté avec succès, false sinon.
     */
    public static function append(string $filePath, string $appendedText): bool
    {
        // Utilise FILE_APPEND pour ajouter du contenu à la fin du fichier.
        // LOCK_EX pour éviter les problèmes de concurrence si plusieurs processus écrivent en même temps.
        // file_put_contents crée le fichier s'il n'existe pas.
        $result = file_put_contents($filePath, $appendedText, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            trigger_error("Impossible d'ajouter le contenu au fichier : " . $filePath, E_USER_WARNING);
            return false;
        }

        return true;
    }

    /**
     * Empties the specified directory of all files and folders.
     * Le répertoire lui-même n'est PAS supprimé.
     *
     * @param string $directory Le chemin du répertoire à vider.
     * @return bool True si le répertoire a été vidé avec succès, false sinon.
     */
    public static function cleanDirectory(string $directory): bool
    {
        // Vérifie si le chemin est bien un répertoire existant.
        if (!self::isDirectory($directory)) {
            trigger_error("Le chemin spécifié n'est pas un répertoire valide : " . $directory, E_USER_WARNING);
            return false;
        }

        // Si le répertoire est vide, il n'y a rien à faire.
        if (!(new FilesystemIterator($directory))->valid()) {
            return true;
        }

        // Ouvre le répertoire
        $handle = opendir($directory);
        if ($handle === false) {
            trigger_error("Impossible d'ouvrir le répertoire pour le nettoyage : " . $directory, E_USER_WARNING);
            return false;
        }

        $success = true; // Variable pour suivre le succès global

        while (false !== ($entry = readdir($handle))) {
            // Ignore les entrées '.' et '..'
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $entry;

            // Si c'est un répertoire, on le supprime récursivement avec la nouvelle fonction deleteDirectory
            if (self::isDirectory($path)) {
                if (!self::deleteDirectory($path)) {
                    $success = false; // Marque un échec mais continue de tenter de supprimer le reste
                    trigger_error("Échec de la suppression du sous-répertoire lors du nettoyage : " . $path, E_USER_WARNING);
                }
            }
            // Si c'est un fichier, on le supprime
            elseif (self::isFile($path)) {
                if (!unlink($path)) {
                    $success = false; // Marque un échec mais continue de tenter de supprimer le reste
                    trigger_error("Impossible de supprimer le fichier lors du nettoyage : " . $path, E_USER_WARNING);
                }
            }
        }

        closedir($handle);
        return $success;
    }

    /**
     * Copy an existing file to another location on the disk.
     *
     * @param string $path Le chemin complet du fichier source.
     * @param string $target Le chemin complet de la destination avec le nom de fichier.
     * @return bool True si le fichier a été copié avec succès, false sinon.
     */
    public static function copy(string $path, string $target): bool
    {
        // Vérifie si le fichier source existe et est bien un fichier.
        if (!self::isFile($path)) {
            trigger_error("Le fichier source spécifié n'existe pas ou n'est pas un fichier : " . $path, E_USER_WARNING);
            return false;
        }

        // Extrait le répertoire de destination du chemin cible.
        $targetDirectory = dirname($target);

        // Crée le répertoire de destination si nécessaire, y compris les sous-répertoires.
        // Utilise la nouvelle fonction makeDirectory.
        if (!self::makeDirectory($targetDirectory)) {
            // Si la création du répertoire échoue, on ne peut pas copier le fichier.
            trigger_error("Impossible de créer le répertoire de destination pour la copie : " . $targetDirectory, E_USER_WARNING);
            return false;
        }

        // Tente de copier le fichier.
        // La fonction copy() de PHP retourne true en cas de succès, false en cas d'échec.
        if (!copy($path, $target)) {
            trigger_error("Impossible de copier le fichier de '" . $path . "' vers '" . $target . "'", E_USER_WARNING);
            return false;
        }

        return true;
    }

    /**
     * May be used to remove a directory, including all of its files, from the disk
     * Utilisée par cleanDirectory et potentiellement d'autres fonctions.
     *
     * @param string $directory Le chemin du répertoire à supprimer.
     * @return bool True si le répertoire a été supprimé avec succès, false sinon.
     */
    public static function deleteDirectory(string $directory): bool
    {
        if (!self::isDirectory($directory)) {
            trigger_error("Le chemin spécifié n'est pas un répertoire valide ou n'existe pas : " . $directory, E_USER_WARNING);
            return false;
        }

        $items = scandir($directory);
        if ($items === false) {
            trigger_error("Impossible de scanner le répertoire pour la suppression récursive : " . $directory, E_USER_WARNING);
            return false;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (self::isDirectory($path)) {
                // Appel récursif pour les sous-répertoires
                if (!self::deleteDirectory($path)) {
                    return false; // Échec de la suppression d'un sous-répertoire
                }
            } 
            elseif (self::isFile($path)) {
                // Suppression du fichier
                if (!unlink($path)) {
                    trigger_error("Impossible de supprimer le fichier lors de la suppression récursive du répertoire : " . $path, E_USER_WARNING);
                    return false; // Échec de la suppression d'un fichier
                }
            }
        }

        // Enfin, supprime le répertoire vide lui-même
        if (!rmdir($directory)) {
            trigger_error("Impossible de supprimer le répertoire : " . $directory, E_USER_WARNING);
            return false;
        }

        return true;
    }

    /**
     * Returns an array of all the directories directly within a given directory (non-recursive).
     *
     * @param string $directory Le chemin du répertoire à scanner.
     * @return array Un tableau des sous-répertoires directs trouvés.
     */
    public static function directories(string $directory): array
    {
        if (!self::isDirectory($directory)) {
            trigger_error("Le répertoire spécifié n'existe pas ou n'est pas un répertoire valide : " . $directory, E_USER_WARNING);
            return [];
        }

        $foundDirectories = [];

        // scandir() est efficace pour lister le contenu direct d'un répertoire.
        $items = scandir($directory);

        if ($items === false) {
            trigger_error("Impossible de lire le contenu du répertoire : " . $directory, E_USER_WARNING);
            return [];
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            // Vérifie si l'élément est un répertoire.
            if (self::isDirectory($path)) {
                $foundDirectories[] = $path;
            }
        }

        return $foundDirectories;
    }

    /**
     * Determine if a file or directory exists.
     *
     * @param string $path Le chemin à vérifier (fichier ou répertoire).
     * @return bool True si le fichier ou le répertoire existe, false sinon.
     */
    public static function exists(string $filePath): bool
    {
        // Utilise la fonction native file_exists() de PHP.
        return file_exists($filePath);
    }

    /**
     * Extract the file extension from a file path.
     *
     * @param string $filePath Le chemin complet du fichier.
     * @return string L'extension du fichier, sans le point (ex: "txt", "jpg", "tar.gz"). Retourne une chaîne vide si aucune extension n'est trouvée.
     */
    public static function extension(string $filePath): string
    {
        // Utilise pathinfo() qui est une fonction PHP robuste pour analyser les chemins de fichiers.
        // PATHINFO_EXTENSION retourne uniquement l'extension.
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        // pathinfo() retourne une chaîne vide si aucune extension n'est trouvée, ce qui est le comportement souhaité.
        return $extension;
    }

    /**
     * Returns an array of all of the files in a directory (non-recursive).
     *
     * @param string $directory Le chemin du répertoire à scanner.
     * @return array Un tableau de tous les chemins de fichiers directement contenus dans le répertoire.
     */
    public static function files(string $directory): array
    {
        // Vérifie si le chemin est bien un répertoire existant.
        if (!self::isDirectory($directory)) {
            trigger_error("Le répertoire spécifié n'existe pas ou n'est pas un répertoire valide : " . $directory, E_USER_WARNING);
            return [];
        }

        $foundFiles = [];

        // scandir() est efficace pour lister le contenu direct d'un répertoire.
        $items = scandir($directory);

        if ($items === false) {
            trigger_error("Impossible de lire le contenu du répertoire : " . $directory, E_USER_WARNING);
            return [];
        }

        foreach ($items as $item) {
            // Ignore les entrées '.' et '..'
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            // Vérifie si l'élément est un fichier en utilisant self::isFile().
            if (self::isFile($path)) { // self::isFile est préférable car elle assure que c'est bien un fichier.
                $foundFiles[] = $path;
            }
        }

        return $foundFiles;
    }

    /**
     * Retrieve the contents of a given file.
     *
     * @param string $filePath Le chemin complet du fichier à lire.
     * @return string|false Le contenu du fichier en cas de succès, ou false en cas d'échec (fichier non trouvé, permissions, etc.).
     */
    public static function get(string $filePath): string|false
    {
        // Vérifie si le fichier existe avant de tenter de le lire.
        if (!self::isFile($filePath)) {
            trigger_error("Le fichier spécifié n'existe pas ou n'est pas un fichier : " . $filePath, E_USER_WARNING);
            return false;
        }

        // Tente de lire le contenu du fichier.
        // file_get_contents() est la fonction PHP native pour cette tâche.
        $contents = file_get_contents($filePath);

        if ($contents === false) {
            // Cela peut arriver si le fichier existe mais n'est pas lisible (problème de permissions, etc.).
            trigger_error("Impossible de lire le contenu du fichier : " . $filePath, E_USER_WARNING);
            return false;
        }

        return $contents;
    }

    /**
     * Determine if the given path is a directory.
     *
     * @param string $directory Le chemin à vérifier.
     * @return bool True si le chemin est un répertoire existant, false sinon.
     */
    public static function isDirectory(string $directory): bool
    {
        // Utilise la fonction native is_dir() de PHP.
        return is_dir($directory);
    }

    /**
     * Determine if the given path is a file.
     *
     * @param string $path Le chemin à vérifier.
     * @return bool True si le chemin est un fichier existant, false sinon.
     */
    public static function isFile(string $path): bool
    {
        // Utilise la fonction native is_file() de PHP.
        return is_file($path);
    }

    /**
     * Determine if the given path is writable.
     *
     * @param string $path Le chemin à vérifier (fichier ou répertoire).
     * @return bool True si le chemin est accessible en écriture, false sinon.
     */
    public static function isWritable(string $path): bool
    {
        // Vérifie d'abord si le chemin existe, car is_writable() peut retourner false
        // ou des avertissements si le chemin n'existe pas.
        if (!self::exists($path)) {
            // Si le chemin n'existe pas, nous devons considérer le répertoire parent.
            // Si le répertoire parent est inscriptible, alors on peut potentiellement écrire ici.
            // Pour être strict "le chemin donné est-il inscriptible", on retourne false si le chemin n'existe pas.
            // Si l'intention était "peut-on écrire À cet endroit", il faudrait vérifier le répertoire parent.
            // Pour l'objectif actuel "est-ce que le chemin donné est inscriptible", cela implique qu'il existe.
            return false;
        }

        // Utilise la fonction native is_writable() de PHP.
        return is_writable($path);
    }

    /**
     * Returns the UNIX timestamp of the last time the file was modified.
     *
     * @param string $filePath Le chemin complet du fichier.
     * @return int|false Le timestamp UNIX de la dernière modification en cas de succès, ou false en cas d'échec.
     */
    public static function lastModified(string $filePath): int|false
    {
        // Vérifie si le chemin est bien un fichier existant avant de tenter d'obtenir son horodatage.
        if (!self::isFile($filePath)) {
            trigger_error("Le chemin spécifié n'est pas un fichier valide ou n'existe pas : " . $filePath, E_USER_WARNING);
            return false;
        }

        // Utilise la fonction native filemtime() de PHP.
        // filemtime() retourne le timestamp de la dernière modification.
        $timestamp = filemtime($filePath);

        if ($timestamp === false) {
            // Cela peut arriver si le fichier existe mais qu'il y a des problèmes de permissions pour obtenir l'info.
            trigger_error("Impossible d'obtenir l'horodatage de dernière modification pour le fichier : " . $filePath, E_USER_WARNING);
            return false;
        }

        return $timestamp;
    }

    /**
     * Will create the given directory, including any needed sub-directories.
     *
     * @param string $directory Le chemin du répertoire à créer.
     * @param int $mode Les permissions du répertoire (par défaut 0755).
     * @param bool $recursive Indique si les répertoires parents doivent être créés récursivement.
     * @return bool True si le répertoire a été créé avec succès ou existe déjà, false sinon.
     */
    public static function makeDirectory(string $directory, int $mode = 0755, bool $recursive = true): bool
    {
        // Si le répertoire existe déjà et est un répertoire, on considère que c'est un succès.
        if (self::isDirectory($directory)) {
            return true;
        }

        // Tente de créer le répertoire.
        // Utilise le mode et l'option récursive.
        if (!mkdir($directory, $mode, $recursive)) {
            trigger_error("Impossible de créer le répertoire : " . $directory, E_USER_WARNING);
            return false;
        }

        return true;
    }

    /**
     * Get the mime-type of a given file.
     *
     * @param string $path Le chemin complet du fichier.
     * @return string|false Le type MIME du fichier (ex: "text/plain", "image/jpeg"), ou false en cas d'échec.
     */
    public static function mimeType(string $path): string|false
    {
        // Vérifie si le chemin est bien un fichier existant.
        if (!self::isFile($path)) {
            trigger_error("Le chemin spécifié n'est pas un fichier valide ou n'existe pas : " . $path, E_USER_WARNING);
            return false;
        }

        // Tente de déterminer le type MIME.
        // Utilise finfo_open et finfo_file pour une détection plus robuste que mime_content_type.
        // C'est la méthode recommandée pour PHP 5.3+
        $finfo = finfo_open(FILEINFO_MIME_TYPE); // Retourne le type MIME, e.g., "text/plain"

        if ($finfo === false) {
            trigger_error("Impossible d'ouvrir la base de données de types MIME. Vérifiez l'extension 'fileinfo'.", E_USER_WARNING);
            return false;
        }

        $mimeType = finfo_file($finfo, $path);
        finfo_close($finfo);

        if ($mimeType === false) {
            trigger_error("Impossible de déterminer le type MIME du fichier : " . $path, E_USER_WARNING);
            return false;
        }

        return $mimeType;
    }

    /**
     * Move an existing file to a new location on the disk.
     * Créera le répertoire de destination si nécessaire.
     *
     * @param string $path Le chemin complet du fichier source.
     * @param string $target Le chemin complet de la destination (peut inclure le nouveau nom de fichier).
     * @return bool True si le fichier a été déplacé avec succès, false sinon.
     */
    public static function move(string $path, string $target): bool
    {
        // 1. Vérifier si le fichier source existe et est bien un fichier.
        if (!self::isFile($path)) {
            trigger_error("Le fichier source spécifié n'existe pas ou n'est pas un fichier : " . $path, E_USER_WARNING);
            return false;
        }

        // 2. Extraire le répertoire de destination.
        $targetDirectory = dirname($target);

        // 3. Créer le répertoire de destination si nécessaire.
        // Utilise la fonction makeDirectory qui gère la création récursive.
        if (!self::makeDirectory($targetDirectory)) {
            trigger_error("Impossible de créer le répertoire de destination pour le déplacement : " . $targetDirectory, E_USER_WARNING);
            return false;
        }

        // 4. Déplacer le fichier.
        // La fonction rename() de PHP est utilisée pour déplacer/renommer des fichiers.
        // C'est une opération atomique sur le même système de fichiers.
        if (!rename($path, $target)) {
            trigger_error("Impossible de déplacer le fichier de '" . $path . "' vers '" . $target . "'", E_USER_WARNING);
            return false;
        }

        return true;
    }

    /**
     * Extract the file name from a file path (without extension).
     *
     * @param string $filePath Le chemin complet du fichier.
     * @return string Le nom du fichier sans l'extension. Retourne une chaîne vide si le chemin est invalide ou ne contient pas de nom de fichier.
     */
    public static function name(string $filePath): string
    {
        // Utilise pathinfo() avec PATHINFO_FILENAME pour obtenir le nom de fichier sans l'extension.
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);

        // pathinfo() retourne une chaîne vide si aucun nom de fichier n'est trouvé,
        // ce qui est le comportement souhaité.
        return $fileName;
    }

    /**
     * Insert content at the beginning of a file.
     * Crée le fichier s'il n'existe pas.
     *
     * @param string $filePath Le chemin complet du fichier.
     * @param string $prependedText Le contenu à insérer au début du fichier.
     * @return bool True si le contenu a été ajouté avec succès, false sinon.
     */
    public static function prepend(string $filePath, string $prependedText): bool
    {
        // 1. Récupérer le contenu actuel du fichier, s'il existe.
        // Si le fichier n'existe pas, get() retournera false.
        $currentContents = self::get($filePath); // Utilise la fonction get() pour récupérer le contenu.

        // Si le fichier n'existe pas, $currentContents sera false. On le traite comme une chaîne vide.
        if ($currentContents === false) {
            // S'il n'existe pas, on tente de créer le répertoire parent si nécessaire.
            $directory = dirname($filePath);
            if (!self::makeDirectory($directory)) {
                // makeDirectory déclenchera déjà un trigger_error si elle échoue.
                return false;
            }
            $currentContents = ''; // Le fichier sera créé, son contenu est initialement vide.
        }

        // 2. Combiner le nouveau contenu avec l'ancien.
        $newContents = $prependedText . $currentContents;

        // 3. Écrire le contenu combiné dans le fichier (cela écrasera l'ancien contenu).
        $result = file_put_contents($filePath, $newContents, LOCK_EX);

        if ($result === false) {
            trigger_error("Impossible d'écrire le contenu prépendu dans le fichier : " . $filePath, E_USER_WARNING);
            return false;
        }

        return true;
    }

    /**
     * Store a file on disk (write content to a file, replacing existing content).
     * Crée le fichier et les répertoires parents s'ils n'existent pas.
     *
     * @param string $filePath Le chemin complet du fichier où stocker le contenu.
     * @param string $contents Le contenu à écrire dans le fichier.
     * @return bool True si le contenu a été stocké avec succès, false sinon.
     */
    public static function put(string $filePath, string $contents): bool
    {
        // 1. Extraire le répertoire parent du fichier.
        $directory = dirname($filePath);

        // 2. Créer le répertoire parent si nécessaire.
        // makeDirectory gère la création récursive et retourne true si le répertoire existe déjà ou est créé.
        if (!self::makeDirectory($directory)) {
            // makeDirectory déclenchera déjà un trigger_error si elle échoue.
            return false;
        }

        // 3. Écrire le contenu dans le fichier.
        // file_put_contents() écrit le contenu dans le fichier, écrasant le contenu existant par défaut.
        // LOCK_EX est utilisé pour verrouiller le fichier pendant l'écriture.
        $result = file_put_contents($filePath, $contents, LOCK_EX);

        if ($result === false) {
            trigger_error("Impossible de stocker le contenu dans le fichier : " . $filePath, E_USER_WARNING);
            return false;
        }

        return true;
    }

    /**
     * Get the size of the file in bytes.
     *
     * @param string $filePath Le chemin complet du fichier.
     * @return int|false La taille du fichier en octets en cas de succès, ou false en cas d'échec.
     */
    public static function size(string $filePath): int|false
    {
        // 1. Vérifier si le chemin est bien un fichier existant.
        if (!self::isFile($filePath)) {
            trigger_error("Le chemin spécifié n'est pas un fichier valide ou n'existe pas : " . $filePath, E_USER_WARNING);
            return false;
        }

        // 2. Obtenir la taille du fichier.
        // Utilise la fonction native filesize() de PHP.
        $fileSize = filesize($filePath);

        if ($fileSize === false) {
            // Cela peut arriver si le fichier existe mais qu'il y a des problèmes de permissions.
            trigger_error("Impossible d'obtenir la taille du fichier : " . $filePath, E_USER_WARNING);
            return false;
        }

        return $fileSize;
    }

    /**
     * Get the file type of a given path (file, dir, or unknown).
     *
     * @param string $path Le chemin à vérifier.
     * @return string Le type du chemin ("file", "dir", ou "unknown").
     */
    public static function type(string $path): string
    {
        if (self::isFile($path)) {
            return 'file';
        }

        if (self::isDirectory($path)) {
            return 'dir';
        }

        // Si ce n'est ni un fichier ni un répertoire (ou s'il n'existe pas), c'est "unknown".
        // La fonction filetype() de PHP pourrait être utilisée, mais elle déclenche des avertissements
        // si le fichier n'existe pas ou si le type est autre (ex: link, fifo, char, block, socket).
        // Cette approche est plus robuste et cohérente avec nos autres vérifications.
        return 'unknown';
    }
}