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
            // XML COMPLETO / PROTOCOLADO DA NF-e
            // --------------------------------------------------

            if (
                str_starts_with(
                    $schema,
                    'procNFe_'
                )
            ) {

                $saved =
                    $this->saveNfeFullXml(
                        $xml,
                        $nsu,
                        $schema
                    );


                if (!$saved) {

                    throw new \RuntimeException(
                        "Não foi possível salvar o XML completo da NF-e do NSU {$nsu}."
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
     * Registrar Ciência da Emissão de uma NF-e.
     *
     * Evento:
     * 210210 - Ciência da Emissão
     *
     * Este evento NÃO confirma o recebimento da mercadoria.
     * Apenas informa à SEFAZ que o destinatário tomou
     * ciência da existência da NF-e.
     *
     * @param string $accessKey Chave de acesso da NF-e
     * @return array
     */
    public function manifestAwareness(
        string $accessKey
    ): array {

        /*
        * Validar chave de acesso.
        */
        if (
            !preg_match(
                '/^\d{44}$/',
                $accessKey
            )
        ) {

            return [
                'success' => false,
                'registered' => false,
                'already_registered' => false,
                'cStat' => null,
                'message' => 'Chave de acesso da NF-e inválida.',
            ];
        }


        /*
        * Criar comunicação com a SEFAZ.
        */
        $tools =
            $this->createTools();


        /*
        * Registrar Ciência da Emissão.
        *
        * 210210 = Ciência da Emissão
        * justificativa = não necessária
        * sequência = 1
        */
        $response =
            $tools->sefazManifesta(
                $accessKey,
                '210210',
                '',
                1
            );


        /*
        * Padronizar retorno da SEFAZ.
        */
        $standardize =
            new Standardize();


        $result =
            $standardize->toStd(
                $response
            );


        /*
        * O retorno externo normalmente possui:
        *
        * cStat = 128
        * Lote de Evento Processado
        *
        * Mas o resultado que realmente interessa
        * está dentro de:
        *
        * retEvento -> infEvento -> cStat
        */
        $eventInfo =
            $result->retEvento
            ->infEvento
            ?? null;


        $eventCStat =
            isset($eventInfo->cStat)
            ? (string) $eventInfo->cStat
            : null;


        $eventMessage =
            isset($eventInfo->xMotivo)
            ? (string) $eventInfo->xMotivo
            : (
                isset($result->xMotivo)
                ? (string) $result->xMotivo
                : null
            );

        $protocol =
            isset($eventInfo->nProt)
            ? (string) $eventInfo->nProt
            : null;


        $registeredAt =
            null;


        if (
            isset($eventInfo->dhRegEvento)
            &&
            !empty((string) $eventInfo->dhRegEvento)
        ) {

            try {

                $registeredAt =
                    (
                        new \DateTimeImmutable(
                            (string) $eventInfo->dhRegEvento
                        )
                    )->format(
                        'Y-m-d H:i:s'
                    );
            } catch (\Throwable) {

                $registeredAt =
                    null;
            }
        }


        /*
        * 135:
        * Evento registrado e vinculado à NF-e.
        *
        * 573:
        * Duplicidade de Evento.
        *
        * Para o nosso fluxo, 573 significa que
        * a Ciência provavelmente já havia sido
        * registrada anteriormente e não devemos
        * tentar registrá-la novamente.
        */
        $success =
            $eventCStat === '135';


        $alreadyRegistered =
            $eventCStat === '573';


        return [
            'success' =>
            $success,

            'registered' =>
            $success
                || $alreadyRegistered,

            'already_registered' =>
            $alreadyRegistered,

            'cStat' =>
            $eventCStat,

            'message' =>
            $eventMessage,

            'batch_cStat' =>
            isset($result->cStat)
                ? (string) $result->cStat
                : null,

            'protocol' =>
            $protocol,

            'registered_at' =>
            $registeredAt,
        ];
    }

    /**
     * Recuperar o XML completo de uma NF-e pela chave de acesso.
     *
     * Esta consulta usa consChNFe através do sefazDownload().
     *
     * IMPORTANTE:
     * - deve ser usada apenas de forma pontual;
     * - não deve ser executada em loop para várias NF-e;
     * - consChNFe e consNSU estão sujeitos às regras de consumo
     *   indevido do NFeDistribuicaoDFe.
     *
     * @param string $accessKey Chave de acesso com 44 dígitos
     * @return array
     */
    public function downloadByAccessKey(
        string $accessKey
    ): array {

        /*
         * Validar chave.
         */
        if (
            !preg_match(
                '/^\d{44}$/',
                $accessKey
            )
        ) {

            return [
                'success' => false,
                'blocked' => false,
                'has_xml' => false,
                'cStat' => null,
                'message' => 'Chave de acesso da NF-e inválida.',
                'schema' => null,
            ];
        }


        /*
         * Consulta PONTUAL pela chave de acesso.
         *
         * O NFePHP gera consChNFe neste método.
         * Não existe repetição automática aqui.
         */
        $tools =
            $this->createTools();


        $response =
            $tools->sefazDownload(
                $accessKey
            );


        /*
         * Ler cStat/xMotivo antes de processar docZip.
         */
        $standardize =
            new Standardize();


        $result =
            $standardize->toStd(
                $response
            );


        $cStat =
            isset($result->cStat)
            ? (string) $result->cStat
            : null;


        $message =
            isset($result->xMotivo)
            ? (string) $result->xMotivo
            : null;


        /*
         * 656 = Consumo indevido.
         *
         * Neste caso o método apenas informa o bloqueio.
         * Não existe retry automático.
         */
        if ($cStat === '656') {

            return [
                'success' => false,
                'blocked' => true,
                'has_xml' => false,
                'cStat' => $cStat,
                'message' => $message
                    ?? 'Consumo indevido informado pela SEFAZ.',
                'schema' => null,
            ];
        }


        /*
         * 138 = Documento localizado.
         *
         * Outros retornos não devem ser interpretados
         * como XML completo disponível.
         */
        if ($cStat !== '138') {

            return [
                'success' => false,
                'blocked' => false,
                'has_xml' => false,
                'cStat' => $cStat,
                'message' => $message
                    ?? 'Documento não localizado pela SEFAZ.',
                'schema' => null,
            ];
        }


        /*
         * Processar os documentos retornados.
         */
        $dom =
            new \DOMDocument();


        if (
            !$dom->loadXML(
                $response,
                LIBXML_NONET
            )
        ) {

            return [
                'success' => false,
                'blocked' => false,
                'has_xml' => false,
                'cStat' => $cStat,
                'message' => 'Não foi possível interpretar o retorno da SEFAZ.',
                'schema' => null,
            ];
        }


        $documents =
            $dom->getElementsByTagName(
                'docZip'
            );


        foreach ($documents as $document) {

            $nsu =
                $document->getAttribute(
                    'NSU'
                );


            $schema =
                $document->getAttribute(
                    'schema'
                );


            $compressed =
                base64_decode(
                    trim(
                        $document->nodeValue
                    ),
                    true
                );


            if ($compressed === false) {
                continue;
            }


            $xml =
                gzdecode(
                    $compressed
                );


            if ($xml === false) {
                continue;
            }


            /*
             * Queremos especificamente o XML
             * completo/protocolado da NF-e.
             */
            if (
                str_starts_with(
                    $schema,
                    'procNFe_'
                )
            ) {

                $saved =
                    $this->saveNfeFullXml(
                        $xml,
                        $nsu,
                        $schema
                    );


                return [
                    'success' => $saved,
                    'blocked' => false,
                    'has_xml' => $saved,
                    'cStat' => $cStat,
                    'message' => $saved
                        ? 'XML completo localizado e salvo.'
                        : 'O XML foi localizado, mas não pôde ser salvo.',
                    'schema' => $schema,
                ];
            }
        }


        /*
         * A SEFAZ localizou um documento, porém ainda
         * não retornou procNFe. Pode ter retornado
         * somente resumo/evento.
         */
        return [
            'success' => false,
            'blocked' => false,
            'has_xml' => false,
            'cStat' => $cStat,
            'message' => $message
                ?? 'Documento localizado, mas o XML completo ainda não está disponível.',
            'schema' => null,
        ];
    }


    /**
     * Recuperar e salvar um documento através de um NSU específico.
     *
     * Pode tratar:
     * - resNFe: resumo da NF-e;
     * - procNFe: XML completo/protocolado.
     */
    public function importNsu(int $nsu): bool
    {
        $tools =
            $this->createTools();


        $response =
            $tools->sefazDistDFe(
                0,
                $nsu
            );


        $dom =
            new \DOMDocument();


        if (!$dom->loadXML($response)) {
            return false;
        }


        $documents =
            $dom->getElementsByTagName(
                'docZip'
            );


        foreach ($documents as $document) {

            $documentNsu =
                $document->getAttribute(
                    'NSU'
                );


            $schema =
                $document->getAttribute(
                    'schema'
                );


            $compressed =
                base64_decode(
                    trim(
                        $document->nodeValue
                    ),
                    true
                );


            if ($compressed === false) {
                continue;
            }


            $xml =
                gzdecode(
                    $compressed
                );


            if ($xml === false) {
                continue;
            }


            if (
                $schema ===
                'resNFe_v1.01.xsd'
            ) {

                return $this->saveNfeSummary(
                    $xml,
                    $documentNsu,
                    $schema
                );
            }


            if (
                str_starts_with(
                    $schema,
                    'procNFe_'
                )
            ) {

                return $this->saveNfeFullXml(
                    $xml,
                    $documentNsu,
                    $schema
                );
            }
        }


        return false;
    }


    /**
     * Processar e salvar o XML completo/protocolado da NF-e.
     *
     * O XML é preservado exatamente como foi recebido da SEFAZ.
     */
    private function saveNfeFullXml(
        string $xml,
        string $nsu,
        string $schema
    ): bool {

        $dom =
            new \DOMDocument();


        if (
            !$dom->loadXML(
                $xml,
                LIBXML_NONET
            )
        ) {

            return false;
        }


        $xpath =
            new \DOMXPath(
                $dom
            );


        $idNode =
            $xpath->query(
                '//*[local-name()="infNFe"]/@Id'
            )
            ?->item(0);


        $accessKey =
            '';


        if ($idNode) {

            $accessKey =
                preg_replace(
                    '/^NFe/',
                    '',
                    trim(
                        $idNode->nodeValue
                    )
                )
                ?? '';
        }


        if (
            strlen($accessKey)
            !== 44
        ) {

            $keyNode =
                $xpath->query(
                    '//*[local-name()="protNFe"]'
                        . '//*[local-name()="chNFe"]'
                )
                ?->item(0);


            $accessKey =
                $keyNode
                ? trim(
                    $keyNode->nodeValue
                )
                : '';
        }


        if (
            !preg_match(
                '/^\d{44}$/',
                $accessKey
            )
        ) {

            return false;
        }


        $value =
            static function (
                \DOMXPath $xpath,
                string $query
            ): ?string {

                $node =
                    $xpath->query(
                        $query
                    )
                    ?->item(0);


                if (!$node) {
                    return null;
                }


                $result =
                    trim(
                        $node->nodeValue
                    );


                return
                    $result !== ''
                    ? $result
                    : null;
            };


        $number =
            $value(
                $xpath,
                '//*[local-name()="infNFe"]'
                    . '/*[local-name()="ide"]'
                    . '/*[local-name()="nNF"]'
            );


        $series =
            $value(
                $xpath,
                '//*[local-name()="infNFe"]'
                    . '/*[local-name()="ide"]'
                    . '/*[local-name()="serie"]'
            );


        $issuerCnpj =
            $value(
                $xpath,
                '//*[local-name()="infNFe"]'
                    . '/*[local-name()="emit"]'
                    . '/*[local-name()="CNPJ"]'
            );


        $issuerName =
            $value(
                $xpath,
                '//*[local-name()="infNFe"]'
                    . '/*[local-name()="emit"]'
                    . '/*[local-name()="xNome"]'
            );


        $totalValue =
            $value(
                $xpath,
                '//*[local-name()="infNFe"]'
                    . '/*[local-name()="total"]'
                    . '/*[local-name()="ICMSTot"]'
                    . '/*[local-name()="vNF"]'
            );


        $issueDateRaw =
            $value(
                $xpath,
                '//*[local-name()="infNFe"]'
                    . '/*[local-name()="ide"]'
                    . '/*[local-name()="dhEmi"]'
            );


        $issueDate =
            null;


        if ($issueDateRaw) {

            try {

                $issueDate =
                    (
                        new \DateTimeImmutable(
                            $issueDateRaw
                        )
                    )->format(
                        'Y-m-d H:i:s'
                    );
            } catch (\Throwable) {

                $issueDate =
                    null;
            }
        }


        $protocolStatus =
            $value(
                $xpath,
                '//*[local-name()="protNFe"]'
                    . '//*[local-name()="cStat"]'
            );


        $status =
            $protocolStatus === '100'
            ? 'authorized'
            : 'unknown';


        return $this->nfeRepository
            ->saveFullNfe([
                'nsu' =>
                $nsu,

                'access_key' =>
                $accessKey,

                'nfe_number' =>
                $number,

                'series' =>
                $series,

                'issuer_cnpj' =>
                $issuerCnpj,

                'issuer_name' =>
                $issuerName,

                'issue_date' =>
                $issueDate,

                'total_value' =>
                $totalValue,

                'status' =>
                $status,

                'xml_content' =>
                $xml,

                'xml_schema_name' =>
                $schema,
            ]);
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
