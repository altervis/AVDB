<?php

namespace AlterVision\AVDB;

class Mysql extends Connect
{
    protected function __construct()
    {
        parent::__construct();

//        $this->query('SET names utf8');
//        $this->query('SET character set utf8');
//        $this->query('SET character_set_client = utf8');
//        $this->query('SET character_set_results = utf8');
//        $this->query('SET character_set_connection = utf8');
//        $this->query('SET collation_connection = utf8_general_ci');
    }
}
