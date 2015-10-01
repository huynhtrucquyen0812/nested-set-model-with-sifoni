## Nested Set Model with Sifoni

1. Khái niệm NSM: https://github.com/PhuongNamCorpsIntern/workspace/issues/4

2. Mình viết 1 cái model để kế thừa là **model/NestedSetModel.php**. Model này kế thừa **Base.php** và có thể được kế thừa bởi bất cứ Model nào có thể sử dụng theo kiểu NSM. Theo demo ở đây là **model/Node.php**

3. Chỉ cần khai báo cho **Node.php** như sau:

```php
use App\Model\NestedSetModel;

class Node extends NestedSetModel{
	protected $table = 'nodes';
	protected $fillable=['id', 'value', 'left', 'right'];
}
```
với các giá trị trong mảng ```$fillable``` là các ```column``` được sử dụng với ý nghĩa tương ứng.

4. Sau đó, ta sẽ có thể sử dụng các phương thức của **model/NestedSetModel.php** để thao tác với NSM table.

```php
Node::addNode($nodeName, $parentName, $position);
// Thêm 1 node mới
//$position là vị trí trong parent mà node mới sẽ ở
// Ví dụ: 1, 2, 3...

Node::deleteNode($nodeName);
// Xóa 1 node

Node::moveNode($parentName, $nodeName, $position);
// Di chuyển 1 node cùng với child của nó
```