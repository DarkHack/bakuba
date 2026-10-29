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
//use bakuba\kernel\protocole\dns\DwDnsQuery as DwDnsQuery;

//use bakuba\kernel\protocole\dns\DwDnsMessage as DwDnsMessage;

/**
 * This class represents a single question in a query/response message
 *
 * It uses a structure similar to `\React\Dns\Message\Record`, but does not
 * contain fields for resulting TTL and resulting record data (IPs etc.).
 *
 * @link https://tools.ietf.org/html/rfc1035#section-4.1.2
 * @see \bakuba\kernel\protocole\dns\DwDnsRecord
 * @link https://github.com/reactphp/dns/blob/3.x/src/Query/Query.php
 */
final class DwDnsQuery
{
    /**
     * @var string query name, i.e. hostname to look up
     */
    public $name;

    /**
     * @var int query type (aka QTYPE), see DwDnsMessage::TYPE_* constants
     */
    public $type;

    /**
     * @var int query class (aka QCLASS), see DwDnsMessage::CLASS_IN constant
     */
    public $class;

    /**
     * @param string $name  query name, i.e. hostname to look up
     * @param int    $type  query type, see DwDnsMessage::TYPE_* constants
     * @param int    $class query class, see DwDnsMessage::CLASS_IN constant
     */
    public function __construct( $name ,$type ,$class )
    {
        $this->name = $name;
        $this->type = $type;
        $this->class = $class;
    }

    /**
     * Describes the hostname and query type/class for this query
     *
     * The output format is supposed to be human readable and is subject to change.
     * The format is inspired by RFC 3597 when handling unkown types/classes.
     *
     * @return string "example.com (A)" or "example.com (CLASS0 TYPE1234)"
     * @link https://tools.ietf.org/html/rfc3597
     */
    public function describe()
    {
        $class = $this->class !== DwDnsMessage::CLASS_IN ? 'CLASS' . $this->class . ' ' : '';

        $type = 'TYPE' . $this->type;
        $ref = new \ReflectionClass( DwDnsMessage::class );
        foreach ($ref->getConstants() as $name => $value) {
            if ($value === $this->type && \strpos($name, 'TYPE_') === 0) {
                $type = \substr($name, 5);
                break;
            }
        }

        return $this->name . ' (' . $class . $type . ')';
    }
}
