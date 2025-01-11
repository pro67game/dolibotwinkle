# Module 2FA pour Dolibarr

Ce module ajoute l'authentification à deux facteurs (2FA) à Dolibarr via Google Authenticator ou toute autre application compatible TOTP.

## Installation

1. Copiez le dossier `mod2fa` dans le répertoire `htdocs/` de votre installation Dolibarr
2. Dans Dolibarr, allez dans Configuration > Modules/Applications
3. Recherchez "2FA" dans la liste des modules et activez-le
4. Configurez le module dans Configuration > 2FA

## Configuration

Dans la configuration du module, vous pouvez :
- Activer/désactiver l'obligation du 2FA pour tous les utilisateurs
- Gérer les paramètres de sécurité

## Utilisation

1. Les utilisateurs peuvent activer le 2FA dans leur profil
2. Lors de l'activation, un QR code est généré à scanner avec Google Authenticator
3. Une fois activé, le code 2FA sera demandé à chaque connexion

## Sécurité

- Les secrets 2FA sont stockés de manière sécurisée dans la base de données
- L'implémentation suit les standards TOTP (RFC 6238)
- Compatible avec Google Authenticator et autres applications TOTP

## Support

Pour toute question ou problème, veuillez créer une issue sur le dépôt du projet.