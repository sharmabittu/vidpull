<?php

return [

    /*
    |--------------------------------------------------------------------------
    | yt-dlp binary path
    |--------------------------------------------------------------------------
    | Set YTDLP_BINARY in your .env to override. Install yt-dlp globally with:
    |   pip install yt-dlp   OR   sudo curl -L https://yt-dlp.org/downloads/latest/yt-dlp -o /usr/local/bin/yt-dlp && chmod +x /usr/local/bin/yt-dlp
    */
    'ytdlp_binary' => env('YTDLP_BINARY', '/usr/local/bin/yt-dlp'),

    /*
    |--------------------------------------------------------------------------
    | Output directory (relative to storage/app)
    |--------------------------------------------------------------------------
    */
    'output_dir' => env('VIDPULL_OUTPUT_DIR', 'downloads'),

    /*
    |--------------------------------------------------------------------------
    | Maximum concurrent downloads
    |--------------------------------------------------------------------------
    */
    'max_concurrent' => (int) env('VIDPULL_MAX_CONCURRENT', 3),

    /*
    |--------------------------------------------------------------------------
    | Allowed URL hosts (empty = allow all)
    |--------------------------------------------------------------------------
    */
    'allowed_hosts' => array_filter(explode(',', env('VIDPULL_ALLOWED_HOSTS', ''))),

];
