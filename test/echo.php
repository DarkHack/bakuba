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

/*
function ctrl_handler(int $event)
{
    switch ($event) {
        case PHP_WINDOWS_EVENT_CTRL_C:
            fwrite( STDOUT, "You have pressed CTRL+C\n");
            break;
        case PHP_WINDOWS_EVENT_CTRL_BREAK:
            fwrite( STDOUT,  "You have pressed CTRL+BREAK\n");
            break;
    }
}

sapi_windows_set_ctrl_handler('ctrl_handler');
//*/
///*
while( ( $data = fread( STDIN, 1 ) ) !==FALSE ) {
	fwrite( STDOUT, $data );
}
//*/
/*
while( 1 ) {
	fwrite( STDOUT, "ping\r\n" );
}
//*/
?>