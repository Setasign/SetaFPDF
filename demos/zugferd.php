<?php
/* PLEASE NOTE THAT THIS ZUGFERD VERSION SHOULD NOT BE USED ANYMORE! */
use setasign\SetaFpdf\SetaFpdi;

require_once '../vendor/autoload.php';

class ZUGFeRD extends SetaFpdi
{
    /**
     * @var null|string
     */
    private $zugferdXml;

    /**
     * @param string $xml
     */
    public function setZUGFeRDXml($xml)
    {
        $this->zugferdXml = $xml;
    }

    private function makePdfAWithZUGFeRD()
    {
        if (!is_string($this->zugferdXml)) {
            throw new \BadMethodCallException('Missing ZUGFeRD xml!');
        }

        $document = $this->getManager()->getDocument()->get();
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

        $info->updateXmp('http://www.aiim.org/pdfa/ns/id/', 'part', '3');
        $info->updateXmp('http://www.aiim.org/pdfa/ns/id/', 'conformance', 'B');

        $info->xmlAliases['urn:ferd:pdfa:CrossIndustryDocument:invoice:1p0#'] = 'zf';
        $info->updateXmp('urn:ferd:pdfa:CrossIndustryDocument:invoice:1p0#', 'ConformanceLevel', 'BASIC');
        $info->updateXmp('urn:ferd:pdfa:CrossIndustryDocument:invoice:1p0#', 'DocumentFileName', 'ZUGFeRD-invoice.xml');
        $info->updateXmp('urn:ferd:pdfa:CrossIndustryDocument:invoice:1p0#', 'DocumentType', 'INVOICE');
        $info->updateXmp('urn:ferd:pdfa:CrossIndustryDocument:invoice:1p0#', 'Version', '1.0');

        $fileId = \SetaPDF_Core_Type_HexString::str2hex($document->getFileIdentifier(true));
        $uuid = 'uuid:' . substr($fileId, 0, 8) . '-' . substr($fileId, 8, 4) . '-' . substr($fileId, 12, 4) . '-'
            . substr($fileId, 16, 4) . '-' . substr($fileId, 20, 12);

        $info->xmlAliases['http://ns.adobe.com/xap/1.0/mm/'] = 'xmpMM';
        $info->updateXmp('http://ns.adobe.com/xap/1.0/mm/', 'DocumentID', $uuid);

        $fileSpecification = \SetaPDF_Core_FileSpecification::createEmbedded(
            $document,
            new \SetaPDF_Core_Reader_String($this->zugferdXml),
            'ZUGFeRD-invoice.xml',
            [
                \SetaPDF_Core_EmbeddedFileStream::PARAM_MODIFICATION_DATE => (new \DateTime())
            ],
            'text/xml'
        );

        $fileSpecification->getDictionary()->offsetSet('AFRelationship', new \SetaPDF_Core_Type_Name('Alternative'));

        $embeddedFiles = $document->getCatalog()->getNames()->getEmbeddedFiles();
        $object = $embeddedFiles->add($fileSpecification->getUnicodeFileSpecification(), $fileSpecification);

        $dict = $document->getCatalog()->getDictionary();
        $dict['AF'] = new \SetaPDF_Core_Type_Array([$object]);

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

        // ZUGFeRD Schema
        $li = $xml->createElementNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:li');
        $li->setAttributeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:parseType', 'Resource');
        $bag->appendChild($li);

        $schema = $xml->createElementNS('http://www.aiim.org/pdfa/ns/schema#', 'schema', 'ZUGFeRD PDFA Extension Schema');
        $li->appendChild($schema);
        $namespaceURI = $xml->createElementNS('http://www.aiim.org/pdfa/ns/schema#', 'namespaceURI', 'urn:ferd:pdfa:CrossIndustryDocument:invoice:1p0#');
        $li->appendChild($namespaceURI);
        $prefix = $xml->createElementNS('http://www.aiim.org/pdfa/ns/schema#', 'prefix', 'zf');
        $li->appendChild($prefix);
        $property = $xml->createElementNS('http://www.aiim.org/pdfa/ns/schema#', 'property');
        $li->appendChild($property);
        $seq = $xml->createElementNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:Seq');
        $property->appendChild($seq);

        $li = $xml->createElementNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:li');
        $li->setAttributeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:parseType', 'Resource');
        $seq->appendChild($li);
        $name = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'name', 'DocumentFileName');
        $li->appendChild($name);
        $valueType = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'valueType', 'Text');
        $li->appendChild($valueType);
        $category = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'category', 'external');
        $li->appendChild($category);
        $description = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'description', 'name of the embedded XML invoice file');
        $li->appendChild($description);

        $li = $xml->createElementNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:li');
        $li->setAttributeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:parseType', 'Resource');
        $seq->appendChild($li);
        $name = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'name', 'DocumentType');
        $li->appendChild($name);
        $valueType = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'valueType', 'Text');
        $li->appendChild($valueType);
        $category = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'category', 'external');
        $li->appendChild($category);
        $description = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'description', 'INVOICE');
        $li->appendChild($description);

        $li = $xml->createElementNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:li');
        $li->setAttributeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:parseType', 'Resource');
        $seq->appendChild($li);
        $name = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'name', 'Version');
        $li->appendChild($name);
        $valueType = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'valueType', 'Text');
        $li->appendChild($valueType);
        $category = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'category', 'external');
        $li->appendChild($category);
        $description = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'description', 'The actual version of the ZUGFeRD data');
        $li->appendChild($description);

        $li = $xml->createElementNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:li');
        $li->setAttributeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:parseType', 'Resource');
        $seq->appendChild($li);
        $name = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'name', 'ConformanceLevel');
        $li->appendChild($name);
        $valueType = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'valueType', 'Text');
        $li->appendChild($valueType);
        $category = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'category', 'external');
        $li->appendChild($category);
        $description = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'description', 'The conformance level of the ZUGFeRD data');
        $li->appendChild($description);
        // ZUGFeRD end

        // save the updated metadata back to the PDF
        $info->syncMetadata();

        $outputIntents = $document->getCatalog()->getOutputIntents();
        $iccStream = \SetaPDF_Core_IccProfile_Stream::create(
            $document,
            __DIR__ . '/../assets/icc/sRGB_ICC_v4_Appearance.icc'
        );
        $outputIntent = \SetaPDF_Core_OutputIntent::createByProfile(
            SetaPDF_Core_OutputIntent::SUBTYPE_GTS_PDFA1,
            $iccStream
        );
        $outputIntents->addOutputIntent($outputIntent);

        $document->setPdfVersion('1.7');
    }

    public function Output($dest = '', $name = '')
    {
        $this->makePdfAWithZUGFeRD();
        return parent::Output($dest, $name);
    }
}

