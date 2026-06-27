import http from 'k6/http';
import { check, sleep } from 'k6';
import {
  BASE_URL,
  DASHBOARD_PATH,
  EMAIL,
  PASSWORD,
  TASKS_PATH,
  buildRampStages,
  extractCsrfToken,
  parseIntEnv,
} from './lib/config.js';

const TARGET_VUS = parseIntEnv('VUS', 15);
const RAMP_UP = __ENV.RAMP_UP || '30s';
const HOLD = __ENV.DURATION || '1m';
const RAMP_DOWN = __ENV.RAMP_DOWN || '15s';
const THINK_TIME = parseFloat(__ENV.THINK_TIME || '1');

export const options = {
  scenarios: {
    staff_readonly: {
      executor: 'ramping-vus',
      startVUs: 0,
      stages: buildRampStages(TARGET_VUS, RAMP_UP, HOLD, RAMP_DOWN),
      gracefulRampDown: '10s',
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.15'],
    'http_req_duration{name:dashboard}': ['p(95)<3000'],
    'http_req_duration{name:tasks}': ['p(95)<4000'],
    checks: ['rate>0.80'],
  },
};

export default function () {
  const jar = http.cookieJar();

  const loginPage = http.get(`${BASE_URL}/`, {
    jar,
    tags: { name: 'login_page' },
  });

  if (!check(loginPage, { 'login page 200': (r) => r.status === 200 })) {
    return;
  }

  const token = extractCsrfToken(loginPage.body);

  const login = http.post(
    `${BASE_URL}/`,
    {
      _token: token,
      email: EMAIL,
      password: PASSWORD,
    },
    {
      jar,
      tags: { name: 'login_post' },
      redirects: 0,
    },
  );

  check(login, {
    'login tanpa csrf error': (r) => r.status !== 419,
  });

  const dashboard = http.get(`${BASE_URL}${DASHBOARD_PATH}`, {
    jar,
    tags: { name: 'dashboard' },
    redirects: 0,
  });

  if (!check(dashboard, { 'dashboard 200 setelah login': (r) => r.status === 200 })) {
    return;
  }

  const tasks = http.get(`${BASE_URL}${TASKS_PATH}`, {
    jar,
    tags: { name: 'tasks' },
    redirects: 0,
  });

  check(tasks, {
    'tasks 200': (r) => r.status === 200,
  });

  sleep(THINK_TIME);
}
