import http from 'k6/http';
import { check, group, sleep } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';
const DEFAULT_PASSWORD = __ENV.TEST_PASSWORD || '12345678';

export const options = {
  // Abaikan error sertifikat SSL jika server lokal menggunakan self-signed HTTPS
  insecureSkipTLSVerify: true,

  scenarios: {
    // Skenario 1: Lalu lintas pengguna Warga
    beban_warga: {
      executor: 'ramping-vus',
      startVUs: 0,
      stages: [
        { duration: '10s', target: 6 },
        { duration: '20s', target: 6 },
        { duration: '5s', target: 0 },
      ],
      exec: 'skenarioWarga',
    },
    // Skenario 2: Lalu lintas Admin Kecamatan
    beban_kecamatan: {
      executor: 'ramping-vus',
      startVUs: 0,
      stages: [
        { duration: '10s', target: 2 },
        { duration: '20s', target: 2 },
        { duration: '5s', target: 0 },
      ],
      exec: 'skenarioKecamatan',
    },
    // Skenario 3: Lalu lintas Admin PUPR
    beban_pupr: {
      executor: 'ramping-vus',
      startVUs: 0,
      stages: [
        { duration: '10s', target: 2 },
        { duration: '20s', target: 2 },
        { duration: '5s', target: 0 },
      ],
      exec: 'skenarioPupr',
    },
  },
  thresholds: {
    http_req_duration: ['p(95)<3000'], // 95% respon harus < 3 detik
    http_req_failed: ['rate<0.05'],    // error < 5%
  },
};

function getCsrfToken(doc, body) {
  try {
    const el = doc.find('input[name="_token"]');
    if (el && el.attr('value')) return el.attr('value');
  } catch (e) {}

  const match = body.match(/name="_token"\s+value="([^"]+)"/);
  return match ? match[1] : '';
}

// -----------------------------------------------------------------
// SKENARIO 1: WARGA
// -----------------------------------------------------------------
export function skenarioWarga() {
  let token = '';

  group('WARGA - Login Flow', function () {
    const resLogin = http.get(`${BASE_URL}/warga/login`);
    check(resLogin, { 'GET Login Warga OK (200)': (r) => r.status === 200 });

    token = getCsrfToken(resLogin.html(), resLogin.body);

    const payload = {
      login: 'test@gmail.com',
      password: DEFAULT_PASSWORD,
      _token: token,
    };

    const res = http.post(`${BASE_URL}/warga/login`, payload, {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      redirects: 5,
    });

    check(res, { 'Warga login berhasil': (r) => r.status === 200 || r.status === 302 });
  });

  sleep(1);

  group('WARGA - Akses Fitur Utama', function () {
    const resDash = http.get(`${BASE_URL}/warga/dashboard`);
    check(resDash, { 'Warga dashboard OK': (r) => r.status === 200 });

    const resLaporan = http.get(`${BASE_URL}/warga/laporan`);
    check(resLaporan, { 'Warga daftar laporan OK': (r) => r.status === 200 });

    const resCreate = http.get(`${BASE_URL}/warga/laporan/create`);
    check(resCreate, { 'Warga form lapor OK': (r) => r.status === 200 });
  });

  sleep(2);
}

// -----------------------------------------------------------------
// SKENARIO 2: KECAMATAN
// -----------------------------------------------------------------
export function skenarioKecamatan() {
  let token = '';

  group('KECAMATAN - Login Flow', function () {
    const resLogin = http.get(`${BASE_URL}/warga/login`);
    check(resLogin, { 'GET Login Kecamatan OK (200)': (r) => r.status === 200 });

    token = getCsrfToken(resLogin.html(), resLogin.body);

    const payload = {
      login: 'kecamatan@gmail.com',
      password: DEFAULT_PASSWORD,
      _token: token,
    };

    const res = http.post(`${BASE_URL}/warga/login`, payload, {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      redirects: 5,
    });

    check(res, { 'Kecamatan login berhasil': (r) => r.status === 200 || r.status === 302 });
  });

  sleep(1);

  group('KECAMATAN - Akses Fitur Utama', function () {
    const resDash = http.get(`${BASE_URL}/kecamatan/dashboard`);
    check(resDash, { 'Kecamatan dashboard OK': (r) => r.status === 200 });

    const resVal = http.get(`${BASE_URL}/kecamatan/validasi`);
    check(resVal, { 'Kecamatan validasi OK': (r) => r.status === 200 });

    const resRekap = http.get(`${BASE_URL}/kecamatan/rekapitulasi`);
    check(resRekap, { 'Kecamatan rekapitulasi OK': (r) => r.status === 200 });

    const resBast = http.get(`${BASE_URL}/kecamatan/bast`);
    check(resBast, { 'Kecamatan BAST OK': (r) => r.status === 200 });
  });

  sleep(2);
}

// -----------------------------------------------------------------
// SKENARIO 3: PUPR
// -----------------------------------------------------------------
export function skenarioPupr() {
  let token = '';

  group('PUPR - Login Flow', function () {
    const resLogin = http.get(`${BASE_URL}/warga/login`);
    check(resLogin, { 'GET Login PUPR OK (200)': (r) => r.status === 200 });

    token = getCsrfToken(resLogin.html(), resLogin.body);

    const payload = {
      login: 'pupr@gmail.com',
      password: DEFAULT_PASSWORD,
      _token: token,
    };

    const res = http.post(`${BASE_URL}/warga/login`, payload, {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      redirects: 5,
    });

    check(res, { 'PUPR login berhasil': (r) => r.status === 200 || r.status === 302 });
  });

  sleep(1);

  group('PUPR - Akses Fitur Utama', function () {
    const resDash = http.get(`${BASE_URL}/pupr/dashboard`);
    check(resDash, { 'PUPR dashboard OK': (r) => r.status === 200 });

    const resTim = http.get(`${BASE_URL}/pupr/tim-lapangan`);
    check(resTim, { 'PUPR tim lapangan OK': (r) => r.status === 200 });

    const resRiw = http.get(`${BASE_URL}/pupr/riwayat`);
    check(resRiw, { 'PUPR riwayat OK': (r) => r.status === 200 });
  });

  sleep(2);
}
