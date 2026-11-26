import http from 'k6/http';
import { check } from 'k6';

// Script singkat untuk mendiagnosis koneksi ke server web
const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export const options = {
  vus: 1,
  iterations: 1,
};

export default function () {
  console.log(`\n========================================`);
  console.log(`[CEK KONEKSI] Menghubungi: ${BASE_URL}/warga/login`);

  const res = http.get(`${BASE_URL}/warga/login`);

  console.log(`[HASIL] HTTP Status Code : ${res.status}`);
  console.log(`[HASIL] Waktu Respon     : ${res.timings.duration.toFixed(2)} ms`);

  if (res.status === 200) {
    console.log(`[SUKSES] Server berhasil dihubungi dan mengembalikan halaman login!`);
    const match = res.body.match(/name="_token"\s+value="([^"]+)"/);
    if (match) {
      console.log(`[SUKSES] CSRF Token ditemukan: ${match[1].substring(0, 10)}...`);
    } else {
      console.log(`[PERINGATAN] Form token CSRF tidak terdeteksi di halaman.`);
    }
  } else if (res.status === 404) {
    console.log(`[ERROR 404] Halaman tidak ditemukan! Periksa apakah URL dan port server sudah benar.`);
  } else if (res.status === 0) {
    console.log(`[ERROR KONEKSI] k6 tidak bisa terhubung ke server! Periksa apakah server web sudah berjalan.`);
  } else {
    console.log(`[ERROR ${res.status}] Server mengembalikan status ${res.status}.`);
    console.log(`Cuplikan isi respon: ${res.body ? res.body.substring(0, 200) : 'KOSONG'}`);
  }
  console.log(`========================================\n`);

  check(res, {
    'Server merespon dengan status 200': (r) => r.status === 200,
  });
}

