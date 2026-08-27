<html>
<head>
    <title>مراحل نصب ورک سوییت</title>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link type="text/css" rel="stylesheet" media="all" href="../itnoo/css/farsi-fonts.css" defer="defer">
</head>
<style>
    .col-md-12 {
    direction: rtl;
    }
</style>
<body>
<!-- Page Content -->
<div class="container">
    <div class="row" style="margin-top: 30px">
        <div class="text-center m-t-20 mt-20">
            <h2>خطا های زیر را رفع کنید</h2>
        </div>

        <?php if ($GLOBALS['error_type'] == 'php-version') { ?>
            <div class="alert alert-danger">
                <div class="row text-center">
                    <div class="col-md-12"><strong>ورژن php شما پایین می باشد </strong> ورژن php سرور شما پایین تر از 
                        <b>8.2.0</b>. لطفا ورژن php خود را ارتقا دهید
                        برای نصب اتوماسیون ورک سوییت باید ورژن php شما بالا تر از  <b>8.2.0</b> باشد 
                        <br>
                        <br>
                        <p class="">ورژن php سرور شما:
                            <b><?php echo phpversion(); ?></b></p>
                    </div>

                </div>


            </div>

        <?php } else { ?>
            <div class="alert alert-danger">
                <strong>.env  یافت نشد  شما فراموش کرده اید که فایل .env را بروی سرور خود آپلود کنید </strong>
            </div>
        <?php } ?>
    </div>
</div>

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</body>
</html>