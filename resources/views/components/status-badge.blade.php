@props(['status' => 'AMAN', 'size' => 'md'])

@php
    $statusUpper = strtoupper($status);
    $configs = [
        'AMAN' => ['bg' => 'bg-[#E3F4EB]', 'text' => 'text-[#17703A]', 'border' => 'border-[#17703A]/20', 'icon' => 'check-circle'],
        'SAFE' => ['bg' => 'bg-[#E3F4EB]', 'text' => 'text-[#17703A]', 'border' => 'border-[#17703A]/20', 'icon' => 'check-circle'],
        'WASPADA' => ['bg' => 'bg-[#FFF1D6]', 'text' => 'text-[#B45F06]', 'border' => 'border-[#B45F06]/20', 'icon' => 'alert-triangle'],
        'WARNING' => ['bg' => 'bg-[#FFF1D6]', 'text' => 'text-[#B45F06]', 'border' => 'border-[#B45F06]/20', 'icon' => 'alert-triangle'],
        'BAHAYA' => ['bg' => 'bg-[#FDE7E4]', 'text' => 'text-[#B3261E]', 'border' => 'border-[#B3261E]/20', 'icon' => 'alert-octagon'],
        'DANGER' => ['bg' => 'bg-[#FDE7E4]', 'text' => 'text-[#B3261E]', 'border' => 'border-[#B3261E]/20', 'icon' => 'alert-octagon'],
        'NORMAL' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'check'],
        'UJI COBA' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => 'info'],
    ];

    $cfg = $configs[$statusUpper] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-200', 'icon' => 'info'];
@endphp

<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $cfg['bg'] }} {{ $cfg['text'] }} {{ $cfg['border'] }}">
    @if(in_array($statusUpper, ['AMAN', 'SAFE', 'NORMAL']))
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    @elseif(in_array($statusUpper, ['WASPADA', 'WARNING']))
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    @else
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    @endif
    {{ $statusUpper }}
</span>