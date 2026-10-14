<?php

return [
    // Review đợt 2: preserve Meeting routes and records, but keep the module
    // out of the visible product until its next release is approved.
    'meetings_visible' => env('FEATURE_MEETINGS_VISIBLE', false),
];
