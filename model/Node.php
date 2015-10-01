<?php

namespace App\Model;

use App\Model\NestedSetModel;

class Node extends NestedSetModel{
	protected $table = 'nodes';
	protected $fillable=['id', 'value', 'left', 'right'];
}