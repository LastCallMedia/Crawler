<?php


namespace LastCall\Crawler\RequestData;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use LastCall\Crawler\Common\DoctrineSetupTeardownTrait;
use LastCall\Crawler\Common\SetupTeardownInterface;

class DoctrineRequestDataStore implements RequestDataStore, SetupTeardownInterface {

    use DoctrineSetupTeardownTrait;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    private $table;

    public function __construct(Connection $connection, $table = 'request_data') {
        $this->connection = $connection;
        $this->table = $table;
    }

    protected function getConnection() {
        return $this->connection;
    }

    protected function getSchema() {
        $schema = new Schema();
        $table = $schema->createTable($this->table);
        $table->addColumn('uri', 'binary');
        $table->addColumn('data', 'binary');
        $table->setPrimaryKey(['uri']);

        return $schema;
    }

    public function merge($uri, array $data) {
        $existing = $this->fetch($uri);
        if(null !== $existing) {
            $this->connection->update($this->table, [
                'data' => serialize($data + $existing),
            ], [
                'uri' => $uri
            ]);
        }
        else {
            $this->connection->insert($this->table, [
                'uri' => $uri,
                'data' => serialize($data),
            ]);
        }
    }

    public function fetch($uri) {
        $stmt = $this->connection->createQueryBuilder()
            ->select('data')
            ->from($this->table)
            ->where('uri = :uri')
            ->setParameter('uri', $uri)
            ->execute();


        $data = $stmt->fetchColumn();
        if($data !== FALSE) {
            return unserialize($data);
        }
        return null;
    }

    public function fetchAll() {
        $stmt = $this->connection->createQueryBuilder()
            ->select('uri, data')
            ->from($this->table)
            ->execute();

        $res = [];
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $res[$row['uri']] = unserialize($row['data']);
        }
        return $res;
    }
}