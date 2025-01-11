import { authenticator } from 'otplib';
import base32Encode from 'base32-encode';

// Function to generate random bytes in browser and encode to base32
const generateRandomBytes = (length: number): string => {
  const array = new Uint8Array(length);
  crypto.getRandomValues(array);
  // Convert to base32 string
  return base32Encode(array, 'RFC4648', { padding: true });
};

// Configure authenticator
authenticator.options = {
  ...authenticator.options,
  // Set a static window for token validation
  window: 1,
  // Use browser-compatible crypto
  createRandomBytes: (length: number) => {
    const array = new Uint8Array(length);
    crypto.getRandomValues(array);
    return array;
  },
};

// Generate a new secret key for the user
export const generateSecret = () => {
  const randomBytes = new Uint8Array(20); // 20 bytes = 160 bits
  crypto.getRandomValues(randomBytes);
  return base32Encode(randomBytes, 'RFC4648', { padding: true });
};

// Generate a TOTP token based on the secret key
export const generateToken = (secret: string) => {
  return authenticator.generate(secret);
};

// Verify if the provided token is valid
export const verifyToken = (token: string, secret: string) => {
  return authenticator.verify({ token, secret });
};