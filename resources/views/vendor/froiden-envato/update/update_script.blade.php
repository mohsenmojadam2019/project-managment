<script type="text/javascript">
    let updateAreaDiv = $('#update-area');
 let refreshPercent = 0;
 let checkInstall = true;
 
 $('#update-app').click(function () {
     if ($('#update-frame').length) {
         return false;
     }
 
     Swal.fire({
         title: "آیا مطمئن هستید؟",
         html: `<x-alert type="danger" icon="info-circle">
                     لطفاً روی دکمه <strong>بله! بروزرسانی کن</strong> کلیک نکنید اگر اپلیکیشن شما تغییراتی اختصاصی داشته است. ممکن است تغییرات شما از دست برود.
                     <br><br>
                     به عنوان یک اقدام پیشگیرانه، لطفاً قبل از بروزرسانی از فایل‌ها و دیتابیس خود بکاپ تهیه کنید.
                     <br><br>
                     <strong class="mt-2"><i>توجه داشته باشید که نویسنده مسئولیتی در قبال از دست رفتن داده‌ها یا مشکلاتی که ممکن است در طول فرآیند بروزرسانی رخ دهد، ندارد.</i></strong>
                 </x-alert>
                 <span class="">برای تأیید اینکه پیام بالا را خوانده‌اید، لطفاً عبارت <strong><i>confirm</i></strong> را در فیلد زیر وارد کنید.</span>`,
         icon: 'info',
         focusConfirm: true,
         customClass: {
             confirmButton: 'btn btn-primary mr-3',
             cancelButton: 'btn btn-secondary'
         },
         showClass: {
             popup: 'swal2-noanimation',
             backdrop: 'swal2-noanimation'
         },
         buttonsStyling: false,
         input: 'text',
         inputAttributes: {
             autocapitalize: 'off'
         },
         showCloseButton: true,
         showCancelButton: true,
         confirmButtonText: "بله، بروزرسانی کن!",
         cancelButtonText: "نه، لغو کن!",
         padding: '3em',
         showLoaderOnConfirm: true,
         preConfirm: (isConfirm) => {
             if (!isConfirm) {
                 return false;
             }
 
             if (isConfirm.toLowerCase() !== "confirm") {
                 Swal.fire({
                     title: "متن تطابق ندارد",
                     html: "عبارت <b>confirm</b> را اشتباه وارد کرده‌اید",
                     icon: 'error',
                 });
                 return false;
             }
             if (isConfirm.toLowerCase() === "confirm") {
                 return true;
             }
         },
         allowOutsideClick: () => !Swal.isLoading()
     }).then((result) => {
         if (result.isConfirmed) {
             updateAreaDiv.removeClass('d-none');
             downloadPercent();
             $.easyAjax({
                 type: 'GET',
                 blockUI: true,
                 url: '{!! route("admin.updateVersion.updater") !!}',
                 
                 success: function (response) {
                     if (response.status === 'success') {
                         installScript();
                         updateAreaDiv.addClass('d-none');
                     } else if (response.status === 'fail') {
                         updateAreaDiv.addClass('d-none');
                         Swal.fire({
                             title: "خطا",
                             text: "عملیات بروزرسانی با شکست مواجه شد!",
                             icon: "error"
                         });
                     }
                 }
             });
         }
     });
 });
 
     function downloadScript() {
         $.easyAjax({
             type: 'GET',
             url: '{!! route("admin.updateVersion.download") !!}',
             success: function (response) {
                 clearInterval(refreshPercent);
 
                 if(response.status === 'fail'){
                     updateAreaDiv.html(`<i><span class='text-red'><strong>Update Failed</strong> :</span> ${response.message}</i>`)
                     return false;
                 }
 
                 $('#percent-complete').css('width', '100%');
                 $('#percent-complete').html('100%');
                 $('#download-progress').append("<i><span class='text-success'>Download complete.</span> Now Installing...Please wait (This may take few minutes.)</i>");
 
                 window.setInterval(function () {
                     /// call your function here
                     if (checkInstall == true) {
                         checkIfFileExtracted();
                     }
                 }, 1500);
 
                 installScript();
 
             }
         });
     }
 
     function getDownloadPercent() {
         $.easyAjax({
             type: 'GET',
             url: '{!! route("admin.updateVersion.downloadPercent") !!}',
             success: function (response) {
                 response = response.toFixed(1);
                 $('#percent-complete').css('width', response + '%');
                 $('#percent-complete').html(response + '%');
             }
         });
     }
 
     function checkIfFileExtracted() {
         $.easyAjax({
             type: 'GET',
             url: '{!! route("admin.updateVersion.checkIfFileExtracted") !!}',
             success: function (response) {
                 checkInstall = false;
                 if (response.status == 'success') {
                     window.location.reload();
                 }
             }
         });
     }
 
     function downloadPercent() {
         updateAreaDiv.append('<hr><div id="download-progress">' +
             'در حال دانلود ...<br><div class="progress progress-lg">' +
             '<div class="progress-bar progress-bar-success active progress-bar-striped" id="percent-complete" role="progressbar""></div>' +
             '</div>' +
             '</div>'
         );
         //getting data
         let progress = 0;
         refreshPercent = window.setInterval(function () {
         progress += 1;
         console.log(progress);
        
         $('#percent-complete').css('width', progress + '%');
         $('#percent-complete').html(progress + '%');
 
         // وقتی درصد به 100 رسید
         if (progress >= 100) {
 
             $('#download-progress').append("<i><span class='text-success'>دانلود کامل شد</span> نصب در حال انجام است .ممکنه  کمی طول بکشه پس صبر کنید </i>");
             
             clearInterval(refreshPercent); 
             
         }
         }, 1000);
     }
 
     function installScript() {
     Swal.fire({
         title: "بروزرسانی کامل شد برای تکمیل عملایت تایید کنید.",
         icon: "success",
         confirmButtonText: "تأیید"
     }).then(() => {
         $.easyAjax({
             type: 'GET',
             url: '{!! route("admin.updateVersion.install2") !!}', // مسیر متد install
             success: function (response) {
                 if (response.status == 'success') {
                     window.location.reload(); // ریفرش صفحه
                 } else {
                     Swal.fire({
                         title: "خطا",
                         text: response.message,
                         icon: "error",
                         confirmButtonText: "بستن"
                     });
                 }
             },
             error: function (xhr) {
                 Swal.fire({
                     title: "خطا",
                     text: xhr.responseJSON.message || "خطای غیرمنتظره",
                     icon: "error",
                     confirmButtonText: "بستن"
                 });
             }
         });
     });
 }
 
     function getPurchaseData() {
         const token = "{{ csrf_token() }}";
         $.easyAjax({
             type: 'POST',
             url: "{{ route('purchase-verified') }}",
             data: {'_token': token},
             container: "#support-div",
             messagePosition: 'inline',
             success: function (response) {
                 window.location.reload();
             }
         });
         return false;
     }
 
     function showHidePurchaseCode() {
         $(this).toggleClass('fa-eye-slash fa-eye');
         $(this).siblings('span').toggleClass('blur-code ');
     }
     $("body").tooltip({
         selector: '[data-toggle="tooltip"]'
     })
 
 </script>
 