<?php

namespace App\Model;

use Sifoni\Model\Base;
use Sifoni\Model\DB;

abstract class NestedSetModel extends Base {
	public $timestamps = false;
	protected static $atb=['a', 'b', 'c', 'd'];
	protected static $tbl='';

	public function __construct(){
		self::$atb = $this->fillable;
		self::$tbl = $this->table;
	}

	public static function getNodes(){
		if($nodes = static::all()){
			return $nodes;
		}
		return false;
	}

	public static function addNode($child_name, $parent_id, $position){
		$sth=new static;
		$child[self::$atb[1]]=$child_name;
		$parent = static::findOrFail($parent_id);
		// Xác định left, right
		for ($i=$parent[self::$atb[2]]+1; $i <= $parent[self::$atb[3]]; ) { 
			if($position==1){
				$child[self::$atb[2]]=$i;
				$child[self::$atb[3]]=$i+1;
				break;
			}else{
				$cur_child = static::where(self::$atb[2], $i)->first();
				$i = $cur_child[self::$atb[3]]+1;
				$position--;
			}
		}
		// Update relevant nodes
		DB::table(self::$tbl)
			->where(self::$atb[2], '>=', $child[self::$atb[2]])
			->increment(self::$atb[2], 2);
		DB::table(self::$tbl)
			->where(self::$atb[3], '>=', $child[self::$atb[2]])
			->increment(self::$atb[3], 2);
		// Thêm node mới vào cuối cùng sau khi update
		DB::table(self::$tbl)->insert($child);
		var_dump($child);
	}

	public static function deleteNode($child_id){
		$sth=new static;
		$child=static::where(self::$atb[0], $child_id)->first();
		$space=$child[self::$atb[3]]-$child[self::$atb[2]]+1; // khoảng trống để lại

		// Xóa nó và con của nó
		DB::table(self::$tbl)->where(self::$atb[2], '>=', $child[self::$atb[2]])
						  ->where(self::$atb[3], '<=', $child[self::$atb[3]])
						  ->delete();
		// Update relevant nodes
		DB::table(self::$tbl)
			->where(self::$atb[2], '>', $child[self::$atb[3]])
			->decrement(self::$atb[2], $space);
		DB::table(self::$tbl)
			->where(self::$atb[3], '>', $child[self::$atb[3]])
			->decrement(self::$atb[3], $space);
	}

	public static function moveNode($parent_id, $child_id, $position){
		$sth=new static;
		$new_parent=static::findOrFail($parent_id);
		$old_child[self::$atb[0]] =$child_id;
		$old_child = static::where(self::$atb[0], $old_child[self::$atb[0]])->first();
		$space = $old_child[self::$atb[3]] - $old_child[self::$atb[2]]+1;

		// Xác định left, right mới
		for ($i=$new_parent[self::$atb[2]]+1; ; ) {
			if($position==1){
				$new_child[self::$atb[2]]=$i;
				$new_child[self::$atb[3]]=$i+$space-1;
				break;
			}else{
				$cur_child = static::where(self::$atb[2], $i)->first();
				$i = $cur_child[self::$atb[3]]+1;
				if($old_child[self::$atb[2]] != $cur_child[self::$atb[2]])
					$position--;
			}
		}
		//Update relevant nodes
		if($new_child[self::$atb[2]] > $old_child[self::$atb[2]]){
			$new_child[self::$atb[2]]-=$space;
			$new_child[self::$atb[3]]-=$space;
			$child_road = $new_child[self::$atb[2]] - $old_child[self::$atb[2]];

			$space = -$space;
			$dau1='>';
			$dau2='<=';
			$lr1=self::$atb[2];
			$lr2=self::$atb[3];
		}else{
			$child_road = $new_child[self::$atb[2]] - $old_child[self::$atb[2]];
			$dau1='<';
			$dau2='>=';
			$lr1=self::$atb[3];
			$lr2=self::$atb[2];
		}
		$little_child=DB::table(self::$tbl)
						->where(self::$atb[2], '>', $old_child[self::$atb[2]])
						->where(self::$atb[3], '<', $old_child[self::$atb[3]])
						->lists(self::$atb[0]);
		DB::table(self::$tbl)
			->whereIn(self::$atb[0], $little_child)
			->increment(self::$atb[2], $child_road);
		DB::table(self::$tbl)
			->whereIn(self::$atb[0], $little_child)
			->increment(self::$atb[3], $child_road);

		$need_update=DB::table(self::$tbl)
					->where($lr1, $dau1, $old_child[$lr1]) // left, >, 3
					->where($lr2, $dau2, $new_child[$lr2]) // right, <=, 9
					->whereNotIn(self::$atb[0], $little_child)
					->lists(self::$atb[0]);
		DB::table(self::$tbl)
			->whereIn(self::$atb[0], $need_update)
			->increment(self::$atb[2], $space);
		DB::table(self::$tbl)
			->whereIn(self::$atb[0], $need_update)
			->increment(self::$atb[3], $space);

		DB::table(self::$tbl)
			->where($lr1, $dau2, $new_child[$lr2]) // left, <=, 9
			->where($lr1, $dau1, $old_child[$lr1]) // left, >, 3
			->where($lr2, $dau1, $new_child[$lr2]) // right, >, 9
			->whereNotIn(self::$atb[0], $little_child)
			->whereNotIn(self::$atb[0], $need_update)
			->increment($lr1, $space);
		DB::table(self::$tbl)
			->where($lr2, $dau1, $old_child[$lr1]) // right, >, 3
			->where($lr2, $dau2, $new_child[$lr2]) // right, <=, 9
			->where($lr1, $dau2, $old_child[$lr1]) // left, <=, 3
			->whereNotIn(self::$atb[0], $little_child)
			->whereNotIn(self::$atb[0], $need_update)
			->increment($lr2, $space);

		static::where(self::$atb[0], $old_child[self::$atb[0]])->update($new_child);
	}
}