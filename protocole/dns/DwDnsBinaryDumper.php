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
//use bakuba\kernel\protocole\dns\DwDnsBinaryDumper as DwDnsBinaryDumper;

final class DwDnsBinaryDumper
{
    /**
     * @param DwDnsMessage $dwDnsMessage
     * @return string
     */
    public function toBinary( DwDnsMessage $dwDnsMessage )
    {
        $data = '';

        $data .= $this->headerToBinary($dwDnsMessage);
        $data .= $this->questionToBinary($dwDnsMessage->questions);
        $data .= $this->recordsToBinary( $dwDnsMessage->answers );
        $data .= $this->recordsToBinary( $dwDnsMessage->authority );
        $data .= $this->recordsToBinary( $dwDnsMessage->additional );

        return $data;
    }

    /**
     * @param DwDnsMessage $dwDnsMessage
     * @return string
     */
    private function headerToBinary( DwDnsMessage $dwDnsMessage )
    {
        $data = '';

        $data .= pack( 'n' ,$dwDnsMessage->id );

        $flags = 0x00;
        $flags = ($flags << 1) | ($dwDnsMessage->qr ? 1 : 0);
        $flags = ($flags << 4) | $dwDnsMessage->opcode;
        $flags = ($flags << 1) | ($dwDnsMessage->aa ? 1 : 0);
        $flags = ($flags << 1) | ($dwDnsMessage->tc ? 1 : 0);
        $flags = ($flags << 1) | ($dwDnsMessage->rd ? 1 : 0);
        $flags = ($flags << 1) | ($dwDnsMessage->ra ? 1 : 0);
        $flags = ($flags << 3) | 0; // skip unused zero bit
        $flags = ($flags << 4) | $dwDnsMessage->rcode;

        $data .= pack('n', $flags);

        $data .= pack('n', is_array( $dwDnsMessage->questions ) ? count( $dwDnsMessage->questions ) : 0);
        $data .= pack('n', is_array( $dwDnsMessage->answers ) ? count( $dwDnsMessage->answers ) : 0 );
        $data .= pack('n', is_array( $dwDnsMessage->authority ) ? count( $dwDnsMessage->authority ) : 0 );
        $data .= pack('n', is_array( $dwDnsMessage->additional ) ? count( $dwDnsMessage->additional ) : 0 );

        return $data;
    }

    /**
     * @param Query[] $questions
     * @return string
     */
    private function questionToBinary( array $questions )
    {
        $data = '';

        foreach ( $questions as $question ) {
            $data .= $this->domainNameToBinary( $question->name );
            $type = is_null( $question->type ) ? -1 : $question->type;
            $class = is_null( $question->class ) ? -1 : $question->class;
            $data .= pack( 'n*' ,$type ,$class );
        }

        return $data;
    }

