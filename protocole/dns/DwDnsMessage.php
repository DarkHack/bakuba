<?php
/**
 * Copyright 2011-2026 Nicolas "DarkHack" Chapuis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace bakuba\kernel\protocole\dns;
//use bakuba\kernel\protocole\dns\DwDnsMessage as DwDnsMessage;

use bakuba\kernel\protocole\dns\DwDnsQuery as DwDnsQuery;

/**
 * This class represents an outgoing query message or an incoming response message
 *
 * @link https://tools.ietf.org/html/rfc1035#section-4.1.1
 * @inspired React\Dns\Model\Message
 * @link https://github.com/reactphp/dns/blob/3.x/src/Model/Message.php
 */
final class DwDnsMessage
{
    const TYPE_A = 1;
    const TYPE_NS = 2;
    const TYPE_CNAME = 5;
    const TYPE_SOA = 6;
    const TYPE_PTR = 12;
    const TYPE_MX = 15;
    const TYPE_TXT = 16;
    const TYPE_AAAA = 28;
    const TYPE_SRV = 33;
    const TYPE_SSHFP = 44;

    /**
     * pseudo-type for EDNS0
     *
     * These are included in the additional section and usually not in answer section.
     * Defined in [RFC 6891](https://tools.ietf.org/html/rfc6891) (or older
     * [RFC 2671](https://tools.ietf.org/html/rfc2671)).
     *
     * The OPT record uses the "class" field to store the maximum size.
     *
     * The OPT record uses the "ttl" field to store additional flags.
     */
    const TYPE_OPT = 41;

    /**
     * Sender Policy Framework (SPF) had a dedicated SPF type which has been
     * deprecated in favor of reusing the existing TXT type.
     *
     * @deprecated https://datatracker.ietf.org/doc/html/rfc7208#section-3.1
     * @see self::TYPE_TXT
     */
    const TYPE_SPF = 99;

    const TYPE_ANY = 255;
    const TYPE_CAA = 257;
	// \bakuba\kernel\protocole\dns\DwDnsMessage::parseStrType( 'AAAA' );
	public static function parseStrType( string $strType = 'A') : ?int {
		$type=null;
		switch( strtolower( $strType ) ) {
			case 'a' :
				$type=self::TYPE_A;
				break;
			case 'ns' :
				$type=self::TYPE_NS;
				break;
			case 'cname' :
				$type=self::TYPE_CNAME;
				break;
			case 'soa' :
				$type=self::TYPE_SOA;
				break;
			case 'ptr' :
				$type=self::TYPE_PTR;
				break;
			case 'mx' :
				$type=self::TYPE_MX;
				break;
			case 'txt' :
				$type=self::TYPE_TXT;
				break;
			case 'aaaa' :
				$type=self::TYPE_AAAA;
				break;
			case 'srv' :
				$type=self::TYPE_SRV;
				break;
			case 'sshfp' :
				$type=self::TYPE_SSHFP;
				break;
			case 'opt' :
				$type=self::TYPE_OPT;
				break;
			case 'spf' :
				$type=self::TYPE_SPF;
				break;
			case 'any' :
				$type=self::TYPE_ANY;
				break;
			case 'caa' :
				$type=self::TYPE_CAA;
				break;
			default :
				break;
		}
		return $type;
	}
    const CLASS_IN = 1;

    const OPCODE_QUERY = 0;
    const OPCODE_IQUERY = 1; // inverse query
    const OPCODE_STATUS = 2;

    const RCODE_OK = 0;
    const RCODE_FORMAT_ERROR = 1;
    const RCODE_SERVER_FAILURE = 2;
    const RCODE_NAME_ERROR = 3;
    const RCODE_NOT_IMPLEMENTED = 4;
    const RCODE_REFUSED = 5;

    /**
     * The edns-tcp-keepalive EDNS0 Option
     *
     * Option value contains a `?float` with timeout in seconds (in 0.1s steps)
     * for DNS response or `null` for DNS query.
     *
     * @link https://tools.ietf.org/html/rfc7828
     */
    const OPT_TCP_KEEPALIVE = 11;

    /**
     * The EDNS(0) Padding Option
     *
     * Option value contains a `string` with binary data (usually variable
     * number of null bytes)
     *
     * @link https://tools.ietf.org/html/rfc7830
     */
    const OPT_PADDING = 12;

    /**
     * Creates a new request message for the given query
     *
     * @param DwDnsQuery $dwQuery
     * @return self
     */
    public static function createRequestForQuery( DwDnsQuery $dwQuery)
    {
        $request = new DwDnsMessage();
        $request->id = self::generateId();
        $request->rd = true;
        $request->questions[] = $dwQuery;

        return $request;
    }

    /**
     * Creates a new response message for the given query with the given answer records
     *
     * @param DwDnsQuery    $dwQuery
     * @param Record[] $answers
     * @return self
     */
    public static function createResponseWithAnswersForQuery( DwDnsQuery $dwQuery, array $answers )
    {
        $response = new DwDnsMessage();
        $response->id = self::generateId();
        $response->qr = true;
        $response->rd = true;

        $response->questions[] = $dwQuery;

        foreach ($answers as $record) {
            $response->answers[] = $record;
        }

        return $response;
    }

    /**
     * generates a random 16 bit message ID
     *
     * This uses a CSPRNG so that an outside attacker that is sending spoofed
     * DNS response messages can not guess the message ID to avoid possible
     * cache poisoning attacks.
     *
     * @return int
     * @see self::getId()
     * @codeCoverageIgnore
     */
    private static function generateId()
    {
        return random_int(0, 0xffff);
    }

    /**
     * The 16 bit message ID
     *
     * The response message ID has to match the request message ID. This allows
     * the receiver to verify this is the correct response message. An outside
     * attacker may try to inject fake responses by "guessing" the message ID,
     * so this should use a proper CSPRNG to avoid possible cache poisoning.
     *
     * @var int 16 bit message ID
     * @see self::generateId()
     */
    public $id = 0;

    /**
     * @var bool Query/Response flag, query=false or response=true
     */
    public $qr = false;

    /**
     * @var int specifies the kind of query (4 bit), see self::OPCODE_* constants
     * @see self::OPCODE_QUERY
     */
    public $opcode = self::OPCODE_QUERY;

    /**
     *
     * @var bool Authoritative Answer
     */
    public $aa = false;

    /**
     * @var bool TrunCation
     */
    public $tc = false;

    /**
     * @var bool Recursion Desired
     */
    public $rd = false;

    /**
     * @var bool Recursion Available
     */
    public $ra = false;

    /**
     * @var int response code (4 bit), see self::RCODE_* constants
     * @see self::RCODE_OK
     */
    public $rcode = DwDnsMessage::RCODE_OK;

    /**
     * An array of Query objects
     *
     * ```php
     * $questions = [
     *     new DwDnsQuery(
     *         'reactphp.org',
     *         DwDnsMessage::TYPE_A,
     *         DwDnsMessage::CLASS_IN
     *     )
     * ];
     * ```
     *
     * @var Query[]
     */
    public $questions = [];

    /**
     * @var Record[]
     */
    public $answers = [];

    /**
     * @var Record[]
     */
    public $authority = [];

    /**
     * @var Record[]
     */
    public $additional = [];
}
