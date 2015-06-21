<?php
namespace InfluxDB\Helper;

trait ErrorSuppression
{
    public function suppressErrors()
    {
        set_error_handler(function() {});
    }

    public function restoreErrors()
    {
        restore_error_handler();
    }
}
