@props(['title' => 'Dashboard Monitoring', 'subtitle' => 'RT 01 · 2 node ESP32 diagonal · konsensus jika selisih waktu < 1,5 detik'])

<header class="h-20 bg-[#F3F5F1] px-8 flex items-center justify-between border-b border-brand-border shrink-0">
    <div>
        <h1 class="text-2xl font-bold text-brand-text leading-tight">{{ $title }}</h1>
        <p class="text-xs text-brand-muted mt-0.5">{{ $subtitle }}</p>
    </div>

    <div class="flex items-center gap-4">
        <!-- Live Clock Indicator -->
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-brand-border text-xs font-mono font-medium text-brand-text">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>LIVE</span>
            <span class="text-brand-muted" id="realtimeClock">10:47:32</span>
        </div>

        <!-- Tombol Uji Sirine -->
        <button onclick="alert('Uji Sirine Dikirim ke Relay RT 01')" class="px-4 py-2 rounded-xl bg-brand-text hover:bg-black text-white text-xs font-semibold tracking-wide transition flex items-center gap-2 shadow-sm">
            <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>
            Uji Sirine
        </button>
    </div>
</header>

<script>
    function updateClock() {
        const now = new Date();
        const str = now.toTimeString().split(' ')[0];
        const el = document.getElementById('realtimeClock');
        if(el) el.innerText = str;
    }
    setInterval(updateClock, 1000);
</script>