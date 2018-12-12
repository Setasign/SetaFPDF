<?php

use setasign\SetaFpdf\Modules\Document;
use \setasign\SetaFpdf\SetaFpdf;

require_once '../vendor/autoload.php';

class PdfA extends SetaFpdf
{
    private function makePdfA()
    {
        $document = $this->getManager()->getModule(Document::class)->get();
        $info = $document->getInfo();

        $info->setSyncMetadata(true);
        $info->setAll($info->getAll());

        // fix creation date:
        $creationDate = $info->getCreationDate(false);
        if ($creationDate) {
            $info->setCreationDate(new \SetaPDF_Core_DataStructure_Date($creationDate->getAsDateTime()));
        } else {
            $info->setCreationDate(new \SetaPDF_Core_DataStructure_Date());
        }
        $info->setTrapped(\SetaPDF_Core_Document_Info::TRAPPED_TRUE);

        $info->updateXmp('http://www.aiim.org/pdfa/ns/id/', 'part', '3');
        $info->updateXmp('http://www.aiim.org/pdfa/ns/id/', 'conformance', 'B');

        $fileId = \SetaPDF_Core_Type_HexString::str2hex($document->getFileIdentifier(true));
        $uuid = 'uuid:' . substr($fileId, 0, 8) . '-' . substr($fileId, 8, 4) . '-' . substr($fileId, 12, 4) . '-'
            . substr($fileId, 16, 4) . '-' . substr($fileId, 20, 12);

        $info->xmlAliases['http://ns.adobe.com/xap/1.0/mm/'] = 'xmpMM';
        $info->updateXmp('http://ns.adobe.com/xap/1.0/mm/', 'DocumentID', $uuid);

        $info->syncMetadata();

        // fix Trapped property
        $xml = $info->getMetadata();

        $description =  $xml->createElementNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'Description');
        // "[...]an empty string, which means that the XMP is physically local to the resource being described.[...]"
        $description->setAttribute('rdf:about', '');
        $description->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:pdfaExtension', 'http://www.aiim.org/pdfa/ns/extension/');
        $description->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:pdfaSchema', 'http://www.aiim.org/pdfa/ns/schema#');
        $description->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:pdfaProperty', 'http://www.aiim.org/pdfa/ns/property#');

        $rdf = $xml->getElementsByTagName('RDF')->item(0);
        $rdf->appendChild($description);

        $schemas = $xml->createElementNS('http://www.aiim.org/pdfa/ns/extension/', 'schemas');
        $description->appendChild($schemas);
        $bag = $xml->createElementNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'Bag');
        $schemas->appendChild($bag);
        $li = $xml->createElementNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:li');
        $li->setAttributeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:parseType', 'Resource');
        $bag->appendChild($li);

        $namespaceURI = $xml->createElementNS('http://www.aiim.org/pdfa/ns/schema#', 'namespaceURI', 'http://ns.adobe.com/pdf/1.3/');
        $li->appendChild($namespaceURI);
        $prefix = $xml->createElementNS('http://www.aiim.org/pdfa/ns/schema#', 'prefix', 'pdf');
        $li->appendChild($prefix);
        $schema = $xml->createElementNS('http://www.aiim.org/pdfa/ns/schema#', 'schema', 'Adobe PDF Schema');
        $li->appendChild($schema);

        $property = $xml->createElementNS('http://www.aiim.org/pdfa/ns/schema#', 'property');
        $li->appendChild($property);
        $seq = $xml->createElementNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:Seq');
        $property->appendChild($seq);
        $li = $xml->createElementNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:li');
        $li->setAttributeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:parseType', 'Resource');
        $seq->appendChild($li);

        $category = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'category', 'internal');
        $li->appendChild($category);
        $description = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'description', 'A name object indicating whether the document has been modified to include trapping information');
        $li->appendChild($description);
        $name = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'name', 'Trapped');
        $li->appendChild($name);
        $valueType = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'valueType', 'Text');
        $li->appendChild($valueType);

        // save the updated metadata back to the PDF
        $info->syncMetadata();

        $outputIntents = $document->getCatalog()->getOutputIntents();
        $iccStream = \SetaPDF_Core_IccProfile_Stream::create($document, __DIR__ . '/../assets/icc/sRGB_ICC_v4_Appearance.icc');
        $outputIntent = \SetaPDF_Core_OutputIntent::createByProfile('GTS_PDFA1', $iccStream);
        $outputIntents->addOutputIntent($outputIntent);

        $document->setPdfVersion('1.4');
    }

    public function Output($dest = '', $name = '')
    {
        $this->makePdfA();
        return parent::Output($dest, $name);
    }
}

$pdf = new PdfA();
$pdf->AddPage();
$pdf->AddFont('DejaVuSans', '', __DIR__ . '/../assets/fonts/DejaVu/DejaVuSans.ttf');
$pdf->SetFont('DejaVuSans', '', 20);
$pdf->Write(20, 'I am a PDF/A-3b document!');
$pdf->Output('D');