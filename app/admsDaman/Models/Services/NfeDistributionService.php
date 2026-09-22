<?php

namespace App\admsDaman\Models\Services;

use App\admsDaman\Models\Repository\NfeRepository;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Tools;
use NFePHP\NFe\Common\Standardize;

/**
 * Serviço responsável pela comunicação
 * e sincronização das NF-e com a SEFAZ.
 */
class NfeDistributionService
{
    private NfeRepository $nfeRepository;

    public function __construct()
    {
        $this->nfeRepository = new NfeRepository();
    }

    /**
     * Recuperar os dados atuais de sincronização
     * do CNPJ configurado no sistema.
     *
     * @return array|null
     */
    public function getSyncData(): ?array
    {
        return $this->nfeRepository->getSyncData(
            $_ENV['NFE_CNPJ']
        );
    }

    /**
     * Criar e configurar a comunicação com a SEFAZ.
     *
     * @return Tools
     */
    private function createTools(): Tools
    {
        $config = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb'       => (int) $_ENV['NFE_AMBIENTE'],
            'razaosocial' => $_ENV['NFE_RAZAO_SOCIAL'],
            'siglaUF'     => $_ENV['NFE_UF'],
            'cnpj'        => $_ENV['NFE_CNPJ'],
            'schemes'     => 'PL_009_V4',
            'versao'      => '4.00',
        ];

        $certificate = Certificate::readPfx(
            file_get_contents($_ENV['NFE_CERTIFICATE_PATH']),
            $_ENV['NFE_CERTIFICATE_PASSWORD']
        );

        $tools = new Tools(
            json_encode($config),
            $certificate
        );

        $tools->model(55);

