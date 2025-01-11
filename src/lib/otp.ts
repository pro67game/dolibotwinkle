import { authenticator } from 'otplib';

// Configure authenticator with browser-compatible options
authenticator.options = {
  window: 1,
  step: 30
};

// Generate a new secret key
export const generateSecret = () => {
  const array = new Uint8Array(20);
  crypto.getRandomValues(array);
  return Array.from(array)
    .map(byte => byte.toString(16).padStart(2, '0'))
    .join('');
};

// Generate a TOTP token
export const generateToken = (secret: string) => {
  try {
    // Convert hex secret to base32 for TOTP generation
    const base32Secret = hexToBase32(secret);
    return authenticator.generate(base32Secret);
  } catch (error) {
    console.error('Error generating token:', error);
    return '';
  }
};

// Verify if the provided token is valid
export const verifyToken = (token: string, secret: string) => {
  try {
    const base32Secret = hexToBase32(secret);
    return authenticator.verify({ token, secret: base32Secret });
  } catch (error) {
    console.error('Error verifying token:', error);
    return false;
  }
};

// Helper function to convert hex to base32
function hexToBase32(hex: string): string {
  const base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
  let bits = '';
  
  // Convert hex to binary string
  for (let i = 0; i < hex.length; i += 2) {
    const byte = parseInt(hex.substr(i, 2), 16);
    bits += byte.toString(2).padStart(8, '0');
  }
  
  // Convert binary to base32
  let base32 = '';
  for (let i = 0; i + 5 <= bits.length; i += 5) {
    const chunk = bits.substr(i, 5);
    base32 += base32Chars[parseInt(chunk, 2)];
  }
  
  // Pad with '=' if necessary
  while (base32.length % 8 !== 0) {
    base32 += '=';
  }
  
  return base32;
}