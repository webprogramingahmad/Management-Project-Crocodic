import { EMAIL, PASSWORD } from './config.js';

/**
 * @typedef {{ email: string, password: string, name?: string }} StaffAccount
 */

/**
 * @returns {{ accounts: StaffAccount[], source: string }}
 */
export function loadStaffAccounts() {
  const accountsFile = __ENV.ACCOUNTS_FILE;
  if (accountsFile) {
    const raw = open(accountsFile);
    const parsed = JSON.parse(raw);
    const list = Array.isArray(parsed) ? parsed : parsed.accounts;
    if (!Array.isArray(list) || list.length === 0) {
      throw new Error(`ACCOUNTS_FILE kosong atau tidak valid: ${accountsFile}`);
    }
    return { accounts: list, source: accountsFile };
  }

  return {
    accounts: [{ email: EMAIL, password: PASSWORD }],
    source: 'single-account (EMAIL/PASSWORD env)',
  };
}

/**
 * Satu VU = satu akun stabil (tidak bentrok session Redis).
 *
 * @param {StaffAccount[]} accounts
 * @returns {StaffAccount}
 */
export function accountForVu(accounts) {
  if (!accounts.length) {
    throw new Error('Daftar akun staff kosong.');
  }
  const index = (__VU - 1) % accounts.length;
  return accounts[index];
}