        return $tools;
    }

    /**
     * Verificar se uma nova consulta à SEFAZ pode ser realizada.
     */
    public function canSynchronize(): bool
    {
        return $this->nfeRepository->canSynchronize(
            $_ENV['NFE_CNPJ']
        );
    }

    /**
     * Consultar a distribuição de DF-e na SEFAZ,
     * processar NF-e e eventos recebidos
     * e atualizar o controle de sincronização.
     *
     * @return array
     */
    public function synchronize(): array
    {

        // Verificar se a sincronização está habilitada neste ambiente.
        if (
            filter_var(
                $_ENV['NFE_SYNC_ENABLED'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            ) === false
        ) {
            return [
                'success' => false,
                'blocked' => true,
                'message' => 'Sincronização de NF-e desabilitada neste ambiente.',
            ];
        }
        
        // Verificar proteção de intervalo entre consultas.
        if (!$this->canSynchronize()) {
            return [
                'success' => false,
                'blocked' => true,
                'message' => 'Aguarde o intervalo mínimo antes de consultar novamente.',
            ];
        }

        // Recuperar último NSU salvo.
        $sync = $this->getSyncData();

        if (!$sync) {
            return [
                'success' => false,
                'blocked' => false,
                'message' => 'Controle de sincronização não encontrado.',
            ];
        }

        $ultNSU = $sync['ult_nsu'];

        // Criar comunicação com a SEFAZ.
        $tools = $this->createTools();

        // Consultar distribuição DF-e.
        $response = $tools->sefazDistDFe((int) $ultNSU);

        // Converter XML de retorno.
        $standardize = new Standardize();
        $result = $standardize->toStd($response);

        // Processar documentos retornados pela SEFAZ.
        $dom = new \DOMDocument();
        $dom->loadXML($response);

        $documents = $dom->getElementsByTagName('docZip');

        $savedCount = 0;

        foreach ($documents as $document) {

            $nsu = $document->getAttribute('NSU');
            $schema = $document->getAttribute('schema');

            $compressed = base64_decode(
                trim($document->nodeValue),
                true
            );

            if ($compressed === false) {
                continue;
            }

            $xml = gzdecode($compressed);

            if ($xml === false) {
                continue;
            }

            // --------------------------------------------------
            // RESUMO DE NF-e
            // --------------------------------------------------

            if ($schema === 'resNFe_v1.01.xsd') {

                $saved = $this->saveNfeSummary(
                    $xml,
                    $nsu,
                    $schema
                );

                if (!$saved) {
                    throw new \RuntimeException(
                        "Não foi possível salvar a NF-e do NSU {$nsu}."
                    );
                }

                $savedCount++;

                continue;
            }


            // --------------------------------------------------
            // EVENTOS DA NF-e
            // --------------------------------------------------

            if (
                str_starts_with($schema, 'resEvento_')
                || str_starts_with($schema, 'procEventoNFe_')
            ) {

                $processed = $this->processNfeEvent($xml);

                if (!$processed) {
                    throw new \RuntimeException(
                        "Não foi possível processar o evento do NSU {$nsu}."
                    );
                }

                continue;
            }
        }

        $cStat = isset($result->cStat)
            ? (string) $result->cStat
            : null;

        // Atualizar NSU somente quando a resposta
        // de distribuição for válida.
        if (in_array($cStat, ['137', '138'], true)) {

            $this->nfeRepository->updateSyncData(
                $_ENV['NFE_CNPJ'],
                $result->ultNSU ?? $ultNSU,
                $result->maxNSU ?? $ultNSU,
                $cStat,
                $result->xMotivo ?? null
            );
        }

        return [
            'success' => true,
            'blocked' => false,
            'cStat' => $result->cStat ?? null,
            'message' => $result->xMotivo ?? null,
            'ultNSU' => $result->ultNSU ?? null,
            'maxNSU' => $result->maxNSU ?? null,
            'saved_count' => $savedCount,
        ];
    }

    /**
     * Processar e salvar um resumo de NF-e retornado pela SEFAZ.
     */
    private function saveNfeSummary(
        string $xml,
        string $nsu,
        string $schema
    ): bool {

        $nfe = simplexml_load_string($xml);

        if ($nfe === false) {
            return false;
        }

        $accessKey = (string) ($nfe->chNFe ?? '');

        if (strlen($accessKey) !== 44) {
            return false;
        }

        // A série e o número da NF-e fazem parte da chave de acesso.
        $series = (string) (int) substr($accessKey, 22, 3);
        $number = (string) (int) substr($accessKey, 25, 9);

        // Situação informada no resumo da NF-e:
        // 1 = Uso autorizado
        // 2 = Uso denegado
        // 3 = NF-e cancelada
        $status = match ((string) ($nfe->cSitNFe ?? '')) {
            '1' => 'authorized',
            '2' => 'denied',
            '3' => 'cancelled',
            default => 'unknown',
        };

        $issueDate = null;

        if (!empty((string) $nfe->dhEmi)) {
            $issueDate = (new \DateTimeImmutable(
                (string) $nfe->dhEmi
            ))->format('Y-m-d H:i:s');
        }

        return $this->nfeRepository->createNfe([
            'nsu' => $nsu,
            'access_key' => $accessKey,
            'nfe_number' => $number,
            'series' => $series,
            'issuer_cnpj' => (string) ($nfe->CNPJ ?? ''),
            'issuer_name' => (string) ($nfe->xNome ?? ''),
            'issue_date' => $issueDate,
            'total_value' => (string) ($nfe->vNF ?? '0.00'),
            'schema_name' => $schema,
            'status' => $status,
        ]);
    }

    /**
     * Recuperar e salvar uma NF-e através de um NSU específico.
     */
    public function importNsu(int $nsu): bool
    {
        $tools = $this->createTools();

        // Consulta pontual pelo NSU.
        $response = $tools->sefazDistDFe(0, $nsu);

        $dom = new \DOMDocument();
        $dom->loadXML($response);

        $documents = $dom->getElementsByTagName('docZip');

        foreach ($documents as $document) {

            $documentNsu = $document->getAttribute('NSU');
            $schema = $document->getAttribute('schema');

            // Neste momento tratamos apenas resumo de NF-e.
            if ($schema !== 'resNFe_v1.01.xsd') {
                continue;
            }

            $compressed = base64_decode(
                trim($document->nodeValue),
                true
            );

            if ($compressed === false) {
                continue;
            }

            $xml = gzdecode($compressed);

            if ($xml === false) {
                continue;
            }

            return $this->saveNfeSummary(
                $xml,
                $documentNsu,
                $schema
            );
        }

        return false;
    }

    /**
     * Processar evento relacionado à NF-e.
     *
     * Neste momento, trata apenas:
     * 110111 - Cancelamento da NF-e.
     *
     * Outros tipos de evento são ignorados normalmente.
     *
     * @param string $xml XML do evento retornado pela SEFAZ
     * @return bool
     */
    private function processNfeEvent(string $xml): bool
    {
        $event = simplexml_load_string($xml);

        if ($event === false) {
            return false;
        }

        // Utilizar XPath para funcionar tanto com
        // resEvento quanto com procEventoNFe.
        $eventTypeNode = $event->xpath(
            '//*[local-name()="tpEvento"]'
        );

        $accessKeyNode = $event->xpath(
            '//*[local-name()="chNFe"]'
        );

        $eventDateNode = $event->xpath(
            '//*[local-name()="dhEvento"]'
        );

        $eventType = !empty($eventTypeNode)
            ? (string) $eventTypeNode[0]
            : '';

        // Ignorar eventos que não sejam cancelamento.
        if ($eventType !== '110111') {
            return true;
        }

        $accessKey = !empty($accessKeyNode)
            ? (string) $accessKeyNode[0]
            : '';

        if (strlen($accessKey) !== 44) {
            return false;
        }

        $cancelledAt = null;

        if (!empty($eventDateNode)) {

            $cancelledAt = (
                new \DateTimeImmutable(
                    (string) $eventDateNode[0]
                )
            )->format('Y-m-d H:i:s');
        }

        return $this->nfeRepository->updateNfeStatus(
            $accessKey,
            'cancelled',
            $cancelledAt
        );
    }
}
