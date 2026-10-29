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
//use bakuba\kernel\protocole\dns\DwDnsParser as DwDnsParser;

//use InvalidArgumentException;

/**
 * DNS protocol parser
 *
 * Obsolete and uncommon types and classes are not implemented.
 */
final class DwDnsParser
{
    /**
     * Parses the given raw binary message into a Message object
     *
     * @param string $data
     * @throws InvalidArgumentException
     * @return Message
     */
    public function parseMessage($data)
    {
        $dwMessage = $this->parse($data, 0);
        if ($dwMessage === null) {
            throw new Exception('Unable to parse binary message');
        }

        return $dwMessage;
    }

    /**
     * @param string $data
     * @param int    $consumed
     * @return ?Message
     */
    private function parse($data, $consumed)
    {
        if (!isset($data[12 - 1])) {
            return null;
        }

        list($id, $fields, $qdCount, $anCount, $nsCount, $arCount) = array_values(unpack('n*', substr($data, 0, 12)));

        $dwMessage = new DwDnsMessage();
        $dwMessage->id = $id;
        $dwMessage->rcode = $fields & 0xf;
        $dwMessage->ra = (($fields >> 7) & 1) === 1;
        $dwMessage->rd = (($fields >> 8) & 1) === 1;
        $dwMessage->tc = (($fields >> 9) & 1) === 1;
        $dwMessage->aa = (($fields >> 10) & 1) === 1;
        $dwMessage->opcode = ($fields >> 11) & 0xf;
        $dwMessage->qr = (($fields >> 15) & 1) === 1;
        $consumed += 12;

        // parse all questions
        for ($i = $qdCount; $i > 0; --$i) {
            list($question, $consumed) = $this->parseQuestion($data, $consumed);
            if ($question === null) {
                return null;
            } else {
                $dwMessage->questions[] = $question;
            }
        }

        // parse all answer records
        for ($i = $anCount; $i > 0; --$i) {
            list($record, $consumed) = $this->parseRecord($data, $consumed);
            if ($record === null) {
                return null;
            } else {
                $dwMessage->answers[] = $record;
            }
        }

        // parse all authority records
        for ($i = $nsCount; $i > 0; --$i) {
            list($record, $consumed) = $this->parseRecord($data, $consumed);
            if ($record === null) {
                return null;
            } else {
                $dwMessage->authority[] = $record;
            }
        }

        // parse all additional records
        for ($i = $arCount; $i > 0; --$i) {
            list($record, $consumed) = $this->parseRecord($data, $consumed);
            if ($record === null) {
                return null;
            } else {
                $dwMessage->additional[] = $record;
            }
        }

        return $dwMessage;
    }

    /**
     * @param string $data
     * @param int $consumed
     * @return array
     */
    private function parseQuestion($data, $consumed)
    {
        list($labels, $consumed) = $this->readLabels($data, $consumed);

        if ($labels === null || !isset($data[$consumed + 4 - 1])) {
            return [null, null];
        }

        list($type, $class) = array_values(unpack('n*', substr($data, $consumed, 4)));
        $consumed += 4;

        return [
            new DwDnsQuery(
                implode('.', $labels),
                $type,
                $class
            ),
            $consumed
        ];
    }

