<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Anti-spam / Rate limit API
    |--------------------------------------------------------------------------
    |
    | Vượt quá max_attempts trong window_seconds → cảnh báo "Vui lòng thao tác đúng."
    | Tiếp tục spam (đủ max_warnings) → khóa block_seconds giây.
    |
    */

    'enabled' => env('ANTI_SPAM_ENABLED', true),

    /** Số request tối đa trong 1 cửa sổ thời gian (theo IP + user + route). */
    'max_attempts' => (int) env('ANTI_SPAM_MAX_ATTEMPTS', 20),

    /** Độ dài cửa sổ đếm request (giây). */
    'window_seconds' => (int) env('ANTI_SPAM_WINDOW_SECONDS', 10),

    /** Số lần bị cảnh báo trước khi khóa. */
    'max_warnings' => (int) env('ANTI_SPAM_MAX_WARNINGS', 2),

    /** Thời gian giữ bộ đếm cảnh báo (giây). */
    'warning_decay_seconds' => (int) env('ANTI_SPAM_WARNING_DECAY', 300),

    /** Thời gian khóa khi vẫn spam sau cảnh báo (giây). */
    'block_seconds' => (int) env('ANTI_SPAM_BLOCK_SECONDS', 60),

    /** Các path (relative sau /api) bỏ qua, ví dụ: v1/health */
    'except' => [
        'v1/health',
    ],

];
