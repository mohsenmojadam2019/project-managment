<div class="alert alert-primary">
    <h6>لطفا این cron job را در تنظیمات سرور خود ست کنید (اگر قبلا ست شده نادیده بگیرید)</h6>
    <code>* * * * * (Every Minute)</code>
    <br>
    <br>
    @php
        try {
            $phpPath = PHP_BINDIR.'/php';
        } catch (\Throwable $th) {
            $phpPath = 'php';
        }
           echo '<code  id="cron-command" class="f-12"> ' . $phpPath . ' ' . base_path() . '/artisan schedule:run >> /dev/null 2>&1</code>';
    @endphp
    <button type="button" data-clipboard-target="#cron-command"
            data-toggle="tooltip"
            data-original-title="@lang('app.copyAboveLink')"
            class="btn-copy-cron btn btn-sm btn-secondary p-1 f-10">
        <i class="fa fa-copy "></i>
    </button>

    <div class="mt-3"><strong>توجه:</strong>

        <ins>{{$phpPath}}</ins>
        در دستور بالا، مسیر PHP بر روی سرور شماست. برای اطمینان از عملکرد صحیح، لطفاً مسیر صحیح PHP برای سرور خود را وارد کنید و مسیر اسکریپت خود را نیز ارائه دهید. اگر در مورد راه‌اندازی یک کار زمان‌بندی‌شده (cron job) مطمئن نیستید، ممکن است بخواهید با مدیر سرور یا ارائه‌دهنده هاستینگ خود مشورت کنید.
    </div>
</div>

@push('scripts')
    <script>
        var clipboard = new ClipboardJS('.btn-copy-cron');

        clipboard.on('success', function (e) {
            Swal.fire({
                icon: 'success',
                text: "{{ __('app.copied') }}",
                toast: true,
                position: 'top-end',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: {
                    confirmButton: 'btn btn-primary',
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
            })
        });
    </script>
@endpush
