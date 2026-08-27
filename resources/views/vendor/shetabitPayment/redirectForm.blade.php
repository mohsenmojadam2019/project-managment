<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>در حال انتقال به در گاه پرداخت امن</title>
        <style>
            .text-center {
                text-align: center;
            }

            .mt-2 {
                margin-top: 2em;
            }

            .spinner {
                margin: 100px auto 0;
                width: 70px;
                text-align: center;
            }

            .spinner > div {
                width: 18px;
                height: 18px;
                background-color: #333;
                border-radius: 100%;
                display: inline-block;
                -webkit-animation: sk-bouncedelay 1.4s infinite ease-in-out both;
                animation: sk-bouncedelay 1.4s infinite ease-in-out both;
            }

            .spinner .bounce1 {
                -webkit-animation-delay: -0.32s;
                animation-delay: -0.32s;
            }

            .spinner .bounce2 {
                -webkit-animation-delay: -0.16s;
                animation-delay: -0.16s;
            }

            @-webkit-keyframes sk-bouncedelay {
                0%, 80%, 100% { -webkit-transform: scale(0) }
                40% { -webkit-transform: scale(1.0) }
            }

            @keyframes sk-bouncedelay {
                0%, 80%, 100% {
                    -webkit-transform: scale(0);
                    transform: scale(0);
                } 40% {
                      -webkit-transform: scale(1.0);
                      transform: scale(1.0);
                  }
            }
        </style>
    </head>
    <body onload="submitForm();">
        <div class="spinner">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
        <form class="text-center mt-2" method="{{ $method }}" action="{{ $action }}">
            <p>در حال انتقال به در گاه پرداخت امن</p>
            <p>
                اگر پس از مدت زمان زیر به درگاه هدایت نشدید بروی دکمه زیر کلیک کنید
            <span id="countdown">4</span>
                ثانیه...
            </p>

            @foreach($inputs as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach

            <button type="submit">هدایت به درگاه پرداخت</button>
        </form>
        <script>
            // Total seconds to wait
            var seconds = 4;

            function submitForm() {
                document.querySelector('button[type="submit"]').click();
            }

            function countdown() {
                seconds = seconds - 1;
                if (seconds <= 0) {
                    // submit the form
                    submitForm();
                } else {
                    // Update remaining seconds
                    document.getElementById("countdown").innerHTML = seconds;
                    // Count down using javascript
                    window.setTimeout("countdown()", 1000);
                }
            }

            // Run countdown function
            countdown();
        </script>
    </body>
</html>
