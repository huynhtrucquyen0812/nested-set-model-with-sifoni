<?php

namespace App\Controller;

use Sifoni\Controller\Base;
use App\Model\Node;
use Sifoni\Model\DB;

class NodeController extends Base {
	public function old_indexAction(){
		$result='<ul>ROOT';
		$not_parent = false;
		for ($i=1; $i < Node::where('left', 0)->first()['right']; $i++) {
			if(Node::where('left', $i)->first() && Node::where('left', $i+1)->first()){
				$cur_node=Node::where('left', $i)->first();
				$result .= '<li>'.$cur_node['value'].'<ul>';
				$not_parent=false;
			}else if(Node::where('left', $i)->first() && Node::where('right', $i+1)->first()){
				$cur_node=Node::where('left', $i)->first();
				$result .= '<li>'.$cur_node['value'];
				$not_parent=true;
			}else {
				if($not_parent){
					$result.='</li>';
					$not_parent=false;
				}
				else
					$result.='</ul></li>';
			}
		}
		$result.='</ul>';
		$data['head']=$result;

		$result='<option value="'.Node::where('value', 'Root')->first()['id'].'"><b>ROOT</b></option>';
		$not_parent = false;
		$space='';
		$root_child=[true, 1];
		for ($i=1; $i < Node::where('left', 0)->first()['right']; $i++) {
			if(Node::where('left', $i)->first() && Node::where('left', $i+1)->first()){
				$cur_node=Node::where('left', $i)->first();
				if($root_child[0]){
					$result .= '<option value="'.$cur_node['id'].'">'.$cur_node['value'];
					$root_child=[false, $cur_node['right']+1];
				}
				else
					$result .= '<option value="'.$cur_node['id'].'">'.$space.'&diams; '.$cur_node['value'];
				$space.='&nbsp; ';
				$not_parent=false;
			}else if(Node::where('left', $i)->first() && Node::where('right', $i+1)->first()){
				$cur_node=Node::where('left', $i)->first();
				if($root_child[0]){
					$result .= '<option value="'.$cur_node['id'].'">'.$cur_node['value'];
					$root_child=[false, $cur_node['right']+1];
				}else
					$result .= '<option value="'.$cur_node['id'].'">'.$space.'&diams; '.$cur_node['value'];
				$not_parent=true;
			}else {
				if($not_parent){
					$result.='</option>';
					$not_parent=false;
				}
				else
					$result.='</option>';
				if(Node::where('right', $i)->first() && Node::where('right', $i+1)->first())
					$space = substr($space, 0, -7);
				if($i+1==$root_child[1]){
					$space='';
					$root_child[0]=true;
				}
			}
		}

		$data['select']=$result;
		$data['selectNoRoot']=substr($result, 38, strlen($result));
		return $this->render('node.html.twig', $data);
	}

	public function indexAction(){
		$result=Node::getNodesAsTree();
		$data['head']=$result;
		$data['select']=$result;
		return $this->render('node.html.twig', $data);
	}

	public function createAction(){
		if ($postData = $this->getPostData()){
			// Node::addNode($postData['value'], $postData['parent'], $postData['position']);
			$node=new Node();
			$node->value=$postData['value'];
			$node->insertNode($postData['parent'], $postData['position']);
	    }
		return $this->redirect('nodeIndex');
	}

	public function deleteAction(){
		if ($postData = $this->getPostData()){
			// Node::deleteNode($postData['value']);
			$node=Node::find($postData['value']);
			$node->removeNode();
		}
		return $this->redirect('nodeIndex');
	}

	public function moveAction(){
		if ($postData = $this->getPostData()){
			// Node::moveNode($postData['parent'], $postData['value'], $postData['position']);
			$node=Node::find($postData['value']);
			$node->updateNode($postData['parent'], $postData['position']);
		}
		return $this->redirect('nodeIndex');
	}
}