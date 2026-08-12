{{-- Session flash messages rendered as SweetAlert2 modals --}}
@if (session()->has('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '{{ __("dashboard.success") }}',
                    text: @js(session('success')),
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false,
                });
            }
        });
    </script>
@endif

@if (session()->has('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("dashboard.error") }}',
                    text: @js(session('error')),
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false,
                });
            }
        });
    </script>
@endif

@if (session()->has('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: '{{ __("dashboard.warning") }}',
                    text: @js(session('warning')),
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false,
                });
            }
        });
    </script>
@endif

@if (session()->has('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: '{{ __("dashboard.info") }}',
                    text: @js(session('info')),
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false,
                });
            }
        });
    </script>
@endif
