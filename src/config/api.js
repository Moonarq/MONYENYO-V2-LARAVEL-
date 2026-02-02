// src/config/api.js
const API_CONFIG = {
  BASE_URL: 'http://localhost:8000',
  STORAGE_URL: 'http://localhost:8000/storage',
};

export const API_ENDPOINTS = {
  PRODUCTS: `${API_CONFIG.BASE_URL}/api/products`,
  VOUCHERS: `${API_CONFIG.BASE_URL}/api/vouchers`,
  CHECKOUT: `${API_CONFIG.BASE_URL}/api/checkout`,
};

export const getImageUrl = (imagePath) => {
  return `${API_CONFIG.STORAGE_URL}/${imagePath}`;
};

export default API_CONFIG;