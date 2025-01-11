import { authenticator } from 'otplib';

// Génère une nouvelle clé secrète pour l'utilisateur
export const generateSecret = () => {
  return authenticator.generateSecret();
};

// Génère un token TOTP basé sur la clé secrète
export const generateToken = (secret: string) => {
  return authenticator.generate(secret);
};

// Vérifie si le token fourni est valide
export const verifyToken = (token: string, secret: string) => {
  return authenticator.verify({ token, secret });
};