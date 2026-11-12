<?php

return [
    'earthquake_threshold_g' => (float) env('IOT_EARTHQUAKE_THRESHOLD_G', 0.3),
    'warning_threshold_ratio' => (float) env('IOT_WARNING_THRESHOLD_RATIO', 0.5),
    'device_timeout_seconds' => (int) env('IOT_DEVICE_TIMEOUT_SECONDS', 15),
];
