<?php

namespace App\Services\Fiscal;

class NfeXmlParser
{
    /**
     * @return array{
     *  nfe_key: string|null,
     *  number: string|null,
     *  series: string|null,
     *  issued_at: string|null,
     *  supplier: array{cnpj: string|null, name: string|null},
     *  items: array<int, array{
     *      sku: string|null,
     *      ean: string|null,
     *      name: string,
     *      ncm: string|null,
     *      cest: string|null,
     *      cfop: string|null,
     *      quantity: int,
     *      unit_cost: float|null,
     *      total: float|null
     *  }>
     * }
     */
    public function parse(string $xmlContent): array
    {
        $xmlContent = trim($xmlContent);
        if ($xmlContent === '') {
            throw new \RuntimeException('XML vazio.');
        }

        // NF-e XML does not require DTD/DOCTYPE. Block it to reduce XXE / entity related attacks.
        if (stripos($xmlContent, '<!DOCTYPE') !== false || stripos($xmlContent, '<!ENTITY') !== false) {
            throw new \RuntimeException('XML com DOCTYPE/ENTITY não é permitido.');
        }

        $prevErrors = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
        if (! $xml) {
            libxml_clear_errors();
            libxml_use_internal_errors($prevErrors);
            throw new \RuntimeException('XML inválido (não foi possível ler).');
        }

        libxml_clear_errors();
        libxml_use_internal_errors($prevErrors);

        /** @var array<int, \SimpleXMLElement> $infList */
        $infList = $xml->xpath("//*[local-name()='infNFe']") ?: [];
        $inf = $infList[0] ?? null;
        if (! $inf) {
            throw new \RuntimeException('XML não parece ser uma NF-e (infNFe não encontrado).');
        }

        $nfeKey = $this->extractNfeKey($xml, $inf);

        /** @var array<int, \SimpleXMLElement> $ideList */
        $ideList = $inf->xpath("./*[local-name()='ide']") ?: [];
        $ide = $ideList[0] ?? null;

        /** @var array<int, \SimpleXMLElement> $emitList */
        $emitList = $inf->xpath("./*[local-name()='emit']") ?: [];
        $emit = $emitList[0] ?? null;

        $number = $ide ? $this->stringValue($ide, "./*[local-name()='nNF']") : null;
        $series = $ide ? $this->stringValue($ide, "./*[local-name()='serie']") : null;

        $issuedAt = null;
        if ($ide) {
            $issuedAt = $this->stringValue($ide, "./*[local-name()='dhEmi']");
            if ($issuedAt === null) {
                $issuedAt = $this->stringValue($ide, "./*[local-name()='dEmi']");
            }
        }

        $supplierCnpj = $emit ? $this->digitsOrNull($this->stringValue($emit, "./*[local-name()='CNPJ']")) : null;
        $supplierName = $emit ? $this->stringValue($emit, "./*[local-name()='xNome']") : null;

        /** @var array<int, \SimpleXMLElement> $detNodes */
        $detNodes = $inf->xpath("./*[local-name()='det']") ?: [];

        $items = [];
        foreach ($detNodes as $det) {
            /** @var array<int, \SimpleXMLElement> $prodList */
            $prodList = $det->xpath("./*[local-name()='prod']") ?: [];
            $prod = $prodList[0] ?? null;
            if (! $prod) {
                continue;
            }

            $sku = $this->stringValue($prod, "./*[local-name()='cProd']");
            $name = $this->stringValue($prod, "./*[local-name()='xProd']") ?: '';
            $name = trim(preg_replace('/\\s+/', ' ', $name) ?? $name);
            if ($name === '') {
                $name = 'Produto';
            }

            $ean = $this->stringValue($prod, "./*[local-name()='cEAN']");
            if ($ean === null || strtoupper(trim($ean)) === 'SEM GTIN') {
                $ean = $this->stringValue($prod, "./*[local-name()='cEANTrib']");
            }
            $ean = $this->digitsOrNull($ean);

            $ncm = $this->digitsOrNull($this->stringValue($prod, "./*[local-name()='NCM']"));
            $cest = $this->digitsOrNull($this->stringValue($prod, "./*[local-name()='CEST']"));
            $cfop = $this->digitsOrNull($this->stringValue($prod, "./*[local-name()='CFOP']"));

            $qComRaw = $this->stringValue($prod, "./*[local-name()='qCom']");
            $qtyFloat = $this->toFloat($qComRaw);
            if ($qtyFloat === null) {
                throw new \RuntimeException('Item com quantidade inválida no XML.');
            }

            $qtyInt = (int) round($qtyFloat);
            if (abs($qtyFloat - $qtyInt) > 0.00001) {
                throw new \RuntimeException('Item com quantidade fracionada no XML. Este sistema ainda não suporta fracionamento.');
            }
            if ($qtyInt < 1) {
                continue;
            }

            $unitCost = $this->toFloat($this->stringValue($prod, "./*[local-name()='vUnCom']"));
            $vProd = $this->toFloat($this->stringValue($prod, "./*[local-name()='vProd']"));

            if ($unitCost === null && $vProd !== null && $qtyInt > 0) {
                $unitCost = $vProd / $qtyInt;
            }

            $total = null;
            if ($unitCost !== null) {
                $total = $unitCost * $qtyInt;
            } elseif ($vProd !== null) {
                $total = $vProd;
            }

            $items[] = [
                'sku' => $sku !== null ? (string) $sku : null,
                'ean' => $ean,
                'name' => $name,
                'ncm' => $ncm,
                'cest' => $cest,
                'cfop' => $cfop,
                'quantity' => $qtyInt,
                'unit_cost' => $unitCost,
                'total' => $total,
            ];
        }

        if ($items === []) {
            throw new \RuntimeException('Nenhum item foi encontrado no XML.');
        }

        return [
            'nfe_key' => $nfeKey,
            'number' => $number,
            'series' => $series,
            'issued_at' => $issuedAt,
            'supplier' => [
                'cnpj' => $supplierCnpj,
                'name' => $supplierName,
            ],
            'items' => $items,
        ];
    }

    private function extractNfeKey(\SimpleXMLElement $xml, \SimpleXMLElement $inf): ?string
    {
        $id = (string) ($inf['Id'] ?? '');
        if ($id !== '') {
            $digits = preg_replace('/\\D+/', '', $id) ?? '';
            // Id costuma vir como "NFe{44_digitos}"
            if (strlen($digits) === 44) {
                return $digits;
            }
        }

        $prot = $xml->xpath("//*[local-name()='protNFe']/*[local-name()='infProt']/*[local-name()='chNFe']") ?: [];
        $ch = $prot[0] ?? null;
        if ($ch) {
            $digits = preg_replace('/\\D+/', '', (string) $ch) ?? '';
            if (strlen($digits) === 44) {
                return $digits;
            }
        }

        return null;
    }

    private function stringValue(\SimpleXMLElement $node, string $xpath): ?string
    {
        $list = $node->xpath($xpath) ?: [];
        $val = $list[0] ?? null;
        if (! $val) {
            return null;
        }

        $s = trim((string) $val);
        return $s !== '' ? $s : null;
    }

    private function digitsOrNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\\D+/', '', $value) ?? '';
        return $digits !== '' ? $digits : null;
    }

    private function toFloat(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $value = str_replace(',', '.', $value);
        $n = (float) $value;
        return is_finite($n) ? $n : null;
    }
}
