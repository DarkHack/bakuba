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

namespace bakuba\kernel\objects;
//use bakuba\kernel\objects\DwPromiseCallBack as DwPromiseCallBack;

use bakuba\kernel\time\DwChronoStartStop as DwChronoStartStop;
use darkphp\string\DwString as DwString;

// PerPLexity Production :
class DwPromiseCallBack extends DwCallBack {
    protected $state = 'pending';
    protected $onFulfilled = [];
    protected $onRejected = [];
    public function then( DwCallBack $onFulfilled, ?DwCallBack $onRejected = null) {
        $this->onFulfilled[] = $onFulfilled;
        if ($onRejected) $this->onRejected[] = $onRejected;
        // En fonction de l'état, exécuter ou empiler
    }
    public function resolve($value) {
        $this->state = 'fulfilled';
        foreach ($this->onFulfilled as $cb) $cb($value);
    }
    public function reject($reason) {
        $this->state = 'rejected';
        foreach ($this->onRejected as $cb) $cb($reason);
    }
    // etc.
}

?>