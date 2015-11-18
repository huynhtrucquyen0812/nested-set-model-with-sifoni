## Nested Set Model with Sifoni

- Khái niệm NSM: https://github.com/PhuongNamCorpsIntern/workspace/issues/4

- Mình viết 1 cái model để kế thừa là **model/NestedSetModel.php**. Model này kế thừa **Base.php** và có thể được kế thừa bởi bất cứ Model nào có thể sử dụng theo kiểu NSM. Theo demo ở đây là **model/Node.php**

- Chỉ cần khai báo cho **Node.php** như sau:

```php
use App\Model\NestedSetModel;

class Node extends NestedSetModel{
	protected $table = 'nodes';
	protected $fillable=['id', 'value', 'left', 'right'];
}
```
với các giá trị trong mảng ```$fillable``` là các ```column``` được sử dụng với ý nghĩa tương ứng.

- Sau đó, ta sẽ có thể sử dụng các phương thức của **model/NestedSetModel.php** để thao tác với NSM table.

```php
Node::addNode($nodeName, $parentID, $position);
// Thêm 1 node mới
//$position là vị trí trong parent mà node mới sẽ ở
// Ví dụ: 1, 2, 3...

Node::deleteNode($nodeID);
// Xóa 1 node

Node::moveNode($parentID, $nodeID, $position);
// Di chuyển 1 node cùng với child của nó
```

- Thêm function: (Update 18/11/2015)

a. Insert, Update, Delete

```php
// Thêm node mới
$node=new Node(); // tạo node mới
$node->value = $nodeName; // cung cấp tên node
$node->insertNode($parentID, $position); // gọi hàm thêm mới node

// Xóa node
$node=Node::find($nodeID); // lấy node
$node->removeNode(); // gọi hàm xóa node

// Update node
$node=Node::find($nodeID); // lấy node
$node->updateNode($parentID, $position); // gọi hàm update node và cung cấp parent cùng vị trí mới
```

b. Get Nodes as Tree

```php
$nodes = Node::getNodesAsTree();

// Result
array (
  'id' => 0,
  'value' => 'Root',
  'left' => 0,
  'right' => 11,
  'child' => 
  array (
    49 => 
    array (
      'id' => 49,
      'value' => 'B',
      'left' => 1,
      'right' => 4,
      'child' => 
      array (
        50 => 
        array (
          'id' => 50,
          'value' => 'con b',
          'left' => 2,
          'right' => 3,
        ),
      ),
    ),
    43 => 
    array (
      'id' => 43,
      'value' => 'A',
      'left' => 5,
      'right' => 10,
      'child' => 
      array (
        48 => 
        array (
          'id' => 48,
          'value' => 'con 2',
          'left' => 6,
          'right' => 9,
          'child' => 
          array (
            51 => 
            array (
              'id' => 51,
              'value' => 'con của con 2',
              'left' => 7,
              'right' => 8,
            ),
          ),
        ),
      ),
    ),
  ),
)
```
