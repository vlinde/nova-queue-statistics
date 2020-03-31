<?php

namespace Vlinde\NovaQueueStatistics\Classes;

interface ThrottledNotification
{
    public function throttleDecayMinutes(): int;

    public function throttleKeyId();
}
