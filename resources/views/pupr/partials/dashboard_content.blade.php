<!-- KPI CARDS -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs relative overflow-hidden flex flex-col justify-between h-32 text-center">
        <div class="flex items-center justify-center">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">TERVALIDASI &amp; SPK CAMAT</div>
        </div>
        <div class="flex items-center justify-center h-full">
            <div class="text-4xl font-extrabold text-slate-900">{{ $statistik['diproses'] ?? 0 }}</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs relative overflow-hidden flex flex-col justify-between h-32 text-center">
        <div class="flex items-center justify-center">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">MENUNGGU VALIDASI CAMAT</div>
        </div>
        <div class="flex items-center justify-center h-full">
            <div class="text-4xl font-extrabold text-slate-900">{{ $statistik['menunggu'] ?? 0 }}</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs relative overflow-hidden flex flex-col justify-between h-32 text-center">
        <div class="flex items-center justify-center">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">SELESAI DIVERIFIKASI</div>
        </div>
        <div class="flex items-center justify-center h-full">
            <div class="text-4xl font-extrabold text-slate-900">{{ $statistik['selesai'] ?? 0 }}</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs relative overflow-hidden flex flex-col justify-between h-32 text-center">
        <div class="flex items-center justify-center">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">TOTAL LAPORAN MASUK</div>
        </div>
        <div class="flex items-center justify-center h-full">
            <div class="text-4xl font-extrabold text-slate-900">{{ $statistik['total'] ?? 0 }}</div>
        </div>
    </div>

</div>

