import { authenticator } from 'otplib';

// Configure authenticator
authenticator.options = {
  window: 1,
  step: 30
};

// Generate a new secret key using browser crypto API
export const generateSecret = () => {
  const array = new Uint8Array(16);
  crypto.getRandomValues(array);
  return Array.from(array)
    .map(byte => byte.toString(16).padStart(2, '0'))
    .join('')
    .toUpperCase();
};

// Generate QR code URL
export const generateQRCodeUrl = (username: string, secret: string, issuer: string = 'MyApp') => {
  const otpauth = `otpauth://totp/${encodeURIComponent(issuer)}:${encodeURIComponent(username)}?secret=${secret}&issuer=${encodeURIComponent(issuer)}`;
  return `https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=${encodeURIComponent(otpauth)}`;
};

// Generate a TOTP token
export const generateToken = (secret: string) => {
  try {
    return authenticator.generate(secret);
  } catch (error) {
    console.error('Error generating token:', error);
    return '';
  }
};

// Verify if the provided token is valid
export const verifyToken = (token: string, secret: string) => {
  try {
    return authenticator.verify({ token, secret });
  } catch (error) {
    console.error('Error verifying token:', error);
    return false;
  }
};