$pdf = new ZUGFeRD();

// We do not build the PDF from scratch her but import an existing document for demonstration purpose.
// For sure this document needs to be valid in view to PDF/A requirements!
$pageCount = $pdf->setSourceFile(__DIR__ . '/../assets/pdfs/tektown/eBook-Invoice.pdf');
for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
    $tpl = $pdf->importPage($pageNo);
    $pdf->AddPage();
    $pdf->useTemplate($tpl, ['adjustPageSize' => true]);
}

$pdf->setZUGFeRDXml(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rsm:CrossIndustryDocument xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rsm="urn:ferd:CrossIndustryDocument:invoice:1p0" xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:12" xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:15">
  <rsm:SpecifiedExchangedDocumentContext>
    <ram:GuidelineSpecifiedDocumentContextParameter>
      <ram:ID>urn:ferd:CrossIndustryDocument:invoice:1p0:comfort</ram:ID>
    </ram:GuidelineSpecifiedDocumentContextParameter>
  </rsm:SpecifiedExchangedDocumentContext>
  <rsm:HeaderExchangedDocument>
    <ram:ID>0806</ram:ID>
    <ram:Name>INVOICE</ram:Name>
    <ram:TypeCode>380</ram:TypeCode>
    <ram:IssueDateTime>
      <udt:DateTimeString format="102">20200122</udt:DateTimeString>
    </ram:IssueDateTime>
  </rsm:HeaderExchangedDocument>
  <rsm:SpecifiedSupplyChainTradeTransaction>
    <ram:ApplicableSupplyChainTradeAgreement>
      <ram:SellerTradeParty>
        <ram:Name>tektown Ltd.</ram:Name>
        <ram:PostalTradeAddress>
          <ram:PostcodeCode>4456</ram:PostcodeCode>
          <ram:LineOne>Parker Av. 214</ram:LineOne>
          <ram:LineTwo></ram:LineTwo>
          <ram:CityName>Motorcity</ram:CityName>
          <ram:CountryID>DE</ram:CountryID>
        </ram:PostalTradeAddress>
      </ram:SellerTradeParty>
      <ram:BuyerTradeParty>
        <ram:Name>Do-Little Enterprises</ram:Name>
        <ram:PostalTradeAddress>
          <ram:PostcodeCode>1003</ram:PostcodeCode>
          <ram:LineOne>Evergreen Terrace 103</ram:LineOne>
          <ram:LineTwo>John Doe</ram:LineTwo>
          <ram:CityName>Richmane</ram:CityName>
          <ram:CountryID>US</ram:CountryID>
        </ram:PostalTradeAddress>
      </ram:BuyerTradeParty>
    </ram:ApplicableSupplyChainTradeAgreement>
    <ram:ApplicableSupplyChainTradeDelivery>
      <ram:ActualDeliverySupplyChainEvent>
        <ram:OccurrenceDateTime>
          <udt:DateTimeString format="102">20200122</udt:DateTimeString>
        </ram:OccurrenceDateTime>
      </ram:ActualDeliverySupplyChainEvent>
    </ram:ApplicableSupplyChainTradeDelivery>
    <ram:ApplicableSupplyChainTradeSettlement>
      <ram:PaymentReference></ram:PaymentReference>
      <ram:InvoiceCurrencyCode>EUR</ram:InvoiceCurrencyCode>
      <ram:SpecifiedTradeSettlementPaymentMeans>
        <ram:TypeCode>1</ram:TypeCode>
        <ram:Information></ram:Information>
      </ram:SpecifiedTradeSettlementPaymentMeans>
      <ram:SpecifiedTradeSettlementMonetarySummation>
        <ram:LineTotalAmount currencyID="USD">58.00</ram:LineTotalAmount>
        <ram:ChargeTotalAmount currencyID="USD">0.00</ram:ChargeTotalAmount>
        <ram:AllowanceTotalAmount currencyID="USD">0.00</ram:AllowanceTotalAmount>
        <ram:TaxBasisTotalAmount currencyID="USD">58.00</ram:TaxBasisTotalAmount>
        <ram:TaxTotalAmount currencyID="USD">0.00</ram:TaxTotalAmount>
        <ram:GrandTotalAmount currencyID="USD">58.00</ram:GrandTotalAmount>
      </ram:SpecifiedTradeSettlementMonetarySummation>
    </ram:ApplicableSupplyChainTradeSettlement>
    <ram:IncludedSupplyChainTradeLineItem>
      <ram:AssociatedDocumentLineDocument>
        <ram:LineID>1</ram:LineID>
      </ram:AssociatedDocumentLineDocument>
      <ram:SpecifiedSupplyChainTradeAgreement>
        <ram:GrossPriceProductTradePrice>
          <ram:ChargeAmount currencyID="USD">29.0000</ram:ChargeAmount>
        </ram:GrossPriceProductTradePrice>
        <ram:NetPriceProductTradePrice>
          <ram:ChargeAmount currencyID="USD">29.0000</ram:ChargeAmount>
        </ram:NetPriceProductTradePrice>
      </ram:SpecifiedSupplyChainTradeAgreement>
      <ram:SpecifiedSupplyChainTradeDelivery>
        <ram:BilledQuantity unitCode="C62">2.0000</ram:BilledQuantity>
      </ram:SpecifiedSupplyChainTradeDelivery>
      <ram:SpecifiedSupplyChainTradeSettlement>
        <ram:SpecifiedTradeSettlementMonetarySummation>
          <ram:LineTotalAmount currencyID="USD">58.00</ram:LineTotalAmount>
        </ram:SpecifiedTradeSettlementMonetarySummation>
      </ram:SpecifiedSupplyChainTradeSettlement>
      <ram:SpecifiedTradeProduct>
        <ram:SellerAssignedID></ram:SellerAssignedID>
        <ram:Name>tektown techMag, Issue 137</ram:Name>
      </ram:SpecifiedTradeProduct>
    </ram:IncludedSupplyChainTradeLineItem>
  </rsm:SpecifiedSupplyChainTradeTransaction>
</rsm:CrossIndustryDocument>
XML
);

$pdf->Output('D');
