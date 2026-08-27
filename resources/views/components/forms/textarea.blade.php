<div {{ $attributes->merge(['class' => 'form-group my-3']) }}>
    <x-forms.label :fieldId="$fieldId" :fieldLabel="$fieldLabel" :fieldRequired="$fieldRequired"
                   :popover="$popover"></x-forms.label>

    <textarea class="form-control f-14 pt-2 auto-expand" style="padding-left: 65px; min-height: 270px; overflow: auto; line-height: 2; transition: none;"
              rows="6"
              placeholder="{{ $fieldPlaceholder }}"
              name="{{ $fieldName }}"
              id="{{ $fieldId }}">{{ $fieldValue }}</textarea>
</div>