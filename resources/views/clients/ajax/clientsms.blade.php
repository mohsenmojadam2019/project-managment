<!-- ROW START -->
<div class="row mt-4">
        <div class="mb-4 col-xl-6">
                <x-cards.data :title="__('modules.module.sms')">
                    {!! $smsForm ?? '' !!}
                </x-cards.data>
        </div>
        <div class="col-xl-6">
                <div class="card bg-white border-0 b-shadow-4">
                    <x-cards.data :title="__('modules.client.profileInfo')">
                        <x-cards.data-row :label="__('modules.employees.fullName')" :value="$client->name_salutation" />
            
                        <x-cards.data-row :label="__('app.email')" :value="$client->email ?? '--'" />
            
                        <x-cards.data-row :label="__('modules.client.companyName')"
                            :value="$client->clientDetails->company_name ?? '--'" />
            
                        <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                            <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                @lang('modules.profile.companyLogo')</p>
                            <p class="mb-0 text-dark-grey f-14 w-70">
                                @if ($client->clientDetails->company_logo)
                                    <img data-toggle="tooltip" style="height:50px;"
                                src="{{ $client->clientDetails->image_url }}">
                                @else
                                --
                                @endif
                            </p>
                        </div>
            
                        <x-cards.data-row :label="__('app.mobile')"
                            :value="$client->mobile" />
            
                        <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                            <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                @lang('modules.employees.gender')</p>
                            <p class="mb-0 text-dark-grey f-14 w-70">
                                <x-gender :gender='$client->gender' />
                            </p>
                        </div>
            
                        <x-cards.data-row :label="__('modules.client.officePhoneNumber')"
                            :value="$client->clientDetails->office ?? '--'" />
            
                        <x-cards.data-row :label="__('modules.client.website')" :value="$client->clientDetails->website ?? '--'" />
            
                        <x-cards.data-row :label="__('app.gstNumber')" :value="$client->clientDetails->gst_number ?? '--'" />
            
                        <x-cards.data-row :label="__('app.address')" :value="$client->clientDetails->address ?? '--'" />
            
                        <x-cards.data-row :label="__('modules.stripeCustomerAddress.state')"
                            :value="$client->clientDetails->state ?? '--'" />
            
                        <x-cards.data-row :label="__('modules.stripeCustomerAddress.city')"
                            :value="$client->clientDetails->city ?? '--'" />
            
                        <x-cards.data-row :label="__('modules.stripeCustomerAddress.postalCode')"
                            :value="$client->clientDetails->postal_code ?? '--'" />
            
                        <x-cards.data-row :label="__('app.language')"
                            :value="$clientLanguage->label. ' '. $clientLanguage->language_name ?? '--'" />
            
                        @if(!is_null($client->clientDetails->added_by))
                            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                    @lang('app.addedBy')</p>
                                <p class="mb-0 text-dark-grey f-14 ">
                                    <x-employee :user="$client->clientDetails->addedBy" />
                                </p>
                            </div>
                        @endif
                    </x-cards.data>
                </div>
        </div>
</div>
<!-- ROW END -->
<script>
    $('body').on('click', '.verify-user', function() {
        const id = $(this).data('user-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.approvalWarning')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('app.approve')",
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
                var url = "{{ route('clients.approve', $client->id) }}";

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            window.location.reload();
                        }
                    }
                });
            }
        });
    });
</script>