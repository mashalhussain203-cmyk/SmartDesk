<?php

return [
    // Must be at least 32 random characters. Set the SAME secret on both Railway services.
    'secret' => env('LIVE_RECORDER_SECRET', ''),
];
