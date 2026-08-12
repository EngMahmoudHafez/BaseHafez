@php
    $containerFooter =
    isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
    ? 'container-xxl'
    : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
    <div class="{{ $containerFooter }}">
        <div class="footer-container d-flex align-items-center justify-content-between flex-md-row flex-column py-4">
            <div class="text-body">
                &#169;
                <script>
                    document.write(new Date().getFullYear());
                </script>
                , made with ❤️ by {{ config('variables.creatorName') }}
            </div>
        </div>
    </div>
</footer>
<!-- / Footer -->
