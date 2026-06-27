export const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:9000';
export const EMAIL = __ENV.EMAIL || 'crocodic3@gmail.com';
export const PASSWORD = __ENV.PASSWORD || 'crocodic123';
export const DASHBOARD_PATH = __ENV.DASHBOARD_PATH || '/staff/dashboard';
export const TASKS_PATH = __ENV.TASKS_PATH || '/staff/tasks';

export function extractCsrfToken(html) {
  const match = String(html).match(/name="_token"\s+value="([^"]+)"/);
  if (!match) {
    throw new Error('CSRF token tidak ditemukan di halaman login.');
  }
  return match[1];
}

export function parseIntEnv(name, fallback) {
  const value = parseInt(__ENV[name] || String(fallback), 10);
  return Number.isFinite(value) ? value : fallback;
}

export function buildRampStages(targetVus, rampUp, hold, rampDown) {
  return [
    { duration: rampUp, target: targetVus },
    { duration: hold, target: targetVus },
    { duration: rampDown, target: 0 },
  ];
}
