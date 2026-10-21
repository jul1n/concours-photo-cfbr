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

    // Noms d'hôte autorisés à servir le site (ex. ['houss.in', 'concours.barrages-cfbr.eu']).
    // L'en-tête Host n'est utilisé pour construire les liens d'e-mail que s'il figure ici.
    'allowed_hosts' => [
        'concours.barrages-cfbr.eu',
        'concours-photo.barrages-cfbr.eu',
        'www.concours-photo.barrages-cfbr.eu',
        // 'houss.in',  // uniquement pour une préproduction
    ],

    // Hash du mot de passe de maintenance (dashboard init/DB/jury).
    'maintenance_password_hash' => '$2y$10$REMPLACEZ_MOI_PAR_UN_VRAI_HASH_GENERE_LOCALEMENT',

    // En-tête portant l'IP réelle derrière un proxy/CDN (ex. 'HTTP_X_FORWARDED_FOR', 'HTTP_CF_CONNECTING_IP').
    // Laisser vide si le site est joignable directement : sinon l'IP vient de REMOTE_ADDR.
    // Si tous les visiteurs apparaissent avec la même IP (proxy de l'hébergeur), renseigner cet en-tête,
    // faute de quoi la limite par IP s'appliquerait à tout le monde à la fois.
    'trusted_proxy_header' => '',

    // Durée de validité d'un lien de connexion jury (en secondes). 3600 = 1h.
    'jury_token_ttl' => 3600,

    // Mettre à false en production pour désactiver l'affichage des liens de debug.
    'debug' => false,
];
