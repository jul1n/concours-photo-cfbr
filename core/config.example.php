<?php
/**
 * config.example.php — Modèle de configuration.
 *
 * COPIER ce fichier en `core/config.php` puis renseigner les valeurs.
 * `core/config.php` est ignoré par Git (voir .gitignore) : il ne doit JAMAIS
 * être commité car il contient des secrets.
 *
 * Générer un hash de mot de passe admin :
 *   php -r "echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT), PHP_EOL;"
 */

return [
    // Domaine canonique du site (sans slash final). Sert à construire les liens
    // e-mail en HTTPS sans se fier à l'en-tête Host (falsifiable).
    'base_url' => 'https://concours.barrages-cfbr.eu',

    // Adresse expéditeur par défaut des e-mails.
    'mail_from' => 'no-reply@barrages-cfbr.eu',

    // Hash bcrypt/argon2 du mot de passe d'administration (résultats/exports).
    // Remplacer par le hash généré avec password_hash().
    'admin_password_hash' => '$2y$10$REMPLACEZ_MOI_PAR_UN_VRAI_HASH_GENERE_LOCALEMENT',

    // Hash du mot de passe de maintenance (dashboard init/DB/jury).
    'maintenance_password_hash' => '$2y$10$REMPLACEZ_MOI_PAR_UN_VRAI_HASH_GENERE_LOCALEMENT',

    // Durée de validité d'un lien de connexion jury (en secondes). 3600 = 1h.
    'jury_token_ttl' => 3600,

    // Mettre à false en production pour désactiver l'affichage des liens de debug.
    'debug' => false,
];
