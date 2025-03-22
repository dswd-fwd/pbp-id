<?php

namespace App\Models;

class SignatureMysql extends Signature
{
    protected $connection = 'mysql';
    protected $table = 'signatures';
}
