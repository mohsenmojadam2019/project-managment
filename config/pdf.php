<?php

return [
    'mode' => 'utf-8',
    'format' => 'A4',
    'author' => '',
    'subject' => '',
    'keywords' => '',
    'creator' => 'Laravel Pdf',
    'display_mode' => 'fullpage',
    'pdfWrapper' => 'misterspelik\LaravelPdf\Wrapper\PdfWrapper',
    'defaultCssFile' => base_path('resources/itnoo/css/pdf.css'),
    'tempDir' => base_path('storage/temp'),
    'font_path' => base_path('resources/fonts'),
	'font_data' => [
		'vazir' => [
			'R'  => 'Vazirmatn-Regular.ttf',    // regular font
			'B'  => 'Vazirmatn-Bold.ttf',                   // bold font
			'I'  => 'Vazirmatn-Italic.ttf',                 // italic font (اگر موجود باشد)
			'BI' => 'Vazirmatn-BoldItalic.ttf',             // bold-italic font (اگر موجود باشد)
			'useOTL' => 0xFF,
			'useKashida' => 75,
		]
	]

];