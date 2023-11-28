<?php

namespace Superzc\Miniprogram;

use Illuminate\Config\Repository;

class Miniprogram {

    protected $config;

    /**
     * 构造方法
     */
    public function __construct(Repository $config)
    {
        $this->config = $config->get('miniprogram');
    }
    
    public function codeToSession()
    {
        $data = [];

        return [
            'ret' => 0,
            'msg' => 'success',
            'data' => $data,
        ];
    }
 
}