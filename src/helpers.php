<?php

if (! function_exists('ethereumd')) {
    /**
     * Get Ethereumd client instance.
     *
     * @return \Weisskpub\Ethereum\Client
     */
    function ethereumd()
    {
        return app('ethereumd');
    }
}
