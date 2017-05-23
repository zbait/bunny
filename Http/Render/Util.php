<?php

namespace Bunny\Http\Render;

class Util{
	public function write($data){
        echo $data;
    }

    public function safeWrite($data){
    	echo isset($data) ? $data : '';
    }
}
