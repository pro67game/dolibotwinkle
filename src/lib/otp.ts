import { authenticator } from 'otplib';

// Function to generate random bytes in browser
const generateRandomBytes = (length: number) => {
  const array = new Uint8Array(length);
  crypto.getRandomValues(array);
  return array;
};

// Configure authenticator to use browser-compatible random bytes
authenticator.options = {
  ...authenticator.options,
  // Set a static window for token validation
  window: 1,
  // Use browser-compatible crypto
  createRandomBytes: generateRandomBytes,
};

// Génère une nouvelle clé secrète pour l'utilisateur
export const generateSecret = () => {
  return authenticator.generateSecret(20); // 20 bytes = 160 bits
};

// Génère un token TOTP basé sur la clé secrète
export const generateToken = (secret: string) => {
  return authenticator.generate(secret);
};

// Vérifie si le token fourni est valide
export const verifyToken = (token: string, secret: string) => {
  return authenticator.verify({ token, secret });
};