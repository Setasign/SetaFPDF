<?php

use setasign\SetaFpdf\SetaFpdi;

require_once '../vendor/autoload.php';

class ZUGFeRD2p1 extends SetaFpdi
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

        // update metadata to PDF/A-3b
        $info->updateXmp('http://www.aiim.org/pdfa/ns/id/', 'part', '3');
        $info->updateXmp('http://www.aiim.org/pdfa/ns/id/', 'conformance', 'B');

        // resolve the specification identifier from ZUGFeRD package to resolve the conformance level
        $dom = new \DOMDocument();
        $dom->loadXml($this->zugferdXml);

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('x', 'adobe:ns:meta/');
        $xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        $xpath->registerNamespace('zf21', 'urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#');
        $xpath->registerNamespace('ram', 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100');
        $xpath->registerNamespace('rsm', 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100');

        $specificationIdentifier = $xpath->query(
            '//rsm:CrossIndustryInvoice/rsm:ExchangedDocumentContext/ram:GuidelineSpecifiedDocumentContextParameter/ram:ID'
        )->item(0)->textContent;

        $conformanceLevels = [
            'urn:cen.eu:en16931:2017#conformant#urn:factur-x.eu:1p0:extended' => 'EXTENDED',
            'urn:cen.eu:en16931:2017' => 'EN 16931',
            'urn:cen.eu:en16931:2017#compliant#urn:factur-x.eu:1p0:basic' => 'BASIC',
            'urn:factur-x.eu:1p0:basicwl' => 'BASIC WL',
            'urn:factur-x.eu:1p0:minimum' => 'MINIMUM'
        ];

        if (!isset($conformanceLevels[$specificationIdentifier])) {
            throw new \Exception('Unknown specification identifier in XML.');
        }

        $conformanceLevel = $conformanceLevels[$specificationIdentifier];
        $version = '1.0';

        $info->xmlAliases['urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#'] = 'fx';
        $info->updateXmp('urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#', 'ConformanceLevel', $conformanceLevel);
        $info->updateXmp('urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#', 'DocumentFileName', 'factur-x.xml');
        $info->updateXmp('urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#', 'DocumentType', 'INVOICE');
        $info->updateXmp('urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#', 'Version', $version);

        $fileId = \SetaPDF_Core_Type_HexString::str2hex($document->getFileIdentifier(true));
        $uuid = 'uuid:' . substr($fileId, 0, 8) . '-' . substr($fileId, 8, 4) . '-' . substr($fileId, 12, 4) . '-'
            . substr($fileId, 16, 4) . '-' . substr($fileId, 20, 12);

        $info->xmlAliases['http://ns.adobe.com/xap/1.0/mm/'] = 'xmpMM';
        $info->updateXmp('http://ns.adobe.com/xap/1.0/mm/', 'DocumentID', $uuid);

        $fileSpecification = \SetaPDF_Core_FileSpecification::createEmbedded(
            $document,
            new \SetaPDF_Core_Reader_String($this->zugferdXml),
            'factur-x.xml',
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

        $schema = $xml->createElementNS('http://www.aiim.org/pdfa/ns/schema#', 'schema', 'Factur-x PDFA Extension Schema');
        $li->appendChild($schema);
        $namespaceURI = $xml->createElementNS('http://www.aiim.org/pdfa/ns/schema#', 'namespaceURI', 'urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#');
        $li->appendChild($namespaceURI);
        $prefix = $xml->createElementNS('http://www.aiim.org/pdfa/ns/schema#', 'prefix', 'fx');
        $li->appendChild($prefix);
        $property = $xml->createElementNS('http://www.aiim.org/pdfa/ns/schema#', 'property');
        $li->appendChild($property);
        $seq = $xml->createElementNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:Seq');
        $property->appendChild($seq);

        $li = $xml->createElementNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:li');
        $li->setAttributeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:parseType', 'Resource');
        $seq->appendChild($li);
        $name = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'name', 'DocumentType');
        $li->appendChild($name);
        $valueType = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'valueType', 'Text');
        $li->appendChild($valueType);
        $category = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'category', 'external');
        $li->appendChild($category);
        $description = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'description', 'In ZUGFeRD invoices, the document type will always contain INVOICE');
        $li->appendChild($description);

        $li = $xml->createElementNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:li');
        $li->setAttributeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:parseType', 'Resource');
        $seq->appendChild($li);
        $name = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'name', 'DocumentFileName');
        $li->appendChild($name);
        $valueType = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'valueType', 'Text');
        $li->appendChild($valueType);
        $category = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'category', 'external');
        $li->appendChild($category);
        $description = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'description', 'The file name of the embedded invoicing data document; In ZUGFeRD 2.1, this value is fixed as factur-x.xml');
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
        $description = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'description', 'The major and minor version of the underlying in-voice data specification. Here it is Factur-X 1.0, which is synonymous with ZUGFeRD 2.1.');
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
        $description = $xml->createElementNS('http://www.aiim.org/pdfa/ns/property#', 'description', 'The profile of XML-invoicing data in accordance with the specifications by Factur-X (permitted val-ues: MINIMUM, BASIC WL, BASIC, EN 16931, EXTENDED)');
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

        $document->setPdfVersion('1.4');
    }

    public function Output($dest = '', $name = '')
    {
        $this->makePdfAWithZUGFeRD();
        return parent::Output($dest, $name);
    }
}

