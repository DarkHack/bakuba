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

namespace bakuba\kernel\time\timer;
//use bakuba\kernel\time\timer\iDwTimer as iDwTimer;

interface iDwTimer {
    /**
     * Get the interval after which this timer will execute, in seconds
     *
     * @return float
     */
    public function getInterval() : int;
    public function setInterval(int $intervale ) : void;

    /**
     * Get the callback that will be executed when this timer elapses
     *
     * @return callable
     */
    public function callback( ) : bool;
    public function getDwCallBack( ) : \bakuba\kernel\objects\DwCallBack;

    /**
     * Determine whether the time is periodic
     *
     * @return bool
     */
    public function isPeriodic() : bool;
}
