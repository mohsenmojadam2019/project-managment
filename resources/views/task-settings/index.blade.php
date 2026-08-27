@extends('layouts.app')
 
@push('styles')
    <style>
        .form_custom_label {
            justify-content: left;
        }

    </style>
@endpush

@section('content')
    <!-- SETTINGS START -->
    <div class="w-100 d-flex">
        <x-setting-sidebar :activeMenu="$activeSettingMenu" />
        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">
                            
                            <a class="nav-item nav-link f-15 active general" href="{{ route('task-settings.index') }}?tab=general"
                                role="tab" aria-controls="nav-general"
                                aria-selected="true">@lang('app.menu.taskSettings')
                            </a>
                            <a class="nav-item nav-link f-15 category"
                                href="{{ route('task-settings.index') }}?tab=category" role="tab"
                                aria-controls="nav-category" aria-selected="true">@lang('modules.tasks.taskCategory')
                            </a>
                            
                        </div>
                    </nav>
                </div>
            </x-slot>

            <x-slot name="buttons">
                <div class="row">
                    <div class="col-md-12 mb-2">
                                               
                        <x-forms.button-primary icon="plus" id="addCategory" class="category-btn mb-2 d-none actionBtn">
                            @lang('app.addNew')
                        </x-forms.button-primary>
                        
                    </div>
                </div>
            </x-slot>

            {{-- include tabs here --}}
            @include($view)

        </x-setting-card>
    </div>
    <!-- SETTINGS END -->
    
@endsection

@push('scripts')
    <script>
        /* LEAD CATEGORY */
        $('.nav-item').removeClass('active');
        const activeTab = "{{ $activeTab }}";
        $('.' + activeTab).addClass('active');

        function showBtn(activeTab) {
            $('.actionBtn').addClass('d-none');
            $('.' + activeTab + '-btn').removeClass('d-none');
        }

        /* open add category modal */
        $('body').on('click', '#addCategory', function() {
            var url = "{{ route('taskCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

      
        $("body").on("click", ".nav a", function(event) {
            event.preventDefault();
            console.log(1);
            
            $('.nav-item').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;
            console.log(requestUrl);
            
            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: "#nav-tabContent",
                historyPush: true,
                success: function(response) {
                    if (response.status == "success") {
                        showBtn(response.activeTab);
                        $('#nav-tabContent').html(response.html);
                        init('#nav-tabContent');
                    }
                }
            });
        });

        /* delete source */
        $('body').on('click', '.delete-category', function() {
            var id = $(this).data('category-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {

                    var url = "{{ route('taskCategory.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        blockUI: true,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                $('.row'+id).fadeOut();
                            }
                        }
                    });
                }
            });
        });
        /* LEAD CATEGORY */
        
        $('[contenteditable=true]').focus(function() {
        $(this).data("initialText", $(this).html());
        let rowId = $(this).data('row-id');
    }).blur(function() {
        if ($(this).data("initialText") !== $(this).html()) {
            let id = $(this).data('row-id');
            let value = $(this).html();

            if (id == null || id == '') {
                return false;
            }
            
            var url = "{{ route('taskCategory.update', ':id') }}";
            url = url.replace(':id', id);

            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                container: '#row-' + id,
                type: "POST",
                data: {
                    'category_name': value,
                    '_token': token,
                    '_method': 'PUT'
                },
                blockUI: true,
                success: function(response) {
                    if (response.status == 'success') {
                        $('#task_category_id').html(response.data);
                        $('#task_category_id').selectpicker('refresh');
                    }
                }
            })
        }
    });

    $('#save-form').click(function() {
            var data = ($('#editSettings').serialize()).replace("_method=PUT", "_method=POST");

            $.easyAjax({
                url: "{{ route('task-settings.store') }}",
                container: '#editSettings',
                blockUI: true,
                type: "POST",
                data: data
            })
        });

    </script>
@endpush
