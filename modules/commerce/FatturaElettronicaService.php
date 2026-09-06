<?php
/**
 * ELECTRONIC INVOICE (FATTURAPA XML v1.2.2) GENERATOR
 * Standard SDI (Sistema di Interscambio) Agenzia delle Entrate
 * Mirco Pregnolato Universe Commerce Core
 */

declare(strict_types=1);

namespace Dependex\Commerce;

use PDO;

class FatturaElettronicaService {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Generate standard SDI XML string for a specific order.
     */
    public function generateXml(string $orderIdOrNumber): array {
        $stmt = $this->db->prepare("
            SELECT o.*, 
                   c.email, c.first_name, c.last_name, c.company_name, c.vat_number, c.fiscal_code,
                   c.address_line1, c.city, c.postal_code, c.country,
                   b.name as business_name, b.domain as business_domain
            FROM commerce_orders o
            JOIN commerce_customers c ON o.customer_id = c.id
            JOIN commerce_businesses b ON o.business_id = b.id
            WHERE o.id = ? OR o.order_number = ?
            LIMIT 1
        ");
        $stmt->execute([$orderIdOrNumber, $orderIdOrNumber]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return ['ok' => false, 'error' => 'Ordine non trovato'];
        }

        // Fetch items
        $itemStmt = $this->db->prepare("
            SELECT * FROM commerce_order_items WHERE order_id = ?
        ");
        $itemStmt->execute([$order['id']]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        $dateFormatted = date('Y-m-d', strtotime($order['created_at']));
        $orderNumClean = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$order['order_number']);
        $progressivoInvio = substr(hash('crc32b', $order['id']), 0, 10);

        // Header Cedente/Prestatore (Mirco Pregnolato Universe / DEPENDEX)
        $cedenteVat = CommerceEnv::get('BUSINESS_VAT_NUMBER', 'IT01234567890');
        $cedenteFiscal = CommerceEnv::get('BUSINESS_FISCAL_CODE', 'IT01234567890');
        $cedenteName = CommerceEnv::get('BUSINESS_LEGAL_NAME', 'Mirco Pregnolato - Ecosystem Hub');
        $cedenteAddress = CommerceEnv::get('BUSINESS_ADDRESS', 'Via Eridania 81');
        $cedenteCap = CommerceEnv::get('BUSINESS_CAP', '45030');
        $cedenteCity = CommerceEnv::get('BUSINESS_CITY', 'Occhiobello');
        $cedenteProv = CommerceEnv::get('BUSINESS_PROV', 'RO');
        $cedenteCountry = 'IT';

        // Cessionario/Committente (Customer)
        $isCompany = !empty($order['vat_number']);
        $destinazioneCodice = '0000000'; // Default SDI B2C or PEC
        $customerVat = preg_replace('/[^a-zA-Z0-9]/', '', (string)($order['vat_number'] ?? ''));
        $customerFiscal = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', (string)($order['fiscal_code'] ?? '')));
        
        $customerName = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''));
        $companyName = trim((string)($order['company_name'] ?? ''));
        $denomOrCognome = $isCompany && $companyName ? $companyName : ($customerName ?: 'Cliente Privato');

        $custAddress = trim((string)($order['address_line1'] ?? 'Via Indirizzo 1'));
        $custCap = trim((string)($order['postal_code'] ?? '00100'));
        $custCity = trim((string)($order['city'] ?? 'Roma'));
        $custCountry = strtoupper(trim((string)($order['country'] ?? 'IT'))) ?: 'IT';

        // Build XML
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><p:FatturaElettronica xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:p="http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="FPR12" xsi:schemaLocation="http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2 http://www.fatturapa.gov.it/export/fatturapa/sdi/fatturapa/v1.2/Schema_del_file_xml_FatturaPA_v1.2.xsd"/>');

        // FatturaElettronicaHeader
        $header = $xml->addChild('FatturaElettronicaHeader');
        
        // DatiTrasmissione
        $dt = $header->addChild('DatiTrasmissione');
        $idTrasm = $dt->addChild('IdTrasmittente');
        $idTrasm->addChild('IdPaese', 'IT');
        $idTrasm->addChild('IdCodice', substr($cedenteFiscal, 0, 16));
        $dt->addChild('ProgressivoInvio', $progressivoInvio);
        $dt->addChild('FormatoTrasmissione', 'FPR12');
        $dt->addChild('CodiceDestinatario', $destinazioneCodice);

        // CedentePrestatore
        $cp = $header->addChild('CedentePrestatore');
        $datiAnagCp = $cp->addChild('DatiAnagrafici');
        $idFiscIvaCp = $datiAnagCp->addChild('IdFiscaleIVA');
        $idFiscIvaCp->addChild('IdPaese', 'IT');
        $idFiscIvaCp->addChild('IdCodice', preg_replace('/^IT/i', '', $cedenteVat));
        $datiAnagCp->addChild('CodiceFiscale', $cedenteFiscal);
        $anagCp = $datiAnagCp->addChild('Anagrafica');
        $anagCp->addChild('Denominazione', htmlspecialchars($cedenteName));
        $datiAnagCp->addChild('RegimeFiscale', 'RF01');

        $sedeCp = $cp->addChild('Sede');
        $sedeCp->addChild('Indirizzo', htmlspecialchars($cedenteAddress));
        $sedeCp->addChild('CAP', $cedenteCap);
        $sedeCp->addChild('Comune', htmlspecialchars($cedenteCity));
        $sedeCp->addChild('Provincia', $cedenteProv);
        $sedeCp->addChild('Nazione', $cedenteCountry);

        // CessionarioCommittente
        $cc = $header->addChild('CessionarioCommittente');
        $datiAnagCc = $cc->addChild('DatiAnagrafici');
        if ($customerVat) {
            $idFiscIvaCc = $datiAnagCc->addChild('IdFiscaleIVA');
            $idFiscIvaCc->addChild('IdPaese', $custCountry === 'IT' ? 'IT' : $custCountry);
            $idFiscIvaCc->addChild('IdCodice', preg_replace('/^[A-Z]{2}/i', '', $customerVat));
        }
        if ($customerFiscal) {
            $datiAnagCc->addChild('CodiceFiscale', $customerFiscal);
        }
        $anagCc = $datiAnagCc->addChild('Anagrafica');
        $anagCc->addChild('Denominazione', htmlspecialchars($denomOrCognome));

        $sedeCc = $cc->addChild('Sede');
        $sedeCc->addChild('Indirizzo', htmlspecialchars($custAddress));
        $sedeCc->addChild('CAP', $custCap);
        $sedeCc->addChild('Comune', htmlspecialchars($custCity));
        $sedeCc->addChild('Nazione', $custCountry);

        // FatturaElettronicaBody
        $body = $xml->addChild('FatturaElettronicaBody');
        $dg = $body->addChild('DatiGenerali');
        $dgf = $dg->addChild('DatiGeneraliDocumento');
        $dgf->addChild('TipoDocumento', 'TD01'); // Fattura ordinaria
        $dgf->addChild('Divisa', $order['currency'] ?: 'EUR');
        $dgf->addChild('Data', $dateFormatted);
        $dgf->addChild('Numero', $orderNumClean);
        $dgf->addChild('ImportoTotaleDocumento', number_format((float)$order['total_amount'], 2, '.', ''));

        // DatiBeniServizi
        $dbs = $body->addChild('DatiBeniServizi');
        $lineNum = 1;
        foreach ($items as $item) {
            $dl = $dbs->addChild('DettaglioLinee');
            $dl->addChild('NumeroLinea', (string)$lineNum);
            $dl->addChild('Descrizione', htmlspecialchars($item['product_name']));
            $dl->addChild('Quantita', number_format((float)$item['quantity'], 2, '.', ''));
            $dl->addChild('PrezzoUnitario', number_format((float)$item['unit_price'], 2, '.', ''));
            $dl->addChild('PrezzoTotale', number_format((float)$item['line_total'], 2, '.', ''));
            $dl->addChild('AliquotaIVA', number_format((float)($item['vat_rate'] ?? 0.00), 2, '.', ''));
            if ((float)($item['vat_rate'] ?? 0) == 0) {
                $dl->addChild('Natura', 'N2.2');
            }
            $lineNum++;
        }

        // DatiRiepilogo
        $dr = $dbs->addChild('DatiRiepilogo');
        $dr->addChild('AliquotaIVA', number_format((float)($order['vat_amount'] > 0 ? 22.00 : 0.00), 2, '.', ''));
        if ((float)$order['vat_amount'] == 0) {
            $dr->addChild('Natura', 'N2.2');
        }
        $dr->addChild('ImponibileImporto', number_format((float)$order['subtotal'], 2, '.', ''));
        $dr->addChild('Imposta', number_format((float)$order['vat_amount'], 2, '.', ''));
        $dr->addChild('EsigibilitaIVA', 'I');

        // DatiPagamento
        $dp = $body->addChild('DatiPagamento');
        $dp->addChild('CondizioniPagamento', 'TP02'); // Pagamento completo
        $ddp = $dp->addChild('DettaglioPagamento');
        $ddp->addChild('ModalitaPagamento', 'MP08'); // Carta di credito / PayPal
        $ddp->addChild('DataScadenzaPagamento', $dateFormatted);
        $ddp->addChild('ImportoPagamento', number_format((float)$order['total_amount'], 2, '.', ''));

        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;
        $xmlContent = $dom->saveXML();

        return [
            'ok' => true,
            'filename' => "IT{$cedenteFiscal}_{$orderNumClean}.xml",
            'xml' => $xmlContent
        ];
    }
}
