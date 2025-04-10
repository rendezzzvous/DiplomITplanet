<?php
namespace Bav;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\SystemException;

class Fns
{
    const TOKEN = '';
    const AUTH_SERVICE_URL = 'https://openapi.nalog.ru:8090/open-api/AuthService/0.1?wsdl';
    const KKT_SERVICE_URL = 'https://openapi.nalog.ru:8090/open-api/ais3/KktService/0.1?wsdl';

    private $tempToken;
    private $words;

    function __construct($words = array())
    {
        $this->words = $words;
        try {
            $this->tempToken = $this->getTempToken();
        } catch (\Exception $e) {
            $this->tempToken = false;
        }
    }

    private function query($url, $postFields, $headers = [])
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            /*CURLOPT_VERBOSE => 1,
            CURLOPT_STDERR => $f,*/
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array_merge(["Content-Type: text/xml"], $headers),
        ));
        $response = curl_exec($ch);
        $curl_info = curl_getinfo($ch);
        //if ($_REQUEST['ttt']) echo '<pre>'.print_r($curl_info, true).'</pre>';
        //if ($_REQUEST['ttt']) echo '<pre>'.print_r($response, true).'</pre>';
        //$this->log('fns_request.log', $postFields."\n".$response);
        //echo "\n\n".$response."\n\n";

        try {
            if (!$response) throw new SystemException('Empty response: status code ' . $curl_info['http_code'] . ', url ' . $url);

            $dom = new \DOMDocument();
            $dom->loadXML($response);

            if ($fault = $this->getTag($dom, 'faultstring')) {
                $is_timeout = $this->getTag($dom, 'MessageNotFoundFault') !== false;
                $err_code = $is_timeout ? 400 : 500;
                //file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/fns_rq', $response . "\n\n\n", FILE_APPEND);
                throw new SystemException('Request error: status code ' . $err_code . '. ' . $fault, $err_code);
            } elseif ($curl_info['http_code'] != 200) {
                throw new SystemException('Request error: status code ' . $curl_info['http_code']);
            }
        } catch (\Exception $e) {
            $this->log('fns_errors.log', $e->getMessage());
            throw $e;
        }

        return $dom;
    }

    private function log($file, $message)
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $file, date('d.m.Y H:i:s') . "\t" . $message . "\n", FILE_APPEND);
    }

    private function getTag($dom, $tag)
    {
        if (!$dom) return false;
        $val = false;
        foreach ($dom->getElementsByTagName($tag) as $element) {
            $val = $element->nodeValue;
        }
        return $val;
    }

    private function getTempToken()
    {
        $fld = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ns=\"urn://x-artefacts-gnivc-ru/inplat/servin/OpenApiMessageConsumerService/types/1.0\">
<soapenv:Header/>
<soapenv:Body>
<ns:GetMessageRequest>
<ns:Message>\n<tns:AuthRequest xmlns:tns=\"urn://x-artefacts-gnivc-ru/ais3/kkt/AuthService/types/1.0\">
<tns:AuthAppInfo>
<tns:MasterToken>" . self::TOKEN . "</tns:MasterToken>
</tns:AuthAppInfo>
</tns:AuthRequest>
</ns:Message>
</ns:GetMessageRequest>
</soapenv:Body>
</soapenv:Envelope>";
        $dom = $this->query(self::AUTH_SERVICE_URL, $fld);
        return $this->getTag($dom, 'Token');
    }


    public function getMessageInfo($mid)
    {
        if (!$this->tempToken) throw new SystemException('Auth error');

        $headers = array(
            "FNS-OpenApi-Token: " . $this->tempToken,
            "FNS-OpenApi-UserToken: " . self::TOKEN
        );
        $fld = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ns=\"urn://x-artefacts-gnivc-ru/inplat/servin/OpenApiAsyncMessageConsumerService/types/1.0\">
   <soapenv:Header/>
   <soapenv:Body>
      <ns:GetMessageRequest>
         <ns:MessageId>" . $mid . "</ns:MessageId>
      </ns:GetMessageRequest>
   </soapenv:Body>
</soapenv:Envelope>";

        try {
            $dom = $this->query(self::KKT_SERVICE_URL, $fld, $headers);
            $code = $this->getTag($dom, 'Code');
            $status = $this->getTag($dom, 'ProcessingStatus');
            $message = $this->getTag($dom, 'Message');
            $ticket = json_decode($this->getTag($dom, 'Ticket'), true);

            if ($status == 'COMPLETED' && $code == 200) {
                $prod = array();
                $allProd = array();
                if (is_array($ticket['content']['items'])) {
                    foreach ($ticket['content']['items'] as $item) {
                        $n = mb_convert_encoding($item['name'], 'UTF-8', 'UTF-8');
                        $allProd[] = $n;
                        foreach ($this->words as $word) {
                            if (mb_strpos(mb_strtolower($n), mb_strtolower($word)) !== false || strpos(strtolower($n), strtolower($word)) !== false) $prod[] = $n;
                            break;
                        }
                    }
                }
                if (count($prod) > 0) {
                    $ret = array(
                        'STATUS' => 'VERIFIED',
                        'TEXT' => implode(", \n", $prod)
                    );
                } else {
                    $ret = array(
                        'STATUS' => 'NOT VERIFIED',
                        'TEXT' => "Товар не найден в чеке\n [" . implode(", \n", $allProd) . "]"
                    );
                    //file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/items.txt', print_r($allProd, true), FILE_APPEND);
                }
            } elseif ($status == 'COMPLETED' && $code != 200) {
                $ret = array(
                    'STATUS' => 'ERROR',
                    'TEXT' => $message
                );
            } elseif ($status == 'PROCESSING') {
                $ret = array(
                    'STATUS' => 'PROCESSING',
                    'TEXT' => $mid
                );
            } else {
                $ret = array(
                    'STATUS' => 'UNKNOWN',
                    'TEXT' => 'CODE=' . $code . ' STATUS=' . $status . ' MESSAGE=' . $message
                );
            }
        } catch (\Exception $e) {
            $ret = array(
                'STATUS' => $e->getCode() == 400 ? 'RETRY' : 'ERROR',
                'TEXT' => $e->getMessage()
            );
        }

        return $ret;
    }

    public function getTicketInfo($ticket)
    {
        try {
            if (!$this->tempToken) throw new SystemException('Auth error');

            $headers = array(
                "FNS-OpenApi-Token: " . $this->tempToken,
                "FNS-OpenApi-UserToken: " . self::TOKEN
            );
            $fld = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ns=\"urn://x-artefacts-gnivc-ru/inplat/servin/OpenApiAsyncMessageConsumerService/types/1.0\">
    <soapenv:Header/>
    <soapenv:Body>
        <ns:SendMessageRequest>
            <ns:Message>
                <tns:GetTicketRequest xmlns:tns=\"urn://x-artefacts-gnivc-ru/ais3/kkt/KktTicketService/types/1.0\">
                    <tns:GetTicketInfo>
                        <tns:Sum>" . floor(floatval($ticket['SUM']) * 100) . "</tns:Sum>
                        <tns:Date>" . date('Y-m-d\TH:i:s', strtotime($ticket['DATE'])) . "</tns:Date>
                        <tns:Fn>" . preg_replace('/[^0-9]+/', '', $ticket['FN']) . "</tns:Fn>
                        <tns:TypeOperation>1</tns:TypeOperation>
                        <tns:FiscalDocumentId>" . preg_replace('/[^0-9]+/', '', $ticket['FD']) . "</tns:FiscalDocumentId>
                        <tns:FiscalSign>" . preg_replace('/[^0-9]+/', '', $ticket['FP']) . "</tns:FiscalSign>
                        <tns:RawData>true</tns:RawData>
                    </tns:GetTicketInfo>
                </tns:GetTicketRequest>
            </ns:Message>
        </ns:SendMessageRequest>
    </soapenv:Body>
</soapenv:Envelope>";

            $dom = $this->query(self::KKT_SERVICE_URL, $fld, $headers);
            $mid = $this->getTag($dom, 'MessageId');
            if (!$mid) throw new SystemException('Message query error');
            $ret = $this->getMessageInfo($mid);

        } catch (\Exception $e) {
            $ret = array(
                'STATUS' => $e->getCode() == 400 ? 'RETRY' : 'ERROR',
                'TEXT' => $e->getMessage()
            );
        }
        return $ret;
    }

}
