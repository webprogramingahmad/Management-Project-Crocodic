import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE_URL, buildRampStages, parseIntEnv } from './lib/config.js';

const TARGET_VUS = parseIntEnv('VUS', 15);
const RAMP_UP = __ENV.RAMP_UP || '20s';
const HOLD = __ENV.DURATION || '1m';
const RAMP_DOWN = __ENV.RAMP_DOWN || '10s';

export const options = {
  scenarios: {
    login_page_readonly: {
      executor: 'ramping-vus',
      startVUs: 0,
      stages: buildRampStages(TARGET_VUS, RAMP_UP, HOLD, RAMP_DOWN),
      gracefulRampDown: '10s',
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.05'],
    'http_req_duration{name:login_page}': ['p(95)<3000'],
  },
};

export default function () {
  const res = http.get(`${BASE_URL}/`, {
    tags: { name: 'login_page' },
  });

  check(res, {
    'login page status 200': (r) => r.status === 200,
    'login page has form': (r) => r.body && r.body.includes('name="_token"'),
  });

  sleep(1);
}