$pdf = new ZUGFeRD2p1();

// We do not build the PDF from scratch her but import an existing document for demonstration purpose.
// For sure this document needs to be valid in view to PDF/A requirements!

// As a programmer we are lazy and import the page of an existing ZUGFeRD document, and simply re-build it here.
// An import with FPDI functionalities does not import the XML nor metadata of the original document.

$pageCount = $pdf->setSourceFile(__DIR__ . '/../assets/pdfs/ZUGFeRD/zugferd_2p1_BASIC_Einfach.pdf');
for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
    $tpl = $pdf->importPage($pageNo);
    $pdf->AddPage();
    $pdf->useTemplate($tpl, ['adjustPageSize' => true]);
}

$pdf->setZUGFeRDXml(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<rsm:CrossIndustryInvoice
    xmlns:a="urn:un:unece:uncefact:data:standard:QualifiedDataType:100"
    xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100"
    xmlns:qdt="urn:un:unece:uncefact:data:standard:QualifiedDataType:10"
    xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100"
    xmlns:xs="http://www.w3.org/2001/XMLSchema"
    xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100">
    <rsm:ExchangedDocumentContext>
        <ram:GuidelineSpecifiedDocumentContextParameter>
            <ram:ID>urn:cen.eu:en16931:2017#compliant#urn:factur-x.eu:1p0:basic</ram:ID>
        </ram:GuidelineSpecifiedDocumentContextParameter>
    </rsm:ExchangedDocumentContext>
    <rsm:ExchangedDocument>
        <ram:ID>471102</ram:ID>
        <ram:TypeCode>380</ram:TypeCode>
        <ram:IssueDateTime>
            <udt:DateTimeString format="102">20200305</udt:DateTimeString>
        </ram:IssueDateTime>
        <ram:IncludedNote>
            <ram:Content>Rechnung gemäß Bestellung vom 01.03.2020.</ram:Content>
        </ram:IncludedNote>
        <ram:IncludedNote>
            <ram:Content>Lieferant GmbH				
Lieferantenstraße 20				
80333 München				
Deutschland				
Geschäftsführer: Hans Muster
Handelsregisternummer: H A 123
      </ram:Content>
        </ram:IncludedNote>
        <ram:IncludedNote>
            <ram:Content>Unsere GLN: 4000001123452
Ihre GLN: 4000001987658
Ihre Kundennummer: GE2020211


Zahlbar innerhalb 30 Tagen netto bis 04.04.2020, 3% Skonto innerhalb 10 Tagen bis 15.03.2020.
      </ram:Content>
        </ram:IncludedNote>
    </rsm:ExchangedDocument>
    <rsm:SupplyChainTradeTransaction>
        <ram:IncludedSupplyChainTradeLineItem>
            <ram:AssociatedDocumentLineDocument>
                <ram:LineID>1</ram:LineID>
            </ram:AssociatedDocumentLineDocument>
            <ram:SpecifiedTradeProduct>
                <ram:GlobalID schemeID="0160">4012345001235</ram:GlobalID>
                <ram:Name>GTIN: 4012345001235
Unsere Art.-Nr.: TB100A4
Trennblätter A4
        </ram:Name>
            </ram:SpecifiedTradeProduct>
            <ram:SpecifiedLineTradeAgreement>
                <ram:NetPriceProductTradePrice>
                    <ram:ChargeAmount>9.90</ram:ChargeAmount>
                </ram:NetPriceProductTradePrice>
            </ram:SpecifiedLineTradeAgreement>
            <ram:SpecifiedLineTradeDelivery>
                <ram:BilledQuantity unitCode="H87">20.0000</ram:BilledQuantity>
            </ram:SpecifiedLineTradeDelivery>
            <ram:SpecifiedLineTradeSettlement>
                <ram:ApplicableTradeTax>
                    <ram:TypeCode>VAT</ram:TypeCode>
                    <ram:CategoryCode>S</ram:CategoryCode>
                    <ram:RateApplicablePercent>19</ram:RateApplicablePercent>
                </ram:ApplicableTradeTax>
                <ram:SpecifiedTradeSettlementLineMonetarySummation>
                    <ram:LineTotalAmount>198.00</ram:LineTotalAmount>
                </ram:SpecifiedTradeSettlementLineMonetarySummation>
            </ram:SpecifiedLineTradeSettlement>
        </ram:IncludedSupplyChainTradeLineItem>
        <ram:ApplicableHeaderTradeAgreement>
            <ram:SellerTradeParty>
                <ram:Name>Lieferant GmbH</ram:Name>
                <ram:PostalTradeAddress>
                    <ram:PostcodeCode>80333</ram:PostcodeCode>
                    <ram:LineOne>Lieferantenstraße 20</ram:LineOne>
                    <ram:CityName>München</ram:CityName>
                    <ram:CountryID>DE</ram:CountryID>
                </ram:PostalTradeAddress>
                <ram:SpecifiedTaxRegistration>
                    <ram:ID schemeID="FC">201/113/40209</ram:ID>
                </ram:SpecifiedTaxRegistration>
                <ram:SpecifiedTaxRegistration>
                    <ram:ID schemeID="VA">DE123456789</ram:ID>
                </ram:SpecifiedTaxRegistration>
            </ram:SellerTradeParty>
            <ram:BuyerTradeParty>
                <ram:Name>Kunden AG Mitte</ram:Name>
                <ram:PostalTradeAddress>
                    <ram:PostcodeCode>69876</ram:PostcodeCode>
                    <ram:LineOne>Hans Muster</ram:LineOne>
                    <ram:LineTwo>Kundenstraße 15</ram:LineTwo>
                    <ram:CityName>Frankfurt</ram:CityName>
                    <ram:CountryID>DE</ram:CountryID>
                </ram:PostalTradeAddress>
            </ram:BuyerTradeParty>
        </ram:ApplicableHeaderTradeAgreement>
        <ram:ApplicableHeaderTradeDelivery>
            <ram:ActualDeliverySupplyChainEvent>
                <ram:OccurrenceDateTime>
                    <udt:DateTimeString format="102">20200305</udt:DateTimeString>
                </ram:OccurrenceDateTime>
            </ram:ActualDeliverySupplyChainEvent>
        </ram:ApplicableHeaderTradeDelivery>
        <ram:ApplicableHeaderTradeSettlement>
            <ram:InvoiceCurrencyCode>EUR</ram:InvoiceCurrencyCode>
            <ram:ApplicableTradeTax>
                <ram:CalculatedAmount>37.62</ram:CalculatedAmount>
                <ram:TypeCode>VAT</ram:TypeCode>
                <ram:BasisAmount>198.00</ram:BasisAmount>
                <ram:CategoryCode>S</ram:CategoryCode>
                <ram:RateApplicablePercent>19.00</ram:RateApplicablePercent>
            </ram:ApplicableTradeTax>
            <ram:SpecifiedTradePaymentTerms>
                <ram:DueDateDateTime>
                    <udt:DateTimeString format="102">20200404</udt:DateTimeString>
                </ram:DueDateDateTime>
            </ram:SpecifiedTradePaymentTerms>
            <ram:SpecifiedTradeSettlementHeaderMonetarySummation>
                <ram:LineTotalAmount>198.00</ram:LineTotalAmount>
                <ram:ChargeTotalAmount>0.00</ram:ChargeTotalAmount>
                <ram:AllowanceTotalAmount>0.00</ram:AllowanceTotalAmount>
                <ram:TaxBasisTotalAmount>198.00</ram:TaxBasisTotalAmount>
                <ram:TaxTotalAmount currencyID="EUR">37.62</ram:TaxTotalAmount>
                <ram:GrandTotalAmount>235.62</ram:GrandTotalAmount>
                <ram:DuePayableAmount>235.62</ram:DuePayableAmount>
            </ram:SpecifiedTradeSettlementHeaderMonetarySummation>
        </ram:ApplicableHeaderTradeSettlement>
    </rsm:SupplyChainTradeTransaction>
</rsm:CrossIndustryInvoice>
XML
);

$pdf->Output('D');
