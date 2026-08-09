import { defineBoot } from '#q-app';
import axios, { type AxiosInstance } from 'axios';

declare module 'vue' {
  interface ComponentCustomProperties {
    $axios: AxiosInstance;
    $api: AxiosInstance;
  }
}

const apiBaseUrl =
  import.meta.env.VITE_API_URL ||
  (import.meta.env.PROD ? 'https://api.modelarcve.com/api' : 'http://localhost:8000/api')

const api = axios.create({
  baseURL: apiBaseUrl,
  timeout: 12000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

export default defineBoot(({ app }) => {
  app.config.globalProperties.$axios = axios;
  app.config.globalProperties.$api = api;
});

export { api };
