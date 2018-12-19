# SetaFPDF

SetaFPDF is a clone of [FPDF](http://www.fpdf.org/) with an almost compatible interface while using [SetaPDF-Core](https://www.setasign.com/core) internally for the PDF generation.

## Motivation

The main motivation for this project was to be able to create PDF/A documents in PHP without changing all existing PDF generation scripts which rely on [FPDF](http://www.fpdf.org/).

FPDF is a wide spread and very common PHP library for PDF generation. It is small, well tested and runs on millions of websites. It also comes with several extensions and an active [forum](http://fpdf.org/phorum/).

The [SetaPDF-Core](https://www.setasign.com/core) component is a PHP component which allows PHP developers to interact with existing PDF documents on a low level. While there is no high-level API for PDF generation it comes with all low-level features which are required to create PDF/A documents:

- TrueType font sub-setting
- Access to XMP metadata
- Color profiles
- Support for embedded files / attachments

Additionally it comes with other nice features which would be available automatically: 

- Access to document outlines
- Access to page labels
- Support for annotations 
- Support for creating and handling destinations (also named destinations)
- Support for individual actions
- Support for other colors and color spaces
- Standard and public key encryption (up to AES256)
- ...and much more...

Bringing both SetaPDF and FPDF together result in SetaFPDF: An almost identical interface of FPDF backed up by SetaPDF on the lower level.

### Future of this project

This project is not a start of a new PDF generation project in PHP but it is a temporary solution to improve existing projects which rely on FPDF.

## Requirements

 - PHP >= 5.6
 - [SetaPDF-Core](https://www.setasign.com/core) (All [SetaPDF products](https://www.setasign.com/products/) include SetaPDF-Core)
   - [iconv](http://www.php.net/iconv) or [Multibyte String](http://www.php.net/mbstring)
   - [OpenSSL](http://php.net/openssl)
   - [Zlib](http://www.setasign.com/support/faq/setapdf/system-requirements/)

## Installation

Add following to your composer.json:

```json
{
    "require": {
        "setasign/setafpdf": "^1.0"
    },
    "repositories": [
        {
            "type": "composer",
            "url": "https://www.setasign.com/downloads/"
        }
    ]
}
```

and execute `composer update`. You need to define the `repository` to evaluate the dependency to the [SetaPDF-Core](https://www.setasign.com/core) component (see [here](https://getcomposer.org/doc/faqs/why-can%27t-composer-load-repositories-recursively.md) for more details).

### Evaluation version
By default this packages depends on a licensed version of the [SetaPDF-Core](https://www.setasign.com/core) component. If you want to use it with an evaluation version please use following in your composer.json:

```json
{
    "require": {
        "setasign/setafpdf": "dev-evaluation"
    },
    "repositories": [
        {
            "type": "composer",
            "url": "https://www.setasign.com/downloads/"
        }
    ]
}
```

Notice that the evaluation branch depends on the version for **PHP 7.1 and up**.

### Without Composer

Make sure, that the [SetaPDF-Core](https://www.setasign.com/core) component is [installed](https://manuals.setasign.com/setapdf-core-manual/installation/#index-2) and its [autoloader is registered](https://manuals.setasign.com/setapdf-core-manual/getting-started/#index-1) correctly.

Then simply require the `src/autoload.php` file or register following namespaces in your own PSR-4 compatible autoload implementation:

```php
$loader = new \Example\Psr4AutoloaderClass;
$loader->register();
$loader->addNamespace('setasign\SetaFpdf', 'path/to/src');
```

## Usage

You can use SetaFPDF the same way as FPDF. Their interfaces are almost identically. So just use another constructor and be aware of following differences:

- The `AddFont()` method was changed to allow only TrueType fonts instead of FPDF font definition files.
- The method `AliasNbPages()` will throw an `\SetaPDF_Exception_NotImplemented` exception throughout. It is removed in favor of a more clean solution by re-accessing the pages afterwards through the `SetPage()` method. By doing this e.g. the alignment is more consistent than with the *marker*.
- The `$isUTF8` parameter is removed from the `Output()` method. The HTTP header is always send with an UTF-8 variant by SetaPDF.
- The `$isUTF8` parameter is removed from following methods: `SetAuthor()`, `SetCreator()`, `SetKeywords()`, `SetSubject()` and `SetTitle()`. You need to pass the parameters in UTF-8 throughout.
- The method `SetCompression()` will throw an `SetaPDF_Exception_NotImplemented` exception if called with `false` as its argument (SetaPDF has no option to turn compression off).
- There's no `Error()` method anymore. Exceptions are thrown where the errors occur.

Improved and new methods:
- The `SetDrawColor()`, `SetTextColor()` and `SetFillColor()` accept 1 argument (0-255) for grayscale colors, 3 arguments (each 0-255) for RGB and 4 arguments (each 0-100) for CMYK now by default.
- A new method `SetPage()` is introduced to allow you to navigate between pages. With it you can e.g. write page numbers and the final page count into the pages footer or header. This was done before with `AliasNbPages()`.
- The new `getPageCount()` method is self-explaining. 
- The `getManager()` method allows you to get access to the underlaying object structure.

### Examples and update information
A simple example will look like:
```php
<?php
use \setasign\SetaFpdf\SetaFpdf;

require_once 'vendor/autoload.php';

$pdf = new SetaFpdf();
$pdf->AddPage();
$pdf->AddFont('DejaVuSans', '', '/path/to/DejaVuSans.ttf');
$pdf->SetFont('DejaVuSans', '', 20);
$pdf->Write(20, 'Love & Ζω!'); // Write in UTF-8
$pdf->Output();
```

Nothing new here. It's code you already know from FPDF. And that's it. In the normal cases you only need to replace the constructor with `\setasign\SetaFpdf\SetaFpdf`, update the `AddFont()`, ensure UTF-8 input and you're done!

#### Page numbering (previously done by `FPDF::AliasNbPages()`)

If your script relies on page numbering which is implemented by the use of `AliasNbPages()` in e.g. the `Header()` or `Footer()` methods you need to refactor your script. With FPDF it was done e.g. this way:

```php
class Pdf extends FPDF
{
    public function Footer()
    {
        $this->SetY(-15);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }
}

$pdf = new Pdf();
$pdf->AddPage();
// ...
$pdf->AddPage();
// ...
$pdf->AddPage();
// ...
$pdf->AddPage();
// ...
$pdf->Output();
``` 

With SetaFPDF you need to iterate over the created pages and write the footer manually:

```php
class Pdf extends SetaFpdf
{
    public function writeFooters()
    {
        $pdf->SetAutoPageBreak(false);
        
        $pageCount = $pdf->getPageCount();
        // iterate through the pages and draw the footer
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pdf->SetPage($pageNo);
            $this->SetY(-15);
            $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/' . $pageCount, 0, 0, 'R');
        }
        
        $pdf->SetAutoPageBreak(true);
    }
}

$pdf = new Pdf();
$pdf->AddPage();
// ...
$pdf->AddPage();
// ...
$pdf->AddPage();
// ...
$pdf->AddPage();
// ...
// write the footers
$pdf->writeFooters();
// ...
$pdf->Output();
```

#### Import existing pages from existing PDF documents

There is also a clone for [FPDI](https://www.setasign.com/fpdi) available. If you need the methods of FPDI just use the class `SetaFpdi` and you can use methods like `setSourceFile()`, `importPage()` and `useTemplate()`:

```php
<?php
use \setasign\SetaFpdf\SetaFpdi;

require_once 'vendor/autoload.php';

$pdf = new SetaFpdi();

$pageCount = $pdf->setSourceFile('/path/to/template.pdf');

for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
    $tpl = $pdf->importPage($pageNo);
    $pdf->AddPage();
    $pdf->useTemplate($tpl, ['adjustPageSize' => true]);
}

$pdf->Output();
```

#### Generating PDF/A documents

With SetaFPDF you are able to create PDF/A documents. Anyhow it's up to you to not use features which are not allowed in PDF/A. Also it's up to you to add the missing puzzle pieces through the underlaying SetaPDF functionallities. 
There's a simple example available in `demos/pdf-a-3b.php` that adds the required pieces to make the PDF PDF/A conform.

If you want to create PDF/A documents while importing other files, you need to make sure, that these documents are PDF/A already.

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
