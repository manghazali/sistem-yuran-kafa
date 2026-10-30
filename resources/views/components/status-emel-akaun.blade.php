@props(['status'])

{{-- Status emel akaun log masuk (lihat App\Services\StatusEmelAkaun) --}}
@switch($status['kod'])
    @case('dihantar')
        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800"><i class="fas fa-check mr-1"></i>Dihantar</span>
        <span class="block mt-1 text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($status['masa'])->format('d/m/Y g:i A') }}</span>
        @break
    @case('giliran')
        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800" title="Akaun dicipta; emel menunggu giliran hantar (had harian)">Dalam giliran</span>
        @break
    @case('belum')
        <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">Belum dihantar</span>
        @break
    @default
        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">Tiada emel</span>
@endswitch
@if ($status['emel'])
    <span class="block mt-1 text-xs text-gray-400">{{ $status['emel'] }}</span>
@endif