    /**
     * @param Record[] $records
     * @return string
     */
    private function recordsToBinary( array $records )
    {
        $data = '';
        $binary = '';
        foreach ( $records as $record ) {
			/* @var $record Record */
            switch ($record->type) {
                case DwDnsMessage::TYPE_A:
                case DwDnsMessage::TYPE_AAAA:
					if ( isset( $record->data ) && is_string( $record->data ) )
						$binary = \inet_pton( $record->data );
                    break;
                case DwDnsMessage::TYPE_CNAME:
                case DwDnsMessage::TYPE_NS:
                case DwDnsMessage::TYPE_PTR:
					if ( isset( $record->data ) && is_string( $record->data ) )
						$binary = $this->domainNameToBinary( $record->data );
                    break;
                case DwDnsMessage::TYPE_TXT:
                case DwDnsMessage::TYPE_SPF:
					if ( isset( $record->data ) && is_string( $record->data ) )
						$binary = $this->textsToBinary( $record->data );
                    break;
                case DwDnsMessage::TYPE_MX:
					if ( ! is_array( $record->data ) ) throw new \Exception( "no record data for ".\print_r( $record ,true )." in ".\print_r( $records ,true ) );
                    $binary = \pack(
                        'n',
                        $record->data['priority']
                    );
                    $binary .= $this->domainNameToBinary( $record->data['target'] );
                    break;
                case DwDnsMessage::TYPE_SRV:
					if ( ! is_array( $record->data ) ) throw new \Exception( "no record data for ".\print_r( $record ,true )." in ".\print_r( $records ,true ) );
                    $binary = \pack(
                        'n*',
                        $record->data['priority'],
                        $record->data['weight'],
                        $record->data['port']
                    );
                    $binary .= $this->domainNameToBinary($record->data['target']);
                    break;
                case DwDnsMessage::TYPE_SOA:
					if ( ! is_array( $record->data ) ) throw new \Exception( "no record data for ".\print_r( $record ,true )." in ".\print_r( $records ,true ) );
                    $binary  = $this->domainNameToBinary($record->data['mname']);
                    $binary .= $this->domainNameToBinary($record->data['rname']);
                    $binary .= \pack(
                        'N*',
                        $record->data['serial'],
                        $record->data['refresh'],
                        $record->data['retry'],
                        $record->data['expire'],
                        $record->data['minimum']
                    );
                    break;
                case DwDnsMessage::TYPE_CAA:
					if ( ! is_array( $record->data ) ) throw new \Exception( "no record data for ".\print_r( $record ,true )." in ".\print_r( $records ,true ) );
                    $binary = \pack(
                        'C*',
                        $record->data['flag'],
                        \strlen($record->data['tag'])
                    );
                    $binary .= $record->data['tag'];
                    $binary .= $record->data['value'];
                    break;
                case DwDnsMessage::TYPE_SSHFP:
					if ( ! is_array( $record->data ) ) throw new \Exception( "no record data for ".\print_r( $record ,true )." in ".\print_r( $records ,true ) );
                    $binary = \pack(
                        'CCH*',
                        $record->data['algorithm'],
                        $record->data['type'],
                        $record->data['fingerprint']
                    );
                    break;
                case DwDnsMessage::TYPE_OPT:
					if ( ! is_array( $record->data ) ) throw new \Exception( "no record data for ".\print_r( $record ,true )." in ".\print_r( $records ,true ) );
                    $binary = '';
                    foreach ($record->data as $opt => $value) {
                        if ($opt === DwDnsMessage::OPT_TCP_KEEPALIVE && $value !== null) {
                            $value = \pack('n', round($value * 10));
                        }
                        $binary .= \pack('n*', $opt, \strlen((string) $value)) . $value;
                    }
                    break;
                default:
					throw new \Exception( "no type for record ".\print_r( $record ,true )." in ".\print_r( $records ,true ) );
					// RDATA is already stored as binary value for unknown record types
					//$binary = $record->data;
            }
			
            $data .= $this->domainNameToBinary($record->name);
            $type = is_null( $record->type ) ? -1 : $record->type;
            $class = is_null( $record->class ) ? -1 : $record->class;
            $ttl = is_null( $record->ttl ) ? -1 : $record->ttl;
            $data .= \pack('nnNn', $type, $class, $ttl, is_null( $binary ) ? 0 : \strlen($binary));
            if ( ! is_null( $binary ) )
				$data .= $binary;
        }

        return $data;
    }

    /**
     * @param string[] $texts
     * @return string
     */
    private function textsToBinary(array $texts)
    {
        $data = '';
        foreach ($texts as $text) {
            $data .= \chr(\strlen($text)) . $text;
        }
        return $data;
    }

    /**
     * @param string $host
     * @return string
     */
    private function domainNameToBinary($host)
    {
        if ($host === '') {
            return "\0";
        }

        // break up domain name at each dot that is not preceeded by a backslash (escaped notation)
        return $this->textsToBinary(
            \array_map(
                'stripcslashes',
                \preg_split(
                    '/(?<!\\\\)\./',
                    $host . '.'
                )
            )
        );
    }
}
