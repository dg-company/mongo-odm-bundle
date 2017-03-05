# MongoODMBundle

The MongoODM bundle provides a PHP object mapping for MongoDB and PHP 7.1+.

## Requirements

- PHP 7.1 or higher
- mongodb PHP driver _(the legacy driver "mongo" is not supported!)_
- Symfony3

## Documents

Documents are simple PHP classes extending the class `DGC\MongoODMBundle\Document\Document` and containing some annotations, so that we know how to map MongoDB types to PHP types and the other way around.
Every document class needs a Document-Annotation and one annotation for each property which will be mapped to a MongoDB field.  
Documents will be persisted to a MongoDB collection with the same name as the PHP class.

    <?php
    
    namespace AppBundle\Document;
    
    use MongoDB\BSON\ObjectID;
    use DGC\MongoODMBundle\Annotation as MongoDB;
    use DGC\MongoODMBundle\Document\Document;
    
    /**
     * @MongoDB\Document()
     */
    class User extends Document
    {
    
        /**
         * @MongoDB\Field(type="objectId")
         * @var ObjectID
         */
        protected $id;
    
        /**
         * @MongoDB\Field(type="string")
         * @var string
         */
        protected $email;
    
        /**
         * @MongoDB\Field(type="bool")
         * @var bool
         */
        protected $active;
    
    
    
        /**
         * @return ObjectID
         */
        public function getId(): ObjectID
        {
            return $this->id;
        }
    
        /**
         * @return string
         */
        public function getEmail(): ?string
        {
            return $this->email;
        }
    
        /**
         * @param string $email
         * @return User
         */
        public function setEmail(string $email): User
        {
            $this->email = $email;
            return $this;
        }
    
        /**
         * @return bool
         */
        public function isActive(): ?bool
        {
            return $this->active;
        }
    
        /**
         * @param bool $active
         * @return User
         */
        public function setActive(bool $active): User
        {
            $this->active = $active;
            return $this;
        }
    
    }
    
### Supported field types

- `float`
- `string` 
- `object` (associative array) 
- `array` (array with numeric indexes) 
- `binData`
- `objectId` (MongoDB ObjectId) 
- `bool`
- `date` (ISODate in MongoDB, DateTime in PHP) 
- `int`
- `timestamp` 
- `raw` (No mapping)

### References

You can create references between documents using the `ReferenceOne` and `ReferenceMany` annotations.
On the PHP-side references are simple object properties containing a PHP object or an array of PHP objects.
On the MongoDB-side they are persisted as DBRef values or arrays of DBRef values.

When you load a document containing references, an "empty" PHP object will be created for each referenced document. The referenced document will not be loaded from MongoDB until you access a property other than `id` for the first time.

#### Reference one document
  
    /**
     * @MongoDB\ReferenceOne(document="AppBundle\Document\Address")
     * @var Address
     */
    protected $address;

#### Reference many documents

    /**
     * @MongoDB\ReferenceMany(document="AppBundle\Document\Address")
     * @var Address[]
     */
    protected $addresses;

### Embedded documents

You can embed documents into other documents using the `EmbeddOne` and `EmbedMany` annotations.
Embedded documents are embedded into their parent document and can not be loaded separately.

*Do not use embedded documents to save a high number of documents or if the number of embedded documents increases over time!*

#### Embed one document
  
    /**
     * @MongoDB\EmbedOne(document="AppBundle\Document\Address")
     * @var Address
     */
    protected $address;

#### Embed many documents

    /**
     * @MongoDB\EmbedMany(document="AppBundle\Document\Address")
     * @var Address[]
     */
    protected $addresses;

### Override the collection used by a class
 
    ...
    /**
     * @MongoDB\Document(document="OtherUserCollection")
     */
    class User extends Document
    {
    ...
 
## Query documents

To create a query and get mapped PHP objects, you need to use the Document Manager service `dgc_mongo_odm.document_manager`.
It provides methods to find documents, save documents and to directly access the MongoDB collection to perform queries not supported by this bundle (Indexing, Aggregation framework, ...). 

### Query documents using the Query Builder

The Query Builder helps you to create MongoDB queries without typing a bunch of square brackets.
You can simply build a query using fluent PHP methods and it will be automatically converted to a MongoDB query.
The Query builder will also make sure that some data types like DateTime objects are converted to the appropriate MongoDB types.

#### Find a single document

    $dm = $this->get("dgc_mongo_odm.document_manager");
     
    $user = $qb = $dm->createQueryBuilder(User::class)
        ->field('id')->equals(new ObjectID("58a9ed31944bd3000712eb93"))
        ->getQuery()
        ->findOne()
    ;

#### Find multiple documents

    $dm = $this->get("dgc_mongo_odm.document_manager");
     
    $users = $qb = $dm->createQueryBuilder(User::class)
        ->field('active')->equals(true)
        ->getQuery()
        ->find()
    ;

#### Limiting Results

You can limit results and set an offset by using the `limit()` and `skip()` methods.

    $users = $qb = $dm->createQueryBuilder(User::class)
        ->field('active')->equals(true)
        ->limit(10)
        ->skip(5)
        ->getQuery()
        ->find()
    ;
    
#### Sorting Results

You can sort results by using the `sort()` method.

    $users = $qb = $dm->createQueryBuilder(User::class)
        ->field('active')->equals(true)
        ->sort('time', 'asc')
        ->getQuery()
        ->find()
    ;

If you want to sort by multiple fields, you can call `sort()` again.

    $users = $qb = $dm->createQueryBuilder(User::class)
        ->field('active')->equals(true)
        ->sort('time', 'asc')
        ->sort('age', 'desc')
        ->getQuery()
        ->find()
    ;

#### Disable hydration

If you want to disable hydration, you can call `hydrate(false)` and a raw result will be returned.

Alternatively you can use the Document Managers `getCollectionForClass(string $class)` method, to directly access the MongoDB collection.

#### Supported operators

- `equals($value)`
- `notEqual($value)`
- `gt($value)`
- `lt($value)`
- `gte($value)`
- `lte($value)`
- `range($min, $max)`
- `in($value)`
- `notIn($value)`
- `not(Expression $value)`
- `exists()`
- `mod(int $divisor, int $remainder)`
- `text(string $search, string $language, bool $caseSensitive = false, bool $diacriticSensitive = false)`
- `where(string $javaScript)`
- `size(int $size)`

### Query documents using raw MongoDB queries

If you want to write a MongoDB query by hand, you can directly use the `find()` and `findOne()` methods of the document manager service.

    $user = $dm->findOne(User::class, [
        'active' => true
    ], [
        //options
    ]);

` `

    $users = $dm->find(User::class, [
        'active' => true
    ], [
        //options
    ]);

## Persist documents

To persist a document, simply call the Document Managers `save($documents)` method for a single object or an array of objects.

_Only the specified object(s) will be persisted! Referenced objects have to be persisted separately!_
 
 
 
## TODO

- Create Proxy objects when clearing cache for production environment
- Add queries and performance to debug toolbar
- GridFS
- Implement embedded documents
- Documentation: Debugging + MongoDB profiler
- Debugger: Warnings
