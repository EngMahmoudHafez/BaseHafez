@props([
    'colspan' => 1,
    'message' => null,
])

{{-- Empty-state row for the @forelse @empty branch of x-dashboard.table.
     Pass a default slot for a richer message (icon + text) or rely on `message`. --}}
<tr>
    <td class="dashboard-empty-state" colspan="{{ $colspan }}">
        {{ $slot->isNotEmpty() ? $slot : ($message ?? __('dashboard.no_data')) }}
    </td>
</tr>