<!-- DAFTAR LAPORAN (FULL WIDTH) -->
<div class="w-full" x-data="{ filterStatus: 'all' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900">Daftar Laporan Penanganan Jalan</h2>
        </div>
        <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl overflow-x-auto max-w-full text-xs shrink-0">
            <button type="button" @click="filterStatus = 'all'"
                :class="filterStatus === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                class="px-3.5 py-1.5 font-bold rounded-lg transition-all whitespace-nowrap">
                Semua ({{ $laporan_diproses->count() }})
            </button>
            <button type="button" @click="filterStatus = 'siap_dikerjakan'"
                :class="filterStatus === 'siap_dikerjakan' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                class="px-3.5 py-1.5 font-bold rounded-lg transition-all whitespace-nowrap flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                Siap Dikerjakan ({{ $siapDikerjakanCount }})
            </button>
            <button type="button" @click="filterStatus = 'diproses'"
                :class="filterStatus === 'diproses' ? 'bg-white text-amber-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                class="px-3.5 py-1.5 font-bold rounded-lg transition-all whitespace-nowrap flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                Di Proses ({{ $diprosesCount }})
            </button>
            <button type="button" @click="filterStatus = 'selesai'"
                :class="filterStatus === 'selesai' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                class="px-3.5 py-1.5 font-bold rounded-lg transition-all whitespace-nowrap flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Selesai ({{ $selesaiCount }})
            </button>
        </div>
    </div>

    <div class="flex flex-col gap-4">

        @forelse($laporan_diproses as $laporan)
        <div x-show="filterStatus === 'all' || filterStatus === '{{ $laporan->tahap_progres }}'"
             class="bg-white rounded-2xl p-4 border {{ $laporan->tingkat_bahaya === 'Bahaya Tinggi' ? 'border-red-200 ring-1 ring-red-500/20' : 'border-slate-200' }} shadow-xs flex flex-col sm:flex-row gap-5 relative overflow-hidden">
            @if($laporan->tingkat_bahaya === 'Bahaya Tinggi')
            <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
            @endif
            <div class="w-full sm:w-48 h-32 rounded-xl overflow-hidden relative shrink-0">
                @if($laporan->url_foto)
                <img src="{{ asset('storage/' . $laporan->url_foto) }}" alt="Jalan rusak" class="w-full h-full object-cover">
                @else
                <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-400 flex-col gap-2">
                    <i class="fa-solid fa-image text-3xl"></i>
                </div>
                @endif
                <div class="absolute top-2 left-2 {{ $laporan->tingkat_bahaya === 'Bahaya Tinggi' ? 'bg-red-600' : ($laporan->tingkat_bahaya === 'Sedang' ? 'bg-amber-500' : 'bg-slate-600') }} text-white text-[10px] font-extrabold px-2 py-0.5 rounded uppercase">{{ $laporan->tingkat_bahaya }}</div>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-[10px] font-mono font-bold text-slate-500">
                        LP-{{ $laporan->created_at->format('Y') }}-{{ str_pad($laporan->id_laporan, 4, '0', STR_PAD_LEFT) }} — SPK: #SPK-PUPR-{{ str_pad($laporan->id_laporan, 4, '0', STR_PAD_LEFT) }}
                    </div>
                    @if($laporan->tahap_progres === 'selesai')
                        <span class="text-[10px] font-extrabold bg-emerald-50 text-emerald-700 px-2.5 py-0.5 rounded-full border border-emerald-200 flex items-center gap-1">
                            <i class="fa-solid fa-check-double text-[9px]"></i> Selesai ({{ $laporan->progres_persen }}%)
                        </span>
                    @elseif($laporan->tahap_progres === 'diproses')
                        <span class="text-[10px] font-extrabold bg-amber-50 text-amber-700 px-2.5 py-0.5 rounded-full border border-amber-200 flex items-center gap-1">
                            <i class="fa-solid fa-spinner fa-spin-pulse text-[9px]"></i> Di Proses ({{ $laporan->progres_persen }}%)
                        </span>
                    @else
                        <span class="text-[10px] font-bold bg-blue-50 text-blue-700 px-2.5 py-0.5 rounded-full border border-blue-200 flex items-center gap-1">
                            <i class="fa-solid fa-file-circle-check text-[9px]"></i> Siap Dikerjakan (0%)
                        </span>
                    @endif
                </div>
                <h3 class="text-sm font-extrabold text-slate-900 mb-1">Jl. {{ $laporan->jalan->nama_jalan ?? 'Tidak diketahui' }} ({{ $laporan->jalan->desa->nama_desa ?? '-' }})</h3>
                <p class="text-[11px] text-slate-600 leading-relaxed mb-3">Kerusakan: <strong>{{ $laporan->kategori->nama_kategori ?? '-' }}</strong>. {{ $laporan->deskripsi }}</p>

                <div class="flex items-center gap-4 mb-3">
                    <div class="flex items-center gap-1.5 text-[10px] font-bold {{ $laporan->tingkat_bahaya === 'Bahaya Tinggi' ? 'text-red-700 bg-red-50 border-red-200' : 'text-slate-700 bg-slate-100 border-slate-200' }} border px-2.5 py-1.5 rounded-lg">
                        <i class="fa-solid fa-user"></i> Dilaporkan oleh: {{ $laporan->pengguna->nama_lengkap ?? 'Warga' }}
                    </div>
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-600 font-medium">
                        <i class="fa-solid fa-location-dot text-slate-400"></i> Kec. {{ $laporan->jalan->desa->kecamatan->nama_kecamatan ?? '-' }}
                    </div>
                    @if($laporan->timLapangan)
                    <div class="flex items-center gap-1.5 text-[10px] font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-1.5 rounded-lg">
                        <i class="fa-solid fa-users text-blue-600"></i> Tim: {{ $laporan->timLapangan->nama_tim }}
                    </div>
                    @endif
                </div>

                <div class="flex items-center justify-between mt-auto">
                    <div class="flex items-center gap-1.5 text-[9px] text-slate-500 font-bold">
                        <i class="fa-solid fa-calendar-day"></i> Dilaporkan pada: {{ $laporan->created_at->format('d M Y, H:i') }} WIB
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="document.getElementById('modal-detail-{{ $laporan->id_laporan }}').classList.remove('hidden')" class="px-3 py-1.5 text-[10px] font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">Detail Laporan</button>

                        @if($laporan->timLapangan)
                            @if($laporan->tahap_progres !== 'selesai' && $laporan->progres_persen < 100)
                            <a href="{{ route('pupr.progres.show', $laporan->id_laporan) }}" class="px-3 py-1.5 text-[10px] font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-xs transition-colors flex items-center gap-1">
                                <i class="fa-solid fa-pen-to-square text-[9px]"></i> Update Progres
                            </a>
                            @endif
                        @else
                            @if(($timTersediaCount ?? 0) > 0)
                            <button type="button"
                                onclick="document.getElementById('modal-tugaskan-laporan-{{ $laporan->id_laporan }}').classList.remove('hidden')"
                                class="px-3.5 py-1.5 text-[10px] font-bold text-white bg-[#a04a14] hover:bg-[#883d0f] rounded-lg shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-user-plus text-[10px]"></i> Tugaskan Tim
                            </button>
                            @else
                            <div class="flex items-center gap-1.5">
                                <span class="text-[9px] font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2 py-1 rounded-md flex items-center gap-1">
                                    <i class="fa-solid fa-triangle-exclamation text-amber-600"></i> Tidak ada tim kosong
                                </span>
                                <button type="button" disabled title="Tidak ada tim yang kosong (semua tim sedang bertugas)"
                                    class="px-3.5 py-1.5 text-[10px] font-bold text-white bg-slate-400 cursor-not-allowed rounded-lg shadow-xs flex items-center gap-1.5 opacity-60">
                                    <i class="fa-solid fa-user-xmark text-[10px]"></i> Tugaskan Tim
                                </button>
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DETAIL -->
        <div id="modal-detail-{{ $laporan->id_laporan }}" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-lg">Detail Laporan LP-{{ $laporan->created_at->format('Y') }}-{{ str_pad($laporan->id_laporan, 4, '0', STR_PAD_LEFT) }}</h3>
                        <p class="text-xs text-slate-500 font-medium mt-1">Dilaporkan pada {{ $laporan->created_at->format('d M Y, H:i') }} WIB</p>
                    </div>
                    <button onclick="document.getElementById('modal-detail-{{ $laporan->id_laporan }}').classList.add('hidden')" class="w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <!-- Body -->
                <div class="p-6 overflow-y-auto flex-1 bg-slate-50/30">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Foto -->
                        <div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Foto Lapangan</div>
                            <div class="w-full h-64 md:h-80 rounded-2xl overflow-hidden bg-slate-100 border border-slate-200">
                                @if($laporan->url_foto)
                                <img src="{{ asset('storage/' . $laporan->url_foto) }}" alt="Foto Laporan" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-slate-400">
                                    <i class="fa-solid fa-image text-4xl mb-2"></i>
                                    <span class="text-xs font-medium">Tidak ada foto</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        <!-- Informasi -->
                        <div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Informasi Laporan</div>

                            <div class="space-y-4">
                                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                                    <div class="text-[10px] text-slate-500 font-bold uppercase mb-1">Pelapor</div>
                                    <div class="font-bold text-slate-900">{{ $laporan->pengguna->nama_lengkap ?? 'Warga' }}</div>
                                    <div class="text-xs text-slate-600"><i class="fa-solid fa-phone text-slate-400 mr-1"></i> {{ $laporan->pengguna->telepon ?? '-' }}</div>
                                </div>

                                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                                    <div class="text-[10px] text-slate-500 font-bold uppercase mb-1">Lokasi Kerusakan</div>
                                    <div class="font-bold text-slate-900">Jl. {{ $laporan->jalan->nama_jalan ?? 'Tidak diketahui' }}</div>
                                    <div class="text-xs text-slate-600">Desa {{ $laporan->jalan->desa->nama_desa ?? '-' }}, Kec. {{ $laporan->jalan->desa->kecamatan->nama_kecamatan ?? '-' }}</div>
                                </div>

                                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                                    <div class="text-[10px] text-slate-500 font-bold uppercase mb-1">Tingkat Bahaya & Kategori</div>
                                    <div class="flex items-center gap-2">
                                        <div class="inline-flex px-2 py-1 {{ $laporan->tingkat_bahaya === 'Bahaya Tinggi' ? 'bg-red-100 text-red-700 border-red-200' : ($laporan->tingkat_bahaya === 'Sedang' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-700 border-slate-200') }} border rounded-lg text-xs font-bold uppercase">
                                            {{ $laporan->tingkat_bahaya }}
                                        </div>
                                        <div class="text-sm font-bold text-slate-700">{{ $laporan->kategori->nama_kategori ?? '-' }}</div>
                                    </div>
                                </div>

                                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                                    <div class="text-[10px] text-slate-500 font-bold uppercase mb-1">Deskripsi Kerusakan</div>
                                    <p class="text-sm text-slate-700 leading-relaxed">{{ $laporan->deskripsi }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Modal Detail -->
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/70 flex justify-between items-center">
                    <button type="button" onclick="document.getElementById('modal-detail-{{ $laporan->id_laporan }}').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-xl transition">
                        Tutup
                    </button>
                    @if($laporan->timLapangan && $laporan->tahap_progres !== 'selesai' && $laporan->progres_persen < 100)
                    <a href="{{ route('pupr.progres.show', $laporan->id_laporan) }}" class="px-4 py-2 bg-[#0f1b33] hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-pen-to-square text-[10px]"></i> Update Progres Lapangan
                    </a>
                    @endif
                </div>
            </div>
        </div>

        @if(!$laporan->timLapangan)
        <!-- MODAL PENUGASAN TIM DARI DASHBOARD -->
        <div id="modal-tugaskan-laporan-{{ $laporan->id_laporan }}" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl w-full max-w-lg overflow-hidden flex flex-col shadow-2xl">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50/70">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">
                            Penugasan Tim Lapangan
                        </h3>
                        <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                            LP-{{ $laporan->created_at->format('Y') }}-{{ str_pad($laporan->id_laporan, 4, '0', STR_PAD_LEFT) }} — Jl. {{ $laporan->jalan->nama_jalan ?? '-' }}
                        </p>
                    </div>
                    <button type="button" onclick="document.getElementById('modal-tugaskan-laporan-{{ $laporan->id_laporan }}').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <!-- Form -->
                <form action="{{ route('pupr.laporan.tugaskan-tim', $laporan->id_laporan) }}" method="POST">
                    @csrf
                    <div class="p-6 space-y-4">
                        <!-- Info Laporan Ringkas -->
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 text-xs text-slate-700 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900">Lokasi Penanganan:</span>
                                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded {{ $laporan->tingkat_bahaya === 'Bahaya Tinggi' ? 'bg-red-100 text-red-700' : ($laporan->tingkat_bahaya === 'Sedang' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $laporan->tingkat_bahaya }}
                                </span>
                            </div>
                            <div class="text-xs">
                                <strong>Jl. {{ $laporan->jalan->nama_jalan ?? '-' }}</strong>
                                <div class="text-[11px] text-slate-500">{{ $laporan->jalan->desa->nama_desa ?? '-' }}, Kec. {{ $laporan->jalan->desa->kecamatan->nama_kecamatan ?? '-' }}</div>
                            </div>
                            <div class="pt-2 border-t border-slate-200/60 text-[11px] text-slate-600">
                                Kerusakan: <strong>{{ $laporan->kategori->nama_kategori ?? '-' }}</strong>
                            </div>
                        </div>

                        <!-- Pilih Tim Kosong -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">Pilih Tim Lapangan yang Kosong / Siaga</label>
                            @if(isset($timTersedia) && $timTersedia->count() > 0)
                                <select name="id_tim" required class="w-full text-xs font-medium rounded-xl border border-slate-200 px-3.5 py-2.5 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400">
                                    <option value="">-- Pilih Tim yang Tersedia --</option>
                                    @foreach($timTersedia as $tim)
                                        <option value="{{ $tim->id_tim }}">
                                            {{ $tim->nama_tim }} ({{ $tim->jumlah_personel }} Orang Personel) — Siaga
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-[11px] text-slate-400 mt-1.5">
                                    Menampilkan tim yang saat ini berstatus Siaga / Tersedia untuk ditugaskan.
                                </p>
                            @else
                                <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs flex items-start gap-2.5">
                                    <i class="fa-solid fa-triangle-exclamation text-amber-600 text-sm mt-0.5 shrink-0"></i>
                                    <div>
                                        <div class="font-bold">Tidak ada tim yang kosong</div>
                                        <div class="text-[11px] text-amber-700 mt-0.5">Semua tim lapangan saat ini sedang bertugas di lapangan.</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer Modal Penugasan -->
                    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/70 flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modal-tugaskan-laporan-{{ $laporan->id_laporan }}').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" @if(!isset($timTersedia) || $timTersedia->count() == 0) disabled @endif class="px-5 py-2 bg-[#a04a14] hover:bg-[#883d0f] disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                            Konfirmasi Penugasan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
        @empty
        <div class="bg-white rounded-2xl p-10 border border-slate-200 shadow-xs flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-4">
                <i class="fa-solid fa-check-double text-emerald-500 text-2xl"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-700">Tidak ada SPK baru</h3>
            <p class="text-xs text-slate-400 mt-1">Semua laporan dari kecamatan telah ditangani atau belum ada disposisi baru masuk.</p>
        </div>
        @endforelse

    </div>
</div>

