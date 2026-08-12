@props([
    'paginator' => null,
])

{{-- List table wrapped in a dashboard card. The `head` slot supplies the
     <th> cells, the default slot the <tbody> rows, and `:paginator` renders a
     card footer with links when the result set has more than one page. --}}
<div {{ $attributes->class(['card dashboard-card']) }}>
    <div class="table-responsive">
        <table class="mb-0 table align-middle">
            <thead>
                <tr>
                    {{ $head ?? '' }}
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if ($paginator?->hasPages())
        <div class="card-footer">{{ $paginator->links() }}</div>
    @endif
</div>
