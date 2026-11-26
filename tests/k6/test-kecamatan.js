import http from 'k6/http';
import { check, group, sleep } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export const options = {
  stages: [
    { duration: '10s', target: 5 },  // Ramp-up: 5 pengguna virtual
    { duration: '20s', target: 5 },  // Tahan di 5 pengguna virtual
    { duration: '5s', target: 0 },   // Ramp-down
  ],
  thresholds: {
    http_req_duration: ['p(95)<2000'],
    http_req_failed: ['rate<0.05'],
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

export default function () {
  let csrfToken = '';

  group('1. Buka Halaman Login', function () {
    const res = http.get(`${BASE_URL}/warga/login`);
    check(res, {
      'Halaman login status 200': (r) => r.status === 200,
    });
    csrfToken = getCsrfToken(res.html(), res.body);
  });

  sleep(1);

  group('2. Login Sebagai Admin Kecamatan', function () {
    const payload = {
      login: 'kecamatan@gmail.com',
      password: '12345678',
      _token: csrfToken,
    };

    const res = http.post(`${BASE_URL}/warga/login`, payload, {
      redirects: 2,
    });

    check(res, {
      'Login kecamatan berhasil': (r) => r.status === 200 || r.status === 302,
    });
  });

  sleep(1);

  group('3. Akses Dashboard Kecamatan', function () {
    const res = http.get(`${BASE_URL}/kecamatan/dashboard`);
    check(res, {
      'Dashboard kecamatan status 200': (r) => r.status === 200,
    });
  });

  sleep(1);

  group('4. Akses Validasi Laporan', function () {
    const res = http.get(`${BASE_URL}/kecamatan/validasi`);
    check(res, {
      'Halaman validasi status 200': (r) => r.status === 200,
    });
  });

  sleep(1);

  group('5. Akses Rekapitulasi Laporan', function () {
    const res = http.get(`${BASE_URL}/kecamatan/rekapitulasi`);
    check(res, {
      'Halaman rekapitulasi status 200': (r) => r.status === 200,
    });
  });

  sleep(1);

  group('6. Akses Statistik Kecamatan', function () {
    const res = http.get(`${BASE_URL}/kecamatan/statistik`);
    check(res, {
      'Halaman statistik status 200': (r) => r.status === 200,
    });
  });

  sleep(1);

  group('7. Akses Peta GIS', function () {
    const res = http.get(`${BASE_URL}/kecamatan/peta-gis`);
    check(res, {
      'Peta GIS status 200': (r) => r.status === 200,
    });
  });

  sleep(1);

  group('8. Akses Halaman BAST Kecamatan', function () {
    const res = http.get(`${BASE_URL}/kecamatan/bast`);
    check(res, {
      'Halaman BAST kecamatan status 200': (r) => r.status === 200,
    });
  });

  sleep(1);
}