    /**
     * @param string $data
     * @param int $consumed
     * @return array An array with a parsed Record on success or array with null if data is invalid/incomplete
     */
    private function parseRecord($data, $consumed)
    {
        list($name, $consumed) = $this->readDomain($data, $consumed);

        if ($name === null || !isset($data[$consumed + 10 - 1])) {
            return [null, null];
        }

        list($type, $class) = array_values(unpack('n*', substr($data, $consumed, 4)));
        $consumed += 4;

        list($ttl) = array_values(unpack('N', substr($data, $consumed, 4)));
        $consumed += 4;

        // TTL is a UINT32 that must not have most significant bit set for BC reasons
        if ($ttl < 0 || $ttl >= 1 << 31) {
            $ttl = 0;
        }

        list($rdLength) = array_values(unpack('n', substr($data, $consumed, 2)));
        $consumed += 2;

        if (!isset($data[$consumed + $rdLength - 1])) {
            return [null, null];
        }

        $rdata = null;
        $expected = $consumed + $rdLength;

        if (DwDnsMessage::TYPE_A === $type) {
            if ($rdLength === 4) {
                $rdata = inet_ntop(substr($data, $consumed, $rdLength));
                $consumed += $rdLength;
            }
        } elseif (DwDnsMessage::TYPE_AAAA === $type) {
            if ($rdLength === 16) {
                $rdata = inet_ntop(substr($data, $consumed, $rdLength));
                $consumed += $rdLength;
            }
        } elseif (DwDnsMessage::TYPE_CNAME === $type || DwDnsMessage::TYPE_PTR === $type || DwDnsMessage::TYPE_NS === $type) {
            list($rdata, $consumed) = $this->readDomain($data, $consumed);
        } elseif (DwDnsMessage::TYPE_TXT === $type || DwDnsMessage::TYPE_SPF === $type) {
            $rdata = [];
            while ($consumed < $expected) {
                $len = ord($data[$consumed]);
                $rdata[] = (string)substr($data, $consumed + 1, $len);
                $consumed += $len + 1;
            }
        } elseif (DwDnsMessage::TYPE_MX === $type) {
            if ($rdLength > 2) {
                list($priority) = array_values(unpack('n', substr($data, $consumed, 2)));
                list($target, $consumed) = $this->readDomain($data, $consumed + 2);

                $rdata = [
                    'priority' => $priority,
                    'target' => $target
                ];
            }
        } elseif (DwDnsMessage::TYPE_SRV === $type) {
            if ($rdLength > 6) {
                list($priority, $weight, $port) = array_values(unpack('n*', substr($data, $consumed, 6)));
                list($target, $consumed) = $this->readDomain($data, $consumed + 6);

                $rdata = [
                    'priority' => $priority,
                    'weight' => $weight,
                    'port' => $port,
                    'target' => $target
                ];
            }
        } elseif (DwDnsMessage::TYPE_SSHFP === $type) {
            if ($rdLength > 2) {
                list($algorithm, $hash) = \array_values(\unpack('C*', \substr($data, $consumed, 2)));
                $fingerprint = \bin2hex(\substr($data, $consumed + 2, $rdLength - 2));
                $consumed += $rdLength;

                $rdata = [
                    'algorithm' => $algorithm,
                    'type' => $hash,
                    'fingerprint' => $fingerprint
                ];
            }
        } elseif (DwDnsMessage::TYPE_SOA === $type) {
            list($mname, $consumed) = $this->readDomain($data, $consumed);
            list($rname, $consumed) = $this->readDomain($data, $consumed);

            if ($mname !== null && $rname !== null && isset($data[$consumed + 20 - 1])) {
                list($serial, $refresh, $retry, $expire, $minimum) = array_values(unpack('N*', substr($data, $consumed, 20)));
                $consumed += 20;

                $rdata = [
                    'mname' => $mname,
                    'rname' => $rname,
                    'serial' => $serial,
                    'refresh' => $refresh,
                    'retry' => $retry,
                    'expire' => $expire,
                    'minimum' => $minimum
                ];
            }
        } elseif (DwDnsMessage::TYPE_OPT === $type) {
            $rdata = [];
            while (isset($data[$consumed + 4 - 1])) {
                list($code, $length) = array_values(unpack('n*', substr($data, $consumed, 4)));
                $value = (string) substr($data, $consumed + 4, $length);
                if ($code === DwDnsMessage::OPT_TCP_KEEPALIVE && $value === '') {
                    $value = null;
                } elseif ($code === DwDnsMessage::OPT_TCP_KEEPALIVE && $length === 2) {
                    list($value) = array_values(unpack('n', $value));
                    $value = round($value * 0.1, 1);
                } elseif ($code === DwDnsMessage::OPT_TCP_KEEPALIVE) {
                    break;
                }
                $rdata[$code] = $value;
                $consumed += 4 + $length;
            }
        } elseif (DwDnsMessage::TYPE_CAA === $type) {
            if ($rdLength > 3) {
                list($flag, $tagLength) = array_values(unpack('C*', substr($data, $consumed, 2)));

                if ($tagLength > 0 && $rdLength - 2 - $tagLength > 0) {
                    $tag = substr($data, $consumed + 2, $tagLength);
                    $value = substr($data, $consumed + 2 + $tagLength, $rdLength - 2 - $tagLength);
                    $consumed += $rdLength;

                    $rdata = [
                        'flag' => $flag,
                        'tag' => $tag,
                        'value' => $value
                    ];
                }
            }
        } else {
            // unknown types simply parse rdata as an opaque binary string
            $rdata = substr($data, $consumed, $rdLength);
            $consumed += $rdLength;
        }

        // ensure parsing record data consumes expact number of bytes indicated in record length
        if ($consumed !== $expected || $rdata === null) {
            return [null, null];
        }
		
        return [
            new DwDnsRecord( new DwDnsQuery( $name ,$type ,$class ) ,$ttl ,$rdata),
            $consumed
        ];
    }

    private function readDomain($data, $consumed)
    {
        list ($labels, $consumed) = $this->readLabels($data, $consumed);

        if ($labels === null) {
            return [null, null];
        }

        // use escaped notation for each label part, then join using dots
        return [
            \implode(
                '.',
                \array_map(
                    function ($label) {
                        return \addcslashes($label, "\0..\40.\177");
                    },
                    $labels
                )
            ),
            $consumed
        ];
    }

    /**
     * @param string $data
     * @param int    $consumed
     * @param int    $compressionDepth maximum depth for compressed labels to avoid unreasonable recursion
     * @return array
     */
    private function readLabels($data, $consumed, $compressionDepth = 127)
    {
        $labels = [];

        while (true) {
            if (!isset($data[$consumed])) {
                return [null, null];
            }

            $length = \ord($data[$consumed]);

            // end of labels reached
            if ($length === 0) {
                $consumed += 1;
                break;
            }

            // first two bits set? this is a compressed label (14 bit pointer offset)
            if (($length & 0xc0) === 0xc0 && isset($data[$consumed + 1]) && $compressionDepth) {
                $offset = ($length & ~0xc0) << 8 | \ord($data[$consumed + 1]);
                if ($offset >= $consumed) {
                    return [null, null];
                }

                $consumed += 2;
                list($newLabels) = $this->readLabels($data, $offset, $compressionDepth - 1);

                if ($newLabels === null) {
                    return [null, null];
                }

                $labels = array_merge($labels, $newLabels);
                break;
            }

            // length MUST be 0-63 (6 bits only) and data has to be large enough
            if ($length & 0xc0 || !isset($data[$consumed + $length - 1])) {
                return [null, null];
            }

            $labels[] = substr($data, $consumed + 1, $length);
            $consumed += $length + 1;
        }

        return [$labels, $consumed];
    }
}
