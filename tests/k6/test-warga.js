import http from 'k6/http';
import { check, group, sleep } from 'k6';

// Ganti URL dengan base URL server lokal Anda jika berbeda
const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export const options = {
  stages: [
    { duration: '10s', target: 5 },  // Ramp-up: 5 pengguna virtual
    { duration: '20s', target: 5 },  // Tahan di 5 pengguna virtual
    { duration: '5s', target: 0 },   // Ramp-down
  ],
  thresholds: {
    http_req_duration: ['p(95)<2000'], // 95% request selesai di bawah 2 detik
    http_req_failed: ['rate<0.05'],    // Kegagalan request < 5%
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

  group('1. Buka Halaman Login Warga', function () {
    const res = http.get(`${BASE_URL}/warga/login`);
    check(res, {
      'Halaman login status 200': (r) => r.status === 200,
    });
    csrfToken = getCsrfToken(res.html(), res.body);
  });

  sleep(1);

  group('2. Login Sebagai Warga', function () {
    const payload = {
      login: 'test@gmail.com',
      password: '12345678', // password default seeder
      _token: csrfToken,
    };

    const res = http.post(`${BASE_URL}/warga/login`, payload, {
      redirects: 2,
    });

    check(res, {
      'Login warga berhasil': (r) => r.status === 200 || r.status === 302,
    });
  });

  sleep(1);

  group('3. Akses Dashboard Warga', function () {
    const res = http.get(`${BASE_URL}/warga/dashboard`);
    check(res, {
      'Dashboard warga status 200': (r) => r.status === 200,
      'Dashboard memuat data warga': (r) => r.body.includes('Warga') || r.body.includes('SIGAP'),
    });
  });

  sleep(1);

  group('4. Akses Daftar Riwayat Laporan Warga', function () {
    const res = http.get(`${BASE_URL}/warga/laporan`);
    check(res, {
      'Halaman laporan status 200': (r) => r.status === 200,
    });
  });

  sleep(1);

  group('5. Akses Form Buat Laporan Baru', function () {
    const res = http.get(`${BASE_URL}/warga/laporan/create`);
    check(res, {
      'Form buat laporan status 200': (r) => r.status === 200,
    });
  });

  sleep(1);
}
